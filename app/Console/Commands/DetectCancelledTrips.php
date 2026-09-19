<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\GroupDive;
use App\Models\Operator;
use App\Models\Trip;
use App\Models\User;
use App\Services\NotificationService;
use App\Support\OperatorHealth;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mailgun\Mailgun;

/**
 * Flags saved trips whose operator has dropped them from the schedule, and
 * tells the divers who saved them - by email and in-app only, never SMS/
 * WhatsApp (Pablo, 2026-09-19: "send a cancellation notification by email
 * and in app if the cancellation happened before we texted or WhatsApp the
 * user"). Before this command, a saved trip that stopped resolving just
 * silently vanished from My Calendar/Dashboard (EventController::show(),
 * MyDashboardController) with no notice at all.
 *
 * There is no "cancelled" status anywhere in the scraped data - trips are
 * hard-deleted and re-inserted daily with fresh ids, so "missing" is the
 * only signal, and it's ambiguous: it also means a genuinely future trip
 * beyond an operator's current scrape horizon, a custom group dive with no
 * backing scraped trip at all, or a scraper that broke on one operator's
 * page. Two consecutive misses roughly an hour apart, an operator-health
 * check, a scrape-horizon check and a custom-dive exclusion all exist to
 * keep this from confirming a "cancellation" that never happened.
 */
class DetectCancelledTrips extends Command
{
    protected $signature = 'trips:detect-cancelled
                            {--dry-run : Report what would change without writing or sending anything}
                            {--event= : Limit to one event id (for testing)}';

    protected $description = 'Flags saved trips whose operator has dropped them from the schedule, and tells the divers who saved them';

    /** Misses in a row before a trip counts as cancelled rather than a scraper hiccup. */
    private const MIN_MISSES = 2;

    /** ...and they must span at least this long, so two runs minutes apart can't confirm one. */
    private const MIN_MISS_MINUTES = 50;

    private bool $dryRun = false;

    private array $counts = [
        'checked' => 0, 'resolved' => 0, 'recovered' => 0,
        'missing_new' => 0, 'missing_repeat' => 0,
        'skipped_custom' => 0, 'skipped_horizon' => 0, 'skipped_departed' => 0, 'skipped_operator' => 0,
        'confirmed' => 0, 'notified' => 0, 'quiet' => 0,
    ];

    public function handle()
    {
        $this->dryRun = (bool) $this->option('dry-run');

        $events = Event::whereNull('cancelled_at')
            ->whereDate('date', '>=', Carbon::today())
            ->when($this->option('event'), fn ($q, $id) => $q->where('id', $id))
            ->get();

        $operators = Operator::select('id', 'operatorName', 'phone', '_status', '_updatedCount')
            ->get()->keyBy('id');

        $horizon = Trip::whereDate('date', '>=', Carbon::today())
            ->selectRaw('operatorId, COUNT(*) as tripCount, MAX(date) as maxDate')
            ->groupBy('operatorId')
            ->get()->keyBy('operatorId');

        $customDiveIds = GroupDive::where('is_custom', true)->pluck('id')->flip();

        $resolved = [];
        $now = Carbon::now();

        foreach ($events as $event) {
            $this->counts['checked']++;

            if ($event->group_dive_id && $customDiveIds->has($event->group_dive_id)) {
                $this->counts['skipped_custom']++;
                continue;
            }

            if (empty($event->operatorId)) {
                $this->counts['skipped_custom']++;
                continue;
            }

            $time = $event->time ?: '00:00';
            $departure = Carbon::parse(Carbon::parse($event->date)->format('Y-m-d') . ' ' . $time);
            $departed = $time === '00:00' ? $departure->copy()->endOfDay()->lt($now) : $departure->lt($now);
            if ($departed) {
                $this->counts['skipped_departed']++;
                continue;
            }

            $op = $operators->get((int) $event->operatorId);
            $opHorizon = $horizon->get((int) $event->operatorId);
            if (Carbon::parse($event->date)->gt(Carbon::parse($opHorizon->maxDate ?? '1970-01-01'))) {
                $this->counts['skipped_horizon']++;
                continue;
            }

            $key = Carbon::parse($event->date)->toDateString() . '|' . $event->time . '|'
                . $event->operatorId . '|' . $event->tripName;
            // $event only passed on a cache miss (and never during --dry-run) so a
            // detected rename can self-heal the event's stored tripName; other
            // events sharing this exact key already have the identical name, so
            // the cache hit path costs them nothing beyond healing lazily later.
            $resolved[$key] ??= Trip::existsForComposite(
                $event->date, $event->time, $event->operatorId, $event->tripName,
                $this->dryRun ? null : $event
            );

            if ($resolved[$key]) {
                $this->counts['resolved']++;
                if ($event->missing_since !== null || $event->missing_checks > 0) {
                    $this->counts['recovered']++;
                    if (!$this->dryRun) {
                        $event->missing_since = null;
                        $event->missing_checks = 0;
                        $event->save();
                    }
                }
                continue;
            }

            if (!$this->operatorIsTrusted($op, $opHorizon)) {
                $this->counts['skipped_operator']++;
                continue;
            }

            $event->missing_checks > 0 ? $this->counts['missing_repeat']++ : $this->counts['missing_new']++;

            if ($this->dryRun) {
                continue;
            }

            $event->missing_since ??= $now;
            $event->missing_checks++;
            $event->save();

            if ($event->missing_checks >= self::MIN_MISSES
                && $event->missing_since->lte($now->copy()->subMinutes(self::MIN_MISS_MINUTES))
            ) {
                $event->cancelled_at = $now;
                $event->save();
                $this->counts['confirmed']++;
                $this->notify($event, $op);
            }
        }

        // Already-cancelled events aren't touched above (cancelled_at is
        // terminal) - just log if one somehow resolves again, as a signal
        // to review the guards above.
        Event::whereNotNull('cancelled_at')
            ->whereDate('date', '>=', Carbon::today())
            ->when($this->option('event'), fn ($q, $id) => $q->where('id', $id))
            ->get()
            ->each(function (Event $event) {
                if (Trip::existsForComposite($event->date, $event->time, $event->operatorId, $event->tripName)) {
                    Log::warning('Cancelled event resolved again: event ' . $event->id . ' (' . $event->tripName . ')');
                }
            });

        $this->printSummary();

        return self::SUCCESS;
    }

    /**
     * Is this operator's data good enough right now to conclude that a
     * missing trip is really cancelled? OperatorHealth::status() already
     * downgrades "OK but zero dives added" to a warning tone - that's the
     * scraper breaking on the operator's page, not an operator with no
     * trips, and is exactly the case that would otherwise cancel every
     * saved trip for that shop at once.
     */
    private function operatorIsTrusted(?Operator $op, $opHorizon): bool
    {
        if (!$op) {
            return false; // operator row itself is gone - not this job's call to make
        }

        $status = OperatorHealth::status($op->_status, $op->_updatedCount);
        if ($status['code'] !== 0 || $status['tone'] !== 'good') {
            return false;
        }

        return (int) ($opHorizon->tripCount ?? 0) > 0;
    }

    /**
     * Stay quiet if this diver has already been reached about this dive on
     * a channel we deliberately don't use for cancellations. The only
     * record we have is `group_dive_reminders_sent`, which is per dive and
     * per offset ('3day'/'1day'), not per user or per channel - so a
     * reminder batch having gone out for this dive is the proxy for "this
     * diver may already know", re-checked against their current phone
     * opt-in the same way the sender applies it at send time. A
     * personal-only saved trip has no group dive and so no reminder system
     * reaches it at all - always eligible.
     */
    private function alreadyReachedByPhone(Event $event, User $user): bool
    {
        if (!$event->group_dive_id) {
            return false;
        }

        if (!$user->phone || (!$user->sms_notifications && !$user->whatsapp_notifications)) {
            return false;
        }

        return DB::connection('mysql_trips')->table('group_dive_reminders_sent')
            ->where('group_dive_id', $event->group_dive_id)
            ->whereIn('type', ['3day', '1day'])
            ->where('sent_at', '<', $event->cancelled_at ?? now())
            ->exists();
    }

    private function notify(Event $event, ?Operator $operator): void
    {
        $user = User::find($event->userId);
        if (!$user || !$user->isNotGuest()) {
            return;
        }

        if ($this->alreadyReachedByPhone($event, $user)) {
            $this->line("  skipped (already reached by SMS/WhatsApp): event {$event->id}");
            $this->counts['quiet']++;
            return; // cancelled_at stays set, cancel_notified_at stays null
        }

        $sentEmail = $this->sendCancellationEmail($event, $user, $operator);
        $sentInApp = $this->notifyCancellationInApp($event, $user);

        if ($sentEmail || $sentInApp) {
            $event->cancel_notified_at = Carbon::now();
            $event->save();
            $this->counts['notified']++;
            Log::info('Sent trip cancellation notice: event ' . $event->id . ', user ' . $user->id . ', trip ' . $event->tripName);
        } else {
            $this->counts['quiet']++;
        }
    }

    /**
     * Reuses the existing "tripreminder" Mailgun template rather than
     * introducing a new one - it supplies "Dear {{name}}:" and a sign-off
     * around a plain body variable and isn't reminder-specific in its own
     * copy, so a cancellation body slots straight in with no external
     * Mailgun setup step.
     */
    private function sendCancellationEmail(Event $event, User $user, ?Operator $operator): bool
    {
        if (!$user->email || !$user->email_notifications) {
            return false;
        }

        $time = $event->time ?: '00:00';
        $when = Carbon::parse($event->date)->format('l, F j')
            . ($time !== '00:00' ? ' at ' . Carbon::parse($time)->format('g:i A') : '');

        // No greeting or sign-off - the "tripreminder" template wraps this
        // body in "Dear {{name}}:" and "Kind regards, Divers Hub." already.
        $html = '<p>A trip on your calendar is no longer on the operator\'s schedule:</p>'
            . '<p><b>' . e($event->tripName) . '</b><br>' . e($when)
            . ($operator ? '<br>Operator: ' . e($operator->operatorName) : '') . '</p>'
            . '<p>We check the schedule every hour and this trip has been gone for more than an hour, '
            . 'so it looks cancelled rather than a glitch on our side. '
            . ($operator && $operator->phone
                ? 'If you had booked a seat, call ' . e($operator->operatorName) . ' on ' . e($operator->phone) . ' to confirm.'
                : 'If you had booked a seat, contact the operator to confirm.')
            . '</p>'
            . '<p>It is still on <a href="' . route('MyCalendar') . '">My Calendar</a>, marked cancelled, until you remove it.</p>'
            . '<p><a href="' . route('Trips') . '">Find another dive that day</a></p>';

        try {
            $mg = Mailgun::create(env('MAILGUN_KEY'));
            $mg->messages()->send('mail.divers-hub.com', [
                'from' => 'Divers-Hub <postmaster@mail.divers-hub.com>',
                'to' => $user->name . ' <' . $user->email . '>',
                'subject' => 'Cancelled: ' . $event->tripName . ' on ' . Carbon::parse($event->date)->format('M j'),
                'template' => 'tripreminder',
                'h:X-Mailgun-Variables' => json_encode(['body' => $html, 'name' => $user->name]),
            ]);
            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to send trip cancellation email for event ' . $event->id . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * group_id deliberately null (Inbox, not Groups) even for a group-
     * sourced event - this is personal "your saved trip" information, and
     * passing a group id would route it through the group's mute filter
     * and could silently drop it for a diver who muted that group's chatter.
     * A cancellation notice is not chatter.
     */
    private function notifyCancellationInApp(Event $event, User $user): bool
    {
        $time = $event->time ?: '00:00';
        $when = Carbon::parse($event->date)->format('D, M j')
            . ($time !== '00:00' ? ' at ' . Carbon::parse($time)->format('g:i A') : '');

        NotificationService::notify(
            [$event->userId],
            'Trip cancelled',
            $event->tripName . ' on ' . $when . ' is no longer on the operator\'s schedule. It is marked cancelled on your calendar.',
            route('MyCalendar')
        );

        return true;
    }

    private function printSummary(): void
    {
        $c = $this->counts;
        $mode = $this->dryRun ? ' (dry run)' : '';
        $this->info("trips:detect-cancelled{$mode} - {$c['checked']} future events checked");
        $this->info("  resolved: {$c['resolved']}   recovered: {$c['recovered']}");
        $this->info("  missing: " . ($c['missing_new'] + $c['missing_repeat']) . " (new: {$c['missing_new']}, repeat: {$c['missing_repeat']})");
        $this->info("  skipped: custom/no-operator {$c['skipped_custom']}, beyond operator horizon {$c['skipped_horizon']}, departed {$c['skipped_departed']}, operator unhealthy {$c['skipped_operator']}");
        $this->info("  confirmed cancelled: {$c['confirmed']}");
        $this->info("  notified: {$c['notified']}   quiet (already reached or notifications off): {$c['quiet']}");
    }
}

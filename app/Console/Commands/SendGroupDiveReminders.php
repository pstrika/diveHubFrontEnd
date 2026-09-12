<?php

namespace App\Console\Commands;

use App\Models\Group;
use App\Models\GroupDive;
use App\Models\Operator;
use App\Models\Trip;
use App\Services\NotificationService;
use App\Services\SmsService;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mailgun\Mailgun;

class SendGroupDiveReminders extends Command
{
    protected $signature = 'groups:send-reminders';

    protected $description = 'Emails group members a reminder 3 days and 1 day before an upcoming group dive';

    public function handle()
    {
        $this->sendForOffset(3, '3day');
        $this->sendForOffset(1, '1day');

        return self::SUCCESS;
    }

    private function sendForOffset(int $daysAhead, string $type)
    {
        $targetDate = now()->addDays($daysAhead)->toDateString();

        $dives = GroupDive::whereDate('date', $targetDate)
            ->whereHas('group', fn ($q) => $q->where('reminders_enabled', true))
            ->with(['group.activeMembers.user', 'rsvps.user', 'operator', 'site'])
            ->get();

        foreach ($dives as $dive) {
            $alreadySent = DB::connection('mysql_trips')
                ->table('group_dive_reminders_sent')
                ->where('group_dive_id', $dive->id)
                ->where('type', $type)
                ->exists();

            if ($alreadySent) {
                continue;
            }

            $this->sendReminderEmail($dive, $daysAhead);
            $this->notifyReminderInApp($dive, $daysAhead);
            $this->sendReminderSms($dive, $daysAhead);
            $this->sendReminderWhatsApp($dive, $daysAhead);

            DB::connection('mysql_trips')->table('group_dive_reminders_sent')->insert([
                'group_dive_id' => $dive->id,
                'type' => $type,
                'sent_at' => now(),
            ]);
        }

        $this->info("Sent {$type} reminders for " . $dives->count() . ' dive(s).');
    }

    private function sendReminderEmail(GroupDive $dive, int $daysAhead)
    {
        $group = $dive->group;
        $members = $group->activeMembers;

        if ($members->isEmpty()) {
            return;
        }

        $operator = $dive->operator;
        $goingNames = $dive->rsvps->pluck('user.name')->filter()->implode(', ') ?: 'No one yet';
        $dateFormatted = Carbon::parse($dive->date)->format('l, F j');
        $timeFormatted = $dive->time ? Carbon::parse($dive->time)->format('g:i A') : 'TBD';

        // No opening greeting or sign-off here - the "tripreminder" Mailgun
        // template supplies "Dear {{name}}:" and "Kind regards, Divers Hub."
        // around this body already.
        $html = '<p>This is a reminder that <b>' . e($group->name) . '</b> has a dive coming up in ' . $daysAhead . ' day' . ($daysAhead > 1 ? 's' : '') . ':</p>'
            . '<p><b>' . e($dive->tripName) . '</b><br>'
            . e($dateFormatted) . ' at ' . e($timeFormatted) . '<br>'
            . ($operator ? 'Operator: ' . e($operator->operatorName) . '<br>' : '')
            . '</p>'
            . ($operator && $operator->waiverLink ? '<p><a href="' . e($operator->waiverLink) . '">Sign the operator\'s waiver</a></p>' : '')
            . '<p><b>Who\'s going so far:</b> ' . e($goingNames) . '</p>'
            . '<p><a href="' . route('Groups.show', ['group' => $group->slug]) . '">View the group calendar</a></p>';

        try {
            $mg = Mailgun::create(env('MAILGUN_KEY'));

            foreach ($members as $member) {
                if (!$member->user || !$member->user->email) {
                    continue;
                }

                Log::info('Sending group dive reminder (' . $daysAhead . 'd) to: ' . $member->user->email);

                $mg->messages()->send('mail.divers-hub.com', [
                    'from' => 'Divers-Hub <postmaster@mail.divers-hub.com>',
                    'to' => $member->user->name . ' <' . $member->user->email . '>',
                    'subject' => 'Reminder: ' . $dive->tripName . ' in ' . $daysAhead . ' day' . ($daysAhead > 1 ? 's' : ''),
                    'template' => 'tripreminder',
                    // {{body}} must be {{{body}}} (triple-brace, unescaped) in the
                    // Mailgun template - see SendGroupDiveReminders history for why.
                    'h:X-Mailgun-Variables' => json_encode(['body' => $html, 'name' => $member->user->name]),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Failed to send group dive reminder email: ' . $e->getMessage());
        }
    }

    /**
     * In-app notification center entry + browser push, alongside the email
     * above. Deliberately a separate call rather than folded into
     * NotificationService's own email path - this reminder's email uses its
     * own branded Mailgun template ("tripreminder"), not the generic digest
     * the external emailQueueFlush function sends for other message types.
     */
    private function notifyReminderInApp(GroupDive $dive, int $daysAhead)
    {
        $group = $dive->group;
        $dateFormatted = Carbon::parse($dive->date)->format('D, M j');
        $timeFormatted = $dive->time ? Carbon::parse($dive->time)->format('g:i A') : 'TBD';

        NotificationService::notify(
            $group->activeMembers()->pluck('user_id'),
            $group->name,
            'Reminder: ' . $dive->tripName . ' in ' . $daysAhead . ' day' . ($daysAhead > 1 ? 's' : '') . ' - ' . $dateFormatted . ' at ' . $timeFormatted,
            route('Groups.show', ['group' => $group->slug])
        );
    }

    /**
     * SMS reminder via Twilio, only for members who have both a phone
     * number on file AND have opted in via the `sms_notifications` profile
     * checkbox (required consent for Twilio's A2P 10DLC campaign, and not
     * pre-selected by default). SmsService itself no-ops if Twilio isn't
     * configured, so this is safe to call unconditionally.
     */
    private function sendReminderSms(GroupDive $dive, int $daysAhead)
    {
        $group = $dive->group;
        $members = $group->activeMembers;

        if ($members->isEmpty()) {
            return;
        }

        $dateFormatted = Carbon::parse($dive->date)->format('D, M j');
        $timeFormatted = $dive->time ? Carbon::parse($dive->time)->format('g:i A') : 'TBD';
        $url = route('Groups.show', ['group' => $group->slug]);

        // Opt-out language is repeated in every message on purpose - Twilio's
        // A2P 10DLC campaign is registered with sample messages that include
        // it, and carriers filter traffic that doesn't match its registered
        // samples.
        $body = 'Divers Hub: ' . $dive->tripName . ' is in ' . $daysAhead . ' day' . ($daysAhead > 1 ? 's' : '')
            . ' - ' . $dateFormatted . ' at ' . $timeFormatted . '. ' . $url . ' Reply STOP to unsubscribe.';

        foreach ($members as $member) {
            if (!$member->user || !$member->user->phone || !$member->user->sms_notifications) {
                continue;
            }

            SmsService::send($member->user->phone, $body);
        }
    }

    /**
     * WhatsApp reminder via Meta's Cloud API, only for members who opted in
     * (`whatsapp_notifications`) and only once a dive-reminder template has
     * actually been approved and configured (WHATSAPP_DIVE_REMINDER_TEMPLATE)
     * - WhatsAppService itself also no-ops without API credentials, so this
     * is safe to call unconditionally once that day comes.
     *
     * Unlike SMS, WhatsApp will not send free-form business-initiated text -
     * only this exact approved template, with its exact variable count and
     * order. {$dive->tripName}, "in N day(s)", the date, the time and the
     * group's URL are what the SMS body above says in prose; adjust this
     * parameter list (and add/remove entries) to match whatever the
     * template actually asks for once it's approved - Meta will reject the
     * send (logged by WhatsAppService, not thrown) if they don't line up.
     */
    private function sendReminderWhatsApp(GroupDive $dive, int $daysAhead)
    {
        $template = config('services.whatsapp.dive_reminder_template');
        if (!$template) {
            return;
        }

        $group = $dive->group;
        $members = $group->activeMembers;

        if ($members->isEmpty()) {
            return;
        }

        $dateFormatted = Carbon::parse($dive->date)->format('D, M j');
        $timeFormatted = $dive->time ? Carbon::parse($dive->time)->format('g:i A') : 'TBD';
        $url = route('Groups.show', ['group' => $group->slug]);
        $daysPhrase = $daysAhead . ' day' . ($daysAhead > 1 ? 's' : '');

        foreach ($members as $member) {
            if (!$member->user || !$member->user->phone || !$member->user->whatsapp_notifications) {
                continue;
            }

            WhatsAppService::sendTemplate($member->user->phone, $template, 'en_US', [
                $dive->tripName,
                $daysPhrase,
                $dateFormatted . ' at ' . $timeFormatted,
                $url,
            ]);
        }
    }
}

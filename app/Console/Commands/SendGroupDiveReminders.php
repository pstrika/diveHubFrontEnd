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

    /**
     * Only members who actually RSVP'd "I'm going" to THIS dive - not the
     * whole group (Pablo, 2026-09-22: "If the user is not coming to a
     * certain trip, we don't want to send 1 day and 3 day reminders. Only
     * to the ones that have said 'I'm coming'"). Still respects the
     * existing group/member mute settings via unmutedActiveMembers().
     */
    private function remindableMembers(GroupDive $dive): \Illuminate\Support\Collection
    {
        $goingUserIds = $dive->rsvps->pluck('user_id');

        return $dive->group->unmutedActiveMembers()
            ->filter(fn ($member) => $goingUserIds->contains($member->user_id))
            ->values();
    }

    private function sendReminderEmail(GroupDive $dive, int $daysAhead)
    {
        $group = $dive->group;
        $members = $this->remindableMembers($dive);

        if ($members->isEmpty()) {
            return;
        }

        $operator = $dive->operator;
        $goingNames = $dive->rsvps->pluck('user.name')->filter()->implode(', ') ?: 'No one yet';
        $dateFormatted = Carbon::parse($dive->date)->format('l, F j');
        $timeFormatted = $dive->time ? Carbon::parse($dive->time)->format('g:i A') : 'TBD';

        // Same document for every recipient - render it once. Uses the
        // blank Mailgun template ("2026 new dh template"), same as the
        // newsletter, instead of the old dedicated "tripreminder" template
        // (Pablo, 2026-09-22: "we need to start using the blank template
        // in mailgun - the same one we have with newsletters" - approved
        // after reviewing a sample render of this exact document).
        $document = view('emails.trip-reminder-document', [
            'preheader' => $dive->tripName . ' is in ' . $daysAhead . ' day' . ($daysAhead > 1 ? 's' : '') . ' - here\'s what you need to know.',
            'tripName' => $dive->tripName,
            'daysAhead' => $daysAhead,
            'dateFormatted' => $dateFormatted,
            'timeFormatted' => $timeFormatted,
            'operatorName' => $operator->operatorName ?? null,
            'waiverLink' => $operator->waiverLink ?? null,
            'goingNames' => $goingNames,
            'groupUrl' => route('Groups.show', ['group' => $group->slug]),
        ])->render();

        try {
            $mg = Mailgun::create(env('MAILGUN_KEY'));

            // email_notifications gate added 2026-09-15 (manifest entry
            // #1) - this reminder used to go out regardless of that
            // preference.
            foreach ($members as $member) {
                if (!$member->user || !$member->user->email || !$member->user->email_notifications) {
                    continue;
                }

                Log::info('Sending group dive reminder (' . $daysAhead . 'd) to: ' . $member->user->email);

                $mg->messages()->send('mail.divers-hub.com', [
                    'from' => 'Divers-Hub <postmaster@mail.divers-hub.com>',
                    'to' => $member->user->name . ' <' . $member->user->email . '>',
                    'subject' => 'Reminder: ' . $dive->tripName . ' in ' . $daysAhead . ' day' . ($daysAhead > 1 ? 's' : ''),
                    'template' => '2026 new dh template',
                    'h:X-Mailgun-Variables' => json_encode(['body' => $document]),
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
            $dive->rsvps->pluck('user_id'),
            $group->name,
            'Reminder: ' . $dive->tripName . ' in ' . $daysAhead . ' day' . ($daysAhead > 1 ? 's' : '') . ' - ' . $dateFormatted . ' at ' . $timeFormatted,
            route('Groups.show', ['group' => $group->slug]),
            groupId: $group->id
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
        $members = $this->remindableMembers($dive);

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
     * WhatsApp reminder via Twilio's "trip_reminder_3_with_waiver" Content
     * template (whatsapp/card: header image, body, footer, "Trip Details"
     * and "Sign Waiver" buttons - content.twilio.com, submitted for Meta
     * review 2026-09-14, approved the same day - Pablo: "all WhatsApp
     * templates were approved"), only for members who opted in
     * (`whatsapp_notifications`) and have a phone on file - WhatsAppService
     * itself also no-ops without Twilio credentials configured.
     *
     * The template's variables are fixed by what was actually submitted:
     * {{1}} diver's name, {{2}} site, {{3}} operator, {{4}} date, {{5}}
     * time, {{6}} the group's slug (the "Trip Details" button's URL is
     * https://divers-hub.com/Groups/{{6}}), {{7}} the operator's id (the
     * "Sign Waiver" button is https://divers-hub.com/w/{{7}}, a fixed-
     * domain redirect to that operator's actual waiver link -
     * OperatorController::redirectToWaiver - since WhatsApp's dynamic URL
     * buttons only allow a fixed base domain and every operator's waiver
     * lives on its own separate site). Not the days-ahead/group-URL
     * phrasing the SMS body above uses, since WhatsApp cannot send
     * free-form business-initiated text, only this exact template shape.
     */
    private function sendReminderWhatsApp(GroupDive $dive, int $daysAhead)
    {
        $contentSid = config('services.whatsapp.trip_reminder_content_sid');
        if (!$contentSid) {
            return;
        }

        $group = $dive->group;
        $members = $this->remindableMembers($dive);

        if ($members->isEmpty()) {
            return;
        }

        $site = $dive->site->name ?? $dive->tripName;
        $operatorName = $dive->operator->operatorName ?? 'Divers Hub';
        $dateFormatted = Carbon::parse($dive->date)->format('l, F j');
        $timeFormatted = $dive->time ? Carbon::parse($dive->time)->format('g:i A') : 'TBD';
        // No waiver on file for this operator: the button still renders (the
        // template's shape is fixed) but falls through to the Waivers page
        // instead of a dead link - see redirectToWaiver's own fallback.
        $operatorId = $dive->operatorId ?: 0;

        foreach ($members as $member) {
            if (!$member->user || !$member->user->phone || !$member->user->whatsapp_notifications) {
                continue;
            }

            WhatsAppService::sendTemplate($member->user->phone, $contentSid, [
                '1' => $member->user->name,
                '2' => $site,
                '3' => $operatorName,
                '4' => $dateFormatted,
                '5' => $timeFormatted,
                '6' => $group->slug,
                '7' => (string) $operatorId,
            ]);
        }
    }
}

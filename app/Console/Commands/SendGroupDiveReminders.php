<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\SendsViaPreferredChannel;
use App\Models\Event;
use App\Models\GroupDive;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendGroupDiveReminders extends Command
{
    use SendsViaPreferredChannel;

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

            $this->notifyReminderInApp($dive, $daysAhead);
            $this->sendChannelReminders($dive, $daysAhead);

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
     * to the ones that have said 'I'm coming'") - AND who have actually
     * confirmed/booked it with the operator, not just RSVP'd (Pablo,
     * 2026-10-11: "I only receive reminders for trips I have confirmed
     * booked" - RSVPing alone only puts an unbooked Event on the diver's
     * own calendar, see GroupDiveController::addRsvp()'s 'booked' => false).
     * Still respects the existing group/member mute settings via
     * unmutedActiveMembers().
     */
    private function remindableMembers(GroupDive $dive): \Illuminate\Support\Collection
    {
        $goingUserIds = $dive->rsvps->pluck('user_id');

        $bookedUserIds = Event::where('group_dive_id', $dive->id)
            ->where('booked', true)
            ->whereIn('userId', $goingUserIds)
            ->pluck('userId');

        return $dive->group->unmutedActiveMembers()
            ->filter(fn ($member) => $bookedUserIds->contains($member->user_id))
            ->values();
    }

    /**
     * One channel per member - WhatsApp, then SMS, then email, stopping at
     * the first that's both opted-in and actually sends (Pablo,
     * 2026-10-11: "only in one channel...depending on how the user has
     * given consent: WhatsApp -> SMS -> Email, in that order") - not the
     * old behavior of firing every eligible channel independently and
     * unconditionally, which is why a diver with both WhatsApp and SMS
     * opted in got the same reminder twice. See
     * App\Console\Commands\Concerns\SendsViaPreferredChannel, the same
     * trait App\Console\Commands\SendPostDiveFeedbackRequests already
     * uses for this exact shape.
     */
    private function sendChannelReminders(GroupDive $dive, int $daysAhead)
    {
        $members = $this->remindableMembers($dive);
        if ($members->isEmpty()) {
            return;
        }

        $sms = $this->smsBody($dive, $daysAhead);
        $email = $this->emailPayload($dive, $daysAhead);

        foreach ($members as $member) {
            if (!$member->user) {
                continue;
            }

            $this->sendViaPreferredChannel(
                $member->user,
                $this->whatsappPayload($dive, $daysAhead, $member->user),
                $sms,
                $email
            );
        }
    }

    /**
     * Same document for every recipient - rendered once per dive, not per
     * member, since none of its content (trip/date/time/who's going) is
     * member-specific. Uses the blank Mailgun template ("2026 new dh
     * template"), same as the newsletter, instead of the old dedicated
     * "tripreminder" template (Pablo, 2026-09-22: "we need to start using
     * the blank template in mailgun - the same one we have with
     * newsletters" - approved after reviewing a sample render of this
     * exact document).
     */
    private function emailPayload(GroupDive $dive, int $daysAhead): array
    {
        $group = $dive->group;
        $operator = $dive->operator;
        $goingNames = $dive->rsvps->pluck('user.name')->filter()->implode(', ') ?: 'No one yet';
        $dateFormatted = Carbon::parse($dive->date)->format('l, F j');
        $timeFormatted = $dive->time ? Carbon::parse($dive->time)->format('g:i A') : 'TBD';

        $html = view('emails.trip-reminder-document', [
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

        return [
            'subject' => 'Reminder: ' . $dive->tripName . ' in ' . $daysAhead . ' day' . ($daysAhead > 1 ? 's' : ''),
            'html' => $html,
        ];
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
        $members = $this->remindableMembers($dive);
        if ($members->isEmpty()) {
            return;
        }

        $group = $dive->group;
        $dateFormatted = Carbon::parse($dive->date)->format('D, M j');
        $timeFormatted = $dive->time ? Carbon::parse($dive->time)->format('g:i A') : 'TBD';

        NotificationService::notify(
            $members->pluck('user_id'),
            $group->name,
            'Reminder: ' . $dive->tripName . ' in ' . $daysAhead . ' day' . ($daysAhead > 1 ? 's' : '') . ' - ' . $dateFormatted . ' at ' . $timeFormatted,
            route('Groups.show', ['group' => $group->slug]),
            groupId: $group->id
        );
    }

    /**
     * Opt-out language is repeated on purpose - Twilio's A2P 10DLC campaign
     * is registered with sample messages that include it, and carriers
     * filter traffic that doesn't match its registered samples. Same body
     * for every recipient, so built once per dive rather than per member.
     */
    private function smsBody(GroupDive $dive, int $daysAhead): string
    {
        $dateFormatted = Carbon::parse($dive->date)->format('D, M j');
        $timeFormatted = $dive->time ? Carbon::parse($dive->time)->format('g:i A') : 'TBD';
        $url = route('Groups.show', ['group' => $dive->group->slug]);

        return 'Divers Hub: ' . $dive->tripName . ' is in ' . $daysAhead . ' day' . ($daysAhead > 1 ? 's' : '')
            . ' - ' . $dateFormatted . ' at ' . $timeFormatted . '. ' . $url . ' Reply STOP to unsubscribe.';
    }

    /**
     * Twilio's "trip_reminder_3_with_waiver" Content template (whatsapp/
     * card: header image, body, footer, "Trip Details" and "Sign Waiver"
     * buttons - content.twilio.com, submitted for Meta review 2026-09-14,
     * approved the same day). Variables are fixed by what was actually
     * submitted: {{1}} diver's name, {{2}} site, {{3}} operator, {{4}}
     * date, {{5}} time, {{6}} the group's slug (the "Trip Details"
     * button's URL is https://divers-hub.com/Groups/{{6}}), {{7}} the
     * operator's id (the "Sign Waiver" button is
     * https://divers-hub.com/w/{{7}}, a fixed-domain redirect to that
     * operator's actual waiver link - OperatorController::redirectToWaiver,
     * since WhatsApp's dynamic URL buttons only allow a fixed base domain
     * and every operator's waiver lives on its own separate site). {{1}}
     * is member-specific (their name), so this is built per member, unlike
     * smsBody()/emailPayload() above.
     */
    private function whatsappPayload(GroupDive $dive, int $daysAhead, User $user): ?array
    {
        $contentSid = config('services.whatsapp.trip_reminder_content_sid');
        if (!$contentSid) {
            return null;
        }

        $site = $dive->site->name ?? $dive->tripName;
        $operatorName = $dive->operator->operatorName ?? 'Divers Hub';
        $dateFormatted = Carbon::parse($dive->date)->format('l, F j');
        $timeFormatted = $dive->time ? Carbon::parse($dive->time)->format('g:i A') : 'TBD';
        // No waiver on file for this operator: the button still renders (the
        // template's shape is fixed) but falls through to the Waivers page
        // instead of a dead link - see redirectToWaiver's own fallback.
        $operatorId = $dive->operatorId ?: 0;

        return [
            'contentSid' => $contentSid,
            'variables' => [
                '1' => $user->name,
                '2' => $site,
                '3' => $operatorName,
                '4' => $dateFormatted,
                '5' => $timeFormatted,
                '6' => $dive->group->slug,
                '7' => (string) $operatorId,
            ],
        ];
    }
}

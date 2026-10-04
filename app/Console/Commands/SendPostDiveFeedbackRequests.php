<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\SendsViaPreferredChannel;
use App\Models\Event;
use App\Models\Operator;
use App\Models\PostDiveFeedbackRequest;
use App\Models\Trip;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * "How was your dive?" - fired once per booked, past dive. Triggered twice
 * daily (3pm and 6:30pm, via two Azure Logic App recurrences hitting
 * CronController::sendPostDiveFeedbackRequests) rather than scheduled off
 * an explicit morning/afternoon label: this command only ever acts on an
 * event whose date+time has already passed, so the 3pm run naturally
 * catches morning dives and the 6:30pm run catches afternoon ones
 * (Pablo, 2026-10-04).
 */
class SendPostDiveFeedbackRequests extends Command
{
    use SendsViaPreferredChannel;

    protected $signature = 'dives:send-feedback-requests';

    protected $description = 'Sends a "how was your dive?" feedback-wizard link for every booked dive whose time has passed today';

    public function handle()
    {
        $events = Event::where('booked', true)
            ->whereNull('cancelled_at')
            ->whereDate('date', Carbon::today())
            ->get()
            ->filter(fn (Event $event) => $this->hasPassed($event))
            ->filter(fn (Event $event) => !PostDiveFeedbackRequest::where('event_id', $event->id)->exists());

        $sent = 0;
        $skippedNoChannel = 0;

        foreach ($events as $event) {
            $user = User::find($event->userId);
            if (!$user) {
                continue;
            }

            $trip = Trip::tripInEvent($event);
            $trip = $trip instanceof Trip ? $trip : null;
            $siteId = $this->firstSiteId($trip);
            $operator = Operator::find($event->operatorId);

            $token = $this->freshToken();
            $wizardUrl = route('DiveFeedback.show', ['token' => $token]);

            $channel = $this->sendViaPreferredChannel(
                $user,
                $this->whatsappPayload($user, $event, $trip, $operator, $wizardUrl),
                $this->smsBody($event, $operator, $wizardUrl),
                $this->emailPayload($event, $trip, $siteId, $operator, $wizardUrl)
            );

            if (!$channel) {
                $skippedNoChannel++;
                continue;
            }

            PostDiveFeedbackRequest::create([
                'event_id' => $event->id,
                'user_id' => $user->id,
                'trip_id' => $trip->id ?? null,
                'site_id' => $siteId,
                'operator_id' => $event->operatorId,
                'token' => $token,
                'channel' => $channel,
                'sent_at' => now(),
                'expires_at' => now()->addDays(30),
            ]);
            $sent++;
        }

        $this->info("Checked {$events->count()} passed/booked dive(s): sent {$sent}, skipped {$skippedNoChannel} with no available channel.");

        return self::SUCCESS;
    }

    private function hasPassed(Event $event): bool
    {
        $dateTime = Carbon::parse(Carbon::parse($event->date)->format('Y-m-d') . ' ' . ($event->time ?: '00:00'));

        return $dateTime->isPast();
    }

    /** Trip->siteId is a comma-separated list; the "primary site" is the first one, same idiom used throughout this app. */
    private function firstSiteId(?Trip $trip): ?int
    {
        if (!$trip || !$trip->siteId) {
            return null;
        }
        $ids = explode(',', $trip->siteId);

        return $ids ? (int) trim($ids[0]) : null;
    }

    private function freshToken(): string
    {
        do {
            $token = Str::random(64);
        } while (PostDiveFeedbackRequest::where('token', $token)->exists());

        return $token;
    }

    private function whatsappPayload(User $user, Event $event, ?Trip $trip, ?Operator $operator, string $wizardUrl): ?array
    {
        $contentSid = config('services.whatsapp.post_dive_feedback_content_sid');
        if (!$contentSid) {
            return null;
        }

        return [
            'contentSid' => $contentSid,
            'variables' => [
                '1' => $user->name,
                '2' => $trip->tripName ?? $event->tripName,
                '3' => $operator->operatorName ?? 'Divers Hub',
                '4' => $wizardUrl,
            ],
        ];
    }

    private function smsBody(Event $event, ?Operator $operator, string $wizardUrl): string
    {
        $tripName = $event->tripName ?: 'your dive';

        return 'Divers Hub: how was ' . $tripName . ($operator ? ' with ' . $operator->operatorName : '') . '? '
            . $wizardUrl . ' Reply STOP to unsubscribe.';
    }

    private function emailPayload(Event $event, ?Trip $trip, ?int $siteId, ?Operator $operator, string $wizardUrl): array
    {
        $tripName = $trip->tripName ?? $event->tripName;
        $dateFormatted = Carbon::parse($event->date)->format('l, F j');
        $siteName = $siteId ? (\App\Models\Site::find($siteId)->name ?? null) : null;

        $html = view('emails.post-dive-feedback-document', [
            'preheader' => 'Quick conditions report, a photo or two, and a rating for ' . $tripName . '.',
            'tripName' => $tripName,
            'dateFormatted' => $dateFormatted,
            'siteName' => $siteName,
            'operatorName' => $operator->operatorName ?? null,
            'wizardUrl' => $wizardUrl,
        ])->render();

        return [
            'subject' => 'How was your dive - ' . $tripName . '?',
            'html' => $html,
        ];
    }
}

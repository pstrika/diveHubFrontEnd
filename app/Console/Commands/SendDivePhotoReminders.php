<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\SendsViaPreferredChannel;
use App\Models\Event;
use App\Models\Operator;
use App\Models\PostDiveFeedbackRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * One follow-up nudge, 2 days after the original feedback request, only for
 * divers who picked "remind me later" on the photo step and still haven't
 * uploaded anything (Pablo, 2026-10-04).
 */
class SendDivePhotoReminders extends Command
{
    use SendsViaPreferredChannel;

    protected $signature = 'dives:send-photo-reminders';

    protected $description = 'Sends one follow-up reminder to upload dive photos, 2 days after the original feedback request, if not already done';

    public function handle()
    {
        $requests = PostDiveFeedbackRequest::where('photo_reminder_requested', true)
            ->whereNull('photo_reminder_sent_at')
            ->whereNull('photos_uploaded_at')
            ->where('sent_at', '<=', now()->subDays(2))
            ->get();

        $sent = 0;

        foreach ($requests as $request) {
            $user = User::find($request->user_id);
            if (!$user) {
                continue;
            }

            $operator = $request->operator_id ? Operator::find($request->operator_id) : null;
            $event = Event::find($request->event_id);
            $wizardUrl = route('DiveFeedback.show', ['token' => $request->token]) . '#photos';

            $channel = $this->sendViaPreferredChannel(
                $user,
                $this->whatsappPayload($user, $event, $operator, $request->token),
                $this->smsBody($operator, $wizardUrl),
                $this->emailPayload($event, $operator, $wizardUrl)
            );

            // Mark attempted either way - this is a one-shot nudge, not
            // something to retry daily if every channel happens to fail.
            $request->photo_reminder_sent_at = now();
            $request->save();

            if ($channel) {
                $sent++;
            }
        }

        $this->info("Checked {$requests->count()} pending photo reminder(s): sent {$sent}.");

        return self::SUCCESS;
    }

    /**
     * Reuses the same approved template as the initial ask (same {{1}}
     * name/{{2}} operator/{{3}} date/{{4}} token contract - see
     * SendPostDiveFeedbackRequests::whatsappPayload()) rather than a
     * dedicated photo-reminder template, which would need its own separate
     * Meta review. The copy ("we hope you enjoyed... let us know how did
     * it go") reads a little generic for a photos-specific nudge, but it's
     * a one-shot follow-up, not worth a second template submission for
     * (Pablo, 2026-10-04) - revisit if this ever needs its own wording.
     */
    private function whatsappPayload(User $user, ?Event $event, ?Operator $operator, string $token): ?array
    {
        $contentSid = config('services.whatsapp.post_dive_feedback_content_sid');
        if (!$contentSid) {
            return null;
        }

        return [
            'contentSid' => $contentSid,
            'variables' => [
                '1' => $user->name,
                '2' => $operator->operatorName ?? 'Divers Hub',
                '3' => $event ? Carbon::parse($event->date)->format('l, F j') : 'your recent dive',
                '4' => $token,
            ],
        ];
    }

    private function smsBody(?Operator $operator, string $wizardUrl): string
    {
        return 'Divers Hub: still have photos from your dive' . ($operator ? ' with ' . $operator->operatorName : '') . '? '
            . 'Add them here: ' . $wizardUrl . ' Reply STOP to unsubscribe.';
    }

    private function emailPayload(?Event $event, ?Operator $operator, string $wizardUrl): array
    {
        $html = view('emails.post-dive-feedback-document', [
            'preheader' => 'Got a minute to add your dive photos?',
            'tripName' => $event->tripName ?? 'your recent dive',
            'dateFormatted' => $event ? Carbon::parse($event->date)->format('l, F j') : Carbon::now()->subDays(2)->format('l, F j'),
            'siteName' => null,
            'operatorName' => $operator->operatorName ?? null,
            'wizardUrl' => $wizardUrl,
        ])->render();

        return [
            'subject' => 'Still have photos from your dive?',
            'html' => $html,
        ];
    }
}

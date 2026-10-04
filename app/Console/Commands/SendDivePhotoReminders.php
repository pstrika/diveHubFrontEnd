<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\SendsViaPreferredChannel;
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
            $wizardUrl = route('DiveFeedback.show', ['token' => $request->token]) . '#photos';

            $channel = $this->sendViaPreferredChannel(
                $user,
                $this->whatsappPayload($user, $wizardUrl),
                $this->smsBody($operator, $wizardUrl),
                $this->emailPayload($user, $operator, $wizardUrl)
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

    private function whatsappPayload(User $user, string $wizardUrl): ?array
    {
        $contentSid = config('services.whatsapp.post_dive_feedback_content_sid');
        if (!$contentSid) {
            return null;
        }

        return [
            'contentSid' => $contentSid,
            'variables' => [
                '1' => $user->name,
                '2' => 'your recent dive',
                '3' => 'Divers Hub',
                '4' => $wizardUrl,
            ],
        ];
    }

    private function smsBody(?Operator $operator, string $wizardUrl): string
    {
        return 'Divers Hub: still have photos from your dive' . ($operator ? ' with ' . $operator->operatorName : '') . '? '
            . 'Add them here: ' . $wizardUrl . ' Reply STOP to unsubscribe.';
    }

    private function emailPayload(User $user, ?Operator $operator, string $wizardUrl): array
    {
        $html = view('emails.post-dive-feedback-document', [
            'preheader' => 'Got a minute to add your dive photos?',
            'tripName' => 'your recent dive',
            'dateFormatted' => Carbon::now()->subDays(2)->format('l, F j'),
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

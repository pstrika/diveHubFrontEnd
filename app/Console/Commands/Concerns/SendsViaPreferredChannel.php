<?php

namespace App\Console\Commands\Concerns;

use App\Models\User;
use App\Services\SmsService;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;
use Mailgun\Mailgun;

/**
 * Shared by SendPostDiveFeedbackRequests and SendDivePhotoReminders: try
 * WhatsApp, then SMS, then email, in that order, moving to the next
 * candidate not just when the user hasn't opted in but also when the send
 * itself fails - the latter matters while the WhatsApp template is still
 * pending Meta approval, so SMS/email cover it in the meantime (Pablo,
 * 2026-10-04: "The preference is to do it through WhatsApp... then SMS and
 * then email in that order. If they have suppressed all communication, we
 * just don't ask."). Returns the channel actually used, or null if none
 * worked/were eligible.
 */
trait SendsViaPreferredChannel
{
    protected function sendViaPreferredChannel(
        User $user,
        ?array $whatsapp, // ['contentSid' => string, 'variables' => array]
        ?string $smsBody,
        ?array $email // ['subject' => string, 'html' => string]
    ): ?string {
        if ($whatsapp && $user->phone && $user->whatsapp_notifications) {
            if (WhatsAppService::sendTemplate($user->phone, $whatsapp['contentSid'], $whatsapp['variables'] ?? [])) {
                return 'whatsapp';
            }
        }

        if ($smsBody && $user->phone && $user->sms_notifications) {
            if (SmsService::send($user->phone, $smsBody)) {
                return 'sms';
            }
        }

        if ($email && $user->email && $user->email_notifications) {
            if ($this->sendBrandedEmail($user, $email['subject'], $email['html'])) {
                return 'email';
            }
        }

        return null;
    }

    private function sendBrandedEmail(User $user, string $subject, string $html): bool
    {
        try {
            $mg = Mailgun::create(env('MAILGUN_KEY'));

            $mg->messages()->send('mail.divers-hub.com', [
                'from' => 'Divers-Hub <postmaster@mail.divers-hub.com>',
                'to' => $user->name . ' <' . $user->email . '>',
                'subject' => $subject,
                'template' => '2026 new dh template',
                'h:X-Mailgun-Variables' => json_encode(['body' => $html]),
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to send branded email to ' . $user->email . ': ' . $e->getMessage());
            return false;
        }
    }
}

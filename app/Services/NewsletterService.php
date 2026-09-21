<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Mailgun\Mailgun;

/**
 * Shared render+send logic for both the weekly digest
 * (Console\Commands\SendNewsletter, run from the cron path) and the admin
 * web composer (Http\Controllers\NewsletterAdminController, for ad hoc
 * platform-wide sends) - one place that actually talks to Mailgun, so
 * both paths behave identically and a fix to one fixes both.
 */
class NewsletterService
{
    /** Everyone eligible for a newsletter send - subscribed AND hasn't turned off email entirely. */
    public static function subscribedRecipients(?array $onlyIds = null)
    {
        return User::where('newsletter_subscribed', true)
            ->where('email_notifications', true)
            ->whereNotNull('email')
            ->when($onlyIds, fn ($q, $ids) => $q->whereIn('id', $ids))
            ->get();
    }

    /**
     * Renders one recipient's copy (their own signed unsubscribe link) and
     * sends it. Returns true/false rather than throwing - one bad address
     * must not stop the rest of a run.
     *
     * $content: ['preheader', 'date', 'headline', 'body', 'conditions'] -
     * body/conditions are raw HTML (see App\Support\NewsletterMarkdown).
     */
    public static function sendToUser(User $user, string $subject, array $content): bool
    {
        $document = view('emails.newsletter-document', array_merge($content, [
            'unsubscribeUrl' => URL::signedRoute('Newsletter.unsubscribe', ['user' => $user->id]),
        ]))->render();

        try {
            Mailgun::create(env('MAILGUN_KEY'))->messages()->send('mail.divers-hub.com', [
                'from' => 'Divers-Hub <postmaster@mail.divers-hub.com>',
                'to' => $user->name . ' <' . $user->email . '>',
                'subject' => $subject,
                'template' => '2026 new dh template',
                'h:X-Mailgun-Variables' => json_encode(['body' => $document]),
            ]);
            return true;
        } catch (\Throwable $e) {
            Log::error('Newsletter send failed for user ' . $user->id . ': ' . $e->getMessage());
            return false;
        }
    }
}

<?php

namespace App\Services;

use App\Models\NewsletterIssue;
use App\Models\User;
use App\Support\NewsletterMarkdown;
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
     * sends it. Returns true on success or the failure reason as a string
     * (never throws - one bad address must not stop the rest of a run).
     * Mailgun's own exception message is often just "Too many requests."
     * regardless of the real reason (rate limit vs. daily quota vs.
     * something else) - the actual reason lives in the response body,
     * which the SDK's own exception doesn't expose, so this reads it
     * directly via a raw HTTP call instead of trusting $e->getMessage().
     *
     * $content: ['preheader', 'date', 'headline', 'body', 'conditions'] -
     * body/conditions are raw HTML (see App\Support\NewsletterMarkdown).
     */
    public static function sendToUser(User $user, string $subject, array $content)
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
            $reason = self::mailgunErrorReason($e) ?? $e->getMessage();
            Log::error('Newsletter send failed for user ' . $user->id . ': ' . $reason);
            return $reason;
        }
    }

    /**
     * The real reason, when Mailgun's SDK gives one - e.g. tooManyRequests()
     * always sets the exception's own message to the generic "Too many
     * requests.", but its constructor separately parses the actual response
     * body (e.g. "daily request limit (100) exceeded...") into
     * getResponseBody() before anything else can consume that stream.
     */
    private static function mailgunErrorReason(\Throwable $e): ?string
    {
        if (!method_exists($e, 'getResponseBody')) {
            return null;
        }

        return $e->getResponseBody()['message'] ?? null;
    }

    /** The $content array sendToUser() expects, built from a saved issue. */
    public static function contentFor(NewsletterIssue $issue): array
    {
        return [
            'preheader' => $issue->preheader ?: $issue->headline,
            'date' => now()->format('F j, Y'),
            'headline' => $issue->headline,
            'body' => NewsletterMarkdown::toHtml($issue->body_markdown),
            'conditions' => $issue->conditions ?: '',
        ];
    }

    /**
     * The real, platform-wide send - used by both the admin's "Send to all
     * subscribers" button and the scheduled-send cron path
     * (Console\Commands\SendScheduledNewsletters), so a fix to one fixes
     * both. Synchronous - there is no queue worker in this app (every
     * other bulk send is CLI/cron-driven, not a web request). Fine at
     * today's subscriber count; revisit if that changes.
     */
    public static function sendIssueToAllSubscribers(NewsletterIssue $issue): int
    {
        set_time_limit(300);

        $recipients = self::subscribedRecipients();
        $content = self::contentFor($issue);
        // sendToUser() returns true on success or the failure reason as a
        // string - a non-empty string is truthy in PHP, so this must check
        // === true explicitly or every failure would count as a success.
        $sent = $recipients->filter(fn ($user) => self::sendToUser($user, $issue->subject, $content) === true)->count();

        $issue->update([
            'status' => 'sent',
            'sent_at' => now(),
            'sent_count' => $sent,
            'scheduled_at' => null,
        ]);

        return $sent;
    }
}

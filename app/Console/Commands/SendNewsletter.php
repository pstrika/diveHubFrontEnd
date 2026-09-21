<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Mailgun\Mailgun;

/**
 * Renders resources/views/emails/newsletter-document.blade.php per
 * recipient (the unsubscribe link is signed per-user) into one complete
 * HTML email, then sends it through Mailgun's "2026 new dh template"
 * template, which holds nothing but a single {{{body}}} placeholder - see
 * docs/newsletter-mailgun-shell.html. Recipients come straight from
 * users.newsletter_subscribed, not a Mailgun-side mailing list (Pablo,
 * 2026-09-21: "so we manage the recipients").
 */
class SendNewsletter extends Command
{
    protected $signature = 'newsletter:send
        {--dry-run : List who would receive it without sending}
        {--only= : Comma-separated user ids, for testing a single recipient}
        {--subject= : Email subject line}
        {--preheader= : Inbox preview text}
        {--date= : Shown under the eyebrow, e.g. "September 21, 2026" (defaults to today)}
        {--headline= : The hero H1}
        {--body= : Path to an HTML file with the issue body}
        {--conditions= : The "this weekend on the water" line}';

    protected $description = 'Send the weekly newsletter to every subscribed user';

    public function handle()
    {
        $bodyPath = $this->option('body');
        if (!$bodyPath || !file_exists($bodyPath)) {
            $this->error('Pass --body=<path to an HTML file> with the issue content.');
            return self::FAILURE;
        }

        $subject = $this->option('subject') ?: 'The Weekly Dive';
        $date = $this->option('date') ?: now()->format('F j, Y');
        $headline = $this->option('headline') ?: $subject;
        $preheader = $this->option('preheader') ?: $headline;
        $body = file_get_contents($bodyPath);
        $conditions = $this->option('conditions') ?: '';

        $recipients = User::where('newsletter_subscribed', true)
            ->where('email_notifications', true)
            ->whereNotNull('email')
            ->when($this->option('only'), fn ($q, $only) => $q->whereIn('id', explode(',', $only)))
            ->get();

        $this->info('Recipients: ' . $recipients->count());

        if ($this->option('dry-run')) {
            $recipients->each(fn ($u) => $this->line(" - {$u->name} <{$u->email}>"));
            return self::SUCCESS;
        }

        $mg = Mailgun::create(env('MAILGUN_KEY'));
        $sent = 0;

        foreach ($recipients as $user) {
            $document = view('emails.newsletter-document', [
                'preheader' => $preheader,
                'date' => $date,
                'headline' => $headline,
                'body' => $body,
                'conditions' => $conditions,
                'unsubscribeUrl' => URL::signedRoute('Newsletter.unsubscribe', ['user' => $user->id]),
            ])->render();

            try {
                $mg->messages()->send('mail.divers-hub.com', [
                    'from' => 'Divers-Hub <postmaster@mail.divers-hub.com>',
                    'to' => $user->name . ' <' . $user->email . '>',
                    'subject' => $subject,
                    'template' => '2026 new dh template',
                    'h:X-Mailgun-Variables' => json_encode(['body' => $document]),
                ]);
                $sent++;
            } catch (\Throwable $e) {
                Log::error('Newsletter send failed for user ' . $user->id . ': ' . $e->getMessage());
            }
        }

        $this->info("Sent: {$sent} / {$recipients->count()}");
        return self::SUCCESS;
    }
}

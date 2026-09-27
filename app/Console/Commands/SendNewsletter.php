<?php

namespace App\Console\Commands;

use App\Services\NewsletterService;
use Illuminate\Console\Command;

/**
 * The weekly digest path - reads its content from CLI flags/files. For an
 * ad hoc, platform-wide send authored through a form instead, see
 * NewsletterAdminController, which uses the same NewsletterService this
 * command does.
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

        $onlyIds = $this->option('only') ? explode(',', $this->option('only')) : null;
        $recipients = NewsletterService::subscribedRecipients($onlyIds);

        $this->info('Recipients: ' . $recipients->count());

        if ($this->option('dry-run')) {
            $recipients->each(fn ($u) => $this->line(" - {$u->name} <{$u->email}>"));
            return self::SUCCESS;
        }

        $content = compact('preheader', 'date', 'headline', 'body', 'conditions');
        // sendToUser() returns true on success or the failure reason as a
        // string - a non-empty string is truthy in PHP, so this must check
        // === true explicitly or every failure would count as a success.
        $sent = $recipients->filter(fn ($user) => NewsletterService::sendToUser($user, $subject, $content) === true)->count();

        $this->info("Sent: {$sent} / {$recipients->count()}");
        return self::SUCCESS;
    }
}

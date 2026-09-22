<?php

namespace App\Console\Commands;

use App\Models\NewsletterIssue;
use App\Services\NewsletterService;
use Illuminate\Console\Command;

/**
 * Triggered every 15 minutes via cron/send-scheduled-newsletters
 * (CronController) - see .github/workflows/send-scheduled-newsletters.yml.
 * Finds drafts whose scheduled_at has arrived and sends them through the
 * same path the admin's "Send to all subscribers" button uses.
 */
class SendScheduledNewsletters extends Command
{
    protected $signature = 'newsletter:send-scheduled {--dry-run : List what would be sent without sending it}';

    protected $description = 'Send any newsletter issue whose scheduled time has arrived';

    public function handle()
    {
        $due = NewsletterIssue::where('status', 'draft')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->get();

        if ($due->isEmpty()) {
            $this->info('Nothing due.');
            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $due->each(fn ($i) => $this->line(" - #{$i->id} \"{$i->subject}\" (scheduled for {$i->scheduled_at})"));
            return self::SUCCESS;
        }

        foreach ($due as $issue) {
            $sent = NewsletterService::sendIssueToAllSubscribers($issue);
            $this->info("Sent issue #{$issue->id} ({$issue->subject}) to {$sent} subscribers.");
        }

        return self::SUCCESS;
    }
}

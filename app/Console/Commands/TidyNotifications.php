<?php

namespace App\Console\Commands;

use App\Models\Message;
use Illuminate\Console\Command;

/**
 * One-off cleanup to go with the 2026-11-12 notification changes (Zach).
 *
 * It only REPORTS unless --force is passed. Nothing here runs by itself:
 * it is not part of the deploy and has no cron route.
 *
 * 1. Sorts old admin notices into the Admin folder. messages.kind only
 *    started being set on 2026-11-12, so everything an admin was sent
 *    before that still sits in their Inbox and still counts on their bell.
 *    Matched on the exact subject each call site uses, with no group and
 *    no kind, so a notice to an article's author ("Your article is
 *    published", "Your article needs changes") is never touched.
 *
 * 2. Only with --mark-read: marks everything unread so far as read, for
 *    every user, so the badges start from zero and mean something from
 *    here on. Before these changes nothing was marked read unless it was
 *    opened one at a time, so most accounts carry a backlog nobody will
 *    ever open. It changes the read flag only; nothing is deleted.
 *    NOT YET CHECKED: whether the external email job (emailQueueFlush)
 *    looks at the read flag. Confirm that before using --mark-read on
 *    production.
 *
 * Safe to run twice.
 *
 *   php artisan notifications:tidy                      report only
 *   php artisan notifications:tidy --force              file admin notices
 *   php artisan notifications:tidy --mark-read --force  and clear the backlog
 */
class TidyNotifications extends Command
{
    protected $signature = 'notifications:tidy
        {--mark-read : Also mark every notification that is unread right now as read, for every user}
        {--force : Write the changes. Without it this only reports what it would do}';

    protected $description = 'Moves old admin notices to the Admin folder and, with --mark-read, clears the unread backlog. Reports only unless --force';

    /** Exactly what the admin call sites write as the subject. */
    public const ADMIN_SUBJECTS = [
        'New diver picture pending review',   // DiverPhotoController::notifyAdmins
        'New diver video pending review',     // SiteVideoController
        'Article waiting for review',         // BlogAdminController, Api\BlogArticleController
        'Published article edited',           // BlogAdminController::notifyAdmins
        'New sms message',                    // ConversationLog::notifyAdmins
        'New whatsapp message',
        'New email message',
    ];

    public function handle()
    {
        $write = (bool) $this->option('force');
        $tag = $write ? '' : '[report only] ';

        $admin = Message::whereNull('kind')->whereNull('group_id')->whereIn('subject', self::ADMIN_SUBJECTS);
        foreach ((clone $admin)->selectRaw('subject, count(*) as n')->groupBy('subject')->pluck('n', 'subject') as $subject => $n) {
            $this->line("  {$subject}: {$n}");
        }
        $adminCount = (clone $admin)->count();
        if ($write && $adminCount > 0) {
            $admin->update(['kind' => Message::KIND_ADMIN]);
        }
        $this->info("{$tag}{$adminCount} old admin notice(s) " . ($write ? 'moved' : 'would move') . ' to the Admin folder.');

        if ($this->option('mark-read')) {
            // A fixed cutoff, so a notification written while this runs is
            // not swallowed.
            $cutoff = now();
            $backlog = Message::where('read', 0)->where('created_at', '<=', $cutoff);
            $n = (clone $backlog)->count();
            $people = (clone $backlog)->distinct()->count('userId');
            if ($write && $n > 0) {
                $backlog->update(['read' => 1]);
            }
            $this->info("{$tag}{$n} unread notification(s) across {$people} account(s) " . ($write ? 'marked read.' : 'would be marked read.'));
        } else {
            $this->line('Unread backlog left alone (pass --mark-read to include it).');
        }

        if (!$write) {
            $this->warn('Nothing was written. Add --force to apply.');
        }

        return self::SUCCESS;
    }
}

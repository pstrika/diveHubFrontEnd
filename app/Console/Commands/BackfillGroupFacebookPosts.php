<?php

namespace App\Console\Commands;

use App\Models\Group;
use App\Models\GroupDive;
use App\Services\GroupDiveService;
use Illuminate\Console\Command;

/**
 * Posts every not-yet-posted dive on a group's calendar to its connected
 * Facebook Page - for a group that connects Facebook AFTER already having
 * dives on the calendar (Pablo, 2026-10-05: just connected Lionfish
 * Hunters and wants its existing dives posted). Unlike
 * GroupDiveService::createFromTrip()'s normal path, this deliberately
 * skips notifyNewDive() - the dives aren't new, only the Facebook post is,
 * so members shouldn't get a wave of "New dive added" notifications for
 * things already on their calendar. One-off/manual-trigger, not wired to
 * any cron - --group is required so running it never silently fires for
 * every Facebook-connected group in the system.
 */
class BackfillGroupFacebookPosts extends Command
{
    protected $signature = 'groups:backfill-facebook-posts {--group= : Group slug}';

    protected $description = 'Posts every not-yet-posted dive on one group\'s calendar to its connected Facebook Page';

    public function handle()
    {
        $slug = $this->option('group');
        if (!$slug) {
            $this->error('--group=<slug> is required.');
            return self::FAILURE;
        }

        $group = Group::where('slug', $slug)->first();
        if (!$group) {
            $this->error("No group with slug \"{$slug}\".");
            return self::FAILURE;
        }
        if (!$group->isFacebookConnected() || !$group->fb_auto_post) {
            $this->error("\"{$group->name}\" isn't connected to Facebook (or auto-post is off).");
            return self::FAILURE;
        }

        $dives = GroupDive::where('group_id', $group->id)->whereNull('fb_post_id')->orderBy('date')->get();

        $diveService = new GroupDiveService();
        $posted = 0;

        foreach ($dives as $dive) {
            $diveService->postDiveToFacebook($group, $dive);
            if ($dive->fresh()->fb_post_id) {
                $posted++;
                $this->info("Posted: {$dive->tripName} ({$dive->date})");
            } else {
                $this->warn("Failed (see log): {$dive->tripName} ({$dive->date})");
            }
            // Light pacing between Graph API calls - single-dive posts
            // never needed this, a batch of them should still be polite.
            if ($dive !== $dives->last()) {
                sleep(1);
            }
        }

        $this->info("Done: {$posted} of {$dives->count()} posted to \"{$group->fb_page_name}\".");

        return self::SUCCESS;
    }
}

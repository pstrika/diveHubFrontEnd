<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class CronController extends Controller
{
    /**
     * HTTP-triggered cron endpoint. Kudu's command executor on this App
     * Service doesn't have a `php` binary available (it runs in a separate,
     * minimal deployment container from the actual PHP-FPM runtime), so
     * scheduled artisan commands are triggered via a plain HTTP request
     * instead - this runs inside the real app, where `php` obviously exists.
     */
    public function sendGroupReminders(Request $request)
    {
        if (!hash_equals((string) env('CRON_SECRET'), (string) $request->query('secret'))) {
            abort(403);
        }

        Artisan::call('groups:send-reminders');

        return response(Artisan::output(), 200, ['Content-Type' => 'text/plain']);
    }

    public function sendGroupActivityDigest(Request $request)
    {
        if (!hash_equals((string) env('CRON_SECRET'), (string) $request->query('secret'))) {
            abort(403);
        }

        Artisan::call('groups:send-activity-digest');

        return response(Artisan::output(), 200, ['Content-Type' => 'text/plain']);
    }

    /**
     * Hourly, triggered by an Azure Logic App (Recurrence + HTTP action)
     * in the same resource group as this App Service - kept out of GitHub
     * Actions deliberately, so the trigger doesn't depend on a separate
     * platform. The 2-consecutive-misses rule in the command means calling
     * this more often than hourly can't cancel anything early - the second
     * miss is ignored unless it's at least ~an hour after the first.
     */
    public function detectCancelledTrips(Request $request)
    {
        if (!hash_equals((string) env('CRON_SECRET'), (string) $request->query('secret'))) {
            abort(403);
        }

        Artisan::call('trips:detect-cancelled');

        return response(Artisan::output(), 200, ['Content-Type' => 'text/plain']);
    }

    /**
     * Every 15 minutes, triggered by an Azure Logic App
     * (divehub-send-scheduled-newsletters) - moved off GitHub Actions
     * 2026-09-22 after its `schedule` trigger proved unreliable at this
     * frequency (fired twice in 17 hours) and silently missed a real
     * scheduled send.
     */
    public function sendScheduledNewsletters(Request $request)
    {
        if (!hash_equals((string) env('CRON_SECRET'), (string) $request->query('secret'))) {
            abort(403);
        }

        Artisan::call('newsletter:send-scheduled');

        return response(Artisan::output(), 200, ['Content-Type' => 'text/plain']);
    }

    /**
     * Every 5 minutes, triggered by an Azure Logic App
     * (divehub-sync-support-inbox) - pulls new mail from the real
     * support@divers-hub.com mailbox into the admin Message Management
     * console (Pablo, 2026-09-22). See SyncSupportInbox / GraphMailService.
     */
    public function syncSupportInbox(Request $request)
    {
        if (!hash_equals((string) env('CRON_SECRET'), (string) $request->query('secret'))) {
            abort(403);
        }

        Artisan::call('support:sync-inbox');

        return response(Artisan::output(), 200, ['Content-Type' => 'text/plain']);
    }

    /**
     * Every 30 minutes, triggered by an Azure Logic App
     * (divehub-apply-group-auto-add-rules) - trips are scraped continuously
     * throughout the day by a process outside this repo, so this is a
     * periodic scan rather than a live hook (Pablo, 2026-09-22). See
     * ApplyGroupAutoAddRules / GroupAutoAddRule::matches().
     */
    public function applyGroupAutoAddRules(Request $request)
    {
        if (!hash_equals((string) env('CRON_SECRET'), (string) $request->query('secret'))) {
            abort(403);
        }

        Artisan::call('groups:apply-auto-rules');

        return response(Artisan::output(), 200, ['Content-Type' => 'text/plain']);
    }

    /**
     * Manual-trigger only (Pablo, 2026-09-24) - not on a recurring Logic
     * App schedule like the others in this file. photos:web-copies
     * (App\Console\Commands\MakeSitePhotoCopies) scans the whole
     * public/assets/img/sites directory for any original missing its WebP
     * copies - both admin-uploaded (Photo) and diver-uploaded (DiverPhoto)
     * files land in that same directory, so this backfills either kind.
     * New uploads already make their own copies at upload time; this is
     * for the pre-2026-09 backlog, or the window before 2026-09-24 when
     * this server's GD build turned out to have no WebP support at all
     * (found chasing "the image is kept at full size" on a real diver
     * upload) - SitePhoto now prefers Imagick, which does support WEBP
     * here, but anything uploaded before that fix stayed original-only
     * until backfilled. Has to actually run on this server: the resize reads/writes
     * public/assets/img/sites, not the shared database, so it can't be
     * done from a local artisan session like every other admin task this
     * session has run directly against the shared DB.
     */
    public function photosWebCopies(Request $request)
    {
        if (!hash_equals((string) env('CRON_SECRET'), (string) $request->query('secret'))) {
            abort(403);
        }

        Artisan::call('photos:web-copies', ['--force' => $request->boolean('force')]);

        return response(Artisan::output(), 200, ['Content-Type' => 'text/plain']);
    }

    /**
     * Manual-trigger only (Pablo, 2026-09-29) - same reasoning as
     * photosWebCopies above: needs to run on this server itself, since
     * IndexNow's submission has to be made from a request that resolves
     * route()/url() against the real production APP_URL, not a local
     * tinker session pointed at divers-hub.com's shared DB. See
     * IndexNowSubmitAll / App\Services\IndexNowService.
     */
    public function indexNowSubmitAll(Request $request)
    {
        if (!hash_equals((string) env('CRON_SECRET'), (string) $request->query('secret'))) {
            abort(403);
        }

        Artisan::call('indexnow:submit-all');

        return response(Artisan::output(), 200, ['Content-Type' => 'text/plain']);
    }

    /**
     * Twice daily (3pm and 6:30pm, two Azure Logic App recurrences -
     * divehub-send-post-dive-feedback-3pm / -630pm - both hitting this same
     * endpoint), covering morning and afternoon dives respectively without
     * the command itself needing to classify which is which - see
     * App\Console\Commands\SendPostDiveFeedbackRequests (Pablo, 2026-10-04).
     */
    public function sendPostDiveFeedbackRequests(Request $request)
    {
        if (!hash_equals((string) env('CRON_SECRET'), (string) $request->query('secret'))) {
            abort(403);
        }

        Artisan::call('dives:send-feedback-requests');

        return response(Artisan::output(), 200, ['Content-Type' => 'text/plain']);
    }

    /**
     * Daily, triggered by an Azure Logic App
     * (divehub-send-dive-photo-reminders) - see App\Console\Commands\
     * SendDivePhotoReminders (Pablo, 2026-10-04).
     */
    public function sendDivePhotoReminders(Request $request)
    {
        if (!hash_equals((string) env('CRON_SECRET'), (string) $request->query('secret'))) {
            abort(403);
        }

        Artisan::call('dives:send-photo-reminders');

        return response(Artisan::output(), 200, ['Content-Type' => 'text/plain']);
    }

    /**
     * Manual-trigger only (Pablo, 2026-10-05), same reasoning as
     * photosWebCopies/indexNowSubmitAll above: the image URLs this posts
     * to Facebook are built with asset()/url(), which resolve against
     * whatever APP_URL the request itself runs under - a local tinker
     * session would hand Facebook an unreachable http://localhost/... URL
     * (confirmed: that's exactly what happened on the first attempt, every
     * post rejected with Graph API error 324 "Missing or invalid image
     * file"). Requires ?group=<slug> - see App\Console\Commands\
     * BackfillGroupFacebookPosts for why there's no "run for every group"
     * default.
     */
    public function backfillGroupFacebookPosts(Request $request)
    {
        if (!hash_equals((string) env('CRON_SECRET'), (string) $request->query('secret'))) {
            abort(403);
        }

        Artisan::call('groups:backfill-facebook-posts', ['--group' => $request->query('group')]);

        return response(Artisan::output(), 200, ['Content-Type' => 'text/plain']);
    }

    /**
     * Rebuilds Laravel's config/route/view cache - the LAST step of every
     * production deploy (main_divehub.yml), not a manual/scheduled action
     * like the endpoints above. Exists because Kudu's command executor
     * (used by that workflow's "clear stale cache" steps right before
     * this) has no `php` binary - it runs in a separate, minimal
     * deployment container from the real PHP-FPM runtime - so rebuilding
     * actually requires a real HTTP hit to the running app, same as every
     * other Artisan-via-cron endpoint here.
     *
     * Found 2026-10-05 (Pablo: "the app is somewhat stalling and
     * lagging"): the deploy workflow was deleting bootstrap/cache/*.php
     * after every push (to keep config/divehub.php's version fresh) but
     * never rebuilding it - production was permanently running with ZERO
     * config/route/view caching, re-parsing everything on every single
     * request. That explained a measured ~300-700ms baseline TTFB even on
     * trivial pages. This closes the loop: clear, then rebuild fresh
     * against the code that was JUST deployed.
     */
    public function optimizeCache(Request $request)
    {
        if (!hash_equals((string) env('CRON_SECRET'), (string) $request->query('secret'))) {
            abort(403);
        }

        $output = '';
        foreach (['config:cache', 'route:cache', 'view:cache'] as $command) {
            Artisan::call($command);
            $output .= "\$ php artisan {$command}\n" . Artisan::output() . "\n";
        }

        return response($output, 200, ['Content-Type' => 'text/plain']);
    }

    /**
     * Every 15 minutes, triggered by an Azure Logic App
     * (divehub-sync-trip-sites-pivot) - rebuilds trip_sites, the
     * normalized pivot SiteController@show now queries instead of a
     * leading-wildcard LIKE against trips.siteId (Pablo, 2026-10-06). 15
     * minutes, not the 30 ApplyGroupAutoAddRules uses: this directly
     * backs a user-facing page's query, not a background matching job, so
     * it should lag the live scrape less.
     */
    public function syncTripSitesPivot(Request $request)
    {
        if (!hash_equals((string) env('CRON_SECRET'), (string) $request->query('secret'))) {
            abort(403);
        }

        Artisan::call('trips:sync-site-pivot');

        return response(Artisan::output(), 200, ['Content-Type' => 'text/plain']);
    }
}

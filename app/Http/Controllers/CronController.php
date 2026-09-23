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
     * only for a backlog photo, or one whose resize failed at upload time
     * (a diver's real 20MP/8MB JPEG turned out to need this - webhook
     * timing or a transient resource limit, not yet root-caused, but
     * running this again against a specific file is a one-line fix either
     * way). Has to actually run on this server: the resize reads/writes
     * public/assets/img/sites, not the shared database, so it can't be
     * done from a local artisan session like every other admin task this
     * session has run directly against the shared DB.
     */
    /**
     * Temporary diagnostic (Pablo, 2026-09-24) - photosWebCopies() below
     * reported "PHP GD with WebP support is required" on this server, so
     * checking whether Imagick is a usable fallback before deciding how to
     * fix SitePhoto. Remove once that's settled.
     */
    public function photoEnvDiag(Request $request)
    {
        if (!hash_equals((string) env('CRON_SECRET'), (string) $request->query('secret'))) {
            abort(403);
        }

        $lines = [];
        $lines[] = 'PHP version: ' . PHP_VERSION;
        $lines[] = 'GD loaded: ' . (extension_loaded('gd') ? 'yes' : 'no');
        $lines[] = 'GD info: ' . json_encode(function_exists('gd_info') ? gd_info() : null);
        $lines[] = 'Imagick loaded: ' . (extension_loaded('imagick') ? 'yes' : 'no');
        if (extension_loaded('imagick')) {
            $lines[] = 'Imagick version: ' . json_encode(\Imagick::getVersion());
            $formats = (new \Imagick())->queryFormats('WEBP*');
            $lines[] = 'Imagick WEBP formats: ' . json_encode($formats);
        }

        return response(implode("\n", $lines), 200, ['Content-Type' => 'text/plain']);
    }

    public function photosWebCopies(Request $request)
    {
        if (!hash_equals((string) env('CRON_SECRET'), (string) $request->query('secret'))) {
            abort(403);
        }

        Artisan::call('photos:web-copies', ['--force' => $request->boolean('force')]);

        return response(Artisan::output(), 200, ['Content-Type' => 'text/plain']);
    }
}

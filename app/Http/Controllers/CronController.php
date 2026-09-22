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
}

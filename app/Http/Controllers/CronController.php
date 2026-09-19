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
     * Hourly (see .github/workflows/detect-cancelled-trips.yml). The
     * 2-consecutive-misses rule in the command means calling this more
     * often than hourly can't cancel anything early - the second miss is
     * ignored unless it's at least ~an hour after the first.
     */
    public function detectCancelledTrips(Request $request)
    {
        if (!hash_equals((string) env('CRON_SECRET'), (string) $request->query('secret'))) {
            abort(403);
        }

        Artisan::call('trips:detect-cancelled');

        return response(Artisan::output(), 200, ['Content-Type' => 'text/plain']);
    }
}

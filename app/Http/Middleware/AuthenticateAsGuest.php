<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuthenticateAsGuest
{
    /** The shared guest account every signed-out visitor is logged in as. */
    public const GUEST_USER_ID = 5;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            Auth::loginUsingId(self::GUEST_USER_ID);
            // Every signed-out visit (crawlers, mostly) opens a guest session,
            // and production keeps sessions 30 days (SESSION_LIFETIME) - 194k
            // rows / 296 MB in two days (2026-10-17). Guest sessions hold
            // nothing worth keeping, so about one guest visit in 500 drops
            // those idle for a day. Members' sessions are untouched.
            if (random_int(1, 500) === 1) {
                self::pruneGuestSessions();
            }
        }
        return $next($request);
    }

    public static function pruneGuestSessions(): int
    {
        if (config('session.driver') !== 'database') return 0;
        try {
            return DB::delete(
                'DELETE FROM ' . config('session.table', 'sessions') . ' WHERE user_id = ? AND last_activity < ? LIMIT 20000',
                [self::GUEST_USER_ID, time() - 86400]
            );
        } catch (\Throwable $e) {
            Log::warning('Guest session prune failed: ' . $e->getMessage());
            return 0;
        }
    }
}

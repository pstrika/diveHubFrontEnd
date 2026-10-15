<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Early-access gate for the diver logbook (Pablo, 2026-10-15: "make it
 * only available for me - nobody else should have access to this feature
 * for now"). Allowed emails live in config/divehub.php so widening the
 * list is a config change, not a code change. Runs after auth, so a
 * missing user means the auth middleware wasn't applied - fail closed.
 * Shared by the web pages (session) and the app API (Sanctum): both put
 * the user on the request.
 */
class LogbookGate
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $allowed = array_map('strtolower', config('divehub.logbook_allowed_emails', []));

        if (!$user || !in_array(strtolower((string) $user->email), $allowed, true)) {
            abort(403, 'The logbook is not available on your account yet.');
        }

        return $next($request);
    }
}

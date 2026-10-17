<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Who can use the diver logbook (web pages + app API). Was an early-access
 * email list, Pablo only (2026-10-15); opened to every registered member
 * (Pablo, 2026-10-16: "allow any registered user with the log"). Guest
 * accounts stay out. Runs after auth, so a missing user means the auth
 * middleware wasn't applied - fail closed. Shared by the web pages
 * (session) and the app API (Sanctum): both put the user on the request.
 */
class LogbookGate
{
    /** The one check the gate, menu, profile and nav-bar picker all use. */
    public static function allows($user): bool
    {
        return $user !== null && $user->isNotGuest();
    }

    public function handle(Request $request, Closure $next)
    {
        if (!self::allows($request->user())) {
            abort(403, 'The logbook is not available on your account yet.');
        }

        return $next($request);
    }
}

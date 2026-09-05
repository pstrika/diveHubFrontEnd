<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureNotGuest
{
    /**
     * The 'guest' middleware auto-logs anonymous visitors in as a shared
     * account (id 5), so plain 'auth' alone does not exclude them. Groups
     * are a fully members-only feature, so every route needs this check.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->user() || !auth()->user()->isNotGuest()) {
            abort(403, 'Create an account to access this feature.');
        }

        return $next($request);
    }
}

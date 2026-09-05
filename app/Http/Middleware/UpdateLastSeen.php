<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class UpdateLastSeen
{
    /**
     * Lightweight heartbeat for the Groups "online" indicator. Throttled to
     * once a minute per user so it doesn't add a write to every request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            $user = auth()->user();
            if (!$user->last_seen_at || $user->last_seen_at->lt(now()->subMinute())) {
                $user->timestamps = false;
                $user->last_seen_at = now();
                $user->save();
            }
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use App\Support\EarlyAccess;
use Closure;
use Illuminate\Http\Request;

/** Route gate for early-access features: ->middleware('early:species'). */
class EarlyAccessGate
{
    public function handle(Request $request, Closure $next, string $feature)
    {
        abort_unless(EarlyAccess::allows($request->user(), $feature), 404);
        return $next($request);
    }
}

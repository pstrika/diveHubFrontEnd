<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnforceCanonicalHost
{
    /**
     * Redirect GET/HEAD requests that arrive on a non-canonical host (e.g. the
     * Azure default hostname) to the canonical APP_URL host, so Google doesn't
     * see the same content as duplicate content on two domains.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!app()->environment('production')) {
            return $next($request);
        }

        $canonicalHost = parse_url(config('app.url'), PHP_URL_HOST);

        if ($canonicalHost && $request->getHost() !== $canonicalHost && ($request->isMethod('GET') || $request->isMethod('HEAD'))) {
            return redirect()->to(
                'https://' . $canonicalHost . $request->getRequestUri(),
                301
            );
        }

        return $next($request);
    }
}

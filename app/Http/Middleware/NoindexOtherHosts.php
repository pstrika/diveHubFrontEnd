<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Any host other than divers-hub.com (the redesign staging slot on Azure, a
 * new slot, the raw Azure hostname) is a copy of the site, so it tells search
 * engines not to index it. EnforceCanonicalHost cannot do this on a slot whose
 * APP_URL is the slot's own address. A header, not robots.txt: robots.txt is a
 * static file and a Disallow would stop crawlers from ever seeing the noindex.
 */
class NoindexOtherHosts
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $host = strtolower($request->getHost());
        if ($host !== 'divers-hub.com' && !str_ends_with($host, '.divers-hub.com')) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        return $response;
    }
}

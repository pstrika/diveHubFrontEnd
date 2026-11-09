<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Crawlers get a quick 429 on /Trips filter combinations, which are endless and
 * each cost a full trip board. 2026-11-09: PerplexityBot (and Baidu, ElevenLabs)
 * sent about a thousand in two hours, tied up all the PHP workers and the site
 * stopped answering. robots.txt already asks them to stay out; this is for when
 * they don't listen. People and Googlebot's bare /Trips are untouched.
 */
class BlockBotTripFilters
{
    private const BOTS = '/bot|crawl|spider|slurp|scrape|fetch|python-requests|curl|wget|go-http|okhttp|httpclient/i';

    public function handle(Request $request, Closure $next)
    {
        if ($request->is('Trips') && $request->query() && preg_match(self::BOTS, (string) $request->userAgent())) {
            return response('Too many filter combinations for crawlers. Use /Trips or /DiveTrips.', 429, ['Retry-After' => '86400', 'X-Robots-Tag' => 'noindex']);
        }

        return $next($request);
    }
}

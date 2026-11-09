<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Writes a warning for any request that takes over 5 seconds, with its URL,
 * user agent and IP, so a PHP worker pile-up (2026-11-09) can be traced to the
 * pages or crawlers behind it. The daily log keeps warnings.
 */
class LogSlowRequests
{
    private const SLOW_SECONDS = 5;

    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }

    public function terminate(Request $request, $response): void
    {
        if (!defined('LARAVEL_START')) return;
        $took = microtime(true) - LARAVEL_START;
        if ($took < self::SLOW_SECONDS) return;

        Log::warning('Slow request', [
            'seconds' => round($took, 1),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'status' => method_exists($response, 'getStatusCode') ? $response->getStatusCode() : null,
            'ua' => substr((string) $request->userAgent(), 0, 160),
            'ip' => $request->ip(),
            'user' => $request->user()?->id,
        ]);
    }
}

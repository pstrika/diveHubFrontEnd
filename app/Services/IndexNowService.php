<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Pushes changed URLs to IndexNow (api.indexnow.org) so Bing, Yandex,
 * Seznam and Naver pick up a change on the next few minutes instead of
 * their next crawl - Google has no equivalent, IndexNow isn't for them.
 * One submission fans out to every participating engine (Pablo, 2026-09-29:
 * "can you build an IndexNow").
 *
 * Setup this depends on: an INDEXNOW_KEY in .env (config('services.indexnow.key'))
 * and a public/{key}.txt file containing exactly that key - IndexNow fetches
 * that file over HTTPS to prove whoever is submitting actually owns the
 * domain. Both need to exist in every environment that calls submit().
 */
class IndexNowService
{
    private const ENDPOINT = 'https://api.indexnow.org/indexnow';

    /**
     * @param string[] $urls Absolute URLs, all on the same host.
     */
    public static function submit(array $urls): bool
    {
        $key = config('services.indexnow.key');
        $urls = array_values(array_unique(array_filter($urls)));

        if (!$key || empty($urls)) {
            return false;
        }

        $host = parse_url($urls[0], PHP_URL_HOST);

        try {
            $response = Http::timeout(10)->asJson()->post(self::ENDPOINT, [
                'host' => $host,
                'key' => $key,
                'keyLocation' => "https://{$host}/{$key}.txt",
                'urlList' => $urls,
            ]);
        } catch (\Throwable $e) {
            Log::warning('IndexNow submission errored', ['message' => $e->getMessage(), 'urlCount' => count($urls)]);
            return false;
        }

        if (!$response->successful()) {
            Log::warning('IndexNow submission failed', ['status' => $response->status(), 'body' => $response->body(), 'urlCount' => count($urls)]);
        }

        return $response->successful();
    }

    public static function submitOne(string $url): bool
    {
        return self::submit([$url]);
    }
}

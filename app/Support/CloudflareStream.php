<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * The few Cloudflare Stream API calls dive videos need (Pablo, 2026-10-20).
 * Keys: CLOUDFLARE_ACCOUNT_ID and CLOUDFLARE_STREAM_TOKEN (a user API token
 * with Stream:Edit). No webhook: DiveVideo::refresh() asks for the status.
 * Every video requires signed URLs; playback tokens are minted here through
 * the API (no signing key to keep) and cached for most of their hour.
 */
class CloudflareStream
{
    public const MAX_SECONDS = 180;          // 3 minutes per video
    public const MAX_BYTES = 1024 ** 3;      // 1 GB per upload
    private const TOKEN_TTL = 3600;          // Cloudflare's default token life

    public static function configured(): bool
    {
        return (bool) (config('services.cloudflare_stream.account_id') && config('services.cloudflare_stream.token'));
    }

    private static function base(): string
    {
        return 'https://api.cloudflare.com/client/v4/accounts/' . config('services.cloudflare_stream.account_id') . '/stream';
    }

    private static function http()
    {
        return Http::withToken(config('services.cloudflare_stream.token'))->timeout(30)->acceptJson();
    }

    private static function fail($response, string $what): RuntimeException
    {
        $msg = $response->json('errors.0.message') ?? ('HTTP ' . $response->status());
        return new RuntimeException("Cloudflare $what failed: $msg");
    }

    /**
     * A resumable (tus) upload the browser sends straight to Cloudflare.
     * Returns [uploadUrl, uid]. Cloudflare refuses a video longer than
     * MAX_SECONDS once it reads it.
     */
    public static function createTusUpload(int $bytes, ?string $name): array
    {
        $meta = ['maxdurationseconds ' . base64_encode((string) self::MAX_SECONDS), 'requiresignedurls'];
        if ($name) $meta[] = 'name ' . base64_encode(mb_substr($name, 0, 200));
        $r = self::http()->withHeaders([
            'Tus-Resumable' => '1.0.0',
            'Upload-Length' => (string) $bytes,
            'Upload-Metadata' => implode(',', $meta),
        ])->withBody('', 'application/offset+octet-stream')->post(self::base() . '?direct_user=true');
        if ($r->status() !== 201 || !$r->header('Location')) throw self::fail($r, 'upload setup');
        return [$r->header('Location'), $r->header('stream-media-id')];
    }

    /** A one-shot upload URL (a plain multipart POST of `file`, up to 200 MB) - for the app. */
    public static function createDirectUpload(?string $name): array
    {
        $r = self::http()->post(self::base() . '/direct_upload', [
            'maxDurationSeconds' => self::MAX_SECONDS,
            'requireSignedURLs' => true,
            'meta' => array_filter(['name' => $name ? mb_substr($name, 0, 200) : null]),
        ]);
        if (!$r->json('success')) throw self::fail($r, 'upload setup');
        return [$r->json('result.uploadURL'), $r->json('result.uid')];
    }

    /** Cloudflare's record of a video, or null when it no longer exists. */
    public static function details(string $uid): ?array
    {
        $r = self::http()->get(self::base() . '/' . $uid);
        if ($r->status() === 404) return null;
        if (!$r->json('success')) throw self::fail($r, 'status check');
        return $r->json('result');
    }

    public static function delete(string $uid): void
    {
        $r = self::http()->delete(self::base() . '/' . $uid);
        if (!$r->successful() && $r->status() !== 404) throw self::fail($r, 'delete');
        Cache::forget('cf-stream-token:' . $uid);
    }

    /** Signed URLs on = owner-only (tokens); off = anyone with the link (a video approved for a site page). */
    public static function setSignedUrls(string $uid, bool $required): void
    {
        $r = self::http()->post(self::base() . '/' . $uid, ['requireSignedURLs' => $required]);
        if (!$r->json('success')) throw self::fail($r, 'visibility change');
    }

    /** A playback token for one video, good for about the next hour. */
    public static function token(string $uid): string
    {
        return Cache::remember('cf-stream-token:' . $uid, self::TOKEN_TTL - 600, function () use ($uid) {
            $r = self::http()->post(self::base() . '/' . $uid . '/token', ['exp' => time() + self::TOKEN_TTL]);
            if (!$r->json('success')) throw self::fail($r, 'playback token');
            return $r->json('result.token');
        });
    }
}

<?php

namespace App\Support;

/**
 * Site-relative storage for image/link URLs kept inside dives.conditions
 * (operator_logo, boat_pic, operator_url). They were being stored as the
 * absolute URL of whichever host the picker ran against - production rows
 * ended up pointing at http://localhost:8000 and http://192.168.86.25:8000
 * after app/local testing (Pablo, 2026-10-15: "the picture is not shown
 * in the PWA. But the picture is for sure there"). Store the path, resolve
 * to the current host on output.
 */
class AssetPath
{
    /** Strip any scheme+host so only the path (+query) remains; null stays null. */
    public static function relative(?string $url): ?string
    {
        if ($url === null || trim($url) === '') return null;
        $url = trim($url);
        if (preg_match('#^https?://[^/]+(/.*)?$#i', $url, $m)) {
            return $m[1] ?? '/';
        }
        return str_starts_with($url, '/') ? $url : '/' . $url;
    }

    /** Absolute URL on the current host for a stored value (already-absolute input is re-homed too). */
    public static function absolute(?string $url): ?string
    {
        $rel = self::relative($url);
        // url() roots on the current request (falls back to APP_URL off-request),
        // the same host every other asset() link on the page uses.
        return $rel === null ? null : url($rel);
    }
}

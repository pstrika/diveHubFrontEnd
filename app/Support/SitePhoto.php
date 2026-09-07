<?php

namespace App\Support;

/**
 * URLs for dive site photos at sensible sizes.
 *
 * The photo library under public/assets/img/sites holds camera originals,
 * many over 10 MB. tools/resize-site-photos.py writes WebP copies next to
 * them (web/ at 1600 px, web/thumb/ at 480 px). Views ask this class for a
 * URL and get the copy when it exists, or the original when it does not,
 * so a photo uploaded after the last resize run still shows, just slower.
 *
 * Usage in Blade:
 *   <img src="{{ SitePhoto::thumb($photo->file) }}">
 *   <img src="{{ SitePhoto::web($photo->file) }}">
 */
final class SitePhoto
{
    private const DIR = 'img/sites';

    /** Gallery and hero size (max 1600 px wide). */
    public static function web(?string $file): ?string
    {
        return self::url($file, 'web');
    }

    /** Card and list size (max 480 px wide). */
    public static function thumb(?string $file): ?string
    {
        return self::url($file, 'web/thumb');
    }

    /** The untouched original. */
    public static function original(?string $file): ?string
    {
        return $file ? asset('assets') . '/' . self::DIR . '/' . $file : null;
    }

    private static function url(?string $file, string $variant): ?string
    {
        if (!$file) {
            return null;
        }
        $stem = pathinfo($file, PATHINFO_FILENAME);
        $relative = self::DIR . '/' . $variant . '/' . $stem . '.webp';
        if (is_file(public_path('assets/' . $relative))) {
            return asset('assets') . '/' . $relative;
        }
        return self::original($file);
    }
}

<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

/**
 * Dive site photos at sensible sizes: URLs for the views, and the resize
 * routine that makes the copies.
 *
 * The photo library under public/assets/img/sites holds camera originals,
 * many over 10 MB. Two WebP copies live next to them:
 *
 *   img/sites/web/<stem>.webp        max 1600 px wide, galleries and heroes
 *   img/sites/web/thumb/<stem>.webp  max 480 px wide, cards and lists
 *
 * Views ask this class for a URL and get the copy when it exists, or the
 * original when it does not, so a photo without copies still shows, just
 * slower. Copies are made on upload (SiteController::upload) and by the
 * `php artisan photos:web-copies` command for the backlog. Both use PHP's
 * GD extension, no extra package. tools/resize-site-photos.py produced the
 * first batch and writes the same names, so the two can be mixed.
 *
 * Usage in Blade:
 *   <img src="{{ SitePhoto::thumb($photo->file) }}">
 *   <img src="{{ SitePhoto::web($photo->file) }}">
 */
final class SitePhoto
{
    public const DIR = 'img/sites';

    /** Variant folder => max width in pixels. */
    public const SIZES = ['web' => 1600, 'web/thumb' => 480];

    public const SOURCE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'jfif'];

    /** Originals above this many pixels are skipped: decoding them would exhaust PHP's memory limit. */
    private const MAX_PIXELS = 40_000_000;

    private const WEBP_QUALITY = 80;

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
        $relative = self::DIR . '/' . $variant . '/' . self::stem($file) . '.webp';
        if (is_file(public_path('assets/' . $relative))) {
            return asset('assets') . '/' . $relative;
        }
        return self::original($file);
    }

    /** "1715368528_PAS_6488-Edit.jpg" => "1715368528_PAS_6488-Edit". Same rule as the Python tool. */
    public static function stem(string $file): string
    {
        return pathinfo($file, PATHINFO_FILENAME);
    }

    /** True when this PHP can decode JPEG/PNG and encode WebP. */
    public static function canMakeCopies(): bool
    {
        if (!function_exists('imagewebp') || !function_exists('imagecreatefromstring')) {
            return false;
        }
        $info = gd_info();
        return !empty($info['WebP Support']) && !empty($info['JPEG Support']);
    }

    /**
     * Make the web and thumb copies for one original in img/sites.
     *
     * Never throws: this runs inside the admin upload, and a resize problem
     * must not fail the upload. Returns true when copies were written, null
     * when they were already up to date, false on failure (logged).
     */
    public static function makeCopies(string $file, bool $force = false): ?bool
    {
        try {
            if (!self::canMakeCopies()) {
                Log::warning("SitePhoto: GD without WebP support, no copies made for $file");
                return false;
            }
            $src = public_path('assets/' . self::DIR . '/' . $file);
            if (!is_file($src)) {
                Log::warning("SitePhoto: original not found: $src");
                return false;
            }

            $stem = self::stem($file);
            $pending = [];
            foreach (self::SIZES as $variant => $maxWidth) {
                $dest = public_path('assets/' . self::DIR . '/' . $variant . '/' . $stem . '.webp');
                if (!$force && is_file($dest) && filemtime($dest) >= filemtime($src)) {
                    continue;
                }
                $pending[$dest] = $maxWidth;
            }
            if (!$pending) {
                return null;
            }

            $size = @getimagesize($src);
            if (!$size) {
                Log::warning("SitePhoto: not an image GD can read: $file");
                return false;
            }
            if ($size[0] * $size[1] > self::MAX_PIXELS) {
                Log::warning("SitePhoto: $file is {$size[0]}x{$size[1]}, too large to resize in PHP; skipped");
                return false;
            }

            $image = @imagecreatefromstring((string) file_get_contents($src));
            if (!$image) {
                Log::warning("SitePhoto: GD could not decode $file");
                return false;
            }
            $image = self::applyExifOrientation($image, $src, $size[2]);

            foreach ($pending as $dest => $maxWidth) {
                $copy = self::scaled($image, $maxWidth);
                if (!is_dir(dirname($dest))) {
                    mkdir(dirname($dest), 0775, true);
                }
                $ok = imagewebp($copy, $dest, self::WEBP_QUALITY);
                if ($copy !== $image) {
                    imagedestroy($copy);
                }
                if (!$ok) {
                    Log::warning("SitePhoto: could not write $dest");
                    imagedestroy($image);
                    return false;
                }
            }
            imagedestroy($image);
            return true;
        } catch (\Throwable $e) {
            Log::warning("SitePhoto: failed for $file: " . $e->getMessage());
            return false;
        }
    }

    /** Scale down to $maxWidth, keeping aspect ratio. Returns the same image when it already fits. */
    private static function scaled(\GdImage $image, int $maxWidth): \GdImage
    {
        $w = imagesx($image);
        if ($w <= $maxWidth) {
            return $image;
        }
        $copy = imagescale($image, $maxWidth, -1, IMG_BICUBIC);
        return $copy ?: $image;
    }

    /** Phones store rotation as EXIF metadata; bake it into the pixels the way browsers show it. */
    private static function applyExifOrientation(\GdImage $image, string $src, int $imageType): \GdImage
    {
        if ($imageType !== IMAGETYPE_JPEG || !function_exists('exif_read_data')) {
            return $image;
        }
        $exif = @exif_read_data($src);
        $orientation = (int) ($exif['Orientation'] ?? 1);
        switch ($orientation) {
            case 3: $rotated = imagerotate($image, 180, 0); break;
            case 6: $rotated = imagerotate($image, -90, 0); break;
            case 8: $rotated = imagerotate($image, 90, 0); break;
            default: return $image;
        }
        if ($rotated) {
            imagedestroy($image);
            return $rotated;
        }
        return $image;
    }
}

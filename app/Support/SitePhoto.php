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
 * slower. Copies are made on upload (SiteController::upload,
 * DiverPhotoController::store) and by the `php artisan photos:web-copies`
 * command for the backlog. Prefers Imagick when it can encode WebP,
 * falling back to GD - found live (Pablo, 2026-09-24) that this app's
 * actual server has GD without WebP support at all, so every copy had
 * been silently failing since this class existed; Imagick is loaded there
 * and does support WEBP. tools/resize-site-photos.py produced the very
 * first batch (before either backend ran here) and writes the same names,
 * so all three can be mixed.
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

    /**
     * True when this PHP can encode WebP, via either backend (Pablo,
     * 2026-09-24: found live, via a diagnostic endpoint, that this app's
     * actual server has GD "bundled (2.1.0 compatible)" with WebP Support
     * false - it decodes/encodes JPEG and PNG fine, just never could write
     * WebP, so every makeCopies() call had been silently no-op-ing since
     * this class existed. Imagick IS loaded there and does support WEBP,
     * confirmed via queryFormats('WEBP*') - preferred below when present.
     */
    public static function canMakeCopies(): bool
    {
        return self::canUseImagick() || self::canUseGd();
    }

    private static function canUseImagick(): bool
    {
        if (!extension_loaded('imagick')) {
            return false;
        }
        try {
            return in_array('WEBP', (new \Imagick())->queryFormats('WEBP*'), true);
        } catch (\Throwable $e) {
            return false;
        }
    }

    private static function canUseGd(): bool
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
        $useImagick = self::canUseImagick();
        if (!$useImagick && !self::canUseGd()) {
            Log::warning("SitePhoto: no WebP-capable backend (GD or Imagick), no copies made for $file");
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
            Log::warning("SitePhoto: not an image this PHP can read: $file");
            return false;
        }
        if ($size[0] * $size[1] > self::MAX_PIXELS) {
            Log::warning("SitePhoto: $file is {$size[0]}x{$size[1]}, too large to resize in PHP; skipped");
            return false;
        }

        return $useImagick ? self::makeCopiesWithImagick($src, $file, $pending) : self::makeCopiesWithGd($src, $file, $size, $pending);
    }

    /** @param array<string,int> $pending dest path => max width */
    private static function makeCopiesWithImagick(string $src, string $file, array $pending): bool
    {
        try {
            $image = new \Imagick($src);
            // autoOrientImage() isn't available on every Imagick build -
            // confirmed live (Pablo, 2026-09-24) it's undefined on this
            // server's (PECL imagick predates its introduction), so this
            // reads the EXIF orientation tag directly instead - getImage
            // Orientation()/rotateImage() are wand-API-old and available
            // everywhere.
            self::applyImagickOrientation($image);

            foreach ($pending as $dest => $maxWidth) {
                $copy = clone $image;
                if ($copy->getImageWidth() > $maxWidth) {
                    // Height 0 = proportional to the new width.
                    $copy->resizeImage($maxWidth, 0, \Imagick::FILTER_LANCZOS, 1, false);
                }
                $copy->setImageFormat('webp');
                $copy->setImageCompressionQuality(self::WEBP_QUALITY);
                if (!is_dir(dirname($dest))) {
                    mkdir(dirname($dest), 0775, true);
                }
                $ok = $copy->writeImage($dest);
                $copy->clear();
                $copy->destroy();
                if (!$ok) {
                    Log::warning("SitePhoto (Imagick): could not write $dest");
                    $image->clear();
                    $image->destroy();
                    return false;
                }
            }
            $image->clear();
            $image->destroy();
            return true;
        } catch (\Throwable $e) {
            Log::warning("SitePhoto (Imagick): failed for $file: " . $e->getMessage());
            return false;
        }
    }

    /** Same 3 real-world cases GD's applyExifOrientation() below handles, via Imagick's own wand-API methods. */
    private static function applyImagickOrientation(\Imagick $image): void
    {
        $orientation = $image->getImageOrientation();
        switch ($orientation) {
            case \Imagick::ORIENTATION_BOTTOMRIGHT: // 3
                $image->rotateImage('#000', 180);
                break;
            case \Imagick::ORIENTATION_RIGHTTOP: // 6
                $image->rotateImage('#000', 90);
                break;
            case \Imagick::ORIENTATION_LEFTBOTTOM: // 8
                $image->rotateImage('#000', -90);
                break;
            default:
                return;
        }
        $image->setImageOrientation(\Imagick::ORIENTATION_TOPLEFT);
    }

    /**
     * @param array{0:int,1:int,2:int} $size getimagesize()'s result
     * @param array<string,int> $pending dest path => max width
     */
    private static function makeCopiesWithGd(string $src, string $file, array $size, array $pending): bool
    {
        try {
            $image = @imagecreatefromstring((string) file_get_contents($src));
            if (!$image) {
                Log::warning("SitePhoto (GD): could not decode $file");
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
                    Log::warning("SitePhoto (GD): could not write $dest");
                    imagedestroy($image);
                    return false;
                }
            }
            imagedestroy($image);
            return true;
        } catch (\Throwable $e) {
            Log::warning("SitePhoto (GD): failed for $file: " . $e->getMessage());
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

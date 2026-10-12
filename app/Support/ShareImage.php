<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

/**
 * JPEG copies of public images for link previews (og:image / twitter:image).
 *
 * SEO spot check, 2026-10-12: blog covers and the dive-site fallback
 * illustrations are WebP, which some link previewers (LinkedIn, X, older
 * apps) don't render. This makes a 1200 px JPEG copy next to nothing else
 * - under public/assets/img/share/ - the first time a page asks for it,
 * and reuses it after that until the source changes. Same backends as
 * SitePhoto::makeShareCopy(): Imagick first (the server's GD can't read
 * WebP), GD as a fallback for JPEG/PNG sources.
 *
 * Usage: $url = ShareImage::jpeg('assets/img/blog/123_cover.webp');
 * Returns the copy's URL, or null if no copy could be made (callers fall
 * back to the original).
 */
final class ShareImage
{
    private const DIR = 'assets/img/share';
    private const WIDTH = 1200;
    private const QUALITY = 85;
    private const MAX_PIXELS = 40_000_000;

    /** $relative: a path under public/, e.g. "assets/img/blog/x.webp". */
    public static function jpeg(?string $relative): ?string
    {
        if (!$relative) {
            return null;
        }
        $relative = ltrim($relative, '/');
        $src = public_path($relative);
        if (!is_file($src)) {
            return null;
        }

        // JPEGs and PNGs under 5 MB are fine as they are.
        if (preg_match('/\.(jpe?g|png)$/i', $relative) && filesize($src) <= 5 * 1024 * 1024) {
            return asset($relative);
        }

        // One flat folder; the hash keeps same-named files from different folders apart.
        $name = pathinfo($relative, PATHINFO_FILENAME) . '-' . substr(md5($relative), 0, 8) . '.jpg';
        $destRelative = self::DIR . '/' . $name;
        $dest = public_path($destRelative);

        if (is_file($dest) && filemtime($dest) >= filemtime($src)) {
            return asset($destRelative);
        }

        return self::make($src, $dest) ? asset($destRelative) : null;
    }

    private static function make(string $src, string $dest): bool
    {
        $size = @getimagesize($src);
        if ($size && $size[0] * $size[1] > self::MAX_PIXELS) {
            Log::warning("ShareImage: $src too large to resize in PHP");
            return false;
        }
        if (!is_dir(dirname($dest))) {
            @mkdir(dirname($dest), 0775, true);
        }

        try {
            if (extension_loaded('imagick')) {
                $image = new \Imagick($src);
                if ($image->getImageWidth() > self::WIDTH) {
                    $image->resizeImage(self::WIDTH, 0, \Imagick::FILTER_LANCZOS, 1, false);
                }
                $image->setImageBackgroundColor('white');
                $image = $image->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
                $image->setImageFormat('jpeg');
                $image->setImageCompressionQuality(self::QUALITY);
                $image->stripImage();
                $ok = $image->writeImage($dest);
                $image->clear();
                $image->destroy();
                return (bool) $ok;
            }

            if (function_exists('imagecreatefromstring') && function_exists('imagejpeg')) {
                $image = @imagecreatefromstring((string) file_get_contents($src));
                if (!$image) {
                    return false; // e.g. WebP on a GD build without WebP support
                }
                $w = imagesx($image);
                $h = imagesy($image);
                $newW = min($w, self::WIDTH);
                $newH = (int) round($h * $newW / $w);
                $flat = imagecreatetruecolor($newW, $newH);
                imagefill($flat, 0, 0, imagecolorallocate($flat, 255, 255, 255));
                imagecopyresampled($flat, $image, 0, 0, 0, 0, $newW, $newH, $w, $h);
                $ok = imagejpeg($flat, $dest, self::QUALITY);
                imagedestroy($flat);
                imagedestroy($image);
                return (bool) $ok;
            }
        } catch (\Throwable $e) {
            Log::warning("ShareImage: copy failed for $src: " . $e->getMessage());
        }

        return false;
    }
}

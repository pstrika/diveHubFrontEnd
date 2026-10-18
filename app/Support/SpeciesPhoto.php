<?php

namespace App\Support;

use App\Models\Species;
use Illuminate\Support\Facades\Log;

/**
 * Mirrors a species picture left-right (Pablo, 2026-10-18: "I want all the
 * fish to be looking to the right"). The file is rewritten under a new
 * name (_m suffix toggles) so browsers and the app don't keep showing the
 * cached, unflipped one; the old file is removed.
 */
class SpeciesPhoto
{
    public static function mirror(Species $s): bool
    {
        $dir = public_path('assets/img/species/');
        $src = $dir . $s->photo;
        if (!is_file($src)) return false;
        $stem = pathinfo($s->photo, PATHINFO_FILENAME);
        $newName = (str_ends_with($stem, '_m') ? substr($stem, 0, -2) : $stem . '_m') . '.webp';
        try {
            if (extension_loaded('imagick')) {
                $img = new \Imagick($src);
                $img->flopImage();
                $img->setImageFormat('webp');
                $img->setImageCompressionQuality(85);
                $ok = $img->writeImage($dir . $newName);
                $img->clear();
            } else {
                $img = imagecreatefromwebp($src);
                $ok = $img && imageflip($img, IMG_FLIP_HORIZONTAL) && imagewebp($img, $dir . $newName, 85);
                if ($img) imagedestroy($img);
            }
        } catch (\Throwable $e) {
            Log::warning('Species mirror failed for ' . $s->uuid . ': ' . $e->getMessage());
            return false;
        }
        if (!$ok) return false;
        if ($newName !== $s->photo) @unlink($src);
        $s->forceFill(['photo' => $newName, 'photo_mirrored' => !$s->photo_mirrored])->save();
        return true;
    }
}

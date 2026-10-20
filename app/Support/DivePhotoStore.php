<?php

namespace App\Support;

use App\Http\Controllers\DiverPhotoController;
use App\Models\Dive;
use App\Models\DivePhoto;
use App\Models\DiverPhoto;
use App\Models\Site;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

/**
 * Adding, sharing and unsharing dive photos - one place for the web page
 * (DivePhotoController) and the app (Api\DivePhotoApiController).
 */
class DivePhotoStore
{
    /** Validation rule for one upload. Same limits as diver site photos. */
    public const RULE = 'required|image|mimes:jpg,jpeg,png,webp|max:20480';

    public static function add(Dive $dive, UploadedFile $file, ?string $caption = null, ?string $uuid = null, ?string $takenAt = null): DivePhoto
    {
        if ($dive->photos()->count() >= DivePhoto::MAX_PER_DIVE) {
            throw ValidationException::withMessages(['photo' => 'A dive can have up to ' . DivePhoto::MAX_PER_DIVE . ' photos.']);
        }
        if ($uuid && ($existing = DivePhoto::where('uuid', $uuid)->where('user_id', $dive->user_id)->first())) {
            return $existing; // the app retried an upload that already landed
        }

        $photo = new DivePhoto([
            'uuid' => $uuid ?: (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $dive->user_id,
            'dive_id' => $dive->id,
            'caption' => $caption ? mb_substr($caption, 0, 500) : null,
            // The browser shrinks big camera files before upload (dive-photos.js)
            // and the copy has no EXIF, so it sends the original's date along.
            'taken_at' => $takenAt ?: self::takenAt($file->getRealPath()),
            'position' => (int) $dive->photos()->max('position') + 1,
        ]);

        $ok = SitePhoto::resizeTo($file->getRealPath(), [$photo->path('web') => 1600, $photo->path('thumb') => 480]);
        if (!$ok || !is_file($photo->path('web'))) {
            throw ValidationException::withMessages(['photo' => "That picture couldn't be read. Try a JPG or PNG."]);
        }
        [$photo->width, $photo->height] = @getimagesize($photo->path('web')) ?: [null, null];
        $photo->save();
        $dive->logUpdated();
        return $photo;
    }

    /** When the camera says it was taken (EXIF), before the original is thrown away. */
    private static function takenAt(string $path): ?string
    {
        if (!function_exists('exif_read_data')) return null;
        $exif = @exif_read_data($path);
        $raw = $exif['DateTimeOriginal'] ?? $exif['DateTime'] ?? null;
        if (!$raw) return null;
        $t = \DateTime::createFromFormat('Y:m:d H:i:s', $raw);
        return $t ? $t->format('Y-m-d H:i:s') : null;
    }

    /**
     * Offer the photo to the dive site's public page: a copy goes into the
     * normal diver-photo review queue (Pablo, 2026-10-17). Only for dives
     * at a catalog site. The private photo stays as it is.
     */
    public static function share(DivePhoto $photo): DiverPhoto
    {
        $dive = $photo->dive;
        $site = $dive?->site_id ? Site::find($dive->site_id) : null;
        if (!$site) {
            throw ValidationException::withMessages(['photo' => 'Only photos of a dive at a Divers Hub site can go on a site page.']);
        }
        if ($existing = $photo->sharedCopy()) {
            return $existing;
        }

        $filename = 'diver_' . $site->id . '_' . time() . '_' . uniqid() . '.webp';
        $dest = public_path('assets/' . SitePhoto::DIR . '/' . $filename);
        if (!is_dir(dirname($dest))) mkdir(dirname($dest), 0775, true);
        copy($photo->path('web'), $dest);
        if (SitePhoto::makeCopies($filename) !== false) {
            @unlink($dest);
        }

        $copy = DiverPhoto::create([
            'siteId' => $site->id,
            'userId' => $photo->user_id,
            'file' => $filename,
            'desc' => $photo->caption,
            'status' => 'pending',
        ]);
        $photo->shared_diver_photo_id = $copy->id;
        $photo->save();
        DiverPhotoController::notifyAdmins($site);
        $dive->logUpdated();
        return $copy;
    }

    /** Take it back off the site page (or out of the review queue). */
    public static function unshare(DivePhoto $photo): void
    {
        $photo->sharedCopy()?->deleteWithFile();
        $photo->shared_diver_photo_id = null;
        $photo->save();
        $photo->dive?->logUpdated();
    }

    /** Delete a photo, and its copy on the site page if it was shared. */
    public static function delete(DivePhoto $photo): void
    {
        $photo->sharedCopy()?->deleteWithFile();
        $dive = $photo->dive;
        $photo->deleteWithFiles();
        $dive?->logUpdated();
    }
}

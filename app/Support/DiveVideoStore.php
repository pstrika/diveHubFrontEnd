<?php

namespace App\Support;

use App\Http\Controllers\SiteVideoController;
use App\Models\Dive;
use App\Models\DiveVideo;
use App\Models\Site;
use App\Models\SiteVideo;
use Illuminate\Validation\ValidationException;

/**
 * Dive videos (Pablo, 2026-10-20) - the video twin of DivePhotoStore. The
 * browser or app uploads straight to Cloudflare Stream; our server only
 * sets the upload up, tracks the row, and shares / deletes.
 */
class DiveVideoStore
{
    /**
     * Set up an upload. Returns [video, uploadUrl, kind]: 'tus' (resumable,
     * needs the byte size up front) or 'direct' (one multipart POST of
     * `file`, up to 200 MB) when no size was given.
     */
    public static function create(Dive $dive, ?int $bytes, ?string $name, ?string $caption = null, ?string $uuid = null): array
    {
        if (!CloudflareStream::configured()) {
            throw ValidationException::withMessages(['video' => 'Video uploads are not available right now.']);
        }
        if ($uuid && ($existing = DiveVideo::where('uuid', $uuid)->where('user_id', $dive->user_id)->first())) {
            throw ValidationException::withMessages(['video' => 'This video was already added.']);
        }
        if ($dive->videos()->count() >= DiveVideo::MAX_PER_DIVE) {
            throw ValidationException::withMessages(['video' => 'A dive can have up to ' . DiveVideo::MAX_PER_DIVE . ' videos.']);
        }
        if ($bytes !== null && $bytes > CloudflareStream::MAX_BYTES) {
            throw ValidationException::withMessages(['video' => 'A video can be up to 1 GB.']);
        }
        [$url, $uid] = $bytes !== null ? CloudflareStream::createTusUpload($bytes, $name) : CloudflareStream::createDirectUpload($name);
        $video = DiveVideo::create([
            'uuid' => $uuid, 'user_id' => $dive->user_id, 'dive_id' => $dive->id,
            'stream_uid' => $uid, 'stream_status' => 'uploading',
            'original_name' => $name ? mb_substr($name, 0, 255) : null, 'size_bytes' => $bytes, 'caption' => $caption,
            'position' => (int) $dive->videos()->max('position') + 1,
        ]);
        return [$video, $url, $bytes !== null ? 'tus' : 'direct'];
    }

    /** The upload finished: Cloudflare is processing it. */
    public static function uploaded(DiveVideo $video): DiveVideo
    {
        if ($video->stream_status === 'uploading') {
            $video->stream_status = 'processing';
            $video->save();
        }
        $video->refreshStream(true);
        $video->dive?->logUpdated();
        return $video;
    }

    /** Offer it for the dive site's public page (admin review first). */
    public static function share(DiveVideo $video): SiteVideo
    {
        $dive = $video->dive;
        $site = $dive?->site_id ? Site::find($dive->site_id) : null;
        if (!$site) {
            throw ValidationException::withMessages(['video' => 'Only videos of a dive at a Divers Hub site can go on a site page.']);
        }
        if (!$video->isReady()) {
            throw ValidationException::withMessages(['video' => 'Wait until the video has finished processing.']);
        }
        if ($existing = $video->sharedCopy()) {
            return $existing;
        }
        $copy = SiteVideo::create([
            'siteId' => $site->id, 'userId' => $video->user_id,
            'stream_uid' => $video->stream_uid, 'owns_stream' => false,
            'stream_status' => $video->stream_status, 'customer_host' => $video->customer_host,
            'duration_seconds' => $video->duration_seconds, 'width' => $video->width, 'height' => $video->height,
            'size_bytes' => $video->size_bytes, 'original_name' => $video->original_name,
            'status' => 'pending', 'desc' => $video->caption ? mb_substr($video->caption, 0, 150) : null,
        ]);
        $video->shared_site_video_id = $copy->id;
        $video->save();
        SiteVideoController::notifyAdmins($site);
        $dive->logUpdated();
        return $copy;
    }

    /** Take it back off the site page (or out of the review queue). */
    public static function unshare(DiveVideo $video): void
    {
        $video->sharedCopy()?->deleteWithStream();
        $video->shared_site_video_id = null;
        $video->save();
        $video->dive?->logUpdated();
    }

    /** Delete a video, its Cloudflare asset, and its site-page copy if shared. */
    public static function delete(DiveVideo $video): void
    {
        if ($copy = $video->sharedCopy()) {
            $copy->delete(); // the row only; the asset goes below
        }
        try {
            CloudflareStream::delete($video->stream_uid);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Cloudflare delete failed for ' . $video->stream_uid . ': ' . $e->getMessage());
        }
        $dive = $video->dive;
        $video->delete();
        $dive?->logUpdated();
    }
}

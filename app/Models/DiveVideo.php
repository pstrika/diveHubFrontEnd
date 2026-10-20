<?php

namespace App\Models;

use App\Models\Concerns\StreamVideo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * A video on a logbook dive (Pablo, 2026-10-20). Private to the diver: the
 * file is on Cloudflare Stream with signed URLs required, and only the owner
 * gets playback tokens. "Share to the site page" makes a SiteVideo row
 * (mysql_trips) pointing at the same Cloudflare video, which goes through
 * the normal admin review - see App\Support\DiveVideoStore.
 */
class DiveVideo extends Model
{
    use StreamVideo;

    public const MAX_PER_DIVE = 5;

    protected $fillable = ['uuid', 'user_id', 'dive_id', 'stream_uid', 'stream_status', 'original_name', 'size_bytes', 'caption', 'position'];

    protected $casts = ['checked_at' => 'datetime'];

    protected static function booted()
    {
        static::creating(fn (DiveVideo $v) => $v->uuid ??= (string) Str::uuid());
    }

    public function dive(): BelongsTo
    {
        return $this->belongsTo(Dive::class);
    }

    /** The site-page copy, when shared (mysql_trips connection). */
    public function sharedCopy(): ?SiteVideo
    {
        return $this->shared_site_video_id ? SiteVideo::find($this->shared_site_video_id) : null;
    }

    /** What the API returns for a video. Playback URLs are good for about an hour. */
    public function toApi(): array
    {
        $shared = $this->sharedCopy();
        return [
            'uuid' => $this->uuid,
            'dive_uuid' => $this->dive?->uuid,
            'status' => $this->stream_status, // uploading | processing | ready | failed
            'error' => $this->stream_error,
            'duration_seconds' => $this->duration_seconds,
            'width' => $this->width,
            'height' => $this->height,
            'size_bytes' => $this->size_bytes,
            'caption' => $this->caption,
            'position' => $this->position,
            'shared' => $shared ? $shared->status : null, // null | pending | approved | rejected
            'player_url' => $this->playerUrl(),
            'hls_url' => $this->hlsUrl(),
            'thumbnail_url' => $this->thumbnailUrl(),
            'urls_expire_at' => $this->isReady() ? now()->addMinutes(50)->toIso8601String() : null,
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

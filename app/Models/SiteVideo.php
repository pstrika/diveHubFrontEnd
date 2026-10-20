<?php

namespace App\Models;

use App\Models\Concerns\StreamVideo;
use App\Support\CloudflareStream;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A diver's video on a site page (Pablo, 2026-10-20), pending admin review
 * before it shows publicly - the video twin of DiverPhoto. Uploaded on the
 * site page (owns_stream), or shared from a logbook dive (the DiveVideo
 * keeps the Cloudflare asset; this row only points at it). Approval turns
 * Cloudflare's signed-URL requirement off so anyone can watch it; reject or
 * remove turns it back on.
 */
class SiteVideo extends Model
{
    use StreamVideo;

    protected $connection = 'mysql_trips';
    protected $table = 'site_videos';

    protected $fillable = ['siteId', 'userId', 'stream_uid', 'owns_stream', 'stream_status', 'stream_error', 'customer_host',
        'duration_seconds', 'width', 'height', 'size_bytes', 'original_name', 'status', 'desc', 'reviewedBy', 'reviewedAt', 'checked_at'];

    protected $casts = ['owns_stream' => 'boolean', 'reviewedAt' => 'datetime', 'checked_at' => 'datetime'];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'siteId', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'userId', 'id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewedBy', 'id');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /** The logbook video this was shared from, if any (default connection). */
    public function diveVideo(): ?DiveVideo
    {
        return $this->owns_stream ? null : DiveVideo::where('shared_site_video_id', $this->id)->first();
    }

    public function approve(int $adminId, ?string $desc): void
    {
        $this->update(['status' => 'approved', 'desc' => $desc, 'reviewedBy' => $adminId, 'reviewedAt' => now()]);
        CloudflareStream::setSignedUrls($this->stream_uid, false);
    }

    public function reject(int $adminId): void
    {
        $wasPublic = $this->status === 'approved';
        $this->update(['status' => 'rejected', 'reviewedBy' => $adminId, 'reviewedAt' => now()]);
        if ($wasPublic) CloudflareStream::setSignedUrls($this->stream_uid, true);
    }

    /**
     * Remove from the site page. A site upload takes its Cloudflare video
     * with it; a shared dive video stays in the diver's logbook, private again.
     */
    public function deleteWithStream(): void
    {
        if ($this->owns_stream) {
            try { CloudflareStream::delete($this->stream_uid); } catch (\Throwable $e) { \Illuminate\Support\Facades\Log::warning($e->getMessage()); }
        } else {
            DiveVideo::where('shared_site_video_id', $this->id)->update(['shared_site_video_id' => null]);
            if ($this->status === 'approved') {
                try { CloudflareStream::setSignedUrls($this->stream_uid, true); } catch (\Throwable $e) { \Illuminate\Support\Facades\Log::warning($e->getMessage()); }
            }
        }
        $this->delete();
    }
}

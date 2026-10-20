<?php

namespace App\Models;

use App\Models\Concerns\StreamVideo;
use App\Support\CloudflareStream;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A video posted in a group chat (Pablo, 2026-10-20) - on Cloudflare Stream,
 * private: playback URLs carry a token the page mints for members only.
 * Uploaded ahead of the message (group_message_id null) like chat photos,
 * then attached when the message is sent.
 */
class GroupMessageVideo extends Model
{
    use StreamVideo;

    protected $connection = 'mysql_trips';
    protected $table = 'group_message_videos';

    protected $fillable = ['group_id', 'group_message_id', 'user_id', 'stream_uid', 'stream_status', 'original_name', 'size_bytes'];

    protected $casts = ['checked_at' => 'datetime'];

    public function message(): BelongsTo
    {
        return $this->belongsTo(GroupMessage::class, 'group_message_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Removes the Cloudflare asset and the row. */
    public function deleteVideo(): void
    {
        try {
            CloudflareStream::delete($this->stream_uid);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Cloudflare delete failed for ' . $this->stream_uid . ': ' . $e->getMessage());
        }
        $this->delete();
    }
}

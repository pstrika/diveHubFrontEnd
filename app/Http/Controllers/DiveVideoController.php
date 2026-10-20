<?php

namespace App\Http\Controllers;

use App\Models\Dive;
use App\Models\DiveVideo;
use App\Support\CloudflareStream;
use App\Support\DiveVideoStore;
use Illuminate\Http\Request;

/**
 * Videos card on the dive page (Pablo, 2026-10-20). The page asks for an
 * upload (store), sends the file straight to Cloudflare with tus
 * (dive-videos.js), tells us when it's done (uploaded), then polls show()
 * until Cloudflare has processed it.
 */
class DiveVideoController extends Controller
{
    private function ownDive(Request $request, $id): Dive
    {
        return Dive::where('user_id', $request->user()->id)->findOrFail((int) $id);
    }

    private function ownVideo(Request $request, string $uuid): DiveVideo
    {
        return DiveVideo::where('user_id', $request->user()->id)->where('uuid', $uuid)->firstOrFail();
    }

    /** JSON {name, size} -> {video, upload_url}. */
    public function store(Request $request, $id)
    {
        $dive = $this->ownDive($request, $id);
        $data = $request->validate(['name' => 'nullable|string|max:255', 'size' => 'required|integer|min:1']);
        [$video, $url] = DiveVideoStore::create($dive, (int) $data['size'], $data['name'] ?? null);
        return response()->json(['video' => self::card($video), 'upload_url' => $url], 201);
    }

    public function uploaded(Request $request, string $uuid)
    {
        $video = DiveVideoStore::uploaded($this->ownVideo($request, $uuid));
        return response()->json(['video' => self::card($video)]);
    }

    /** Current status (polled while processing). */
    public function show(Request $request, string $uuid)
    {
        $video = $this->ownVideo($request, $uuid)->refreshStream();
        return response()->json(['video' => self::card($video)]);
    }

    public function update(Request $request, string $uuid)
    {
        $video = $this->ownVideo($request, $uuid);
        $data = $request->validate(['caption' => 'nullable|string|max:500']);
        $video->caption = $data['caption'] ?? null;
        $video->save();
        $video->dive?->logUpdated();
        return response()->json(['video' => self::card($video)]);
    }

    public function destroy(Request $request, string $uuid)
    {
        DiveVideoStore::delete($this->ownVideo($request, $uuid));
        return response()->json(['ok' => true]);
    }

    public function share(Request $request, string $uuid)
    {
        $video = $this->ownVideo($request, $uuid);
        $request->boolean('share') ? DiveVideoStore::share($video) : DiveVideoStore::unshare($video);
        return response()->json(['video' => self::card($video->fresh())]);
    }

    /** What the card's JS needs for one video. */
    public static function card(DiveVideo $video): array
    {
        $shared = $video->sharedCopy();
        return [
            'uuid' => $video->uuid,
            'status' => $video->stream_status,
            'error' => $video->stream_error,
            'thumb' => $video->thumbnailUrl(),
            'player' => $video->playerUrl(),
            'duration' => $video->durationLabel(),
            'caption' => $video->caption,
            'name' => $video->original_name,
            'shared' => $shared?->status,
            'status_url' => route('DiveVideos.show', $video->uuid),
            'uploaded_url' => route('DiveVideos.uploaded', $video->uuid),
            'update_url' => route('DiveVideos.update', $video->uuid),
            'share_url' => route('DiveVideos.share', $video->uuid),
            'delete_url' => route('DiveVideos.destroy', $video->uuid),
        ];
    }

    public static function limits(): array
    {
        return ['max' => DiveVideo::MAX_PER_DIVE, 'maxSeconds' => CloudflareStream::MAX_SECONDS, 'maxBytes' => CloudflareStream::MAX_BYTES];
    }
}

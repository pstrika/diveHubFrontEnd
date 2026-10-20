<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\SiteVideo;
use App\Models\User;
use App\Services\NotificationService;
use App\Support\CloudflareStream;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Registered divers uploading a video to a site page (Pablo, 2026-10-20),
 * and removing their own. The browser sends the file straight to Cloudflare
 * (site-videos.js); admin review lives in SiteVideoAdminController.
 */
class SiteVideoController extends Controller
{
    public const MAX_PER_SITE_PER_DIVER = 5;

    /** JSON {name, size} -> {id, upload_url}. */
    public function store(Request $request, $siteId)
    {
        abort_unless($request->user()->isNotGuest(), 403);
        if (!CloudflareStream::configured()) {
            throw ValidationException::withMessages(['video' => 'Video uploads are not available right now.']);
        }
        $site = Site::findOrFail($siteId);
        $data = $request->validate(['name' => 'nullable|string|max:255', 'size' => 'required|integer|min:1|max:' . CloudflareStream::MAX_BYTES]);
        if (SiteVideo::where('siteId', $site->id)->where('userId', $request->user()->id)->where('status', '!=', 'rejected')->count() >= self::MAX_PER_SITE_PER_DIVER) {
            throw ValidationException::withMessages(['video' => 'You already have ' . self::MAX_PER_SITE_PER_DIVER . ' videos on this site.']);
        }
        [$url, $uid] = CloudflareStream::createTusUpload((int) $data['size'], $data['name'] ?? null);
        $video = SiteVideo::create([
            'siteId' => $site->id, 'userId' => $request->user()->id, 'stream_uid' => $uid, 'owns_stream' => true,
            'stream_status' => 'uploading', 'original_name' => $data['name'] ?? null, 'size_bytes' => (int) $data['size'], 'status' => 'pending',
        ]);
        return response()->json(['id' => $video->id, 'upload_url' => $url, 'uploaded_url' => route('SiteVideos.uploaded', $video->id)], 201);
    }

    /** The upload finished: now pending review while Cloudflare processes it. */
    public function uploaded(Request $request, $id)
    {
        $video = SiteVideo::where('userId', $request->user()->id)->findOrFail((int) $id);
        if ($video->stream_status === 'uploading') {
            $video->stream_status = 'processing';
            $video->save();
        }
        $video->refreshStream(true);
        self::notifyAdmins($video->site);
        return response()->json(['ok' => true, 'status' => $video->stream_status]);
    }

    public function destroy($id)
    {
        $video = SiteVideo::findOrFail((int) $id);
        abort_unless($video->userId === auth()->id() || auth()->user()->isAdmin(), 403);
        $video->deleteWithStream();
        return back()->with('msg', 'Video removed.');
    }

    /** Same "notify every admin" pattern as DiverPhotoController. */
    public static function notifyAdmins(?Site $site): void
    {
        if (!$site) return;
        $adminIds = User::where('role_id', 1)->pluck('id');
        if ($adminIds->isEmpty()) return;
        NotificationService::notify($adminIds, 'New diver video pending review', "A diver uploaded a video of {$site->name}.", route('SiteVideos.manage.index'));
    }
}

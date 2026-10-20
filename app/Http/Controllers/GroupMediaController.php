<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupMessage;
use App\Models\GroupMessagePhoto;
use App\Models\GroupMessageVideo;
use App\Support\CloudflareStream;
use App\Support\UserAvatar;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Group chat videos and the "Photos & videos" gallery (Pablo, 2026-10-20):
 * "everything that was uploaded to the chat now shows there too... easy for
 * a user to delete a video or photo they may have uploaded". Videos go
 * straight from the browser to Cloudflare Stream (group-media.js), ahead
 * of the message like chat photos do. Deleting is for the uploader, or a
 * group admin.
 */
class GroupMediaController extends Controller
{
    private function memberGroup(string $slug): Group
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        abort_unless($group->isMember(auth()->id()), 403);
        return $group;
    }

    /** JSON {name, size} -> {id, upload_url, uploaded_url, status_url}. */
    public function storeVideo(Request $request, $groupSlug)
    {
        $group = $this->memberGroup($groupSlug);
        if (!CloudflareStream::configured()) {
            throw ValidationException::withMessages(['video' => 'Video uploads are not available right now.']);
        }
        $data = $request->validate(['name' => 'nullable|string|max:255', 'size' => 'required|integer|min:1|max:' . CloudflareStream::MAX_BYTES]);
        [$url, $uid] = CloudflareStream::createTusUpload((int) $data['size'], $data['name'] ?? null);
        $video = GroupMessageVideo::create([
            'group_id' => $group->id, 'user_id' => auth()->id(), 'stream_uid' => $uid, 'stream_status' => 'uploading',
            'original_name' => $data['name'] ?? null, 'size_bytes' => (int) $data['size'],
        ]);
        return response()->json([
            'id' => $video->id, 'upload_url' => $url,
            'uploaded_url' => route('Groups.videos.uploaded', ['group' => $group->slug, 'id' => $video->id]),
            'status_url' => route('Groups.videos.status', ['group' => $group->slug, 'id' => $video->id]),
        ], 201);
    }

    public function videoUploaded(Request $request, $groupSlug, $id)
    {
        $group = $this->memberGroup($groupSlug);
        $video = GroupMessageVideo::where('group_id', $group->id)->where('user_id', auth()->id())->findOrFail((int) $id);
        if ($video->stream_status === 'uploading') {
            $video->stream_status = 'processing';
            $video->save();
        }
        $video->refreshStream(true);
        return response()->json(['id' => $video->id, 'status' => $video->stream_status, 'thumb' => $video->thumbnailUrl(120)]);
    }

    public function videoStatus(Request $request, $groupSlug, $id)
    {
        $group = $this->memberGroup($groupSlug);
        $video = GroupMessageVideo::where('group_id', $group->id)->findOrFail((int) $id)->refreshStream();
        return response()->json(['id' => $video->id, 'status' => $video->stream_status, 'error' => $video->stream_error, 'thumb' => $video->thumbnailUrl(120)]);
    }

    /** The gallery as JSON (refreshed after a post or a delete). */
    public function index(Request $request, $groupSlug)
    {
        $group = $this->memberGroup($groupSlug);
        return response()->json(['media' => self::galleryItems($group)]);
    }

    /** DELETE a photo or video you uploaded (group admins can remove anyone's). */
    public function destroy(Request $request, $groupSlug, string $kind, $id)
    {
        $group = $this->memberGroup($groupSlug);
        $item = $kind === 'video'
            ? GroupMessageVideo::where('group_id', $group->id)->findOrFail((int) $id)
            : GroupMessagePhoto::whereHas('message', fn ($q) => $q->where('group_id', $group->id))->findOrFail((int) $id);
        $message = $item->message;
        $ownerId = $kind === 'video' ? (int) $item->user_id : (int) ($message?->user_id ?? 0);
        abort_unless($ownerId === (int) auth()->id() || $group->isAdmin(auth()->id()), 403);

        $kind === 'video' ? $item->deleteVideo() : $item->deletePhoto();

        // A message that was only this attachment goes too.
        if ($message && !$message->body && $message->photos()->count() === 0 && $message->videos()->count() === 0) {
            $message->reactions()->delete();
            $message->siteMentions()->delete();
            $message->delete();
        }
        return response()->json(['ok' => true, 'media' => self::galleryItems($group)]);
    }

    /** Every photo and video posted in the chat, newest first, with what the gallery needs. */
    public static function galleryItems(Group $group): array
    {
        $viewerId = (int) auth()->id();
        $admin = $group->isAdmin($viewerId);
        $items = [];
        $messages = $group->messages()->with(['user', 'photos', 'videos'])->get();
        foreach ($messages as $m) {
            $uploader = ['name' => $m->user->name ?? 'A diver', 'avatar' => UserAvatar::url($m->user->picture ?? null)];
            $canDelete = $admin || (int) $m->user_id === $viewerId;
            foreach ($m->photos as $p) {
                $items[] = [
                    'kind' => 'photo', 'id' => $p->id, 'url' => asset('assets/' . $p->file), 'thumb' => asset('assets/' . $p->file),
                    'status' => 'ready', 'caption' => $m->body ? \Illuminate\Support\Str::limit($m->body, 120) : null,
                    'user' => $uploader, 'when' => $p->created_at->toIso8601String(), 'when_label' => $p->created_at->format('M j, Y'),
                    'can_delete' => $canDelete, 'delete_url' => route('Groups.media.destroy', ['group' => $group->slug, 'kind' => 'photo', 'id' => $p->id]),
                ];
            }
            foreach ($m->videos as $v) {
                $v->refreshStream();
                $items[] = [
                    'kind' => 'video', 'id' => $v->id, 'url' => $v->playerUrl(), 'thumb' => $v->thumbnailUrl(),
                    'status' => $v->stream_status, 'duration' => $v->durationLabel(), 'caption' => $m->body ? \Illuminate\Support\Str::limit($m->body, 120) : null,
                    'user' => $uploader, 'when' => $v->created_at->toIso8601String(), 'when_label' => $v->created_at->format('M j, Y'),
                    'can_delete' => $canDelete, 'delete_url' => route('Groups.media.destroy', ['group' => $group->slug, 'kind' => 'video', 'id' => $v->id]),
                ];
            }
        }
        usort($items, fn ($a, $b) => strcmp($b['when'], $a['when']));
        return $items;
    }
}

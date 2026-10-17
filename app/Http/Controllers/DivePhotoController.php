<?php

namespace App\Http\Controllers;

use App\Models\Dive;
use App\Models\DivePhoto;
use App\Support\DivePhotoStore;
use Illuminate\Http\Request;

/**
 * Photos card on the dive page (Pablo, 2026-10-17). The page uploads one
 * file per request (phones' pictures run 5-15 MB; several at once would
 * pass the 30 MB post limit) and redraws the card from the JSON.
 */
class DivePhotoController extends Controller
{
    private function ownDive(Request $request, $id): Dive
    {
        return Dive::where('user_id', $request->user()->id)->findOrFail((int) $id);
    }

    private function ownPhoto(Request $request, string $uuid): DivePhoto
    {
        return DivePhoto::where('user_id', $request->user()->id)->where('uuid', $uuid)->firstOrFail();
    }

    public function store(Request $request, $id)
    {
        $dive = $this->ownDive($request, $id);
        $request->validate(['photo' => DivePhotoStore::RULE, 'caption' => 'nullable|string|max:500']);
        $photo = DivePhotoStore::add($dive, $request->file('photo'), $request->input('caption'));
        return response()->json(['photo' => $this->card($photo), 'count' => $dive->photos()->count()], 201);
    }

    public function update(Request $request, string $uuid)
    {
        $photo = $this->ownPhoto($request, $uuid);
        $data = $request->validate(['caption' => 'nullable|string|max:500']);
        $photo->caption = $data['caption'] ?? null;
        $photo->save();
        $photo->dive?->logUpdated();
        return response()->json(['photo' => $this->card($photo)]);
    }

    public function destroy(Request $request, string $uuid)
    {
        DivePhotoStore::delete($this->ownPhoto($request, $uuid));
        return response()->json(['ok' => true]);
    }

    public function share(Request $request, string $uuid)
    {
        $photo = $this->ownPhoto($request, $uuid);
        $request->boolean('share') ? DivePhotoStore::share($photo) : DivePhotoStore::unshare($photo);
        return response()->json(['photo' => $this->card($photo->fresh())]);
    }

    /** New order: uuids in order. */
    public function reorder(Request $request, $id)
    {
        $dive = $this->ownDive($request, $id);
        $uuids = $request->validate(['order' => 'required|array', 'order.*' => 'string'])['order'];
        foreach ($uuids as $i => $u) {
            DivePhoto::where('dive_id', $dive->id)->where('uuid', $u)->update(['position' => $i + 1]);
        }
        $dive->logUpdated();
        return response()->json(['ok' => true]);
    }

    /** The file itself - owner only (private photos), cached by the browser. */
    public function file(Request $request, string $uuid, string $size = 'web')
    {
        $photo = $this->ownPhoto($request, $uuid);
        $path = $photo->path($size === 'thumb' ? 'thumb' : 'web');
        abort_unless(is_file($path), 404);
        return response()->file($path, ['Content-Type' => 'image/webp', 'Cache-Control' => 'private, max-age=31536000, immutable']);
    }

    /** What the card's JS needs for one photo. */
    public static function card(DivePhoto $photo): array
    {
        $shared = $photo->sharedCopy();
        return [
            'uuid' => $photo->uuid,
            'url' => $photo->url('web'),
            'thumb' => $photo->url('thumb'),
            'caption' => $photo->caption,
            'taken_at' => $photo->taken_at?->format('M j, Y g:i A'),
            'shared' => $shared?->status,
            'species' => $photo->species->map(fn ($s) => ['uuid' => $s->uuid, 'name' => $s->common_name])->values(),
            'update_url' => route('DivePhotos.update', $photo->uuid),
            'share_url' => route('DivePhotos.share', $photo->uuid),
        ];
    }
}

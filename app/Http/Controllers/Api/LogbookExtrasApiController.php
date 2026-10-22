<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\DiveSpeciesController;
use App\Http\Controllers\EquipmentController;
use App\Models\Dive;
use App\Models\DivePhoto;
use App\Models\EquipmentConfig;
use App\Models\EquipmentItem;
use App\Models\Species;
use App\Support\DivePhotoStore;
use App\Support\EarlyAccess;
use App\Support\SitePhoto;
use App\Support\SpeciesTagger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Photos, marine life and equipment for the app (Pablo, 2026-10-17: "allow
 * for upload pictures ... allow from the app the tagging of species on
 * pictures and dives"). Contract: docs/app-api/PHOTOS-SPECIES-
 * EQUIPMENT.md (sent to the iOS session). Every change to a dive's photos,
 * species or gear logs 'updated' for that dive in the change feed.
 * Species and equipment are early access: 404 for accounts without it,
 * and GET /api/me/features says which the account has.
 */
class LogbookExtrasApiController extends Controller
{
    private function dive(Request $request, string $uuid): Dive
    {
        return Dive::where('user_id', $request->user()->id)->where('uuid', $uuid)->firstOrFail();
    }

    private function photo(Request $request, string $uuid): DivePhoto
    {
        return DivePhoto::where('user_id', $request->user()->id)->where('uuid', $uuid)->with('species', 'dive')->firstOrFail();
    }

    private function early(Request $request, string $feature): void
    {
        abort_unless(EarlyAccess::allows($request->user(), $feature), 404);
    }

    public function features(Request $request)
    {
        $u = $request->user();
        return response()->json([
            'photos' => true,
            'max_photos_per_dive' => DivePhoto::MAX_PER_DIVE,
            // Videos on Cloudflare Stream (2026-10-20): POST /api/dives/{uuid}/videos.
            'videos' => EarlyAccess::allows($u, 'videos') && \App\Support\CloudflareStream::configured(),
            'max_videos_per_dive' => \App\Models\DiveVideo::MAX_PER_DIVE,
            'max_video_seconds' => \App\Support\CloudflareStream::MAX_SECONDS,
            'max_video_bytes' => \App\Support\CloudflareStream::MAX_BYTES,
            'species' => EarlyAccess::allows($u, 'species'),
            'equipment' => EarlyAccess::allows($u, 'equipment'),
            // "What's this?" on photos: POST /api/photos/{uuid}/identify.
            'species_id' => \App\Support\SpeciesIdentifier::enabled($u),
            // Already agreed to sending photos to Anthropic for "What's this?".
            'species_id_consent' => (bool) $u->ai_photo_consent_at,
            // null = never asked; ask before the first species tag.
            'share_sightings' => $u->share_sightings === null ? null : (bool) $u->share_sightings,
        ]);
    }

    // Photos.

    public function photos(Request $request, string $dive)
    {
        $d = $this->dive($request, $dive);
        return response()->json(['photos' => $d->photos()->with('species')->get()->map->toApi()->values()]);
    }

    /** multipart: photo (jpg/png/webp, 20 MB), caption?, uuid? (client id - a retried upload returns the same photo). */
    public function upload(Request $request, string $dive)
    {
        $d = $this->dive($request, $dive);
        $data = $request->validate(['photo' => DivePhotoStore::RULE, 'caption' => 'nullable|string|max:500', 'uuid' => 'nullable|uuid']);
        $photo = DivePhotoStore::add($d, $request->file('photo'), $data['caption'] ?? null, $data['uuid'] ?? null);
        return response()->json(['photo' => $photo->load('species')->toApi()], 201);
    }

    // Videos (2026-10-20). The app uploads straight to Cloudflare.

    private function ownVideo(Request $request, string $uuid): \App\Models\DiveVideo
    {
        return \App\Models\DiveVideo::where('user_id', $request->user()->id)->where('uuid', $uuid)->with('dive')->firstOrFail();
    }

    private function videosOn(Request $request): void
    {
        abort_unless(EarlyAccess::allows($request->user(), 'videos') && \App\Support\CloudflareStream::configured(), 404);
    }

    public function videos(Request $request, string $dive)
    {
        $this->videosOn($request);
        $d = $this->dive($request, $dive);
        return response()->json(['videos' => $d->videos()->get()->map(fn ($v) => $v->refreshStream()->toApi())->values()]);
    }

    /**
     * JSON {name?, size_bytes?, caption?, uuid?} -> {video, upload_url, upload_kind}.
     * With size_bytes: a resumable tus upload (upload_kind "tus"). Without:
     * a one-shot multipart POST of `file` to upload_url ("direct", 200 MB max).
     * Then POST /api/videos/{uuid}/uploaded.
     */
    public function createVideo(Request $request, string $dive)
    {
        $this->videosOn($request);
        $d = $this->dive($request, $dive);
        $data = $request->validate(['name' => 'nullable|string|max:255', 'size_bytes' => 'nullable|integer|min:1', 'caption' => 'nullable|string|max:500', 'uuid' => 'nullable|uuid']);
        [$video, $url, $kind] = \App\Support\DiveVideoStore::create($d, isset($data['size_bytes']) ? (int) $data['size_bytes'] : null, $data['name'] ?? null, $data['caption'] ?? null, $data['uuid'] ?? null);
        return response()->json(['video' => $video->toApi(), 'upload_url' => $url, 'upload_kind' => $kind], 201);
    }

    public function video(Request $request, string $video)
    {
        $this->videosOn($request);
        return response()->json(['video' => $this->ownVideo($request, $video)->refreshStream()->toApi()]);
    }

    public function videoUploaded(Request $request, string $video)
    {
        $this->videosOn($request);
        return response()->json(['video' => \App\Support\DiveVideoStore::uploaded($this->ownVideo($request, $video))->toApi()]);
    }

    public function updateVideo(Request $request, string $video)
    {
        $this->videosOn($request);
        $v = $this->ownVideo($request, $video);
        $data = $request->validate(['caption' => 'nullable|string|max:500', 'position' => 'nullable|integer|min:0']);
        if ($request->has('caption')) $v->caption = $data['caption'];
        if ($request->has('position')) $v->position = (int) $data['position'];
        $v->save();
        $v->dive?->logUpdated();
        return response()->json(['video' => $v->toApi()]);
    }

    public function deleteVideo(Request $request, string $video)
    {
        $this->videosOn($request);
        \App\Support\DiveVideoStore::delete($this->ownVideo($request, $video));
        return response()->noContent();
    }

    /** {share: true|false} - offer to / take off the dive site's public page (admin review). */
    public function shareVideo(Request $request, string $video)
    {
        $this->videosOn($request);
        $v = $this->ownVideo($request, $video);
        $request->boolean('share') ? \App\Support\DiveVideoStore::share($v) : \App\Support\DiveVideoStore::unshare($v);
        return response()->json(['video' => $v->fresh()->toApi()]);
    }

    public function updatePhoto(Request $request, string $photo)
    {
        $p = $this->photo($request, $photo);
        $data = $request->validate(['caption' => 'nullable|string|max:500', 'position' => 'nullable|integer|min:0']);
        if ($request->has('caption')) $p->caption = $data['caption'];
        if ($request->has('position')) $p->position = (int) $data['position'];
        $p->save();
        $p->dive?->logUpdated();
        return response()->json(['photo' => $p->toApi()]);
    }

    public function deletePhoto(Request $request, string $photo)
    {
        DivePhotoStore::delete($this->photo($request, $photo));
        return response()->noContent();
    }

    /** {share: true|false} - offer to / take off the dive site's public page. */
    public function sharePhoto(Request $request, string $photo)
    {
        $p = $this->photo($request, $photo);
        $request->validate(['share' => 'required|boolean']);
        $request->boolean('share') ? DivePhotoStore::share($p) : DivePhotoStore::unshare($p);
        return response()->json(['photo' => $p->fresh('species')->toApi()]);
    }

    public function photoFile(Request $request, string $photo, string $size)
    {
        $p = DivePhoto::where('user_id', $request->user()->id)->where('uuid', $photo)->firstOrFail();
        $path = $p->path($size === 'thumb' ? 'thumb' : 'web');
        abort_unless(is_file($path), 404);
        return response()->file($path, ['Content-Type' => 'image/webp', 'Cache-Control' => 'private, max-age=31536000, immutable']);
    }

    // Marine life.

    /** Catalog + the diver's own. ?updated_since=ISO8601 returns only what changed (deleted own species: removed_uuids is not tracked - refetch fully now and then). */
    public function species(Request $request)
    {
        $this->early($request, 'species');
        $q = Species::visibleTo($request->user()->id)->orderBy('common_name');
        if ($since = $request->query('updated_since')) $q->where('updated_at', '>', \Carbon\Carbon::parse($since));
        return response()->json([
            'categories' => collect(Species::CATEGORIES)->map(fn ($label, $slug) => ['slug' => $slug, 'label' => $label])->values(),
            'species' => $q->get()->map->toApi()->values(),
            'server_time' => now()->toIso8601String(),
        ]);
    }

    public function createSpecies(Request $request)
    {
        $this->early($request, 'species');
        $s = new Species($this->speciesData($request));
        $s->user_id = $request->user()->id;
        $s->save();
        return response()->json(['species' => $s->toApi()], 201);
    }

    public function updateSpecies(Request $request, string $uuid)
    {
        $this->early($request, 'species');
        $s = Species::where('user_id', $request->user()->id)->where('uuid', $uuid)->firstOrFail();
        $s->fill($this->speciesData($request))->save();
        return response()->json(['species' => $s->toApi()]);
    }

    public function deleteSpecies(Request $request, string $uuid)
    {
        $this->early($request, 'species');
        $s = Species::where('user_id', $request->user()->id)->where('uuid', $uuid)->firstOrFail();
        $diveIds = DB::table('dive_species')->where('species_id', $s->id)->pluck('dive_id')->all();
        DB::table('dive_species')->where('species_id', $s->id)->delete();
        DB::table('dive_photo_species')->where('species_id', $s->id)->delete();
        $s->delete();
        Dive::logUpdatedIds($diveIds);
        return response()->noContent();
    }

    /** {species: [{uuid, count?}]} - the dive's whole list. */
    public function setDiveSpecies(Request $request, string $dive)
    {
        $this->early($request, 'species');
        $d = $this->dive($request, $dive);
        $data = $request->validate(['species' => 'present|array', 'species.*.uuid' => 'required|string', 'species.*.count' => 'nullable|integer|min:1|max:9999']);
        SpeciesTagger::syncDive($d, collect($data['species'])->mapWithKeys(fn ($s) => [$s['uuid'] => $s['count'] ?? null])->all());
        return response()->json(['species' => self::diveSpecies($d)]);
    }

    /** {species: [uuid]} - the photo's whole list; each also lands on the dive. */
    public function setPhotoSpecies(Request $request, string $photo)
    {
        $this->early($request, 'species');
        $p = $this->photo($request, $photo);
        $data = $request->validate(['species' => 'present|array', 'species.*' => 'string']);
        SpeciesTagger::syncPhoto($p, $data['species']);
        return response()->json(['photo' => $p->fresh('species')->toApi(), 'dive_species' => self::diveSpecies($p->dive)]);
    }

    /** "What's this?" - AI species suggestions for a photo; the app then PUTs the photo's species as usual. */
    public function identifyPhoto(Request $request, string $photo)
    {
        abort_unless(\App\Support\SpeciesIdentifier::enabled($request->user()), 404);
        // One-time consent before a photo goes to Anthropic (send consent: true with the answer).
        if (\App\Support\SpeciesIdentifier::needsConsent($request->user())) {
            if (!$request->boolean('consent')) {
                return response()->json(['consent_required' => true, 'provider' => 'Anthropic (Claude)',
                    'message' => 'Your photo is sent to Anthropic\'s Claude to suggest which species are in it. Nothing is tagged unless you confirm.'], 409);
            }
            $request->user()->forceFill(['ai_photo_consent_at' => now()])->save();
        }
        return response()->json(\App\Support\SpeciesIdentifier::identify($this->photo($request, $photo), $request->boolean('fresh')));
    }

    /** {share: bool} - the answer to "share your sightings with the community?". */
    public function shareSightings(Request $request)
    {
        $request->validate(['share' => 'required|boolean']);
        $request->user()->forceFill(['share_sightings' => $request->boolean('share')])->save();
        return response()->json(['share_sightings' => (bool) $request->user()->share_sightings]);
    }

    // Equipment.

    public function equipment(Request $request)
    {
        $this->early($request, 'equipment');
        $uid = $request->user()->id;
        $items = EquipmentItem::where('user_id', $uid)->orderBy('name')->get();
        return response()->json([
            'categories' => collect(EquipmentItem::CATEGORIES)->map(fn ($v, $slug) => ['slug' => $slug, 'label' => $v[0]])->values(),
            'items' => $items->map->toApi()->values(),
            'configs' => EquipmentConfig::where('user_id', $uid)->with('items')->orderBy('name')->get()->map->toApi()->values(),
            'suggested_computers' => EquipmentController::suggestedComputers($uid, $items),
        ]);
    }

    public function createItem(Request $request)
    {
        $this->early($request, 'equipment');
        $item = new EquipmentItem($this->itemData($request));
        $item->user_id = $request->user()->id;
        $item->retired_at = $request->input('retired_on') ?: null;
        $item->save();
        $this->itemPhoto($request, $item);
        return response()->json(['item' => $item->toApi()], 201);
    }

    /** POST with multipart (to carry a photo) or PATCH with JSON. */
    public function updateItem(Request $request, string $uuid)
    {
        $this->early($request, 'equipment');
        $item = EquipmentItem::where('user_id', $request->user()->id)->where('uuid', $uuid)->firstOrFail();
        $item->fill($this->itemData($request, true));
        // retired_on (YYYY-MM-DD or null) wins; the older boolean still works.
        if ($request->has('retired_on')) $item->retired_at = $request->input('retired_on') ?: null;
        elseif ($request->has('retired')) $item->retired_at = $request->boolean('retired') ? ($item->retired_at ?? now()->toDateString()) : null;
        $item->save();
        $this->itemPhoto($request, $item);
        return response()->json(['item' => $item->toApi()]);
    }

    public function deleteItem(Request $request, string $uuid)
    {
        $this->early($request, 'equipment');
        $item = EquipmentItem::where('user_id', $request->user()->id)->where('uuid', $uuid)->firstOrFail();
        $diveIds = DB::table('dive_equipment')->where('equipment_item_id', $item->id)->pluck('dive_id')->all();
        DB::table('dive_equipment')->where('equipment_item_id', $item->id)->delete();
        DB::table('equipment_config_items')->where('equipment_item_id', $item->id)->delete();
        $item->delete();
        Dive::logUpdatedIds($diveIds);
        return response()->noContent();
    }

    public function itemPhotoFile(Request $request, string $uuid)
    {
        $this->early($request, 'equipment');
        $item = EquipmentItem::where('user_id', $request->user()->id)->where('uuid', $uuid)->firstOrFail();
        abort_unless($item->photoPath() && is_file($item->photoPath()), 404);
        return response()->file($item->photoPath(), ['Content-Type' => 'image/webp']);
    }

    /** {name, notes?, items: [uuid]} */
    public function saveConfig(Request $request, ?string $uuid = null)
    {
        $this->early($request, 'equipment');
        $data = $request->validate(['name' => 'required|string|max:120', 'notes' => 'nullable|string|max:2000', 'items' => 'present|array', 'items.*' => 'string']);
        $uid = $request->user()->id;
        $config = $uuid ? EquipmentConfig::where('user_id', $uid)->where('uuid', $uuid)->firstOrFail() : new EquipmentConfig(['user_id' => $uid]);
        $config->user_id = $uid;
        $config->fill(['name' => $data['name'], 'notes' => $data['notes'] ?? null])->save();
        $config->items()->sync(EquipmentItem::where('user_id', $uid)->whereIn('uuid', $data['items'])->pluck('id'));
        return response()->json(['config' => $config->load('items')->toApi()], $uuid ? 200 : 201);
    }

    public function deleteConfig(Request $request, string $uuid)
    {
        $this->early($request, 'equipment');
        $config = EquipmentConfig::where('user_id', $request->user()->id)->where('uuid', $uuid)->firstOrFail();
        $config->items()->detach();
        $config->delete();
        return response()->noContent();
    }

    /** {items: [uuid], config?: uuid} - the dive's whole gear list (computers matched by serial are added by the server). */
    public function setDiveEquipment(Request $request, string $dive)
    {
        $this->early($request, 'equipment');
        $d = $this->dive($request, $dive);
        $data = $request->validate(['items' => 'present|array', 'items.*' => 'string', 'config' => 'nullable|string']);
        $uid = $request->user()->id;
        $configId = !empty($data['config']) ? EquipmentConfig::where('user_id', $uid)->where('uuid', $data['config'])->value('id') : null;
        $ids = EquipmentItem::where('user_id', $uid)->whereIn('uuid', $data['items'])->pluck('id');
        $d->equipment()->sync($ids->mapWithKeys(fn ($id) => [$id => ['equipment_config_id' => $configId]])->all());
        $d->logUpdated();
        return response()->json(['equipment' => EquipmentController::diveList($d)]);
    }

    // Helpers shared with the sync JSON.

    public static function diveSpecies(Dive $dive): array
    {
        $species = $dive->relationLoaded('species') ? $dive->species->sortBy('common_name') : $dive->species()->orderBy('common_name')->get();
        return $species->map(fn ($s) => ['uuid' => $s->uuid, 'count' => $s->pivot->count])->values()->all();
    }

    private function speciesData(Request $request): array
    {
        $rank = 'nullable|string|max:60';
        $d = $request->validate([
            'common_name' => 'required|string|max:120', 'category' => ['required', Rule::in(array_keys(Species::CATEGORIES))],
            'kingdom' => $rank, 'phylum' => $rank, 'class' => $rank, 'subclass' => $rank, 'division' => $rank, 'order' => $rank,
            'family' => $rank, 'genus' => $rank, 'scientific_name' => 'nullable|string|max:120', 'description' => 'nullable|string|max:2000',
            'other_names' => 'nullable|array', 'other_names.*' => 'string|max:120',
        ]);
        if (array_key_exists('order', $d)) { $d['taxon_order'] = $d['order']; unset($d['order']); }
        return $d;
    }

    private function itemData(Request $request, bool $partial = false): array
    {
        $req = $partial ? 'sometimes|required' : 'required';
        $d = $request->validate([
            'category' => [$partial ? 'sometimes' : 'required', Rule::in(array_keys(EquipmentItem::CATEGORIES))],
            'name' => "$req|string|max:120",
            'manufacturer' => 'nullable|string|max:120', 'model' => 'nullable|string|max:120', 'serial_number' => 'nullable|string|max:120',
            'purchased_on' => 'nullable|date', 'last_service_on' => 'nullable|date', 'retired_on' => 'nullable|date', 'notes' => 'nullable|string|max:5000',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:20480',
        ]);
        unset($d['photo'], $d['retired_on']);
        return $d;
    }

    private function itemPhoto(Request $request, EquipmentItem $item): void
    {
        if (!$request->hasFile('photo')) return;
        $name = $item->uuid . '_' . time() . '.webp';
        if (SitePhoto::resizeTo($request->file('photo')->getRealPath(), [storage_path('app/equipment/' . $item->user_id . '/' . $name) => 1200])) {
            if ($item->photoPath()) @unlink($item->photoPath());
            $item->photo = $name;
            $item->save();
        }
    }
}

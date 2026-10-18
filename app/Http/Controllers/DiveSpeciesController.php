<?php

namespace App\Http\Controllers;

use App\Models\Dive;
use App\Models\DivePhoto;
use App\Support\SpeciesTagger;
use Illuminate\Http\Request;

/** Species tags on the dive page - the dive's Marine life card and the photo viewer (Pablo, 2026-10-17). */
class DiveSpeciesController extends Controller
{
    public function tagDive(Request $request, $id)
    {
        $dive = Dive::where('user_id', $request->user()->id)->findOrFail((int) $id);
        $data = $request->validate(['species' => 'required|string', 'count' => 'nullable|integer|min:1|max:9999']);
        SpeciesTagger::tagDive($dive, SpeciesTagger::find($request->user()->id, $data['species']), $data['count'] ?? null);
        return response()->json(['species' => self::diveList($dive)]);
    }

    public function untagDive(Request $request, $id, string $uuid)
    {
        $dive = Dive::where('user_id', $request->user()->id)->findOrFail((int) $id);
        SpeciesTagger::untagDive($dive, SpeciesTagger::find($request->user()->id, $uuid));
        return response()->json(['species' => self::diveList($dive)]);
    }

    public function tagPhoto(Request $request, string $photo)
    {
        $p = DivePhoto::where('user_id', $request->user()->id)->where('uuid', $photo)->firstOrFail();
        $data = $request->validate(['species' => 'required|string']);
        SpeciesTagger::tagPhoto($p, SpeciesTagger::find($request->user()->id, $data['species']));
        return response()->json(['photo' => DivePhotoController::card($p->fresh('species')), 'species' => self::diveList($p->dive)]);
    }

    public function untagPhoto(Request $request, string $photo, string $uuid)
    {
        $p = DivePhoto::where('user_id', $request->user()->id)->where('uuid', $photo)->firstOrFail();
        SpeciesTagger::untagPhoto($p, SpeciesTagger::find($request->user()->id, $uuid));
        return response()->json(['photo' => DivePhotoController::card($p->fresh('species')), 'species' => self::diveList($p->dive)]);
    }

    /** "What's this?" - AI species suggestions for one of the diver's photos (2026-10-18). */
    public function identify(Request $request, string $photo)
    {
        abort_unless(\App\Support\SpeciesIdentifier::enabled($request->user()), 404);
        $p = DivePhoto::where('user_id', $request->user()->id)->where('uuid', $photo)->firstOrFail();
        return response()->json(\App\Support\SpeciesIdentifier::identify($p, $request->boolean('fresh')));
    }

    /** The diver's answer to "share your sightings with the community?". */
    public function shareSightings(Request $request)
    {
        $request->user()->forceFill(['share_sightings' => $request->boolean('share')])->save();
        return $request->wantsJson() ? response()->json(['share_sightings' => (bool) $request->user()->share_sightings]) : back()->with('status', 'Saved.');
    }

    /** What the dive's Marine life card shows. */
    public static function diveList(Dive $dive): array
    {
        return $dive->species()->orderBy('common_name')->get()->map(fn ($s) => [
            'uuid' => $s->uuid, 'name' => $s->common_name, 'scientific' => $s->scientific_name,
            'category' => $s->categoryLabel(), 'count' => $s->pivot->count,
            'url' => route('Species.show', $s->uuid),
        ])->values()->all();
    }
}

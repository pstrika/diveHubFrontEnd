<?php

namespace App\Http\Controllers;

use App\Models\Dive;
use App\Models\DivePhoto;
use App\Models\Species;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Marine life pages (Pablo, 2026-10-17): the catalog with what you've seen,
 * one species with its classification and your sightings, and your own
 * species (private to you). Early access: 'early:species'.
 */
class SpeciesController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $species = Species::visibleTo($userId)->orderBy('common_name')->get();
        // Times seen = dives tagged, this diver's dives only.
        $seen = DB::table('dive_species')->join('dives', 'dives.id', '=', 'dive_species.dive_id')
            ->where('dives.user_id', $userId)->whereNull('dives.deleted_at')
            ->groupBy('dive_species.species_id')
            ->selectRaw('dive_species.species_id, COUNT(*) AS n, MAX(dives.started_at) AS last_seen')
            ->get()->keyBy('species_id');

        $groups = [];
        foreach (Species::CATEGORIES as $slug => $label) {
            $list = $species->where('category', $slug)->values();
            if ($list->isNotEmpty()) $groups[$slug] = ['label' => $label, 'species' => $list];
        }

        return view('pages.logbook.species.index', [
            'groups' => $groups,
            'seen' => $seen,
            'seenCount' => $seen->count(),
            'shareSightings' => $request->user()->share_sightings,
        ]);
    }

    public function show(Request $request, string $uuid)
    {
        $userId = $request->user()->id;
        $species = Species::visibleTo($userId)->where('uuid', $uuid)->firstOrFail();

        $dives = Dive::where('user_id', $userId)->select(Dive::LIST_COLUMNS)
            ->whereIn('id', DB::table('dive_species')->where('species_id', $species->id)->pluck('dive_id'))
            ->orderByDesc('started_at')->get();
        Dive::preloadSites($dives);
        $dives->load('userSite');
        $counts = DB::table('dive_species')->where('species_id', $species->id)->whereIn('dive_id', $dives->pluck('id'))->pluck('count', 'dive_id');

        $photos = DivePhoto::where('user_id', $userId)
            ->whereIn('id', DB::table('dive_photo_species')->where('species_id', $species->id)->pluck('dive_photo_id'))
            ->with('dive:id,started_at')->latest('id')->take(40)->get();

        return view('pages.logbook.species.show', compact('species', 'dives', 'counts', 'photos'));
    }

    public function create()
    {
        return view('pages.logbook.species.form', ['species' => new Species(['category' => 'reef_fish', 'kingdom' => 'Animalia'])]);
    }

    public function store(Request $request)
    {
        $species = new Species($this->validated($request));
        $species->user_id = $request->user()->id;
        $species->save();
        return redirect()->route('Species.show', $species->uuid)->with('status', 'Species added - only you can see it.');
    }

    public function edit(Request $request, string $uuid)
    {
        return view('pages.logbook.species.form', ['species' => $this->own($request, $uuid)]);
    }

    public function update(Request $request, string $uuid)
    {
        $species = $this->own($request, $uuid);
        $species->fill($this->validated($request))->save();
        return redirect()->route('Species.show', $species->uuid)->with('status', 'Saved.');
    }

    public function destroy(Request $request, string $uuid)
    {
        $species = $this->own($request, $uuid);
        $diveIds = DB::table('dive_species')->where('species_id', $species->id)->pluck('dive_id');
        DB::table('dive_species')->where('species_id', $species->id)->delete();
        DB::table('dive_photo_species')->where('species_id', $species->id)->delete();
        $species->delete();
        Dive::logUpdatedIds($diveIds->all());
        return redirect()->route('Species.index')->with('status', 'Species deleted.');
    }

    /**
     * Review the Wikimedia Commons candidates (species:fetch-photos): keep
     * or skip each. Catalog-wide, so admins only.
     */
    public function photoReview(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);
        $filter = in_array($request->query('show'), ['candidate', 'approved', 'rejected', 'missing'], true) ? $request->query('show') : 'candidate';
        $q = Species::whereNull('user_id')->orderBy('category')->orderBy('common_name');
        $filter === 'missing' ? $q->whereNull('photo_status') : $q->where('photo_status', $filter);
        $counts = Species::whereNull('user_id')->selectRaw("COALESCE(photo_status, 'missing') st, COUNT(*) n")->groupBy('st')->pluck('n', 'st');
        return view('pages.logbook.species.photos', ['species' => $q->get(), 'filter' => $filter, 'counts' => $counts]);
    }

    public function photoDecide(Request $request, string $uuid)
    {
        abort_unless($request->user()->isAdmin(), 403);
        $s = Species::whereNull('user_id')->where('uuid', $uuid)->firstOrFail();
        $decision = $request->validate(['decision' => 'required|in:approved,rejected,candidate'])['decision'];
        abort_unless($s->photo, 422);
        $s->forceFill(['photo_status' => $decision])->save();
        return $request->wantsJson() ? response()->json(['status' => $decision]) : back();
    }

    private function own(Request $request, string $uuid): Species
    {
        return Species::where('user_id', $request->user()->id)->where('uuid', $uuid)->firstOrFail();
    }

    private function validated(Request $request): array
    {
        $rank = 'nullable|string|max:60';
        $data = $request->validate([
            'common_name' => 'required|string|max:120',
            'category' => ['required', Rule::in(array_keys(Species::CATEGORIES))],
            'kingdom' => $rank, 'phylum' => $rank, 'class' => $rank, 'subclass' => $rank, 'division' => $rank,
            'taxon_order' => $rank, 'family' => $rank, 'genus' => $rank,
            'scientific_name' => 'nullable|string|max:120',
            'description' => 'nullable|string|max:2000',
        ]);
        return $data;
    }
}

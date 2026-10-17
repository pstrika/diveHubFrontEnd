<?php

namespace App\Http\Controllers;

use App\Models\Dive;
use App\Models\DivePhoto;
use App\Models\Site;
use App\Models\UserSite;
use App\Support\EarlyAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Site journal (Pablo, 2026-10-17: "show in the My Sites pictures and
 * species (and when they were seen)"): every site the diver has dived -
 * catalog or their own - with their dives, photos and species there.
 * Linked from My Visited Sites and Logbook > My sites. The diver's own
 * data only.
 */
class SiteJournalController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $dives = Dive::where('user_id', $userId)->where(fn ($q) => $q->whereNotNull('site_id')->orWhereNotNull('user_site_id'))
            ->get(['id', 'site_id', 'user_site_id', 'started_at']);
        $photoCounts = DivePhoto::where('user_id', $userId)->groupBy('dive_id')->selectRaw('dive_id, COUNT(*) n')->pluck('n', 'dive_id');
        $speciesOn = EarlyAccess::allows($request->user(), 'species');
        $speciesByDive = $speciesOn
            ? DB::table('dive_species')->whereIn('dive_id', $dives->pluck('id'))->get(['dive_id', 'species_id'])->groupBy('dive_id')
            : collect();

        $rows = [];
        foreach ($dives as $d) {
            $key = $d->site_id ? 'site-' . $d->site_id : 'mine-' . $d->user_site_id;
            $r = $rows[$key] ?? ['kind' => $d->site_id ? 'site' : 'mine', 'id' => $d->site_id ?: $d->user_site_id, 'dives' => 0, 'photos' => 0, 'species' => [], 'last' => null];
            $r['dives']++;
            $r['photos'] += (int) ($photoCounts[$d->id] ?? 0);
            foreach ($speciesByDive[$d->id] ?? [] as $s) $r['species'][$s->species_id] = true;
            if (!$r['last'] || $d->started_at->gt($r['last'])) $r['last'] = $d->started_at;
            $rows[$key] = $r;
        }
        $names = Site::whereIn('id', collect($rows)->where('kind', 'site')->pluck('id'))->get(['id', 'name', 'location'])->keyBy('id');
        $mine = UserSite::whereIn('id', collect($rows)->where('kind', 'mine')->pluck('id'))->get()->keyBy('id');
        $rows = collect($rows)->map(function ($r) use ($names, $mine) {
            $s = $r['kind'] === 'site' ? $names[$r['id']] ?? null : $mine[$r['id']] ?? null;
            $r['name'] = $s?->name ?? 'Unknown site';
            $r['city'] = $r['kind'] === 'site' ? ($s ? \App\Support\SiteLocations::name($s->location) : null) : $s?->cityLabel();
            $r['species'] = count($r['species']);
            return $r;
        })->sortByDesc('dives')->values();

        return view('pages.logbook.journal.index', ['rows' => $rows, 'speciesOn' => $speciesOn]);
    }

    public function show(Request $request, string $kind, int $id)
    {
        $userId = $request->user()->id;
        abort_unless(in_array($kind, ['site', 'mine'], true), 404);
        if ($kind === 'site') {
            $site = Site::findOrFail($id);
            $name = $site->name; $city = \App\Support\SiteLocations::name($site->location); $publicUrl = $site->publicUrl();
        } else {
            $site = UserSite::where('user_id', $userId)->findOrFail($id);
            $name = $site->name; $city = $site->cityLabel(); $publicUrl = null;
        }

        $dives = Dive::where('user_id', $userId)->select(Dive::LIST_COLUMNS)
            ->where($kind === 'site' ? 'site_id' : 'user_site_id', $id)
            ->orderByDesc('started_at')->get();
        $photos = DivePhoto::where('user_id', $userId)->whereIn('dive_id', $dives->pluck('id'))
            ->with('dive:id,started_at')->orderByDesc('dive_id')->orderBy('position')->get();

        $species = collect();
        $speciesOn = EarlyAccess::allows($request->user(), 'species');
        if ($speciesOn && $dives->isNotEmpty()) {
            $species = DB::table('dive_species')->join('species', 'species.id', '=', 'dive_species.species_id')
                ->join('dives', 'dives.id', '=', 'dive_species.dive_id')
                ->whereIn('dive_species.dive_id', $dives->pluck('id'))
                ->groupBy('species.id', 'species.uuid', 'species.common_name', 'species.scientific_name', 'species.category')
                ->selectRaw('species.uuid, species.common_name, species.scientific_name, species.category, COUNT(*) AS dives, MIN(dives.started_at) AS first_seen, MAX(dives.started_at) AS last_seen')
                ->orderByDesc('dives')->orderBy('species.common_name')->get();
        }

        return view('pages.logbook.journal.show', compact('kind', 'id', 'name', 'city', 'publicUrl', 'dives', 'photos', 'species', 'speciesOn'));
    }
}

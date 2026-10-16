<?php

namespace App\Http\Controllers;

use App\Models\Boat;
use App\Models\Dive;
use App\Models\DiveChange;
use App\Models\Operator;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Diver logbook (Pablo, 2026-10-14): browsing and annotating the dives the
 * native uploader apps sync in (see DiveUploadController for how they get
 * here). Registered users only, own dives only - no admin override, no
 * public sharing, same posture as deco_plans.
 *
 * Phase 1 (this): list + detail view with the depth/PPO2/temp/gas-switch
 * chart, plus editable "conditions" (operator, buddies, weather, etc - see
 * `conditions` on the Dive model). Manual dive entry, logbook import, own
 * dive sites, pictures/equipment/SAC, and marine life sightings are
 * deliberately out of scope here - see docs/roadmap.md #9 for the agreed
 * future phases instead of re-deciding scope ad hoc later.
 */
class LogbookController extends Controller
{
    /**
     * Most recent dives first, with filter chips (Pablo, 2026-10-15:
     * circuit, recreational/technical, tags, dive type - each shown only
     * when the diver's own logbook has something to filter by) and
     * paginated. Filtering happens in PHP over the diver's whole logbook
     * rather than in SQL: classification and mode are derived (depth,
     * deco samples, tank usage tags) and tags/dive types live in JSON -
     * a personal logbook is a few hundred rows at most, so loading it
     * whole is cheaper than teaching SQL about those rules.
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        // No profile JSON here (Dive::LIST_COLUMNS): classification reads the
        // stored entered_deco flag, and a full logbook is ~900 rows.
        $all = Dive::where('user_id', $userId)->select(Dive::LIST_COLUMNS)->orderByDesc('started_at')->orderByDesc('id')->get();
        $total = $all->count();

        // Chronological number (oldest = 1) before any filtering, so a
        // filtered list still shows each dive's real number.
        $seq = $total;
        foreach ($all as $dive) {
            $dive->listSeq = $seq--;
        }

        $stats = [
            'total' => $total,
            'total_time_seconds' => (int) $all->sum('duration_seconds'),
            'max_depth_m' => $all->max('max_depth_m'),
        ];

        $diveTypeLabels = ['drift' => 'Drift', 'training' => 'Training', 'photo_video' => 'Photo/Video', 'cave' => 'Cave', 'cavern' => 'Cavern', 'wall' => 'Wall', 'reef' => 'Reef', 'wreck' => 'Wreck'];

        $filters = [
            'circuit' => in_array($request->query('circuit'), ['cc', 'oc'], true) ? $request->query('circuit') : null,
            'class' => in_array($request->query('class'), ['recreational', 'technical'], true) ? $request->query('class') : null,
            'tag' => (string) $request->query('tag', '') !== '' ? (string) $request->query('tag') : null,
            'type' => isset($diveTypeLabels[$request->query('type')]) ? $request->query('type') : null,
        ];

        $matches = function (Dive $dive, array $f): bool {
            if ($f['circuit'] === 'cc' && !$dive->isCcr()) return false;
            if ($f['circuit'] === 'oc' && $dive->isCcr()) return false;
            if ($f['class'] && $dive->classification() !== $f['class']) return false;
            if ($f['tag'] && !in_array($f['tag'], $dive->tags ?? [], true)) return false;
            if ($f['type'] && !in_array($f['type'], $dive->conditions['dive_type'] ?? [], true)) return false;
            return true;
        };

        $filtered = $all->filter(fn (Dive $dive) => $matches($dive, $filters))->values();

        // Chip options are computed against the dives that pass the OTHER
        // active filters, so no chip is ever offered that would return
        // nothing (Pablo, 2026-10-15: "do not show dive types, tags or
        // anything to filter that will return no dives at all"). A chip
        // row with nothing to choose between is dropped entirely.
        $within = function (string $except) use ($all, $filters, $matches) {
            $others = $filters; $others[$except] = null;
            return $all->filter(fn (Dive $dive) => $matches($dive, $others));
        };
        $hasCcr = false; $hasOc = false;
        foreach ($within('circuit') as $dive) { $dive->isCcr() ? $hasCcr = true : $hasOc = true; }
        $hasTec = false; $hasRec = false;
        foreach ($within('class') as $dive) { $dive->classification() === 'technical' ? $hasTec = true : $hasRec = true; }
        $typeCounts = [];
        foreach ($within('type') as $dive) foreach ($dive->conditions['dive_type'] ?? [] as $t) $typeCounts[$t] = ($typeCounts[$t] ?? 0) + 1;
        $typeOptions = [];
        foreach ($diveTypeLabels as $k => $label) if (isset($typeCounts[$k])) $typeOptions[$k] = $label;
        $tagCounts = [];
        foreach ($within('tag') as $dive) foreach ($dive->tags ?? [] as $t) $tagCounts[$t] = ($tagCounts[$t] ?? 0) + 1;
        ksort($tagCounts);
        $tagOptions = [];
        foreach ($tagCounts as $t => $n) $tagOptions[$t] = $t;
        // Keep an active chip visible even if it is the only one left.
        if ($filters['tag'] && !isset($tagOptions[$filters['tag']])) $tagOptions[$filters['tag']] = $filters['tag'];
        if ($filters['type'] && !isset($typeOptions[$filters['type']])) $typeOptions[$filters['type']] = $diveTypeLabels[$filters['type']];

        $perPage = 20;
        $page = max(1, (int) $request->query('page', 1));
        $pageDives = $filtered->forPage($page, $perPage)->values();
        // One catalog query for the page's sites, one for the diver's own.
        Dive::preloadSites($pageDives);
        $pageDives->load('userSite');
        $dives = new \Illuminate\Pagination\LengthAwarePaginator(
            $pageDives,
            $filtered->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Row decorations: operator logo (live catalog match by name, like
        // DiveDetails), the site's city, and the wishlist bookmark.
        $operatorLogos = Operator::whereNotNull('logoUrl')->get(['operatorName', 'logoUrl'])
            ->mapWithKeys(fn ($o) => [mb_strtolower($o->operatorName) => asset('assets') . $o->logoUrl]);
        $wishedSiteIds = \App\Models\WishedSite::where('userId', $userId)->pluck('siteId')->map(fn ($v) => (int) $v)->flip();

        return view('pages.Logbook', [
            'dives' => $dives,
            'stats' => $stats,
            'filters' => $filters,
            'showCircuitFilter' => $hasCcr && $hasOc,
            'showClassFilter' => $hasTec && $hasRec,
            'tagOptions' => $tagOptions,
            'typeOptions' => $typeOptions,
            'operatorLogos' => $operatorLogos,
            'wishedSiteIds' => $wishedSiteIds,
        ]);
    }

    /**
     * Delete a dive (Pablo, 2026-10-16). Soft delete + change-feed row via
     * Dive::deleteAndLog(); the row stays as a tombstone so a later
     * re-sync from the computer can't bring it back.
     */
    public function destroy(Request $request, $id)
    {
        $dive = Dive::where('user_id', $request->user()->id)->findOrFail($id);
        $title = $dive->title();
        $dive->deleteAndLog();

        return redirect()->route('Logbook')->with('status', 'Deleted "' . $title . '" (' . $dive->started_at->format('M j, Y') . ').');
    }

    /**
     * Merge the next dive into this one (Pablo, 2026-10-16) - see
     * App\Support\DiveMerger for what "merge" means.
     */
    public function merge(Request $request, $id)
    {
        $dive = Dive::where('user_id', $request->user()->id)->findOrFail($id);
        $data = $request->validate(['with_id' => 'required|integer']);
        $other = Dive::where('user_id', $request->user()->id)->findOrFail($data['with_id']);

        $candidate = \App\Support\DiveMerger::nextCandidate($dive);
        if (!$candidate || $candidate->id !== $other->id) {
            return redirect()->route('DiveDetails', $dive->id)->with('error', 'That dive is not right after this one any more.');
        }

        $merged = \App\Support\DiveMerger::merge($dive, $other);

        return redirect()->route('DiveDetails', $merged->id)->with('status', 'Merged into one dive of ' . gmdate('H:i', $merged->duration_seconds) . '.');
    }

    // ----- Manual entry (Pablo, 2026-10-16: "add a dive manually, with no
    // depth vs time chart - just the rest of the information") -----

    private function factRules(): array
    {
        return [
            'date' => 'required|date_format:Y-m-d',
            'time' => 'required|date_format:H:i',
            'duration_min' => 'required|integer|min:1|max:1440',
            'units' => 'required|in:metric,imperial',
            'max_depth' => 'required|numeric|min:0|max:1000',
            'avg_depth' => 'nullable|numeric|min:0|max:1000',
            'min_water_temp' => 'nullable|numeric|min:-5|max:150',
            'dive_mode' => 'required|in:open_circuit,closed_circuit',
            'sidemount' => 'nullable|boolean',
            'gases' => 'nullable|array|max:6',
            'gases.*.o2' => 'nullable|integer|min:4|max:100',
            'gases.*.he' => 'nullable|integer|min:0|max:96',
            'computer_model' => 'nullable|string|max:100',
            'site_id' => 'nullable|integer',
            'user_site_id' => 'nullable|integer',
            'site_name_custom' => 'nullable|string|max:150',
            'notes' => 'nullable|string|max:5000',
            'classification' => 'nullable|in:recreational,technical',
        ];
    }

    /** Validated manual-entry input -> the computer-owned columns, metric. */
    private function factAttributes(array $d): array
    {
        $imperial = $d['units'] === 'imperial';
        $depth = fn ($v) => $v === null || $v === '' ? null : round($imperial ? $v * 0.3048 : $v, 2);
        $temp = fn ($v) => $v === null || $v === '' ? null : round($imperial ? ($v - 32) * 5 / 9 : $v, 1);

        $mixes = [];
        foreach ($d['gases'] ?? [] as $i => $g) {
            $o2 = (int) ($g['o2'] ?? 0);
            if ($o2 <= 0) continue;
            $he = (int) ($g['he'] ?? 0);
            // Closed circuit: the first gas is the diluent (usage 2, the
            // same tag Shearwater reports), the rest bailout/deco.
            $mixes[] = ['oxygen' => $o2, 'helium' => $he, 'nitrogen' => max(0, 100 - $o2 - $he), 'usage' => ($d['dive_mode'] === 'closed_circuit' && count($mixes) === 0) ? 2 : 0];
        }
        $tanks = null;
        if (!empty($d['sidemount']) && $d['dive_mode'] === 'open_circuit') {
            // Two cylinders tagged the way the computer would tag them, so
            // Dive::isSidemount() and the Gases card pair them up.
            $tanks = [
                ['usage' => 'sidemount', 'volume' => 0, 'workPressure' => 0, 'beginPressure' => 0, 'endPressure' => 0, 'gasMix' => 0],
                ['usage' => 'sidemount', 'volume' => 0, 'workPressure' => 0, 'beginPressure' => 0, 'endPressure' => 0, 'gasMix' => 0],
            ];
        }

        return [
            'started_at' => $d['date'] . ' ' . $d['time'] . ':00',
            'duration_seconds' => (int) $d['duration_min'] * 60,
            'max_depth_m' => $depth($d['max_depth']),
            'avg_depth_m' => $depth($d['avg_depth'] ?? null),
            'min_water_temp_c' => $temp($d['min_water_temp'] ?? null),
            'dive_mode' => $d['dive_mode'],
            'gas_mixes' => $mixes ?: null,
            'tanks' => $tanks,
            'computer_model' => $d['computer_model'] ?? null,
        ];
    }

    private function applySiteChoice(Dive $dive, array $d): void
    {
        $userId = $dive->user_id;
        $dive->site_id = !empty($d['site_id']) ? (int) $d['site_id'] : null;
        $usId = !empty($d['user_site_id']) ? (int) $d['user_site_id'] : null;
        $dive->user_site_id = (!$dive->site_id && $usId && \App\Models\UserSite::where('user_id', $userId)->where('id', $usId)->exists()) ? $usId : null;
        $conditions = $dive->conditions ?? [];
        $conditions['site_name_custom'] = ($dive->site_id || $dive->user_site_id) ? null : ($d['site_name_custom'] ?? null);
        $conditions['classification'] = $d['classification'] ?? null;
        $dive->conditions = array_filter($conditions, fn ($v) => $v !== null && $v !== '' && $v !== []) ?: null;
    }

    private function siteOptions(int $userId)
    {
        return Site::orderBy('name')->get(['id', 'name'])
            ->map(fn ($s) => ['id' => $s->id, 'name' => $s->name, 'custom' => false])
            ->concat(\App\Models\UserSite::where('user_id', $userId)->orderBy('name')->get(['id', 'name'])
                ->map(fn ($s) => ['id' => $s->id, 'name' => $s->name, 'custom' => true]))
            ->values();
    }

    public function create(Request $request)
    {
        return view('pages.logbook.dive-form', [
            'dive' => null,
            'sites' => $this->siteOptions($request->user()->id),
        ]);
    }

    public function store(Request $request)
    {
        $d = $request->validate($this->factRules());
        $dive = new Dive($this->factAttributes($d) + [
            'user_id' => $request->user()->id,
            'computer_family' => 'manual',
            'profile' => [],
            'source' => 'manual',
            'notes' => $d['notes'] ?? null,
        ]);
        $this->applySiteChoice($dive, $d);
        $dive->save();
        DiveChange::record($dive->user_id, $dive->uuid, 'created');

        return redirect()->route('DiveDetails', $dive->id)->with('status', 'Dive added. Fill in the rest below.');
    }

    public function edit(Request $request, $id)
    {
        $dive = Dive::where('user_id', $request->user()->id)->findOrFail($id);
        abort_unless($dive->factsEditable(), 403, 'This dive came from a dive computer; its facts are not editable.');

        return view('pages.logbook.dive-form', [
            'dive' => $dive,
            'sites' => $this->siteOptions($request->user()->id),
        ]);
    }

    public function update(Request $request, $id)
    {
        $dive = Dive::where('user_id', $request->user()->id)->findOrFail($id);
        abort_unless($dive->factsEditable(), 403);
        $d = $request->validate($this->factRules());
        $dive->fill($this->factAttributes($d));
        $dive->notes = $d['notes'] ?? null;
        $this->applySiteChoice($dive, $d);
        $dive->save();
        DiveChange::record($dive->user_id, $dive->uuid, 'updated');

        return redirect()->route('DiveDetails', $dive->id)->with('status', 'Dive saved.');
    }

    // ----- Settings: the whole-logbook wipe (Pablo, 2026-10-16: "a way to
    // delete the whole logbook for a user - data removal and data privacy
    // related. Bury it in the configuration for the logbook") -----

    public function settings(Request $request)
    {
        $userId = $request->user()->id;
        return view('pages.logbook.settings', [
            'diveCount' => Dive::where('user_id', $userId)->count(),
            'siteCount' => \App\Models\UserSite::where('user_id', $userId)->count(),
            'importCount' => \App\Models\DiveImport::where('user_id', $userId)->count(),
        ]);
    }

    /**
     * Hard delete, not soft: this is data removal. Every dive (including
     * already-deleted tombstones), the change log, staged imports and the
     * diver's own sites. Phones notice through GET /api/dives/uuids coming
     * back empty and drop their copies; a later computer sync would
     * re-create dives from scratch, which is the diver's call.
     */
    public function destroyAll(Request $request)
    {
        $request->validate(['confirm' => 'required|in:DELETE']);
        $userId = $request->user()->id;

        \Illuminate\Support\Facades\DB::transaction(function () use ($userId) {
            Dive::withTrashed()->where('user_id', $userId)->forceDelete();
            DiveChange::where('user_id', $userId)->delete();
            \App\Models\DiveImport::where('user_id', $userId)->delete(); // items cascade
            \App\Models\UserSite::where('user_id', $userId)->delete();
        });

        return redirect()->route('Logbook')->with('status', 'Your logbook has been deleted.');
    }

    /**
     * GET Logbook/duplicates - every pair of dives in the logbook that look
     * like one dive logged by two computers (strong: clocks agree;
     * possible: clocks hours apart but depth, time and profile agree).
     * Light columns first, profiles only for candidate pairs.
     */
    public function duplicates(Request $request)
    {
        $userId = $request->user()->id;
        $dives = Dive::where('user_id', $userId)->orderBy('started_at')
            ->get(['id', 'started_at', 'duration_seconds', 'max_depth_m', 'computer_model', 'computer_serial', 'conditions', 'site_id', 'user_site_id']);
        $pairs = [];
        $n = $dives->count();
        for ($a = 0; $a < $n; $a++) {
            $da = $dives[$a];
            $dismissed = $da->conditions['same_dive_dismissed'] ?? [];
            for ($b = $a + 1; $b < $n; $b++) {
                $db = $dives[$b];
                if ($db->started_at->getTimestamp() - $da->started_at->getTimestamp() > \App\Support\SameDiveMatcher::CLOCK_WINDOW) break;
                if (!\App\Support\SameDiveMatcher::differentComputers($da->computer_serial, $db->computer_serial)) continue;
                if (!\App\Support\SameDiveMatcher::depthsAgree((float) $da->max_depth_m, (float) $db->max_depth_m)) continue;
                if (!\App\Support\SameDiveMatcher::durationsAgree((int) $da->duration_seconds, (int) $db->duration_seconds)) continue;
                if (in_array($db->id, $dismissed) || in_array($da->id, $db->conditions['same_dive_dismissed'] ?? [])) continue;
                $pairs[] = [$da->id, $db->id];
            }
        }
        $ids = collect($pairs)->flatten()->unique();
        $full = $ids->isEmpty() ? collect() : Dive::whereIn('id', $ids)->get()->keyBy('id');
        Dive::preloadSites($full->values());
        $out = [];
        foreach ($pairs as [$x, $y]) {
            $a = $full[$x] ?? null; $b = $full[$y] ?? null;
            if (!$a || !$b) continue;
            $m = \App\Support\SameDiveMatcher::compare(['started_at' => $a->started_at->format('Y-m-d H:i:s'), 'duration_seconds' => $a->duration_seconds, 'max_depth_m' => $a->max_depth_m, 'profile' => $a->profile], $b);
            if ($m) $out[] = ['a' => $a, 'b' => $b] + $m;
        }
        usort($out, fn ($p, $q) => [$p['level'] !== 'strong', $q['a']->started_at->getTimestamp()] <=> [$q['level'] !== 'strong', $p['a']->started_at->getTimestamp()]);
        return view('pages.logbook.duplicates', ['pairs' => $out]);
    }

    /** POST - "not the same dive" from the duplicates page. */
    public function dismissPair(Request $request, $id, $other)
    {
        $dive = Dive::where('user_id', $request->user()->id)->findOrFail($id);
        Dive::where('user_id', $request->user()->id)->findOrFail($other);
        $c = $dive->conditions ?? [];
        $c['same_dive_dismissed'] = array_values(array_unique(array_merge($c['same_dive_dismissed'] ?? [], [(int) $other])));
        $dive->conditions = $c;
        $dive->save();
        return redirect()->route('Logbook.duplicates');
    }

    /**
     * The other computers' logs of a dive, for the page: a label and a
     * thinned depth trace each, plus the profile/tanks the chart uses -
     * the main computer's, with tank pressure filled in from another
     * computer when the main one had no transmitter (Petrel on the loop,
     * Perdix with the AI). Samples are aligned by time into the dive, so
     * clocks set to different hours don't matter.
     *
     * @return array{0: array, 1: array, 2: ?array, 3: ?string}
     */
    private function computerLogs(Dive $dive): array
    {
        $profile = $dive->profile ?? [];
        $tanks = $dive->tanks;
        $logs = $dive->companions()->get();
        $colors = ['#ffb74d', '#4dd0e1', '#ba68c8', '#aed581'];
        $out = [];
        foreach ($logs as $i => $c) {
            $pts = [];
            $prof = $c->profile ?? [];
            $step = max(1, (int) ceil(count($prof) / 600));
            foreach ($prof as $k => $s) if ($k % $step === 0 && isset($s['time'], $s['depth'])) $pts[] = [(int) $s['time'], round((float) $s['depth'], 2)];
            $out[] = [
                'id' => $c->id,
                'label' => $c->computer_model ?: 'Computer ' . ($i + 2),
                'serial' => $c->computer_serial,
                'source' => $c->source,
                'max_depth_m' => $c->max_depth_m,
                'duration_seconds' => $c->duration_seconds,
                'started_at' => $c->started_at,
                'has_pressure' => collect($prof)->contains(fn ($s) => isset($s['tankPressures']) || isset($s['pressure'])),
                'color' => $colors[$i % count($colors)],
                'points' => $pts,
            ];
        }

        $pressureFrom = null;
        $mainHasPressure = collect($profile)->contains(fn ($s) => isset($s['tankPressures']) || isset($s['pressure']));
        if (!$mainHasPressure && $profile) {
            foreach ($logs as $c) {
                $src = array_values(array_filter($c->profile ?? [], fn ($s) => isset($s['time']) && (isset($s['tankPressures']) || isset($s['pressure']))));
                if (!$src) continue;
                $j = 0; $n = count($src);
                foreach ($profile as &$s) {
                    if (!isset($s['time'])) continue;
                    while ($j < $n - 1 && $src[$j + 1]['time'] <= $s['time']) $j++;
                    if ($src[$j]['time'] > $s['time'] + 30) continue; // before the other computer's first reading
                    $s['tankPressures'] = $src[$j]['tankPressures'] ?? [$src[$j]['pressure']];
                }
                unset($s);
                if (!collect($tanks ?? [])->contains(fn ($t) => isset($t['beginPressure']))) $tanks = $c->tanks;
                $pressureFrom = $c->computer_model ?: 'another computer';
                break;
            }
        }
        return [$out, $profile, $tanks, $pressureFrom];
    }

    /** POST Logbook/{id}/computers/{log}/main - that computer becomes the dive's main one. */
    public function computerMain(Request $request, $id, $log)
    {
        $dive = Dive::where('user_id', $request->user()->id)->findOrFail($id);
        $c = $dive->companions()->findOrFail($log);
        $dive->promoteCompanion($c);
        return redirect()->route('DiveDetails', $dive->id)->with('status', ($dive->computer_model ?: 'That computer') . ' is now this dive\'s main computer.');
    }

    /** POST Logbook/{id}/computers/{log}/detach - a wrong match becomes a dive of its own. */
    public function computerDetach(Request $request, $id, $log)
    {
        $dive = Dive::where('user_id', $request->user()->id)->findOrFail($id);
        $c = $dive->companions()->findOrFail($log);
        $dive->detachCompanion($c);
        return redirect()->route('DiveDetails', $c->id)->with('status', 'Separated: this is now a dive of its own.');
    }

    /** POST Logbook/{id}/combine/{other} - $other's computer log joins this dive. */
    public function combine(Request $request, $id, $other)
    {
        $dive = Dive::where('user_id', $request->user()->id)->findOrFail($id);
        $o = Dive::where('user_id', $request->user()->id)->findOrFail($other);
        $dive->absorb($o);
        $back = $request->input('back');
        return ($back === 'duplicates' ? redirect()->route('Logbook.duplicates') : redirect()->route('DiveDetails', $dive->id))
            ->with('status', 'Combined: the ' . ($o->computer_model ?: 'other') . ' log is now part of this dive.');
    }

    /** POST Logbook/{id}/same-dive/dismiss - "not the same dive". */
    public function dismissSameDive(Request $request, $id)
    {
        $dive = Dive::where('user_id', $request->user()->id)->findOrFail($id);
        $c = $dive->conditions ?? [];
        if ($sug = ($c['same_dive_suggestion'] ?? null)) $c['same_dive_dismissed'][] = (int) ($sug['dive_id'] ?? 0);
        unset($c['same_dive_suggestion']);
        $dive->conditions = $c ?: null;
        $dive->save();
        return redirect()->route('DiveDetails', $dive->id);
    }

    public function show(Request $request, $id)
    {
        $any = Dive::withoutGlobalScope('companions')->where('user_id', $request->user()->id)->findOrFail($id);
        if ($any->isCompanion()) return redirect()->route('DiveDetails', $any->merged_into_id);
        $dive = $any;
        [$companions, $chartProfile, $chartTanks, $pressureFrom] = $this->computerLogs($dive);
        $sameDive = null;
        if ($sug = ($dive->conditions['same_dive_suggestion'] ?? null)) {
            $other = Dive::where('user_id', $dive->user_id)->find($sug['dive_id'] ?? 0);
            if ($other) $sameDive = ['dive' => $other, 'offset' => (int) ($sug['offset'] ?? 0), 'shape' => $sug['shape'] ?? null];
        }

        // Operator search + its boats (Pablo, 2026-10-15: "if the user
        // chooses an operator, we can show the Boat names...can type
        // anything they want", "a small logo of the operator next to the
        // input...same with the boat and its picture"), same "pass the
        // whole small catalog, filter client-side" pattern edit-site.blade.php's
        // own operator search already uses - a few hundred operators/82
        // boats is cheap to ship whole, and it's the free-text-override
        // behavior that matters, not server-side search. logoUrl/pic come
        // along so the picker can show them without a round trip.
        $operators = Operator::orderBy('operatorName')->get(['id', 'operatorName', 'logoUrl', 'slug']);
        $boats = Boat::whereNotNull('operatorId')->orderBy('name')->get(['operatorId', 'name', 'pic']);
        $boatsByOperator = $boats->groupBy('operatorId')
            ->map(fn ($group) => $group->map(fn ($b) => [
                'name' => $b->name,
                'pic' => $b->pic ? asset('assets') . $b->pic : null,
            ])->values());

        // Buddy avatars (Pablo, 2026-10-15: "if known by DH") - buddies is
        // free-text comma-separated names, not structured picks, so this
        // is a best-effort match-by-name against the user directory, done
        // here rather than N+1 queries in the blade. A name collision just
        // shows whichever matches first - no stronger identity to match on
        // without turning buddies into a real multi-select like dive_type.
        $conditions = $dive->conditions ?? [];
        $buddyNames = array_filter(array_map('trim', explode(',', $conditions['buddies'] ?? '')));
        // Two users can share a display name (confirmed: two real "Pablo
        // Strika" rows exist) - plain pluck('picture','name') would let
        // whichever row sorts last silently overwrite an earlier one's
        // picture with null. Ordering pictured rows first and deduping by
        // name keeps a real picture whenever ANY matching user has one.
        $buddyAvatars = $buddyNames
            ? User::whereIn('name', $buddyNames)
                ->orderByRaw('picture IS NULL')
                ->get(['name', 'picture'])
                ->unique('name')
                ->mapWithKeys(fn ($u) => [$u->name => $u->picture ? asset('assets') . '/img/users/' . $u->picture : null])
            : collect();

        return view('pages.DiveDetails', [
            'dive' => $dive,
            'companions' => $companions,
            'chartProfile' => $chartProfile,
            'chartTanks' => $chartTanks,
            'pressureFrom' => $pressureFrom,
            'sameDive' => $sameDive,
            'site' => $dive->site(),
            'operators' => $operators->map(fn ($o) => [
                'id' => $o->id,
                'name' => $o->operatorName,
                'logoUrl' => $o->logoUrl ? asset('assets') . $o->logoUrl : null,
                'url' => route('OperatorDetails', ['id' => $o->slug ?? $o->id]),
            ]),
            'boatsByOperator' => $boatsByOperator,
            'buddyAvatars' => $buddyAvatars,
            // Site-tagging pencil edit (Pablo, 2026-10-15: "search within
            // our database or free type") - same client-side-filtered
            // pattern, not a new search endpoint, for the same reason.
            // The diver's own sites ride along, flagged custom:true.
            'sites' => Site::orderBy('name')->get(['id', 'name', 'slug'])
                ->map(fn ($s) => ['id' => $s->id, 'name' => $s->name, 'custom' => false])
                ->concat(\App\Models\UserSite::where('user_id', $request->user()->id)->orderBy('name')->get(['id', 'name'])
                    ->map(fn ($s) => ['id' => $s->id, 'name' => $s->name, 'custom' => true]))
                ->values(),
            // Surface-break halves the computer logged as two dives (Pablo,
            // 2026-10-16): the next dive by this diver that starts within an
            // hour of this one's end can be merged into it.
            'mergeCandidate' => \App\Support\DiveMerger::nextCandidate($dive),
        ]);
    }

    /**
     * Saves diver-entered data - everything else on a dive is read-only,
     * computer-reported data. Logs a `dive_changes` row (SYNC-CONTRACT.md)
     * so phones pick up the edit through the change feed.
     *
     * Eight distinct forms post here (`form` tells them apart), one per
     * editable surface (Pablo, 2026-10-15: "add a pencil edit button per
     * card...a save button per card"): the two quick-edit chips
     * (classification, site) above the dashboard, three per-card forms
     * (operator, conditions, other), the Dive Setup card's tags line, the
     * whole "Gases used" section as one more (Pablo, 2026-10-15: "use a
     * pencil icon to edit the whole Gases used section...show button
     * save") - gas_tank_choice carries the tank-with-AI pick, the bottom/
     * deco/travel (or bailout/deco) role, the delete checkboxes, AND the
     * per-tank cylinder type/twins all in one submit, since the whole
     * section now has a single pencil/Save instead of each row saving on
     * its own - and gas_row_order, the diver's own drag-to-reorder of
     * those same rows (Pablo: "put the rows in Kanban so the user can
     * sort them...just for visuals"), saved separately and immediately on
     * drop rather than gated behind that same pencil/Save, since it's
     * display order only, never a fact about the dive. Each form only
     * ever sends its own fields - MERGING into the existing conditions
     * rather than a wholesale replace is what keeps one card's save from
     * wiping out another's fields. `dive_type` is the one field the
     * 'other' form is allowed to clear entirely (an absent key there
     * means "every checkbox was unchecked", not "this form doesn't know
     * about dive_type").
     */
    public function updateConditions(Request $request, $id)
    {
        $dive = Dive::where('user_id', $request->user()->id)->findOrFail($id);

        $data = $request->validate([
            'form' => 'required|in:classification,site,operator,conditions,other,tags,gas_tank_choice,gas_row_order',
            'notes' => 'nullable|string|max:5000',
            'site_id' => 'nullable|integer',
            'user_site_id' => 'nullable|integer',
            'conditions' => 'nullable|array',
            'conditions.operator_name' => 'nullable|string|max:150',
            'conditions.divemaster' => 'nullable|string|max:150',
            'conditions.boat_name' => 'nullable|string|max:150',
            'conditions.boat_captain' => 'nullable|string|max:150',
            'conditions.operator_logo' => 'nullable|string|max:500',
            'conditions.operator_url' => 'nullable|string|max:500',
            'conditions.boat_pic' => 'nullable|string|max:500',
            'conditions.current' => 'nullable|string|max:50',
            'conditions.current_direction' => 'nullable|in:N,NE,E,SE,S,SW,W,NW',
            'conditions.weather' => 'nullable|string|max:150',
            'conditions.surface_conditions' => 'nullable|string|max:50',
            // Meters, metric-native storage, regardless of which unit the
            // slider/input displayed - same convention as every other
            // measurement this app stores (Pablo, 2026-10-15: "a slider
            // for visibility and a text input, same we use in the deco
            // planner...make sure we use metric or imperial depending on
            // what's set" - a display concern, not a storage one).
            'conditions.visibility_m' => 'nullable|numeric|min:0|max:100',
            'conditions.buddies' => 'nullable|string|max:300',
            'conditions.dive_type' => 'nullable|array',
            'conditions.dive_type.*' => 'in:drift,training,photo_video,cave,cavern,wall,reef,wreck',
            'conditions.classification' => 'nullable|in:recreational,technical',
            // OC/CC for dives whose mode didn't come from a computer sync
            // (Dive::modeEditable) - ignored for synced dives.
            'dive_mode' => 'nullable|in:open_circuit,closed_circuit',
            // Free-typed site name when nothing in our catalog matches
            // (Pablo, 2026-10-15: "search within our database or free
            // type") - site_id above covers the catalog-match case.
            'conditions.site_name_custom' => 'nullable|string|max:150',
            'tags' => 'nullable|array|max:5',
            'tags.*' => 'string|max:40',
            // Tank type/twins per tank index (Pablo, 2026-10-15: "add one
            // row per tank... select the tank configuration they used").
            // Type is validated as a plain string, not an `in:` list of
            // every DH_TANK_TYPES key - that catalog lives client-side
            // only (dh-tanks.js, mirroring DecoPlanner's own frontend-only
            // tank state), so duplicating its keys here would just be a
            // second copy to keep in sync. An unrecognized type is
            // harmless: the chart/RMV math simply can't look it up and
            // skips that tank, same as no type being set at all.
            'conditions.tank_config' => 'nullable|array',
            'conditions.tank_config.*.type' => 'nullable|string|max:40',
            'conditions.tank_config.*.twins' => 'nullable|boolean',
            // Tank size logged for a gas with no AI-tracked tank to attach
            // it to (Pablo, 2026-10-15: "even if there's no more AI info
            // available for another tank, allow the user to select the
            // tank size. We won't be able to calculate the RMV, but at
            // least he can log what size tank he used") - keyed by GAS
            // index (there's no tank index to key tank_config off of in
            // this case), display-only, never fed into the RMV math.
            'conditions.gas_tank_size_choice' => 'nullable|array',
            'conditions.gas_tank_size_choice.*.type' => 'nullable|string|max:40',
            'conditions.gas_tank_size_choice.*.twins' => 'nullable|boolean',
            // One size for a sidemount pair (see the gas_tank_choice case).
            'conditions.sidemount_size' => 'nullable|array',
            'conditions.sidemount_size.type' => 'nullable|string|max:40',
            'conditions.sidemount_size.twins' => 'nullable|boolean',
            // Gas<->tank pairing overrides, keyed by GAS index (Pablo,
            // 2026-10-15: "I want the gases in each row, not the tank.
            // Then we can associate the tanks to the gases") - a whole
            // section-level edit now (Pablo: "use a pencil icon to edit
            // the whole Gases used section...show button save"), so this
            // one form submission also carries gas_role_choice (bottom/
            // deco/travel, or bailout/deco for CCR) and tank_config
            // (type/twins), not just the tank pick - GasTankMatcher::
            // suggest() computes a default fresh from $dive on every
            // render, these are only the diver's CONFIRMED picks.
            'conditions.gas_tank_choice' => 'nullable|array',
            'conditions.gas_tank_choice.*' => 'nullable|integer',
            'conditions.gas_role_choice' => 'nullable|array',
            'conditions.gas_role_choice.*' => 'nullable|in:bottom,deco,travel,bailout',
            'conditions.deleted_gas_mixes' => 'nullable|array',
            'conditions.deleted_gas_mixes.*' => 'integer',
            // Diver's own drag-to-reorder of the Gases used rows (Pablo,
            // 2026-10-15: "I will also put the rows in Kanban so the user
            // can sort them in any particular order - just for visuals")
            // - a row's KEY ('diluent'/'oxygen' for CCR's two fixed rows,
            // the gas index otherwise), not an array index, so a row that
            // later disappears (deleted, or the dive gets re-synced with
            // a different gas list) doesn't shift every other saved
            // position. Purely a display order - never touches which
            // tank/role/gas is matched to what.
            'conditions.gas_row_order' => 'nullable|array',
            'conditions.gas_row_order.*' => 'string|max:20',
        ]);

        $conditions = $dive->conditions ?? [];

        switch ($data['form']) {
            case 'classification':
                $conditions['classification'] = $data['conditions']['classification'] ?? null;
                if (!empty($data['dive_mode']) && $dive->modeEditable() && $dive->dive_mode !== $data['dive_mode']) {
                    $dive->setModeByHand($data['dive_mode']);
                    // Gas pairings/roles were keyed to the old reading.
                    foreach (['gas_tank_choice', 'gas_role_choice', 'deleted_gas_mixes', 'gas_row_order'] as $k) unset($conditions[$k]);
                }
                break;
            case 'site':
                $dive->site_id = $data['site_id'] ?? null;
                // One of the diver's own sites (must be theirs) - exclusive
                // with a catalog site.
                $userSiteId = $data['user_site_id'] ?? null;
                $dive->user_site_id = (!$dive->site_id && $userSiteId && \App\Models\UserSite::where('user_id', $dive->user_id)->where('id', $userSiteId)->exists()) ? (int) $userSiteId : null;
                // A real pick always wins over and clears any earlier
                // free-typed name - a dive has one site, not several.
                $conditions['site_name_custom'] = ($dive->site_id || $dive->user_site_id) ? null : ($data['conditions']['site_name_custom'] ?? null);
                break;
            case 'operator':
                // operator_logo/boat_pic (Pablo, 2026-10-15: "a small logo
                // of the operator next to the input...same with the boat
                // and its picture") - the JS fills these hidden fields in
                // when a real catalog match is picked, so the image
                // persists across reloads without a live lookup.
                foreach (['operator_name', 'divemaster', 'boat_name', 'boat_captain', 'operator_logo', 'operator_url', 'boat_pic'] as $field) {
                    $conditions[$field] = $data['conditions'][$field] ?? null;
                }
                // The picker hands these over as absolute URLs of THIS host;
                // store the path only (App\Support\AssetPath) so the same row
                // renders on any host - localhost, the LAN address, the app.
                foreach (['operator_logo', 'operator_url', 'boat_pic'] as $field) {
                    $conditions[$field] = \App\Support\AssetPath::relative($conditions[$field]);
                }
                break;
            case 'conditions':
                foreach (['current', 'current_direction', 'weather', 'surface_conditions', 'visibility_m'] as $field) {
                    $conditions[$field] = $data['conditions'][$field] ?? null;
                }
                break;
            case 'other':
                $dive->notes = $data['notes'] ?? null;
                $conditions['buddies'] = $data['conditions']['buddies'] ?? null;
                $conditions['dive_type'] = $data['conditions']['dive_type'] ?? [];
                break;
            case 'tags':
                $dive->tags = $data['tags'] ?? [];
                break;
            case 'gas_tank_choice':
                // Mutual exclusivity (Pablo, 2026-10-15: "if a tank was
                // already assigned to a gas, then it cannot be assigned
                // to another gas") - the picker also disables an
                // already-taken tank client-side, but this is the real
                // enforcement: first gas (by index) to claim a tank wins,
                // any later claim on the same tank drops back to
                // unmatched rather than silently double-booking it.
                $seenTanks = [];
                $dedupedChoice = [];
                foreach (($data['conditions']['gas_tank_choice'] ?? []) as $gi => $ti) {
                    if ($ti !== null && !in_array($ti, $seenTanks, true)) {
                        $seenTanks[] = $ti;
                        $dedupedChoice[$gi] = $ti;
                    } else {
                        $dedupedChoice[$gi] = null;
                    }
                }
                $conditions['gas_tank_choice'] = $dedupedChoice;
                $conditions['gas_role_choice'] = $data['conditions']['gas_role_choice'] ?? [];
                $conditions['deleted_gas_mixes'] = $data['conditions']['deleted_gas_mixes'] ?? [];
                if (array_key_exists('tank_config', $data['conditions'] ?? [])) {
                    $conditions['tank_config'] = $data['conditions']['tank_config'];
                }
                // One size for a sidemount pair, copied onto every tank
                // the computer tagged "sidemount" (Pablo, 2026-10-15).
                if (!empty($data['conditions']['sidemount_size'])) {
                    foreach ($dive->tanks ?? [] as $ti => $tank) {
                        if (($tank['usage'] ?? null) === 'sidemount') {
                            $conditions['tank_config'][$ti] = $data['conditions']['sidemount_size'];
                        }
                    }
                }
                if (array_key_exists('gas_tank_size_choice', $data['conditions'] ?? [])) {
                    $conditions['gas_tank_size_choice'] = $data['conditions']['gas_tank_size_choice'];
                }
                break;
            case 'gas_row_order':
                $conditions['gas_row_order'] = $data['conditions']['gas_row_order'] ?? [];
                break;
        }

        $dive->conditions = array_filter($conditions, fn ($v) => $v !== null && $v !== '' && $v !== []);
        $dive->save();

        DiveChange::record($dive->user_id, $dive->uuid, 'updated');

        return redirect()->route('DiveDetails', $dive->id);
    }

    /**
     * @-mention search for the Buddies field (Pablo, 2026-10-15: "if the
     * user types @ we can search the user list"). Unlike the operator/
     * site/boat catalogs (a few hundred rows, safe to ship whole and
     * filter client-side), the full user directory is personal data at
     * real scale - a live, rate-limited-by-debounce search endpoint
     * instead, same shape as GroupController::searchSites but with no
     * group-membership scope (a dive buddy isn't necessarily in a shared
     * group). Name only, never email - avoids turning this into an email
     * enumeration tool. Excludes guests and the searching user themself.
     */
    public function searchUsers(Request $request)
    {
        $q = trim((string) $request->input('q'));
        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $users = User::where('name', 'LIKE', "%$q%")
            ->where('id', '!=', $request->user()->id)
            ->where('role_id', '!=', 4) // guests - User::isGuest()'s own check
            ->orderBy('name')
            ->take(10)
            ->get(['id', 'name']);

        return response()->json($users);
    }
}

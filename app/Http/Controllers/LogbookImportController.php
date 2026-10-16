<?php

namespace App\Http\Controllers;

use App\Models\Dive;
use App\Models\DiveChange;
use App\Models\DiveImport;
use App\Models\DiveImportItem;
use App\Models\Site;
use App\Models\UserSite;
use App\Support\UddfParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Logbook import wizard (Pablo, 2026-10-16: "a wizard for the import to
 * identify dups, match sites to well known sites we have in DH and run
 * all the logic that we have when we download a dive from a computer").
 *
 *   1. upload   - parse the UDDF (UddfParser) and stage one item per dive
 *                 in dive_import_items, already in the `dives` row shape,
 *                 with duplicate detection done against the diver's
 *                 logbook and within the file itself.
 *   2. review   - counts + the duplicates, each with an import/skip toggle.
 *   3. sites    - every site the file references, matched to a catalog
 *                 site (GPS within 1 km, or the same name), one of the
 *                 diver's own sites, a new own site, or just the name.
 *   4. run      - creates the dives (same path as DiveUploadController:
 *                 Dive::create + dive_changes 'created'), creating own
 *                 sites as decided, then marks the import done.
 *
 * Duplicate kinds:
 *   fingerprint - same (computer_serial, fingerprint) as a dive already
 *                 on file, deleted ones included (exact; Shearwater only).
 *   time        - a dive already on file starts within 3 minutes, from
 *                 any computer (two computers on one dive, or a brand
 *                 whose fingerprint the file can't rebuild).
 *   file        - another dive in this same file starts within 3 minutes
 *                 (MacDive logged two computers for one dive).
 */
class LogbookImportController extends Controller
{
    private const TIME_WINDOW = 180;

    public function index(Request $request)
    {
        $imports = DiveImport::where('user_id', $request->user()->id)
            ->where('status', 'staged')
            ->withCount('items')
            ->orderByDesc('id')
            ->get();

        return view('pages.logbook.import.index', ['imports' => $imports]);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:140000', // KB
        ]);
        @set_time_limit(900);
        @ini_set('memory_limit', '1024M');

        $file = $request->file('file');
        $name = $file->getClientOriginalName();
        $ext = strtolower($file->getClientOriginalExtension());
        if (!in_array($ext, ['uddf', 'xml'], true)) {
            return back()->withErrors(['file' => 'Please upload a MacDive XML (.xml) or UDDF (.uddf) export.']);
        }

        // Two formats, sniffed from the file itself: MacDive's own XML (the
        // richer one - PPO2, setpoints, operator, conditions, tanks) or UDDF.
        $isMacDive = \App\Support\MacDiveXmlParser::sniff($file->getRealPath());
        try {
            $parsed = $isMacDive
                ? \App\Support\MacDiveXmlParser::parse($file->getRealPath())
                : UddfParser::parse($file->getRealPath());
        } catch (\Throwable $e) {
            return back()->withErrors(['file' => 'That file could not be read as ' . ($isMacDive ? 'MacDive XML' : 'UDDF') . ': ' . $e->getMessage()]);
        }
        if (!count($parsed->dives)) {
            return back()->withErrors(['file' => 'No dives found in that file.']);
        }

        $userId = $request->user()->id;

        // Existing dives (tombstones too) for duplicate detection.
        $existing = Dive::withTrashed()->where('user_id', $userId)
            ->get(['id', 'computer_serial', 'fingerprint', 'started_at', 'deleted_at', 'duration_seconds', 'max_depth_m']);
        $byKey = [];
        $byTime = [];
        foreach ($existing as $e) {
            if ($e->fingerprint) $byKey[strtolower((string) $e->computer_serial) . '|' . $e->fingerprint] = $e;
            $byTime[] = $e;
        }

        $import = DiveImport::create([
            'user_id' => $userId,
            'filename' => Str::limit($name, 250, ''),
            'generator' => $parsed->generator,
            'status' => 'staged',
            'sites' => collect($parsed->sites)->map(fn ($s) => $s + ['decision' => null, 'catalog_site_id' => null, 'user_site_id' => null])->all(),
        ]);

        // Sort by start time so within-file duplicates are neighbors.
        $dives = $parsed->dives;
        usort($dives, fn ($a, $b) => strcmp($a['started_at'], $b['started_at']));

        $counts = ['total' => count($dives), 'new' => 0, 'fingerprint' => 0, 'time' => 0, 'file' => 0, 'no_profile' => 0];
        $computers = [];
        $items = [];
        $prev = null;
        foreach ($dives as $i => $d) {
            $kind = null; $ofDive = null; $ofItemPos = null;
            $key = strtolower((string) $d['computer_serial']) . '|' . $d['fingerprint'];
            if ($d['computer_serial'] && isset($byKey[$key])) {
                $kind = 'fingerprint'; $ofDive = $byKey[$key]->id;
            } else {
                $ts = strtotime($d['started_at']);
                foreach ($byTime as $e) {
                    if (abs($e->started_at->getTimestamp() - $ts) <= self::TIME_WINDOW) { $kind = 'time'; $ofDive = $e->id; break; }
                }
            }
            if ($kind === null && $prev !== null && abs(strtotime($d['started_at']) - strtotime($prev['started_at'])) <= self::TIME_WINDOW) {
                // Same dive from a second computer in the same file: keep the
                // copy with a profile (or the earlier one), skip the other.
                if (!count($prev['profile']) && count($d['profile'])) {
                    $items[$prev['pos']]['duplicate_kind'] = 'file';
                    $items[$prev['pos']]['duplicate_of_item_pos'] = $i;
                    $items[$prev['pos']]['decision'] = 'skip';
                    $counts['file']++; $counts['new']--;
                } else {
                    $kind = 'file'; $ofItemPos = $prev['pos'];
                }
            }
            $hasProfile = count($d['profile']) > 1;
            if (!$hasProfile) $counts['no_profile']++;
            if ($kind) $counts[$kind]++; else $counts['new']++;
            $label = trim(($d['computer_model'] ?? 'Unknown computer') . ($d['computer_serial'] ? ' (' . $d['computer_serial'] . ')' : ''));
            $computers[$label] = ($computers[$label] ?? 0) + 1;

            $items[$i] = [
                'position' => $i,
                'started_at' => $d['started_at'],
                'duration_seconds' => $d['duration_seconds'],
                'max_depth_m' => $d['max_depth_m'],
                'computer_model' => $d['computer_model'],
                'computer_serial' => $d['computer_serial'],
                'fingerprint' => $d['fingerprint'],
                'site_key' => $d['site_key'],
                'has_profile' => $hasProfile,
                'duplicate_kind' => $kind,
                'duplicate_of_dive_id' => $ofDive,
                'duplicate_of_item_pos' => $ofItemPos,
                'decision' => $kind ? 'skip' : 'import',
                'payload' => $d,
            ];
            $prev = ['started_at' => $d['started_at'], 'profile' => $d['profile'], 'pos' => $i];
        }

        // Multi-row inserts in chunks (one statement per 40 dives, not one
        // per dive - a full logbook is ~850 rows with big profile JSON),
        // then resolve item-to-item references by position.
        $now = now();
        $pending = [];
        foreach (array_chunk($items, 40, true) as $chunk) {
            $rows = [];
            foreach ($chunk as $pos => $row) {
                if ($row['duplicate_of_item_pos'] !== null) $pending[$pos] = $row['duplicate_of_item_pos'];
                unset($row['duplicate_of_item_pos']);
                $row['dive_import_id'] = $import->id;
                $row['payload'] = json_encode($row['payload']);
                $row['created_at'] = $now; $row['updated_at'] = $now;
                $rows[] = $row;
            }
            DB::table('dive_import_items')->insert($rows);
        }
        if ($pending) {
            $posToId = DB::table('dive_import_items')->where('dive_import_id', $import->id)->pluck('id', 'position');
            foreach ($pending as $pos => $ofPos) {
                if (isset($posToId[$pos], $posToId[$ofPos])) {
                    DB::table('dive_import_items')->where('id', $posToId[$pos])->update(['duplicate_of_item_id' => $posToId[$ofPos]]);
                }
            }
        }

        // Compact per-dive rows for the review step's dive-mode list, so it
        // never has to load ~850 payloads (profiles) to render. Only dives
        // that will be imported and whose mode the file didn't record.
        $modeRows = [];
        $fromFile = ['closed_circuit' => 0, 'semi_closed_circuit' => 0, 'open_circuit' => 0, 'freedive' => 0];
        foreach ($items as $pos => $row) {
            $d = $row['payload'];
            if ($row['decision'] !== 'import') continue;
            if (in_array($d['mode_source'] ?? null, ['file', 'inferred'], true)) { $fromFile[$d['dive_mode']] = ($fromFile[$d['dive_mode']] ?? 0) + 1; continue; }
            $modeRows[] = [
                'pos' => $pos,
                'date' => $d['started_at'],
                'computer' => $d['computer_model'] ?? null,
                'depth' => $d['max_depth_m'],
                'dur' => $d['duration_seconds'],
                'gases' => array_map(fn ($g) => $g['helium'] > 0 ? $g['oxygen'] . '/' . $g['helium'] : ($g['oxygen'] == 21 ? 'Air' : $g['oxygen'] . '%'), $d['gas_mixes'] ?? []),
            ];
        }

        $import->summary = [
            'counts' => $counts,
            'computers' => $computers,
            'from' => $dives[0]['started_at'],
            'to' => end($dives)['started_at'],
            'sites' => count($parsed->sites),
            'mode_from_file' => array_filter($fromFile),
            'mode_unknown' => $modeRows,
            'cc_positions' => [],
        ];
        $import->save();

        return redirect()->route('Logbook.import.review', $import->id);
    }

    private function load(Request $request, $id): DiveImport
    {
        return DiveImport::where('user_id', $request->user()->id)->where('status', 'staged')->findOrFail($id);
    }

    public function review(Request $request, $id)
    {
        $import = $this->load($request, $id);
        if (empty($import->summary['counts'])) {
            // The upload request died before staging finished (timeout,
            // closed tab) - nothing reviewable; start over.
            $import->delete();
            return redirect()->route('Logbook.import.index')->withErrors(['file' => 'That upload did not finish. Please upload the file again.']);
        }
        $dups = $import->items()->whereNotNull('duplicate_kind')->with('duplicateOfDive')->get();
        $itemsById = $import->items()->get(['id', 'started_at', 'computer_model', 'has_profile'])->keyBy('id');

        return view('pages.logbook.import.review', [
            'import' => $import,
            'dups' => $dups,
            'itemsById' => $itemsById,
        ]);
    }

    public function decisions(Request $request, $id)
    {
        $import = $this->load($request, $id);
        $chosen = array_map('intval', (array) $request->input('import_ids', []));
        $import->items()->whereNotNull('duplicate_kind')->update(['decision' => 'skip']);
        if ($chosen) {
            $import->items()->whereNotNull('duplicate_kind')->whereIn('id', $chosen)->update(['decision' => 'import']);
        }

        // Dive mode per DIVE (Pablo, 2026-10-16: "a Petrel 2 can be used as
        // OC too... this is for the general public"): only dives whose mode
        // the file didn't record are offered, all open circuit unless the
        // diver ticks them as closed circuit. Applied to the staged payload
        // (diluent = the gas breathed at the start, usage 2) and remembered
        // so going Back keeps the ticks.
        $unknown = array_column($import->summary['mode_unknown'] ?? [], 'pos');
        $cc = array_values(array_intersect($unknown, array_map('intval', (array) $request->input('cc_positions', []))));
        if ($unknown) {
            $ccSet = array_flip($cc);
            $import->items()->whereIn('position', $unknown)->orderBy('id')->chunkById(100, function ($items) use ($ccSet) {
                foreach ($items as $item) {
                    $p = $item->payload;
                    $mode = isset($ccSet[$item->position]) ? 'closed_circuit' : 'open_circuit';
                    if (($p['dive_mode'] ?? null) === $mode) continue;
                    $p['dive_mode'] = $mode;
                    $dil = $p['profile'][0]['currentGas'] ?? 0;
                    foreach ($p['gas_mixes'] ?? [] as $gi => $mix) {
                        $p['gas_mixes'][$gi]['usage'] = ($mode === 'closed_circuit' && $gi === $dil) ? 2 : 0;
                    }
                    $item->payload = $p;
                    $item->save();
                }
            });
            $summary = $import->summary;
            $summary['cc_positions'] = $cc;
            $import->summary = $summary;
            $import->save();
        }

        return redirect()->route('Logbook.import.sites', $import->id);
    }

    public function sites(Request $request, $id)
    {
        $import = $this->load($request, $id);
        $userId = $request->user()->id;

        // Only sites that an importing dive actually uses.
        $usedKeys = $import->items()->where('decision', 'import')->whereNull('imported_dive_id')
            ->whereNotNull('site_key')->pluck('site_key')->countBy();
        $fileSites = collect($import->sites ?? [])->only($usedKeys->keys())->map(fn ($s, $k) => $s + ['uses' => $usedKeys[$k]]);

        $catalog = Site::orderBy('name')->get(['id', 'name', 'location', 'gpsLat', 'gpsLon'])
            ->map(fn ($s) => ['id' => $s->id, 'name' => $s->name, 'city' => \App\Support\SiteLocations::name($s->location), 'lat' => $s->decimalLatitude(), 'lon' => $s->decimalLongitude()])
            ->values();
        $own = UserSite::where('user_id', $userId)->orderBy('name')->get(['id', 'name', 'latitude', 'longitude']);

        $suggestions = [];
        foreach ($fileSites as $key => $s) {
            $suggestions[$key] = self::suggest($s, $catalog, $own);
        }

        return view('pages.logbook.import.sites', [
            'import' => $import,
            'fileSites' => $fileSites->sortBy('name'),
            'catalog' => $catalog,
            'own' => $own,
            'suggestions' => $suggestions,
        ]);
    }

    /** Best match for a file site: ['kind' => catalog|own|create, 'id' => ?, 'why' => text]. */
    public static function suggest(array $s, $catalog, $own): array
    {
        $name = mb_strtolower(trim($s['name'] ?? ''));
        // The diver's own site with the same name wins - they made it on purpose.
        foreach ($own as $o) {
            if (mb_strtolower($o->name) === $name) return ['kind' => 'own', 'id' => $o->id, 'why' => 'Same name as one of your sites'];
        }
        $best = null; $bestKm = null;
        if (($s['latitude'] ?? null) !== null && ($s['longitude'] ?? null) !== null) {
            foreach ($catalog as $c) {
                if ($c['lat'] === null || $c['lon'] === null) continue;
                $km = self::km($s['latitude'], $s['longitude'], $c['lat'], $c['lon']);
                if ($bestKm === null || $km < $bestKm) { $best = $c; $bestKm = $km; }
            }
        }
        if ($best && $bestKm <= 1.0) {
            return ['kind' => 'catalog', 'id' => $best['id'], 'why' => number_format($bestKm * 1000) . ' m from ' . $best['name']];
        }
        foreach ($catalog as $c) {
            $cn = mb_strtolower($c['name']);
            if ($cn === $name) return ['kind' => 'catalog', 'id' => $c['id'], 'why' => 'Same name'];
        }
        foreach ($catalog as $c) {
            $cn = mb_strtolower($c['name']);
            if (mb_strlen($name) >= 5 && (str_contains($cn, $name) || str_contains($name, $cn))) {
                return ['kind' => 'catalog', 'id' => $c['id'], 'why' => 'Name looks like ' . $c['name'], 'weak' => true];
            }
            similar_text($cn, $name, $pct);
            if ($pct >= 88) return ['kind' => 'catalog', 'id' => $c['id'], 'why' => 'Name looks like ' . $c['name'], 'weak' => true];
        }
        return ['kind' => 'create', 'id' => null, 'why' => $best ? 'Nearest catalog site is ' . number_format($bestKm, 1) . ' km away (' . $best['name'] . ')' : 'Nothing close in the catalog'];
    }

    private static function km(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $r = 6371;
        $dLat = deg2rad($lat2 - $lat1); $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        return 2 * $r * atan2(sqrt($a), sqrt(1 - $a));
    }

    public function saveSites(Request $request, $id)
    {
        $import = $this->load($request, $id);
        $sites = $import->sites ?? [];
        $decisions = (array) $request->input('site', []);
        foreach ($decisions as $key => $d) {
            if (!isset($sites[$key])) continue;
            $kind = $d['kind'] ?? 'name';
            $sites[$key]['decision'] = in_array($kind, ['catalog', 'own', 'create', 'name'], true) ? $kind : 'name';
            $sites[$key]['catalog_site_id'] = $kind === 'catalog' ? (int) ($d['catalog_site_id'] ?? 0) ?: null : null;
            $sites[$key]['user_site_id'] = $kind === 'own' ? (int) ($d['user_site_id'] ?? 0) ?: null : null;
            if ($kind === 'catalog' && !$sites[$key]['catalog_site_id']) $sites[$key]['decision'] = 'name';
            if ($kind === 'own' && !$sites[$key]['user_site_id']) $sites[$key]['decision'] = 'name';
        }
        $import->sites = $sites;
        $import->save();

        return $this->run($request, $id);
    }

    /** Dives created per request; the progress page re-posts until none are left. */
    private const RUN_BATCH = 60;

    /** GET Logbook/import/{id}/run - progress page that auto-posts the next batch. */
    public function progress(Request $request, $id)
    {
        $import = $this->load($request, $id);
        $remaining = $import->items()->where('decision', 'import')->whereNull('imported_dive_id')->count();
        $done = $import->items()->whereNotNull('imported_dive_id')->count();

        return view('pages.logbook.import.progress', ['import' => $import, 'remaining' => $remaining, 'done' => $done]);
    }

    /**
     * POST Logbook/import/{id}/run - creates up to RUN_BATCH dives, then
     * either bounces back to the progress page for the next batch or,
     * when nothing is left, marks the import done. Batched so a whole
     * logbook never has to fit inside one request's execution window.
     */
    public function run(Request $request, $id)
    {
        $import = $this->load($request, $id);
        $user = $request->user();
        @set_time_limit(300);

        $siteMap = $import->sites ?? [];
        $createdSites = [];
        $ownIds = UserSite::where('user_id', $user->id)->pluck('id')->flip();
        $created = 0; $skipped = 0;

        $batch = $import->items()->where('decision', 'import')->whereNull('imported_dive_id')->orderBy('position')->limit(self::RUN_BATCH)->get();
        foreach ([$batch] as $items) {
                foreach ($items as $item) {
                    $p = $item->payload;
                    // Last-second duplicate guard (the same file imported twice).
                    if ($p['computer_serial'] && Dive::withTrashed()->where('user_id', $user->id)->where('computer_serial', $p['computer_serial'])->where('fingerprint', $p['fingerprint'])->exists()) {
                        $item->update(['decision' => 'skip', 'duplicate_kind' => $item->duplicate_kind ?? 'fingerprint']);
                        $skipped++;
                        continue;
                    }

                    $siteId = null; $userSiteId = null; $siteName = null;
                    $s = $item->site_key ? ($siteMap[$item->site_key] ?? null) : null;
                    if ($s) {
                        switch ($s['decision'] ?? 'name') {
                            case 'catalog': $siteId = $s['catalog_site_id'] ?: null; break;
                            case 'own': $userSiteId = ($s['user_site_id'] && isset($ownIds[$s['user_site_id']])) ? $s['user_site_id'] : null; break;
                            case 'create':
                                if (!isset($createdSites[$item->site_key])) {
                                    // Created in an earlier batch? The map was
                                    // flipped to 'own' then, so this is first time.
                                    $createdSites[$item->site_key] = UserSite::create([
                                        'user_id' => $user->id,
                                        'name' => Str::limit($s['name'] ?: 'Imported site', 150, ''),
                                        'type' => \App\Support\MacDiveXmlParser::siteType($s),
                                        'min_cert' => $s['min_cert'] ?? null,
                                        'water_type' => $s['water_type'] ?? null,
                                        'altitude_m' => $s['altitude_m'] ?? null,
                                        'latitude' => $s['latitude'] ?? null,
                                        'longitude' => $s['longitude'] ?? null,
                                        'location' => ($s['location'] ?? '') ?: null,
                                        'country' => ($s['country'] ?? '') ?: null,
                                    ])->id;
                                    $siteMap[$item->site_key]['user_site_id'] = $createdSites[$item->site_key];
                                    $siteMap[$item->site_key]['decision'] = 'own';
                                }
                                $userSiteId = $createdSites[$item->site_key];
                                break;
                            default: $siteName = $s['name'] ?: null;
                        }
                        if (!$siteId && !$userSiteId && !$siteName) $siteName = $s['name'] ?: null;
                    }

                    // Conditions the file carried (MacDive XML: operator, boat,
                    // weather, current, dive types, tank sizes...) first.
                    $conditions = $p['conditions'] ?? [];
                    if ($siteName) $conditions['site_name_custom'] = $siteName;
                    if (!empty($p['buddies'])) $conditions['buddies'] = implode(', ', $p['buddies']);
                    if (!empty($p['rating'])) $conditions['rating'] = (int) $p['rating'];

                    $dive = DB::transaction(function () use ($p, $user, $siteId, $userSiteId, $conditions) {
                        $dive = Dive::create([
                            'user_id' => $user->id,
                            'uuid' => (string) Str::uuid(),
                            'computer_family' => $p['computer_family'] ?? 'uddf',
                            'computer_model' => $p['computer_model'] ?? null,
                            'computer_serial' => $p['computer_serial'] ?? null,
                            'fingerprint' => $p['fingerprint'] ?? null,
                            'dive_number' => $p['dive_number'] ?? null,
                            'started_at' => $p['started_at'],
                            'duration_seconds' => $p['duration_seconds'],
                            'max_depth_m' => $p['max_depth_m'],
                            'avg_depth_m' => $p['avg_depth_m'] ?? null,
                            'min_water_temp_c' => $p['min_water_temp_c'] ?? null,
                            'max_water_temp_c' => $p['max_water_temp_c'] ?? null,
                            'surface_temp_c' => $p['surface_temp_c'] ?? null,
                            'deco_model' => $p['deco_model'] ?? null,
                            'sensor_data' => $p['sensor_data'] ?? null,
                            'tags' => $p['tags'] ?? [],
                            'dive_mode' => $p['dive_mode'] ?? 'open_circuit',
                            'gas_mixes' => $p['gas_mixes'] ?? null,
                            'tanks' => $p['tanks'] ?? null,
                            'profile' => $p['profile'] ?? [],
                            'site_id' => $siteId,
                            'user_site_id' => $userSiteId,
                            'notes' => $p['notes'] ?? null,
                            'conditions' => $conditions ?: null,
                            'source' => 'uddf-import',
                        ]);
                        DiveChange::record($user->id, $dive->uuid, 'created');
                        return $dive;
                    });
                    $item->update(['imported_dive_id' => $dive->id]);
                    $created++;
                }
        }

        // Persist the site map (newly created own sites are now 'own')
        // and running totals, then either continue or finish.
        $summary = $import->summary ?? [];
        $result = $summary['result'] ?? ['created' => 0, 'skipped' => 0, 'sites_created' => 0];
        $result['created'] += $created;
        $result['sites_created'] += count($createdSites);
        $import->sites = $siteMap;

        $remaining = $import->items()->where('decision', 'import')->whereNull('imported_dive_id')->count();
        if ($remaining > 0) {
            $summary['result'] = $result;
            $import->summary = $summary;
            $import->save();
            return redirect()->route('Logbook.import.progress', $import->id);
        }

        $result['skipped'] = $skipped + $import->items()->where('decision', 'skip')->count();
        $summary['result'] = $result;
        $import->summary = $summary;
        $import->status = 'done';
        $import->save();

        return redirect()->route('Logbook')->with('status', 'Imported ' . $result['created'] . ' dive' . ($result['created'] === 1 ? '' : 's') . ' from ' . $import->filename . ($result['skipped'] ? ' (' . $result['skipped'] . ' skipped as duplicates)' : '') . ($result['sites_created'] ? ', created ' . $result['sites_created'] . ' of your own sites' : '') . '.');
    }

    public function cancel(Request $request, $id)
    {
        $import = $this->load($request, $id);
        $import->delete();

        return redirect()->route('Logbook.import.index')->with('status', 'Import discarded.');
    }
}

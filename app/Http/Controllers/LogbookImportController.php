<?php

namespace App\Http\Controllers;

use App\Models\Dive;
use App\Models\DiveChange;
use App\Models\DiveImport;
use App\Models\DiveImportItem;
use App\Models\Site;
use App\Models\UserSite;
use App\Support\SameDiveMatcher;
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

    /** Files per chunk request - under PHP's default max_file_uploads (20). */
    public const CHUNK_FILES = 20;

    private function chunkDir(int $userId, string $batch): string
    {
        return storage_path('app/import-chunks/' . $userId . '/' . $batch);
    }

    /**
     * One batch of files from the dropzone (Pablo, 2026-10-16: "logbook can
     * be VERY long... I'm not sure if we can drag and drop 1000 files at
     * once"). A Shearwater Cloud export is one XML per dive, and PHP takes
     * at most max_file_uploads files per request and drops the rest
     * silently - so the page sends them CHUNK_FILES at a time into a
     * per-batch folder, then calls upload() with the batch id to read them
     * all as one import.
     */
    public function chunk(Request $request)
    {
        $request->validate([
            'batch' => 'required|uuid',
            'files' => 'required|array|max:' . self::CHUNK_FILES,
            'files.*' => 'file|max:140000', // KB
            'file_count' => 'required|integer|min:1',
        ]);
        $files = $request->file('files');
        if ((int) $request->input('file_count') > count($files)) {
            return response()->json(['message' => 'The server received ' . count($files) . ' of ' . (int) $request->input('file_count') . ' files in a batch. Please try again.'], 422);
        }
        $userId = $request->user()->id;
        $this->sweepOldChunks($userId);
        $dir = $this->chunkDir($userId, $request->input('batch'));
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $n = count(glob($dir . '/*'));
        foreach ($files as $file) {
            $ext = strtolower($file->getClientOriginalExtension());
            if (!in_array($ext, ['uddf', 'xml', 'fit'], true)) {
                return response()->json(['message' => $file->getClientOriginalName() . ' is not a .xml, .uddf or .fit export.'], 422);
            }
            // Index prefix keeps two same-named files (from different
            // subfolders) apart; the original name follows it.
            $safe = preg_replace('/[^\w\[\]#.\- ]+/u', '_', $file->getClientOriginalName());
            $file->move($dir, sprintf('%05d_', $n++) . $safe);
        }
        return response()->json(['stored' => $n]);
    }

    /** Batches abandoned mid-upload (closed tab) are removed after a day. */
    private function sweepOldChunks(int $userId): void
    {
        foreach (glob(storage_path('app/import-chunks/' . $userId . '/*'), GLOB_ONLYDIR) ?: [] as $d) {
            if (filemtime($d) < time() - 86400) $this->removeDir($d);
        }
    }

    private function removeDir(string $dir): void
    {
        foreach (glob($dir . '/*') ?: [] as $f) @unlink($f);
        @rmdir($dir);
    }

    public function upload(Request $request)
    {
        // One logbook file, or many single-dive files at once (Pablo,
        // 2026-10-16: Shearwater Cloud exports one XML per dive). The
        // dropzone sends files ahead through chunk() and passes `batch`;
        // a plain form post (no JS) sends them here directly.
        $request->validate([
            'batch' => 'nullable|uuid',
            'files' => 'required_without_all:file,batch|array|max:2000',
            'files.*' => 'file|max:140000', // KB
            'file' => 'required_without_all:files,batch|file|max:140000',
        ]);
        @set_time_limit(900);
        @ini_set('memory_limit', '1024M');

        $batchDir = null;
        if ($request->filled('batch')) {
            $batchDir = $this->chunkDir($request->user()->id, $request->input('batch'));
            $uploads = [];
            foreach (glob($batchDir . '/*') ?: [] as $p) {
                $uploads[] = ['path' => $p, 'name' => preg_replace('/^\d{5}_/', '', basename($p))];
            }
            if (!$uploads) return $this->uploadError($request, 'The upload didn\'t arrive. Please try again.');
        } else {
            $uploads = array_map(fn ($f) => ['path' => $f->getRealPath(), 'name' => $f->getClientOriginalName()], $request->file('files') ?: [$request->file('file')]);
        }
        try {
            return $this->stage($request, $uploads);
        } finally {
            if ($batchDir) $this->removeDir($batchDir);
        }
    }

    /** @param array<int, array{path:string, name:string}> $uploads */
    private function stage(Request $request, array $uploads)
    {
        $logbooks = []; $shearwater = []; $garmin = []; $names = [];
        foreach ($uploads as $file) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['uddf', 'xml', 'fit'], true)) {
                return $this->uploadError($request, $file['name'] . ' is not a .xml, .uddf or .fit export.');
            }
            $names[] = $file['name'];
            if (\App\Support\GarminFitParser::sniff($file['path'])) {
                $garmin[] = $file;
            } elseif ($ext === 'fit') {
                return $this->uploadError($request, $file['name'] . ' is not a Garmin FIT file.');
            } elseif (\App\Support\ShearwaterXmlParser::sniff($file['path'])) {
                $shearwater[] = $file;
            } else {
                $logbooks[] = $file;
            }
        }

        // Sniffed from each file's content: MacDive XML (richest logbook),
        // UDDF (any app), or Shearwater per-dive XML (the computer's log).
        $dives = []; $sites = []; $generators = [];
        try {
            foreach ($logbooks as $file) {
                $isMacDive = \App\Support\MacDiveXmlParser::sniff($file['path']);
                $parsed = $isMacDive ? \App\Support\MacDiveXmlParser::parse($file['path']) : UddfParser::parse($file['path']);
                array_push($dives, ...$parsed->dives);
                $sites += $parsed->sites;
                $generators[] = $parsed->generator ?: 'UDDF';
            }
            if ($shearwater) {
                $parsed = \App\Support\ShearwaterXmlParser::parseMany($shearwater);
                array_push($dives, ...$parsed->dives);
                $generators[] = $parsed->generator;
            }
            if ($garmin) {
                // Garmin Descent .FIT: no site names, but GPS entry points.
                $parsed = \App\Support\GarminFitParser::parseMany($garmin);
                array_push($dives, ...$parsed->dives);
                $sites += $parsed->sites;
                $generators[] = $parsed->generator;
            }
        } catch (\Throwable $e) {
            return $this->uploadError($request, 'A file could not be read: ' . $e->getMessage());
        }
        if (!count($dives)) {
            return $this->uploadError($request, 'No dives found in ' . (count($names) === 1 ? 'that file.' : 'those files.'));
        }
        $name = count($names) === 1 ? $names[0] : count($names) . ' files';
        $parsed = (object) ['dives' => $dives, 'sites' => $sites, 'generator' => implode(' + ', array_unique($generators))];

        $userId = $request->user()->id;

        // Existing dives (tombstones and attached logs too) for duplicate
        // detection. Light columns only; profiles load per candidate.
        $existing = Dive::withTrashed()->withoutGlobalScope('companions')->where('user_id', $userId)
            ->get(['id', 'computer_serial', 'fingerprint', 'started_at', 'deleted_at', 'duration_seconds', 'max_depth_m', 'source', 'merged_into_id']);
        $byKey = [];
        $byTime = [];
        foreach ($existing as $e) {
            if ($e->fingerprint) $byKey[strtolower((string) $e->computer_serial) . '|' . $e->fingerprint] = $e;
            $byTime[] = $e;
        }
        // An attached log stands for the dive it's attached to.
        $mainOf = fn ($e) => ($e->merged_into_id && !$e->deleted_at) ? $e->merged_into_id : $e->id;

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

        // "Auto-merge dives" (logbook settings, off by default): pre-tick
        // attaching sure matches; otherwise they import as dives that ask.
        $autoMerge = (bool) ($request->user()->logbook_auto_merge ?? false);
        $counts = ['total' => count($dives), 'new' => 0, 'fingerprint' => 0, 'time' => 0, 'file' => 0, 'upgrade' => 0, 'companion' => 0, 'no_profile' => 0];
        $computers = [];
        $items = [];
        $prev = null;
        foreach ($dives as $i => $d) {
            $kind = null; $ofDive = null; $ofItemPos = null; $decision = null;
            $key = strtolower((string) $d['computer_serial']) . '|' . $d['fingerprint'];
            if ($d['computer_serial'] && isset($byKey[$key])) {
                $kind = 'fingerprint'; $ofDive = $mainOf($byKey[$key]);
            } else {
                // Same dive already on file from THIS computer (or from one
                // that named no computer): +-3 min. Another computer's log
                // of it is not a duplicate - see the companion check below.
                $ts = strtotime($d['started_at']);
                foreach ($byTime as $e) {
                    if (abs($e->started_at->getTimestamp() - $ts) > self::TIME_WINDOW) continue;
                    if (SameDiveMatcher::differentComputers($d['computer_serial'], $e->computer_serial)) continue;
                    $kind = 'time'; $ofDive = $mainOf($e); break;
                }
            }
            // Two computers' logs of one dive in this same file (MacDive
            // holding both, or a folder of Petrel + Perdix files).
            if ($kind === null && $prev !== null && abs(strtotime($d['started_at']) - strtotime($prev['started_at'])) <= self::TIME_WINDOW) {
                if (SameDiveMatcher::differentComputers($d['computer_serial'], $prev['serial'])
                    && SameDiveMatcher::depthsAgree((float) $d['max_depth_m'], (float) $prev['depth'])
                    && SameDiveMatcher::durationsAgree((int) $d['duration_seconds'], (int) $prev['duration'])) {
                    $kind = 'companion'; $ofItemPos = $prev['pos']; $decision = $autoMerge ? 'attach' : 'import';
                    $d['_match'] = ['level' => 'strong', 'offset' => strtotime($prev['started_at']) - strtotime($d['started_at']), 'shape' => SameDiveMatcher::shapeSimilarity($d['profile'], $items[$prev['pos']]['payload']['profile'] ?? [])];
                } elseif (!count($prev['profile']) && count($d['profile'])) {
                    // Same dive, same computer twice: keep the copy with a
                    // profile (or the earlier one), skip the other.
                    $items[$prev['pos']]['duplicate_kind'] = 'file';
                    $items[$prev['pos']]['duplicate_of_item_pos'] = $i;
                    $items[$prev['pos']]['decision'] = 'skip';
                    $counts['file']++; $counts['new']--;
                } else {
                    $kind = 'file'; $ofItemPos = $prev['pos'];
                }
            }
            // Another computer already logged this dive: attach this log to
            // it (enrich) rather than add a twin. Clocks that agree are
            // pre-ticked; clocks hours apart only when the profiles agree.
            if ($kind === null && $d['computer_serial']) {
                $m = SameDiveMatcher::find($userId, $d);
                if ($m) {
                    $kind = 'companion'; $ofDive = $m['dive']->id;
                    $decision = ($autoMerge && $m['level'] === 'strong') ? 'attach' : 'import';
                    $d['_match'] = ['level' => $m['level'], 'offset' => $m['offset'], 'shape' => $m['shape'], 'corr' => $m['corr'] ?? null];
                }
            }
            // A computer's own file for a dive imported from a logbook (same
            // computer, or a logbook copy that named none): offer to upgrade
            // it (deco, PPO2, cells) instead of skipping it as a duplicate.
            if (!empty($d['computer_native']) && $ofDive && in_array($kind, ['fingerprint', 'time'], true)) {
                $match = $existing->firstWhere('id', $ofDive);
                if ($match && !$match->deleted_at && $match->source === 'uddf-import'
                    && !SameDiveMatcher::differentComputers($d['computer_serial'], $match->computer_serial)) $kind = 'upgrade';
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
                'decision' => $decision ?? ($kind === 'upgrade' ? 'upgrade' : ($kind ? 'skip' : 'import')),
                'payload' => $d,
            ];
            $prev = ['started_at' => $d['started_at'], 'profile' => $d['profile'], 'pos' => $i, 'serial' => $d['computer_serial'], 'depth' => $d['max_depth_m'], 'duration' => $d['duration_seconds']];
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

        $next = route('Logbook.import.review', $import->id);
        // The dropzone uploads with XHR (for its progress bar) and follows this.
        return $request->expectsJson() ? response()->json(['redirect' => $next]) : redirect($next);
    }

    private function uploadError(Request $request, string $message)
    {
        return $request->expectsJson()
            ? response()->json(['message' => $message], 422)
            : back()->withErrors(['file' => $message]);
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
        $dups = $import->items()->whereNotNull('duplicate_kind')->whereNotIn('duplicate_kind', ['upgrade', 'companion'])->with('duplicateOfDive')->get();
        $companions = $import->items()->where('duplicate_kind', 'companion')->with('duplicateOfDive')->orderBy('position')->get();
        $upgrades = $import->items()->where('duplicate_kind', 'upgrade')->with('duplicateOfDive')->get(['id', 'dive_import_id', 'started_at', 'computer_model', 'max_depth_m', 'duration_seconds', 'decision', 'duplicate_of_dive_id']);
        $itemsById = $import->items()->get(['id', 'started_at', 'computer_model', 'has_profile'])->keyBy('id');

        return view('pages.logbook.import.review', [
            'import' => $import,
            'dups' => $dups,
            'upgrades' => $upgrades,
            'companions' => $companions,
            'itemsById' => $itemsById,
        ]);
    }

    public function decisions(Request $request, $id)
    {
        $import = $this->load($request, $id);
        $chosen = array_map('intval', (array) $request->input('import_ids', []));
        $dupes = fn () => $import->items()->whereNotNull('duplicate_kind')->whereNotIn('duplicate_kind', ['upgrade', 'companion']);
        $dupes()->update(['decision' => 'skip']);
        if ($chosen) $dupes()->whereIn('id', $chosen)->update(['decision' => 'import']);
        // Upgrades of already-imported dives: on unless unticked.
        $upgrades = array_map('intval', (array) $request->input('upgrade_ids', []));
        $import->items()->where('duplicate_kind', 'upgrade')->update(['decision' => 'skip']);
        if ($upgrades) $import->items()->where('duplicate_kind', 'upgrade')->whereIn('id', $upgrades)->update(['decision' => 'upgrade']);
        // Same dive, another computer: ticked = attach to that dive (enrich
        // it), unticked = keep as a dive of its own.
        $attach = array_map('intval', (array) $request->input('attach_ids', []));
        $import->items()->where('duplicate_kind', 'companion')->update(['decision' => 'import']);
        if ($attach) $import->items()->where('duplicate_kind', 'companion')->whereIn('id', $attach)->update(['decision' => 'attach']);

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

    /** Countries the Divers Hub catalog covers (all Florida today). */
    private const CATALOG_COUNTRIES = ['us', 'usa', 'u.s.', 'u.s.a.', 'united states', 'united states of america', 'florida'];

    private static function nameScore(string $a, string $b): int
    {
        $a = mb_strtolower(trim(preg_replace('/\b(reef|wreck|ledge|site|the)\b/i', '', $a)));
        $b = mb_strtolower(trim(preg_replace('/\b(reef|wreck|ledge|site|the)\b/i', '', $b)));
        if ($a === '' || $b === '') return 0;
        if ($a === $b) return 100;
        if (mb_strlen($a) >= 4 && mb_strlen($b) >= 4 && (str_contains($a, $b) || str_contains($b, $a))) return 90;
        similar_text($a, $b, $pct);
        return (int) $pct;
    }

    /**
     * Best match for a file site (Pablo, 2026-10-16):
     *  - a different country than the catalog's -> never a Divers Hub site,
     *    whatever the name says;
     *  - high: name matches (same or partial) AND within 2 km;
     *  - check: within 1 km but another name, same name 2-25 km away, or
     *    same name where either side has no GPS;
     *  - none: create it as the diver's own site.
     * One of the diver's own sites with the same name always wins (high).
     *
     * @return array{kind:string, id:?int, confidence:string, why:string, distance_m:?int, weak?:bool}
     */
    public static function suggest(array $s, $catalog, $own): array
    {
        $name = trim($s['name'] ?? '');
        foreach ($own as $o) {
            if (mb_strtolower($o->name) === mb_strtolower($name)) {
                return ['kind' => 'own', 'id' => $o->id, 'confidence' => 'high', 'why' => 'Same name as one of your sites', 'distance_m' => null];
            }
        }
        $country = mb_strtolower(trim($s['country'] ?? ''));
        if ($country !== '' && !in_array($country, self::CATALOG_COUNTRIES, true)) {
            return ['kind' => 'create', 'id' => null, 'confidence' => 'none', 'why' => 'Divers Hub has no sites in ' . trim($s['country']) . ' yet', 'distance_m' => null];
        }

        $hasGps = ($s['latitude'] ?? null) !== null && ($s['longitude'] ?? null) !== null;
        if (!empty($s['gps_only']) && $hasGps) {
            // Only coordinates (a Garmin watch's GPS fix as the diver went
            // in): position is the whole match. The fix is taken at the
            // surface, so anything within ~300 m is the same site.
            $best = null; $bestM = null;
            foreach ($catalog as $c) {
                if ($c['lat'] === null || $c['lon'] === null) continue;
                $dm = self::km($s['latitude'], $s['longitude'], $c['lat'], $c['lon']) * 1000;
                if ($bestM === null || $dm < $bestM) { $best = $c; $bestM = $dm; }
            }
            if ($best && $bestM <= 1000) {
                return ['kind' => 'catalog', 'id' => $best['id'], 'confidence' => $bestM <= 300 ? 'high' : 'check',
                    'why' => $best['name'] . ' is ', 'distance_m' => (int) round($bestM), 'suffix' => ' from the GPS entry point'];
            }
            return ['kind' => 'create', 'id' => null, 'confidence' => 'none',
                'why' => $best ? 'Nearest Divers Hub site, ' . $best['name'] . ', is ' : 'Nothing close in Divers Hub', 'distance_m' => $best ? (int) round($bestM) : null, 'suffix' => $best ? ' away' : ''];
        }
        $nearest = null; $nearestM = null; $bestName = null; $bestNameScore = 0; $bestNameM = null;
        foreach ($catalog as $c) {
            $dm = ($hasGps && $c['lat'] !== null && $c['lon'] !== null) ? self::km($s['latitude'], $s['longitude'], $c['lat'], $c['lon']) * 1000 : null;
            if ($dm !== null && ($nearestM === null || $dm < $nearestM)) { $nearest = $c; $nearestM = $dm; }
            $score = self::nameScore($name, $c['name']);
            if ($score >= 85 && ($score > $bestNameScore || ($score === $bestNameScore && $dm !== null && ($bestNameM === null || $dm < $bestNameM)))) {
                $bestName = $c; $bestNameScore = $score; $bestNameM = $dm;
            }
        }

        if ($bestName && $bestNameM !== null && $bestNameM <= 2000) {
            return ['kind' => 'catalog', 'id' => $bestName['id'], 'confidence' => 'high', 'why' => ($bestNameScore === 100 ? 'Same name' : 'Name matches ' . $bestName['name']) . ', ', 'distance_m' => (int) round($bestNameM)];
        }
        if ($nearest && $nearestM <= 1000) {
            return ['kind' => 'catalog', 'id' => $nearest['id'], 'confidence' => 'check', 'why' => 'Different name, but ' . $nearest['name'] . ' is ', 'distance_m' => (int) round($nearestM), 'suffix' => ' away'];
        }
        if ($bestName && ($bestNameM === null || $bestNameM <= 25000)) {
            return ['kind' => 'catalog', 'id' => $bestName['id'], 'confidence' => 'check',
                'why' => 'Name matches ' . $bestName['name'] . ($bestNameM === null ? ' (no GPS to compare)' : ', but '), 'distance_m' => $bestNameM !== null ? (int) round($bestNameM) : null, 'suffix' => $bestNameM !== null ? ' apart' : ''];
        }
        return ['kind' => 'create', 'id' => null, 'confidence' => 'none',
            'why' => $nearest ? 'Nearest Divers Hub site, ' . $nearest['name'] . ', is ' : 'Nothing close in Divers Hub', 'distance_m' => $nearest ? (int) round($nearestM) : null, 'suffix' => $nearest ? ' away' : ''];
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
        $remaining = $import->items()->whereIn('decision', ['import', 'upgrade', 'attach'])->whereNull('imported_dive_id')->count();
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

        // Attachments last, so the dive they attach to (maybe from this same
        // file) already exists.
        $batch = $import->items()->whereIn('decision', ['import', 'upgrade', 'attach'])->whereNull('imported_dive_id')
            ->orderByRaw("decision = 'attach'")->orderBy('position')->limit(self::RUN_BATCH)->get();
        $upgraded = 0; $attached = 0;
        foreach ([$batch] as $items) {
                foreach ($items as $item) {
                    $p = $item->payload;
                    if ($item->decision === 'upgrade') {
                        // The computer's file replaces what the logbook guessed.
                        $target = Dive::where('user_id', $user->id)->find($item->duplicate_of_dive_id);
                        if ($target) {
                            // The file knows low/high but not the values; the
                            // logbook copy may (MacDive setpoint events).
                            $known = collect($target->profile ?? [])->pluck('setpoint')->filter(fn ($v) => $v > 0)->map(fn ($v) => round($v, 2))->unique()->sort()->values();
                            if ($known->count() >= 2) {
                                foreach ($p['profile'] as &$smp) {
                                    if (isset($smp['setpointLevel'])) $smp['setpoint'] = $smp['setpointLevel'] === 'high' ? $known->last() : $known->first();
                                }
                                unset($smp);
                            }
                            $target->replaceComputerFacts($p, $p['source'] ?? 'computer-file');
                            $item->update(['imported_dive_id' => $target->id]);
                            $upgraded++;
                        } else {
                            $item->update(['decision' => 'skip']);
                        }
                        continue;
                    }
                    if ($item->decision === 'attach') {
                        $target = null;
                        if ($item->duplicate_of_dive_id) {
                            $target = Dive::where('user_id', $user->id)->find($item->duplicate_of_dive_id);
                        } elseif ($item->duplicate_of_item_id) {
                            $tid = DiveImportItem::where('id', $item->duplicate_of_item_id)->value('imported_dive_id');
                            $target = $tid ? Dive::where('user_id', $user->id)->find($tid) : null;
                        }
                        if ($target) {
                            $facts = $p; unset($facts['conditions'], $facts['tags']);
                            $log = $target->attachComputerLog($facts);
                            $item->update(['imported_dive_id' => $log->id]);
                            $attached++;
                            continue;
                        }
                        // The dive it belonged with wasn't imported: this log
                        // becomes the dive instead.
                        $item->decision = 'import';
                    }
                    // Last-second duplicate guard (the same file imported twice).
                    if ($p['computer_serial'] && Dive::withTrashed()->withoutGlobalScope('companions')->where('user_id', $user->id)->where('computer_serial', $p['computer_serial'])->where('fingerprint', $p['fingerprint'])->exists()) {
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
                    // A known operator that runs a single boat: that's the
                    // boat (Pablo, 2026-10-16), with its picture and logo.
                    if (!empty($conditions['operator_name'])) {
                        $op = $this->catalogOperator($conditions['operator_name']);
                        if ($op) {
                            if ($op['logo']) $conditions['operator_logo'] ??= $op['logo'];
                            $conditions['operator_url'] ??= $op['url'];
                            if (empty($conditions['boat_name']) && count($op['boats']) === 1) {
                                $conditions['boat_name'] = $op['boats'][0]['name'];
                                if ($op['boats'][0]['pic']) $conditions['boat_pic'] = $op['boats'][0]['pic'];
                            }
                        }
                    }
                    if (!empty($p['rating'])) $conditions['rating'] = (int) $p['rating'];

                    // Same dive as another computer's, not attached: imported
                    // as its own dive that asks (844.b) until the diver answers.
                    if ($item->duplicate_kind === 'companion') {
                        $askAbout = $item->duplicate_of_dive_id;
                        if (!$askAbout && $item->duplicate_of_item_id) $askAbout = DiveImportItem::where('id', $item->duplicate_of_item_id)->value('imported_dive_id');
                        if ($askAbout) {
                            $m = $p['_match'] ?? [];
                            $conditions['same_dive_suggestion'] = ['dive_id' => (int) $askAbout, 'offset' => (int) ($m['offset'] ?? 0), 'shape' => $m['shape'] ?? null, 'corr' => $m['corr'] ?? null, 'level' => $m['level'] ?? 'strong'];
                        }
                    }
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
                            'location' => $p['location'] ?? null,
                            'profile' => $p['profile'] ?? [],
                            'site_id' => $siteId,
                            'user_site_id' => $userSiteId,
                            'notes' => $p['notes'] ?? null,
                            'conditions' => $conditions ?: null,
                            'source' => $p['source'] ?? 'uddf-import',
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
        $result = ($summary['result'] ?? []) + ['created' => 0, 'skipped' => 0, 'sites_created' => 0, 'upgraded' => 0, 'attached' => 0];
        $result['created'] += $created;
        $result['attached'] += $attached;
        $result['upgraded'] += $upgraded;
        $result['sites_created'] += count($createdSites);
        $import->sites = $siteMap;

        $remaining = $import->items()->whereIn('decision', ['import', 'upgrade', 'attach'])->whereNull('imported_dive_id')->count();
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

        return redirect()->route('Logbook')->with('status', 'Imported ' . $result['created'] . ' dive' . ($result['created'] === 1 ? '' : 's') . ($result['upgraded'] ? ', upgraded ' . $result['upgraded'] . ' with computer data' : '') . ($result['attached'] ? ', added ' . $result['attached'] . ' second-computer log' . ($result['attached'] === 1 ? '' : 's') . ' to dives you already had' : '') . ' from ' . $import->filename . ($result['skipped'] ? ' (' . $result['skipped'] . ' skipped as duplicates)' : '') . ($result['sites_created'] ? ', created ' . $result['sites_created'] . ' of your own sites' : '') . '.');
    }

    /** Catalog operator by name (case-insensitive), with its boats - cached per run. */
    private array $operatorCache = [];

    private function catalogOperator(string $name): ?array
    {
        $key = mb_strtolower(trim($name));
        if (!array_key_exists($key, $this->operatorCache)) {
            $op = \App\Models\Operator::whereRaw('LOWER(operatorName) = ?', [$key])->first();
            $this->operatorCache[$key] = $op ? [
                'logo' => $op->logoUrl ? \App\Support\AssetPath::relative('/assets' . $op->logoUrl) : null,
                'url' => \App\Support\AssetPath::relative(route('OperatorDetails', ['id' => $op->slug ?? $op->id])),
                'boats' => \App\Models\Boat::where('operatorId', $op->id)->get(['name', 'pic'])
                    ->map(fn ($b) => ['name' => $b->name, 'pic' => $b->pic ? \App\Support\AssetPath::relative('/assets' . $b->pic) : null])->all(),
            ] : null;
        }
        return $this->operatorCache[$key];
    }

    public function cancel(Request $request, $id)
    {
        $import = $this->load($request, $id);
        $import->delete();

        return redirect()->route('Logbook.import.index')->with('status', 'Import discarded.');
    }
}

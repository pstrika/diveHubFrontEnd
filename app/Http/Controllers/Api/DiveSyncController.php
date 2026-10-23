<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dive;
use App\Models\DiveChange;
use App\Support\AssetPath;
use App\Support\DiveGasStats;
use Illuminate\Http\Request;

/**
 * Pull side of phone sync (SYNC-CONTRACT.md sections 3-4), the
 * counterpart to DiveUploadController's push side.
 *
 * `dive` objects in both endpoints here use this app's real column names
 * (`notes` + the `conditions` JSON blob - operator_name, divemaster,
 * boat_name, boat_captain, current, weather, surface_conditions,
 * entry_type, visibility, buddies, dive_type) rather than the contract
 * doc's illustrative `notes`/`buddy`/`rating` shorthand - there is no
 * `rating` column, and buddies live inside `conditions`, not as their own
 * field.
 *
 * `site_id` stays for compatibility; `site` (Pablo/uploader session,
 * 2026-10-14: "app shows basic site info, website has the rich page") is
 * the resolved nested object - name, decimal lat/lon (Site::gpsLat/gpsLon
 * are degrees+decimal-minutes strings like "26° 19.500' N", parsed by
 * Site::decimalLatitude()/decimalLongitude() so clients don't each guess
 * the format), and a ready-made public URL. Null whenever site_id is
 * null or the id doesn't resolve on mysql_trips (Dive::site() already
 * handles both - no DB-level FK since Site lives on that separate
 * connection). No current web path sets site_id at all yet (site tagging
 * is docs/roadmap.md #9, not built) - nothing to log to dive_changes for
 * that yet either; wire it in alongside that feature when it exists.
 */
class DiveSyncController extends Controller
{
    /**
     * The full dive object (show/index): profile and tanks are what the web
     * chart shows - tank pressure and deco filled in from another computer
     * on the dive when the main one had none (DiveChartData) - so the apps
     * draw the same chart (2026-10-21: iOS showed no pressures on a Petrel +
     * NERD dive). pressure_from / deco_from name that computer, or null.
     */
    private function fullArray(Dive $dive): array
    {
        $chart = \App\Support\DiveChartData::resolve($dive, $dive->relationLoaded('companions') ? $dive->companions : null);
        $view = clone $dive;
        $view->profile = $chart['profile'];
        $view->tanks = $chart['tanks'];
        return array_merge(
            $this->syncArray($view),
            ['profile' => $view->profile, 'sensor_data' => $dive->sensor_data, 'pressure_from' => $chart['pressure_from'], 'deco_from' => $chart['deco_from']],
            DiveGasStats::block($view)
        );
    }

    private ?string $syncPressureFrom = null;

    /** Cylinders with pressures, borrowed from an attached computer's log when the main one has none. */
    private function syncTanks(Dive $dive): ?array
    {
        $logs = $dive->relationLoaded('companions') ? $dive->companions : $dive->companions()->get();
        $r = \App\Support\DiveChartData::tanksFor($dive, $logs);
        $this->syncPressureFrom = $r['pressure_from'];
        return $r['tanks'];
    }

    /** What index() / show() eager-load so syncArray() runs no query per dive. */
    public const SYNC_WITH = ['photos.species', 'videos', 'species', 'equipment', 'companions', 'userSite'];
    /** The change feed: no profile JSON for the dives or their logs (syncTanks() borrows cylinders from the logs' tank lists). */
    public const FEED_WITH = ['photos.species', 'videos', 'species', 'equipment', 'userSite'];

    /** sensor_data without its per-sample arrays (anything over 20 entries). */
    public static function compactSensorData($data): ?array
    {
        if (!is_array($data)) return $data;
        return array_filter($data, fn ($v) => !is_array($v) || count($v) <= 20);
    }

    private function syncArray(Dive $dive): array
    {
        // The feed is always the owner's: no users lookup per dive.
        $viewer = auth()->user() ?: $dive->user;
        $companions = $dive->relationLoaded('companions') ? $dive->companions : $dive->companions()->get();
        $photos = $dive->relationLoaded('photos') ? $dive->photos : $dive->photos()->with('species')->get();
        $videos = $dive->relationLoaded('videos') ? $dive->videos : $dive->videos()->get();
        // toApi() prints the dive's uuid through $photo->dive: hand it the
        // parent instead of a full dive load (profile and all) per photo.
        $photos->each(fn ($p) => $p->setRelation('dive', $dive));
        $videos->each(fn ($v) => $v->setRelation('dive', $dive));
        return [
            // Integer id too (iOS session, 2026-10-15): the web URL is
            // Logbook/{id}, and the dive-number tie-break is by id.
            'id' => $dive->id,
            'uuid' => $dive->uuid,
            'computer_family' => $dive->computer_family,
            'computer_model' => $dive->computer_model,
            'computer_serial' => $dive->computer_serial,
            'fingerprint' => $dive->fingerprint,
            'dive_number' => $dive->dive_number,
            'started_at' => optional($dive->started_at)->toIso8601String(),
            'duration_seconds' => $dive->duration_seconds,
            'max_depth_m' => $dive->max_depth_m,
            'avg_depth_m' => $dive->avg_depth_m,
            'surface_temp_c' => $dive->surface_temp_c,
            'min_water_temp_c' => $dive->min_water_temp_c,
            'max_water_temp_c' => $dive->max_water_temp_c,
            'dive_mode' => $dive->dive_mode,
            'gas_mixes' => $dive->gas_mixes,
            // The same cylinders the web chart shows: when this computer has
            // no transmitter, the pressures of another computer worn on the
            // dive (DiveChartData). The change feed sent the bare row before,
            // so the app showed no consumption on a Petrel + NERD dive
            // merged on the web (Pablo, 2026-10-22).
            'tanks' => $this->syncTanks($dive),
            'pressure_from' => $this->syncPressureFrom,
            'deco_model' => $dive->deco_model,
            'location' => $dive->location,
            // Scalars only in the summary (cns, setpoint, salinity...): a NERD's
            // ppo2Readings is thousands of entries per dive, and decoding it for
            // 200 dives blew the feed's 128 MB (2026-10-22). The full record keeps it.
            'sensor_data' => self::compactSensorData($dive->sensor_data),
            'site_id' => $dive->site_id,
            'site' => $this->siteArray($dive),
            'notes' => $dive->notes,
            // An empty PHP array encodes as JSON [] - a list, not the object
            // clients expect here (iOS session, 2026-10-15). Empty = null.
            'conditions' => $this->conditionsForApi($dive->conditions),
            'tags' => $dive->tags ?? [],
            // Effective classification (saved pick, else the depth-based
            // default) and the row title the web list shows, so the app
            // mirrors Logbook.blade.php without re-deriving either.
            'classification' => $dive->classification(),
            // "Closed Circuit" / "Open Circuit Sidemount" / "Open Circuit",
            // and the two computer facts behind mode + classification.
            'mode_label' => $dive->modeLabel(),
            'is_sidemount' => $dive->isSidemount(),
            'entered_deco' => $dive->enteredDeco(),
            'title' => $dive->title(),
            // The diver's own site, when the dive is tagged with one (Pablo,
            // 2026-10-16) - `site` below then carries it with custom:true.
            'user_site_id' => $dive->user_site_id,
            // Manual entries / imports without a profile: the app has no
            // chart to draw and the facts are editable there too.
            'source' => $dive->source,
            'has_profile' => $dive->hasProfile(),
            'facts_editable' => $dive->factsEditable(),
            // Open "same dive, another computer?" question (web 12.14.0):
            // null, or {label: "844.b", with: [{uuid, label, computer_model}],
            // asking: bool}. The app shows the dive highlighted and offers
            // POST /api/dives/{uuid}/combine and /not-same.
            'same_dive' => \App\Support\SameDivePending::syncBlock($dive),
            // Other computers already combined into this dive.
            'computers' => $companions->map(fn ($c) => ['model' => $c->computer_model, 'serial' => $c->computer_serial])->values(),
            // Photos on the dive (web 12.17.0) - URLs need the bearer token.
            'photos' => $photos->map->toApi()->values(),
            // Videos (web 12.21.0): null when the account doesn't have them, else the list (playback URLs good ~1 h).
            'videos' => \App\Support\EarlyAccess::allows($viewer, 'videos') && \App\Support\CloudflareStream::configured()
                ? $videos->map(fn ($v) => $v->refreshStream()->toApi())->values() : null,
            // Early-access blocks: null when the account doesn't have the
            // feature (GET /api/me/features), else the dive's list.
            'species' => \App\Support\EarlyAccess::allows($viewer, 'species') ? \App\Http\Controllers\Api\LogbookExtrasApiController::diveSpecies($dive) : null,
            'equipment' => \App\Support\EarlyAccess::allows($viewer, 'equipment')
                ? collect(\App\Http\Controllers\EquipmentController::diveList($dive))->map(fn ($i) => ['uuid' => $i['uuid'], 'auto' => $i['auto']])->values() : null,
            'updated_at' => optional($dive->updated_at)->toIso8601String(),
        ];
    }

    /**
     * Stored conditions carry host-free paths for the image/link fields
     * (App\Support\AssetPath); the app wants absolute URLs. Empty = null,
     * never [] (a JSON list where clients expect an object).
     */
    private function conditionsForApi(?array $conditions): ?array
    {
        if (!$conditions) return null;
        foreach (['operator_logo', 'boat_pic', 'operator_url'] as $k) {
            if (!empty($conditions[$k])) $conditions[$k] = AssetPath::absolute($conditions[$k]);
        }
        return $conditions;
    }

    /** The requesting diver's wishlisted site ids, fetched once per request. */
    private ?array $wishedSiteIds = null;

    private function siteArray(Dive $dive): ?array
    {
        $site = $dive->site();
        if (!$site) {
            // One of the diver's own sites (user_sites) - same keys, plus
            // custom:true and the extra fields a custom site carries.
            $us = $dive->user_site_id ? $dive->userSite : null;
            if (!$us) return null;
            return [
                'id' => $us->id,
                'name' => $us->name,
                'city' => $us->cityLabel(),
                'latitude' => $us->latitude,
                'longitude' => $us->longitude,
                'url' => $us->publicUrl(),
                'wished' => false,
                'custom' => true,
                'type' => $us->type,
                'min_cert' => $us->min_cert,
                'water_type' => $us->water_type,
                'altitude_m' => $us->altitude_m,
                'location' => $us->location,
                'country' => $us->country,
            ];
        }

        if ($this->wishedSiteIds === null) {
            $this->wishedSiteIds = \App\Models\WishedSite::where('userId', $dive->user_id)->pluck('siteId')->map(fn ($v) => (int) $v)->flip()->all();
        }

        return [
            'id' => $site->id,
            'name' => $site->name,
            // Coast code (sites.location, e.g. FLL) as the city the web
            // logbook list shows after the operator name.
            'city' => \App\Support\SiteLocations::name($site->location),
            'latitude' => $site->decimalLatitude(),
            'longitude' => $site->decimalLongitude(),
            'url' => $site->publicUrl(),
            // On the diver's wishlist (wishedSites) - the bookmark on a
            // logbook row.
            'wished' => isset($this->wishedSiteIds[(int) $site->id]),
            'custom' => false,
        ];
    }

    /** GET /api/dives/changes?since=<seq>&limit=200 */
    public function changes(Request $request)
    {
        $data = $request->validate([
            'since' => 'nullable|integer|min:0',
            'limit' => 'nullable|integer|min:1|max:200',
        ]);

        // $request->validate()'s 'integer' rule checks the format but
        // doesn't cast the type, so a query string ?since=0 stays a PHP
        // string here - explicit (int) casts keep next_since consistently
        // an integer whether it echoes this or comes from a change row's
        // own id (Pablo's uploader session, 2026-10-14: "a consistent
        // integer would be tidier for the Android client").
        $since = (int) ($data['since'] ?? 0);
        $limit = (int) ($data['limit'] ?? 200);
        $user = $request->user();

        $rows = DiveChange::where('user_id', $user->id)
            ->where('id', '>', $since)
            ->orderBy('id')
            ->limit($limit)
            ->get();

        $uuids = $rows->pluck('dive_uuid')->unique();
        // Attached logs included: a dive combined into another later still
        // replays its older created/updated entries, then its 'deleted'.
        $dives = Dive::withTrashed()->withoutGlobalScope('companions')
            ->where('user_id', $user->id)
            ->whereIn('uuid', $uuids)
            ->select(Dive::feedColumns())
            ->with(self::FEED_WITH)
            ->with(['companions' => fn ($q) => $q->select(Dive::feedColumns())])
            ->get()
            ->keyBy('uuid');
        Dive::preloadSites($dives);

        $changes = $rows->map(function (DiveChange $change) use ($dives) {
            $dive = $dives->get($change->dive_uuid);
            return [
                'seq' => $change->id,
                'uuid' => $change->dive_uuid,
                'action' => $change->action,
                'dive' => ($dive && $change->action !== 'deleted') ? $this->syncArray($dive) : null,
            ];
        });

        return response()->json([
            'changes' => $changes,
            'next_since' => $changes->isNotEmpty() ? $changes->last()['seq'] : $since,
            'has_more' => $rows->count() === $limit,
        ]);
    }

    /** GET /api/dives/{uuid} - full record including profile, for a dive the app doesn't hold locally yet. */
    public function show(Request $request, string $uuid)
    {
        $dive = Dive::where('user_id', $request->user()->id)
            ->where('uuid', $uuid)
            ->with(self::SYNC_WITH)
            ->firstOrFail();

        return response()->json($this->fullArray($dive));
    }

    /**
     * GET /api/dives?uuids=a,b,c - up to 20 full records (same object as
     * show(), profile included) in one call, so a fresh install pulls a
     * logbook in N/20 requests rather than N (iOS session, 2026-10-15).
     * Unknown, deleted or someone else's uuids are simply absent from the
     * result - the client already knows which ones it asked for.
     */
    public function index(Request $request)
    {
        $raw = (string) $request->query('uuids', '');
        $uuids = array_values(array_unique(array_filter(array_map('trim', explode(',', $raw)))));

        if (!count($uuids)) {
            return response()->json(['message' => 'uuids is required'], 422);
        }
        if (count($uuids) > 20) {
            return response()->json(['message' => 'At most 20 uuids per request'], 422);
        }
        foreach ($uuids as $uuid) {
            if (!preg_match('/^[0-9a-fA-F-]{36}$/', $uuid)) {
                return response()->json(['message' => 'Invalid uuid: ' . $uuid], 422);
            }
        }

        $dives = Dive::where('user_id', $request->user()->id)
            ->whereIn('uuid', $uuids)
            ->with(self::SYNC_WITH)
            ->get();
        Dive::preloadSites($dives);
        $dives = $dives->map(fn (Dive $dive) => $this->fullArray($dive))
            ->values();

        return response()->json(['dives' => $dives]);
    }

    /**
     * GET /api/dives/uuids - every live dive the user owns, cheapest
     * possible: a gap detector for the change feed (iOS session,
     * 2026-10-15), so a client can diff against what it holds and fetch
     * whatever a missed change row would have hidden. Ordered like the
     * logbook (started_at, then id).
     */
    public function uuids(Request $request)
    {
        $dives = Dive::where('user_id', $request->user()->id)
            ->orderBy('started_at')->orderBy('id')
            ->get(['id', 'uuid', 'updated_at'])
            ->map(fn (Dive $d) => ['id' => $d->id, 'uuid' => $d->uuid, 'updated_at' => optional($d->updated_at)->toIso8601String()])
            ->values();

        return response()->json(['dives' => $dives, 'count' => $dives->count()]);
    }
}

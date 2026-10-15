<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dive;
use App\Models\DiveChange;
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
    private function syncArray(Dive $dive): array
    {
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
            'tanks' => $dive->tanks,
            'deco_model' => $dive->deco_model,
            'location' => $dive->location,
            'sensor_data' => $dive->sensor_data,
            'site_id' => $dive->site_id,
            'site' => $this->siteArray($dive),
            'notes' => $dive->notes,
            // An empty PHP array encodes as JSON [] - a list, not the object
            // clients expect here (iOS session, 2026-10-15). Empty = null.
            'conditions' => $dive->conditions ?: null,
            'tags' => $dive->tags ?? [],
            // Effective classification (saved pick, else the depth-based
            // default) and the row title the web list shows, so the app
            // mirrors Logbook.blade.php without re-deriving either.
            'classification' => $dive->classification(),
            'title' => $dive->site()->name ?? ($dive->conditions['site_name_custom'] ?? (ucfirst($dive->classification()) . ' Dive')),
            'updated_at' => optional($dive->updated_at)->toIso8601String(),
        ];
    }

    private function siteArray(Dive $dive): ?array
    {
        $site = $dive->site();
        if (!$site) return null;

        return [
            'id' => $site->id,
            'name' => $site->name,
            'latitude' => $site->decimalLatitude(),
            'longitude' => $site->decimalLongitude(),
            'url' => $site->publicUrl(),
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
        $dives = Dive::withTrashed()
            ->where('user_id', $user->id)
            ->whereIn('uuid', $uuids)
            ->get()
            ->keyBy('uuid');

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
            ->firstOrFail();

        return response()->json($this->syncArray($dive) + ['profile' => $dive->profile] + DiveGasStats::block($dive));
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
            ->get()
            ->map(fn (Dive $dive) => $this->syncArray($dive) + ['profile' => $dive->profile] + DiveGasStats::block($dive))
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

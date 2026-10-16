<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dive;
use App\Models\DiveChange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Upload endpoint for the native dive-computer uploader apps (Pablo,
 * 2026-10-14: iOS first via LibDC-Swift/libdivecomputer, Android later).
 * Sanctum-authenticated with a personal access token the diver generates
 * from their own account settings (UploadTokenController) - not a
 * password/Google login flow inside the native app itself, since a chunk
 * of users only ever signed in via Google and have no password they'd
 * know, and this way the narrow uploader app never needs any OAuth UI.
 *
 * Batched, not one dive per request - a single BLE sync session typically
 * surfaces several new dives at once (DiveLogRetriever downloads however
 * many are new since the last fingerprint), so the app uploads everything
 * it just pulled off the computer in one call.
 *
 * Identity and dedup follow DiversHub-Uploader/docs/SYNC-CONTRACT.md,
 * proposed by the uploader session 2026-10-14 and confirmed here:
 * - Dedup key is (user_id, computer_serial, fingerprint), not fingerprint
 *   alone - a bare fingerprint (just a start timestamp on Shearwater) can
 *   collide across two different physical computers.
 * - Every dive carries a client-generated `uuid`. On a duplicate match,
 *   the SERVER's existing uuid is returned (never the client's) so two
 *   phones that download the same dive converge on one identity.
 * - A duplicate that matches a soft-deleted row goes in `deleted`, not
 *   `created` or `duplicates` - the dive was removed on the web and must
 *   not be silently resurrected by a re-upload.
 * - Every create is logged to `dive_changes` in the same transaction, for
 *   DiveSyncController's change feed to replay to other phones.
 */
class DiveUploadController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'computer' => 'required|array',
            'computer.family' => 'required|string|max:50',
            'computer.model' => 'required|string|max:100',
            'computer.serial' => 'nullable|string|max:100',

            'dives' => 'required|array|min:1|max:200',
            'dives.*.uuid' => 'nullable|uuid',
            'dives.*.dive_number' => 'nullable|integer',
            'dives.*.datetime' => 'required|date',
            'dives.*.duration_seconds' => 'required|integer|min:0',
            'dives.*.max_depth_m' => 'required|numeric|min:0',
            'dives.*.avg_depth_m' => 'nullable|numeric|min:0',
            'dives.*.surface_temp_c' => 'nullable|numeric',
            'dives.*.min_water_temp_c' => 'nullable|numeric',
            'dives.*.max_water_temp_c' => 'nullable|numeric',
            'dives.*.dive_mode' => 'nullable|in:freedive,gauge,open_circuit,closed_circuit,semi_closed_circuit',
            'dives.*.gas_mixes' => 'nullable|array',
            'dives.*.tanks' => 'nullable|array',
            'dives.*.deco_model' => 'nullable|array',
            'dives.*.location' => 'nullable|array',
            'dives.*.sensor_data' => 'nullable|array',
            'dives.*.fingerprint' => 'nullable|string|max:512', // base64, or the app's synthesised "sha256:..." form
            'dives.*.profile' => 'required|array',
        ]);

        $user = $request->user();
        $created = [];
        $duplicates = [];
        $deleted = [];

        foreach ($data['dives'] as $i => $diveData) {
            $serial = $data['computer']['serial'] ?? null;
            $fingerprint = $diveData['fingerprint'] ?? null;

            DB::transaction(function () use ($data, $diveData, $user, $serial, $fingerprint, $i, &$created, &$duplicates, &$deleted) {
                $existing = $fingerprint
                    ? Dive::withTrashed()
                        ->where('user_id', $user->id)
                        ->where('computer_serial', $serial)
                        ->where('fingerprint', $fingerprint)
                        ->first()
                    : null;

                // An imported (UDDF) copy of this same dive: matched by the
                // exact key above for Shearwater, otherwise by start time
                // (+-3 min) on the same computer, or on an import that named
                // no computer. Never matched against another computer's
                // synced dive - two computers on one dive stay two rows.
                if (!$existing) {
                    $ts = strtotime($diveData['datetime']);
                    // computer-file too: a Garmin .FIT import has no
                    // fingerprint the app would reproduce, so the app's
                    // later sync of that dive is matched by time instead.
                    $existing = Dive::where('user_id', $user->id)
                        ->whereIn('source', ['uddf-import', 'computer-file'])
                        ->whereBetween('started_at', [date('Y-m-d H:i:s', $ts - 180), date('Y-m-d H:i:s', $ts + 180)])
                        ->where(fn ($q) => $q->whereNull('computer_serial')->orWhere('computer_serial', $serial))
                        ->orderByRaw('ABS(TIMESTAMPDIFF(SECOND, started_at, ?))', [date('Y-m-d H:i:s', $ts)])
                        ->first();
                }

                if ($existing && !$existing->trashed() && in_array($existing->source, ['uddf-import', 'computer-file'], true)) {
                    // The computer wins (Pablo, 2026-10-16): its depth/time/
                    // mode/gases/tanks/PPO2 replace what the import guessed;
                    // everything the diver added - site, notes, buddies,
                    // tags, operator, conditions - stays. Gas pairings were
                    // keyed to the import's gas list, so they reset.
                    $existing->replaceComputerFacts([
                        'computer_family' => $data['computer']['family'],
                        'computer_model' => $data['computer']['model'],
                        'computer_serial' => $serial,
                        'fingerprint' => $fingerprint,
                        'started_at' => $diveData['datetime'],
                        'duration_seconds' => $diveData['duration_seconds'],
                        'max_depth_m' => $diveData['max_depth_m'],
                        'avg_depth_m' => $diveData['avg_depth_m'] ?? null,
                        'surface_temp_c' => $diveData['surface_temp_c'] ?? null,
                        'min_water_temp_c' => $diveData['min_water_temp_c'] ?? null,
                        'max_water_temp_c' => $diveData['max_water_temp_c'] ?? null,
                        'dive_mode' => $diveData['dive_mode'] ?? null,
                        'gas_mixes' => $diveData['gas_mixes'] ?? null,
                        'tanks' => $diveData['tanks'] ?? null,
                        'deco_model' => $diveData['deco_model'] ?? null,
                        'location' => $diveData['location'] ?? null,
                        'sensor_data' => $diveData['sensor_data'] ?? null,
                        'profile' => $diveData['profile'],
                    ], 'ios-uploader');
                    // Reported as a duplicate (server uuid wins), flagged so
                    // the app knows to refetch the full record.
                    $duplicates[] = ['index' => $i, 'uuid' => $existing->uuid, 'id' => $existing->id, 'dive_number' => $existing->dive_number, 'replaced_import' => true];
                    return;
                }

                if ($existing && $existing->trashed()) {
                    $deleted[] = ['index' => $i, 'uuid' => $existing->uuid, 'id' => $existing->id];
                    return;
                }

                if ($existing) {
                    // Server's uuid always wins, even if the client sent a
                    // different one - this is what lets two phones that
                    // downloaded the same dive converge on one identity.
                    $duplicates[] = ['index' => $i, 'uuid' => $existing->uuid, 'id' => $existing->id, 'dive_number' => $existing->dive_number];
                    return;
                }

                $dive = Dive::create([
                    'user_id' => $user->id,
                    'uuid' => $diveData['uuid'] ?? (string) Str::uuid(),
                    'computer_family' => $data['computer']['family'],
                    'computer_model' => $data['computer']['model'],
                    'computer_serial' => $serial,
                    'fingerprint' => $fingerprint,
                    'dive_number' => $diveData['dive_number'] ?? null,
                    'started_at' => $diveData['datetime'],
                    'duration_seconds' => $diveData['duration_seconds'],
                    'max_depth_m' => $diveData['max_depth_m'],
                    'avg_depth_m' => $diveData['avg_depth_m'] ?? null,
                    'surface_temp_c' => $diveData['surface_temp_c'] ?? null,
                    'min_water_temp_c' => $diveData['min_water_temp_c'] ?? null,
                    'max_water_temp_c' => $diveData['max_water_temp_c'] ?? null,
                    'dive_mode' => $diveData['dive_mode'] ?? null,
                    'gas_mixes' => $diveData['gas_mixes'] ?? null,
                    'tanks' => $diveData['tanks'] ?? null,
                    'deco_model' => $diveData['deco_model'] ?? null,
                    'location' => $diveData['location'] ?? null,
                    'sensor_data' => $diveData['sensor_data'] ?? null,
                    'profile' => $diveData['profile'],
                    'source' => 'ios-uploader',
                ]);

                DiveChange::record($user->id, $dive->uuid, 'created');

                $created[] = ['index' => $i, 'uuid' => $dive->uuid, 'id' => $dive->id, 'dive_number' => $dive->dive_number];
            });
        }

        return response()->json([
            'created' => $created,
            'duplicates' => $duplicates,
            'deleted' => $deleted,
        ], 201);
    }
}

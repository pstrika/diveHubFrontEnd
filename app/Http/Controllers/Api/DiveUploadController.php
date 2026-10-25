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
        // Large uploads (2026-10-25, Andre's Divesoft Liberty: 3-18 MB,
        // ~240 KB of profile per dive) died here. A `dives.*.x` rule makes
        // the validator flatten the whole payload - every field of every
        // profile sample - into one dotted array: 128 MB gone before a
        // single dive was saved. The dive-level fields are now validated
        // one dive at a time on a copy without the profile, and the
        // profile is only checked for being a non-empty list.
        @ini_set('memory_limit', '512M');
        @set_time_limit(180);
        $request->validate([
            'computer' => 'required|array',
            'computer.family' => 'required|string|max:50',
            'computer.model' => 'required|string|max:100',
            'computer.serial' => 'nullable|string|max:100',
            'dives' => 'required|array|min:1|max:200',
        ]);
        $diveRules = [
            'uuid' => 'nullable|uuid',
            'dive_number' => 'nullable|integer',
            'datetime' => 'required|date',
            'duration_seconds' => 'required|integer|min:0',
            'max_depth_m' => 'required|numeric|min:0',
            'avg_depth_m' => 'nullable|numeric|min:0',
            'surface_temp_c' => 'nullable|numeric',
            'min_water_temp_c' => 'nullable|numeric',
            'max_water_temp_c' => 'nullable|numeric',
            'dive_mode' => 'nullable|in:freedive,gauge,open_circuit,closed_circuit,semi_closed_circuit',
            'gas_mixes' => 'nullable|array',
            'tanks' => 'nullable|array',
            'deco_model' => 'nullable|array',
            'location' => 'nullable|array',
            'sensor_data' => 'nullable|array',
            'fingerprint' => 'nullable|string|max:512', // base64, or the app's synthesised "sha256:..." form
            // The diver explicitly picked this dive to import again (Apple
            // Watch import, 2026-10-19): a dive they deleted comes back.
            'restore' => 'nullable|boolean',
        ];
        $data = ['computer' => $request->input('computer'), 'dives' => $request->input('dives')];
        $errors = [];
        foreach ($data['dives'] as $i => $dive) {
            if (!is_array($dive)) { $errors["dives.$i"] = ['The dive must be an object.']; continue; }
            $light = $dive;
            unset($light['profile']);
            // sensor_data can carry per-sample arrays; only its type matters here.
            if (isset($light['sensor_data']) && is_array($light['sensor_data'])) $light['sensor_data'] = ['_' => 1];
            $v = \Illuminate\Support\Facades\Validator::make($light, $diveRules);
            foreach ($v->errors()->toArray() as $field => $msgs) $errors["dives.$i.$field"] = $msgs;
            if (!isset($dive['profile']) || !is_array($dive['profile'])) $errors["dives.$i.profile"] = ['The profile field is required and must be an array.'];
        }
        if ($errors) throw \Illuminate\Validation\ValidationException::withMessages($errors);

        $user = $request->user();
        $created = [];
        $duplicates = [];
        $deleted = [];

        foreach ($data['dives'] as $i => $diveData) {
            $serial = $data['computer']['serial'] ?? null;
            // Heart rate: the web and the FIT importer call it heartRate;
            // libdivecomputer's sample field (sent as-is by the Android
            // uploader, 2026-10-23) is "heartbeat". One name on file.
            foreach ($diveData['profile'] as &$smp) {
                if (is_array($smp) && array_key_exists('heartbeat', $smp)) {
                    if (!isset($smp['heartRate']) && (int) $smp['heartbeat'] > 0) $smp['heartRate'] = (int) round($smp['heartbeat']);
                    unset($smp['heartbeat']);
                }
            }
            unset($smp);
            $fingerprint = $diveData['fingerprint'] ?? null;

            DB::transaction(function () use ($data, $diveData, $user, $serial, $fingerprint, $i, &$created, &$duplicates, &$deleted) {
                $facts = [
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
                ];

                // 1. Exact identity: this computer's log of this dive, as a
                //    dive or as a log attached to another computer's dive.
                $existing = $fingerprint
                    ? Dive::withTrashed()->withoutGlobalScope('companions')
                        ->where('user_id', $user->id)
                        ->where('computer_serial', $serial)
                        ->where('fingerprint', $fingerprint)
                        ->first()
                    : null;

                // 2. A logbook import of this dive from THIS computer (or one
                //    that named no computer): matched by start time +-3 min.
                //    computer-file too: a Garmin .FIT has no fingerprint the
                //    app reproduces.
                if (!$existing) {
                    $ts = strtotime($diveData['datetime']);
                    // Sort ids only, then load: sorting whole rows (profile
                    // JSON up to ~350 KB) overflows MySQL's 256 KB sort
                    // buffer - "Out of sort memory" (2026-10-17).
                    $existingId = Dive::where('user_id', $user->id)
                        ->whereIn('source', ['uddf-import', 'computer-file'])
                        ->whereBetween('started_at', [date('Y-m-d H:i:s', $ts - 180), date('Y-m-d H:i:s', $ts + 180)])
                        ->where(fn ($q) => $q->whereNull('computer_serial')->orWhere('computer_serial', $serial))
                        ->orderByRaw('ABS(TIMESTAMPDIFF(SECOND, started_at, ?))', [date('Y-m-d H:i:s', $ts)])
                        ->value('id');
                    $existing = $existingId ? Dive::find($existingId) : null;
                }

                if ($existing && $existing->isCompanion()) {
                    // Already attached to a dive: that dive is the identity.
                    $main = Dive::withTrashed()->find($existing->merged_into_id);
                    $duplicates[] = ['index' => $i, 'uuid' => $main?->uuid ?? $existing->uuid, 'id' => $main?->id ?? $existing->id, 'dive_number' => $main?->dive_number, 'attached' => true];
                    return;
                }

                if ($existing && !$existing->trashed() && in_array($existing->source, ['uddf-import', 'computer-file'], true)) {
                    // The computer wins (Pablo, 2026-10-16): its depth/time/
                    // mode/gases/tanks/PPO2 replace what the import guessed;
                    // everything the diver added - site, notes, buddies,
                    // tags, operator, conditions - stays. Gas pairings were
                    // keyed to the import's gas list, so they reset.
                    $f = $facts; unset($f['source'], $f['dive_number']);
                    $existing->replaceComputerFacts($f, 'ios-uploader');
                    // Reported as a duplicate (server uuid wins), flagged so
                    // the app knows to refetch the full record.
                    $duplicates[] = ['index' => $i, 'uuid' => $existing->uuid, 'id' => $existing->id, 'dive_number' => $existing->dive_number, 'replaced_import' => true];
                    return;
                }

                if ($existing && $existing->trashed() && !empty($diveData['restore'])) {
                    // An explicit re-import brings a deleted dive back, same
                    // uuid, with the uploaded computer facts winning (the old
                    // log may be the broken one being re-imported to fix it).
                    // Without `restore` a deleted dive stays deleted.
                    $existing->restore();
                    $f = $facts; unset($f['source'], $f['dive_number']);
                    $existing->replaceComputerFacts($f, 'ios-uploader');
                    // After the feed's "deleted": other devices add it back.
                    DiveChange::record($existing->user_id, $existing->uuid, 'created');
                    $duplicates[] = ['index' => $i, 'uuid' => $existing->uuid, 'id' => $existing->id, 'dive_number' => $existing->dive_number, 'replaced_import' => true, 'restored' => true];
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

                // 3. Another computer already logged this dive (Petrel and
                //    Perdix on one dive). Clocks agree -> attached to that
                //    dive, so it gains this computer's data (transmitter
                //    pressure, a second profile) instead of a twin row.
                $match = \App\Support\SameDiveMatcher::find($user->id, [
                    'started_at' => $diveData['datetime'], 'duration_seconds' => $diveData['duration_seconds'],
                    'max_depth_m' => $diveData['max_depth_m'], 'computer_serial' => $serial, 'profile' => $diveData['profile'],
                ]);
                // Only with the diver's "Auto-merge dives" setting on (off by
                // default - Pablo, 2026-10-16: keep asking).
                if ($match && $match['level'] === 'strong' && ($user->logbook_auto_merge ?? false)) {
                    $match['dive']->attachComputerLog($facts);
                    $duplicates[] = ['index' => $i, 'uuid' => $match['dive']->uuid, 'id' => $match['dive']->id, 'dive_number' => $match['dive']->dive_number, 'attached' => true];
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

                // 4. Looks like another computer's dive but the clocks are
                //    hours apart: never assumed - the dive page asks.
                if ($match) {
                    // The dive it asks about now shows same_dive too.
                    $match['dive']->logUpdated();
                    $dive->conditions = ['same_dive_suggestion' => ['dive_id' => $match['dive']->id, 'offset' => $match['offset'], 'shape' => $match['shape'], 'corr' => $match['corr'] ?? null, 'level' => $match['level']]];
                    $dive->save();
                }

                $created[] = ['index' => $i, 'uuid' => $dive->uuid, 'id' => $dive->id, 'dive_number' => $dive->dive_number]
                    + ($match ? ['same_dive_as' => $match['dive']->uuid] : []);
            });
            // Each dive is its own transaction, so a request cut short still
            // keeps the dives already saved; free this one before the next.
            unset($data['dives'][$i], $diveData);
        }

        return response()->json([
            'created' => $created,
            'duplicates' => $duplicates,
            'deleted' => $deleted,
        ], 201);
    }
}

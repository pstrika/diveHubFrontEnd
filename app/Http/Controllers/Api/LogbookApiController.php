<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Boat;
use App\Models\Dive;
use App\Models\DiveChange;
use App\Models\Operator;
use App\Models\Site;
use App\Models\User;
use App\Support\LogbookConditionRules;
use App\Support\TankCatalog;
use Illuminate\Http\Request;

/**
 * The web logbook for the native app (iOS session, 2026-10-15): the
 * option lists the pickers need, the site/operator catalogs, buddy
 * search, and the one PATCH that logs anything against a dive. All
 * bearer-authenticated and behind the 'logbook' early-access gate.
 * Static payloads carry an ETag and answer 304 to If-None-Match.
 */
class LogbookApiController extends Controller
{
    /** GET /api/logbook-options */
    public function options(Request $request)
    {
        $weatherIcons = ['sunny' => 113, 'partly_cloudy' => 116, 'cloudy' => 119, 'rainy' => 296, 'stormy' => 389];
        $label = fn (string $k) => ucfirst(str_replace('_', ' ', $k));

        $payload = [
            'classification' => array_map(fn ($k) => ['key' => $k, 'label' => $label($k)], LogbookConditionRules::CLASSIFICATION),
            'current' => array_map(fn ($k) => ['key' => $k, 'label' => $label($k)], LogbookConditionRules::CURRENT),
            'current_direction' => LogbookConditionRules::CURRENT_DIRECTION,
            'surface_conditions' => array_map(fn ($k) => ['key' => $k, 'label' => $label($k)], LogbookConditionRules::SURFACE),
            'weather' => array_map(fn ($k) => [
                'key' => $k,
                'label' => $label($k),
                // Same files the Weather page shows; "windy" has no icon in that set.
                'icon_url' => isset($weatherIcons[$k]) ? asset('assets') . '/img/weather/64x64/day/' . $weatherIcons[$k] . '.png' : null,
            ], LogbookConditionRules::WEATHER),
            'dive_type' => array_map(fn ($k) => ['key' => $k, 'label' => $k === 'photo_video' ? 'Photo/Video' : $label($k)], LogbookConditionRules::DIVE_TYPE),
            'gas_roles' => [
                'oc' => ['bottom' => 'Bottom gas', 'deco' => 'Deco gas', 'travel' => 'Travel gas'],
                'ccr' => ['bailout' => 'Bailout', 'deco' => 'Deco gas'],
            ],
            'tank_catalog' => TankCatalog::all(),
            'tank_templates' => [
                'single' => asset('assets') . '/img/tank_single.png',
                'double' => asset('assets') . '/img/tank_double.png',
                'ccr' => asset('assets') . '/img/tank_ccr.png',
            ],
            'visibility' => [
                'imperial' => ['max_ft' => 100, 'step_ft' => 10],
                'metric' => ['max_m' => 30, 'step_m' => 3],
            ],
            'tags' => ['max' => 5, 'max_length' => 40],
            'units' => [
                'imperial' => ['depth' => 'ft', 'volume' => 'cuft', 'rate' => 'cuft/min', 'pressure' => 'psi', 'temperature' => '°F'],
                'metric' => ['depth' => 'm', 'volume' => 'L', 'rate' => 'L/min', 'pressure' => 'bar', 'temperature' => '°C'],
                'liters_per_cuft' => TankCatalog::LITERS_PER_CUFT,
                'psi_per_bar' => TankCatalog::PSI_PER_BAR,
            ],
        ];

        return $this->etagJson($request, $payload);
    }

    /** GET /api/sites */
    public function sites(Request $request)
    {
        $sites = Site::orderBy('name')->get()->map(fn (Site $s) => [
            'id' => $s->id,
            'name' => $s->name,
            'slug' => $s->slug,
            'type' => $s->type,
            'latitude' => $s->decimalLatitude(),
            'longitude' => $s->decimalLongitude(),
            'url' => $s->publicUrl(),
        ])->values();

        return $this->etagJson($request, ['sites' => $sites]);
    }

    /** GET /api/operators - with each operator's boats as quick picks. */
    public function operators(Request $request)
    {
        $boats = Boat::whereNotNull('operatorId')->orderBy('name')->get(['operatorId', 'name', 'pic'])->groupBy('operatorId');
        $operators = Operator::orderBy('operatorName')->get(['id', 'operatorName', 'logoUrl', 'slug'])->map(fn (Operator $o) => [
            'id' => $o->id,
            'name' => $o->operatorName,
            'logo_url' => $o->logoUrl ? asset('assets') . $o->logoUrl : null,
            'url' => route('OperatorDetails', ['id' => $o->slug ?? $o->id]),
            'boats' => ($boats[$o->id] ?? collect())->map(fn ($b) => [
                'name' => $b->name,
                'pic_url' => $b->pic ? asset('assets') . $b->pic : null,
            ])->values(),
        ])->values();

        return $this->etagJson($request, ['operators' => $operators]);
    }

    /** GET /api/users/search?q= - buddy @-mention; name + avatar only, never email. */
    public function searchUsers(Request $request)
    {
        $q = trim((string) $request->query('q'));
        if (mb_strlen($q) < 2) {
            return response()->json(['users' => []]);
        }

        $users = User::where('name', 'LIKE', "%$q%")
            ->where('id', '!=', $request->user()->id)
            ->where('role_id', '!=', 4)
            ->orderBy('name')
            ->take(10)
            ->get(['id', 'name', 'picture'])
            ->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'avatar_url' => $u->picture ? asset('assets') . '/img/users/' . $u->picture : null,
            ])->values();

        return response()->json(['users' => $users]);
    }

    /**
     * PATCH /api/dives/{uuid}/conditions - any subset of the loggable keys;
     * absent keys untouched, a key sent as null clears it. Same rules the
     * web forms use (LogbookConditionRules). Returns the dive as GET
     * /api/dives/{uuid} does, minus profile, plus the change seq.
     */
    public function updateConditions(Request $request, string $uuid)
    {
        $dive = Dive::where('user_id', $request->user()->id)->where('uuid', $uuid)->firstOrFail();

        $input = $request->all();
        // Accept the flat shape the app sends (keys at top level) as well as
        // the web's conditions[...] nesting.
        $flat = [];
        foreach ($input as $k => $v) {
            if ($k === 'conditions' && is_array($v)) { $flat = array_merge($flat, $v); continue; }
            $flat[$k] = $v;
        }

        $conditionKeys = [
            'classification', 'site_name_custom', 'operator_name', 'operator_logo', 'operator_url', 'boat_name', 'boat_pic',
            'boat_captain', 'divemaster', 'current', 'current_direction', 'surface_conditions', 'weather', 'visibility_m',
            'buddies', 'dive_type', 'tank_config', 'gas_tank_size_choice', 'gas_tank_choice', 'gas_role_choice',
            'deleted_gas_mixes', 'gas_row_order',
        ];
        $columnKeys = ['site_id', 'notes', 'tags'];

        $unknown = array_diff(array_keys($flat), $conditionKeys, $columnKeys);
        if (count($unknown)) {
            return response()->json(['message' => 'Unknown keys', 'errors' => array_fill_keys(array_values($unknown), ['Not a loggable field'])], 422);
        }

        // Re-nest for the shared rules, then validate only what was sent.
        $toValidate = [];
        foreach ($flat as $k => $v) {
            if (in_array($k, $columnKeys, true)) $toValidate[$k] = $v; else $toValidate['conditions'][$k] = $v;
        }
        $rules = LogbookConditionRules::rules();
        $validated = validator($toValidate, $rules)->validate();

        $conditions = $dive->conditions ?? [];
        foreach ($conditionKeys as $k) {
            if (!array_key_exists($k, $flat)) continue;
            $v = $validated['conditions'][$k] ?? null;
            if ($k === 'gas_tank_choice' && is_array($v)) $v = LogbookConditionRules::dedupeTankChoice($v);
            $conditions[$k] = $v;
        }
        if (array_key_exists('site_id', $flat)) {
            $dive->site_id = $validated['site_id'] ?? null;
            // A catalog pick clears any free-typed name - a dive has one site.
            if ($dive->site_id) $conditions['site_name_custom'] = null;
        }
        if (array_key_exists('notes', $flat)) $dive->notes = $validated['notes'] ?? null;
        if (array_key_exists('tags', $flat)) $dive->tags = $validated['tags'] ?? [];

        $dive->conditions = array_filter($conditions, fn ($v) => $v !== null && $v !== '' && $v !== []);
        $dive->save();

        DiveChange::record($dive->user_id, $dive->uuid, 'updated');
        $seq = (int) DiveChange::where('user_id', $dive->user_id)->where('dive_uuid', $dive->uuid)->max('id');

        $full = app(DiveSyncController::class)->show($request, $uuid)->getData(true);
        unset($full['profile']);

        return response()->json($full + ['seq' => $seq]);
    }

    private function etagJson(Request $request, array $payload)
    {
        $json = json_encode($payload);
        $etag = '"' . md5($json) . '"';
        if (trim((string) $request->header('If-None-Match')) === $etag) {
            return response('', 304)->header('ETag', $etag);
        }
        return response($json, 200, ['Content-Type' => 'application/json', 'ETag' => $etag, 'Cache-Control' => 'private, max-age=0, must-revalidate']);
    }
}

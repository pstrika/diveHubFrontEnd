<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\UserSiteController;
use App\Models\Dive;
use App\Models\DiveChange;
use App\Models\UserSite;
use Illuminate\Http\Request;

/**
 * The diver's own sites for the app (Pablo, 2026-10-16). Same rules and
 * storage as the web form (UserSiteController); altitude accepted as
 * `altitude` + `altitude_unit` (m|ft) or straight `altitude_m`.
 *
 *   GET    /api/user-sites          -> {sites:[...]}
 *   POST   /api/user-sites          -> 201 site (optional `dive_uuid` tags that dive)
 *   PATCH  /api/user-sites/{id}     -> site
 *   DELETE /api/user-sites/{id}     -> 204 (tagged dives keep the name as site_name_custom)
 *
 * To tag a dive with an existing one: PATCH /api/dives/{uuid}/conditions
 * {user_site_id: <id>} (null clears; exclusive with site_id).
 */
class UserSiteApiController extends Controller
{
    public static function siteArray(UserSite $s): array
    {
        return [
            'id' => $s->id,
            'name' => $s->name,
            'type' => $s->type,
            'min_cert' => $s->min_cert,
            'water_type' => $s->water_type,
            'altitude_m' => $s->altitude_m,
            'latitude' => $s->latitude,
            'longitude' => $s->longitude,
            'location' => $s->location,
            'country' => $s->country,
            'city' => $s->cityLabel(),
            'notes' => $s->notes,
            'url' => $s->publicUrl(),
            'custom' => true,
            'dives_count' => $s->dives_count ?? null,
            'updated_at' => optional($s->updated_at)->toIso8601String(),
        ];
    }

    private function normalize(array $input): array
    {
        if (array_key_exists('altitude_m', $input) && !array_key_exists('altitude', $input)) {
            $input['altitude'] = $input['altitude_m'];
            $input['altitude_unit'] = 'm';
        }
        unset($input['altitude_m']);
        return $input;
    }

    public function index(Request $request)
    {
        $sites = UserSite::where('user_id', $request->user()->id)->withCount('dives')->orderBy('name')->get();
        return response()->json([
            'sites' => $sites->map(fn ($s) => self::siteArray($s))->values(),
            'options' => ['types' => UserSite::TYPES, 'certs' => UserSite::CERTS, 'water_types' => UserSite::WATER],
        ]);
    }

    public function store(Request $request)
    {
        $input = $this->normalize($request->all());
        $data = validator($input, UserSiteController::rules() + ['dive_uuid' => 'nullable|uuid'])->validate();
        $site = UserSite::create(['user_id' => $request->user()->id] + UserSiteController::attributes($data));

        if (!empty($data['dive_uuid'])) {
            $dive = Dive::where('user_id', $request->user()->id)->where('uuid', $data['dive_uuid'])->first();
            if ($dive) UserSiteController::tagDive($dive, $site);
        }

        return response()->json(self::siteArray($site), 201);
    }

    public function update(Request $request, $id)
    {
        $site = UserSite::where('user_id', $request->user()->id)->findOrFail($id);
        $input = $this->normalize($request->all());
        // Partial update: only validate/apply what was sent, name/type keep their values.
        $rules = array_intersect_key(UserSiteController::rules(), $input);
        $data = validator($input, $rules)->validate();
        $merged = array_merge([
            'name' => $site->name, 'type' => $site->type, 'min_cert' => $site->min_cert, 'water_type' => $site->water_type,
            'altitude' => $site->altitude_m, 'altitude_unit' => 'm', 'latitude' => $site->latitude, 'longitude' => $site->longitude,
            'location' => $site->location, 'country' => $site->country, 'notes' => $site->notes,
        ], $data);
        $site->update(UserSiteController::attributes($merged));

        foreach (Dive::where('user_site_id', $site->id)->get(['uuid', 'user_id']) as $dive) {
            DiveChange::record($dive->user_id, $dive->uuid, 'updated');
        }

        return response()->json(self::siteArray($site->fresh()));
    }

    public function destroy(Request $request, $id)
    {
        $site = UserSite::where('user_id', $request->user()->id)->findOrFail($id);
        foreach (Dive::where('user_site_id', $site->id)->get() as $dive) {
            $conditions = $dive->conditions ?? [];
            $conditions['site_name_custom'] = $site->name;
            $dive->conditions = $conditions;
            $dive->user_site_id = null;
            $dive->save();
            DiveChange::record($dive->user_id, $dive->uuid, 'updated');
        }
        $site->delete();

        return response()->noContent();
    }
}

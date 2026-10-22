<?php

namespace App\Http\Controllers;

use App\Models\Dive;
use App\Models\DiveChange;
use App\Models\UserSite;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * A diver's own dive sites (Pablo, 2026-10-16: "Custom User Sites -> Name,
 * GPS, type [wreck, reef, cave, cavern, quarry, mine, lake, river, other],
 * min cert level [OW, AOW, Ta, Tn, Th], water type [fresh, salt],
 * altitude, location, country"). Private to the diver; a dive points at
 * one through dives.user_site_id, exclusive with the catalog site_id.
 *
 * `?dive=<id>` on create/store tags that dive with the new site in the same
 * step - the "New site" link next to the dive-details site picker.
 */
class UserSiteController extends Controller
{
    public static function rules(): array
    {
        return [
            'name' => 'required|string|max:150',
            'type' => ['required', Rule::in(UserSite::TYPES)],
            'min_cert' => ['nullable', Rule::in(UserSite::CERTS)],
            'water_type' => ['nullable', Rule::in(UserSite::WATER)],
            'altitude' => 'nullable|numeric|min:-500|max:9000',
            'altitude_unit' => 'nullable|in:m,ft',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'location' => 'nullable|string|max:150',
            'country' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:2000',
        ];
    }

    /** Validated form input -> user_sites columns (altitude stored in meters). */
    public static function attributes(array $data): array
    {
        $altitude = $data['altitude'] ?? null;
        if ($altitude !== null && $altitude !== '') {
            $altitude = ($data['altitude_unit'] ?? 'm') === 'ft' ? round($altitude * 0.3048) : round($altitude);
        } else {
            $altitude = null;
        }
        return [
            'name' => trim($data['name']),
            'type' => $data['type'],
            'min_cert' => $data['min_cert'] ?? null,
            'water_type' => $data['water_type'] ?? null,
            'altitude_m' => $altitude,
            'latitude' => isset($data['latitude']) && $data['latitude'] !== '' ? (float) $data['latitude'] : null,
            'longitude' => isset($data['longitude']) && $data['longitude'] !== '' ? (float) $data['longitude'] : null,
            'location' => $data['location'] ?? null,
            'country' => $data['country'] ?? null,
            'notes' => $data['notes'] ?? null,
        ];
    }

    public function index(Request $request)
    {
        $all = UserSite::where('user_id', $request->user()->id)
            ->withCount('dives')
            ->orderBy('name')
            ->get();

        // Search + Type / Country / Location / Min cert filters (Pablo,
        // 2026-10-22). Same rule as the logbook's chips: each dropdown only
        // offers values that still match something under the OTHER filters.
        $filters = [
            'q' => trim(mb_substr((string) $request->query('q', ''), 0, 100)) ?: null,
            'type' => in_array($request->query('type'), UserSite::TYPES, true) ? $request->query('type') : null,
            'country' => trim((string) $request->query('country')) ?: null,
            'location' => trim((string) $request->query('location')) ?: null,
            'cert' => in_array($request->query('cert'), UserSite::CERTS, true) ? $request->query('cert') : null,
        ];
        $terms = $filters['q'] ? preg_split('/\s+/', mb_strtolower($filters['q'])) : [];
        $matches = function (UserSite $s, array $f) use ($terms): bool {
            [$loc, $country] = \App\Support\SiteFacets::ofUserSite($s);
            if ($f['type'] && $s->type !== $f['type']) return false;
            if ($f['country'] && $country !== $f['country']) return false;
            if ($f['location'] && $loc !== $f['location']) return false;
            if ($f['cert'] && $s->min_cert !== $f['cert']) return false;
            if ($terms) {
                $text = mb_strtolower(implode(' ', [$s->name, $loc, $country, $s->notes]));
                foreach ($terms as $t) if (!str_contains($text, $t)) return false;
            }
            return true;
        };
        $within = function (string $except) use ($all, $filters, $matches) {
            $others = $filters; $others[$except] = null;
            return $all->filter(fn (UserSite $s) => $matches($s, $others));
        };

        $typeOptions = [];
        $present = $within('type')->pluck('type')->unique()->all();
        foreach (\App\Models\Site::TYPES as $t => $label) if (in_array($t, $present, true)) $typeOptions[$t] = $label;
        $countries = $within('country')->map(fn ($s) => \App\Support\SiteFacets::ofUserSite($s)[1])->filter()->unique()->sort()->values()->all();
        $locations = $within('location')->map(fn ($s) => \App\Support\SiteFacets::ofUserSite($s)[0])->filter()->unique()->sort(SORT_NATURAL | SORT_FLAG_CASE)->values()->all();
        $certOptions = [];
        $present = $within('cert')->pluck('min_cert')->filter()->unique()->all();
        foreach (UserSite::CERTS as $c) if (in_array($c, $present, true)) $certOptions[$c] = UserSite::certLabel($c);

        return view('pages.logbook.sites', [
            'sites' => $all->filter(fn (UserSite $s) => $matches($s, $filters))->values(),
            'totalSites' => $all->count(),
            'filters' => $filters,
            'typeOptions' => $typeOptions,
            'countryOptions' => $countries ? array_combine($countries, $countries) : [],
            'locationOptions' => $locations ? array_combine($locations, $locations) : [],
            'certOptions' => $certOptions,
            'highlight' => (int) $request->query('highlight'),
        ]);
    }

    public function create(Request $request)
    {
        $dive = $this->diveFromQuery($request);

        return view('pages.logbook.site-form', [
            'site' => new UserSite([
                // Pre-fill from the dive's free-typed name, if it has one.
                'name' => $dive->conditions['site_name_custom'] ?? '',
                'type' => 'other',
            ]),
            'dive' => $dive,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(self::rules());
        $site = UserSite::create(['user_id' => $request->user()->id] + self::attributes($data));

        if ($dive = $this->diveFromQuery($request)) {
            $this->tagDive($dive, $site);
            return redirect()->route('DiveDetails', $dive->id)->with('status', 'Site "' . $site->name . '" created and tagged.');
        }

        return redirect()->route('Logbook.sites.index', ['highlight' => $site->id])->with('status', 'Site "' . $site->name . '" created.');
    }

    public function edit(Request $request, $id)
    {
        $site = UserSite::where('user_id', $request->user()->id)->findOrFail($id);

        return view('pages.logbook.site-form', ['site' => $site, 'dive' => null]);
    }

    public function update(Request $request, $id)
    {
        $site = UserSite::where('user_id', $request->user()->id)->findOrFail($id);
        $data = $request->validate(self::rules());
        $site->update(self::attributes($data));

        // Phones show the site's name on every dive tagged with it.
        foreach (Dive::where('user_site_id', $site->id)->get(['uuid', 'user_id']) as $dive) {
            DiveChange::record($dive->user_id, $dive->uuid, 'updated');
        }

        return redirect()->route('Logbook.sites.index', ['highlight' => $site->id])->with('status', 'Site "' . $site->name . '" saved.');
    }

    public function destroy(Request $request, $id)
    {
        $site = UserSite::where('user_id', $request->user()->id)->findOrFail($id);
        $name = $site->name;

        // Dives keep the name as a free-typed site so nothing is lost.
        foreach (Dive::where('user_site_id', $site->id)->get() as $dive) {
            $conditions = $dive->conditions ?? [];
            $conditions['site_name_custom'] = $name;
            $dive->conditions = $conditions;
            $dive->user_site_id = null;
            $dive->save();
            DiveChange::record($dive->user_id, $dive->uuid, 'updated');
        }
        $site->delete();

        return redirect()->route('Logbook.sites.index')->with('status', 'Site "' . $name . '" deleted.');
    }

    private function diveFromQuery(Request $request): ?Dive
    {
        $diveId = (int) $request->query('dive');
        return $diveId ? Dive::where('user_id', $request->user()->id)->find($diveId) : null;
    }

    public static function tagDive(Dive $dive, UserSite $site): void
    {
        $dive->user_site_id = $site->id;
        $dive->site_id = null;
        $conditions = $dive->conditions ?? [];
        unset($conditions['site_name_custom']);
        $dive->conditions = $conditions;
        $dive->save();
        DiveChange::record($dive->user_id, $dive->uuid, 'updated');
    }
}

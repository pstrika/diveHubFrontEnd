<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\User;
use App\Models\WeatherLocation;
use App\Models\WreckTrek;
use App\Support\WreckTrekData;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** Admin page for the predefined wreck treks: create them, then add wrecks. */
class WreckTrekManageController extends Controller
{
    public function index()
    {
        $this->authorize('manage-items', User::class);

        $wrecks = WreckTrekData::eligible()->orderBy('name')->get(['id', 'name', 'slug', 'wreck_trek_id']);
        $treks = WreckTrek::orderBy('location')->orderBy('name')->get();
        $trekNames = $treks->pluck('name', 'id');
        $locations = WeatherLocation::all()->sortBy('location')->mapWithKeys(fn ($l) => [$l->short => ucwords($l->location)])->all();
        $picker = $wrecks->map(fn ($w) => ['id' => $w->id, 'name' => $w->name, 'trek' => $trekNames[$w->wreck_trek_id] ?? null])->values();
        $members = $wrecks->whereNotNull('wreck_trek_id')->groupBy('wreck_trek_id');

        return view('pages.wreck-treks-admin', compact('treks', 'locations', 'picker', 'members'));
    }

    public function store(Request $request)
    {
        $this->authorize('manage-items', User::class);
        $data = $this->validated($request);

        $base = Str::slug($data['name']);
        $slug = $base;
        for ($n = 2; WreckTrek::where('slug', $slug)->exists(); $n++) {
            $slug = $base . '-' . $n;
        }

        $trek = WreckTrek::create(Arr::except($data, ['logo', 'remove_logo']) + ['slug' => $slug]);
        $this->saveLogo($request, $trek);
        WreckTrekData::forget();

        return back()->with('open_trek', $trek->id)->with('status', $data['name'] . ' created. Add its wrecks below.');
    }

    public function update(Request $request, int $id)
    {
        $this->authorize('manage-items', User::class);
        $trek = WreckTrek::findOrFail($id);

        $trek->update(Arr::except($this->validated($request, $id), ['logo', 'remove_logo']));
        $this->saveLogo($request, $trek);
        WreckTrekData::forget();

        return back()->with('open_trek', $trek->id)->with('status', $trek->name . ' saved.');
    }

    public function destroy(int $id)
    {
        $this->authorize('manage-items', User::class);
        $trek = WreckTrek::findOrFail($id);

        Site::where('wreck_trek_id', $id)->update(['wreck_trek_id' => null]);
        $this->deleteLogoFile($trek);
        $trek->delete();
        WreckTrekData::forget();

        return back()->with('status', $trek->name . ' deleted.');
    }

    /** A wreck belongs to one trek, so adding it moves it from any other. */
    public function addWreck(Request $request, int $id)
    {
        $this->authorize('manage-items', User::class);
        $trek = WreckTrek::findOrFail($id);
        $data = $request->validate(['site_id' => 'required|integer']);

        $site = WreckTrekData::eligible()->findOrFail($data['site_id']);
        $site->forceFill(['wreck_trek_id' => $trek->id])->save();
        WreckTrekData::forget();

        return back()->with('open_trek', $trek->id)->with('status', $site->name . ' added to ' . $trek->name . '.');
    }

    public function removeWreck(int $id, int $siteId)
    {
        $this->authorize('manage-items', User::class);
        $site = Site::where('wreck_trek_id', $id)->findOrFail($siteId);

        $site->forceFill(['wreck_trek_id' => null])->save();
        WreckTrekData::forget();

        return back()->with('open_trek', $id)->with('status', $site->name . ' removed.');
    }

    /** Two wrecks of this trek joined by rebar; the chart draws their leg in green. */
    public function addRebar(Request $request, int $id)
    {
        $this->authorize('manage-items', User::class);
        $trek = WreckTrek::findOrFail($id);
        $data = $request->validate(['site_a' => 'required|string|different:site_b', 'site_b' => 'required|string']);

        // Either end can be a wreck of this trek (its slug) or one of the trek's aids ('aid-' and its id).
        $valid = Site::where('wreck_trek_id', $trek->id)->pluck('slug')->merge(collect($trek->aids ?? [])->pluck('id')->filter()->map(fn ($a) => 'aid-' . $a));
        $slugs = [$data['site_a'], $data['site_b']];
        if (!$valid->contains($slugs[0]) || !$valid->contains($slugs[1])) return back()->with('open_trek', $trek->id)->withErrors(['site_a' => 'Pick two different points from this trek.']);
        sort($slugs);

        $rebars = collect($trek->rebars ?? []);
        if (!$rebars->contains(fn ($p) => $p === $slugs)) $rebars->push($slugs);
        $trek->update(['rebars' => $rebars->values()->all()]);
        WreckTrekData::forget();

        return back()->with('open_trek', $trek->id)->with('status', 'Rebar link added to ' . $trek->name . '.');
    }

    public function removeRebar(int $id, int $n)
    {
        $this->authorize('manage-items', User::class);
        $trek = WreckTrek::findOrFail($id);
        $rebars = $trek->rebars ?? [];
        unset($rebars[$n]);
        $trek->update(['rebars' => array_values($rebars)]);
        WreckTrekData::forget();

        return back()->with('open_trek', $trek->id)->with('status', 'Rebar link removed.');
    }

    /** A navigational aid on the chart: boulders, a mitigation point or pipes, at a GPS position. */
    public function addAid(Request $request, int $id)
    {
        $this->authorize('manage-items', User::class);
        $trek = WreckTrek::findOrFail($id);
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(WreckTrekData::AID_TYPES))],
            'name' => 'nullable|string|max:60',
            'lat' => 'required|string|max:40',
            'lon' => 'required|string|max:40',
            'depth' => 'nullable|integer|min:0|max:500',
        ]);
        $lat = $this->coordinate($data['lat'], 'NS');
        $lon = $this->coordinate($data['lon'], 'EW');
        if ($lat === null || $lon === null || abs($lat) > 90 || abs($lon) > 180) {
            return back()->with('open_trek', $trek->id)->withErrors(['lat' => 'Could not read those coordinates. Use 25 49.510 N and 80 04.874 W, or 25.8252 and -80.0812.'])->withInput();
        }

        $aids = $trek->aids ?? [];
        $aids[] = ['id' => Str::lower(Str::random(8)), 'depth' => $data['depth'] ?? null, 'type' => $data['type'], 'name' => $data['name'] ?? null, 'lat' => round($lat, 6), 'lon' => round($lon, 6)];
        $trek->update(['aids' => $aids]);
        WreckTrekData::forget();

        return back()->with('open_trek', $trek->id)->with('status', WreckTrekData::AID_TYPES[$data['type']] . ' added to ' . $trek->name . '.');
    }

    public function removeAid(int $id, int $n)
    {
        $this->authorize('manage-items', User::class);
        $trek = WreckTrek::findOrFail($id);
        $aids = $trek->aids ?? [];
        unset($aids[$n]);
        $trek->update(['aids' => array_values($aids)]);
        WreckTrekData::forget();

        return back()->with('open_trek', $trek->id)->with('status', 'Aid removed.');
    }

    /** Degrees and decimal minutes with a hemisphere letter ("25 49.510 N"), or signed decimal degrees ("-80.0812"). */
    private function coordinate(string $raw, string $hemispheres): ?float
    {
        $raw = trim(str_replace(['°', "'", '′'], ' ', $raw));
        if (preg_match('/^(-?\d{1,3}(?:\.\d+)?)$/', $raw, $m)) return (float) $m[1];
        if (!preg_match('/^(\d{1,3})\s+(\d{1,2}(?:\.\d+)?)\s*([' . $hemispheres . '])$/i', $raw, $m)) return null;
        $value = (float) $m[1] + (float) $m[2] / 60;

        return in_array(strtoupper($m[3]), ['S', 'W']) ? -$value : $value;
    }

    /** A new file replaces the old one; the remove box clears it. */
    private function saveLogo(Request $request, WreckTrek $trek): void
    {
        if ($request->hasFile('logo')) {
            $this->deleteLogoFile($trek);
            $file = $request->file('logo');
            $name = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->extension();
            Storage::disk('siteAssets')->putFileAs('img/treks', $file, $name);
            $trek->update(['logo' => 'assets/img/treks/' . $name]);
        } elseif ($request->boolean('remove_logo')) {
            $this->deleteLogoFile($trek);
            $trek->update(['logo' => null]);
        }
    }

    private function deleteLogoFile(WreckTrek $trek): void
    {
        if ($trek->logo && str_starts_with($trek->logo, 'assets/img/treks/')) {
            Storage::disk('siteAssets')->delete(substr($trek->logo, strlen('assets/')));
        }
    }

    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:80', Rule::unique('wreck_treks', 'name')->ignore($id)],
            'location' => ['required', Rule::in(WeatherLocation::pluck('short')->all())],
            'notes' => 'nullable|string|max:5000',
            'logo' => 'nullable|image|max:5120',
            'logo_link' => ['nullable', 'url', 'max:255', 'regex:/^https?:\/\//i'],
            'remove_logo' => 'nullable|boolean',
        ]);
    }
}

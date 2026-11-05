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

        $wrecks = WreckTrekData::eligible()->orderBy('name')->get(['id', 'name', 'wreck_trek_id']);
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

        return back()->with('status', $data['name'] . ' created. Add its wrecks below.');
    }

    public function update(Request $request, int $id)
    {
        $this->authorize('manage-items', User::class);
        $trek = WreckTrek::findOrFail($id);

        $trek->update(Arr::except($this->validated($request, $id), ['logo', 'remove_logo']));
        $this->saveLogo($request, $trek);
        WreckTrekData::forget();

        return back()->with('status', $trek->name . ' saved.');
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

        return back()->with('status', $site->name . ' added to ' . $trek->name . '.');
    }

    public function removeWreck(int $id, int $siteId)
    {
        $this->authorize('manage-items', User::class);
        $site = Site::where('wreck_trek_id', $id)->findOrFail($siteId);

        $site->forceFill(['wreck_trek_id' => null])->save();
        WreckTrekData::forget();

        return back()->with('status', $site->name . ' removed.');
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

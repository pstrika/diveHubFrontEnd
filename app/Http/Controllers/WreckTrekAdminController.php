<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\User;
use App\Models\WreckTrekLeg;
use App\Support\WreckTrekData;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** The "Wreck trek" card in the site editor: wreck fields and measured legs. */
class WreckTrekAdminController extends Controller
{
    public function saveWreck(Request $request, int $id)
    {
        $this->authorize('manage-items', User::class);
        $site = Site::findOrFail($id);

        $data = $request->validate([
            'trek_type' => ['nullable', Rule::in(WreckTrekData::TYPES)],
            'wreck_trek_id' => ['required_with:trek_type', 'nullable', 'integer', Rule::exists('wreck_treks', 'id')],
            'trek_sand_ft' => 'required_with:trek_type|nullable|integer|min:0|max:1000',
            'trek_lie_deg' => 'nullable|integer|min:0|max:359',
            'trek_broken' => 'required|boolean',
            'trek_sitting' => ['required', Rule::in(array_keys(WreckTrekData::SITTINGS))],
        ]);
        $data['trek_lie_deg'] ??= 180; // blank means the default, bow south

        $site->forceFill($data)->save();
        WreckTrekData::forget();

        return back()->with('status', 'Wreck trek details saved.');
    }

    public function storeLeg(Request $request, int $id)
    {
        $this->authorize('manage-items', User::class);
        $from = Site::findOrFail($id);

        $data = $request->validate($this->legRules() + ['to_site_id' => 'required|integer']);
        $to = (int) $data['to_site_id'];

        // Unique on the pair: the reverse is derived, so it can't be stored too.
        $exists = WreckTrekLeg::where(fn ($q) => $q->where('from_site_id', $from->id)->where('to_site_id', $to))
            ->orWhere(fn ($q) => $q->where('from_site_id', $to)->where('to_site_id', $from->id))
            ->exists();
        if ($to === (int) $from->id || $exists || !Site::whereKey($to)->whereNotNull('trek_type')->exists()) {
            return back()->withErrors(['to_site_id' => 'That leg already exists, in one direction or the other, or that wreck can\'t be used.']);
        }

        WreckTrekLeg::create($data + ['from_site_id' => $from->id]);
        WreckTrekData::forget();

        return back()->with('status', 'Leg added.');
    }

    public function updateLeg(Request $request, int $leg)
    {
        $this->authorize('manage-items', User::class);

        WreckTrekLeg::findOrFail($leg)->update($request->validate($this->legRules()));
        WreckTrekData::forget();

        return back()->with('status', 'Leg saved.');
    }

    public function destroyLeg(int $leg)
    {
        $this->authorize('manage-items', User::class);

        WreckTrekLeg::findOrFail($leg)->delete();
        WreckTrekData::forget();

        return back()->with('status', 'Leg deleted.');
    }

    private function legRules(): array
    {
        return [
            'heading_deg' => 'required|integer|min:0|max:359',
            'distance_ft' => 'required|integer|min:1|max:100000',
            'notes' => 'nullable|string|max:255',
            'verified_by' => 'nullable|string|max:100',
            'verified_on' => 'nullable|date',
        ];
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Dive;
use App\Models\EquipmentConfig;
use App\Models\EquipmentItem;
use App\Support\SitePhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * The diver's gear (Pablo, 2026-10-17): items by category, named
 * configurations, gear on each dive, and the computers their dives came
 * from offered as one-tap additions. Early access: 'early:equipment'.
 */
class EquipmentController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $items = EquipmentItem::where('user_id', $userId)->orderBy('name')->get();

        // Dives per item: dive_equipment rows + computers by serial.
        $explicit = DB::table('dive_equipment')->join('dives', 'dives.id', '=', 'dive_equipment.dive_id')
            ->whereNull('dives.deleted_at')->whereIn('equipment_item_id', $items->pluck('id'))
            ->groupBy('equipment_item_id')->selectRaw('equipment_item_id, COUNT(*) n')->pluck('n', 'equipment_item_id');
        $bySerial = Dive::withoutGlobalScope('companions')->where('user_id', $userId)->whereNotNull('computer_serial')
            ->groupBy('computer_serial')->selectRaw('computer_serial, COUNT(*) n')->pluck('n', 'computer_serial');
        $diveCounts = $items->mapWithKeys(fn ($i) => [$i->id => (int) ($explicit[$i->id] ?? 0)
            + ($i->category === 'computer' && $i->serial_number ? (int) ($bySerial[$i->serial_number] ?? 0) : 0)]);

        $groups = [];
        foreach (EquipmentItem::CATEGORIES as $slug => [$label, $icon]) {
            $list = $items->where('category', $slug)->values();
            if ($list->isNotEmpty()) $groups[$slug] = compact('label', 'icon', 'list');
        }

        return view('pages.logbook.equipment.index', [
            'groups' => $groups,
            'diveCounts' => $diveCounts,
            'configs' => EquipmentConfig::where('user_id', $userId)->with('items')->orderBy('name')->get(),
            'suggested' => self::suggestedComputers($userId, $items),
        ]);
    }

    /**
     * Computers the diver's dives came from that aren't in their gear yet
     * (Pablo: "computers - we can infer which ones they have when they
     * upload dives"). One per serial (or per model when no serial).
     */
    public static function suggestedComputers(int $userId, $items): array
    {
        $have = $items->where('category', 'computer')->pluck('serial_number')->filter()->map(fn ($s) => (string) $s)->flip();
        $haveModels = $items->where('category', 'computer')->whereNull('serial_number')->pluck('model')->filter()->map(fn ($m) => mb_strtolower($m))->flip();
        return Dive::withoutGlobalScope('companions')->where('user_id', $userId)->whereNotNull('computer_model')
            ->groupBy('computer_family', 'computer_model', 'computer_serial')
            ->selectRaw('computer_family, computer_model, computer_serial, COUNT(*) n, MIN(started_at) first_at')
            ->orderByDesc('n')->get()
            ->reject(fn ($r) => $r->computer_serial ? isset($have[(string) $r->computer_serial]) : isset($haveModels[mb_strtolower($r->computer_model)]))
            ->map(fn ($r) => [
                'manufacturer' => self::brand($r->computer_family, $r->computer_model),
                'model' => $r->computer_model, 'serial' => $r->computer_serial, 'dives' => (int) $r->n,
                'first' => $r->first_at,
            ])->values()->all();
    }

    private static function brand(?string $family, string $model): ?string
    {
        foreach (['Shearwater', 'Garmin', 'Suunto', 'Oceanic', 'Aqualung', 'Mares', 'Scubapro', 'Uwatec', 'Cressi', 'Apple', 'Ratio', 'Heinrichs Weikamp', 'Divesoft', 'Atomic', 'Tecdiving', 'Halcyon'] as $b) {
            if (stripos($model, $b) !== false || stripos((string) $family, $b) !== false) return $b;
        }
        if (stripos((string) $family, 'shearwater') !== false || preg_match('/perdix|petrel|teric|nerd|peregrine|tern/i', $model)) return 'Shearwater';
        return $family ? Str::headline(preg_replace('/_.*/', '', $family)) : null;
    }

    public function addSuggested(Request $request)
    {
        $data = $request->validate(['model' => 'required|string|max:120', 'manufacturer' => 'nullable|string|max:120', 'serial' => 'nullable|string|max:120', 'first' => 'nullable|date']);
        $item = EquipmentItem::create([
            'user_id' => $request->user()->id, 'category' => 'computer',
            'name' => trim(($data['manufacturer'] && stripos($data['model'], $data['manufacturer']) === false ? $data['manufacturer'] . ' ' : '') . $data['model']),
            'manufacturer' => $data['manufacturer'] ?? null, 'model' => $data['model'], 'serial_number' => $data['serial'] ?? null,
            'purchased_on' => null,
        ]);
        return redirect()->route('Equipment.index')->with('status', "{$item->name} added. Its dives are linked automatically.");
    }

    public function show(Request $request, string $uuid)
    {
        $item = $this->own($request, $uuid);
        $dives = Dive::where('user_id', $request->user()->id)->select(Dive::LIST_COLUMNS)->whereIn('id', $item->diveIds())->orderByDesc('started_at')->get();
        Dive::preloadSites($dives);
        $dives->load('userSite');
        return view('pages.logbook.equipment.show', compact('item', 'dives'));
    }

    public function create(Request $request)
    {
        $cat = array_key_exists($request->query('category'), EquipmentItem::CATEGORIES) ? $request->query('category') : 'regulator';
        return view('pages.logbook.equipment.form', ['item' => new EquipmentItem(['category' => $cat])]);
    }

    public function store(Request $request)
    {
        $item = new EquipmentItem($this->validated($request));
        $item->user_id = $request->user()->id;
        $item->save();
        $this->savePhoto($request, $item);
        return redirect()->route('Equipment.show', $item->uuid)->with('status', 'Added to your gear.');
    }

    public function edit(Request $request, string $uuid)
    {
        return view('pages.logbook.equipment.form', ['item' => $this->own($request, $uuid)]);
    }

    public function update(Request $request, string $uuid)
    {
        $item = $this->own($request, $uuid);
        $item->fill($this->validated($request));
        $item->retired_at = $request->boolean('retired') ? ($item->retired_at ?? now()) : null;
        $item->save();
        $this->savePhoto($request, $item);
        return redirect()->route('Equipment.show', $item->uuid)->with('status', 'Saved.');
    }

    public function destroy(Request $request, string $uuid)
    {
        $item = $this->own($request, $uuid);
        $diveIds = DB::table('dive_equipment')->where('equipment_item_id', $item->id)->pluck('dive_id')->all();
        DB::table('dive_equipment')->where('equipment_item_id', $item->id)->delete();
        DB::table('equipment_config_items')->where('equipment_item_id', $item->id)->delete();
        $item->delete();
        Dive::logUpdatedIds($diveIds);
        return redirect()->route('Equipment.index')->with('status', 'Removed from your gear.');
    }

    public function photo(Request $request, string $uuid)
    {
        $item = $this->own($request, $uuid);
        abort_unless($item->photoPath() && is_file($item->photoPath()), 404);
        return response()->file($item->photoPath(), ['Content-Type' => 'image/webp', 'Cache-Control' => 'private, max-age=31536000']);
    }

    // Configurations.

    public function configCreate(Request $request)
    {
        return view('pages.logbook.equipment.config', ['config' => new EquipmentConfig(), 'items' => $this->activeItems($request), 'selected' => []]);
    }

    public function configStore(Request $request)
    {
        $data = $this->validatedConfig($request);
        $config = EquipmentConfig::create(['user_id' => $request->user()->id, 'name' => $data['name'], 'notes' => $data['notes'] ?? null]);
        $config->items()->sync($this->ownItemIds($request, $data['items'] ?? []));
        return redirect()->route('Equipment.index')->with('status', "Configuration \"{$config->name}\" saved.");
    }

    public function configEdit(Request $request, string $uuid)
    {
        $config = $this->ownConfig($request, $uuid);
        return view('pages.logbook.equipment.config', ['config' => $config, 'items' => $this->activeItems($request), 'selected' => $config->items->pluck('uuid')->all()]);
    }

    public function configUpdate(Request $request, string $uuid)
    {
        $config = $this->ownConfig($request, $uuid);
        $data = $this->validatedConfig($request);
        $config->fill(['name' => $data['name'], 'notes' => $data['notes'] ?? null])->save();
        $config->items()->sync($this->ownItemIds($request, $data['items'] ?? []));
        return redirect()->route('Equipment.index')->with('status', 'Configuration saved. Dives it was already applied to keep their gear.');
    }

    public function configDestroy(Request $request, string $uuid)
    {
        $config = $this->ownConfig($request, $uuid);
        $config->items()->detach();
        $config->delete();
        return redirect()->route('Equipment.index')->with('status', 'Configuration deleted. Dives keep their gear.');
    }

    // Gear on a dive.

    /** Apply a configuration (adds its items) or add one item. */
    public function diveAdd(Request $request, $id)
    {
        $dive = Dive::where('user_id', $request->user()->id)->findOrFail((int) $id);
        $data = $request->validate(['config' => 'nullable|string', 'item' => 'nullable|string']);
        if (!empty($data['config'])) {
            $config = $this->ownConfig($request, $data['config']);
            foreach ($config->items as $item) {
                $dive->equipment()->syncWithoutDetaching([$item->id => ['equipment_config_id' => $config->id]]);
            }
        } elseif (!empty($data['item'])) {
            $dive->equipment()->syncWithoutDetaching([$this->own($request, $data['item'])->id]);
        }
        $dive->logUpdated();
        return response()->json(['equipment' => self::diveList($dive)]);
    }

    public function diveRemove(Request $request, $id, string $uuid)
    {
        $dive = Dive::where('user_id', $request->user()->id)->findOrFail((int) $id);
        $dive->equipment()->detach($this->own($request, $uuid)->id);
        $dive->logUpdated();
        return response()->json(['equipment' => self::diveList($dive)]);
    }

    /** Gear on a dive: recorded items, plus computers matched by serial (not removable). */
    public static function diveList(Dive $dive): array
    {
        $list = $dive->equipment()->get()->map(fn ($i) => [
            'uuid' => $i->uuid, 'name' => $i->name, 'category' => $i->categoryLabel(), 'icon' => $i->icon(),
            'auto' => false, 'url' => route('Equipment.show', $i->uuid),
        ])->keyBy('uuid');
        $serials = Dive::withoutGlobalScope('companions')->where(fn ($q) => $q->where('id', $dive->id)->orWhere('merged_into_id', $dive->id))
            ->whereNotNull('computer_serial')->pluck('computer_serial');
        if ($serials->isNotEmpty()) {
            EquipmentItem::where('user_id', $dive->user_id)->where('category', 'computer')->whereIn('serial_number', $serials)->get()
                ->each(function ($i) use ($list) {
                    if (!$list->has($i->uuid)) $list[$i->uuid] = ['uuid' => $i->uuid, 'name' => $i->name, 'category' => $i->categoryLabel(), 'icon' => $i->icon(), 'auto' => true, 'url' => route('Equipment.show', $i->uuid)];
                });
        }
        return $list->values()->all();
    }

    private function own(Request $request, string $uuid): EquipmentItem
    {
        return EquipmentItem::where('user_id', $request->user()->id)->where('uuid', $uuid)->firstOrFail();
    }

    private function ownConfig(Request $request, string $uuid): EquipmentConfig
    {
        return EquipmentConfig::where('user_id', $request->user()->id)->where('uuid', $uuid)->with('items')->firstOrFail();
    }

    private function ownItemIds(Request $request, array $uuids): array
    {
        return EquipmentItem::where('user_id', $request->user()->id)->whereIn('uuid', $uuids)->pluck('id')->all();
    }

    private function activeItems(Request $request)
    {
        return EquipmentItem::where('user_id', $request->user()->id)->whereNull('retired_at')->orderBy('name')->get()->groupBy('category');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'category' => ['required', Rule::in(array_keys(EquipmentItem::CATEGORIES))],
            'name' => 'required|string|max:120',
            'manufacturer' => 'nullable|string|max:120',
            'model' => 'nullable|string|max:120',
            'serial_number' => 'nullable|string|max:120',
            'purchased_on' => 'nullable|date',
            'last_service_on' => 'nullable|date',
            'notes' => 'nullable|string|max:5000',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:20480',
        ]);
        unset($data['photo']); // saved separately (savePhoto)
        return $data;
    }

    private function validatedConfig(Request $request): array
    {
        return $request->validate(['name' => 'required|string|max:120', 'notes' => 'nullable|string|max:2000', 'items' => 'nullable|array', 'items.*' => 'string']);
    }

    private function savePhoto(Request $request, EquipmentItem $item): void
    {
        if (!$request->hasFile('photo')) return;
        $name = $item->uuid . '_' . time() . '.webp';
        $dest = storage_path('app/equipment/' . $item->user_id . '/' . $name);
        if (SitePhoto::resizeTo($request->file('photo')->getRealPath(), [$dest => 1200])) {
            if ($item->photoPath()) @unlink($item->photoPath());
            $item->photo = $name;
            $item->save();
        }
    }
}

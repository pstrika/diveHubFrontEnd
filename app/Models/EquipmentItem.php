<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * One piece of the diver's gear (Pablo, 2026-10-17). A computer whose
 * serial_number matches a dive's computer_serial counts as used on that
 * dive automatically; everything else is recorded in dive_equipment.
 * Photo files live in storage/app/equipment/{user_id}/ (private).
 */
class EquipmentItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid', 'user_id', 'category', 'name', 'manufacturer', 'model', 'serial_number',
        'purchased_on', 'last_service_on', 'notes', 'photo', 'retired_at',
    ];

    protected $casts = ['purchased_on' => 'date', 'last_service_on' => 'date', 'retired_at' => 'date'];

    public const CATEGORIES = [
        'regulator' => ['Regulators', 'air'],
        'computer' => ['Computers', 'watch'],
        'bcd' => ['BCDs', 'checkroom'],
        'tank' => ['Tanks', 'propane_tank'],
        'mask' => ['Masks', 'visibility'],
        'fins' => ['Fins', 'water'],
        'camera' => ['Cameras', 'photo_camera'],
        // Wetsuits, drysuits, shorts, hoods, boots (Pablo, 2026-10-20).
        'suit' => ['Suits & Boots', 'checkroom'],
        'other' => ['Other', 'category'],
    ];

    protected static function booted()
    {
        static::creating(fn (EquipmentItem $i) => $i->uuid ??= (string) Str::uuid());
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category][0] ?? 'Other';
    }

    public function icon(): string
    {
        return self::CATEGORIES[$this->category][1] ?? 'category';
    }

    /** The type's icon (public/assets/img/icons/logbook, 2026-10-17). */
    public static function categoryIconUrl(?string $category): string
    {
        $c = array_key_exists((string) $category, self::CATEGORIES) ? $category : 'other';
        return asset('assets/img/icons/logbook/equipment_' . $c . '.svg');
    }

    public function iconUrl(): string
    {
        return self::categoryIconUrl($this->category);
    }

    /**
     * Gear you could have used on a dive that day: still in use, or retired
     * after it (Pablo, 2026-10-18: a retire date for gear no longer used
     * or no longer owned).
     */
    public function scopeUsableOn($q, $date)
    {
        return $q->where(fn ($w) => $w->whereNull('retired_at')->orWhereDate('retired_at', '>=', \Carbon\Carbon::parse($date)->toDateString()));
    }

    public function photoPath(): ?string
    {
        return $this->photo ? storage_path('app/equipment/' . $this->user_id . '/' . $this->photo) : null;
    }

    public function photoUrl(): ?string
    {
        return $this->photo ? route('Equipment.photo', $this->uuid) . '?v=' . substr(md5($this->photo), 0, 6) : null;
    }

    /** "Shearwater Perdix 2" - what lists show. */
    public function label(): string
    {
        return $this->name;
    }

    /** Ids of the diver's dives that used this item (explicit, or the computer's serial). */
    public function diveIds(): array
    {
        $ids = \DB::table('dive_equipment')->where('equipment_item_id', $this->id)->pluck('dive_id')->all();
        if ($this->category === 'computer' && !$this->serial_number && ($names = array_filter([$this->model, $this->name]))) {
            // No serial (an old watch): its dives that also have no serial, by model.
            $byModel = Dive::withoutGlobalScope('companions')->where('user_id', $this->user_id)->whereNull('computer_serial')
                ->whereIn('computer_model', $names)->get(['id', 'merged_into_id'])->map(fn ($d) => $d->merged_into_id ?: $d->id)->all();
            $ids = array_merge($ids, $byModel);
        }
        if ($this->category === 'computer' && $this->serial_number) {
            $bySerial = Dive::withoutGlobalScope('companions')->where('user_id', $this->user_id)
                ->where('computer_serial', $this->serial_number)
                ->get(['id', 'merged_into_id'])
                ->map(fn ($d) => $d->merged_into_id ?: $d->id)->all();
            $ids = array_merge($ids, $bySerial);
        }
        return array_values(array_unique(array_map('intval', $ids)));
    }

    public function toApi(): array
    {
        return [
            'uuid' => $this->uuid, 'category' => $this->category, 'name' => $this->name,
            'manufacturer' => $this->manufacturer, 'model' => $this->model, 'serial_number' => $this->serial_number,
            'purchased_on' => $this->purchased_on?->toDateString(), 'last_service_on' => $this->last_service_on?->toDateString(),
            'notes' => $this->notes, 'photo_url' => $this->photo ? url('api/equipment/items/' . $this->uuid . '/photo') . '?v=' . substr(md5($this->photo), 0, 6) : null, 'retired' => (bool) $this->retired_at, 'retired_on' => $this->retired_at?->toDateString(), 'icon_url' => $this->iconUrl(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

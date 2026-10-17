<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/** A named set of the diver's gear, applied to dives in one tap (Pablo, 2026-10-17). */
class EquipmentConfig extends Model
{
    use SoftDeletes;

    protected $fillable = ['uuid', 'user_id', 'name', 'notes'];

    protected static function booted()
    {
        static::creating(fn (EquipmentConfig $c) => $c->uuid ??= (string) Str::uuid());
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(EquipmentItem::class, 'equipment_config_items');
    }

    public function toApi(): array
    {
        return [
            'uuid' => $this->uuid, 'name' => $this->name, 'notes' => $this->notes,
            'items' => $this->items->pluck('uuid')->values(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

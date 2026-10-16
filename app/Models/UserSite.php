<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A diver's own dive site (Pablo, 2026-10-16: "Custom User Sites - Name,
 * GPS, type, min cert level, water type, altitude, location, country") for
 * anywhere the public catalog (Site, on mysql_trips) doesn't cover.
 * Private to its owner - never listed publicly, never merged into the
 * catalog by itself.
 */
class UserSite extends Model
{
    public const TYPES = ['wreck', 'reef', 'cave', 'cavern', 'quarry', 'mine', 'lake', 'river', 'other'];
    public const CERTS = ['OW', 'AOW', 'Ta', 'Tn', 'Th'];
    public const WATER = ['fresh', 'salt'];

    protected $fillable = [
        'user_id', 'name', 'type', 'min_cert', 'water_type', 'altitude_m',
        'latitude', 'longitude', 'location', 'country', 'notes',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'altitude_m' => 'integer',
    ];

    public static function typeLabel(?string $type): string
    {
        return $type ? ucfirst($type) : '';
    }

    public static function certLabel(?string $cert): string
    {
        return [
            'OW' => 'Open Water', 'AOW' => 'Advanced Open Water',
            'Ta' => 'Tec Advanced Nitrox', 'Tn' => 'Tec Normoxic Trimix', 'Th' => 'Tec Hypoxic Trimix',
        ][$cert] ?? ($cert ?? '');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dives(): HasMany
    {
        return $this->hasMany(Dive::class);
    }

    /** Shown as the "city" on logbook rows, same slot a catalog site's coast code fills. */
    public function cityLabel(): ?string
    {
        return $this->location ?: $this->country;
    }

    public function hasGps(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    /** Catalog site types have their own SVG icons; the extra custom types reuse "other". */
    public function iconType(): string
    {
        return in_array($this->type, ['wreck', 'reef'], true) ? $this->type : 'other';
    }

    public function publicUrl(): string
    {
        return route('Logbook.sites.index', ['highlight' => $this->id]);
    }
}

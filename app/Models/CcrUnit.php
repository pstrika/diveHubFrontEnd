<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * One rebreather a diver owns (Zach, 2026-11-10). `model` is a key into
 * config/ccr_checklist_templates.php 'units', which decides which checklist
 * stages the unit has and which template each one uses - so supporting a
 * new rebreather is a config entry, not a new table or view.
 */
class CcrUnit extends Model
{
    protected $table = 'ccr_units';

    protected $fillable = [
        'user_id',
        'model',
        'name',
        'serial_number',
        'equipment_item_id',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'retired_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(fn (CcrUnit $u) => $u->uuid ??= (string) Str::uuid());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(CcrChecklistSession::class, 'unit_id');
    }

    /**
     * This unit's entry in the config. An unknown key gets an empty
     * definition, never another unit's: a checklist written for one
     * rebreather must not be offered for a different one.
     */
    public function definition(): array
    {
        return config('ccr_checklist_templates.units', [])[$this->model] ?? [];
    }

    /** stage => template version. Empty when the unit has no checklist yet. */
    public function stageVersions(): array
    {
        return $this->definition()['stages'] ?? [];
    }

    public function modelLabel(): string
    {
        return $this->definition()['label'] ?? $this->model;
    }
}

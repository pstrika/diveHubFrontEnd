<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Marine life catalog (Pablo, 2026-10-17). user_id null = the shared
 * catalog (seeded with common South Florida species); user_id set = a
 * species that diver added, visible only to them. Ranks follow the
 * Wikipedia taxobox; `taxon_order` because ORDER is reserved in SQL.
 */
class Species extends Model
{
    protected $table = 'species';

    protected $fillable = [
        'uuid', 'user_id', 'common_name', 'other_names', 'category', 'kingdom', 'phylum', 'class', 'subclass',
        'division', 'taxon_order', 'family', 'genus', 'scientific_name', 'description', 'photo',
    ];

    protected $casts = ['other_names' => 'array'];

    /** Slug => label, in the order the pickers show them. */
    public const CATEGORIES = [
        'sharks' => 'Sharks',
        'rays' => 'Rays',
        'reef_fish' => 'Reef Fish',
        'pelagic_fish' => 'Pelagic Fish',
        'eels' => 'Eels',
        'turtles' => 'Turtles',
        'dolphins_manatees' => 'Dolphins & Manatees',
        'crustaceans' => 'Crustaceans',
        'octopus_squid' => 'Octopus & Squid',
        'sea_slugs_mollusks' => 'Sea Slugs & Mollusks',
        'urchins_stars' => 'Urchins & Sea Stars',
        'jellyfish' => 'Jellyfish',
        'corals' => 'Corals',
        'sponges' => 'Sponges',
        'other' => 'Other',
    ];

    /** Taxobox ranks, field => label, top down. */
    public const RANKS = [
        'kingdom' => 'Kingdom', 'phylum' => 'Phylum', 'class' => 'Class', 'subclass' => 'Subclass',
        'division' => 'Division', 'taxon_order' => 'Order', 'family' => 'Family', 'genus' => 'Genus',
        'scientific_name' => 'Species',
    ];

    protected static function booted()
    {
        static::creating(fn (Species $s) => $s->uuid ??= (string) Str::uuid());
    }

    /** The catalog plus this diver's own species. */
    public function scopeVisibleTo(Builder $q, int $userId): Builder
    {
        return $q->where(fn ($w) => $w->whereNull('user_id')->orWhere('user_id', $userId));
    }

    public function isCustom(): bool
    {
        return $this->user_id !== null;
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst((string) $this->category);
    }

    public function toApi(): array
    {
        return [
            'uuid' => $this->uuid,
            'common_name' => $this->common_name,
            'other_names' => $this->other_names ?? [],
            'category' => $this->category,
            'kingdom' => $this->kingdom, 'phylum' => $this->phylum, 'class' => $this->class, 'subclass' => $this->subclass,
            'division' => $this->division, 'order' => $this->taxon_order, 'family' => $this->family, 'genus' => $this->genus,
            'scientific_name' => $this->scientific_name,
            'description' => $this->description,
            'photo_url' => $this->photo ? asset('assets/img/species/' . $this->photo) : null,
            'custom' => $this->isCustom(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

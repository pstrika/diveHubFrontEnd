<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * A real dive downloaded from a dive computer via the native uploader
 * apps. `uuid` is the identity phones sync on (SYNC-CONTRACT.md) - the
 * integer `id` stays server-internal. SoftDeletes because a web delete
 * must propagate to phones via the change feed rather than just vanish.
 */
class Dive extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted()
    {
        static::creating(function (Dive $dive) {
            $dive->uuid ??= (string) Str::uuid();
        });
        // entered_deco is derived from the profile once, at write time (see
        // migration 2026_10_16_010000) so list views never load profiles.
        static::saving(function (Dive $dive) {
            if ($dive->isDirty('profile') || $dive->entered_deco === null) {
                $dive->entered_deco = self::scanEnteredDeco($dive->profile ?? []);
            }
        });
    }

    public static function scanEnteredDeco(array $profile): bool
    {
        foreach ($profile as $sample) {
            if (($sample['decoKind'] ?? null) === 'deco_stop') return true;
        }
        return false;
    }

    /** Columns a list view needs - everything but the (large) profile JSON. */
    public const LIST_COLUMNS = [
        'id', 'uuid', 'user_id', 'computer_family', 'computer_model', 'computer_serial', 'fingerprint', 'dive_number',
        'started_at', 'duration_seconds', 'max_depth_m', 'avg_depth_m', 'min_water_temp_c', 'max_water_temp_c',
        'dive_mode', 'entered_deco', 'gas_mixes', 'tanks', 'site_id', 'user_site_id', 'notes', 'conditions', 'tags',
        'source', 'merged_into_id', 'created_at', 'updated_at', 'deleted_at',
    ];

    /** Catalog sites already looked up this request (id => Site|null), so a list makes one query, not one per row. */
    private static array $siteCache = [];

    /** Look up every catalog site these dives reference in one query. */
    public static function preloadSites(iterable $dives): void
    {
        $ids = [];
        foreach ($dives as $d) {
            if ($d->site_id && !array_key_exists((int) $d->site_id, self::$siteCache)) $ids[(int) $d->site_id] = true;
        }
        if (!$ids) return;
        foreach (Site::whereIn('id', array_keys($ids))->get() as $site) {
            self::$siteCache[(int) $site->id] = $site;
        }
        foreach (array_keys($ids) as $id) {
            self::$siteCache[$id] ??= null;
        }
    }

    protected $fillable = [
        'user_id',
        'uuid',
        'computer_family',
        'computer_model',
        'computer_serial',
        'fingerprint',
        'dive_number',
        'started_at',
        'duration_seconds',
        'max_depth_m',
        'avg_depth_m',
        'surface_temp_c',
        'min_water_temp_c',
        'max_water_temp_c',
        'dive_mode',
        'gas_mixes',
        'tanks',
        'deco_model',
        'location',
        'sensor_data',
        'profile',
        'site_id',
        'user_site_id',
        'deco_plan_id',
        'notes',
        'conditions',
        'tags',
        'source',
        'merged_into_id',
    ];

    /** Sources whose computer-owned facts (depth, duration, gases...) a diver may edit by hand. */
    public const EDITABLE_SOURCES = ['manual', 'uddf-import'];

    protected $casts = [
        'started_at' => 'datetime',
        'gas_mixes' => 'array',
        'tanks' => 'array',
        'deco_model' => 'array',
        'location' => 'array',
        'sensor_data' => 'array',
        'profile' => 'array',
        'conditions' => 'array',
        'tags' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function decoPlan(): BelongsTo
    {
        return $this->belongsTo(DecoPlan::class);
    }

    /** No DB-level relation - Site lives on the separate mysql_trips connection. */
    public function site(): ?Site
    {
        if (!$this->site_id) return null;
        $id = (int) $this->site_id;
        if (!array_key_exists($id, self::$siteCache)) {
            self::$siteCache[$id] = Site::find($id);
        }
        return self::$siteCache[$id];
    }

    public function userSite(): BelongsTo
    {
        return $this->belongsTo(UserSite::class);
    }

    /**
     * Where this dive happened, whichever of the three ways it was tagged:
     * a catalog site (site_id), one of the diver's own sites
     * (user_site_id), or a free-typed name (conditions.site_name_custom).
     * One shape for the list rows, the details chip, the PDF and the API
     * so none of them re-derive the precedence. Null when untagged.
     *
     * @return array{name:string, city:?string, url:?string, type:?string, latitude:?float, longitude:?float, custom:bool, site:?Site, user_site:?UserSite}|null
     */
    public function siteInfo(): ?array
    {
        if ($this->site_id && ($site = $this->site())) {
            return [
                'name' => $site->name,
                'city' => \App\Support\SiteLocations::name($site->location),
                'url' => $site->publicUrl(),
                'type' => $site->type,
                'latitude' => $site->decimalLatitude(),
                'longitude' => $site->decimalLongitude(),
                'custom' => false,
                'site' => $site,
                'user_site' => null,
            ];
        }
        if ($this->user_site_id && ($us = $this->userSite)) {
            return [
                'name' => $us->name,
                'city' => $us->cityLabel(),
                'url' => $us->publicUrl(),
                'type' => $us->iconType(),
                'latitude' => $us->latitude,
                'longitude' => $us->longitude,
                'custom' => true,
                'site' => null,
                'user_site' => $us,
            ];
        }
        $name = $this->conditions['site_name_custom'] ?? null;
        if ($name) {
            return ['name' => $name, 'city' => null, 'url' => null, 'type' => null, 'latitude' => null, 'longitude' => null, 'custom' => true, 'site' => null, 'user_site' => null];
        }
        return null;
    }

    /** Row title: the site's name, else "{Recreational|Technical} Dive". */
    public function title(): string
    {
        return $this->siteInfo()['name'] ?? (ucfirst($this->classification()) . ' Dive');
    }

    /** True when the computer-owned facts can be edited by hand (manual entry, or an import that carried no profile). */
    public function factsEditable(): bool
    {
        if ($this->source === 'manual') return true;
        return $this->source === 'uddf-import' && !$this->hasProfile();
    }

    /** OC/CC is the diver's call only when no computer sync recorded it. */
    public function modeEditable(): bool
    {
        return in_array($this->source, self::EDITABLE_SOURCES, true);
    }

    /**
     * Switch a hand-logged or imported dive between open and closed
     * circuit. Closed circuit tags the gas breathed at the start as the
     * diluent (usage 2, as Shearwater reports it); open circuit clears it.
     */
    public function setModeByHand(string $mode): void
    {
        $this->dive_mode = $mode;
        $mixes = $this->gas_mixes ?? [];
        $dil = ($this->profile[0]['currentGas'] ?? null) ?? 0;
        foreach ($mixes as $gi => $mix) {
            $mixes[$gi]['usage'] = ($mode === 'closed_circuit' && $gi === $dil) ? 2 : 0;
        }
        $this->gas_mixes = $mixes ?: null;
    }

    public function hasProfile(): bool
    {
        return count($this->profile ?? []) > 1;
    }

    /**
     * Web-side delete (SYNC-CONTRACT.md "Web delete"): soft delete + a
     * `deleted` row in the change feed so phones drop their copy, and a
     * re-upload of the same fingerprint lands in DiveUploadController's
     * `deleted` bucket instead of resurrecting it.
     */
    public function deleteAndLog(): void
    {
        $this->delete();
        DiveChange::record($this->user_id, $this->uuid, 'deleted');
    }

    public function isCcr(): bool
    {
        return in_array($this->dive_mode, ['closed_circuit', 'semi_closed_circuit'], true);
    }

    /**
     * Open-circuit sidemount, straight from the computer: Shearwater tags
     * every cylinder of a sidemount dive with tanks[].usage "sidemount"
     * (libdivecomputer's DC_TANKUSAGE_SIDEMOUNT). Never true for CCR - a
     * sidemount rebreather is still a rebreather dive.
     */
    public function isSidemount(): bool
    {
        if ($this->isCcr()) return false;
        foreach ($this->tanks ?? [] as $tank) {
            if (($tank['usage'] ?? null) === 'sidemount') return true;
        }
        return false;
    }

    private ?bool $enteredDecoCache = null;

    /** Any sample the computer flagged as a mandatory deco stop (not an NDL/safety stop). */
    public function enteredDeco(): bool
    {
        // Stored column first (set on save, backfilled); a row loaded
        // without it - or from before the column - scans the profile.
        if (array_key_exists('entered_deco', $this->attributes) && $this->attributes['entered_deco'] !== null) {
            return (bool) $this->attributes['entered_deco'];
        }
        if ($this->enteredDecoCache === null) {
            $this->enteredDecoCache = self::scanEnteredDeco($this->profile ?? []);
        }
        return $this->enteredDecoCache;
    }

    /** "Closed Circuit" / "Open Circuit Sidemount" / "Open Circuit" - computer-reported, not editable. */
    public function modeLabel(): string
    {
        if ($this->isCcr()) return 'Closed Circuit';
        return $this->isSidemount() ? 'Open Circuit Sidemount' : 'Open Circuit';
    }

    /**
     * "Recreational"/"technical" (Pablo, 2026-10-15) - auto-classified as
     * technical past the standard 130ft/39.6m recreational limit OR when
     * the diver incurred a deco obligation (any mode - OC, CC or OC
     * sidemount), but a diver can override it (stored in
     * conditions.classification). The override always wins once set; the
     * rules are only ever a default for a dive nobody has touched yet.
     */
    public function classification(): string
    {
        $override = $this->conditions['classification'] ?? null;
        if (in_array($override, ['recreational', 'technical'], true)) {
            return $override;
        }

        return ($this->max_depth_m > 39.624 || $this->enteredDeco()) ? 'technical' : 'recreational';
    }

    /**
     * Which calendar SVG represents this dive (Pablo, 2026-10-15: reuse
     * the existing icons_calendar_*.svg set - see svg-icon.blade.php).
     * CCR and freediving come straight from the computer-reported
     * dive_mode (immutable, never user-editable - "this came from the
     * computer"); everything else (OC, gauge, unset) falls back to the
     * rec/tec classification. "snorkeling" has no DiveMode equivalent
     * today - only reachable once manual dive entry (docs/roadmap.md #9)
     * exists, so it's not wired into this lookup yet.
     */
    public function iconFile(): string
    {
        if ($this->isCcr()) return 'icons_calendar_ccr.svg';
        if ($this->dive_mode === 'freedive') return 'icons_calendar_freediving.svg';
        if ($this->isSidemount()) return 'divetype_sidemount_upright.svg';

        return $this->classification() === 'technical' ? 'icons_calendar_tec.svg' : 'icons_calendar_rec.svg';
    }

    /**
     * This diver's dive number - chronological position among their own
     * dives (oldest = 1), NOT the raw `dive_number` column. That column
     * is the dive computer's own on-device sequence, which the uploader
     * app can only report accurately on a full "All" download - a
     * partial "New"/"Last N" sync renumbers from 1 each time (see the
     * uploader session's note, 2026-10-14), so it's unreliable as "the
     * diver's Nth dive" and is display-only trivia, never used for this.
     */
    public function sequenceNumber(): int
    {
        return static::where('user_id', $this->user_id)
            ->where(function ($q) {
                $q->where('started_at', '<', $this->started_at)
                    ->orWhere(function ($q2) {
                        $q2->where('started_at', $this->started_at)->where('id', '<=', $this->id);
                    });
            })
            ->count();
    }

    /**
     * Role-labels each gas_mixes entry (Pablo, 2026-10-15). Usage==2 is
     * always "Diluent" (an explicit computer-reported tag, confirmed
     * reliable against all 47 real dives). Among the rest, the leanest
     * (lowest O2%) is the gas you'd actually breathe at depth - "Bailout"
     * on a CCR dive (an OC gas carried in case of bailout), "Backgas" on
     * an OC dive (your primary bottom gas); anything richer is a "Deco"
     * gas for shallower stops. A single-gas dive just gets that one
     * label, no "Deco" needed since there's nothing else to contrast it
     * against.
     */
    public function labeledGasMixes(): array
    {
        $mixes = $this->gas_mixes ?? [];
        $diluent = null;
        $others = [];

        foreach ($mixes as $mix) {
            if (($mix['usage'] ?? null) === 2) {
                $diluent = $mix;
            } else {
                $others[] = $mix;
            }
        }

        usort($others, fn ($a, $b) => ($a['oxygen'] ?? 0) <=> ($b['oxygen'] ?? 0));

        $primaryLabel = $this->isCcr() ? 'Bailout' : 'Backgas';
        $labeled = [];

        if ($diluent) {
            $labeled[] = ['mix' => $diluent, 'label' => 'Diluent'];
        }
        foreach ($others as $i => $mix) {
            $labeled[] = ['mix' => $mix, 'label' => $i === 0 ? $primaryLabel : 'Deco'];
        }

        return $labeled;
    }
}

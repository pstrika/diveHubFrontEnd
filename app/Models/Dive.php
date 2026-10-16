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
        'deco_plan_id',
        'notes',
        'conditions',
        'tags',
        'source',
    ];

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
        return $this->site_id ? Site::find($this->site_id) : null;
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
        if ($this->enteredDecoCache === null) {
            $this->enteredDecoCache = false;
            foreach ($this->profile ?? [] as $sample) {
                if (($sample['decoKind'] ?? null) === 'deco_stop') {
                    $this->enteredDecoCache = true;
                    break;
                }
            }
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

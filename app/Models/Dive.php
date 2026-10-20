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
        // A second (third...) computer's log of a dive is stored as its own
        // row with merged_into_id pointing at the dive it belongs to, and
        // stays out of every list, count, feed and lookup (Pablo,
        // 2026-10-16: "implement the full dedup suite"). Merged halves of
        // a split dive also carry merged_into_id but are soft-deleted, and
        // those must stay visible to withTrashed() queries (the app's
        // tombstone feed) - so only LIVE linked rows are hidden.
        // Code that needs them: withoutGlobalScope('companions').
        static::addGlobalScope('companions', function ($q) {
            $t = $q->getModel()->getTable();
            $q->where(fn ($w) => $w->whereNull($t . '.merged_into_id')->orWhereNotNull($t . '.deleted_at'));
        });
        static::creating(function (Dive $dive) {
            $dive->uuid ??= (string) Str::uuid();
        });
        // entered_deco is derived from the profile once, at write time (see
        // migration 2026_10_16_010000) so list views never load profiles.
        static::saving(function (Dive $dive) {
            if ($dive->isDirty('profile') || $dive->entered_deco === null) {
                // A dive went into deco if ANY computer worn on it says so
                // (the MacDive copy of a Petrel dive has no stops; the NERD
                // attached to it does).
                $dive->entered_deco = self::scanEnteredDeco($dive->profile ?? [])
                    || ($dive->exists && !$dive->merged_into_id && Dive::withoutGlobalScope('companions')->where('merged_into_id', $dive->id)->whereNull('deleted_at')->where('entered_deco', true)->exists());
            }
        });
        // A dive at a catalog site marks that site visited (Pablo,
        // 2026-10-17: "if a dive has a site we know, we should mark that
        // site as visited"). Only when the site is set or changed, so a
        // diver who un-ticks a site by hand isn't overridden on every save.
        static::saved(function (Dive $dive) {
            if ($dive->site_id && !$dive->merged_into_id && !$dive->trashed()
                && ($dive->wasRecentlyCreated || $dive->wasChanged('site_id'))) {
                self::markSiteVisited((int) $dive->user_id, (int) $dive->site_id, $dive->started_at);
            }
        });
    }

    /** Visited, dated with the earliest dive there. Never un-visits. */
    public static function markSiteVisited(int $userId, int $siteId, $when = null): void
    {
        $when = $when ? \Carbon\Carbon::parse($when) : now();
        $row = VisitedSite::where('userId', $userId)->where('siteId', $siteId)->first();
        if (!$row) {
            VisitedSite::create(['userId' => $userId, 'siteId' => $siteId, 'date' => $when]);
        } elseif (!$row->date || $when->lt($row->date)) {
            $row->date = $when;
            $row->save();
        }
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

    /** Photos on this dive, in the diver's order (Pablo, 2026-10-17). */
    public function photos(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DivePhoto::class)->orderBy('position')->orderBy('id');
    }

    /** Species seen on this dive. */
    public function species(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Species::class, 'dive_species')->withPivot('count', 'notes')->withTimestamps();
    }

    /** Gear recorded on this dive (computers matched by serial aren't rows here). */
    public function equipment(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(EquipmentItem::class, 'dive_equipment')->withPivot('equipment_config_id')->withTimestamps();
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
    /**
     * Lower-case text the logbook search looks through (Pablo, 2026-10-17):
     * site and city, operator, buddies, notes, tags and computer. Built
     * once per dive per request.
     */
    public function searchHaystack(): string
    {
        if (isset($this->searchHaystackCache)) return $this->searchHaystackCache;
        $si = $this->siteInfo();
        $c = $this->conditions ?? [];
        return $this->searchHaystackCache = mb_strtolower(implode(' ', array_filter([
            $si['name'] ?? null, $si['city'] ?? null, $c['operator_name'] ?? null, $c['buddies'] ?? null,
            $this->notes, implode(' ', $this->tags ?? []), $this->computer_model,
        ])));
    }

    private ?string $searchHaystackCache = null;

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
        $class = $this->classification();
        return $this->siteInfo()['name'] ?? (in_array($class, self::BREATH_HOLD_CLASSES, true) ? ucfirst($class) : ucfirst($class) . ' Dive');
    }

    /** True when the computer-owned facts can be edited by hand (manual entry, or an import that carried no profile). */
    public function factsEditable(): bool
    {
        if ($this->source === 'manual') return true;
        return $this->source === 'uddf-import' && !$this->hasProfile();
    }

    /**
     * "The computer wins" (Pablo, 2026-10-16): replace an IMPORTED dive's
     * computer-owned facts with a computer's own record of the same dive
     * (app sync, or a computer-native export like Shearwater XML), keeping
     * everything the diver added - site, notes, buddies, tags, operator,
     * conditions - except the gas pairings, which were keyed to the old
     * gas list. Logs 'updated' to the change feed.
     *
     * @param array $facts keys as in the dives table (computer_*, started_at,
     *                     duration_seconds, depths, temps, dive_mode,
     *                     gas_mixes, tanks, deco_model, sensor_data, profile)
     */
    public function replaceComputerFacts(array $facts, string $newSource): void
    {
        $conditions = $this->conditions ?? [];
        foreach (['gas_tank_choice', 'gas_role_choice', 'deleted_gas_mixes', 'gas_row_order', 'tank_config', 'gas_tank_size_choice'] as $k) unset($conditions[$k]);
        $keep = ['computer_family', 'computer_model', 'computer_serial', 'fingerprint', 'started_at', 'duration_seconds',
            'max_depth_m', 'avg_depth_m', 'surface_temp_c', 'min_water_temp_c', 'max_water_temp_c', 'dive_mode',
            'gas_mixes', 'tanks', 'deco_model', 'location', 'sensor_data', 'profile'];
        foreach ($keep as $k) {
            if (array_key_exists($k, $facts) && ($facts[$k] !== null || !in_array($k, ['fingerprint', 'location', 'computer_serial'], true))) {
                $this->$k = $facts[$k];
            }
        }
        $this->source = $newSource;
        $this->conditions = $conditions ?: null;
        $this->save();
        DiveChange::record($this->user_id, $this->uuid, 'updated');
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
        // The other computers' logs of this dive go with it; they stay as
        // tombstones so a re-sync of any of them isn't resurrected either.
        foreach ($this->companions()->get() as $c) $c->delete();
        $this->delete();
        DiveChange::record($this->user_id, $this->uuid, 'deleted');
    }

    /**
     * The app's dive JSON carries computed fields (same_dive, computers)
     * that change without the row's own columns changing: bump updated_at
     * and log 'updated' so phones refetch it (uploader session, 2026-10-16:
     * "log 'updated' for every dive whose computed fields change").
     */
    public function logUpdated(): void
    {
        if (!$this->exists || $this->trashed() || $this->isCompanion()) return;
        $this->touch();
        DiveChange::record($this->user_id, $this->uuid, 'updated');
    }

    /** logUpdated() for every live dive of an open same-dive group (ids), but $except. */
    public static function logUpdatedIds(array $ids, array $except = []): void
    {
        $ids = array_values(array_diff(array_unique(array_map('intval', $ids)), array_map('intval', $except)));
        if (!$ids) return;
        foreach (Dive::whereIn('id', $ids)->get() as $d) $d->logUpdated();
    }

    /** Ids of the open same-dive group this dive belongs to (itself included), or []. */
    public function sameDiveGroupIds(): array
    {
        $p = \App\Support\SameDivePending::forUser($this->user_id);
        $target = $p['asks'][$this->id] ?? (isset($p['groups'][$this->id]) ? $this->id : null);
        return $target === null ? [] : array_merge([$target], $p['groups'][$target] ?? []);
    }

    /** Other computers' logs of this same dive (live only). */
    public function companions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Dive::class, 'merged_into_id')->withoutGlobalScope('companions')->orderBy('id');
    }

    /** This row is another computer's log, attached to a dive. */
    public function isCompanion(): bool
    {
        return $this->merged_into_id !== null && !$this->trashed();
    }

    /** The fact columns a computer owns (vs the diver's annotations). */
    public const COMPUTER_FACTS = ['computer_family', 'computer_model', 'computer_serial', 'fingerprint', 'dive_number',
        'started_at', 'duration_seconds', 'max_depth_m', 'avg_depth_m', 'surface_temp_c', 'min_water_temp_c',
        'max_water_temp_c', 'dive_mode', 'gas_mixes', 'tanks', 'deco_model', 'location', 'sensor_data', 'profile', 'source'];

    /**
     * Attach another computer's log of this dive. Only computer facts are
     * copied - the dive's site, notes, tags and conditions stay on this
     * (main) row. Not logged to the change feed: phones only know the
     * main dive.
     */
    public function attachComputerLog(array $facts): Dive
    {
        $row = ['user_id' => $this->user_id, 'merged_into_id' => $this->id];
        foreach (self::COMPUTER_FACTS as $k) if (array_key_exists($k, $facts)) $row[$k] = $facts[$k];
        $row['profile'] ??= [];
        $row['dive_mode'] ??= 'open_circuit';
        $c = new Dive();
        $c->forceFill($row);
        $c->save();
        $this->refreshEnteredDeco();
        $this->logUpdated();
        return $c;
    }

    /**
     * Make a companion's computer the dive's main one: the two rows swap
     * their computer facts (identity included, so each computer's re-sync
     * still finds its own log); the diver's annotations stay here. Gas
     * pairings were keyed to the old gas list, so they reset.
     */
    public function promoteCompanion(Dive $companion): void
    {
        if ($companion->merged_into_id !== $this->id) throw new \InvalidArgumentException('Not a log of this dive.');
        \Illuminate\Support\Facades\DB::transaction(function () use ($companion) {
            $mine = []; $theirs = [];
            foreach (self::COMPUTER_FACTS as $k) { $mine[$k] = $this->getAttribute($k); $theirs[$k] = $companion->getAttribute($k); }
            // Unique-ish identity (serial + fingerprint) moves in two steps.
            $companion->forceFill(['fingerprint' => null])->save();
            $this->forceFill($theirs);
            $conditions = $this->conditions ?? [];
            foreach (['gas_tank_choice', 'gas_role_choice', 'deleted_gas_mixes', 'gas_row_order', 'tank_config', 'gas_tank_size_choice'] as $k) unset($conditions[$k]);
            $this->conditions = $conditions ?: null;
            $this->save();
            $companion->forceFill($mine)->save();
            DiveChange::record($this->user_id, $this->uuid, 'updated');
        });
    }

    /**
     * Combine: $other (another computer's log of this same dive, until now
     * a dive of its own) becomes an attached log of this one. What the
     * diver wrote on $other that this dive lacks (site, notes, tags, each
     * conditions field) is carried over first; $other's own attached logs
     * move here too. Phones drop $other (change feed 'deleted'); a re-sync
     * of its computer then finds the attached log and reports this dive.
     */
    public function absorb(Dive $other): void
    {
        if ($other->id === $this->id || $other->user_id !== $this->user_id) throw new \InvalidArgumentException('Not combinable.');
        $group = array_merge($this->sameDiveGroupIds(), $other->sameDiveGroupIds());
        \Illuminate\Support\Facades\DB::transaction(function () use ($other) {
            if (!$this->site_id && !$this->user_site_id) { $this->site_id = $other->site_id; $this->user_site_id = $other->user_site_id; }
            if (!trim((string) $this->notes) && trim((string) $other->notes)) $this->notes = $other->notes;
            $this->tags = array_values(array_unique(array_merge($this->tags ?? [], $other->tags ?? [])));
            $c = $this->conditions ?? [];
            foreach (($other->conditions ?? []) as $k => $v) {
                if (str_starts_with($k, 'same_dive_')) continue;
                if (!array_key_exists($k, $c) || $c[$k] === null || $c[$k] === '') $c[$k] = $v;
            }
            unset($c['same_dive_suggestion']);
            $this->conditions = $c ?: null;
            $this->save();
            Dive::withoutGlobalScope('companions')->where('merged_into_id', $other->id)->whereNull('deleted_at')->update(['merged_into_id' => $this->id]);
            $oc = $other->conditions ?? [];
            unset($oc['same_dive_suggestion']);
            $other->forceFill(['merged_into_id' => $this->id, 'conditions' => $oc ?: null])->save();
            DiveChange::record($other->user_id, $other->uuid, 'deleted');
            DiveChange::record($this->user_id, $this->uuid, 'updated');
        });
        \App\Support\SameDivePending::forget();
        $this->refreshEnteredDeco();
        // A third computer still asking about either dive: its same_dive changed.
        self::logUpdatedIds($group, [$this->id, $other->id]);
    }

    /** Re-derive entered_deco from this dive's profile and its attached logs. */
    public function refreshEnteredDeco(): void
    {
        $this->entered_deco = self::scanEnteredDeco($this->profile ?? [])
            || Dive::withoutGlobalScope('companions')->where('merged_into_id', $this->id)->whereNull('deleted_at')->where('entered_deco', true)->exists();
        $this->saveQuietly();
    }

    /** Turn an attached log back into a dive of its own (a wrong match). */
    public function detachCompanion(Dive $companion): Dive
    {
        if ($companion->merged_into_id !== $this->id) throw new \InvalidArgumentException('Not a log of this dive.');
        $companion->forceFill(['merged_into_id' => null])->save();
        DiveChange::record($companion->user_id, $companion->uuid, 'created');
        $this->logUpdated();
        return $companion;
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
        if ($this->dive_mode === 'freedive') return 'Breath-hold';
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
    /** Classifications for a breath-hold (dive_mode 'freedive') dive; every other mode is recreational/technical. */
    public const BREATH_HOLD_CLASSES = ['snorkeling', 'freediving'];

    /** The pair of classifications this dive can take. */
    public function classificationOptions(): array
    {
        return $this->dive_mode === 'freedive' ? self::BREATH_HOLD_CLASSES : ['recreational', 'technical'];
    }

    public function classification(): string
    {
        $override = $this->conditions['classification'] ?? null;
        // Breath-hold dives (computer in freedive/snorkel mode) pick between
        // their own pair (Pablo, 2026-10-19): snorkeling unless deeper than
        // 5 ft, freediving otherwise; the diver's own choice wins.
        if ($this->dive_mode === 'freedive') {
            if (in_array($override, self::BREATH_HOLD_CLASSES, true)) return $override;
            return $this->max_depth_m < 1.524 ? 'snorkeling' : 'freediving';
        }
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
        if ($this->dive_mode === 'freedive') return $this->classification() === 'snorkeling' ? 'icons_calendar_snorkeling.svg' : 'icons_calendar_freediving.svg';
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
    /** The number the diver sees: "844", or "844.b" while a same-dive question is open. */
    public function logbookNumber(): string
    {
        return \App\Support\SameDivePending::label($this);
    }

    public function sequenceNumber(): int
    {
        // Dives still asking "same dive as...?" share the number of the
        // dive they ask about (844.b) and don't use one up.
        $asking = array_keys(\App\Support\SameDivePending::forUser($this->user_id)['asks']);
        return static::where('user_id', $this->user_id)
            ->when($asking, fn ($q) => $q->whereNotIn('id', $asking))
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

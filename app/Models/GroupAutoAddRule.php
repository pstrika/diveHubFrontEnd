<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One auto-add rule per group (Pablo, 2026-09-22: "implement automatic
 * rules (only 1 per group that the group admin can edit)...add trips
 * automatically to the group"). See matches() for the exact criteria.
 * Applied by App\Console\Commands\ApplyGroupAutoAddRules, on a cron
 * (Azure Logic App) and immediately after a save via
 * GroupAutoAddRuleController::update().
 */
class GroupAutoAddRule extends Model
{
    protected $connection = 'mysql_trips';
    protected $table = 'group_auto_add_rules';

    protected $fillable = [
        'group_id',
        'enabled',
        'trip_types',
        'operator_ids',
        'locations',
        'levels',
        'site_ids',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'trip_types' => 'array',
        'operator_ids' => 'array',
        'locations' => 'array',
        'levels' => 'array',
        'site_ids' => 'array',
    ];

    /** Caps enforced at save time (GroupAutoAddRuleController) and mirrored client-side. */
    public const MAX_OPERATORS = 7;
    public const MAX_LOCATIONS = 3;
    public const MAX_SITES = 10;

    public const TRIP_TYPES = ['SHARK', 'LOBSTER', 'REC', 'TEC'];

    /**
     * Sentinel "All levels" pick, distinct from the real 0-4 DiveLevel
     * values (Pablo, 2026-09-23: lobster trips almost never get a site
     * assigned - confirmed zero of 22 upcoming lobster trips from a real
     * rule's operators had any siteId at all - so requiring a level match
     * meant the level-or-site clause could never be satisfied). Selecting
     * it bypasses level filtering entirely, matching trips with no site/
     * level data too - mutually exclusive with real levels in the UI.
     */
    public const LEVEL_ALL = -1;

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    /**
     * A trip matches when it has one of trip_types, AND (its operator is
     * in operator_ids OR its location is in locations), AND (a site level
     * is in levels OR one of its sites is in site_ids). Pure - no queries
     * beyond what $trip already carries, so this is cheap to call once per
     * candidate trip and independently testable in tinker.
     */
    public function matches(Trip $trip): bool
    {
        if (!$this->matchesTripType($trip)) {
            return false;
        }

        $operatorIds = $this->operator_ids ?? [];
        $locations = $this->locations ?? [];
        $matchesOperatorOrLocation = in_array((int) $trip->operatorId, $operatorIds, true)
            || in_array($this->tripLocation($trip), $locations, true);

        if (!$matchesOperatorOrLocation) {
            return false;
        }

        $levels = $this->levels ?? [];
        $siteIds = $this->site_ids ?? [];
        $tripSiteIds = $this->tripSiteIds($trip);

        if (in_array(self::LEVEL_ALL, $levels, true)) {
            return true;
        }

        $matchesLevelOrSite = (!empty($levels) && $this->tripHasLevel($trip, $levels))
            || (!empty($siteIds) && !empty(array_intersect($tripSiteIds, $siteIds)));

        return $matchesLevelOrSite;
    }

    /**
     * Same derivation TripBoard::card() uses: isShark/isLobster from tags
     * containing SHA/LOB, isTech from tags containing TEC or tripType
     * containing "tech" - REC means none of the other three are true.
     */
    private function matchesTripType(Trip $trip): bool
    {
        $types = $this->trip_types ?? [];
        if (empty($types)) {
            return false;
        }

        $tags = strtoupper((string) $trip->tags);
        $isShark = str_contains($tags, 'SHA');
        $isLobster = str_contains($tags, 'LOB');
        $isTech = str_contains($tags, 'TEC') || stripos((string) $trip->tripType, 'tech') !== false;
        $isRec = !$isShark && !$isLobster && !$isTech;

        return (in_array('SHARK', $types, true) && $isShark)
            || (in_array('LOBSTER', $types, true) && $isLobster)
            || (in_array('TEC', $types, true) && $isTech)
            || (in_array('REC', $types, true) && $isRec);
    }

    /**
     * Same derivation TripBoard::card() uses for locationCode: the first
     * token of tags if it looks like a weather-location code, else the
     * operator's own location.
     */
    private function tripLocation(Trip $trip): ?string
    {
        $tags = strtoupper((string) $trip->tags);
        $code = strtok($tags, ' ') ?: null;
        if ($code && preg_match('/^[A-Z]{2,3}$/', $code)) {
            return $code;
        }

        return $trip->operator->location ?? null;
    }

    /**
     * trips.siteId is a comma-separated list, sometimes with a trailing
     * comma/space (e.g. "370, ") - filtered to real positive ids so that
     * doesn't silently become a spurious 0.
     *
     * @return int[]
     */
    private function tripSiteIds(Trip $trip): array
    {
        if (!$trip->siteId) {
            return [];
        }

        return array_values(array_filter(array_map('intval', explode(',', $trip->siteId))));
    }

    private function tripHasLevel(Trip $trip, array $levels): bool
    {
        $siteIds = $this->tripSiteIds($trip);
        if (empty($siteIds)) {
            return false;
        }

        return Site::whereIn('id', $siteIds)->whereIn('level', $levels)->exists();
    }
}

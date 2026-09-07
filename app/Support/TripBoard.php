<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Builds the trip board (proposal W2) from the trips a controller already
 * loaded and enriched.
 *
 * Why a separate class: the Blade view used to read Eloquent rows directly
 * and each screen (Trips, the themed calendars, My Dashboard) repeated the
 * same "Y / 11 / -" and icon logic. The board is now a plain array with
 * one normalised card per trip, grouped by coast, so every screen renders
 * the same card and a JSON feed for the offline app later is just
 * `return response()->json($board)`.
 *
 * Input trips must already carry what TripsController adds in its loop:
 * $trip->site (array of Site rows), $trip->level, $trip->tags, $trip->fav,
 * $trip->visited. Nothing here touches the database.
 *
 * Filters (all optional, read from the query string):
 *   region = coast key (see Coast)      level = 0..4 (see DiveLevel)
 *   type   = rec|tec|wreck|shark|lobster|night
 *   seats  = 1 to hide trips with no seats
 */
final class TripBoard
{
    public const TYPE_OPTIONS = [
        'rec'     => 'Recreational',
        'tec'     => 'Technical',
        'wreck'   => 'Wreck',
        'shark'   => 'Shark',
        'lobster' => 'Lobster',
        'night'   => 'Night',
    ];

    /** Seats at or below this count show as "Few seats". */
    public const FEW_SEATS = 3;

    /** Read and sanitise filters from the request. Unknown values fall back to "all". */
    public static function filtersFromRequest(Request $request): array
    {
        $region = $request->query('region');
        $level  = $request->query('level');
        $type   = $request->query('type');

        return [
            'region' => Coast::isValid($region) ? $region : null,
            'level'  => DiveLevel::isValid($level) ? (int) $level : null,
            'type'   => array_key_exists((string) $type, self::TYPE_OPTIONS) ? $type : null,
            'seats'  => $request->boolean('seats'),
        ];
    }

    /**
     * @param Collection $trips     enriched Trip models for one date
     * @param Collection $weathers  Weatherday rows for that date, all locations
     * @param Collection $locations WeatherLocation rows (short => location name)
     * @param array      $filters   from filtersFromRequest()
     * @param array      $operators id => Operator (for phone numbers on "Call to book"), optional
     */
    public static function build(Collection $trips, Collection $weathers, Collection $locations, array $filters, array $operators = []): array
    {
        $now = Carbon::now();
        $shortToName = $locations->pluck('location', 'short')->map(fn ($n) => strtolower($n))->all();
        $nameToShort = array_flip($shortToName);
        $weatherByShort = [];
        foreach ($weathers as $w) {
            $short = $nameToShort[strtolower($w->location)] ?? null;
            if ($short) {
                $weatherByShort[$short] = $w;
            }
        }

        $groups = [];
        $total = 0;
        $shown = 0;

        foreach ($trips as $trip) {
            $total++;
            $card = self::card($trip, $now, $operators);
            if (!self::passes($card, $filters)) {
                continue;
            }
            $shown++;
            $coast = Coast::forCode($card['locationCode']);
            if (!isset($groups[$coast])) {
                $groups[$coast] = ['key' => $coast, 'label' => Coast::label($coast), 'trips' => [], 'locations' => []];
            }
            $groups[$coast]['trips'][] = $card;
            // Collect the sea state for each location that has trips in this coast.
            $code = $card['locationCode'];
            if ($code && !isset($groups[$coast]['locations'][$code])) {
                $w = $weatherByShort[$code] ?? null;
                $groups[$coast]['locations'][$code] = [
                    'code' => $code,
                    'name' => ucwords($shortToName[$code] ?? $code),
                    'am'   => $w ? $w->conditionsAM_text : null,
                    'pm'   => $w ? $w->conditionsPM_text : null,
                ];
            }
        }

        // Coast display order is fixed (north to south); trips within by time.
        $ordered = [];
        foreach (array_keys(Coast::all()) as $key) {
            if (isset($groups[$key])) {
                usort($groups[$key]['trips'], fn ($a, $b) => strcmp($a['time24'], $b['time24']));
                $groups[$key]['locations'] = array_values($groups[$key]['locations']);
                $ordered[] = $groups[$key];
            }
        }

        return [
            'groups'  => $ordered,
            'total'   => $total,
            'shown'   => $shown,
            'filters' => $filters,
        ];
    }

    /** One normalised trip card. Keys are stable; views and the future JSON feed rely on them. */
    public static function card($trip, Carbon $now, array $operators = []): array
    {
        $tags = strtoupper((string) $trip->tags);
        $locationCode = strtok($tags, ' ') ?: null;
        if ($locationCode && !preg_match('/^[A-Z]{2,3}$/', $locationCode)) {
            $locationCode = null;
        }
        if (!$locationCode && isset($operators[$trip->operatorId])) {
            $locationCode = $operators[$trip->operatorId]->location;
        }

        $sites = is_array($trip->site ?? null) ? $trip->site : [];
        $first = $sites[0] ?? null;
        $siteNames = array_values(array_unique(array_filter(array_map(fn ($s) => $s->name ?? null, $sites))));

        $departure = Carbon::parse($trip->date . ' ' . $trip->departureTime);
        $departed = $departure->lt($now);
        $hour = (int) substr((string) $trip->departureTime, 0, 2);

        $isTech = stripos((string) $trip->tripType, 'tech') !== false || str_contains($tags, 'TEC');
        $isWreck = collect($sites)->contains(fn ($s) => strtolower($s->type ?? '') === 'wreck') || str_contains($tags, 'WRE');

        return [
            'id'            => $trip->id,
            'date'          => $trip->date,
            'time24'        => $trip->departureTime,
            'time'          => $departure->format('g:i'),
            'meridiem'      => $departure->format('A'),
            'period'        => $hour < 12 ? 'AM' : 'PM',
            'departed'      => $departed,
            'title'         => self::cleanTitle($trip->tripName, $siteNames),
            'fullTitle'     => (string) $trip->tripName,
            'siteNames'     => $siteNames,
            'operatorName'  => $trip->operatorName,
            'operatorId'    => $trip->operatorId,
            'operatorPhone' => $operators[$trip->operatorId]->phone ?? null,
            'locationCode'  => $locationCode,
            'level'         => isset($trip->level) && DiveLevel::isValid($trip->level) ? (int) $trip->level : null,
            'maxDepth'      => $first->maxDepth ?? null,
            'isTech'        => $isTech,
            'isWreck'       => $isWreck,
            'isShark'       => str_contains($tags, 'SHA'),
            'isLobster'     => str_contains($tags, 'LOB'),
            'isNight'       => $hour >= 17,
            'fav'           => (bool) ($trip->fav ?? false),
            'visited'       => (bool) ($trip->visited ?? false),
            'availability'  => self::availability($trip, $departed),
            'bookUrl'       => $departed ? null : ($trip->linkToBook ?: null),
            'detailsUrl'    => route('TripDetails', ['tripId' => $trip->id]),
            'operatorUrl'   => route('OperatorDetails', ['id' => $trip->operatorId]),
            'siteUrl'       => $first ? route('SiteDetails') . '/' . ($first->slug ?? $first->id) : null,
        ];
    }

    /**
     * One availability vocabulary for the whole product (proposal W2 note 4).
     * The crawler stores 1000 when an operator only says "available", so that
     * becomes "Seats open" without a number. Real counts are shown when known.
     *
     * @return array{state:string,label:string,count:?int}
     */
    public static function availability($trip, bool $departed): array
    {
        $spots = (int) $trip->tripFreeSpots;
        $hasLink = !empty($trip->linkToBook);

        if ($departed) {
            return ['state' => 'departed', 'label' => 'Departed', 'count' => null];
        }
        if ($spots <= 0) {
            return ['state' => 'full', 'label' => 'Full', 'count' => 0];
        }
        if ($spots >= 1000) {
            return ['state' => $hasLink ? 'open' : 'call', 'label' => $hasLink ? 'Seats open' : 'Call to book', 'count' => null];
        }
        if ($spots <= self::FEW_SEATS) {
            return ['state' => 'few', 'label' => $spots === 1 ? '1 seat left' : "$spots seats left", 'count' => $spots];
        }
        return ['state' => $hasLink ? 'open' : 'call', 'label' => $hasLink ? "$spots seats" : "$spots seats, call to book", 'count' => $spots];
    }

    /**
     * Operators publish marketing strings as trip names ("Turtle Nesting
     * Season- See up to four species..."). Cards show a short title; the full
     * string stays available in the details page and as a tooltip.
     */
    public static function cleanTitle(?string $name, array $siteNames): string
    {
        $name = trim((string) $name);
        if ($name === '') {
            return $siteNames ? implode(' + ', array_slice($siteNames, 0, 2)) : 'Dive trip';
        }
        // Cut at the first sentence-ish separator, then hard cap.
        $short = preg_split('/\s[-–]\s|[-–]\s|\.\s|:\s|\s\|\s/u', $name, 2)[0] ?? $name;
        $short = trim($short);
        if (mb_strlen($short) > 48) {
            $short = rtrim(mb_substr($short, 0, 45)) . '…';
        }
        return $short;
    }

    private static function passes(array $card, array $filters): bool
    {
        if ($filters['region'] && Coast::forCode($card['locationCode']) !== $filters['region']) {
            return false;
        }
        if ($filters['level'] !== null && $card['level'] !== $filters['level']) {
            return false;
        }
        if ($filters['seats'] && in_array($card['availability']['state'], ['full', 'departed'], true)) {
            return false;
        }
        switch ($filters['type']) {
            case 'rec':     return !$card['isTech'];
            case 'tec':     return $card['isTech'];
            case 'wreck':   return $card['isWreck'];
            case 'shark':   return $card['isShark'];
            case 'lobster': return $card['isLobster'];
            case 'night':   return $card['isNight'];
        }
        return true;
    }
}

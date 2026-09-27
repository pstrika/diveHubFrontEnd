<?php

namespace App\Support;

use App\Models\Boat;
use App\Models\Operator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Builds the Dive Operators explorer: filters from the query string, the
 * matching operators, chip counts, and a card view model per operator.
 *
 * Companion to TripBoard (trips) and SiteController::explorer (sites). All
 * three follow the same rule: every filter is a query parameter, every chip
 * is a link, and the filtering runs in MySQL so it holds up when there are
 * thousands of operators instead of fifty.
 *
 * Query parameters
 *   q       free text: name, city, area, marina, or a boat name
 *   region  a Coast key (treasure, palm, broward, miami, keys). Operators are
 *           mapped to a coast through operators.location, the same weather
 *           location short code the trips use.
 *   has     one feature: tec, nitrox, trimix, private
 *   sort    name | price | rate | tec
 *   view    list (default) | map
 */
final class OperatorBoard
{
    public const SORTS = ['name' => 'A to Z', 'price' => 'Price', 'rate' => 'Top rated', 'tec' => 'Tech first'];

    /** Feature chips. key => [label, column]. Only features present in the current selection are shown. */
    public const FEATURES = [
        'tec'     => ['label' => 'Technical',       'column' => 'tec'],
        'nitrox'  => ['label' => 'Nitrox fills',    'column' => 'onSiteFillNitrox'],
        'trimix'  => ['label' => 'Trimix fills',    'column' => 'onSiteFillTrimix'],
        'private' => ['label' => 'Private charters', 'column' => 'private'],
    ];

    /** Which trip price to show on a card, in order of preference. */
    private const PRICE_TYPES = ['Recreational - 2 Tank', 'Recreational - 2 Tank Reef', 'Recreational - 3 Tank', 'Private - Half Day'];

    public static function filtersFromRequest(Request $request): array
    {
        $region = $request->query('region');
        $has    = $request->query('has');
        $sort   = $request->query('sort', 'name');
        return [
            'q'      => trim((string) $request->query('q', '')),
            'region' => Coast::isValid($region) && $region !== 'other' ? $region : null,
            'has'    => array_key_exists((string) $has, self::FEATURES) ? $has : null,
            'sort'   => array_key_exists((string) $sort, self::SORTS) ? $sort : 'name',
            'view'   => $request->query('view') === 'map' ? 'map' : 'list',
        ];
    }

    /**
     * @return array{operators: Collection, cards: array, filters: array, regionCounts: array, featureCounts: array, total: int}
     */
    public static function build(Request $request): array
    {
        $f = self::filtersFromRequest($request);

        // Base query: text search only. Region and feature filters are layered on
        // below, and the chip counts are taken with that chip's own filter off so
        // each chip reads "how many if I pick this".
        $base = Operator::query();
        if ($f['q'] !== '') {
            $q = $f['q'];
            $boatOperatorIds = Boat::where('name', 'LIKE', "%$q%")->pluck('operatorId');
            $base->where(function ($w) use ($q, $boatOperatorIds) {
                $w->where('operatorName', 'LIKE', "%$q%")
                  ->orWhere('cityAddress', 'LIKE', "%$q%")
                  ->orWhere('locationArea', 'LIKE', "%$q%")
                  ->orWhere('marinaAddress', 'LIKE', "%$q%");
                if ($boatOperatorIds->isNotEmpty()) {
                    $w->orWhereIn('id', $boatOperatorIds);
                }
            });
        }

        $applyRegion = function ($query, ?string $region) {
            if ($region === null) {
                return $query;
            }
            return $query->whereIn('location', Coast::all()[$region]['codes']);
        };
        $applyFeature = function ($query, ?string $has) {
            return $has === null ? $query : $query->where(self::FEATURES[$has]['column'], 1);
        };

        // Region chips are static (every coast) with a count in parentheses.
        $byLocation = $applyFeature(clone $base, $f['has'])
            ->selectRaw('location, COUNT(*) c')->groupBy('location')->pluck('c', 'location');
        $regionCounts = array_fill_keys(array_keys(Coast::chipOptions()), 0);
        foreach ($byLocation as $code => $c) {
            $key = Coast::forCode($code);
            if (isset($regionCounts[$key])) {
                $regionCounts[$key] += $c;
            }
        }

        // Feature chips show only what exists in the current region selection.
        $featureQuery = $applyRegion(clone $base, $f['region']);
        $featureCounts = [];
        foreach (self::FEATURES as $key => $feat) {
            $c = (clone $featureQuery)->where($feat['column'], 1)->count();
            if ($c > 0 || $f['has'] === $key) {
                $featureCounts[$key] = $c;
            }
        }

        $query = $applyFeature($applyRegion($base, $f['region']), $f['has']);
        switch ($f['sort']) {
            case 'rate': $query->orderByRaw('rate IS NULL, rate DESC')->orderBy('votes', 'desc')->orderBy('operatorName'); break;
            case 'tec':  $query->orderBy('tec', 'desc')->orderBy('operatorName'); break;
            default:     $query->orderBy('operatorName');
        }
        $operators = $query->get();

        $cards = $operators->map(fn ($o) => self::card($o))->values()->all();
        if ($f['sort'] === 'price') {
            // Price lives inside a JSON text column, so this one sort happens in PHP.
            usort($cards, fn ($a, $b) => ($a['price'] ?? PHP_INT_MAX) <=> ($b['price'] ?? PHP_INT_MAX) ?: strcmp($a['name'], $b['name']));
        }

        return [
            'operators'     => $operators,
            'cards'         => $cards,
            'filters'       => $f,
            'regionCounts'  => $regionCounts,
            'featureCounts' => $featureCounts,
            'total'         => Operator::count(),
        ];
    }

    /**
     * Plain array for an operator card. Kept free of Eloquent so it can be
     * emitted as JSON later for the offline app.
     */
    public static function card(Operator $o): array
    {
        [$price, $priceLabel] = self::fromPrice($o);
        return [
            'id'         => $o->id,
            'name'       => $o->operatorName,
            'url'        => route('OperatorDetails', ['id' => $o->slug ?? $o->id]),
            'logo'       => $o->logoUrl ? asset('assets') . $o->logoUrl : null,
            'city'       => $o->cityAddress,
            'area'       => $o->locationArea,
            'coast'      => Coast::label(Coast::forCode($o->location)),
            'price'      => $price,
            'priceLabel' => $priceLabel,
            'tec'        => (bool) $o->tec,
            'private'    => (bool) $o->private,
            'fills'      => array_keys(array_filter([
                'Air' => $o->onSiteFillAir, 'Nitrox' => $o->onSiteFillNitrox, 'Trimix' => $o->onSiteFillTrimix, 'O2' => $o->onSiteFillO2,
            ])),
            'rate'       => $o->rate ? round((float) $o->rate, 1) : null,
            'votes'      => (int) $o->votes,
            'phone'      => $o->phone,
            'website'    => $o->webSite,
            'address'    => trim(implode(', ', array_filter([$o->streetAddress, $o->cityAddress])) . ' ' . $o->stateAddress . ' ' . $o->zipAddress),
        ];
    }

    /**
     * The headline price for a card: the two tank recreational trip when the
     * operator lists one, otherwise the first price they do list.
     * operators.tripPrice is JSON text: [{"type":"Recreational - 2 Tank","price":"95"}, ...]
     *
     * @return array{0: ?int, 1: ?string} [price in USD, price type label]
     */
    public static function fromPrice(Operator $o): array
    {
        $rows = json_decode((string) $o->tripPrice, true);
        if (!is_array($rows) || !$rows) {
            return [null, null];
        }
        foreach (self::PRICE_TYPES as $type) {
            foreach ($rows as $r) {
                if (($r['type'] ?? '') === $type && is_numeric($r['price'] ?? null)) {
                    return [(int) $r['price'], $type];
                }
            }
        }
        $first = $rows[0];
        return [is_numeric($first['price'] ?? null) ? (int) $first['price'] : null, $first['type'] ?? null];
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Operator;
use App\Models\Site;
use App\Models\Trip;
use App\Models\WeatherLocation;
use App\Models\Weatherday;
use App\Support\TripBoard;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Indexable schedule pages (SEO audit, 2026-10-24, proposals 3 and 4).
 * The combined schedule is Divers Hub's best-ranking asset, but it lives on
 * one URL; searches like "Fort Lauderdale dive trips this weekend" or
 * "shark dive trips Jupiter" go to Tripadvisor and single operators. Each
 * page here lists the next seven days of departures from every operator
 * for one area (by the trips' weather-location codes) or one kind of dive
 * (the board's own shark / wreck / lobster tests), with a short written
 * intro, using the same trip cards as /Trips.
 *
 *   /DiveTrips              index of every page below
 *   /DiveTrips/{slug}       one area or theme
 *
 * Nothing here is personal, so a page is built once per 10 minutes for
 * everyone - the same cost posture as the guest /Trips board.
 */
class DiveTripsController extends Controller
{
    public const DAYS = 7;

    /**
     * slug => page. Areas filter by weather-location codes (see Coast);
     * themes by TripBoard type. Copy is written for divers searching the
     * area, not for the site.
     */
    public const PAGES = [
        'fort-lauderdale' => [
            'kind' => 'area', 'codes' => ['FLL', 'POM', 'DEB'], 'region' => 'broward', 'group' => 'fort-lauderdale-diving-community',
            'name' => 'Fort Lauderdale',
            'h1' => 'Fort Lauderdale dive trips',
            'title' => 'Fort Lauderdale Dive Trips This Week | Divers Hub',
            'desc' => 'Every dive boat leaving Fort Lauderdale, Pompano Beach and Deerfield Beach in the next 7 days: departure times, wrecks and reefs, and seats left.',
            'intro' => 'Broward County has one of the densest wreck trails in Florida - the Lady Luck, the Copenhagen, the Hydro Atlantic and dozens of artificial reefs - and three lines of reef a short boat ride off the beach. These are the next seven days of departures from every Fort Lauderdale, Pompano Beach and Deerfield Beach operator we follow, with the site each boat is planning and how many seats are left.',
        ],
        'palm-beach' => [
            'kind' => 'area', 'codes' => ['JUP', 'WPB', 'BOY'], 'region' => 'palm',
            'name' => 'Palm Beach & Jupiter',
            'h1' => 'Palm Beach and Jupiter dive trips',
            'title' => 'Palm Beach & Jupiter Dive Trips This Week | Divers Hub',
            'desc' => 'Dive boats leaving Jupiter, West Palm Beach and Boynton Beach in the next 7 days: drift dives, shark dives and wrecks, with departure times and seats.',
            'intro' => 'North of Boynton the Gulf Stream runs close to shore, so most Palm Beach diving is drift diving: steady current, warm clear water and big animals - goliath groupers in late summer, lemon sharks in winter, and the shark dives Jupiter is known for. These are the next seven days of trips from every Jupiter, West Palm Beach and Boynton Beach operator we follow.',
        ],
        'miami' => [
            'kind' => 'area', 'codes' => ['MIA'], 'region' => 'miami',
            'name' => 'Miami',
            'h1' => 'Miami dive trips',
            'title' => 'Miami Dive Trips This Week | Divers Hub',
            'desc' => 'Dive boats leaving Miami and Miami Beach in the next 7 days: Neptune Memorial Reef, the Miami wreck trail and Biscayne reefs, with times and seats.',
            'intro' => 'Miami diving runs from the Neptune Memorial Reef and the wrecks off Miami Beach to the shallow reefs of Biscayne National Park. These are the next seven days of departures from every Miami operator we follow.',
        ],
        'key-largo' => [
            'kind' => 'area', 'codes' => ['KLA', 'ISM'], 'region' => 'keys',
            'name' => 'Key Largo & Islamorada',
            'h1' => 'Key Largo and Islamorada dive trips',
            'title' => 'Key Largo & Islamorada Dive Trips This Week | Divers Hub',
            'desc' => 'Dive boats leaving Key Largo and Islamorada in the next 7 days: the Spiegel Grove, Christ of the Abyss and the Upper Keys reefs, with times and seats.',
            'intro' => 'The Upper Keys are the most-dived stretch of the Florida reef: shallow, protected reefs in John Pennekamp and the Florida Keys National Marine Sanctuary, plus the Spiegel Grove and the Duane and Bibb for deeper wreck dives. These are the next seven days of trips from every Key Largo and Islamorada operator we follow.',
        ],
        'marathon' => [
            'kind' => 'area', 'codes' => ['MAR'], 'region' => 'keys',
            'name' => 'Marathon',
            'h1' => 'Marathon dive trips',
            'title' => 'Marathon & Middle Keys Dive Trips This Week | Divers Hub',
            'desc' => 'Dive boats leaving Marathon in the next 7 days: Sombrero Reef, the Thunderbolt wreck and the Middle Keys reefs, with departure times and seats.',
            'intro' => 'Marathon sits in the middle of the Keys, with Sombrero Reef, Coffins Patch and the Thunderbolt wreck within a short run. These are the next seven days of trips from every Marathon operator we follow.',
        ],
        'key-west' => [
            'kind' => 'area', 'codes' => ['KWE'], 'region' => 'keys',
            'name' => 'Key West',
            'h1' => 'Key West dive trips',
            'title' => 'Key West Dive Trips This Week | Divers Hub',
            'desc' => 'Dive boats leaving Key West in the next 7 days: the Vandenberg, Sand Key and the Lower Keys reefs, with departure times and seats left.',
            'intro' => 'Key West diving means the USNS Vandenberg, one of the largest artificial reefs in the world, and the reefs of the Lower Keys from Sand Key to Western Sambo. These are the next seven days of trips from every Key West operator we follow.',
        ],
        // No Treasure Coast page: Stuart Scuba is the only operator there and
        // none of its trips reached the schedule in 90 days (2026-10-25), so
        // the page would be empty. Add it back with codes STU/PSL when they do.
        'shark-dives' => [
            'kind' => 'theme', 'type' => 'shark',
            'name' => 'Shark dives',
            'h1' => 'Shark dive trips in South Florida',
            'title' => 'Shark Dive Trips in Jupiter & South Florida | Divers Hub',
            'desc' => 'Every shark dive leaving South Florida in the next 7 days, from Jupiter to the Keys: operator, departure time, the site and how many seats are left.',
            'intro' => 'Jupiter is one of the best places in the United States to dive with sharks: lemon sharks gather on the wrecks in winter, and bull, tiger and sandbar sharks are seen year round. These are the shark dives every operator we follow has scheduled for the next seven days. Our guide to shark diving in Jupiter covers the sites and the rules.',
            'guide' => 'shark-diving-in-jupiter-florida',
        ],
        'wreck-dives' => [
            'kind' => 'theme', 'type' => 'wreck',
            'name' => 'Wreck dives',
            'h1' => 'Wreck dive trips in South Florida',
            'title' => 'Wreck Dive Trips in South Florida This Week | Divers Hub',
            'desc' => 'Every wreck dive leaving South Florida and the Keys in the next 7 days: which wreck each boat is planning, depth, departure time and seats left.',
            'intro' => 'South Florida has hundreds of wrecks and artificial reefs, from the Lady Luck and the Hydro Atlantic off Broward to the Spiegel Grove and the Vandenberg in the Keys. These are the trips every operator we follow has scheduled to a wreck in the next seven days; each card names the wreck, with its depth and history one tap away in our wreck wiki.',
            'guide' => 'top-5-wrecks-fort-lauderdale',
        ],
        'lobster-dives' => [
            'kind' => 'theme', 'type' => 'lobster',
            'name' => 'Lobster dives',
            'h1' => 'Lobster dive trips in South Florida',
            'title' => 'Lobster Dive Trips in South Florida | Divers Hub',
            'desc' => 'Every lobster dive leaving South Florida and the Keys in the next 7 days. Regular season runs August 6 to March 31; check bag limits before you go.',
            'intro' => "Florida's regular spiny lobster season runs from August 6 to March 31, with the two-day sport season at the end of July. These are the lobster trips every operator we follow has scheduled for the next seven days. Our lobster season guide covers bag limits, gauges and the rules.",
            'guide' => 'lobster-season-2026',
        ],
    ];

    /** The area page slug for a weather-location code (an operator's home port), or null. */
    public static function areaForCode(?string $code): ?string
    {
        $code = strtoupper(trim((string) $code));
        foreach (self::PAGES as $slug => $p) {
            if ($p['kind'] === 'area' && in_array($code, $p['codes'], true)) return $slug;
        }
        return null;
    }

    public function index()
    {
        $counts = [];
        foreach (array_keys(self::PAGES) as $slug) {
            $counts[$slug] = $this->data($slug)['count'];
        }
        $SEO = [
            'title' => 'South Florida Dive Trips by Area and Type | Divers Hub',
            'desc' => 'Dive boat schedules for Fort Lauderdale, Palm Beach and Jupiter, Miami, Key Largo, Marathon and Key West, plus shark, wreck and lobster dives.',
            'canonical' => route('DiveTrips'),
        ];
        return view('pages.DiveTrips.Index', ['pages' => self::PAGES, 'counts' => $counts, 'SEO' => $SEO]);
    }

    public function show(string $slug)
    {
        abort_unless(isset(self::PAGES[$slug]), 404);
        $page = self::PAGES[$slug];
        $data = $this->data($slug);

        $SEO = [
            'title' => $page['title'],
            'desc' => $page['desc'],
            'canonical' => route('DiveTrips.show', $slug),
        ];
        $fullBoard = route('Trips') . '?' . http_build_query(['range' => '7d'] + ($page['kind'] === 'area' ? ['region' => $page['region']] : ['type' => $page['type']]));
        $guide = !empty($page['guide']) ? \App\Models\Post::published()->where('slug', $page['guide'])->first(['title', 'slug']) : null;

        return view('pages.DiveTrips.Show', [
            'slug' => $slug,
            'page' => $page,
            'days' => $data['days'],
            'count' => $data['count'],
            'operators' => $data['operators'],
            'fullBoard' => $fullBoard,
            'guide' => $guide,
            'others' => array_diff_key(self::PAGES, [$slug => true]),
            'SEO' => $SEO,
        ]);
    }

    /**
     * The next DAYS days for one page: date => board (TripBoard::build()
     * shape, groups trimmed to the page's area or theme), the trip count,
     * and the operators running them.
     *
     * @return array{days: array<string, array>, count: int, operators: array<int, array{name: string, url: ?string, n: int}>}
     */
    private function data(string $slug): array
    {
        return Cache::remember('divetrips.' . $slug . '.' . Carbon::today()->toDateString(), 600, function () use ($slug) {
            $page = self::PAGES[$slug];
            $from = Carbon::today()->toDateString();
            $to = Carbon::today()->addDays(self::DAYS - 1)->toDateString();

            $trips = $this->trips($from, $to);
            $locations = Cache::remember('weatherlocations.all', 300, fn () => WeatherLocation::all());
            $operators = Cache::remember('operators.minimal', 300, fn () => Operator::select('id', 'location', 'phone')->get()->keyBy('id')->all());
            $weather = Weatherday::whereBetween('date', [$from, $to])->get()->groupBy('date');
            $siteCovers = Cache::remember('sites.covers', 300, fn () => \App\Models\Photo::coversFor($this->sites()->keys()->all()));
            $filters = ['region' => null, 'level' => null, 'type' => $page['kind'] === 'theme' ? $page['type'] : null, 'seats' => false, 'ops' => []];

            $byDate = $trips->groupBy('date');
            $days = [];
            $count = 0;
            $ops = [];
            for ($d = Carbon::parse($from); $d->lte(Carbon::parse($to)); $d->addDay()) {
                $key = $d->toDateString();
                $board = TripBoard::build($byDate->get($key, collect()), $weather->get($key, collect()), $locations, $filters, $operators, [], [], [], $siteCovers);
                if ($page['kind'] === 'area') {
                    foreach ($board['groups'] as $gi => $g) {
                        $board['groups'][$gi]['trips'] = array_values(array_filter($g['trips'], fn ($c) => in_array($c['locationCode'], $page['codes'], true)));
                        $board['groups'][$gi]['locations'] = array_values(array_filter($g['locations'], fn ($l) => in_array($l['code'], $page['codes'], true)));
                    }
                    $board['groups'] = array_values(array_filter($board['groups'], fn ($g) => count($g['trips']) > 0));
                }
                $n = array_sum(array_map(fn ($g) => count($g['trips']), $board['groups']));
                $board['shown'] = $n;
                $count += $n;
                foreach ($board['groups'] as $g) {
                    foreach ($g['trips'] as $c) {
                        if (!$c['operatorId']) continue;
                        $ops[$c['operatorId']] ??= ['name' => $c['operatorName'], 'url' => $c['operatorUrl'], 'n' => 0];
                        $ops[$c['operatorId']]['n']++;
                    }
                }
                $days[$key] = $board;
            }
            uasort($ops, fn ($a, $b) => $b['n'] <=> $a['n'] ?: strcasecmp($a['name'], $b['name']));

            return ['days' => $days, 'count' => $count, 'operators' => array_values($ops)];
        });
    }

    private function sites()
    {
        return Cache::remember('sites.minimal', 300, fn () => Site::select('id', 'name', 'type', 'slug', 'maxDepth', 'level')->get()->keyBy('id'));
    }

    /**
     * Trips in the range with what TripBoard::card() reads - the site rows
     * and the level - the same enrichment TripsController does, minus the
     * per-viewer parts (favorites, visited, wishlist).
     */
    private function trips(string $from, string $to)
    {
        $sites = $this->sites();
        $trips = Cache::remember("trips.range.$from.$to", 120, fn () => Trip::whereBetween('date', [$from, $to])->get()->sortBy('departureTime'));
        foreach ($trips as $trip) {
            $trip->visited = 0;
            $trip->fav = 0;
            $tripSites = [];
            if ($trip->siteId !== null && $trip->siteId !== '') {
                foreach (explode(',', $trip->siteId) as $id) {
                    $s = $sites->get((int) trim($id));
                    if (!$s) continue;
                    $tripSites[] = $s;
                    if ($s->level !== null && $s->level !== '') $trip->level = (int) $s->level;
                }
            }
            if (!$tripSites) $trip->level = -1;
            $trip->site = $tripSites;
        }
        return $trips;
    }
}

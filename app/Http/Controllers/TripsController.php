<?php

namespace App\Http\Controllers;

use App\Models\Weatherday;
use App\Models\Operator;
use App\Support\TripBoard;
use Illuminate\Http\Request;


use App\Models\Trip;
use App\Models\Site;
use App\Models\User;
use App\Models\WeatherLocation;
use App\Models\VisitedSite;
use Carbon\Carbon;
use Symfony\Component\Console\Input\Input;
use Illuminate\Support\Facades\Log;

class TripsController extends Controller
{
    //


    public function show(Request $request, $date = null)
    {
        //$user = User::findorFail(auth()->user()->id);
        $user = User::find(auth()->user()->id);
        if (!$user)
            $user = User::find(5);  // Get designated for user Guest if user do
        // if we didn't receive $date, we just put today's
        if (!$date) {
            $date = Carbon::today()->toDateString();
        }

        // Trip finder (redesign): the same page serves one day (the default) or a
        // date range. ?range=weekend|nextweekend|7d|30d picks a preset, ?from=&to=
        // a custom range (capped at TripBoard::MAX_RANGE_DAYS). Day mode is from == to.
        [$from, $to, $rangeKey] = $this->resolveRange($request, $date, Carbon::today());
        $mode = $from === $to ? 'day' : 'range';
        if ($mode === 'day') {
            $date = $from;
        }

        $trips = Trip::whereBetween('date', [$from, $to])->get()->sortBy('departureTime');

        // name, type and slug are needed by the trip cards (site link, wreck chip).
        // Keyed by id: a range search enriches thousands of trips, and a scan per trip was the slow part.
        $sites = Site::select('id', 'name', 'type', 'slug', 'maxDepth', 'level')->get()->keyBy('id');
        //$trips = Trip::where('date', $date)->with(['site' => function ($query) {
        //    $query->select('id', 'maxDepth', 'level');
        //}])->get()->sortBy('departureTime');
        //Log::debug("size of sites: " . count($sites));

        $visitedSites = VisitedSite::where('userId', auth()->user()->id)->get();
        Log::debug("Visited sites:" . $visitedSites);

        foreach($trips as $i => $trip) {
            $trips[$i]->visited = 0;
            if($trip->siteId != null) {
                //Log::debug("trip->siteTd " . $trip->siteId);
                $siteIds = explode(',', $trip->siteId);
                //$relatedSites = Site::whereIn('id', $siteIds)->get();
                $relatedSites = array_filter(array_map(fn ($id) => $sites->get((int) trim($id)), $siteIds));
                //Log::debug("size of relatedSites: " . count($relatedSites));
                //$trips[$i]->site = $relatedSites;
                
                #$j=0;

                
                
                foreach($relatedSites as $relatedSite) {
                    #$trips[$i]->site[$j]->id = $relatedSite->id;
                    #$trips[$i]->site[$j]->maxDepth = $relatedSite->maxDepth;
                    #$trips[$i]->site[$j]->level = $relatedSite->level;
                    //Log::debug("Trip [" . $trips[$i]->date . " " . $trips[$i]->departureTime . " " . $trips[$i]->tripName . "[" . $trips[$i]->site[$j]->maxDepth . "]");
                    #$j++;
                    $trips[$i]->site[] = $relatedSite;

                    // check if the user has visited this site
                    if (auth()->user()->id == 5 or auth()->user()->show_visited == 0)
                        $trips[$i]->visited = 0;
                    else
                        if ($visitedSites->contains('siteId', $relatedSite->id) and $trip->siteIdStatus == "confirmed")
                            $trips[$i]->visited += 1;
                    
                    //add tag for OW and AOW
                    if($relatedSite->level == 0) {
                        $trips[$i]->tags .= " OW";
                        $trips[$i]->level = 0;
                    }
                    elseif($relatedSite->level == 1) {
                        $trips[$i]->tags .= " AOW";        
                        $trips[$i]->level = 1;
                    }
                    elseif($relatedSite->level == 2) {        
                        $trips[$i]->level = 2;
                    }
                    elseif($relatedSite->level == 3) {        
                        $trips[$i]->level = 3;
                    }
                    elseif($relatedSite->level == 4) {
                        $trips[$i]->level = 4;
                    }
                    
                }
            
            }
            else
                $trips[$i]->level = -1;

            // check if trip is a favorite

            // user prefers operator fav or location fav?
            if($user->prefersLocation) {
                $favoriteLocationsIndex = explode(',', $user->favLocations);
                $favLocationShorts = WeatherLocation::whereIn('id', $favoriteLocationsIndex)
                    ->pluck('short')
                    ->toArray();

                $favoriteLevels = explode(',', $user->showLevel);
                $showLevelLow = intval($favoriteLevels[0]);
                $showLevelHigh = intval($favoriteLevels[1]);

                if(in_array(substr($trip->tags,0 ,3),  $favLocationShorts)) {
                    Log::debug("Operator for this trip is in favorites!");

                    Log::debug("Level low: " . str($showLevelLow));
                    Log::debug("Level high: " . str($showLevelHigh));

                    // if we don't have siteIds, then we set value to -1
                    Log::debug("trip->level: " . $trip->level);
                    if(isset($trip->level))
                        if(intval($trip->level) >= $showLevelLow and intval($trip->level) <= $showLevelHigh) {
                            $trips[$i]->fav = 1;
                            $trips[$i]->tags .= " FAV";
                        }
                    // this code will set FAV if the level is not set
                    //else {
                    //    $trips[$i]->fav = 1;
                     //   $trips[$i]->tags .= " FAV";
                    //}
                }
            } else {
                $favoriteOperatorsIndex = explode(',', $user->favOperators);
                $favoriteLevels = explode(',', $user->showLevel);
                $showLevelLow = intval($favoriteLevels[0]);
                $showLevelHigh = intval($favoriteLevels[1]);

                if(in_array($trip->operatorId, $favoriteOperatorsIndex)) {
                    Log::debug("Operator for this trip is in favorites!");

                    Log::debug("Level low: " . str($showLevelLow));
                    Log::debug("Level high: " . str($showLevelHigh));

                    // if we don't have siteIds, then we set value to -1
                    Log::debug("trip->level: " . $trip->level);
                    if(intval($trip->level) >= $showLevelLow and intval($trip->level) <= $showLevelHigh) {
                        $trips[$i]->fav = 1;
                        $trips[$i]->tags .= " FAV";
                    }
                }
            }
        }
        

        $favoriteLocationsIndex = explode(',', $user->favLocations);
        Log::debug("favor locations: " . str(count($favoriteLocationsIndex)));
        $weatherLocationsNames = WeatherLocation::whereIn('id', $favoriteLocationsIndex)->get()->pluck('location')->toArray();
        Log::debug("favorite locations name : " . implode(', ', $weatherLocationsNames));
        $weathers = Weatherday::where('date', $date)->whereIn('location', $weatherLocationsNames)->get();

        // create prev and next dates
        $carbonDate = new Carbon($date);
        // Get the previous day
        $previousDay = $carbonDate->subDay()->toDateString(); // Decrements the date by one day and converts it to 'YYYY-MM-DD' format
        // Get the next day
        $nextDay = $carbonDate->addDays(2)->toDateString(); // Adds two days to the original date and converts it to 'YYYY-MM-DD' format
        
        $today = Carbon::today()->toDateString();

        if(Carbon::today()->toDateString() == $date)
            $controlNav = "disabled";
        else 
            $controlNav = "";
        

        /*Provide SEO metadata */
        $SEO = array(
            "title" => "Today scuba diving trips in Florida",
            "desc" => "A calendar for all scuba diving trips going out today in South Florida. From Stuart to key West",
            "keywords" => "diving, fort lauderdale beach diving, palm beach beach diving,dive sites,scuba diving sites,dive wrecks,dive reefs,wreck,reef",
            "canonical" => route("Trips")
        );

        /*
         * Trip board (redesign W2). The enriched $trips collection above is
         * turned into plain card arrays grouped by coast. Conditions come from
         * every location for the day, not only the user's favourites, because
         * each region header shows its own sea state. $weathers (favourites
         * only) is kept for anything else that still reads it.
         */
        $filters   = TripBoard::filtersFromRequest($request);
        $locations = WeatherLocation::all();
        $allWeather = Weatherday::whereBetween('date', [$from, $to])->get()->groupBy('date');
        $operators = Operator::select('id', 'location', 'phone')->get()->keyBy('id')->all();

        // One board per day in the range (a single day in day mode). Every date
        // is present, even with no trips, so the day strip has a cell for each.
        $byDate = $trips->groupBy('date');
        $days = [];
        for ($d = Carbon::parse($from); $d->lte(Carbon::parse($to)); $d->addDay()) {
            $key = $d->toDateString();
            $days[$key] = TripBoard::build($byDate->get($key, collect()), $allWeather->get($key, collect()), $locations, $filters, $operators);
        }
        $board = $mode === 'day' ? $days[$date] : TripBoard::merge($days);
        $presets = TripBoard::rangePresets(Carbon::today());

        // Query string to carry the active filters (not the dates) across the
        // day stepper, the preset chips and the "full board" links.
        $filterParams = array_filter($request->only(['region', 'level', 'type', 'seats']), fn ($v) => $v !== null && $v !== '');
        if ($filters['ops']) {
            $filterParams['op'] = implode(',', $filters['ops']); // one param, readable URL
        }
        $query = $filterParams ? '?' . http_build_query($filterParams) : '';

        // The member's favourite operators (users.favOperators, comma list) for the "My favorites" shortcut.
        $favOperatorIds = ($user && $user->isNotGuest() && $user->favOperators)
            ? array_values(array_filter(array_map('intval', explode(',', $user->favOperators))))
            : [];

        if ($mode === 'range') {
            // Range views are query string pages: useful, shareable, not indexed.
            $SEO['title'] = 'Scuba diving trips in Florida, ' . Carbon::parse($from)->format('M j') . ' to ' . Carbon::parse($to)->format('M j');
            $SEO['robots'] = 'noindex, follow';
        }

        return view('pages.Trips', compact('board', 'days', 'mode', 'from', 'to', 'rangeKey', 'presets', 'filterParams', 'favOperatorIds',
            'date', 'today', 'previousDay', 'nextDay', 'controlNav', 'user', 'SEO', 'query'));
        //return view('pages.Trips', compact('trips', 'weathers', 'today', 'previousDay', 'nextDay', 'controlNav'));

    }

    /**
     * Work out the dates the finder shows.
     *
     * @return array{0: string, 1: string, 2: ?string} [from, to, preset key or null]
     */
    private function resolveRange(Request $request, string $date, Carbon $today): array
    {
        $presets = TripBoard::rangePresets($today);
        $key = $request->query('range');
        if (is_string($key) && isset($presets[$key])) {
            return [$presets[$key]['from'], $presets[$key]['to'], $key];
        }
        $valid = fn ($v) => is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) && strtotime($v) !== false;
        $from = $request->query('from');
        $to   = $request->query('to');
        if ($valid($from) || $valid($to)) {
            $f = Carbon::parse($valid($from) ? $from : $to)->startOfDay();
            $t = Carbon::parse($valid($to) ? $to : $from)->startOfDay();
            if ($f->lt($today)) {
                $f = $today->copy();
            }
            if ($t->lt($f)) {
                $t = $f->copy();
            }
            if ($f->diffInDays($t) > TripBoard::MAX_RANGE_DAYS - 1) {
                $t = $f->copy()->addDays(TripBoard::MAX_RANGE_DAYS - 1);
            }
            return [$f->toDateString(), $t->toDateString(), null];
        }
        return [$date, $date, null];
    }
}

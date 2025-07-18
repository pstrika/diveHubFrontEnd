<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Trip;
use App\Models\Operator;
use App\Models\WeatherLocation;
use App\Models\WeatherDay;
use App\Models\WishedSite;
use App\Models\User;
use App\Models\Site;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MyDashboardController extends Controller
{
    //
    public function showDashboard() {
        $user = User::findorFail(auth()->user()->id);
        // if we didn't receive $date, we just put today's
    
        // Get calendar in timeline --------------------
        $date = Carbon::today()->toDateString();

        $month = date('m', strtotime($date)); // Extract the month from the 'date' variable
        $year = date('Y', strtotime($date)); // Extract the year from the 'date' variable
        
        $dateFrom = Carbon::parse($date)->format('Y-m-d');
        $dateTo = Carbon::parse($date)->addWeek(6)->format('Y-m-d');
        $events = Event::whereDate('date', '>=', Carbon::today())
            ->where('userId', auth()->user()->id)
            ->take(10)->get()->sortBy("date");

        Log::debug("Got " . str(count($events)) . " event for user " . $user->name);
        $trips = [];
        foreach($events as $event) {
            $trip = Trip::tripInEvent($event);
            if($trip) {
                $trip->booked = $event->booked;
                $trip->eventId = $event->id;
                // need to get the link to the waiver
                $operator = Operator::where('id', $trip->operatorId)->first();
                $trip->waiver = $operator->waiverLink;
                $trips[] = $trip;
            }
        }
        //---------------------------

        //-- Get favorite dives for the weenend ---------------------
        $favoriteLocationsIndex = explode(',', $user->favLocations);
        $favLocationShorts = WeatherLocation::whereIn('id', $favoriteLocationsIndex)
            ->pluck('short')
            ->toArray();

        // NEED TO OPTIMIZE THIS QUERY TO GET ONLY POTENTIAL FAVs BASED ON LOCATION AND OPERATORS
        $favTrips =  Trip::whereBetween('date', [
            now()->startOfWeek()->addDays(5), // Saturday
            now()->startOfWeek()->addDays(6), // Sunday
            ])
            ->get()
            ->sortBy('departureTime')
            ->sortBy('date');
            
        Log::debug("Size of fav trips is" . count($favTrips));
        $sites = collect(Site::select('id', 'maxDepth', 'level')->get());

        foreach($favTrips as $i => $trip) {
            if($trip->siteId != null) {
                Log::debug("trip->siteId " . $trip->siteId . "tripId = " .$trip->id);
                $siteIds = explode(',', $trip->siteId);
                //$relatedSites = Site::whereIn('id', $siteIds)->get();
                $relatedSites = $sites->whereIn('id', $siteIds)->all();
                Log::debug("size of relatedSites: " . count($relatedSites));
                //$trips[$i]->site = $relatedSites;
                
                $j=0;
                
                foreach($relatedSites as $relatedSite) {
                    Log::debug("i: " . $i . "j: " . $j);
                    // COMENTED next 3 lines on 12/21/2024 (was giving error: Undefined array key 0). Also discovered
                    // that for Molases Reef, operator KL Dive Center sometimes uses Molases Shallow Reef clashing with Keys Shallow reef
                    //$favTrips[$i]->site[$j]->id = $relatedSite->id;
                    //$favTrips[$i]->site[$j]->maxDepth = $relatedSite->maxDepth;
                    //$favTrips[$i]->site[$j]->level = $relatedSite->level;
                    //Log::debug("Trip [" . $trips[$i]->date . " " . $trips[$i]->departureTime . " " . $trips[$i]->tripName . "[" . $trips[$i]->site[$j]->maxDepth . "]");
                    $j++;
                    $favTrips[$i]->site[] = $relatedSite;
                    
                    //add tag for OW and AOW
                    if($relatedSite->level == 0) {
                        $favTrips[$i]->tags .= " OW";
                        $favTrips[$i]->level = 0;
                    }
                    elseif($relatedSite->level == 1) {
                        $favTrips[$i]->tags .= " AOW";        
                        $favTrips[$i]->level = 1;
                    }
                    elseif($relatedSite->level == 2) {        
                        $favTrips[$i]->level = 2;
                    }
                    elseif($relatedSite->level == 3) {        
                        $favTrips[$i]->level = 3;
                    }
                    elseif($relatedSite->level == 4) {
                        $favTrips[$i]->level = 4;
                    }
                    
                }
            
            }
            else
                $favTrips[$i]->level = -1;

            // check if trip is a favorite

            // user prefers operator fav or location fav?
            if($user->prefersLocation) {
                //$favoriteLocationsIndex = explode(',', $user->favLocations);
                //$favLocationShorts = WeatherLocation::whereIn('id', $favoriteLocationsIndex)
                //    ->pluck('short')
                //    ->toArray();

                $favoriteLevels = explode(',', $user->showLevel);
                $showLevelLow = intval($favoriteLevels[0]);
                $showLevelHigh = intval($favoriteLevels[1]);

                if(in_array(substr($trip->tags,0 ,3),  $favLocationShorts)) {
                    //Log::debug("Operator for this trip is in favorites!");

                    //Log::debug("Level low: " . str($showLevelLow));
                    //Log::debug("Level high: " . str($showLevelHigh));

                    // if we don't have siteIds, then we set value to -1
                    //Log::debug("trip->level: " . $trip->level);
                    if(isset($trip->level))
                        if(intval($trip->level) >= $showLevelLow and intval($trip->level) <= $showLevelHigh) {
                            $favTrips[$i]->fav = 1;
                            $favTrips[$i]->tags .= " FAV";
                        }
                }
            } else {
                $favoriteOperatorsIndex = explode(',', $user->favOperators);
                $favoriteLevels = explode(',', $user->showLevel);
                $showLevelLow = intval($favoriteLevels[0]);
                $showLevelHigh = intval($favoriteLevels[1]);

                if(in_array($trip->operatorId, $favoriteOperatorsIndex)) {
                    //Log::debug("Operator for this trip is in favorites!");

                    //Log::debug("Level low: " . str($showLevelLow));
                    //Log::debug("Level high: " . str($showLevelHigh));

                    // if we don't have siteIds, then we set value to -1
                    //Log::debug("trip->level: " . $trip->level);
                    if(intval($trip->level) >= $showLevelLow and intval($trip->level) <= $showLevelHigh) {
                        $favTrips[$i]->fav = 1;
                        $favTrips[$i]->tags .= " FAV";
                    }
                }
            }
        }
        

        $favoriteLocationsIndex = explode(',', $user->favLocations);
        Log::debug("favor locations: " . str(count($favoriteLocationsIndex)));
        $weatherLocationsNames = WeatherLocation::whereIn('id', $favoriteLocationsIndex)->get()->pluck('location')->toArray();
        Log::debug("favorite locations name : " . implode(', ', $weatherLocationsNames));
        $weathers = Weatherday::where('date', $date)->whereIn('location', $weatherLocationsNames)->get();


        // work the wishlist
        $wished = WishedSite::where('userId', $user->id)->with('site')->get();
        Log::debug("Got wished sites: " . count($wished));

        
        foreach($wished as $i => $wish) {
            Log::debug("Site: " . $wish->site->name);
            $wishedTrips = Trip::where('siteId', $wish->siteId)
                ->where('siteIdStatus', 'confirmed')
                ->whereDate('date', '>=', Carbon::today())
                ->get()->sortBy('date');
            Log::debug("Count of trips for this site: " . count($wishedTrips));
            if(count($wishedTrips) > 0) {
                $wished[$i]->operator = $wishedTrips[0]->operatorName;
                $wished[$i]->date = $wishedTrips[0]->date;
                $wished[$i]->time = $wishedTrips[0]->departureTime;
                $wished[$i]->linkToBook = $wishedTrips[0]->linkToBook;
                $wished[$i]->tripName = $wishedTrips[0]->tripName;
                $wished[$i]->tripFreeSpots = $wishedTrips[0]->tripFreeSpots;
                $wished[$i]->operatorId = $wishedTrips[0]->operatorId;
                $wished[$i]->tripId = $wishedTrips[0]->id;

                Log::debug("Trip on: " . $wished[$i]->date . " " . $wished[$i]->time . " " . $wished[$i]->operator);
              
            }
        }

        // Get favorite operators calendars
        $favoriteOperatorsIndex = array_filter(explode(',', $user->favOperators), fn($val) => trim($val) !== '');
        Log::debug($favoriteOperatorsIndex);
        if(!empty($favoriteOperatorsIndex)) {
            $favOperators = Operator::select('id', 'operatorName', 'logoUrl')
                ->whereIn('id', $favoriteOperatorsIndex)
                ->get();
            Log::debug($favOperators);
            
        } else
            $favOperators = [];

        $favCalendars = Trip::whereIn('operatorId', $favoriteOperatorsIndex)
            ->whereDate('date', '>=', Carbon::today())
            ->get()->sortBy("date");
            //Log::debug($favCalendars);


        return view('pages.Dashboard', compact('trips', 'favTrips', 'weathers', 'wished', 'favOperators', 'favCalendars'));
    }
}

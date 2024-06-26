<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Trip;
use App\Models\Operator;
use App\Models\WeatherLocation;
use App\Models\WeatherDay;
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
            

        $sites = collect(Site::select('id', 'maxDepth', 'level')->get());

        foreach($favTrips as $i => $trip) {
            if($trip->siteId != null) {
                //Log::debug("trip->siteTd " . $trip->siteId);
                $siteIds = explode(',', $trip->siteId);
                //$relatedSites = Site::whereIn('id', $siteIds)->get();
                $relatedSites = $sites->whereIn('id', $siteIds)->all();
                //Log::debug("size of relatedSites: " . count($relatedSites));
                //$trips[$i]->site = $relatedSites;
                
                $j=0;
                
                foreach($relatedSites as $relatedSite) {
                    $favTrips[$i]->site[$j]->id = $relatedSite->id;
                    $favTrips[$i]->site[$j]->maxDepth = $relatedSite->maxDepth;
                    $favTrips[$i]->site[$j]->level = $relatedSite->level;
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


        return view('pages.Dashboard', compact('trips', 'favTrips', 'weathers'));
    }
}

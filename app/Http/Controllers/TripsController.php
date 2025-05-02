<?php

namespace App\Http\Controllers;

use App\Models\Weatherday;
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


    public function show($date = null)
    {
        //$user = User::findorFail(auth()->user()->id);
        $user = User::find(auth()->user()->id);
        if (!$user)
            $user = User::find(5);  // Get designated for user Guest if user do
        // if we didn't receive $date, we just put today's
        if (!$date) {
            $date = Carbon::today()->toDateString();
        }
        

        $trips = Trip::where('date', $date)->get()->sortBy('departureTime');

        $sites = collect(Site::select('id', 'maxDepth', 'level')->get());
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
                $relatedSites = $sites->whereIn('id', $siteIds)->all();
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
        

        return view('pages.Trips', compact('trips', 'weathers', 'today', 'previousDay', 'nextDay', 'controlNav', 'user'));
        //return view('pages.Trips', compact('trips', 'weathers', 'today', 'previousDay', 'nextDay', 'controlNav'));

    }

    
}

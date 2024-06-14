<?php

namespace App\Http\Controllers;

use App\Models\Weatherday;
use Illuminate\Http\Request;


use App\Models\Trip;
use App\Models\Site;
use App\Models\User;
use App\Models\WeatherLocation;
use Carbon\Carbon;
use Symfony\Component\Console\Input\Input;
use Illuminate\Support\Facades\Log;

class TripsController extends Controller
{
    //


    public function show($date = null)
    {
        $user = User::findorFail(auth()->user()->id);
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

        foreach($trips as $i => $trip) {
            if($trip->siteId != null) {
                //Log::debug("trip->siteTd " . $trip->siteId);
                $siteIds = explode(',', $trip->siteId);
                //$relatedSites = Site::whereIn('id', $siteIds)->get();
                $relatedSites = $sites->whereIn('id', $siteIds)->all();
                //Log::debug("size of relatedSites: " . count($relatedSites));
                //$trips[$i]->site = $relatedSites;
                
                $j=0;
                
                foreach($relatedSites as $relatedSite) {
                    $trips[$i]->site[$j]->id = $relatedSite->id;
                    $trips[$i]->site[$j]->maxDepth = $relatedSite->maxDepth;
                    $trips[$i]->site[$j]->level = $relatedSite->level;
                    //Log::debug("Trip [" . $trips[$i]->date . " " . $trips[$i]->departureTime . " " . $trips[$i]->tripName . "[" . $trips[$i]->site[$j]->maxDepth . "]");
                    $j++;
                    $trips[$i]->site[] = $relatedSite;  
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

    }
}

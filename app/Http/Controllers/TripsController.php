<?php

namespace App\Http\Controllers;

use App\Models\Weatherday;
use Illuminate\Http\Request;


use App\Models\Trip;
use App\Models\Site;
use Carbon\Carbon;
use Symfony\Component\Console\Input\Input;
use Illuminate\Support\Facades\Log;

class TripsController extends Controller
{
    //


    public function show($date = null)
    {
        // if we didn't receive $date, we just put today's
        if (!$date) {
            $date = Carbon::today()->toDateString();
        }
        

        $trips = Trip::where('date', $date)->get()->sortBy('departureTime');

        $sites = collect(Site::select('id', 'maxDepth', 'level')->get());
        //$trips = Trip::where('date', $date)->with(['site' => function ($query) {
        //    $query->select('id', 'maxDepth', 'level');
        //}])->get()->sortBy('departureTime');
        Log::debug("size of sites: " . $sites);

        foreach($trips as $i => $trip) {
            if($trip->siteId != null) {
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
                    
                }
            
            }
        }
        


        $weathers = Weatherday::where('date', $date)->get();

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
        

        return view('pages.Trips', compact('trips', 'weathers', 'today', 'previousDay', 'nextDay', 'controlNav'));

    }
}

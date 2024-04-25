<?php

namespace App\Http\Controllers;

use App\Models\Weatherday;
use Illuminate\Http\Request;


use App\Models\Trip;
use Carbon\Carbon;
use Symfony\Component\Console\Input\Input;

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

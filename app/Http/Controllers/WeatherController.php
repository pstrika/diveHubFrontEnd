<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Weatherday;
use App\Models\WeatherLocation;

class WeatherController extends Controller
{
    public function show($location = null)
    {
        // if we didn't receive $date, we just put today's
        if (!$location)
            $location = "fort lauderdale";
        
        
        $date = Carbon::today()->toDateString();

        $weathers = Weatherday::where('location', $location)->get();

        $allLocations = WeatherLocation::all();

        return view('pages.Weather', compact('weathers', 'date', 'location', 'allLocations'));

    }

}

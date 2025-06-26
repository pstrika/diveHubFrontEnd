<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Weatherday;
use App\Models\WeatherLocation;
use Illuminate\Support\Facades\Log;

class WeatherController extends Controller
{
    public function show($location = null)
    {
        // if we didn't receive $date, we just put today's
        if (!$location)
            $location = "fort lauderdale";
        
        
        $date = Carbon::today()->toDateString();

        $weathers = Weatherday::where('location', $location)->get();

        foreach($weathers as $weather){
            Log::debug($weather->tides);
        }

        //$allLocations = WeatherLocation::all();
        $allLocations = WeatherLocation::where('country', 'US')->get();
        $currentLocation = WeatherLocation::where('location', $location)->first();
        Log::debug('current location: ' . $currentLocation);

        /*Provide SEO metadata */
        $SEO = array(
            "title" => "Marine forecast for " . $location . " - divers-hub.com",
            "desc" => "7-day marine forecast for " . $location . ". Ocean conditions, tides and more",
            "keywords" => "marine weather " . $location . ",dive,diving,scuba,florida diving,tides,florida scuba",
        );

        return view('pages.Weather', compact('weathers', 'date', 'location', 'allLocations', 'currentLocation', 'SEO'));

    }

    public function showAR($location = null)
    {
        $deco_unit = auth()->user()->deco_unit;

        // if we didn't receive $date, we just put today's
        if (!$location)
            $location = "mar del plata";
        
        
        $date = Carbon::today()->toDateString();

        $weathers = Weatherday::where('location', $location)->get();

        foreach($weathers as $weather){
            Log::debug($weather->tides);
        }

        //$allLocations = WeatherLocation::all();
        $allLocations = WeatherLocation::where('country', 'AR')->get();
        $currentLocation = WeatherLocation::where('location', $location)->first();
        Log::debug('current location: ' . $currentLocation);

        /*Provide SEO metadata */
        $SEO = array(
            "title" => "Marine forecast for " . $location . " - divers-hub.com",
            "desc" => "7-day marine forecast for " . $location . ". Ocean conditions, tides and more",
            "keywords" => "marine weather " . $location . ",dive,diving,scuba,florida diving,tides,florida scuba",
        );

        return view('pages.WeatherAR', compact('weathers', 'date', 'location', 'allLocations', 'currentLocation', 'SEO', 'deco_unit'));

    }

    public function showARImperial($location = null)
    {
        $deco_unit = 0;

        // if we didn't receive $date, we just put today's
        if (!$location)
            $location = "mar del plata";
        
        
        $date = Carbon::today()->toDateString();

        $weathers = Weatherday::where('location', $location)->get();

        foreach($weathers as $weather){
            Log::debug($weather->tides);
        }

        //$allLocations = WeatherLocation::all();
        $allLocations = WeatherLocation::where('country', 'AR')->get();
        $currentLocation = WeatherLocation::where('location', $location)->first();
        Log::debug('current location: ' . $currentLocation);

        /*Provide SEO metadata */
        $SEO = array(
            "title" => "Marine forecast for " . $location . " - divers-hub.com",
            "desc" => "7-day marine forecast for " . $location . ". Ocean conditions, tides and more",
            "keywords" => "marine weather " . $location . ",dive,diving,scuba,florida diving,tides,florida scuba",
        );

        return view('pages.WeatherAR', compact('weathers', 'date', 'location', 'allLocations', 'currentLocation', 'SEO', 'deco_unit'));

    }

    public function showARMetric($location = null)
    {
        $deco_unit = 1;

        // if we didn't receive $date, we just put today's
        if (!$location)
            $location = "mar del plata";
        
        
        $date = Carbon::today()->toDateString();

        $weathers = Weatherday::where('location', $location)->get();

        foreach($weathers as $weather){
            Log::debug($weather->tides);
        }

        //$allLocations = WeatherLocation::all();
        $allLocations = WeatherLocation::where('country', 'AR')->get();
        $currentLocation = WeatherLocation::where('location', $location)->first();
        Log::debug('current location: ' . $currentLocation);

        /*Provide SEO metadata */
        $SEO = array(
            "title" => "Marine forecast for " . $location . " - divers-hub.com",
            "desc" => "7-day marine forecast for " . $location . ". Ocean conditions, tides and more",
            "keywords" => "marine weather " . $location . ",dive,diving,scuba,florida diving,tides,florida scuba",
        );

        return view('pages.WeatherAR', compact('weathers', 'date', 'location', 'allLocations', 'currentLocation', 'SEO', 'deco_unit'));

    }

}

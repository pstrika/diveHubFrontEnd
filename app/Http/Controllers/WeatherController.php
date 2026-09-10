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
        // No location in the URL: a member lands on the first of their favourite
        // places (users.favLocations is a list of weatherlocations ids, the same
        // list the dashboard and the trip finder use), everyone else on Fort
        // Lauderdale. Weather is a bottom tab now, so this is the page a diver
        // opens the night before a trip; it should open on their water.
        if (!$location) {
            $location = "fort lauderdale";
            $user = auth()->user();
            if ($user && $user->isNotGuest()) {
                $firstFav = (int) current(explode(',', (string) $user->favLocations));
                $fav = $firstFav ? WeatherLocation::find($firstFav) : null;
                if ($fav && $fav->country === 'US') {
                    $location = $fav->location;
                }
            }
        }

        $date = Carbon::today()->toDateString();

        //$allLocations = WeatherLocation::all();
        $allLocations = WeatherLocation::where('country', 'US')->get();
        $currentLocation = WeatherLocation::where('location', $location)->first();

        // An unknown location in the URL (a typo, an old link) used to be a 500
        // because the view reads the location's buoy. Fall back to the default.
        if (!$currentLocation) {
            $location = "fort lauderdale";
            $currentLocation = WeatherLocation::where('location', $location)->first();
        }

        $weathers = Weatherday::where('location', $location)->get();

        /*Provide SEO metadata */
        $SEO = array(
            "title" => "Marine forecast for " . $location,
            "desc" => "7-day marine forecast for " . $location . ". Ocean conditions, tides and more",
            "keywords" => "marine weather " . $location . ",dive,diving,scuba,florida diving,tides,florida scuba",
            "canonical" => route("Weather") . "/" . rawurlencode($location),
        );

        return view('pages.Weather', compact('weathers', 'date', 'location', 'allLocations', 'currentLocation', 'SEO'));

    }

    public function showAR($location = null)
    {
        $deco_unit = auth()->check() ? auth()->user()->deco_unit : 0;

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
            "keywords" => "marine weather " . $location . ",dive,diving,scuba,argentina diving,tides,argentina scuba,buceo,buceo argentina,buceo mar del plata,buceo las grutas,buceo ushuaia,buceo puerto madryn",
            "canonical" => route("WeatherAR") . "/" . rawurlencode($location),
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
            "canonical" => route("WeatherAR") . "/" . rawurlencode($location),
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
            "canonical" => route("WeatherAR") . "/" . rawurlencode($location),
        );

        return view('pages.WeatherAR', compact('weathers', 'date', 'location', 'allLocations', 'currentLocation', 'SEO', 'deco_unit'));

    }

}

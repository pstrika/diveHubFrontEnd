<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Weatherday;
use App\Models\Trip;
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

        // Every location, Argentina included: this page replaced the separate
        // Argentina forecast in release 10.
        $allLocations = WeatherLocation::orderBy('country')->orderBy('id')->get();
        $currentLocation = WeatherLocation::where('location', $location)->first();

        // An unknown location in the URL (a typo, an old link) used to be a 500
        // because the view reads the location's buoy. Fall back to the default.
        if (!$currentLocation) {
            $location = "fort lauderdale";
            $currentLocation = WeatherLocation::where('location', $location)->first();
        }

        $weathers = Weatherday::where('location', $location)->get();

        // ------------------------------------------------------------------
        // What the page leads with (2026-09-10). Pablo's crawler already scores
        // every half day from Poor to Perfect; the old page showed those four
        // words in one small table two screens down and never showed the swell
        // period or the wind direction at all, which are the two numbers that
        // decide whether a day is a nice roll or unpleasant chop.
        // ------------------------------------------------------------------
        // Today forward only. The table keeps a few days of history and nobody
        // plans a dive for last Sunday.
        $days = $weathers->filter(fn ($d) => $d->date >= Carbon::today()->toDateString())
            ->sortBy('date')->values();
        if ($days->isEmpty()) {
            $days = $weathers->sortBy('date')->values();
        }
        $today = $days->first();

        // Every US location for today, so "where is it good" is one glance
        // instead of thirteen page loads. One query, ordered north to south.
        $coastOrder = ['port st lucie', 'stuart', 'jupiter', 'west palm beach', 'boynton beach',
                       'deerfield beach', 'pompano beach', 'fort lauderdale', 'miami beach',
                       'key largo', 'islamorada', 'marathon', 'key west'];
        $coastToday = collect();
        if ($today) {
            $rows = Weatherday::where('date', $today->date)
                ->whereIn('location', $coastOrder)->get()->keyBy('location');
            foreach ($coastOrder as $name) {
                if ($rows->has($name)) {
                    $coastToday->push($rows->get($name));
                }
            }
        }

        // Boats leaving from this stretch of coast, per day. operators.location
        // holds the weather location's short code (FLL, POM, ...), so the two
        // data sets join on that. Conditions plus boats on one screen is the
        // thing only we can show; a weather app cannot.
        $boatsByDate = [];
        if ($currentLocation && $days->isNotEmpty()) {
            $operatorIds = \App\Models\Operator::where('location', $currentLocation->short)->pluck('id');
            if ($operatorIds->isNotEmpty()) {
                $boatsByDate = Trip::whereIn('operatorId', $operatorIds)
                    ->whereBetween('date', [$days->first()->date, $days->last()->date])
                    ->selectRaw('date, COUNT(*) as c')->groupBy('date')
                    ->pluck('c', 'date')->all();
            }
        }

        /*Provide SEO metadata */
        $SEO = array(
            "title" => "Marine forecast for " . $location,
            "desc" => "7-day marine forecast for " . $location . ". Ocean conditions, tides and more",
            "keywords" => "marine weather " . $location . ",dive,diving,scuba,florida diving,tides,florida scuba",
            "canonical" => route("Weather") . "/" . rawurlencode($location),
        );

        return view('pages.Weather', compact('weathers', 'date', 'location', 'allLocations', 'currentLocation', 'SEO', 'days', 'today', 'coastToday', 'boatsByDate'));

    }




}

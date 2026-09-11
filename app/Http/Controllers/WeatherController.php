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
    /**
     * Registration seeds favLocations with '3, 5, 8' for everybody
     * (RegisterController and GoogleController), and 3 is key largo. So "the
     * first of your favourite places" opened the forecast on Key Largo for
     * every member who had never touched their profile, which is not a
     * preference, it is a default nobody chose.
     *
     * Treat that exact seed as "no preference" and fall back to Fort
     * Lauderdale. The real fix is to stop writing a fake preference at sign up;
     * that touches the welcome wizard's "needs setup" test too, so it is a
     * roadmap item rather than a change on release day.
     */
    private static function pickedTheirOwnPlaces($user): bool
    {
        $ids = array_filter(array_map('trim', explode(',', (string) $user->favLocations)), 'strlen');
        if (!$ids) {
            return false;
        }
        sort($ids);
        return $ids !== ['3', '5', '8'];
    }

    public function show(Request $request, $location = null)
    {
        // No location in the URL: a member lands on the first of their favourite
        // places (users.favLocations is a list of weatherlocations ids, the same
        // list the dashboard and the trip finder use), everyone else on Fort
        // Lauderdale. Weather is a bottom tab now, so this is the page a diver
        // opens the night before a trip; it should open on their water.
        if (!$location) {
            $location = "fort lauderdale";
            $user = auth()->user();
            if ($user && $user->isNotGuest() && self::pickedTheirOwnPlaces($user)) {
                $firstFav = (int) current(explode(',', (string) $user->favLocations));
                $fav = $firstFav ? WeatherLocation::find($firstFav) : null;
                if ($fav) {
                    $location = $fav->location;
                }
            }
        }

        $date = Carbon::today()->toDateString();

        // Florida only (2026-09-11): the separate Argentina forecast was
        // retired in release 10, but its locations stayed queryable through
        // this page. They're gone from here now too - filtered at the
        // source rather than deleted, so the crawler data isn't touched.
        $currentLocation = WeatherLocation::where('location', $location)->where('country', 'US')->first();

        // An unknown (or now-removed, e.g. Argentina) location in the URL
        // used to be a 500 because the view reads the location's buoy.
        // Fall back to the default.
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
        // instead of thirteen page loads. Ordered by the location's real
        // latitude (weatherlocations.centerLat) rather than a hand-kept
        // list, south to north by default (Key West -> Port St Lucie) with
        // a toggle (?coastSort=ntos) for the other direction.
        $coastSort = $request->query('coastSort') === 'ntos' ? 'ntos' : 'ston';
        $coastLocations = WeatherLocation::where('country', 'US')
            ->orderBy('centerLat', $coastSort === 'ntos' ? 'desc' : 'asc')
            ->get(['id', 'location']);

        // Favorites first, then everything else - each half keeping the same
        // N/S order as the whole list, just split in two (2026-09-11).
        //
        // Deliberately NOT gated on pickedTheirOwnPlaces() the way the
        // default-location pick above is: that rule exists because silently
        // landing someone on a location they never chose (Key Largo, from
        // the registration seed) is actively misleading. Grouping is the
        // opposite - it is a visible, reversible "here is what's marked" list
        // that is still correct even for a member who never touched the
        // setting and is still sitting on the seed.
        $viewer = auth()->user();
        $favLocationIds = ($viewer && $viewer->isNotGuest())
            ? array_map('intval', array_filter(array_map('trim', explode(',', (string) $viewer->favLocations)), 'strlen'))
            : [];
        $favCoastNames = $coastLocations->whereIn('id', $favLocationIds)->pluck('location');
        $otherCoastNames = $coastLocations->whereNotIn('id', $favLocationIds)->pluck('location');
        $coastNamesOrdered = $favCoastNames->concat($otherCoastNames);
        $coastFavCount = $favCoastNames->count();

        $coastToday = collect();
        if ($today) {
            $rows = Weatherday::where('date', $today->date)
                ->whereIn('location', $coastNamesOrdered)->get()->keyBy('location');
            foreach ($coastNamesOrdered as $name) {
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

        return view('pages.Weather', compact('weathers', 'date', 'location', 'currentLocation', 'SEO', 'days', 'today', 'coastToday', 'coastFavCount', 'boatsByDate', 'coastSort'));

    }




}

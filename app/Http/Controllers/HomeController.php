<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Models\Site;
use App\Models\Trip;
use App\Models\Weatherday;
use App\Models\WeatherLocation;
use App\Support\Coast;
use Carbon\Carbon;

/**
 * The front door at "/" (redesign proposal W1).
 *
 * Replaces the route closure that rendered the "Let's get you to..." card
 * list. The page now leads with what is live today: sea state and boat
 * count per coast, then a few top rated sites, then the account invitation.
 * The route name stays "/" so the sitemap and canonical tags are unchanged.
 *
 * Everything here is read only and cheap: one query each for today's
 * forecast, today's trips and the featured sites.
 */
class HomeController extends Controller
{
    /** How many featured sites to show. */
    private const FEATURED = 6;

    public function index()
    {
        $today = Carbon::today()->toDateString();

        // Sea state per coast: the best available forecast text for each
        // location, then grouped north to south the same way the board does.
        $locations = WeatherLocation::all();
        $shortByName = $locations->mapWithKeys(fn ($l) => [strtolower($l->location) => $l->short]);
        $weathers = Weatherday::where('date', $today)->get();

        $coasts = [];
        foreach (Coast::all() as $key => $coast) {
            if (in_array($key, ['other', 'argentina'], true)) {
                continue;
            }
            $coasts[$key] = ['key' => $key, 'label' => $coast['label'], 'codes' => $coast['codes'], 'am' => null, 'pm' => null, 'boats' => 0, 'nextTime' => null];
        }
        foreach ($weathers as $w) {
            $code = $shortByName[strtolower($w->location)] ?? null;
            $key = Coast::forCode($code);
            if (!isset($coasts[$key])) {
                continue;
            }
            // First forecast wins for the pill; the board shows every location.
            $coasts[$key]['am'] = $coasts[$key]['am'] ?? $w->conditionsAM_text;
            $coasts[$key]['pm'] = $coasts[$key]['pm'] ?? $w->conditionsPM_text;
        }

        // Boats leaving today per coast, and the next departure still ahead.
        $now = Carbon::now();
        foreach (Trip::where('date', $today)->get() as $trip) {
            $code = strtok(strtoupper((string) $trip->tags), ' ') ?: null;
            $key = Coast::forCode($code);
            if (!isset($coasts[$key])) {
                continue;
            }
            $coasts[$key]['boats']++;
            $departs = Carbon::parse($trip->date . ' ' . $trip->departureTime);
            if ($departs->gt($now) && ($coasts[$key]['nextTime'] === null || $departs->lt($coasts[$key]['nextTime']))) {
                $coasts[$key]['nextTime'] = $departs;
            }
        }
        $totalBoats = array_sum(array_column($coasts, 'boats'));

        // Featured sites: highest rated, with a photo when one exists.
        // Ratings are sparse (many sites have two or three votes), so a plain
        // sort by rate puts a 5.0 with two votes above a 4.8 with forty. The
        // score below pulls low vote counts toward the catalog average (4.2)
        // with the weight of three votes, a standard damped average.
        $featured = Site::where('_hidden', '<>', 1)
            ->whereNotNull('rate')
            ->orderByRaw('(rate * COALESCE(votes, 0) + 4.2 * 3) / (COALESCE(votes, 0) + 3) DESC')
            ->orderBy('votes', 'desc')
            ->take(self::FEATURED)
            ->get();
        $photos = Photo::whereIn('siteId', $featured->pluck('id'))->get()->groupBy('siteId');
        foreach ($featured as $site) {
            $photo = $photos->get($site->id)?->first();
            $site->photoUrl = $photo ? asset('assets') . '/img/sites/' . $photo->file : null;
        }

        $SEO = [
            'title'     => 'Florida scuba diving sites, calendars and operators',
            'desc'      => 'All you need to know for diving in Florida: dive operators, dive sites and wreckwiki, calendars, dive planning and more',
            'keywords'  => 'scuba diving florida, scuba, dive operators miami, dive operators fort lauderdale, diving florida keys, dive sites florida',
            'canonical' => route('/'),
        ];

        return view('pages.Landing', [
            'coasts'     => array_values($coasts),
            'totalBoats' => $totalBoats,
            'featured'   => $featured,
            'today'      => $today,
            'SEO'        => $SEO,
        ]);
    }
}

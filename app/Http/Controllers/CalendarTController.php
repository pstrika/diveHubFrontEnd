<?php

namespace App\Http\Controllers;

use App\Models\Weatherday;
use App\Models\Site;
use Illuminate\Http\Request;


use App\Models\Trip;
use App\Models\Boat;
use Carbon\Carbon;
use Symfony\Component\Console\Input\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CalendarTController extends Controller
{
    /**
     * The themed calendars (recreational, technical, wreck, shark, lobster)
     * used to be five month pages. They are now presets of the trip finder:
     * same URLs, rendering /Trips in range mode with the type chosen and the
     * next 30 days from the given date. One implementation, one card, one
     * set of filters.
     */
    private function finder(string $type, $date = null)
    {
        $from = $date ? Carbon::parse($date) : Carbon::today();
        if ($from->lt(Carbon::today())) {
            $from = Carbon::today();
        }
        // The finder reads its filters from the query string (Request::query()),
        // so the preset is written there; merge() would only touch the input bag.
        $request = request();
        $request->query->set('type', $type);
        $request->query->set('from', $from->toDateString());
        $request->query->set('to', $from->copy()->addDays(29)->toDateString());
        $request->query->remove('range');
        return app(TripsController::class)->show($request);
    }

    public function show($tripType = null, $date = null)
    {
        return $this->finder($tripType === 'tec' ? 'tec' : 'rec', $date);
    }

    /** Shark diving: the trip finder with the type preset and the next 30 days (URL unchanged). */
    public function showShark($date = null)
    {
        return $this->finder('shark', $date);
    }

    /** Lobster diving: the trip finder with the type preset and the next 30 days (URL unchanged). */
    public function showLobster($date = null)
    {
        return $this->finder('lobster', $date);
    }

    /** Wreck diving: the trip finder with the type preset and the next 30 days (URL unchanged). */
    public function showWreck($date = null)
    {
        return $this->finder('wreck', $date);
    }

    // showHydrotherapy moved to HydrotherapyCalendarController so this file can never
    // change the client's embedded calendar. Do not add it back here.
}

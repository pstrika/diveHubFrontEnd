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

    public function showHydrotherapy($date = null)
    {
        // if we didn't receive $date, we just put today's
        if (!$date) {
            $date = Carbon::today()->toDateString();
        }

        $month = date('m', strtotime($date)); // Extract the month from the 'date' variable
        $year = date('Y', strtotime($date)); // Extract the year from the 'date' variable
        
        $dateFrom = Carbon::parse($date)->format('Y-m-d');
        $dateTo = Carbon::parse($date)->endOfMonth()->format('Y-m-d'); // the page shows one month; fetching six weeks made the recreational list 4,000+ trips and 20+ seconds
        $trips = Trip::whereBetween('date', [$dateFrom, $dateTo])
            ->where('operatorId', '=', "46")
            ->whereDate('date', '>=', Carbon::today())
            ->get()->sortBy("date");

        $boat = Boat::where('operatorId', 46)->first();
        Log::debug($boat);
        Log::debug("Boat: " . $boat->name);
        
        $sites = Site::select('id', 'name', 'type', 'slug', 'maxDepth', 'level')->get()->keyBy('id'); // keyed so each trip is a lookup, not a scan of 380 sites
        
        Log::debug("size of sites: " . $sites);

        foreach($trips as $i => $trip) {
            // add boat capacity depending on trip type
            if($trip->tripType == 'Technical')
                $trips[$i]->boatCapacity = $boat->tec_capacity;
            else
                $trips[$i]->boatCapacity = $boat->capacity;
            if($trip->siteId != null) {
                $siteIds = explode(',', $trip->siteId);
                $relatedSites = array_filter(array_map(fn ($id) => $sites->get((int) trim($id)), $siteIds));
                
                #$j=0;
                foreach($relatedSites as $relatedSite) {
                    #$trips[$i]->site[$j]->id = $relatedSite->id;
                    #$trips[$i]->site[$j]->maxDepth = $relatedSite->maxDepth;
                    #$trips[$i]->site[$j]->level = $relatedSite->level;
                    #$j++;
                    $trips[$i]->site[] = $relatedSite;
                    
                }
            
            }
        }
            
        $dateF = Carbon::parse($date);
        
        // Get the next month....
        $nextMonthS = $dateF->addMonth()->startOfMonth()->toDateString(); // Add a month
        $thisMonth = Carbon::today()->startOfMonth();
        $prevMonth = $dateF->sub(new \DateInterval('P2M'));

        $controlNav = "";
        if($prevMonth < $thisMonth) {
            $prevMonth = $thisMonth;
            $controlNav = "disabled";
        }
        $prevMonthS = $prevMonth->toDateString();

        $currentMonthS = Carbon::parse($date)->format('F');
        $year = Carbon::parse($date)->format('Y');
        $currentDate = Carbon::parse($date)->startOfMonth()->toDateString();

        /*Provide SEO metadata */
        $SEO = array(
            "title" => "Hydrotherapy Dive Trip Calendar | Divers Hub",
            "desc" => "Calendar of upcoming hydrotherapy scuba diving trips in South Florida.",
            "canonical" => route("CalendarHydrotherapy"),
        );

        return view('pages.CalendarHydrotherapy', compact('trips', 'currentDate', 'currentMonthS', 'year', 'prevMonthS', 'nextMonthS', 'controlNav', 'SEO'));

    }
    
}

<?php

namespace App\Http\Controllers;

use App\Models\Boat;
use App\Models\Site;
use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * DO NOT CHANGE THIS FILE OR pages/CalendarHydrotherapy.blade.php.
 *
 * Divers Hub provides the calendar service to Hydrotherapy. Their website
 * embeds this page in an iframe and it is the only calendar view their
 * customers use, so it is a product we deliver to a client, not one of our own
 * pages. Pablo's instruction on 2026-09-10: the controller and the view are
 * completely untouched and the page renders exactly as it did before the
 * redesign. No logic, no colours, no layout, no markup.
 *
 * The code below is a byte for byte copy of CalendarTController::showHydrotherapy
 * as it stands on main. It was moved into its own controller precisely so that
 * work on the other calendars can never reach it by accident, which is what
 * happened during the redesign: the shared controller was tuned for speed and
 * that quietly changed this page's date range from six weeks to one month.
 *
 * If the shared stylesheet or a shared component ever has to change in a way
 * that would affect this page, copy what this page needs into it instead of
 * editing the shared thing.
 *
 * Routes: /CalendarHydrotherapy and /CalendarHydrotherapy/{date} (guest).
 */
class HydrotherapyCalendarController extends Controller
{
    public function show($date = null)
    {
        // if we didn't receive $date, we just put today's
        if (!$date) {
            $date = Carbon::today()->toDateString();
        }

        $month = date('m', strtotime($date)); // Extract the month from the 'date' variable
        $year = date('Y', strtotime($date)); // Extract the year from the 'date' variable

        $dateFrom = Carbon::parse($date)->format('Y-m-d');
        $dateTo = Carbon::parse($date)->addWeek(6)->format('Y-m-d');
        $trips = Trip::whereBetween('date', [$dateFrom, $dateTo])
            ->where('operatorId', '=', "46")
            ->whereDate('date', '>=', Carbon::today())
            ->get()->sortBy("date");

        $boat = Boat::where('operatorId', 46)->first();
        Log::debug($boat);
        Log::debug("Boat: " . $boat->name);

        $sites = collect(Site::select('id', 'maxDepth', 'level')->get());

        Log::debug("size of sites: " . $sites);

        foreach($trips as $i => $trip) {
            // add boat capacity depending on trip type
            if($trip->tripType == 'Technical')
                $trips[$i]->boatCapacity = $boat->tec_capacity;
            else
                $trips[$i]->boatCapacity = $boat->capacity;
            if($trip->siteId != null) {
                $siteIds = explode(',', $trip->siteId);
                $relatedSites = $sites->whereIn('id', $siteIds)->all();

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

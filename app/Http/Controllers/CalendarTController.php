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
    //
    public function show($tripType = null, $date = null)
    {
        // if we didn't receive $date, we just put today's
        if (!$date) {
            $date = Carbon::today()->toDateString();
        }

        // if we don't get tripType we assume is recreational
        if (!$tripType or $tripType == "rec") {
            $tripType = "Recreational";
        }
        elseif ($tripType == "tec") {
            $tripType = "Technical";
        }
        

        $month = date('m', strtotime($date)); // Extract the month from the 'date' variable
        $year = date('Y', strtotime($date)); // Extract the year from the 'date' variable

        /*$trips = Trip::whereMonth('date', $month)
            ->whereYear('date', $year)
            ->where('tripType', 'like', "%" . $tripType . "%")
            ->whereDate('date', '>=', Carbon::today())
            ->get()->sortBy("date");
        */
        
        $dateFrom = Carbon::parse($date)->format('Y-m-d');
        $dateTo = Carbon::parse($date)->addWeek(6)->format('Y-m-d');
        $trips = Trip::whereBetween('date', [$dateFrom, $dateTo])
            ->where('tripType', 'like', "%" . $tripType . "%")
            ->whereDate('date', '>=', Carbon::today())
            ->get()->sortBy("date");

        $sites = collect(Site::select('id', 'maxDepth', 'level')->get());
        
        Log::debug("size of sites: " . count($sites));
        Log::debug("size of trips: " . count($trips));

        foreach($trips as $i => $trip) {
            if($trip->siteId != null) {
                $siteIds = explode(',', $trip->siteId);
                $relatedSites = $sites->whereIn('id', $siteIds)->all();
                
                #$j=0;
                foreach($relatedSites as $relatedSite) {
                    #Log::debug("i, j: " . $i . " , " .$j);
                    //$trips[$i]->site[$j]->id = $relatedSite->id;
                    //$trips[$i]->site[$j]->maxDepth = $relatedSite->maxDepth;
                    //$trips[$i]->site[$j]->level = $relatedSite->level;
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

        if($tripType == "Technical")
            return view('pages.CalendarT', compact('trips', 'currentDate', 'currentMonthS', 'year', 'prevMonthS', 'nextMonthS', 'controlNav'));
        else
            return view('pages.CalendarR', compact('trips', 'currentDate', 'currentMonthS', 'year', 'prevMonthS', 'nextMonthS', 'controlNav'));

    }

    public function showShark($date = null)
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
            ->where('tags', 'like', "%SHA%")
            ->whereDate('date', '>=', Carbon::today())
            ->get()->sortBy("date");

        $sites = collect(Site::select('id', 'maxDepth', 'level')->get());
        
        Log::debug("size of sites: " . $sites);

        foreach($trips as $i => $trip) {
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

        return view('pages.CalendarShark', compact('trips', 'currentDate', 'currentMonthS', 'year', 'prevMonthS', 'nextMonthS', 'controlNav'));

    }

    public function showLobster($date = null)
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
            ->where('tags', 'like', "%LOB%")
            ->whereDate('date', '>=', Carbon::today())
            ->get()->sortBy("date");

        $sites = collect(Site::select('id', 'maxDepth', 'level')->get());
        
        Log::debug("size of sites: " . $sites);

        foreach($trips as $i => $trip) {
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

        return view('pages.CalendarLobster', compact('trips', 'currentDate', 'currentMonthS', 'year', 'prevMonthS', 'nextMonthS', 'controlNav'));

    }

    public function showWreck($date = null)
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
            ->where('siteIdStatus', 'confirmed')
            ->where(function ($query) {
                $query->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('sites')
                        ->whereRaw("FIND_IN_SET(sites.id, trips.siteId)")
                        ->where('sites.type', 'wreck');
                });
            })
            ->get()->sortBy("date");
        
        LOG::debug("Size of trips for wrecks: " . count($trips));
        
        /*$trips = collect(Trip::whereBetween('date', [$dateFrom, $dateTo])
            ->where('siteIdStatus', 'confirmed')
            ->get()->sortBy("date"));

        

        $sites = collect(Site::select('id', 'maxDepth', 'level', 'type')->get());
        Log::debug("size of sites: " . count($sites));

        foreach($trips as $i => $trip) {
            if($trip->siteId != null) {
                $siteIds = explode(',', $trip->siteId);
                $relatedSites = $sites->whereIn('id', $siteIds)->all();
                foreach($relatedSites as $relatedSite) {
                    if ($relatedSite->type == 'wreck')
                        $trips[$i]->site[] = $relatedSite;   
                }    
            }
        }

        // Filter out trips that don't have the 'site' attribute
        $trips = $trips->filter(function ($trip) {
            return isset($trip->site) && !empty($trip->site);
        })->values(); // Reset the keys

        $trips = $trips->toArray();*/

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

        Log::debug("Now returning view...");
        return view('pages.CalendarWreck', compact('trips', 'currentDate', 'currentMonthS', 'year', 'prevMonthS', 'nextMonthS', 'controlNav'));

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


        return view('pages.CalendarHydrotherapy', compact('trips', 'currentDate', 'currentMonthS', 'year', 'prevMonthS', 'nextMonthS', 'controlNav'));

    }
    
}

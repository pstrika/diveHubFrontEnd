<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Trip;
use App\Models\Operator;
use App\Models\User;
use App\Models\Site;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class EventController extends Controller
{
    //
    public function addEventToCalendar($tripId) {
        $user = User::findorFail(auth()->user()->id);
        $trip = Trip::findorFail($tripId);

        $newEvent = Event::create([
            'userId' => $user->id,
            'operatorId' => $trip->operatorId,
            'date' => $trip->date,
            'time' => $trip->departureTime,
            'tripName' => $trip->tripName,
            'booked' => false,
        ]);

        return redirect()->back()->with('alreadyInCalendar', true);


    }


    public function show($date = null) {
        $user = User::findorFail(auth()->user()->id);
        // if we didn't receive $date, we just put today's
        if (!$date) {
            $date = Carbon::today()->toDateString();
        }

        $month = date('m', strtotime($date)); // Extract the month from the 'date' variable
        $year = date('Y', strtotime($date)); // Extract the year from the 'date' variable
        
        $dateFrom = Carbon::parse($date)->format('Y-m-d');
        $dateTo = Carbon::parse($date)->addWeek(6)->format('Y-m-d');
        $events = Event::whereBetween('date', [$dateFrom, $dateTo])
            ->where('userId', auth()->user()->id)
            //->where('userId', '8')
            ->whereDate('date', '>=', Carbon::today())
            ->get()->sortBy("date");

        Log::debug("Got " . str(count($events)) . " event for user " . $user->name);
        $trips = [];
        foreach($events as $event) {
            $trip = Trip::tripInEvent($event);
            if($trip) {
                $trip->booked = $event->booked;
                $trip->eventId = $event->id;
                $trip->waiver = $trip->operator->waiverLink;
                //Log::debug("waiver: " . $trip->waiver);
                $trips[] = $trip;
            }
        }

        $sites = collect(Site::select('id', 'maxDepth', 'level')->get());
        
        Log::debug("size of sites: " . count($sites));
        Log::debug("Size of trips: " . count($trips));
        /* why did I put this codde in here???!?!?!?!?!?!?
        foreach($trips as $i => $trip) {
            Log::debug("content of siteId: " . $trip->siteId);
            if($trip->siteId != null) {
                $siteIds = explode(',', $trip->siteId);
                $relatedSites = $sites->whereIn('id', $siteIds)->all();
                Log::debug("relatedSites: " . count($relatedSites));
                
                //$j=0;
                foreach($relatedSites as $relatedSite) {
                    Log::debug("index i,j: " . $i . ", " . $j);
                    //$trips[$i]->site[$j]->id = $relatedSite->id;
                    //$trips[$i]->site[$j]->maxDepth = $relatedSite->maxDepth;
                    //$trips[$i]->site[$j]->level = $relatedSite->level;
                    //$j++;

                    $tempSite = [];
                    $tempSite['id'] = $relatedSite->id;
                    $tempSite['maxDepth'] = $relatedSite->maxDepth;
                    $tempSite['level'] = $relatedSite->level;
                    
                    
                }
            
            }
        }
            */
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

        
        return view('pages.MyCalendar', compact('trips', 'currentDate', 'currentMonthS', 'year', 'prevMonthS', 'nextMonthS', 'controlNav'));

    }

    public function setEventBook($eventId) {
        $event = Event::findOrFail($eventId);
        $event->booked = true;

        $event->save();

        return redirect()->back();
    }

    public function removeFromCalendar($eventId) {
        $event = Event::findOrFail($eventId);
        $event->delete();

        return redirect()->back();
    }
}

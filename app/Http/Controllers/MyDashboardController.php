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

class MyDashboardController extends Controller
{
    //
    public function showDashboard() {
        $user = User::findorFail(auth()->user()->id);
        // if we didn't receive $date, we just put today's
    
        $date = Carbon::today()->toDateString();

        $month = date('m', strtotime($date)); // Extract the month from the 'date' variable
        $year = date('Y', strtotime($date)); // Extract the year from the 'date' variable
        
        $dateFrom = Carbon::parse($date)->format('Y-m-d');
        $dateTo = Carbon::parse($date)->addWeek(6)->format('Y-m-d');
        $events = Event::whereDate('date', '>=', Carbon::today())
            ->where('userId', auth()->user()->id)
            ->take(10)->get()->sortBy("date");

        Log::debug("Got " . str(count($events)) . " event for user " . $user->name);
        $trips = [];
        foreach($events as $event) {
            $trip = Trip::tripInEvent($event);
            if($trip) {
                $trip->booked = $event->booked;
                $trip->eventId = $event->id;
                $trips[] = $trip;
            }
        }


        return view('pages.Dashboard', compact('trips'));
    }
}

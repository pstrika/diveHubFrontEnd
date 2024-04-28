<?php

namespace App\Http\Controllers;

use App\Models\Weatherday;
use Illuminate\Http\Request;


use App\Models\Trip;
use Carbon\Carbon;
use Symfony\Component\Console\Input\Input;

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
}

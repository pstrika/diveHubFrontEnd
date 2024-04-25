<?php

namespace App\Http\Controllers;

use App\Models\Weatherday;
use Illuminate\Http\Request;


use App\Models\Trip;
use App\Models\Operator;
use App\Models\Location;
use Carbon\Carbon;
use Symfony\Component\Console\Input\Input;

class TripDetailsController extends Controller
{
    //


    public function show($tripId)
    {
        
        $tripDetails = Trip::where('id', $tripId)->get()[0];

        $operator = Operator::where('id', $tripDetails->operatorId)->get()[0];

        $location = Location::where('short', $operator->location)->get()[0];

        return view('pages.TripDetails', compact('tripDetails', 'operator', 'location'));

    }
}
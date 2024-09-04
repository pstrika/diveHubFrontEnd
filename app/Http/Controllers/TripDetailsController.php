<?php

namespace App\Http\Controllers;

use App\Models\Weatherday;
use Illuminate\Http\Request;


use App\Models\Trip;
use App\Models\Site;
use App\Models\Operator;
use App\Models\Location;
use App\Models\Boat;
use App\Models\Photo;
use App\Models\User;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Input\Input;

class TripDetailsController extends Controller
{
    //


    public function show($tripId)
    {
        
        #$tripDetails = Trip::where('id', $tripId)->get()[0];
        $tripDetails = Trip::findOrFail($tripId);

        $operator = Operator::where('id', $tripDetails->operatorId)->get()[0];

        $location = Location::where('short', $operator->location)->get()[0];

        $boatBulk = Boat::where('id', $tripDetails->boatId)->get();

        

        $siteIds = explode(',', $tripDetails->siteId);
        

        Log::debug('siteIds count: ' . count($siteIds));
        Log::debug('siteIds: ' . json_encode($siteIds));
        
        # get all sites
        

        if(count($siteIds)>1) { //can also use $siteIds[0] != ""
            $sites = Site::whereIn('id', $siteIds)->get();
            Log::debug("Got " . count($sites) . " sites for this trip");
            #foreach($sites as $site) {
            #    Log::debug("Photos for site " . $site->name . " is " . $site->photos[0]->id . " and location long is " . $site->locationLong->location);
            #}
        #    $sites = Site::where('id', $siteIds[0])->get();
        #    $siteLocation = Location::where('short', $sites[0]->location)->get();
        #    $sites[0]->location = $siteLocation[0]->location;
        #    $sitePhoto = Photo::where('siteId', $sites[0]->id)->get()[0];
        #    Log::debug('Site location: ' . $siteLocation);
        } else {
            $sites = [];
        #    $sitePhoto = null;
        }
        

        if($boatBulk->isNotEmpty())
            $boats = $boatBulk;
        else {
            $boatBulk = Boat::where('operatorId', $tripDetails->operatorId)->get();
            if($boatBulk->isNotEmpty()){
                $boats = $boatBulk;
            }
            else
                $boats = null;
        }
        #return view('pages.TripDetails', compact('tripDetails', 'operator', 'location', 'boats', 'sites', 'sitePhoto'));

        // check if for this user this trip is on his calendar
        $alreadyInCalendar = Event::alreadyInCalendar($tripId);
        return view('pages.TripDetails', compact('tripDetails', 'operator', 'location', 'boats', 'sites', 'alreadyInCalendar'));

    }
}
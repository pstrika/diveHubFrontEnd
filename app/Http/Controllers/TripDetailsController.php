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
use App\Models\Group;
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

        // check if for this user this trip is on his calendar - the id (not
        // just the boolean) lets the view link "Remove" straight at the
        // real event instead of back at the add action.
        $myCalendarEvent = Event::findInCalendar($tripId);
        $alreadyInCalendar = $myCalendarEvent !== null;

        // Every active group this user belongs to (real users only - the
        // shared guest account has no groups and shouldn't see the option
        // anyway) - used only to tell "not in any group" apart from "in
        // groups, just none you can add a dive to" in the view below.
        $allMyGroups = auth()->user()->isNotGuest()
            ? Group::whereHas('members', function ($q) {
                $q->where('user_id', auth()->user()->id)->where('status', 'active');
            })->get()
            : collect();

        // Computed on every group the user belongs to (not just the
        // add-permission-narrowed $myGroups below) so the "Already in X, Y"
        // line further down the page - unrelated to who can add a dive,
        // just "which of my groups already have this" - keeps seeing the
        // full picture.
        foreach ($allMyGroups as $group_) {
            $group_->alreadyAdded = $group_->dives()
                ->where('operatorId', $tripDetails->operatorId)
                ->where('date', $tripDetails->date)
                ->where('time', $tripDetails->departureTime)
                ->where('tripName', $tripDetails->tripName)
                ->exists();
        }

        // Narrowed to groups this user can actually add a dive to - a group
        // admin, or any group that lets all members add dives (Pablo,
        // 2026-09-22: "showing me all the groups I'm in - regardless if I'm
        // able to add dives to that group...we only need to show the
        // groups that the use is able to add trips"). A regular member of
        // a group that doesn't allow it has no business seeing it here at
        // all, since Groups.dives.store would 403 them anyway. Shares the
        // same Group instances as $allMyGroups, so alreadyAdded above
        // still applies to each.
        $myGroups = $allMyGroups->filter(fn ($g) => $g->canAddDives(auth()->user()->id))->values();

        /*Provide SEO metadata */
        // Trips are single-day, high-volume, expiring content (thousands generated daily) -
        // not worth indexing, but we still want Google to follow the links to the Site/
        // Operator pages a trip references.
        $SEO = array(
            "title" => $tripDetails->tripName . " - " . $tripDetails->operatorName . " - " . $tripDetails->date,
            "desc" => "Scuba diving trip with " . $tripDetails->operatorName . " on " . $tripDetails->date . ".",
            "canonical" => route("TripDetails", ['tripId' => $tripDetails->id]),
            "robots" => "noindex, follow",
        );

        return view('pages.TripDetails', compact('tripDetails', 'operator', 'location', 'boats', 'sites', 'alreadyInCalendar', 'myCalendarEvent', 'myGroups', 'allMyGroups', 'SEO'));

    }
}
<?php

namespace App\Http\Controllers;
use App\Models\Operator;
use App\Models\WeatherLocation;
use App\Models\Boat;
use App\Models\Trip;
use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Symfony\Component\Console\Input\Input;


class OperatorController extends Controller
{
    //
    public function show($id = null) {
    
    

    if ($id != null) {
        $user = User::findorFail(auth()->user()->id);
        $operator = Operator::findOrFail($id);
        $boats = Boat::where("operatorId", $id)->get();
        $favOperators = explode(',', $user->favOperators);
        if(in_array($id, $favOperators)) {
            $fav = true;
        } else
            $fav = false;


        // Get most popular sites for operator
        $trips = Trip::where('operatorId', $id)
            ->where('siteIdStatus', 'confirmed')
            ->get();

        if(count($trips)) {
            // Initialize an empty array to store the counts
            $counts = [];

            // Loop through each trip
            foreach ($trips as $trip) {
                // Split the comma-separated siteId values
                $siteIds = explode(',', $trip->siteId);

                // Increment the count for each integer
                foreach ($siteIds as $siteId) {
                    $siteId = trim($siteId); // Remove any leading/trailing spaces
                    if (!empty($siteId)) {
                        $counts[$siteId] = isset($counts[$siteId]) ? $counts[$siteId] + 1 : 1;
                    }
                }
            }

            
            arsort($counts);

            // Now $counts contains the count of each integer
            foreach ($counts as $siteId => $count) {
                
                Log::debug( "Site ID $siteId: $count occurrences");
            }
            
            $topIds =array_slice( array_keys($counts), 0, 10);
            Log::debug("ids= " . implode('-', $topIds));
            $topSites = Site::whereIn('id', $topIds)
            ->orderByRaw('FIELD(id, ' . implode(',', $topIds) . ')')
            ->get();


        } else
            $topSites = null;     

        /*Provide SEO metadata */
        $SEO = array(
            "title" => $operator->operatorName . " details - divers-hub.com",
            "desc" => "All details for " . $operator->operatorName . ": location, popular dive sites, prices, etc",
            "keywords" => $operator->operatorName . ", " . $operator->location . ", " . $operator->cityAddress,
        );

        return view('pages.OperatorDetails', compact('operator', 'boats', 'fav', 'topSites', 'SEO'));
    }


    $operators = Operator::all()->sortBy('operatorName');

    $locationAreas = Operator::distinct()->pluck('locationArea')->toArray();

    /*Provide SEO metadata */
    $operatorNames = $operators->pluck('operatorName')->toArray();
    $SEO = array(
        "title" => "Scuba diving operators in South Florida - divers-hub.com",
        "desc" => "Find all scuba diving operator in Miami, Fort Lauderdale, West Palm Beach and the Florida Keys",
        "keywords" => implode(', ', $operatorNames),
    );

    return view('pages.Operators', compact('operators', 'locationAreas', 'SEO'));

    }

    public function toggleFav($id) {
        $user = User::findorFail(auth()->user()->id);
        $favOperators = explode(',', $user->favOperators);
        if(in_array($id, $favOperators)) {
            Log::debug("found fav operator in index: " . str(array_search($id, $favOperators)));
            unset($favOperators[array_search($id, $favOperators)]);
            $user->favOperators = implode(', ', $favOperators);
        } else {
            Log::debug("Operator not in favs");
            $user->favOperators .= ', ' . str($id);
        }

        $user->save();

        return redirect()->back();
    }
    public function getWaivers() {
        
        
        $operators = Operator::all();
    
        return view('pages.Waivers', compact('operators'));
    
        }

    public function showHealth() {
        $operators = Operator::whereNotNull('_ver')->get()->sortBy('operatorName');
        $notScrapping = Operator::whereNull('_ver')->get()->sortBy('operatorName');
        $weatherLocations = WeatherLocation::all();
        return view('pages.PlatformHealth', compact('operators', 'weatherLocations', 'notScrapping'));
    }
}

<?php

namespace App\Http\Controllers;
use App\Models\Operator;
use App\Models\OperatorRating;
use App\Models\WeatherLocation;
use App\Models\Boat;
use App\Models\Trip;
use App\Models\Site;
use App\Models\User;
use App\Models\Photo;
use App\Support\OperatorBoard;
use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Symfony\Component\Console\Input\Input;


class OperatorController extends Controller
{
    //
    /**
     * /Operators (no id) renders the operators explorer; /OperatorDetails/{id}
     * renders one operator. Laravel injects $request and passes the route
     * parameter as $id, so one method serves both routes as it always has.
     */
    public function show(Request $request, $id = null) {

    if ($id != null) {
        $user = User::findorFail(auth()->user()->id);

        if (is_numeric($id)) {
            $operator = Operator::findOrFail(intval($id));

            // Canonicalize to the slug URL for SEO; old numeric links still work via this redirect.
            if (!empty($operator->slug)) {
                return redirect(route('OperatorDetails', ['id' => $operator->slug]), 301);
            }
        } else {
            $operator = Operator::where('slug', $id)->firstOrFail();
        }

        $id = $operator->id;

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

        $trips = Trip::where('operatorId', $id)
            ->whereDate('date', '>=', Carbon::today())
            ->get()->sortBy("date");

        $ratedAlready = OperatorRating::where('userId', auth()->id())->where('operatorId', $id)->exists();

        // First photo per top site so the detail page can show site cards.
        if ($topSites) {
            $firstPhotos = Photo::whereIn('siteId', $topSites->pluck('id'))->orderBy('id')->get()->groupBy('siteId');
            foreach ($topSites as $s) {
                $s->photoFile = $firstPhotos->get($s->id)?->first()?->file;
            }
        }
        $card = OperatorBoard::card($operator);

        /*Provide SEO metadata */
        $SEO = array(
            "title" => $operator->operatorName . " details - divers-hub.com",
            "desc" => "All details for " . $operator->operatorName . ": location, popular dive sites, prices, etc",
            "keywords" => $operator->operatorName . ", " . $operator->location . ", " . $operator->cityAddress,
            "canonical" => route("OperatorDetails", ['id' => $operator->slug ?? $operator->id]) ,
        );

        return view('pages.OperatorDetails', compact('operator', 'boats', 'fav', 'topSites', 'trips', 'ratedAlready', 'SEO', 'card'));
    }


    // Explorer: filters, counts and cards come from OperatorBoard (query string driven).
    $board = OperatorBoard::build($request);

    /*Provide SEO metadata */
    $operatorNames = Operator::pluck('operatorName')->toArray();
    $SEO = array(
        "title" => "Scuba diving operators in South Florida - divers-hub.com",
        "desc" => "Find all scuba diving operator in Miami, Fort Lauderdale, West Palm Beach and the Florida Keys",
        "keywords" => implode(', ', $operatorNames),
        "canonical" => route("Operators"),
    );

    return view('pages.Operators', ['board' => $board, 'SEO' => $SEO, 'mapboxToken' => 'pk.eyJ1IjoicHN0cmlrYSIsImEiOiJjbHZsc2p2bXcyY240MmtuMDcydHJzd2UxIn0.KBf79cvk47WseBc9rNu6gQ']);

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

        /*Provide SEO metadata */
        $SEO = array(
            "title" => "Online Dive Waivers | Divers Hub",
            "desc" => "Find online liability waiver links for scuba diving operators in South Florida.",
            "canonical" => route("Waivers"),
        );

        return view('pages.Waivers', compact('operators', 'SEO'));
    
        }

    public function showHealth() {
        $operators = Operator::whereNotNull('_ver')->get()->sortBy('operatorName');
        $notScrapping = Operator::whereNull('_ver')->get()->sortBy('operatorName');
        $weatherLocations = WeatherLocation::all();
        return view('pages.PlatformHealth', compact('operators', 'weatherLocations', 'notScrapping'));
    }
}

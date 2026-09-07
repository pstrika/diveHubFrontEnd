<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Operator;
use App\Models\Trip;
use App\Models\WeatherLocation;
use App\Models\WeatherDay;
use App\Models\VisitedSite;
use App\Models\WishedSite;
use App\Models\Site;
use App\Support\DiveLevel;
use App\Models\SiteComment;
use App\Models\Photo;
use App\Models\SiteRating;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Helpers\GasHelper;

class SiteController extends Controller
{
    //

    public function addReview($id, Request $request) {
        Log::debug("Got update for site Id: " . $id);
        Log::debug("Got this review: " . $request->review);

        SiteComment::create([
            'siteId' => $id,
            'userId' => auth()->id(),
            'comment' => $request->review,
            // Add other relevant fields as needed
        ]);

        return redirect()->back();

    }
    public function showBeach() {
        $sitesFLL = Site::where('access', 'Beach Access')->where('location', 'FLL')->get();
        //$sitesWPB = Site::where('access', 'Beach Access')->where('location', 'WPB')->get();
        $sitesWPB = Site::where(function ($query) {
            $query->where('location', 'BOY')
                  ->orWhere('location', 'WPB');
        })
        ->where('access', 'Beach Access')
        ->get();
        $locations = WeatherLocation::all();
        $weathersFLL = Weatherday::whereIn('location', ['fort lauderdale'])->orderBy('date')->take(5)->get();
        $weathersWPB = Weatherday::whereIn('location', ['west palm beach'])->orderBy('date')->take(5)->get();

        $weathers[0] = $weathersFLL;
        $weathers[1] = $weathersWPB;

        $i=0;
        if($sitesFLL->isNotEmpty()) {
            Log::debug("Site found for FLL " . str(count($sitesFLL)));
            $sites[$i] = $sitesFLL;
            $i++;
        }
        if($sitesWPB->isNotEmpty()) {
            Log::debug("Site found for WPB " . str(count($sitesWPB)));
            $sites[$i] = $sitesWPB;
            $i++;
        }

        /*Provide SEO metadata */
        $SEO = array(
            "title" => "Beach diving in South Florida",
            "desc" => "Find all the details for planning a successful beach diving in Fort Lauderdale or West Palm Beach",
            "keywords" => "beach diving, fort lauderdale beach diving, palm beach beach diving, shore diving",
            "canonical" => route("BeachDiving")
        );

        return view('pages.BeachDiving', compact('sites', 'locations', 'weathers', 'SEO'));
    }

    public function show($id = null) {
        if (is_numeric($id)) {
            $site = Site::with('reviews.user')->findOrFail(intval($id));

            // Canonicalize to the slug URL for SEO; old numeric links still work via this redirect.
            if (!empty($site->slug)) {
                return redirect(route('SiteDetails') . '/' . $site->slug, 301);
            }
        } else {
            $site = Site::with('reviews.user')->where('slug', $id)->firstOrFail();
        }

        $id = $site->id;

        // get trips for this site
        //$trips = Trip::where(function ($query) use ($site) {
        //    $query->whereRaw("FIND_IN_SET(?, siteId)", [$site->id]);
        //})->where('siteIdStatus', "confirmed")->whereDate('date', '>=', Carbon::today())->get()->sortBy('date');    //->where('siteIdStatus', '<>', "suggested")

        // Get best mixes for site
        $gasMixes = GasHelper::GasMixes($site->maxDepth);

        Log::debug("Best mixes");
        Log::debug('O2mix: ', $gasMixes['O2mix']);
        Log::debug('BestMix: ', $gasMixes['bestMix']);
        Log::debug('BestMixO2Narcotic: ', $gasMixes['bestMixO2Narcotic']);

        $trips = Trip::where('siteId', 'LIKE', '% ' . $id . ',%')
        ->orWhere('siteId', 'LIKE', $id . ',%')     //this is to account for the case where the siteId is in the first postion (not space in front)
        ->where('siteIdStatus', 'confirmed')
        ->whereDate('date', '>=', Carbon::today())
        ->get()->sortBy('date');



        $site->upcomingTrips = $trips;
        Log::debug("This site has upcoming trips:" . count($site->upcomingTrips));

        $photos = Photo::where('siteId', $id)->get();
        $location = WeatherLocation::where('short', $site->location)->first();

        $ids = explode(',', $site->visitingOperators);
        $operators = Operator::whereIn('id', $ids)->get();

        $ratedAlready = SiteRating::where('userId', auth()->id())->where('siteId', $id)->exists();

        // user has visited this site?
        $visited = VisitedSite::where('userId', auth()->id())->where('siteId', $id)->exists();
        Log::debug("User already visite this site? " . str($visited));

        // user has this site on wishlist?
        $wished = WishedSite::where('userId', auth()->id())->where('siteId', $id)->exists();
        Log::debug("User already has site on wishlist? " . str($wished));

        /*Provide SEO metadata */
        $SEO = array(
            "title" => $site->name . " ". $site->type,
            "desc" => $site->name . " " . $site->type . " in " . $location->location . ". Max depth " . $site->maxDepth . " ft",
            "keywords" => $site->name . "," . ($site->aka ? $site->aka . "," : "") . $location->location . "," . $site->type,
            "canonical" => route("SiteDetails") . "/" . ($site->slug ?? $site->id),
        );
        
        // get site list to print map
        $sites = Site::where('_hidden', '<>', 1)
             ->select('id', 'name', 'type', 'gpsLat', 'gpsLon')
             ->get()
             ->sortBy('name');

        // Redesign W3: live "diveable today" pill from this location's forecast,
        // and the next boats to this site as trip cards (the board's card).
        $forecast = $location
            ? \App\Models\Weatherday::where('date', Carbon::today()->toDateString())->where('location', $location->location)->first()
            : null;
        $now = Carbon::now();
        $operatorsById = Operator::select('id', 'location', 'phone')->get()->keyBy('id')->all();
        $nextTrips = $trips->take(8)->map(fn ($t) => \App\Support\TripBoard::card($t, $now, $operatorsById))->values()->all();

        return view('pages.SiteDetails', compact('site','photos', 'location', 'operators', 'ratedAlready', 'visited', 'wished', 'SEO', 'sites', 'gasMixes', 'forecast', 'nextTrips'));

    }
    public function getMyVisitedSites() {
        $visitedSites = VisitedSite::where('userId', auth()->user()->id)->get();
        $sites = Site::select('id', 'type', 'level', 'name', 'location')->where('_hidden', '<>', 1)->with('locationLong')->get();

        foreach ($sites as $i => $site)
            if($visitedSites->contains('siteId', $site->id))
                $sites[$i]->visited = 1;
            else
                $sites[$i]->visited = 0;
        
        return view('pages.MyVisitedSites', compact('sites'));
    }
    public function updateAllVisitedSites(Request $request) {
        Log::debug($request);

        $boardContent = json_decode($request->input('boardContent'), true);

        if (is_array($boardContent)) {
            // Delete all entries where userId matches the authenticated user's ID
            VisitedSite::where('userId', auth()->id())->delete();

            foreach($boardContent as $site) {
                VisitedSite::create([
                    'siteId' => $site,
                    'userId' => auth()->id(),
                    // Add other relevant fields as needed
                ]);    
            }
        }

        $view = $this->getMyVisitedSites();
        return $view;
    }
    public function updateVisited(Request $request) {
        Log::debug($request);

        if(VisitedSite::where('siteId', $request->site)->where('userId', auth()->id())->exists()) {
            VisitedSite::where('siteId', $request->site)->where('userId', auth()->id())->delete();
        } else {
            VisitedSite::create([
                'siteId' => $request->site,
                'userId' => auth()->id(),
                // Add other relevant fields as needed
            ]);
        }

        return redirect()->back();

    }
    public function updateWished($siteId) {
        Log::debug($siteId);

        if(WishedSite::where('siteId', $siteId)->where('userId', auth()->id())->exists()) {
            WishedSite::where('siteId', $siteId)->where('userId', auth()->id())->delete();
        } else {
            WishedSite::create([
                'siteId' => $siteId,
                'userId' => auth()->id(),
                'notified_email' => 0,
                'notified_sms' => 0,
                // Add other relevant fields as needed
            ]);
        }

        return redirect()->back();

    }
    public function showAdmin($id) {
        $this->authorize('manage-items', User::class);
        $status = null;
        $site = Site::findOrFail(intval($id));
        $photos = Photo::where('siteId', $id)->get();
        $locations = WeatherLocation::all();
        $operators = Operator::all();

        $visitingOperatorsIndex = explode(',', $site->visitingOperators);
        $visitingOperators = Operator::whereIn('id', $visitingOperatorsIndex)->get();

        return view('pages.edit-site', compact('site','photos', 'locations', 'operators', 'status', 'visitingOperators'));
    }

    public function showAdminPics($id) {
        $this->authorize('manage-items', User::class);
        $status = null;
        $site = Site::findOrFail(intval($id));
        $photos = Photo::where('siteId', $id)->get();

        return view('pages.updatePics', compact('site','photos', 'status'));
    }

    public function updatePics(Request $request) {
        $this->authorize('manage-items', User::class);

        Log::info('Request data @ updatePics:', $request->all());

        $site = Site::findOrFail($request->siteId);

        
        // Update photos only if we get an array with photoIds
        
        if($request->has('photoId')) {
            $photoIds = $request->photoId;
            $photoDescs = $request->picDesc;
            $photoCredits = $request->picCredit;
            $photos = Photo::whereIn('id', $photoIds)->get();

            //Log::debug("Length of photoId: " . count($photos));
            $i =0;
            foreach($photos as $photo) {
                Log::debug("Photo name: " . $photo->file);
                Log::debug("Old desc: " . $photo->desc);
                Log::debug("New desc: " . $photoDescs[$i]);

                $photo->desc = $photoDescs[$i];
                $photo->credit = $photoCredits[$i];
                $i++;
                $photo->save();
            }
        }

        if($request->has('videoCredit')) {
            Log::debug("Got video info");
            $videoArray = array(
                array("link" => $request->video, "credit" => $request->videoCredit),
            );

            $site->videos = json_encode($videoArray);

            $site->save();
        }

        // retrieve latest from model and show blade
        $photos = Photo::where('siteId', $request->siteId)->get();
        $status = "Media for site " . $site->name . " updated successfully";
        return view('pages.updatePics', compact('site','photos', 'status'));
    }

    public function update(Request $request) {
     
        $this->authorize('manage-items', User::class);

        $locations = WeatherLocation::all();
        $operators = Operator::all();
        $photos = Photo::where('siteId', $request->id)->get();
        //$newSite = Site::create($request->all());

        Log::info('Request data:', $request->all());

        $site = Site::findOrFail($request->id);

        //$path = request()->picture->store('pictures', 'public');
        //$attributes['picture'] = "$path";
        $wreckDetails = array(
            "type" => $request->shipType,
            "length" => $request->length,
            "beam" => $request->beam,
            "sunkDate" => $request->sunkDate,
        );

        if($request->has('type')) {
            Log::info("Got type. Updating to: " . str($request->type));
            $site->type = $request->type;
        }

        if($request->has('avgDepth')) {
            Log::info("Got avgDepth. Updating to: " . str($request->avgDepth));
            $site->avgDepth = $request->avgDepth;
        }

        if($request->has('maxDepth')) {
            Log::info("Got maxDepth. Updating to: " . str($request->maxDepth));
            $site->maxDepth = $request->maxDepth;
        }

        if($request->has('level')) {
            Log::info("Got level. Updating to: " . str($request->level));
            $site->level = $request->level;
        }
        if($request->has('location')) {
            Log::info("Got location. Updating to: " . str($request->location));
            $site->location = $request->location;
        }

        if($request->has('gpsLat')) {
            Log::info("Got gpsLat. Updating to: " . str($request->gpsLat));
            $site->gpsLat = $request->gpsLat;
        }

        if($request->has('gpsLon')) {
            Log::info("Got gpsLon. Updating to: " . str($request->gpsLon));
            $site->gpsLon = $request->gpsLon;
        }

        if($request->has('access')) {
            Log::info("Got access. Updating to: " . str($request->access));
            $site->access = $request->access;
        }

        if($request->has('visitingOperators')) {
            Log::info("Got visitingOperators. Updating to: " . str(implode(', ' , $request->input('visitingOperators'))));
            $site->visitingOperators = implode(', ' , $request->input('visitingOperators'));
        }

        if($request->has('desc')) {
            Log::info("Got desc. Updating to: " . str($request->desc));
            $site->desc = $request->desc;
        }

        if($request->has('route')) {
            Log::info("Got route. Updating to: " . str($request->route));
            $site->route = $request->route;
        }

        if($request->has('conditions')) {
            Log::info("Got conditions. Updating to: " . str($request->conditions));
            $site->typicalConditions = $request->conditions;
        }

        if($request->has('externalLink')) {
            Log::info("Got externalLink. Updating to: " . str($request->externalLink));
            $site->externalLink = $request->externalLink;
        }

        if($request->has('history')) {
            Log::info("Got history. Updating to: " . str($request->history));
            $site->history = $request->history;
        }

        if($request->has('shipType')) {
            Log::info("Got wreckData. Updating...");
            $site->wreckData = json_encode($wreckDetails);
        }

        
        $site->save();
        $status = "Dive site updated successfully";

        $visitingOperatorsIndex = explode(',', $site->visitingOperators);
        $visitingOperators = Operator::whereIn('id', $visitingOperatorsIndex)->get();
        
        //return view('pages.new-site', compact('locations','operators','status', 'newId'));

        
        //return redirect('new-site-uploadPics')->withStatus('Item successfully created.');
        //return view('pages.new-site-uploadPics', compact('newId', 'newName', 'status'));
        return view('pages.edit-site', compact('site','photos', 'locations', 'operators', 'status', 'visitingOperators'));
        
    }

    public function deletePic($id) {
        $this->authorize('manage-items', User::class);
        $photo = Photo::findOrFail($id);
        $site = Site::findOrFail($photo->siteId);
        
        $photo->deletePhoto();

        
        $photos = Photo::where('siteId', $site->id)->get();
        $photoIds = $photos->pluck('id')->toArray();
        $commaSeparatedString = implode(', ', $photoIds);
        $site->pics = $commaSeparatedString;
        $site->save();

        $status = "Picture successfully deleted.";
        return view('pages.updatePics', compact('site','photos', 'status'));
    }
    public function delete($id) {

        $this->authorize('manage-items', User::class);
        $site = Site::findOrFail(intval($id));
        $photos = Photo::where('siteId', $id)->get();

        $siteName = $site->name;

        foreach ($photos as $photo) {
            $photo->deletePhoto();
        }
        $site->delete();

        $sites = Site::all();
        $locations = WeatherLocation::all();

        return redirect()->back()->withStatus("Site \"" . $siteName . " \"successfully deleted");
    }
    public function showAll() {
        $sites = Site::where('_hidden', '<>', 1)->get()->sortby("name");
        Log::debug("# sites we got: " . count($sites));
        $locations = WeatherLocation::all();

        //center map on first user fav. If user is guest, we do FLL
        $defaultLoc = WeatherLocation::find(5);   //5 is the id for FLL
        $centerLon = $defaultLoc->centerLon;
        $centerLat = $defaultLoc->centerLat;
        if( auth()->id() != 5) {
            $user = User::findorFail(auth()->user()->id);
            $first = current(explode(',', $user->favLocations));
            if($first) {
                $defaultLoc = WeatherLocation::find($first);   //5 is the id for FLL
                $centerLon = $defaultLoc->centerLon;
                $centerLat = $defaultLoc->centerLat;
            }   
        }
        
        return view('pages.DiveSitesMap', compact('sites', 'locations', 'centerLon', 'centerLat'));
    }

    public function showAllSearch() {
        //$results = Site::all()->with('locationLong')->sortby("name");
        $results = Site::where('_hidden', '<>', 1)->with('locationLong')->get()->sortby("name");
        //$locations = WeatherLocation::all();
        $searchString = "all";

        Log::debug("Count of sites: " . count($results));

        return view('pages.DiveSitesSearch', compact('searchString', 'results'))->withStatus("show all");
    }

    /**
     * Dive Sites explorer (redesign W4). Serves two indexed URLs:
     *   /DiveSites   all sites, "Top Rated" intent, default sort by rating
     *   /WreckSites  wreckWiki, the wreck collection, default A to Z
     * Sites Map and Search redirect here; the map is ?view=map and search is ?q=.
     *
     * Every control is a query parameter (q, type, level, sort, view) and every
     * value is validated against a fixed list, so a hand edited URL can never
     * break the page or reach the database with junk.
     */
    private const EXPLORER_TYPES = ['wreck' => 'Wrecks', 'reef' => 'Reefs', 'other' => 'Other', 'shore' => 'Shore entry'];
    private const EXPLORER_SORTS = ['rate' => 'Top rated', 'name' => 'A to Z', 'maxDepth' => 'Deepest'];

    private function explorer(Request $request, array $explorer, array $SEO)
    {
        $q     = trim((string) $request->query('q', ''));
        $type  = $explorer['fixedType'] ?? $request->query('type');
        $type  = array_key_exists((string) $type, self::EXPLORER_TYPES) ? $type : null;
        $level = $request->query('level');
        $level = DiveLevel::isValid($level) ? (int) $level : null;
        $sort  = $request->query('sort', $explorer['defaultSort']);
        $sort  = array_key_exists((string) $sort, self::EXPLORER_SORTS) ? $sort : $explorer['defaultSort'];
        $view  = $request->query('view') === 'map' ? 'map' : 'list';

        $base = Site::where('_hidden', '<>', 1);
        if ($q !== '') {
            $base->where(fn ($w) => $w->where('name', 'LIKE', "%$q%")->orWhere('aka', 'LIKE', "%$q%"));
        }
        $applyType = function ($query, $t) {
            if ($t === 'shore') {
                return $query->where('access', 'Beach Access');
            }
            return $t ? $query->where('type', $t) : $query;
        };

        // Counts for the level chips are computed with the level filter off, so
        // each chip reads "how many if I picked this" (same idea as the board).
        $levelCounts = $applyType((clone $base), $type)
            ->selectRaw('level, COUNT(*) c')->groupBy('level')->pluck('c', 'level')->all();
        $levelCounts = array_replace(array_fill_keys(array_keys(DiveLevel::all()), 0), array_intersect_key($levelCounts, DiveLevel::all()));

        $query = $applyType($base, $type);
        if ($level !== null) {
            $query->where('level', $level);
        }
        switch ($sort) {
            case 'name':     $query->orderBy('name'); break;
            case 'maxDepth': $query->orderBy('maxDepth', 'desc'); break;
            default:         $query->orderByRaw('rate IS NULL, rate DESC')->orderBy('votes', 'desc')->orderBy('name');
        }
        $sites = $query->get();

        // First photo per site for the cards, one query for the whole page.
        $firstPhotos = Photo::whereIn('siteId', $sites->pluck('id'))->orderBy('id')->get()->groupBy('siteId');
        $locationNames = WeatherLocation::all()->pluck('location', 'short')->map(fn ($n) => ucwords($n));
        foreach ($sites as $site) {
            $site->photoFile = $firstPhotos->get($site->id)?->first()?->file;
            $site->locationName = $locationNames[$site->location] ?? null;
        }

        // Map features: sites store GPS as DMS text ("26° 22.838' N"); convert here so the page ships plain numbers.
        $mapFeatures = [];
        if ($view === 'map') {
            foreach ($sites as $site) {
                $lat = self::dmsToDecimal($site->gpsLat);
                $lon = self::dmsToDecimal($site->gpsLon);
                if ($lat === null || $lon === null) {
                    continue;
                }
                $mapFeatures[] = [
                    'type' => 'Feature',
                    'properties' => ['name' => $site->name, 'icon' => 'icon_' . (in_array($site->type, ['wreck', 'reef']) ? $site->type : 'other'), 'url' => route('SiteDetails') . '/' . ($site->slug ?? $site->id)],
                    'geometry' => ['type' => 'Point', 'coordinates' => [$lon, $lat]],
                ];
            }
        }

        return view('pages.SitesExplorer', [
            'explorer'    => $explorer,
            'SEO'         => $SEO,
            'sites'       => $sites,
            'filters'     => ['q' => $q, 'type' => $type, 'level' => $level, 'sort' => $sort, 'view' => $view],
            'typeOptions' => self::EXPLORER_TYPES,
            'sortOptions' => self::EXPLORER_SORTS,
            'levelCounts' => $levelCounts,
            'mapFeatures' => $mapFeatures,
            'mapboxToken' => 'pk.eyJ1IjoicHN0cmlrYSIsImEiOiJjbHZsc2p2bXcyY240MmtuMDcydHJzd2UxIn0.KBf79cvk47WseBc9rNu6gQ',
        ]);
    }

    /** "26° 22.838' N" to 26.38063. Null when the text does not parse. */
    public static function dmsToDecimal(?string $dms): ?float
    {
        if (!$dms) {
            return null;
        }
        $parts = sscanf(str_replace('&#039;', "'", $dms), "%d° %f' %c");
        if (!is_array($parts) || count(array_filter($parts, fn ($v) => $v !== null)) !== 3) {
            return null;
        }
        [$deg, $min, $dir] = $parts;
        $val = $deg + $min / 60;
        return round(in_array($dir, ['S', 'W'], true) ? -$val : $val, 6);
    }

    public function showTopRated(Request $request) {
        /*Provide SEO metadata */
        $SEO = array(
            "title" => "Best Dive Sites in Florida | Top Rated Reefs & Wrecks",
            "desc" => "Browse the top rated scuba diving sites in Florida, from wrecks to reefs, with depth, conditions and diver ratings.",
            "keywords" => "florida dive sites, best dive sites florida, top rated dive sites, florida reefs, florida wrecks, scuba diving florida",
            "canonical" => route("DiveSites")
        );
        return $this->explorer($request, [
            'heading'     => 'Top rated dive sites in Florida',
            'intro'       => 'Every reef, wreck and shore entry from Stuart to Key West, rated by the divers who have been there.',
            'fixedType'   => null,
            'defaultSort' => 'rate',
        ], $SEO);
    }

    public function showWrecks(Request $request) {
        /*Provide SEO metadata */
        $SEO = array(
            "title" => "Florida wreckwiki",
            "desc" => "An encyclopedia of all scuaba diving sites in Florida. The evolution of wreckwiki.com",
            "keywords" => "diving, fort lauderdale beach diving, palm beach beach diving,dive sites,scuba diving sites,dive wrecks,dive reefs,wreck,reef",
            "canonical" => route("WreckSites")
        );
        return $this->explorer($request, [
            'heading'     => 'wreckWiki',
            'intro'       => 'Powered by wreckwiki.com. Every artificial reef and shipwreck in Florida with depth, level, history and photos.',
            'fixedType'   => 'wreck',
            'defaultSort' => 'name',
        ], $SEO);
    }

    public function searchSites(Request $request) {

        Log::info('Request data:', $request->all());

        $locations = WeatherLocation::all();

        if ($request->has('searchString')) {
            Log::debug("Got searchString in request");
            $searchString = $request->searchString;
            $results = Site::select('id', 'name', 'type', 'level', 'location')
                ->where('name', 'LIKE', "%$searchString%")
                ->orWhere('aka', 'LIKE', "%$searchString%")
                //->orWhere('wreckData', 'LIKE', "%$searchString%")
                ->take(10)
                ->get();
            Log::info("Got " . str(count($results)) . " matches in the search");

            $resultsWreckType = Site::select('id', 'name', 'type', 'level', 'location')
                ->where('wreckData', 'LIKE', "%$searchString%")
                ->where('type', '=', 'wreck')
                ->take(10)
                ->get();
            Log::info("Got " . str(count($resultsWreckType)) . " matches in the search for wreck type");

            $resultsOperator = Operator::select('id', 'operatorName', 'cityAddress', 'stateAddress', 'logoUrl')
                ->where('operatorName', 'LIKE', "%$searchString%")
                ->take(10)
                ->get();

            $resultsDesc = Site::select('id', 'name', 'type', 'level', 'location', 'desc')
                ->where('desc', 'LIKE', "%$searchString%")
                ->take(10)
                ->get();
            Log::info("Got " . str(count($resultsDesc)) . " matches in the search for desc");

            $resultsDescription = [];
            $contextWords = 5; // Number of words before and after the match
            foreach($resultsDesc as $resultDesc) {
                //get the plain text from the quill json
                $delta = json_decode($resultDesc->desc);
                $desc = '';

                foreach ($delta->ops as $op) {
                    if (isset($op->insert) && is_string($op->insert)) {
                        $desc .= $op->insert;
                    }
                }

                // see where the search token is
                $position = stripos($desc, $searchString);
                Log::debug('position is: ' . $position);

                // get pre a pos words
                $preString = substr($desc, 0, $position);
                $posString = substr($desc, $position + strlen($searchString));

                $preWords = explode(' ', $preString);
                $posWords = explode(' ', $posString);
                //Log::debug('Pre words: ' . implode(' ', $preWords));
                //Log::debug('Pos words: ' . implode(' ', $posWords));

                $afterWords = array_slice($posWords, 0, $contextWords); // Get the first $contextWords elements
                $beforeWords = array_slice($preWords, -$contextWords, $contextWords, true);
                //Log::debug("Before words: " . implode(' ', $beforeWords));
                //Log::debug("After words: " . implode(' ', $afterWords));

                $beforeString = implode(' ', $beforeWords);
                $afterString = implode(' ', $afterWords);

                Log::debug($beforeString . $searchString . $afterString);
                $temp = array(
                    'beforeString' => $beforeString,
                    'searchString' => $searchString,
                    'afterString' => $afterString,
                    'siteId' => $resultDesc->id,
                    'siteName' => $resultDesc->name,
                    'siteType' => $resultDesc->type,
                );
                
                $resultsDescription[] = $temp;
            }

            $resultsHistory = Site::select('id', 'name', 'type', 'level', 'location', 'history')
                ->where('history', 'LIKE', "%$searchString%")
                ->take(10)
                ->get();
            Log::info("Got " . str(count($resultsHistory)) . " matches in the search for history");

            //check on history
            $resultsHistoryA = [];
            $contextWords = 5; // Number of words before and after the match
            foreach($resultsHistory as $resultHistory) {
                //get the plain text from the quill json
                $delta = json_decode($resultHistory->history);
                $history = '';

                foreach ($delta->ops as $op) {
                    if (isset($op->insert) && is_string($op->insert)) {
                        $history .= $op->insert;
                    }
                }

                // see where the search token is (stripos makes it case insensitive)
                $position = stripos($history, $searchString);
                Log::debug('position is: ' . $position);

                // get pre a pos words
                $preString = substr($history, 0, $position);
                $posString = substr($history, $position + strlen($searchString));

                $preWords = explode(' ', $preString);
                $posWords = explode(' ', $posString);

                $afterWords = array_slice($posWords, 0, $contextWords); // Get the first $contextWords elements
                $beforeWords = array_slice($preWords, -$contextWords, $contextWords, true);

                $beforeString = implode(' ', $beforeWords);
                $afterString = implode(' ', $afterWords);

                Log::debug($beforeString . $searchString . $afterString);
                $temp = array(
                    'beforeString' => $beforeString,
                    'searchString' => $searchString,
                    'afterString' => $afterString,
                    'siteId' => $resultHistory->id,
                    'siteName' => $resultHistory->name,
                    'siteType' => $resultHistory->type,
                );
                
                $resultsHistoryA[] = $temp;
                
            }
            Log::debug("Count history: " . count($resultsHistoryA));
            

            if(count($results) or count($resultsDescription) or count($resultsHistoryA) or count($resultsWreckType))
                return view('pages.DiveSitesSearch', compact('searchString', 'results', 'locations', 'resultsDescription', 'resultsHistoryA', 'resultsWreckType', 'resultsOperator'))->withStatus("match");
            else
                return view('pages.DiveSitesSearch', compact('searchString', 'results'))->withStatus("no match");
        }
        else
            return view('pages.DiveSitesSearch');
    }
    public function showAllAdmin() {
        $this->authorize('manage-items', User::class);
        $sites = Site::all();
        $locations = WeatherLocation::all();

        return view('pages.DiveSitesAdmin', compact('sites', 'locations'));
    }

    public function create() {
        $this->authorize('manage-items', User::class);
        
        $locations = WeatherLocation::all();
        $operators = Operator::all();

        $status = null;
        $newId = Site::max("id") + 1;

        return view('pages.new-site', compact('locations','operators', 'status', 'newId'));
    }

    public function store(Request $request) {
        
        $this->authorize('manage-items', User::class);

        $locations = WeatherLocation::all();
        $operators = Operator::all();
        //$newSite = Site::create($request->all());

        Log::info('Request data:', $request->all());

        $attributes=request()->validate([
            'name' => 'required|min:4|unique:mysql_trips.sites',
            'type' => 'required',
            'avgDepth' => 'max:500',
            //'picture' => 'required|mimes:jpg,jpeg,png,bmp,tiff |max:4096',            
            
        ]);

        //$path = request()->picture->store('pictures', 'public');
        //$attributes['picture'] = "$path";
        $wreckDetails = array(
            "type" => $request->shipType,
            "length" => $request->length,
            "beam" => $request->beam,
            "sunkDate" => $request->sunkDate,
        );

        $newSite = Site::create([
            'name' => $request->name,
            'type' => $request->type,
            'avgDepth' => $request->avgDepth,
            'maxDepth' => $request->maxDepth,
            'level' => $request->level,
            'location' => $request->location,
            'gpsLat' => $request->gpsLat,
            'gpsLon' => $request->gpsLon,
            'access'=> $request->access,
            'visitingOperators' => ($request->has('visitingOperators')? implode(', ' , $request->input('visitingOperators')) : ''),
            'desc' => $request->desc,
            'route' => $request->route,
            'typicalConditions' => $request->conditions,
            'externalLink' => $request->externalLink,
            'history' => $request->history,
            'wreckData' => json_encode($wreckDetails),
            
            //'tag' => strtoupper($request->type)[0] . $request->newId,
            //'pics' => $attributes['picture'],
            
        ]);
        
        $newSite->update([
            'tag' => strtoupper($request->type)[0] . $newSite->id,
        ]);
        

        $status = "Dive site created successfully";
        $newId = $newSite->id;
        $newName = $request->name;
        //return view('pages.new-site', compact('locations','operators','status', 'newId'));

        
        //return redirect('new-site-uploadPics')->withStatus('Item successfully created.');
        return view('pages.new-site-uploadPics', compact('newId', 'newName', 'status'));
        
    }

    public function updateMedia(Request $request) {

        $videoArray = array(
            array("link" => $request->video, "credit" => $request->videoCredit),
        );

        $photos = Photo::where('siteId',$request->newId)->get();
        $idArray = $photos->pluck('id')->toArray();
        $idString = implode(',', $idArray);

        if(Site::where('id',$request->newId)->update([
            'videos' => json_encode($videoArray),
            'pics' => $idString,
            ])) {
            
            $newId = $request->newId;
            $site = Site::where('id',$newId)->first();
            $newName = $site->name;
            return view('pages.new-site-updatePicsDesc', compact('newId', 'newName', 'photos'))->withStatus('Media Successfully Uploaded');    
        }
        else {
            Log::error("Couldn't update media for site: " . $request->newId);  
        }
    }

    public function updateDesc(Request $request) {
        $photos = Photo::where('siteId',$request->newId)->get();
        $descInputs = $request->input('picDesc');
        $creditInputs = $request->input('picCredit');
        //$photoIdInputs = $request->input('photId');
        $i=0;
        foreach($photos as $photo) {
            $photo->update([
                'desc' => $descInputs[$i],
                'credit' => $creditInputs[$i],
            ]);
            $i++;

        }
        
        $locations = WeatherLocation::all();
        $operators = Operator::all();

        $status = "Dive site created successfully";
        $newId = Site::max("id") + 1;

        return view('pages.new-site', compact('locations','operators', 'status', 'newId'));
    }

    public function upload(Request $request) {
        Log::info('Request data in upload picture!', $request->all());
        // Get chunk information
        $chunkNumber = $request->input('dzchunkindex');
        $totalChunks = $request->input('dztotalchunkcount');
        $fileUuid = $request->input('dzuuid');
        

        $filename = time() . '_' . $request->file('img_file')->getClientOriginalName();
        Log::info('The file name we created is: ' . $filename);
        
        // check if we are doing chunking
        if($chunkNumber == '') {
            $path = Storage::disk('siteAssets')->putFileAs('img/sites', $request->file('img_file'), $filename);
        } else {
            // Store the chunk (e.g., in a temporary directory)
            $chunkPath = storage_path('app/temp/' . $fileUuid . '/' . $chunkNumber);
            //$request->file('img_file')->storeAs('temp', $chunkNumber);
            Storage::disk('siteAssets')->putFileAs('img/sites/temp/' . $fileUuid, $request->file('img_file'), $chunkNumber);

            // Check if all chunks are uploaded
            Log::info('the current chunk number is: ' . $chunkNumber +1 . '/' . $totalChunks);
            if ($chunkNumber == $totalChunks - 1) {
                // Merge chunks into a complete file
                
                $mergedFilePath = config('filesystems.disks.siteAssets.root') . '/img/sites/' . $filename;
                
                $this->combineChunks($fileUuid, $totalChunks, $mergedFilePath);

                // Clean up temporary files
                $this->cleanupChunks($fileUuid);

                Photo::create([
                    'file'=> $filename,
                    'siteId'=> $request->input('siteId'),
                ]);   

                //agregar el id de la photo al Model del sitio (coma separated)
                $site = Site::findOrFail($request->input('siteId'));
                $photos = Photo::where('siteId', $site->id)->get();
                $photoIds = $photos->pluck('id')->toArray();
                $commaSeparatedString = implode(', ', $photoIds);
                $site->pics = $commaSeparatedString;
                $site->save();

                return response()->json(['message' => 'File uploaded and combined successfully']);
            }
            return response()->json(['message' => 'Chunk uploaded successfully']);
        }
        Photo::create([
            'file'=> $filename,
            'siteId'=> $request->input('siteId'),
        ]);    

        //agregar el id de la photo al Model del sitio (coma separated)
        $site = Site::findOrFail($request->input('siteId'));
        $photos = Photo::where('siteId', $site->id)->get();
        $photoIds = $photos->pluck('id')->toArray();
        $commaSeparatedString = implode(', ', $photoIds);
        $site->pics = $commaSeparatedString;
        $site->save();

        return response()->json(['message' => 'File no-chunk uploaded successfully']);
    }

    private function combineChunks($fileUuid, $totalChunks, $outputFilePath)
    {
        Log::info('Output file path is: ' . $outputFilePath);
        $outputFile = fopen($outputFilePath, 'ab', true);
        for ($i = 0; $i < $totalChunks; $i++) {
            $chunkPath = config('filesystems.disks.siteAssets.root') . '/img/sites/temp/' . $fileUuid .'/'. $i;
            Log::debug('Chunk # :' . $i . 'Path= ' . $chunkPath);
            $chunkContent = file_get_contents($chunkPath);
            $bytesWritten = fwrite($outputFile, $chunkContent);
            Log::debug('Bytes written for chunk: '. $i . ' = ' . $bytesWritten);
        
        }
        fclose($outputFile);
    }

    private function cleanupChunks($fileUuid)
    {
        $chunkDirectory = config('filesystems.disks.siteAssets.root') . '/img/sites/temp/' . $fileUuid;

        if (is_dir($chunkDirectory)) {
            try {
                File::deleteDirectory($chunkDirectory);
                Log::info('Folder deleted: ' . $chunkDirectory);
            } catch (\Exception $e) {
                Log::error('Error deleting folder: ' . $e->getMessage());
                // Handle the error (e.g., log it or return an error response)
            }
        } else {
            Log::warning('Folder does not exist: ' . $chunkDirectory);
            // Handle the case where the directory doesn't exist
        }
    }

}

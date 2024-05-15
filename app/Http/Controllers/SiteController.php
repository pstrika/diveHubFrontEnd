<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Operator;
use App\Models\WeatherLocation;
use App\Models\Site;
use App\Models\Photo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class SiteController extends Controller
{
    //

    public function show($id = null) {
        $site = Site::findOrFail(intval($id));
        $photos = Photo::where('siteId', $id)->get();
        $location = WeatherLocation::where('short', $site->location)->first();

        $ids = explode(',', $site->visitingOperators);
        $operators = Operator::whereIn('id', $ids)->get();

        return view('pages.SiteDetails', compact('site','photos', 'location', 'operators'));

    }

    public function delete($id) {
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
        $sites = Site::all();
        $locations = WeatherLocation::all();

        return view('pages.DiveSites', compact('sites', 'locations'));
    }

    public function showAllAdmin() {
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

    public function upload(Request $request)
    {
        Log::info('Request data:', $request->all());
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

                return response()->json(['message' => 'File uploaded and combined successfully']);
            }
            return response()->json(['message' => 'Chunk uploaded successfully']);
        }
        Photo::create([
            'file'=> $filename,
            'siteId'=> $request->input('siteId'),
        ]);    

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

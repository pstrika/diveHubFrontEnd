<?php

namespace App\Http\Controllers;

use App\Models\SiteRating;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class SiteRatingController extends Controller
{
    //
    public function new(Request $request) {
        Log::info('Request data:', $request->all());    

        $rating = new SiteRating();
        $rating->userId = Auth::user()->id;
        $rating->siteId = $request->siteId;
        $rating->starRating = $request->rate;

        $rating->save();

        // update rating in Site table
        $site = Site::findOrFail(intval($request->siteId));
        $newRating = ($site->rate * $site->votes + $request->rate) / ($site->votes + 1);
        $site->update([
            'rate' => $newRating,
            'votes' => $site->votes + 1,
        ]);
        
        session()->flash('msg', 'Rating successfully submitted!');
        return redirect()->back();
        //return back()->withStatus("Rating successfully submitted!");

    }
}

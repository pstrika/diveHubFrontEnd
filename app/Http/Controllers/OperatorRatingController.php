<?php

namespace App\Http\Controllers;

use App\Models\OperatorRating;
use App\Models\Operator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class OperatorRatingController extends Controller
{
    //
    public function new(Request $request) {
        Log::info('Request data:', $request->all());

        $rating = new OperatorRating();
        $rating->userId = Auth::user()->id;
        $rating->operatorId = $request->operatorId;
        $rating->starRating = $request->rate;

        $rating->save();

        // update rating in Operator table
        $operator = Operator::findOrFail(intval($request->operatorId));
        $newRating = ($operator->rate * $operator->votes + $request->rate) / ($operator->votes + 1);
        $operator->update([
            'rate' => $newRating,
            'votes' => $operator->votes + 1,
        ]);

        session()->flash('msg', 'Rating successfully submitted!');
        return redirect()->back();
        //return back()->withStatus("Rating successfully submitted!");

    }
}

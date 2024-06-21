<?php

namespace App\Http\Controllers;
use App\Models\Operator;
use App\Models\WeatherLocation;
use App\Models\Boat;
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

        return view('pages.OperatorDetails', compact('operator', 'boats', 'fav'));
    }


    $operators = Operator::all();

    $locationAreas = Operator::distinct()->pluck('locationArea')->toArray();

    return view('pages.Operators', compact('operators', 'locationAreas'));

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
        $operators = Operator::all()->sortBy('operatorName');
        $weatherLocations = WeatherLocation::all();
        return view('pages.PlatformHealth', compact('operators', 'weatherLocations'));
    }
}

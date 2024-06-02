<?php

namespace App\Http\Controllers;
use App\Models\Operator;
use App\Models\Boat;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Symfony\Component\Console\Input\Input;


class OperatorController extends Controller
{
    //
    public function show($id = null) {
        
    if ($id != null) {
        $operator = Operator::findOrFail($id);
        $boats = Boat::where("operatorId", $id)->get();
        return view('pages.OperatorDetails', compact('operator', 'boats'));
    }

    $operators = Operator::all();

    $locationAreas = Operator::distinct()->pluck('locationArea')->toArray();

    return view('pages.Operators', compact('operators', 'locationAreas'));

    }

    public function getWaivers() {
        
        
        $operators = Operator::all();
    
        return view('pages.Waivers', compact('operators'));
    
        }

    public function showHealth() {
        $operators = Operator::all();
        return view('pages.PlatformHealth', compact('operators'));
    }
}

<?php

namespace App\Http\Controllers;
use App\Models\Operator;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Symfony\Component\Console\Input\Input;


class OperatorController extends Controller
{
    //
    public function show($id = null) {



    $operators = Operator::all();

    $locationAreas = Operator::distinct()->pluck('locationArea')->toArray();

    return view('pages.Operators', compact('operators', 'locationAreas'));

    }
}

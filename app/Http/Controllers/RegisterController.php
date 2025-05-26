<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use App\Models\User;
use DevPro\GA4EventTracking\Facades\GA4;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    public function create()
    {
        return view('register.create',['roles'=>Role::get(['id','name'])]);
    }

    public function store(){

        $attributes = request()->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|min:7|max:255',
            'captcha' => 'required|captcha',
            //'role_id'=>'required'
        ]);

        // force role_id to Member
        $attributes['role_id'] = 3;
        $attributes['showLevel'] = '0, 4';
        $attributes['favLocations'] = '3, 5, 8';
        $attributes['certLevel'] = 0;
        $attributes['prefersLocation'] = 0;

        $user = User::create($attributes);
        auth()->login($user);
        
        session()->put('newUser', 1);
        Log::info('New user registered! Setting newUser flag in session');

        // add google tag
        /*$clientId = session('clientId');

        if (!is_null($clientId)) {
            GA4::setClientId($clientId);
        } else {
            // Handle the case where clientId is null
            // You can log an error or set a default value
            Log::error('Client ID is null');
        }
        $eventData = [
            'name' => 'conversion_event_signup',
            'params' => [
                'event_category' => 'Sign Up',
                'event_label' => 'Sign Up Successful',
                'value' => 1
            ]
        ];
    
        $response = GA4::sendEvent($eventData);*/

        return redirect('/user-profile');
    } 
}

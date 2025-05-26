<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Log;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        \Log::info('Redirect URI: ' . env('GOOGLE_REDIRECT_URI'));
        \Log::info('Client ID: ' . env('GOOGLE_CLIENT_ID'));
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $user = Socialite::driver('google')->user();
            $finduser = User::where('google_id', $user->id)->first();
            $userExists = User::where('email', $user->email)->first();

            if ($finduser) {
                Auth::login($finduser);
                return redirect()->intended('MyDashboard');
            } elseif($userExists) { //if the user already exists, we add the google_id to the account to allow SSO
                $userExists->google_id = $user->id;
                $userExists->save();
                return redirect()->intended('MyDashboard');
            } else {
                $newUser = User::create([
                    'name' => $user->name,
                    'email' => $user->email,
                    'google_id'=> $user->id,
                    'password' => encrypt('123456dummy'),
                    'role_id' => 3,
                    'showLevel' => '0, 4',
                    'favLocations' => '3, 5, 8',
                    'certLevel' => 0,
                    'prefersLocation' => 0,
                ]);

                Auth::login($newUser);
                return redirect()->intended('MyDashboard');
            }
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }
}

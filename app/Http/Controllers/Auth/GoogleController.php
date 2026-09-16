<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\GroupMember;
use App\Models\User;
use App\Services\NotificationService;
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
                // Kept fresh in case the diver's Google photo changed since
                // last login - only read by the welcome wizard's photo step
                // when there's no picture on file yet, so this is harmless
                // otherwise.
                $finduser->google_avatar_url = $user->avatar;
                $finduser->save();
                Auth::login($finduser, true);
                session()->put('dh_show_splash', true);
                return redirect()->intended('MyDashboard');
            } elseif($userExists) { //if the user already exists, we add the google_id to the account to allow SSO
                $userExists->google_id = $user->id;
                $userExists->google_avatar_url = $user->avatar;
                $userExists->save();
                Auth::login($userExists, true);
                session()->put('dh_show_splash', true);
                return redirect()->intended('MyDashboard');
            } else {
                $newUser = User::create([
                    'name' => $user->name,
                    'email' => $user->email,
                    'google_id'=> $user->id,
                    'google_avatar_url' => $user->avatar,
                    'password' => encrypt('123456dummy'),
                    'role_id' => 3,
                    // certLevel/favLocations/showLevel/prefersLocation are
                    // deliberately left unset (null), not given fake
                    // placeholder defaults - see RegisterController::store()
                    // for why (Pablo, 2026-09-16: the welcome wizard needs
                    // these genuinely empty to know a diver hasn't been
                    // through it yet).
                ]);

                // Link any pending group invites sent to this email before
                // they had an account - they'll now show up on MyGroups.
                $pendingInvites = GroupMember::where('invited_email', strtolower($newUser->email))
                    ->whereNull('user_id')
                    ->with('group')
                    ->get();

                foreach ($pendingInvites as $invite) {
                    $invite->user_id = $newUser->id;
                    $invite->save();

                    if ($invite->group) {
                        NotificationService::notify(
                            [$newUser->id],
                            $invite->group->name,
                            'You\'ve been invited to join "' . $invite->group->name . '"',
                            route('MyGroups'),
                            null,
                            $invite->invited_by,
                            $invite->group->id
                        );
                    }
                }

                Auth::login($newUser, true);
                session()->put('dh_show_splash', true);
                return redirect()->intended('MyDashboard');
            }
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }
}

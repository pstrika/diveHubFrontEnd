<?php

namespace App\Http\Controllers;

use App\Models\GroupMember;
use App\Models\Role;
use App\Services\NotificationService;
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
        // certLevel/favLocations/showLevel/prefersLocation are deliberately
        // left unset (null) here, not given fake placeholder defaults - the
        // welcome wizard (OnboardingController::needs()) uses exactly these
        // fields being empty to decide whether to prompt a fresh registrant,
        // and the old hardcoded defaults ('0, 4' / '3, 5, 8' / certLevel 0)
        // silently satisfied that check for every single new user, so the
        // wizard could never actually fire (Pablo, 2026-09-16: "I just
        // registered as a new user and I did not see any wizard at all").

        $user = User::create($attributes);

        // Link any pending group invites sent to this email before they had
        // an account - they'll now show up on MyGroups to accept/decline.
        $pendingInvites = GroupMember::where('invited_email', strtolower($user->email))
            ->whereNull('user_id')
            ->with('group')
            ->get();

        foreach ($pendingInvites as $invite) {
            $invite->user_id = $user->id;
            $invite->save();

            if ($invite->group) {
                NotificationService::notify(
                    [$user->id],
                    $invite->group->name,
                    'You\'ve been invited to join "' . $invite->group->name . '"',
                    route('MyGroups'),
                    null,
                    $invite->invited_by,
                    $invite->group->id
                );
            }
        }

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

        // Signed up from the app's sign-in sheet (Authenticate flagged the
        // session app_login and remembered /app/login?state=... as the
        // intended URL): finish the app's sign-in instead of the profile
        // page, so the app gets its divershub://auth callback (Pablo, via
        // the uploader session, 2026-10-16: "make sign-up return to the
        // app"). Google sign-up already returns there via intended().
        if (session('app_login') && str_contains((string) session('url.intended'), '/app/login')) {
            return redirect()->intended(route('AppLogin'));
        }
        return redirect('/user-profile');
    } 
}

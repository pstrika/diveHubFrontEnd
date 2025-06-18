<?php

namespace App\Http\Controllers;
use App\Models\WeatherLocation;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Operator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
//require 'vendor/autoload.php';
use Mailgun\Mailgun;

use function PHPUnit\Framework\isNull;


class UserController extends Controller
{
    protected $redirectTo = '/MyDashboard';
    public function index(){
        
        Log::info("Passing through User@index");
        Log::debug("Session new user is: " . session()->get('newUser'));
        //return view('laravel-examples.user-profile.edit');
        if(auth()->id() == 5)  //check for guest user
            return redirect()->route('Trips');
        elseif(session()->get('newUser')) {
            // send welcome email
            $mg = Mailgun::create(env('MAILGUN_KEY'));
            //$domain = "mail.divers-hub.com";

            Log::info("Sending welcome email to: " . auth()->user()->name . " <" . auth()->user()->email . ">");
            $mg->messages()->send('mail.divers-hub.com', [
                'from'    => 'Divers-Hub <postmaster@mail.divers-hub.com>',
                'to'      => auth()->user()->name . " <" . auth()->user()->email . ">",
                //'to'      => "Pablo Strika <pstrika@gmail.com>",
                'subject' => 'Welcome to Divers Hub',
                'template' => 'welcome',
                'h:X-Mailgun-Variables'    => '{"name": "' . auth()->user()->name . '"}'
              ]);

            return redirect()->route('MyDashboard')->with('newUser', true);
        }
        else
            //return redirect()->intended($this->redirectTo);
            return redirect()->route('MyDashboard')->with('newUser', false);;
    }

    public function update()
    {
        $user = request()->user();
        
        request()->validate([
            'email' => 'required|email|unique:users,email,'.$user->id,
            'phone' => 'required|max:10',
            'picture' => 'mimes:jpg,jpeg,png,bmp,tiff |max:4096',
            'location' => 'required',
            'name'=>'required',
        ]);

        $attributes = request()->all();

        if (env('IS_DEMO') && in_array($user->id, [1, 2, 3])){
            
            if(auth()->user()->email == request()->email){
                
                if (request()->file('picture')) {
                    $currentAvatar = auth()->user()->picture;
        
                    if($currentAvatar !== 'profile/avatar.jpg' && $currentAvatar !== 'profile/avatar2.jpg' && $currentAvatar !== 'profile/avatar3.jpg' && !empty($currentAvatar)){
        
                        unlink(storage_path('app/public/'.$currentAvatar));
                        $path = request()->picture->store('profile', 'public');
                        $attributes['picture'] = "$path";
                    }
                    else{
        
                        $path = request()->picture->store('profile', 'public');
                        $attributes['picture'] = "$path";
                    }
        
                }else{
                    unset($attributes['picture']);
                }

                auth()->user()->update($attributes);
                return back()->withStatus('Profile successfully updated.');
            }
            
            return back()->with('demo', "You are in a demo version, you can't change the default user email." );
        };


        if (request()->file('picture')) {
            $currentAvatar = auth()->user()->picture;

            if($currentAvatar !== 'profile/avatar.jpg' && $currentAvatar !== 'profile/avatar2.jpg' && $currentAvatar !== 'profile/avatar3.jpg' && !empty($currentAvatar)){

                unlink(storage_path('app/public/'.$currentAvatar));
                $path = request()->picture->store('profile', 'public');
                $attributes['picture'] = "$path";
            }
            else{

                $path = request()->picture->store('profile', 'public');
                $attributes['picture'] = "$path";
            }

        }else{
            unset($attributes['picture']);
        }

        auth()->user()->update($attributes);

        return back()->withStatus('Profile successfully updated.');
    }

    public function passwordUpdate() {

        /*request()->validate([ 
            'old_password' => 'required',
            'password' => 'required|min:7|confirmed',
        ]);*/

        Log::debug("Updating password");

        if(strlen(request()->old_password) == 0)
            return back()->with(['error' =>"Please type current password"]);

        if(strlen(request()->password) < 7)
            return back()->with(['error' =>"New password needs to be at least 7 characters"]);

        if(request()->password !=request()->password_confirmation)
            return back()->with(['error' =>"New password doesn't match confirmation"]);
        /*if (env('IS_DEMO') && in_array(auth()->user()->id, [1, 2, 3])){

            return back()->with('demo', "You are in a demo version, you can't change the default user password." );
        }*/
        
        $hashedPassword = auth()->user()->password;

        if (Hash::check(request()->old_password , $hashedPassword)) {
            Log::debug("Old password match!");
            if (!Hash::check(request()->password , $hashedPassword))
            {
                $users = User::findorFail(auth()->user()->id);
                $users->password = request()->password;
                $users->save();
                Log::debug("Password successfully updated.");
                return back()->with(['success'=>'Password successfully updated.']);
            }
            else{
                Log::debug("New password can not be the old password!");
                return back()->with(['error' =>"New password can not be the old password!"]);
            } 
        }
        else{
            Log::debug("Old password doesn't match");
            return back()->with(['error' =>"Old password doesn't match"]);
        }
    }

    public function getProfile() {
        $user = User::findorFail(auth()->user()->id);

        $operators = Operator::all();
        $favOperatorsIndex = explode(',', $user->favOperators);
        $favOperators = Operator::whereIn('id', $favOperatorsIndex)->get();

        $locations = WeatherLocation::all();
        $favLocationsIndex = explode(',', $user->favLocations);
        $favLocations = WeatherLocation::whereIn('id', $favLocationsIndex)->get();

        $favoriteLevels = explode(',', $user->showLevel);
        $showLevelLow = intval($favoriteLevels[0]);
        $showLevelHigh = intval($favoriteLevels[1]);

        return view('pages.profile.overview', compact('user', 'operators', 'favOperators', 'locations', 'favLocations', 'showLevelLow', 'showLevelHigh'));
    }

    public function updateProfile(Request $request) {

        $user = User::findorFail(auth()->user()->id);

        Log::info('Request data:', $request->all());

        if($request->has('level')) {
            Log::info("Got certification level. Updating to: " . str($request->level));
            $user->certLevel = $request->level;
        }
        
        if($request->has('name')) {
            Log::info("Got name. Updating to: " . str($request->name));
            $user->name = $request->name;
        }

        if($request->has('phone')) {
            Log::info("Got phone. Updating to: " . str($request->phone));
            $user->phone = $request->phone;
        }

        if($request->has('levelLow') and $request->has('levelHigh')) {
            Log::info("Got show level. Updating to: " . str($request->levelLow . "-" . str($request->levelHigh)));
            $user->showLevel = str($request->levelLow) . ", " . str($request->levelHigh);
        }

        if($request->intentEditFavOperators == "1")
            if($request->has('favOperators')) {
                Log::info("Got Favorite Operators. Updating to: ");
                $user->favOperators = implode(', ', $request->favOperators);
            }
            else {
                Log::info("Fav operators not present, but user intends to edit. Updating to: EMPTY");
                $user->favOperators = null;
            }

        if($request->intentEditFavLocations == "1")
            if($request->has('favLocations')) {
                Log::info("Got Favorite Locations. Updating to: ");
                $user->favLocations = implode(', ', $request->favLocations);
            }
            else {
                Log::info("Fav locations not present, but user intends to edit. Updating to: EMPTY");
                $user->favLocations = null;
            }

        // Special case: if we dont have the input, means the checkbox is off so we update to 0
        if($request->has('prefersLocation')) {
            Log::info("Got prefersLocation. Updating to: 1");
            $user->prefersLocation = true;
        } else {
            Log::info("Didn't get prefersLocation. Updating to: 0");
            $user->prefersLocation = false;
        }

        // Special case: if we dont have the input, means the checkbox is off so we update to 0. This is for config firstDayOfWeek
        if($request->has('firstDayOfWeek')) {
            Log::info("Got first day of week. Updating to: 1, which means monday");
            $user->firstDayOfWeek = 1;
        } else {
            Log::info("Didn't get firstDayOfWeek. Updating to: 0");
            $user->firstDayOfWeek = 0;
        }

        // Special case: if we dont have the input, means the checkbox is off so we update to 0. This is for config firstDayOfWeek
        if($request->has('email_notifications')) {
            Log::info("Got email_notifications. Updating to: 1");
            $user->email_notifications = 1;
        } else {
            Log::info("Didn't get email_notifications. Updating to: 0");
            $user->email_notifications = 0;
        }

        // Special case: if we dont have the input, means the checkbox is off so we update to 0. This is for config firstDayOfWeek
        if($request->has('sms_notifications')) {
            Log::info("Got sms_notifications. Updating to: 1");
            $user->sms_notifications = 1;
        } else {
            Log::info("Didn't get sms_notifications. Updating to: 0");
            $user->sms_notifications = 0;
        }

        if($request->has('show_visited')) {
            Log::info("Got show_visited. Updating to: 1");
            $user->show_visited = 1;
        } else {
            Log::info("Didn't get sms_notifications. Updating to: 0");
            $user->show_visited = 0;
        }

        if($request->has('deco_unit')) {
            Log::info("Got deco_unit. Updating to: 1");
            $user->deco_unit = 1;
        } else {
            Log::info("Didn't get deco_unit. Updating to: 0");
            $user->deco_unit = 0;
        }


        $user->save();

        return redirect()->back();
    }

    public function updateProfilePic(Request $request) {
        $user = User::findorFail(auth()->user()->id);
        Log::info('Request data updateProfilePic:', $request->all());

        $filename = time() . '_' . $request->file('img_file')->getClientOriginalName();
        Storage::disk('siteAssets')->putFileAs('img/users', $request->file('img_file'), $filename);
        
        $user->picture = $filename;
        $user->save();
        return redirect()->back();
    }
}

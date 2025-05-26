<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Log;
use Str;

class SessionsController extends Controller
{
    protected $redirectTo = '/MyDashboard';
    public function create()
    {
        // Save the URL the user is coming from
        //session()->put('url.intended', url()->previous());
        return view('sessions.create');
    }

    public function store()
    {
        $attributes = request()->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (! auth()->attempt($attributes)) {
            throw ValidationException::withMessages([
                'email' => 'Your provided credentials could not be verified.'
            ]);
        }

        
        session()->regenerate();
        session()->put('logged_in', true);  // use this to clear the localStorage for filter in the client side

        //return redirect()->intended($this->redirectTo);
        //return redirect('/Trips');

        if(auth()->id() == 5)  //check for guest user
            return redirect()->route('Trips');
        else
            return redirect()->intended('MyDashboard')->with('newUser', false);

    }

    public function show(){

        if(env('IS_DEMO')){

            return back()->with('demo', 'This is a demo version. You can not change the password.');
        }
        else{
        request()->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink(
            request()->only('email')
        );
    
        return $status === Password::RESET_LINK_SENT
                    ? back()->with(['status' => __($status)])
                    : back()->withErrors(['email' => __($status)]);
    }
    }

    public function update(){
        
        request()->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]); 
          
        $status = Password::reset(
            request()->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => ($password)
                ])->setRememberToken(Str::random(60));
    
                $user->save();
    
                event(new PasswordReset($user));
            }
        );
    
        return $status === Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('status', __($status))
                    : back()->withErrors(['email' => [__($status)]]);
    }


    public function destroy()
    {
        // Remove the intended URL from the session
        session()->forget('url.intended');
        auth()->logout();

        //return redirect('/sign-in');

        return view('sessions.create');
    }

}

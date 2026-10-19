<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OnboardingController;
use App\Support\AppleSignIn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

/**
 * Sign in with Apple on the website (2026-10-19). Apple answers with a
 * cross-site POST (form_post), which the browser sends without our session
 * cookie (SameSite=Lax) - so the state check and the "return to the app"
 * memory would be lost. callbackPost() therefore just bounces the answer to
 * a same-site GET of the same URL, where the session is back.
 */
class AppleController extends Controller
{
    public function redirect()
    {
        abort_unless(AppleSignIn::configured(), 404);
        return Socialite::driver('apple')->scopes(['name', 'email'])->redirect();
    }

    public function callbackPost(Request $request)
    {
        return redirect()->to(route('login.apple.callback', array_filter([
            'code' => $request->input('code'),
            'state' => $request->input('state'),
            'user' => $request->input('user'), // JSON with the name - first sign-in only
            'error' => $request->input('error'),
        ])));
    }

    public function callback(Request $request)
    {
        if ($request->input('error')) {
            // Cancelled on Apple's page.
            return redirect()->route('login');
        }
        if (is_string($request->input('user'))) {
            $request->merge(['user' => json_decode($request->input('user'), true) ?: null]);
        }
        try {
            $apple = Socialite::driver('apple')->user();
            $claims = AppleSignIn::verifyIdentityToken($apple->token);
        } catch (\Throwable $e) {
            Log::warning('Sign in with Apple failed: ' . $e->getMessage());
            return redirect()->route('login')->withErrors(['email' => 'Sign in with Apple didn\'t work. Please try again.']);
        }

        [$user, $isNew] = AppleSignIn::findOrCreate($claims['sub'], $claims['email'] ?? $apple->getEmail(), $apple->getName());
        Auth::login($user, true);
        session()->put('dh_show_splash', true);

        // A new account made from the app's sign-in sheet: the welcome wizard first, then back to the app.
        if ($isNew && ($toWizard = OnboardingController::appSignUpRedirect())) {
            return $toWizard;
        }
        return redirect()->intended('MyDashboard');
    }
}

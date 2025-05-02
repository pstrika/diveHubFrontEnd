<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return string|null
     */

     public function handle($request, Closure $next, ...$guards)
    {
        // Save the intended URL before redirecting
        Log::info("Saving intended URL: " . $request->url());
        $request->session()->put('url.intended', $request->url());
        if ($this->isGuest($request)) {
            //abort(403); // Unauthorized access
            

            // Redirect to the login page
            return redirect()->route('login');
            
        }
        return $next($request);
    }
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return route('login');
        }
    }

    protected function isGuest($request)
    {
        // Implement your logic to check if the user is an admin.
        // For example:
        // return $request->user()->role->description === 'Admin';
        if($request->user() == null)
            return route('login');
        return $request->user()->role_id == 4;
    }
    
   
}

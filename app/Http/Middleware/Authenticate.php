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
        // Only remember real page loads as the post-login redirect target.
        // This used to run unconditionally, so a background AJAX poll (e.g.
        // the group chat's /messages/poll, which passes through this same
        // 'auth' middleware) would overwrite url.intended with its own raw
        // JSON endpoint - every subsequent login then landed on that JSON
        // response instead of the dashboard (found 2026-09-24: "every login
        // is forcing to go to .../messages/poll").
        //
        // fullUrl(), not url() (2026-10-15, the app sign-in flow: GET
        // /app/login?state=<random> needs that query string to survive the
        // forced-login round trip so the state can be echoed back - url()
        // silently drops it, confirmed directly: Request::create('...?state=abc')
        // ->url() returns the path with no query string at all, ->fullUrl()
        // keeps it). Any other protected page with meaningful query params
        // was quietly losing them on a forced-login redirect too; this is a
        // strict improvement, not just a fix scoped to the new flow.
        if ($request->isMethod('GET') && !$request->expectsJson() && !$request->ajax()) {
            $request->session()->put('url.intended', $request->fullUrl());
        }
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

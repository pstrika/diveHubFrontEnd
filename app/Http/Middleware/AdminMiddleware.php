<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$this->isAdmin($request)) {
            abort(403); // Unauthorized access
        }
        return $next($request);
    }

    protected function isAdmin($request)
{
    // Implement your logic to check if the user is an admin.
    // For example:
    // return $request->user()->role->description === 'Admin';
    return $request->user()->role_id == 1;
}
}

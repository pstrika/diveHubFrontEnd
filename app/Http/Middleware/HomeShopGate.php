<?php

namespace App\Http\Middleware;

use App\Support\HomeShop;
use Closure;
use Illuminate\Http\Request;

/**
 * Route gate for the diver's home shop pages: ->middleware('home.shop').
 *
 * The same rule the menu link and the "Make this my home shop" button use
 * (HomeShop::allows): the listed accounts, anyone who manages a shop, and
 * any diver credited to a shop's QR code. It replaced early:home_shop on
 * these routes (2026-11-22), which only knew the list and would have
 * answered 404 to the other two groups after showing them the link.
 */
class HomeShopGate
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless(HomeShop::allows($request->user()), 404);
        return $next($request);
    }
}

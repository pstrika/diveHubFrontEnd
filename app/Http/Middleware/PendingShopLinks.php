<?php

namespace App\Http\Middleware;

use App\Support\ShopAccess;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

/**
 * Picks a shop link back up after someone signs in or signs up (shop
 * portal, Zach, 2026-11-13).
 *
 * Two links can be opened by a visitor who has no account yet:
 *   /operator-invite/{token}   become a shop admin   cookie dh_op_invite
 *   /go/{code}                 a shop's QR code      cookie dh_ref
 * Each leaves a cookie and sends the visitor to sign in or sign up. There
 * are five ways to do that here (form, Google, Apple, each as a login or
 * a new account), so instead of hooking every one, this runs on every
 * page: the first page a real member loads while a cookie is waiting
 * sends them to the screen that finishes the job.
 *
 * It only ever redirects. Nothing is granted or changed here: the invite
 * still needs its Accept button, and the home shop page decides what a
 * scan means for this diver.
 *
 * With no cookie this is two array lookups and no query.
 */
class PendingShopLinks
{
    public const INVITE_COOKIE = 'dh_op_invite';
    public const REFERRAL_COOKIE = 'dh_ref';

    public function handle(Request $request, Closure $next)
    {
        $invite = $request->cookie(self::INVITE_COOKIE);
        $referral = $request->cookie(self::REFERRAL_COOKIE);

        if ((!$invite && !$referral)
            || !$request->isMethod('GET')
            || $request->ajax() || $request->expectsJson()
            || !$this->isPageLoad($request)
            || $request->is('operator-invite/*', 'home-shop', 'home-shop/*', 'go/*', 'logout', 'sign-in*', 'sign-up*', 'login/*', 'cron/*', 'api/*')
            || !ShopAccess::isMember($request->user())) {
            return $next($request);
        }

        if (is_string($invite) && preg_match('/^[A-Za-z0-9]{40,80}$/', $invite)) {
            return redirect()->route('operator-invite.show', ['token' => $invite]);
        }
        if ($invite) {
            Cookie::queue(Cookie::forget(self::INVITE_COOKIE));
        }

        if ($referral) {
            return redirect()->route('home-shop.offer');
        }

        return $next($request);
    }

    /**
     * Only a person opening a page is sent anywhere. A background request
     * (the service worker, a prefetch, a script's fetch without JSON
     * headers) must never be, because following the redirect would use up
     * the waiting link on a page nobody is looking at.
     */
    private function isPageLoad(Request $request): bool
    {
        $dest = $request->header('Sec-Fetch-Dest');
        if ($dest !== null && $dest !== 'document') {
            return false;
        }
        if ($request->header('Purpose') === 'prefetch' || $request->header('Sec-Purpose') !== null) {
            return false;
        }
        return str_contains((string) $request->header('Accept'), 'text/html');
    }
}

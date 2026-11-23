<?php

namespace App\Http\Controllers;

use App\Http\Middleware\PendingShopLinks;
use App\Models\Operator;
use App\Models\OperatorInvite;
use App\Support\HomeShop;
use App\Support\ShopAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

/**
 * The link we send a shop so somebody there becomes its admin (Zach,
 * 2026-11-13). Creating and revoking links is ShopAccessAdminController;
 * this is the other end.
 *
 * Opening the link never grants anything. A signed in member sees a
 * confirm screen and has to press Accept. A visitor with no account is
 * sent to sign in or sign up, and PendingShopLinks brings them back to the
 * same confirm screen afterwards.
 *
 * The raw token is in the URL and in a 30 minute encrypted cookie, and
 * nowhere else: the database holds its SHA-256, and it is never logged.
 */
class OperatorInviteController extends Controller
{
    public function show(Request $request, string $token)
    {
        $invite = $this->find($token);
        if (!$invite) {
            Cookie::queue(Cookie::forget(PendingShopLinks::INVITE_COOKIE));
            return response()->view('pages.shop.invite-invalid', [], 404);
        }

        $operator = Operator::find($invite->operator_id);
        abort_unless($operator, 404);
        $shopName = HomeShop::shopName($operator);
        $user = $request->user();

        // Movement for Shop management: the link was opened. Once per
        // link, and not for the obvious crawlers and link previews. A mail
        // scanner that follows links can still set this, so it is shown as
        // "link opened", never as proof somebody read the page.
        try {
            $agent = (string) $request->userAgent();
            if ($agent !== '' && !preg_match('/bot|crawl|spider|slurp|preview|facebookexternalhit|monitor|curl|wget|python|headless/i', $agent)
                && !\App\Models\OperatorAudit::where('action', 'invite.viewed')->where('subject_type', 'invite')->where('subject_id', $invite->id)->exists()) {
                ShopAccess::audit((int) $operator->id, ShopAccess::isMember($user) ? (int) $user->id : 0, 'invite.viewed', 'invite', (int) $invite->id);
            }
        } catch (\Throwable $e) {
            // Never worth an error page.
        }

        if (!ShopAccess::isMember($user)) {
            // Remember the link across sign in or sign up.
            Cookie::queue(PendingShopLinks::INVITE_COOKIE, $token, 30);
            return view('pages.shop.invite', ['operator' => $operator, 'shopName' => $shopName, 'token' => $token, 'state' => 'signed-out']);
        }

        $wrongEmail = $invite->email && strcasecmp($invite->email, (string) $user->email) !== 0;

        return view('pages.shop.invite', [
            'operator' => $operator,
            'shopName' => $shopName,
            'token' => $token,
            'state' => $wrongEmail ? 'wrong-email' : 'confirm',
        ]);
    }

    public function accept(Request $request, string $token)
    {
        $user = $request->user();
        abort_unless(ShopAccess::isMember($user), 403);

        $invite = $this->find($token);
        if (!$invite) {
            Cookie::queue(Cookie::forget(PendingShopLinks::INVITE_COOKIE));
            return response()->view('pages.shop.invite-invalid', [], 404);
        }
        if ($invite->email && strcasecmp($invite->email, (string) $user->email) !== 0) {
            return redirect()->route('operator-invite.show', ['token' => $token]);
        }

        // Single use: claim the row first, so two taps cannot both win.
        $claimed = OperatorInvite::where('id', $invite->id)->whereNull('accepted_at')->whereNull('revoked_at')
            ->update(['accepted_at' => now(), 'accepted_user_id' => $user->id]);
        if (!$claimed) {
            Cookie::queue(Cookie::forget(PendingShopLinks::INVITE_COOKIE));
            return response()->view('pages.shop.invite-invalid', [], 404);
        }

        ShopAccess::grant((int) $invite->operator_id, (int) $user->id, (int) $invite->created_by, 'invite', (int) $invite->id);

        $operator = Operator::find($invite->operator_id);
        ShopAccess::notifyPlatformAdmins(
            'Shop admin invite accepted',
            $user->name . ' is now an admin for ' . ($operator ? HomeShop::shopName($operator) : 'operator ' . $invite->operator_id) . '.',
            route('shop-access.index')
        );

        Cookie::queue(Cookie::forget(PendingShopLinks::INVITE_COOKIE));
        return redirect()->route('shop-portal.overview', ['operator' => $invite->operator_id])
            ->with('shop_status', 'You are now an admin for this shop.');
    }

    /** "Not now": drop the waiting link so it stops following them around. */
    public function dismiss()
    {
        Cookie::queue(Cookie::forget(PendingShopLinks::INVITE_COOKIE));
        return redirect('/');
    }

    /** A live invite for this token: exists, not used, not revoked, not expired. */
    private function find(string $token): ?OperatorInvite
    {
        if (!preg_match('/^[A-Za-z0-9]{40,80}$/', $token)) {
            return null;
        }
        return OperatorInvite::where('token_hash', hash('sha256', $token))
            ->whereNull('accepted_at')->whereNull('revoked_at')
            ->where('expires_at', '>', now())->first();
    }
}

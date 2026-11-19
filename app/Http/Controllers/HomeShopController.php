<?php

namespace App\Http\Controllers;

use App\Http\Middleware\PendingShopLinks;
use App\Models\Operator;
use App\Models\OperatorClaim;
use App\Models\OperatorLocation;
use App\Models\ReferralCode;
use App\Models\ReferralScan;
use App\Models\UserReferral;
use App\Support\EarlyAccess;
use App\Support\HomeShop;
use App\Support\ShopAccess;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

/**
 * The diver's side of the shop work (Zach, 2026-11-13): scanning a shop's
 * code, having a home shop, and asking to manage a shop.
 *
 * The one rule that matters: a home shop is never changed without the
 * diver saying so. The only time it is set for them is at signup, when
 * they arrived by a shop's code and have none, and they are told so with
 * a way to change it on the same screen.
 */
class HomeShopController extends Controller
{
    /** How long a scan is remembered for a visitor who has not signed up yet. */
    public const REFERRAL_DAYS = 90;

    /**
     * /go/{code}: what a shop's QR code points at. Records the scan, leaves
     * a cookie, and lands on the shop's page. Open to everyone.
     */
    public function scan(Request $request, string $code)
    {
        try {
            $row = ReferralCode::where('code', strtoupper($code))->where('is_active', true)->first();
        } catch (\Throwable $e) {
            $row = null;   // code deployed ahead of its migration
        }
        $operator = $row ? Operator::find($row->operator_id) : null;
        if (!$row || !$operator) {
            return redirect()->route('Operators');
        }

        $agent = (string) $request->userAgent();
        $user = $request->user();
        ReferralScan::create([
            'referral_code_id' => $row->id,
            'scanned_at' => now(),
            // Same visitor on the same day gives the same hash; the IP itself
            // is never stored, and the daily salt stops it being a tracker.
            'visitor_hash' => hash('sha256', $request->ip() . '|' . $agent . '|' . now()->toDateString() . '|' . config('app.key')),
            'user_id' => ShopAccess::isMember($user) ? $user->id : null,
            'is_bot' => (bool) preg_match('/bot|crawl|spider|slurp|preview|facebookexternalhit|headless|curl|wget|python|monitor/i', $agent) || $agent === '',
            'user_agent_family' => $this->agentFamily($agent),
        ]);

        // Last touch wins: a later scan of another shop's code replaces this.
        Cookie::queue(PendingShopLinks::REFERRAL_COOKIE, $row->code . '|' . now()->timestamp, self::REFERRAL_DAYS * 24 * 60);

        $url = route('OperatorDetails', ['id' => $operator->slug ?: $operator->id])
            . '?utm_source=qr&utm_medium=' . urlencode($row->placement) . '&utm_campaign=home_shop#locations';

        return redirect($url);
    }

    /**
     * Where PendingShopLinks sends a signed in member who has a scan
     * waiting. Decides what the scan means for this diver:
     *   new account, no home shop   it is set, and they are told
     *   existing member, none       asked "make this your home shop?"
     *   existing member, has one    asked "switch?"
     *   already their home shop     nothing to say
     */
    public function offer(Request $request)
    {
        $user = $request->user();
        abort_unless(ShopAccess::isMember($user), 403);

        $cookie = (string) $request->cookie(PendingShopLinks::REFERRAL_COOKIE);
        Cookie::queue(Cookie::forget(PendingShopLinks::REFERRAL_COOKIE));
        [$code, $scannedAt] = array_pad(explode('|', $cookie, 2), 2, null);

        $row = $code ? ReferralCode::where('code', $code)->first() : null;
        $operator = $row ? Operator::find($row->operator_id) : null;
        if (!$row || !$operator) {
            return redirect('/');
        }
        $operatorUrl = route('OperatorDetails', ['id' => $operator->slug ?: $operator->id]);

        $signedUpAfterScan = $scannedAt && $user->created_at
            && $user->created_at->gte(Carbon::createFromTimestamp((int) $scannedAt)->subMinute());

        // Credit the shop once per diver, whatever the home shop gate says.
        UserReferral::firstOrCreate(['user_id' => $user->id], [
            'referral_code_id' => $row->id,
            'operator_id' => $row->operator_id,
            'operator_location_id' => $row->operator_location_id,
            'attributed_at' => now(),
            'attribution_source' => $signedUpAfterScan ? 'signup' : 'existing_user_scan',
        ]);

        if (!HomeShop::allows($user)) {
            return redirect($operatorUrl);
        }

        $location = $row->operator_location_id
            ? OperatorLocation::where('id', $row->operator_location_id)->where('is_active', true)->where('is_home_eligible', true)->first()
            : null;
        $location ??= HomeShop::defaultLocationFor((int) $operator->id);
        if (!$location || (int) $user->home_operator_location_id === (int) $location->id) {
            return redirect($operatorUrl);
        }

        $current = HomeShop::forUser($user);
        $shopName = HomeShop::shopName($operator);

        if ($signedUpAfterScan && !$current) {
            HomeShop::set($user, $location, 'referral');
            return view('pages.shop.home-offer', ['state' => 'set', 'shopName' => $shopName, 'current' => null, 'operatorUrl' => $operatorUrl]);
        }

        // Held in the session, not a form field: the answer can only ever
        // apply to the shop that was actually offered.
        $request->session()->put('home_shop_offer', $location->id);

        return view('pages.shop.home-offer', [
            'state' => $current ? 'switch' : 'make',
            'shopName' => $shopName,
            'current' => $current,
            'operatorUrl' => $operatorUrl,
        ]);
    }

    public function answer(Request $request)
    {
        $user = $request->user();
        abort_unless(HomeShop::allows($user), 404);

        $locationId = $request->session()->pull('home_shop_offer');
        if ($request->input('answer') === 'yes' && $locationId) {
            $location = OperatorLocation::where('id', $locationId)->where('is_active', true)->where('is_home_eligible', true)->first();
            if ($location) {
                HomeShop::set($user, $location, 'referral');
                return redirect()->route('home-shop')->with('shop_status', 'Your home shop is set.');
            }
        }
        return redirect('/');
    }

    /** "My home shop": what it is, change it, remove it. */
    public function index(Request $request)
    {
        $user = $request->user();
        $eligible = OperatorLocation::where('is_active', true)->where('is_home_eligible', true)->orderBy('name')->get();
        $operators = Operator::whereIn('id', $eligible->pluck('operator_id')->unique())->get()->keyBy('id');

        return view('pages.shop.home', [
            'home' => HomeShop::forUser($user),
            'choices' => $eligible->filter(fn ($l) => $operators->has($l->operator_id))
                ->map(fn ($l) => ['id' => $l->id, 'label' => HomeShop::shopName($operators[$l->operator_id]) . ($l->city ? ', ' . $l->city : '')])
                ->sortBy('label')->values(),
        ]);
    }

    public function set(Request $request)
    {
        $data = $request->validate(['location_id' => 'required|integer']);
        $location = OperatorLocation::where('id', $data['location_id'])->where('is_active', true)->where('is_home_eligible', true)->firstOrFail();
        HomeShop::set($request->user(), $location, 'manual');

        return back()->with('shop_status', 'Home shop set. Their trips now show first on the dive calendar.');
    }

    public function remove(Request $request)
    {
        HomeShop::clear($request->user());
        return back()->with('shop_status', 'Home shop removed.');
    }

    /** "Is this your shop?": the request form. */
    public function claimForm(Request $request, int $operator)
    {
        $op = Operator::findOrFail($operator);
        $user = $request->user();

        return view('pages.shop.claim', [
            'operator' => $op,
            'shopName' => HomeShop::shopName($op),
            'alreadyAdmin' => ShopAccess::canManage($user, (int) $op->id) && !$user->isAdmin(),
            'pending' => OperatorClaim::where('operator_id', $op->id)->where('user_id', $user->id)->where('status', 'pending')->exists(),
        ]);
    }

    /** Files the request. It grants nothing; a platform admin decides. */
    public function claim(Request $request, int $operator)
    {
        $op = Operator::findOrFail($operator);
        $user = $request->user();
        $data = $request->validate([
            'role_at_shop' => 'required|string|max:80',
            'contact_phone' => 'nullable|string|max:40',
            'note' => 'nullable|string|max:1000',
        ]);

        $exists = OperatorClaim::where('operator_id', $op->id)->where('user_id', $user->id)->where('status', 'pending')->exists();
        if (!$exists) {
            $claim = OperatorClaim::create([
                'operator_id' => $op->id,
                'user_id' => $user->id,
                'role_at_shop' => trim(strip_tags($data['role_at_shop'])),
                'contact_phone' => isset($data['contact_phone']) ? trim(strip_tags($data['contact_phone'])) : null,
                'note' => isset($data['note']) ? trim(strip_tags($data['note'])) : null,
            ]);
            ShopAccess::audit((int) $op->id, (int) $user->id, 'claim.requested', 'claim', (int) $claim->id);
            ShopAccess::notifyPlatformAdmins(
                'Shop access requested',
                $user->name . ' asked to manage ' . $op->operatorName . '.',
                route('shop-access.index')
            );
        }

        return redirect()->route('shop-claim.form', ['operator' => $op->id])
            ->with('shop_status', 'Request sent. We will check with the shop and get back to you.');
    }

    /** Whether the claim link is shown to this viewer on an operator page. */
    public static function claimOpenTo(?object $user): bool
    {
        return ShopAccess::isMember($user) && EarlyAccess::allows($user, 'operator_portal');
    }

    private function agentFamily(string $agent): string
    {
        foreach (['Edg' => 'Edge', 'OPR' => 'Opera', 'SamsungBrowser' => 'Samsung', 'CriOS' => 'Chrome', 'FxiOS' => 'Firefox', 'Chrome' => 'Chrome', 'Firefox' => 'Firefox', 'Safari' => 'Safari'] as $needle => $family) {
            if (str_contains($agent, $needle)) {
                return $family;
            }
        }
        return 'Other';
    }
}

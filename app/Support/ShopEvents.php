<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Counts what divers do around a shop, for the shop's insights (Zach,
 * 2026-11-19: "we need to start collecting whatever data is necessary to
 * get insights we want to give in the future").
 *
 * One row per action in operator_events. Rules:
 *   - It must never break or slow the page it is called from: every write
 *     is wrapped, and a failure is swallowed.
 *   - Crawlers and link previews are not divers and are not counted.
 *   - A shop's own admins looking at their own page are not counted.
 *   - Shops only ever see totals. user_id is stored so one diver's ten
 *     visits can be told from ten divers, and for nothing else.
 *
 * Already recorded elsewhere and NOT repeated here: trips saved to a
 * calendar and marked booked (events), QR scans (referral_scans), sign-ups
 * from a scan (user_referrals), ratings (operatorratings).
 */
class ShopEvents
{
    public const PAGE_VIEW = 'page_view';          // opened the shop's page
    public const TRIP_VIEW = 'trip_view';          // opened one of their trips
    public const WAIVER_CLICK = 'waiver_click';    // followed their waiver link
    public const FAVORITE_ON = 'favorite_on';
    public const FAVORITE_OFF = 'favorite_off';
    public const HOME_SET = 'home_shop_set';
    public const HOME_REMOVED = 'home_shop_removed';
    public const GOOGLE_REVIEW_CLICK = 'google_review_click';   // tapped "Review on Google" after a dive

    /**
     * @param array{trip_date?:?string,trip_time?:?string,trip_name?:?string,site_id?:?int,meta?:?array,user_id?:?int} $about
     *        user_id: the diver, when the page knows them some other way
     *        than a sign-in (the post-dive link carries a token).
     */
    public static function record(int $operatorId, string $type, array $about = []): void
    {
        try {
            $request = request();
            $agent = (string) $request->userAgent();
            if ($agent === '' || preg_match('/bot|crawl|spider|slurp|preview|facebookexternalhit|monitor|curl|wget|python|headless/i', $agent)) {
                return;
            }

            $user = auth()->user();
            $userId = ($user && (int) $user->id !== 5) ? (int) $user->id : null;   // 5 is the shared guest account
            if (!$userId && !empty($about['user_id'])) {
                $userId = (int) $about['user_id'];
            }
            if ($user && $userId && ShopAccess::canManage($user, $operatorId)) {
                return;
            }

            DB::connection('mysql_trips')->table('operator_events')->insert([
                'operator_id' => $operatorId,
                'type' => $type,
                'user_id' => $userId,
                'visitor_hash' => $userId ? null : hash('sha256', $request->ip() . '|' . $agent . '|' . now()->toDateString() . '|' . config('app.key')),
                'trip_date' => $about['trip_date'] ?? null,
                'trip_time' => isset($about['trip_time']) ? mb_substr((string) $about['trip_time'], 0, 10) : null,
                'trip_name' => isset($about['trip_name']) ? mb_substr((string) $about['trip_name'], 0, 200) : null,
                'site_id' => $about['site_id'] ?? null,
                'meta' => isset($about['meta']) ? json_encode($about['meta']) : null,
                'occurred_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Counting is never worth an error page.
        }
    }
}

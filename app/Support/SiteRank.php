<?php

namespace App\Support;

use App\Models\Trip;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Site ranking: how often boats actually go there, blended with what divers rate.
 *
 * Why (Pablo, 2026-09-07): "top rated" was really "our technical divers'
 * favorite sites", because most people who rate are tech divers. Trip counts
 * are the signal the whole fleet produces, so they carry more weight than the
 * still sparse ratings. Both numbers are shown on the cards; the blend only
 * decides the order.
 *
 *   popularity = log(1 + trips to the site in the window) / log(1 + busiest site)   0..1
 *   rating     = damped average / 5, where damped = (rate * votes + 4.2 * 3) / (votes + 3)
 *                (a site with no votes sits at the catalog average, 4.2)
 *   score      = 0.6 * popularity + 0.4 * rating
 *
 * Trip counts come from trips.siteId (a comma separated id list) over the last
 * twelve months plus whatever is scheduled ahead. The trips table is rebuilt by
 * the crawlers, so nothing here is stored: it is computed and cached for an hour.
 * No schema change.
 */
final class SiteRank
{
    public const WINDOW_MONTHS = 12;
    private const CACHE_KEY = 'siterank.tripcounts.v1';
    private const CACHE_SECONDS = 3600;
    private const CATALOG_AVERAGE = 4.2;
    private const DAMPING_VOTES = 3;
    private const POPULARITY_WEIGHT = 0.6;

    /** @return array<int,int> siteId => number of trips in the window */
    public static function tripCounts(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_SECONDS, function () {
            $counts = [];
            Trip::select('siteId')
                ->whereNotNull('siteId')->where('siteId', '<>', '')
                ->where('date', '>=', now()->subMonths(self::WINDOW_MONTHS)->toDateString())
                ->chunk(2000, function ($trips) use (&$counts) {
                    foreach ($trips as $trip) {
                        foreach (explode(',', $trip->siteId) as $id) {
                            $id = (int) trim($id);
                            if ($id > 0) {
                                $counts[$id] = ($counts[$id] ?? 0) + 1;
                            }
                        }
                    }
                });
            return $counts;
        });
    }

    /** Damped rating used everywhere a rating orders things (home, explorer, dashboard). */
    public static function dampedRating($rate, $votes): float
    {
        $votes = (int) $votes;
        if ($rate === null || $votes <= 0) {
            return self::CATALOG_AVERAGE;
        }
        return ((float) $rate * $votes + self::CATALOG_AVERAGE * self::DAMPING_VOTES) / ($votes + self::DAMPING_VOTES);
    }

    /**
     * Annotate each site with tripCount and rankScore, and return the collection
     * sorted best first. Works on Site models or anything with id, rate, votes.
     */
    public static function apply(Collection $sites): Collection
    {
        $counts = self::tripCounts();
        $max = max(1, (int) max([0, ...array_values($counts)]));
        $logMax = log(1 + $max);
        foreach ($sites as $site) {
            $n = $counts[$site->id] ?? 0;
            $site->tripCount = $n;
            $popularity = $logMax > 0 ? log(1 + $n) / $logMax : 0.0;
            $rating = self::dampedRating($site->rate, $site->votes) / 5;
            $site->rankScore = round(self::POPULARITY_WEIGHT * $popularity + (1 - self::POPULARITY_WEIGHT) * $rating, 4);
        }
        return $sites->sortByDesc(fn ($s) => [$s->rankScore, $s->tripCount, (int) $s->votes])->values();
    }

    /**
     * Highest level a member should be recommended (0..4), or null when unknown
     * or a guest. Reads users.showLevel ("low,high"), the range the diver set in
     * their profile; falls back to certLevel when only that is set.
     */
    public static function levelCap($user): ?int
    {
        if (!$user || !method_exists($user, 'isNotGuest') || !$user->isNotGuest()) {
            return null;
        }
        if (!empty($user->showLevel) && str_contains((string) $user->showLevel, ',')) {
            $high = (int) trim(explode(',', $user->showLevel)[1]);
            return DiveLevel::isValid($high) ? $high : null;
        }
        return DiveLevel::isValid($user->certLevel) ? (int) $user->certLevel : null;
    }
}

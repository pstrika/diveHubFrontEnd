<?php

namespace App\Support;

use App\Models\Dive;
use App\Models\Location;
use App\Models\UserSite;

/**
 * Country and location (city / area) of a site, the same way for a catalog
 * site and one of the diver's own, so one "Country" and one "Location"
 * filter can cover both (Pablo, 2026-10-22: filters on My sites and the
 * logbook landing).
 *
 * Catalog sites only carry a coast code (sites.location = "FLL"); its city
 * and 2-letter country live on weatherLocations. Own sites carry free text,
 * where the same country shows up as "US" or "United States" - both read as
 * the full name here.
 */
class SiteFacets
{
    private const ISO = [
        'US' => 'United States', 'USA' => 'United States', 'AR' => 'Argentina', 'MX' => 'Mexico',
        'UK' => 'United Kingdom', 'GB' => 'United Kingdom', 'UAE' => 'United Arab Emirates',
    ];

    /** @var array<string, array{0:?string,1:?string}>|null coast code => [location, country] */
    private static ?array $coasts = null;

    public static function country(?string $raw): ?string
    {
        $raw = trim((string) $raw);
        if ($raw === '') return null;
        return self::ISO[strtoupper($raw)] ?? $raw;
    }

    /** @var array<string, string>|null lowercase country name => ISO code */
    private static ?array $codes = null;

    /** Flag image URL for a country name, or null when it isn't a known country. */
    public static function flag(?string $country): ?string
    {
        if ($country === null || $country === '') return null;
        self::$codes ??= require __DIR__ . '/country_codes.php';
        $code = self::$codes[mb_strtolower(trim($country))] ?? null;
        return $code && is_file(public_path("assets/img/flags/$code.svg")) ? asset("assets/img/flags/$code.svg") : null;
    }

    /** value => flag URL for a list of country names (chip icons). */
    public static function flags(iterable $countries): array
    {
        $out = [];
        foreach ($countries as $c) if ($f = self::flag($c)) $out[$c] = $f;
        return $out;
    }

    /** [location, country] of a catalog site's coast code. */
    public static function coast(?string $code): array
    {
        if ($code === null || $code === '') return [null, null];
        if (self::$coasts === null) {
            self::$coasts = [];
            foreach (Location::get(['short', 'location', 'country']) as $l) {
                self::$coasts[strtoupper($l->short)] = [ucwords(strtolower((string) $l->location)) ?: null, self::country($l->country)];
            }
        }
        $code = strtoupper($code);
        $coast = self::$coasts[$code] ?? [null, null];
        // The site pages' own short names ("Pompano", "Deerfield") win when set.
        return [SiteLocations::NAMES[$code] ?? $coast[0] ?? $code, $coast[1]];
    }

    /** [location, country] of a diver's own site. */
    public static function ofUserSite(UserSite $s): array
    {
        $loc = trim((string) $s->location);
        return [$loc !== '' ? $loc : null, self::country($s->country)];
    }

    /**
     * [location, country] of a dive's site, or [null, null] for a free-typed
     * name / no site. Expects Dive::preloadSites() and ->load('userSite').
     */
    public static function ofDive(Dive $dive): array
    {
        if ($dive->site_id && ($site = $dive->site())) return self::coast($site->location);
        if ($dive->user_site_id && $dive->userSite) return self::ofUserSite($dive->userSite);
        return [null, null];
    }
}

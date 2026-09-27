<?php

namespace App\Support;

/**
 * Pages we deliver to a client to embed in their own website.
 *
 * The Hydrotherapy calendar is rendered in an iframe on divehydrotherapy.com and
 * it is the only calendar view their customers use. Pablo's instruction on
 * 2026-09-10: it must render exactly as it did before the redesign, so none of
 * the shared redesign chrome may reach it. That means no tokens stylesheet, no
 * shared script, no guest prompt, no add to home screen bar, no release stamp,
 * and the head colours it had before.
 *
 * Anything shared that would otherwise change one of these pages asks here
 * first. The frozen controller and view live apart on purpose:
 * HydrotherapyCalendarController and pages/CalendarHydrotherapy.blade.php.
 *
 * Add a route name below if we ever provide another embedded page.
 */
final class EmbeddedPage
{
    /** Route names whose output is frozen to its pre redesign form. */
    private const FROZEN_ROUTES = ['CalendarHydrotherapy'];

    /** Colours these pages carried before the redesign changed the app palette. */
    public const LEGACY_THEME_COLOR = '#1a73e8';
    public const LEGACY_OG_IMAGE = 'assets/img/diveHub-login.jpg';

    public static function isFrozen(): bool
    {
        foreach (self::FROZEN_ROUTES as $name) {
            if (request()->routeIs($name)) {
                return true;
            }
        }
        return false;
    }
}

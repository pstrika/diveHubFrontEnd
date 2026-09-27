<?php

namespace App\Support;

/**
 * The two customizable slots in the mobile bottom nav bar (Pablo,
 * 2026-09-24: "Trips, Dashboard and More need to stay always in the same
 * position and cannot be swapped. But they can choose to fill the other
 * two slots with..."). Used by resources/views/components/shell/nav.blade.php
 * (renders the bar), the profile overview page and the welcome wizard
 * (pick a value for each slot), and UserController/OnboardingController
 * (validate what gets saved to users.nav_slot_1/nav_slot_2).
 *
 * Icon strings follow shell/menu.blade.php's own convention: a bare name
 * is a Material icon, "svg:<file>" is one of the themed calendar SVGs
 * under public/assets/img/icons, inlined and recolored the same way.
 */
final class NavTabs
{
    public const DEFAULT_SLOT_1 = 'weather';
    public const DEFAULT_SLOT_2 = 'groups';

    public const OPTIONS = [
        'weather'          => ['label' => 'Weather', 'short' => 'Weather', 'icon' => 'cloud'],
        'groups'           => ['label' => 'Groups', 'short' => 'Groups', 'icon' => 'groups'],
        'sites'            => ['label' => 'Dive Sites', 'short' => 'Sites', 'icon' => 'pin_drop'],
        'operators'        => ['label' => 'Operators', 'short' => 'Operators', 'icon' => 'directions_boat'],
        'deco'             => ['label' => 'Deco Planner', 'short' => 'Deco', 'icon' => 'timer'],
        'gases'            => ['label' => 'Best Gases', 'short' => 'Gases', 'icon' => 'science'],
        'blog'             => ['label' => 'Blog', 'short' => 'Blog', 'icon' => 'auto_stories'],
        'beach'            => ['label' => 'Beach Diving', 'short' => 'Beach', 'icon' => 'beach_access'],
        'calendar_rec'     => ['label' => 'Recreational Calendar', 'short' => 'Rec', 'icon' => 'svg:icons_calendar_rec.svg'],
        'calendar_tec'     => ['label' => 'Technical Calendar', 'short' => 'Tec', 'icon' => 'svg:icons_calendar_tec.svg'],
        'calendar_wreck'   => ['label' => 'Wreck Calendar', 'short' => 'Wreck', 'icon' => 'svg:wreck_icon.svg'],
        'calendar_shark'   => ['label' => 'Shark Calendar', 'short' => 'Shark', 'icon' => 'svg:icons_calendar_shark.svg'],
        'calendar_lobster' => ['label' => 'Lobster Calendar', 'short' => 'Lobster', 'icon' => 'svg:icons_calendar_lobster.svg'],
    ];

    public static function isValid(?string $key): bool
    {
        return $key !== null && array_key_exists($key, self::OPTIONS);
    }

    /** Falls back to the default when null, invalid, or (rare) also picked in the other slot. */
    public static function resolveSlot(?string $key, string $default): string
    {
        return self::isValid($key) ? $key : $default;
    }

    public static function href(string $key): string
    {
        return match ($key) {
            'weather' => route('Weather'),
            'groups' => route('MyGroups'),
            'sites' => route('DiveSites'),
            'operators' => route('Operators'),
            'deco' => route('DecoPlanner'),
            'gases' => route('gasplanning'),
            'blog' => route('Blog'),
            'beach' => route('BeachDiving'),
            'calendar_rec' => route('CalendarT') . '/rec',
            'calendar_tec' => route('CalendarT') . '/tec',
            'calendar_wreck' => route('CalendarWreck'),
            'calendar_shark' => route('CalendarShark'),
            'calendar_lobster' => route('CalendarLobster'),
            default => route('Weather'),
        };
    }
}

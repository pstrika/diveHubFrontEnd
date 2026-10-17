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
        // Added 2026-10-16 (Pablo: "add the Dive Log... the multi dive planner
        // and gas blending (all the new functionalities we have created since
        // you built that)"). 'gate' = who can open it (see canUse()).
        'logbook'          => ['label' => 'Dive Log', 'short' => 'Log', 'icon' => 'menu_book', 'gate' => 'logbook'],
        'multidive'        => ['label' => 'Multi Dive Planner', 'short' => 'Multi', 'icon' => 'repeat', 'gate' => 'member'],
        'blending'         => ['label' => 'Gas Blending', 'short' => 'Blend', 'icon' => 'colorize'],
        'ccr_checklist'    => ['label' => 'rEvo CCR Checklist', 'short' => 'CCR', 'icon' => 'checklist', 'gate' => 'ccrChecklistAccess'],
    ];

    /** Can this user open the page behind $key? (Same gates as the menu.) */
    public static function canUse(string $key, $user = null): bool
    {
        $gate = self::OPTIONS[$key]['gate'] ?? null;
        if ($gate === null) return true;
        if (!$user) return false;
        if ($gate === 'member') {
            return $user->isNotGuest();
        }
        if ($gate === 'logbook') {
            return \App\Http\Middleware\LogbookGate::allows($user);
        }
        return $user->can($gate, \App\Models\User::class);
    }

    /** The options this user may pick. */
    public static function optionsFor($user = null): array
    {
        return array_filter(self::OPTIONS, fn ($o, $k) => self::canUse($k, $user), ARRAY_FILTER_USE_BOTH);
    }

    public static function isValid(?string $key, $user = null): bool
    {
        if ($key === null || !array_key_exists($key, self::OPTIONS)) return false;
        return $user === null || self::canUse($key, $user);
    }

    /** Falls back to the default when null, invalid, no longer allowed, or (rare) also picked in the other slot. */
    public static function resolveSlot(?string $key, string $default, $user = null): string
    {
        return self::isValid($key, $user) ? $key : $default;
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
            'logbook' => route('Logbook'),
            'multidive' => route('MultiDivePlanner'),
            'blending' => route('GasBlending'),
            'ccr_checklist' => route('CcrChecklist'),
            default => route('Weather'),
        };
    }
}

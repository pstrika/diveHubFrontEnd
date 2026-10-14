<?php

/**
 * Named gas-mix presets for the Gas Blending page (Pablo, 2026-10-17).
 *
 * A superset of the small 6-entry `gasArray` already duplicated in
 * GasPlanning.blade.php and SiteDetails.blade.php (Air, 36%, 32%, 21/35,
 * 18/45, 10/50) - those two existing pages are left untouched and keep
 * their own copies; this file is only consumed by GasBlending.blade.php's
 * quick-pick chips.
 *
 * The standalone Blend Helitrox tool was removed (Pablo, 2026-10-17: "the
 * helitrox calculator is redundant" - it was always just Blend Trimix with
 * a target O2 >= 21% floor and a worse, un-upgraded UI once Trimix got the
 * configurable bank gas + reorderable results). Its 4 normoxic presets
 * (21/21, 25/25, 28/30, 30/30) are folded into 'trimix' below instead, so
 * they're still one tap away as chips - nothing else enforces the >=21%
 * floor anymore, same as any other target mix on that tool.
 */
return [

    // 'trimix' is the Target gas chip list - no "Air" entry here (Pablo,
    // 2026-10-17: "does not make sense" as a blend target). 'bank' below
    // keeps its own "Air" since that's a legitimate topping gas. `group`
    // tags which of the 3 mobile rows a chip renders in (Pablo, 2026-10-17:
    // "show the chips for target in 3 rows - ean32/ean36, all helitrox,
    // all trimix") - this can't be inferred from o2/he alone (21/35 and
    // 21/21 both have o2=21 but belong to different rows), so it's an
    // explicit tag rather than a filter in the view.
    'trimix' => [
        ['label' => 'EAN32', 'o2' => 32, 'he' => 0, 'group' => 'nitrox'],
        ['label' => 'EAN36', 'o2' => 36, 'he' => 0, 'group' => 'nitrox'],
        ['label' => 'EAN50', 'o2' => 50, 'he' => 0, 'group' => 'nitrox'],
        ['label' => 'EAN80', 'o2' => 80, 'he' => 0, 'group' => 'nitrox'],
        ['label' => '21/21', 'o2' => 21, 'he' => 21, 'group' => 'helitrox'],
        ['label' => '25/25', 'o2' => 25, 'he' => 25, 'group' => 'helitrox'],
        ['label' => '28/30', 'o2' => 28, 'he' => 30, 'group' => 'helitrox'],
        ['label' => '30/30', 'o2' => 30, 'he' => 30, 'group' => 'helitrox'],
        ['label' => '21/35', 'o2' => 21, 'he' => 35, 'group' => 'trimix'],
        ['label' => '18/45', 'o2' => 18, 'he' => 45, 'group' => 'trimix'],
        ['label' => '15/50', 'o2' => 15, 'he' => 50, 'group' => 'trimix'],
        ['label' => '10/55', 'o2' => 10, 'he' => 55, 'group' => 'trimix'],
    ],

    // Standard fill-station bank gases, offered as the topping gas for
    // Blend Standard Trimix (Pablo, 2026-10-17: "we should be able to top
    // off with not only air, but standard bank gases") - generalizes the
    // blend formula from a hardcoded pure-air top-off to whichever bank is
    // actually on hand.
    'bank' => [
        ['label' => 'Air', 'o2' => 21, 'he' => 0],
        ['label' => 'EAN32', 'o2' => 32, 'he' => 0],
        ['label' => 'EAN36', 'o2' => 36, 'he' => 0],
        ['label' => '10/55', 'o2' => 10, 'he' => 55],
        ['label' => '18/45', 'o2' => 18, 'he' => 45],
        ['label' => '21/35', 'o2' => 21, 'he' => 35],
    ],

];

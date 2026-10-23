<?php

namespace App\Support;

/**
 * Fixed color convention for the dive profile chart (Pablo, 2026-10-15):
 * Depth, Temperature, NDL and PPO2 always keep their own colors; every
 * tank series (pressure, SAC, RMV) takes a tone of the family its gas's
 * ROLE belongs to:
 *   - CCR oxygen                              -> greens
 *   - CCR diluent, OC bottom gas              -> yellows
 *   - deco, travel and bailout gases          -> reds
 *   - a tank not matched to any gas yet       -> greys
 * Within a family the 1st, 2nd, 3rd tank get successive variants, and
 * each variant has a tone per series (pressure / SAC / RMV) so the three
 * lines of one tank read as a set. Tuned for the chart's dark background.
 */
class DiveChartPalette
{
    public const DEPTH = '#ffffff';
    public const TEMPERATURE = '#ff8a65';
    public const PPO2 = '#4fc3f7';
    public const NDL = '#ba68c8';
    public const HEART_RATE = '#f06292';

    private const FAMILIES = [
        'green' => [
            ['pressure' => '#66bb6a', 'sac' => '#a5d6a7', 'rmv' => '#00e676'],
            ['pressure' => '#9ccc65', 'sac' => '#c5e1a5', 'rmv' => '#76ff03'],
        ],
        'yellow' => [
            ['pressure' => '#ffd54f', 'sac' => '#fff59d', 'rmv' => '#ffab00'],
            ['pressure' => '#fbc02d', 'sac' => '#ffee58', 'rmv' => '#ffd600'],
            ['pressure' => '#fff176', 'sac' => '#fffde7', 'rmv' => '#ffc400'],
        ],
        'red' => [
            ['pressure' => '#ef5350', 'sac' => '#ef9a9a', 'rmv' => '#ff1744'],
            ['pressure' => '#d32f2f', 'sac' => '#e57373', 'rmv' => '#ff5252'],
            ['pressure' => '#b71c1c', 'sac' => '#ffcdd2', 'rmv' => '#d50000'],
        ],
        'grey' => [
            ['pressure' => '#90a4ae', 'sac' => '#b0bec5', 'rmv' => '#cfd8dc'],
            ['pressure' => '#78909c', 'sac' => '#a7b6bd', 'rmv' => '#eceff1'],
        ],
    ];

    public static function family(?string $role): string
    {
        switch (strtolower((string) $role)) {
            case 'oxygen':
                return 'green';
            case 'diluent':
            case 'bottom':
                return 'yellow';
            case 'deco':
            case 'travel':
            case 'bailout':
                return 'red';
            default:
                return 'grey';
        }
    }

    /**
     * @param array $tankRoles [tankIndex => role|null] for every tank the dive has
     * @return array [tankIndex => ['family' => ..., 'pressure' => hex, 'sac' => hex, 'rmv' => hex]]
     */
    public static function forTanks(array $tankRoles): array
    {
        ksort($tankRoles);
        $used = [];
        $out = [];
        foreach ($tankRoles as $tankIndex => $role) {
            $family = self::family($role);
            $n = $used[$family] ?? 0;
            $used[$family] = $n + 1;
            $variants = self::FAMILIES[$family];
            $out[$tankIndex] = ['family' => $family] + $variants[$n % count($variants)];
        }
        return $out;
    }
}

<?php

namespace App\Support;

/**
 * Server-side copy of the tank catalog in public/assets/js/dh-tanks.js
 * (and DecoPlanner's DH_TANK_TYPES) - keys, capacities and labels must
 * stay identical across the three. Exists so the app API can serve the
 * catalog (GET /api/logbook-options) and compute gas stats with the same
 * numbers the web page computes in the browser.
 */
class TankCatalog
{
    public const PSI_PER_BAR = 14.5038;
    public const LITERS_PER_CUFT = 28.3168;

    public const TYPES = [
        'AL-13'     => ['capacityPsi' => 3000, 'cuft' => 12.8, 'label' => 'AL-13 (2L)'],
        'AL-19'     => ['capacityPsi' => 3000, 'cuft' => 18.3, 'label' => 'AL-19 (3L)'],
        'AL-30'     => ['capacityPsi' => 3000, 'cuft' => 29.1],
        'AL-40'     => ['capacityPsi' => 3000, 'cuft' => 38.8],
        'AL-72'     => ['capacityPsi' => 3000, 'cuft' => 69.9],
        'AL-80'     => ['capacityPsi' => 3000, 'cuft' => 77.5],
        'ST-HP-2L'  => ['capacityPsi' => 3442, 'cuft' => 15.7],
        'ST-HP-3L'  => ['capacityPsi' => 3442, 'cuft' => 23.5],
        'ST-LP-50'  => ['capacityPsi' => 2640, 'cuft' => 48.7],
        'ST-LP-85'  => ['capacityPsi' => 2640, 'cuft' => 81.1],
        'ST-HP-100' => ['capacityPsi' => 3442, 'cuft' => 101.3],
        'Carbon Fiber 6.8L' => ['capacityPsi' => 4351, 'cuft' => 72.0],
        'Carbon Fiber 12L'  => ['capacityPsi' => 4351, 'cuft' => 127.1],
    ];

    /** The 2-3 L bottles drawn with the CCR template in the Gases card. */
    public const CCR_TEMPLATE_TYPES = ['AL-13', 'AL-19', 'ST-HP-2L', 'ST-HP-3L'];

    public static function label(?string $type): ?string
    {
        if ($type === null) return null;
        return self::TYPES[$type]['label'] ?? $type;
    }

    public static function workingPressureBar(?string $type): ?float
    {
        if ($type === null || !isset(self::TYPES[$type])) return null;
        return self::TYPES[$type]['capacityPsi'] / self::PSI_PER_BAR;
    }

    /** Same derivation as dh-tanks.js waterVolumeLiters(): free gas at rated pressure / rated pressure in bar. */
    public static function waterVolumeLiters(?string $type, bool $twins = false): ?float
    {
        if ($type === null || !isset(self::TYPES[$type])) return null;
        $t = self::TYPES[$type];
        $ratedBar = $t['capacityPsi'] / self::PSI_PER_BAR;
        $liters = ($t['cuft'] * self::LITERS_PER_CUFT) / $ratedBar;
        return $liters * ($twins ? 2 : 1);
    }

    /** Which template image the Gases card shows (tank_double / tank_ccr / tank_single). */
    public static function template(?string $type, bool $twins, bool $fixedCcrRow): string
    {
        if ($twins) return 'double';
        if ($type !== null && in_array($type, self::CCR_TEMPLATE_TYPES, true)) return 'ccr';
        if ($type !== null) return 'single';
        return $fixedCcrRow ? 'ccr' : 'single';
    }

    /** Catalog rows for the options endpoint. */
    public static function all(): array
    {
        $rows = [];
        foreach (self::TYPES as $key => $t) {
            $rows[] = [
                'key' => $key,
                'label' => $t['label'] ?? $key,
                'cuft' => $t['cuft'],
                'working_pressure_psi' => $t['capacityPsi'],
                'working_pressure_bar' => round($t['capacityPsi'] / self::PSI_PER_BAR, 1),
                'volume_l' => round(self::waterVolumeLiters($key), 2),
                'twins_allowed' => true,
                'template' => self::template($key, false, false),
            ];
        }
        return $rows;
    }
}

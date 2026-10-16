<?php

namespace App\Support;

use App\Models\Dive;

/**
 * The Gases card, computed server-side for the app API: one row per gas
 * exactly as resources/views/pages/DiveDetails.blade.php builds $gasRows
 * (CCR's fixed diluent/oxygen rows first, then every other gas_mixes
 * entry, the diver's saved order applied), with the per-tank numbers
 * public/assets/js/dive-details-form.js computes in the browser
 * (tankPressureRange / avgSacBar / RMV) ported 1:1 so both surfaces show
 * the same figures. Pressures are bar, volumes liters, rates per minute;
 * the client converts for display.
 */
class DiveGasStats
{
    public static function block(Dive $dive): array
    {
        $isCcr = $dive->isCcr();
        $conditions = $dive->conditions ?? [];
        $mixes = $dive->gas_mixes ?? [];
        $tanks = $dive->tanks ?? [];
        $match = GasTankMatcher::suggest($dive);
        $roleOptions = GasTankMatcher::roleOptions($isCcr);

        $deleted = $conditions['deleted_gas_mixes'] ?? [];
        $tankChoice = $conditions['gas_tank_choice'] ?? [];
        $roleChoice = $conditions['gas_role_choice'] ?? [];
        $tankConfig = $conditions['tank_config'] ?? [];
        $sizeChoice = $conditions['gas_tank_size_choice'] ?? [];

        $rows = [];
        $fixedTankIndices = [];

        if ($isCcr) {
            $diluentMix = null;
            $diluentGasIdx = null;
            foreach ($mixes as $gi => $m) {
                if (($m['usage'] ?? null) === 2) { $diluentMix = $m; $diluentGasIdx = $gi; break; }
            }
            // Diluent and oxygen rows on EVERY CC dive, with or without air
            // integration (a Petrel on a rEvo reports no tanks[] at all) -
            // same rule as the web Gases card. tank_index null without AI.
            $diluentTank = null;
            $oxygenTank = null;
            foreach ($tanks as $ti => $t) {
                $usage = $t['usage'] ?? null;
                if ($usage === 'diluent' && $diluentTank === null) $diluentTank = $ti;
                if ($usage === 'oxygen' && $oxygenTank === null) $oxygenTank = $ti;
            }
            if ($diluentMix) {
                $rows[] = self::row('diluent', $diluentGasIdx, $diluentMix, $diluentTank, $diluentTank !== null ? 'Tank D' : null, 'diluent', 'Diluent', true, false);
                if ($diluentTank !== null) $fixedTankIndices[] = $diluentTank;
            }
            $rows[] = self::row('oxygen', null, ['oxygen' => 100, 'helium' => 0], $oxygenTank, $oxygenTank !== null ? 'Tank O' : null, 'oxygen', 'Oxygen', true, false);
            if ($oxygenTank !== null) $fixedTankIndices[] = $oxygenTank;
        }

        foreach ($mixes as $gi => $mix) {
            if (($mix['usage'] ?? null) === 2) continue;
            $isDeleted = in_array($gi, $deleted);
            $tankIdx = array_key_exists($gi, $tankChoice) ? $tankChoice[$gi] : ($match['gasToTank'][$gi] ?? null);
            $tankIdx = $tankIdx === null || $tankIdx === '' ? null : (int) $tankIdx;
            if ($tankIdx !== null && in_array($tankIdx, $fixedTankIndices, true)) $tankIdx = null;
            $role = array_key_exists($gi, $roleChoice) ? $roleChoice[$gi] : ($match['roles'][$gi] ?? null);
            $rows[] = self::row(
                (string) $gi, $gi, $mix,
                $isDeleted ? null : $tankIdx,
                $isDeleted ? null : ($tankIdx !== null ? 'Tank ' . ($tankIdx + 1) : null),
                $role, $role ? ($roleOptions[$role] ?? ucfirst($role)) : null,
                false, $isDeleted
            );
        }

        $order = $conditions['gas_row_order'] ?? [];
        if (count($order)) {
            $pos = array_flip($order);
            usort($rows, fn ($a, $b) => ($pos[$a['row_key']] ?? PHP_INT_MAX) <=> ($pos[$b['row_key']] ?? PHP_INT_MAX));
        }

        // Fill tank + stats now that fixed/non-fixed is settled.
        foreach ($rows as &$row) {
            $tankIdx = $row['tank_index'];
            // Without AI the size is keyed by gas index - or, for CCR's fixed
            // rows, by row_key ('diluent'/'oxygen'), exactly as the web saves it.
            $sizeKey = $row['fixed'] ? $row['row_key'] : $row['gas_index'];
            $size = $tankIdx !== null
                ? ($tankConfig[$tankIdx] ?? null)
                : ($sizeKey !== null ? ($sizeChoice[$sizeKey] ?? null) : null);
            $row['size_key'] = $sizeKey;
            $type = $size['type'] ?? null;
            $twins = (bool) ($size['twins'] ?? false);
            $liters = TankCatalog::waterVolumeLiters($type, $twins);
            $row['tank'] = ($type !== null || $row['fixed']) ? [
                'type' => $type,
                'label' => TankCatalog::label($type),
                'twins' => $twins,
                'water_volume_l' => $liters !== null ? round($liters, 2) : null,
                'working_pressure_bar' => TankCatalog::workingPressureBar($type) !== null ? round(TankCatalog::workingPressureBar($type), 1) : null,
                'template' => TankCatalog::template($type, $twins, $row['fixed']),
            ] : null;
            $row['stats'] = (!$row['deleted'] && $tankIdx !== null) ? self::stats($dive, $tankIdx, $liters) : null;
            // Flat copies of the fields the app asked for by name.
            $row['start_bar'] = $row['stats']['start_bar'] ?? null;
            $row['end_bar'] = $row['stats']['end_bar'] ?? null;
            $row['sac_bar_min'] = $row['stats']['sac_bar_min'] ?? null;
            $row['consumed_l'] = $row['stats']['consumed_l'] ?? null;
            $row['rmv_l_min'] = $row['stats']['rmv_l_min'] ?? null;
        }
        unset($row);

        $tankOptions = [];
        foreach ($tanks as $ti => $t) {
            if (!in_array($ti, $fixedTankIndices, true)) $tankOptions[] = ['tank_index' => $ti, 'label' => 'Tank ' . ($ti + 1)];
        }

        // Only suggestions the app may act on: never a CCR fixed tank, never
        // the diluent gas (those rows are resolved above, not pickable).
        $suggestTanks = [];
        foreach (($match['gasToTank'] ?? []) as $gi => $ti) {
            if ($ti !== null && !in_array((int) $ti, $fixedTankIndices, true) && (($mixes[$gi]['usage'] ?? null) !== 2)) $suggestTanks[(string) $gi] = (int) $ti;
        }
        $suggestRoles = [];
        foreach (($match['roles'] ?? []) as $gi => $role) {
            if ((($mixes[$gi]['usage'] ?? null) !== 2)) $suggestRoles[(string) $gi] = $role;
        }

        // Chart color convention (DiveChartPalette), same as the web chart.
        $tankRoles = [];
        foreach ($tanks as $ti => $t) $tankRoles[$ti] = null;
        foreach ($rows as $r) {
            if ($r['tank_index'] !== null && !$r['deleted'] && array_key_exists($r['tank_index'], $tankRoles)) $tankRoles[$r['tank_index']] = $r['role'];
        }
        foreach ($tanks as $ti => $t) {
            if (($t['usage'] ?? null) === 'sidemount' && !in_array($tankRoles[$ti], ['deco', 'travel', 'bailout'], true)) $tankRoles[$ti] = 'bottom';
        }

        return [
            'chart_colors' => [
                'depth' => DiveChartPalette::DEPTH,
                'temperature' => DiveChartPalette::TEMPERATURE,
                'po2' => DiveChartPalette::PPO2,
                'ndl' => DiveChartPalette::NDL,
                'tanks' => (object) DiveChartPalette::forTanks($tankRoles),
            ],
            'gas_stats' => array_values($rows),
            'gas_tank_suggestion' => (object) $suggestTanks,
            'gas_role_suggestion' => (object) $suggestRoles,
            'gas_role_options' => $roleOptions,
            'tank_options' => $tankOptions,
            'has_air_integration' => count($tanks) > 0,
        ];
    }

    private static function row(string $rowKey, ?int $gasIndex, array $mix, ?int $tankIndex, ?string $tankLabel, ?string $role, ?string $roleLabel, bool $fixed, bool $deleted): array
    {
        return [
            'row_key' => $rowKey,
            'gas_index' => $gasIndex,
            'mix' => ['oxygen' => $mix['oxygen'] ?? null, 'helium' => $mix['helium'] ?? null],
            'label' => GasTankMatcher::gasLabel($mix),
            'role' => $role,
            'role_label' => $roleLabel,
            'fixed' => $fixed,
            'deleted' => $deleted,
            'tank_index' => $tankIndex,
            'tank_label' => $tankLabel,
        ];
    }

    /** Port of dive-details-form.js tankPressureRange() + avgSacBar(); null without two real readings. */
    private static function stats(Dive $dive, int $tankIndex, ?float $liters): ?array
    {
        $first = null; $last = null; $depthSum = 0.0; $depthCount = 0;
        foreach ($dive->profile ?? [] as $s) {
            $p = self::tankPressureAt($s, $tankIndex);
            if ($p !== null) {
                if ($first === null) $first = ['time' => $s['time'] ?? 0, 'p' => $p];
                $last = ['time' => $s['time'] ?? 0, 'p' => $p];
            }
            if (isset($s['depth']) && $s['depth'] !== null) { $depthSum += $s['depth']; $depthCount++; }
        }
        if ($first === null || $last === null) return null;
        $dtMin = ($last['time'] - $first['time']) / 60;
        $avgDepth = $depthCount ? $depthSum / $depthCount : 0;
        $sac = $dtMin > 0 ? (max(0, $first['p'] - $last['p']) / $dtMin) / (1 + $avgDepth / 10) : null;
        return [
            'start_bar' => round($first['p'], 1),
            'end_bar' => round($last['p'], 1),
            'duration_min' => round($dtMin, 1),
            'avg_depth_m' => round($avgDepth, 2),
            'sac_bar_min' => $sac !== null ? round($sac, 2) : null,
            'consumed_l' => $liters !== null ? round(max(0, $first['p'] - $last['p']) * $liters, 1) : null,
            'rmv_l_min' => ($sac !== null && $liters !== null) ? round($sac * $liters, 2) : null,
        ];
    }

    /** tankPressures is a list when every tank reported, a string-keyed object otherwise. */
    private static function tankPressureAt(array $sample, int $tankIndex): ?float
    {
        $tp = $sample['tankPressures'] ?? null;
        if (!is_array($tp)) return null;
        if (array_key_exists($tankIndex, $tp)) return $tp[$tankIndex] === null ? null : (float) $tp[$tankIndex];
        if (array_key_exists((string) $tankIndex, $tp)) return $tp[(string) $tankIndex] === null ? null : (float) $tp[(string) $tankIndex];
        return null;
    }
}

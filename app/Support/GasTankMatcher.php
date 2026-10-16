<?php

namespace App\Support;

use App\Models\Dive;

/**
 * Suggests which physical tank held which configured gas (Pablo,
 * 2026-10-15). Shearwater's own driver never links the two structurally -
 * confirmed against the actual libdivecomputer source
 * (shearwater_predator_parser.c): `dc_tank_t.gasmix` is hardcoded to
 * DC_GASMIX_UNKNOWN for every Predator/Petrel/Perdix dive, and
 * `gas_mixes[]` reflects the diver's gas-list CONFIGURATION (an
 * enabled-bitmask from the log header), not what was actually breathed -
 * a gas can sit in gas_mixes[] having never been selected this dive.
 *
 * CCR already has a real link (tanks[].usage 'diluent'/'oxygen' vs.
 * gas_mixes[].usage===2 for the diluent) - this class just repackages
 * that as a map. OC gets no such tag, so this infers a pairing from
 * per-tank pressure-drop timing against the gas list, in three cases:
 *   - one tank, one surviving gas candidate: trivial.
 *   - one tank, several candidates: no timing signal to disambiguate -
 *     left unmapped, a diver pick is required.
 *   - several tanks: ordered by when each one starts actually being
 *     breathed, paired against gases ordered leanest-O2-first - or, when
 *     more than one bottom/travel-shaped gas is configured (the travel-
 *     gas scenario), the most-consumed tank is called the bottom gas and
 *     a tank breathed in two separate windows (start + end, not the
 *     middle) is called the travel gas.
 *
 * Always a SUGGESTION, computed fresh from $dive and its current
 * conditions overrides - never persisted by this class. A confirmed pick
 * in conditions.gas_tank_choice (gasIndex => tankIndex, Pablo, 2026-10-15:
 * "I want the gases in each row, not the tank") always wins;
 * conditions.deleted_gas_mixes removes a gas from the candidate pool
 * entirely (soft - the gas_mixes column itself is never touched, same
 * posture as every other diver-entered fact in `conditions`).
 */
final class GasTankMatcher
{
    // Nitrox/O2 gases above this O2% are treated as deco gases (breathed
    // richer/shallower), not bottom/travel candidates - helium alone
    // (any amount) always counts as a bottom/travel candidate regardless
    // of O2%.
    private const DECO_O2_THRESHOLD = 40;

    // Below this surface-normalized bar/min, a pressure change is
    // treated as sensor noise/thermal drift rather than real breathing -
    // same noise-floor reasoning as the chart's own SAC series this
    // session (a rate can never be negative there for the same reason).
    private const NOISE_FLOOR_BAR_PER_MIN = 0.05;

    /**
     * @return array{tankToGas: array<int, int|null>, gasToTank: array<int, int>, labels: array<int, string>, roles: array<int, string>}
     */
    public static function suggest(Dive $dive): array
    {
        if ($dive->isCcr()) {
            [$map, $roles] = self::suggestCcr($dive);
        } elseif ($dive->isSidemount()) {
            [$map, $roles] = self::suggestSidemount($dive);
        } else {
            [$map, $roles] = self::suggestOc($dive);
        }

        $gasToTank = [];
        foreach ($map as $tankIdx => $gasIdx) {
            if ($gasIdx !== null) {
                $gasToTank[$gasIdx] = $tankIdx;
            }
        }

        return [
            'tankToGas' => $map,
            'gasToTank' => $gasToTank,
            'labels' => self::gasLabels($dive->gas_mixes ?? []),
            'roles' => $roles,
        ];
    }

    /**
     * OC sidemount: every cylinder is tagged "sidemount" and they share
     * the gas actually breathed - the one-gas-per-tank timing heuristic
     * in suggestOc() pairs a never-breathed configured gas (Air) with one
     * cylinder as "bottom" and the real gas as "travel" (seen on all 9 of
     * Pablo's sidemount dives). So: only gases the computer actually
     * switched to (profile currentGas) are candidates; the leanest of
     * them is the bottom gas on the first cylinder, any richer breathed
     * gas is deco; configured-but-never-breathed gases get no tank and no
     * role.
     *
     * @return array{0: array<int, int|null>, 1: array<int, string>}
     */
    private static function suggestSidemount(Dive $dive): array
    {
        $mixes = $dive->gas_mixes ?? [];
        $breathed = [];
        foreach ($dive->profile ?? [] as $sample) {
            $g = $sample['currentGas'] ?? null;
            if ($g !== null && isset($mixes[$g])) $breathed[$g] = $mixes[$g];
        }
        if (!$breathed && isset($mixes[0])) $breathed[0] = $mixes[0];
        uasort($breathed, fn ($a, $b) => ($a['oxygen'] ?? 0) <=> ($b['oxygen'] ?? 0));

        $roles = [];
        $first = true;
        foreach ($breathed as $gi => $mix) {
            $roles[$gi] = $first ? 'bottom' : 'deco';
            $first = false;
        }

        $map = [];
        foreach (array_keys($dive->tanks ?? []) as $ti) $map[$ti] = null;
        $bottomGas = array_key_first($breathed);
        if ($bottomGas !== null && array_key_exists(0, $map)) $map[0] = $bottomGas;

        return [$map, $roles];
    }

    /** @return array{0: array<int, int|null>, 1: array<int, string>} */
    private static function suggestCcr(Dive $dive): array
    {
        $tanks = $dive->tanks ?? [];
        $mixes = $dive->gas_mixes ?? [];

        $diluentIdx = null;
        foreach ($mixes as $i => $mix) {
            if (($mix['usage'] ?? null) === 2) {
                $diluentIdx = $i;
                break;
            }
        }

        $map = [];
        foreach ($tanks as $i => $tank) {
            $usage = $tank['usage'] ?? null;
            // Oxygen supply tanks have no gas_mix entry at all (pure O2
            // fed to the loop, not a breathing gas on its own) - same
            // rule $tankLabels already applies.
            $map[$i] = ($usage === 'diluent' && $diluentIdx !== null) ? $diluentIdx : null;
        }

        // Roles for the "extra" (non-diluent) gases a CCR diver might
        // still carry on a real AI-tracked bailout cylinder (Pablo,
        // 2026-10-15: "for CC, the dropdown will show bailout and deco...
        // only one bailout, but could be multiple decos") - leanest of
        // them is the suggested bailout, same leaner-is-the-one-you-
        // actually-bail-out-on convention as OC's own bottom gas.
        $extras = [];
        foreach ($mixes as $i => $mix) {
            if (($mix['usage'] ?? null) !== 2) {
                $extras[$i] = $mix;
            }
        }
        uasort($extras, fn ($a, $b) => ($a['oxygen'] ?? 0) <=> ($b['oxygen'] ?? 0));
        $roles = [];
        $first = true;
        foreach ($extras as $i => $mix) {
            $roles[$i] = $first ? 'bailout' : 'deco';
            $first = false;
        }

        return [$map, $roles];
    }

    /** @return array{0: array<int, int|null>, 1: array<int, string>} */
    private static function suggestOc(Dive $dive): array
    {
        $tanks = $dive->tanks ?? [];
        $mixes = $dive->gas_mixes ?? [];
        $deleted = $dive->conditions['deleted_gas_mixes'] ?? [];

        $map = [];
        foreach (array_keys($tanks) as $i) {
            $map[$i] = null;
        }
        $roles = [];

        $candidates = [];
        foreach ($mixes as $i => $mix) {
            if (!in_array($i, $deleted, true)) {
                $candidates[$i] = $mix;
            }
        }
        if (!count($candidates)) {
            return [$map, $roles];
        }

        $profile = $dive->profile ?? [];
        $timelines = [];
        foreach (array_keys($tanks) as $i) {
            $timeline = self::tankTimeline($profile, $i);
            if ($timeline !== null) {
                $timelines[$i] = $timeline;
            }
        }

        $bottomTravel = [];
        $deco = [];
        foreach ($candidates as $i => $mix) {
            $o2 = $mix['oxygen'] ?? 0;
            $he = $mix['helium'] ?? 0;
            if ($he > 0 || $o2 <= self::DECO_O2_THRESHOLD) {
                $bottomTravel[$i] = $mix;
            } else {
                $deco[$i] = $mix;
            }
        }
        foreach ($deco as $i => $mix) {
            $roles[$i] = 'deco';
        }

        if (count($timelines) < 2) {
            // No timing signal to disambiguate beyond a single
            // unambiguous tank/gas pair.
            if (count($timelines) === 1 && count($candidates) === 1) {
                $map[array_key_first($timelines)] = array_key_first($candidates);
            }
            foreach ($bottomTravel as $i => $mix) {
                $roles[$i] = 'bottom';
            }
            return [$map, $roles];
        }

        uasort($timelines, fn ($a, $b) => $a['windows'][0]['start'] <=> $b['windows'][0]['start']);
        $tanksByStartTime = array_keys($timelines);

        if (count($bottomTravel) > 1) {
            self::matchTravelGasScenario($map, $roles, $timelines, $tanksByStartTime, $bottomTravel, $deco);
        } else {
            self::matchSimpleScenario($map, $roles, $tanksByStartTime, $candidates, $bottomTravel);
        }

        // Every dive has exactly one "went down on it" gas - if nothing
        // ended up classified 'bottom' (e.g. several nitrox deco-range
        // gases with no clear low-O2/helium candidate at all), the
        // earliest-breathed tank's gas still deserves that role over
        // being left as just another 'deco' entry.
        if (!in_array('bottom', $roles, true) && count($tanksByStartTime)) {
            $earliestGas = $map[$tanksByStartTime[0]] ?? null;
            if ($earliestGas !== null) {
                $roles[$earliestGas] = 'bottom';
            }
        }

        return [$map, $roles];
    }

    /**
     * More than one bottom/travel-shaped gas is configured - most likely
     * a travel gas (surface-to-depth, then switched to the real bottom
     * mix, possibly switched back on ascent) rather than one bottom gas
     * plus deco gases alone.
     */
    private static function matchTravelGasScenario(array &$map, array &$roles, array $timelines, array $tanksByStartTime, array $bottomTravel, array $deco): void
    {
        $byTotal = $timelines;
        uasort($byTotal, fn ($a, $b) => $b['total'] <=> $a['total']);
        $bottomTankIdx = array_key_first($byTotal);

        uasort($bottomTravel, fn ($a, $b) => ($a['oxygen'] ?? 0) <=> ($b['oxygen'] ?? 0));
        $btKeys = array_keys($bottomTravel);
        $bottomGasIdx = array_shift($btKeys);
        $map[$bottomTankIdx] = $bottomGasIdx;
        $roles[$bottomGasIdx] = 'bottom';

        $remaining = array_values(array_filter($tanksByStartTime, fn ($i) => $i !== $bottomTankIdx));

        // A tank breathed in >=2 separate windows (paused in the middle,
        // where the bottom gas took over) reads as the travel gas.
        $travelTanks = array_values(array_filter($remaining, fn ($i) => count($timelines[$i]['windows']) >= 2));
        foreach ($travelTanks as $i) {
            if (!count($btKeys)) break;
            $gasIdx = array_shift($btKeys);
            $map[$i] = $gasIdx;
            $roles[$gasIdx] = 'travel';
            $remaining = array_values(array_diff($remaining, [$i]));
        }

        // Elimination for any bottom/travel candidates still unmatched -
        // no stronger signal than "not deco", so these default to bottom.
        foreach ($remaining as $i) {
            if (!count($btKeys)) break;
            $gasIdx = array_shift($btKeys);
            $map[$i] = $gasIdx;
            $roles[$gasIdx] = 'bottom';
        }
        foreach ($btKeys as $gasIdx) {
            $roles[$gasIdx] = 'bottom';
        }

        // Deco candidates go to whatever tanks are still unmapped, in
        // start-time order.
        uasort($deco, fn ($a, $b) => ($a['oxygen'] ?? 0) <=> ($b['oxygen'] ?? 0));
        $decoKeys = array_keys($deco);
        foreach ($tanksByStartTime as $i) {
            if ($map[$i] !== null || !count($decoKeys)) continue;
            $map[$i] = array_shift($decoKeys);
        }
    }

    /**
     * At most one bottom/travel gas configured - the straightforward
     * case Pablo's own example describes: the tank breathed from the
     * start pairs with the leanest gas, and later-starting tanks pair
     * with progressively richer ones (deco gases get richer the
     * shallower/later they're used).
     */
    private static function matchSimpleScenario(array &$map, array &$roles, array $tanksByStartTime, array $candidates, array $bottomTravel): void
    {
        uasort($candidates, fn ($a, $b) => ($a['oxygen'] ?? 0) <=> ($b['oxygen'] ?? 0));
        $gasKeys = array_keys($candidates);
        foreach ($tanksByStartTime as $i) {
            if (!count($gasKeys)) break;
            $map[$i] = array_shift($gasKeys);
        }
        foreach ($bottomTravel as $i => $mix) {
            $roles[$i] = 'bottom';
        }
    }

    private static function tankPressureAt(array $sample, int $tankIndex): ?float
    {
        $tp = $sample['tankPressures'] ?? null;
        if ($tp === null) {
            return null;
        }
        if (array_is_list($tp)) {
            return $tp[$tankIndex] ?? null;
        }
        return $tp[(string) $tankIndex] ?? null;
    }

    /**
     * @return array{total: float, windows: list<array{start: float, end: float}>}|null
     */
    private static function tankTimeline(array $profile, int $tankIndex): ?array
    {
        $total = 0.0;
        $windows = [];
        $current = null;
        $prev = null;

        foreach ($profile as $sample) {
            $p = self::tankPressureAt($sample, $tankIndex);
            if ($p === null || !isset($sample['time'])) {
                $prev = null;
                continue;
            }

            if ($prev !== null) {
                $prevP = self::tankPressureAt($prev, $tankIndex);
                $dtMin = ($sample['time'] - $prev['time']) / 60;
                if ($prevP !== null && $dtMin > 0) {
                    $avgDepth = (($prev['depth'] ?? 0) + ($sample['depth'] ?? 0)) / 2;
                    $ata = 1 + $avgDepth / 10;
                    $deltaP = max(0, $prevP - $p);
                    $total += $deltaP;
                    $rate = ($deltaP / $dtMin) / $ata;

                    if ($rate > self::NOISE_FLOOR_BAR_PER_MIN) {
                        $current = $current === null
                            ? ['start' => $prev['time'], 'end' => $sample['time']]
                            : ['start' => $current['start'], 'end' => $sample['time']];
                    } elseif ($current !== null) {
                        $windows[] = $current;
                        $current = null;
                    }
                }
            }

            $prev = $sample;
        }
        if ($current !== null) {
            $windows[] = $current;
        }

        return count($windows) ? ['total' => $total, 'windows' => $windows] : null;
    }

    /**
     * Role picker options (Pablo, 2026-10-15: "below the gas pill...a
     * small dropdown to select if the gas is bottom gas, deco or travel
     * in open circuit...for CC, the dropdown will show bailout and
     * deco"). One vocabulary per mode - a CCR diver's extra gas is never
     * a "travel" gas and an OC diver never has a "bailout".
     *
     * @return array<string, string>
     */
    public static function roleOptions(bool $isCcr): array
    {
        return $isCcr
            ? ['bailout' => 'Bailout', 'deco' => 'Deco gas']
            : ['bottom' => 'Bottom gas', 'deco' => 'Deco gas', 'travel' => 'Travel gas'];
    }

    /** Same "Air"/"Pure O2"/O2-He text convention as <x-gas-mix-pill>. */
    public static function gasLabel(array $mix): string
    {
        $o2 = (int) round($mix['oxygen'] ?? 0);
        $he = (int) round($mix['helium'] ?? 0);
        if ($he === 0 && $o2 === 21) return 'Air';
        if ($he === 0 && $o2 === 100) return 'Pure O2';
        if ($he === 0) return 'EAN' . $o2;
        return $o2 . '/' . $he;
    }

    /** @return array<int, string> */
    private static function gasLabels(array $mixes): array
    {
        $labels = [];
        foreach ($mixes as $i => $mix) {
            $labels[$i] = self::gasLabel($mix);
        }
        return $labels;
    }
}

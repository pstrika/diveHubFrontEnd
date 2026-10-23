<?php

namespace App\Support;

use App\Models\Dive;

/**
 * The profile and tanks a dive's chart shows: the main computer's, with
 * tank pressure and deco filled in from another computer worn on the same
 * dive when the main one had none (a Petrel on the loop with the NERD's
 * transmitters, a MacDive export with no stops). Samples are aligned by
 * time into the dive, so computers whose clocks disagree still line up.
 *
 * Moved out of LogbookController::computerLogs() (2026-10-21) so the app
 * API returns the same chart the web shows: the iOS app showed no tank
 * pressure at all on a Petrel + NERD dive, because it only ever received
 * the Petrel's log.
 */
class DiveChartData
{
    /**
     * @param iterable<Dive>|null $logs the dive's companions, when already loaded
     * @return array{profile: array, tanks: ?array, pressure_from: ?string, deco_from: ?string}
     */
    public static function resolve(Dive $dive, $logs = null): array
    {
        $profile = $dive->profile ?? [];
        $tanks = $dive->tanks;
        $logs ??= $dive->companions()->get();
        $decoFrom = null;

        $pressureFrom = null;
        $mainHasPressure = collect($profile)->contains(fn ($s) => isset($s['tankPressures']) || isset($s['pressure']));
        if (!$mainHasPressure && $profile) {
            foreach ($logs as $c) {
                $src = array_values(array_filter($c->profile ?? [], fn ($s) => isset($s['time']) && (isset($s['tankPressures']) || isset($s['pressure']))));
                if (!$src) continue;
                $j = 0; $n = count($src);
                foreach ($profile as &$s) {
                    if (!isset($s['time'])) continue;
                    while ($j < $n - 1 && $src[$j + 1]['time'] <= $s['time']) $j++;
                    if ($src[$j]['time'] > $s['time'] + 30) continue; // before the other computer's first reading
                    $s['tankPressures'] = $src[$j]['tankPressures'] ?? [$src[$j]['pressure']];
                }
                unset($s);
                // The main log's cylinders carry no real pressures (MacDive
                // lists the diluent with 0 bar): use the other computer's
                // cylinder list - one per transmitter - keeping the main
                // log's role for each (diluent) and, on a rebreather, the
                // remaining transmitter cylinder as the oxygen (Pablo's
                // NERD: diluent + O2 sensors).
                $tanks = self::borrowTanks($dive, $tanks, $c, count($src[0]['tankPressures'] ?? [1]));
                $pressureFrom = $c->computer_model ?: 'another computer';
                break;
            }
        }
        // Deco the same way (Pablo, 2026-10-16: "what about the
        // decompression profile missing"): a logbook export (MacDive) keeps
        // no stops; the NERD/Perdix worn on the same dive did. When the
        // main computer has no deco data at all, its samples take stops,
        // stop time, TTS and NDL from the attached computer that has them.
        $mainHasDeco = collect($profile)->contains(fn ($s) => isset($s['decoKind']) || isset($s['tts']));
        if (!$mainHasDeco && $profile) {
            foreach ($logs as $c) {
                $src = array_values(array_filter($c->profile ?? [], fn ($s) => isset($s['time']) && (isset($s['decoKind']) || isset($s['tts']))));
                if (!collect($src)->contains(fn ($s) => ($s['decoKind'] ?? '') === 'deco_stop' || ($s['tts'] ?? 0) > 0)) continue;
                $j = 0; $n = count($src);
                foreach ($profile as &$s) {
                    if (!isset($s['time'])) continue;
                    while ($j < $n - 1 && $src[$j + 1]['time'] <= $s['time']) $j++;
                    if ($src[$j]['time'] > $s['time'] + 30) continue;
                    foreach (['decoKind', 'decoStop', 'decoTime', 'tts'] as $k) if (isset($src[$j][$k])) $s[$k] = $src[$j][$k];
                    if (!isset($s['ndl']) && isset($src[$j]['ndl'])) $s['ndl'] = $src[$j]['ndl'];
                }
                unset($s);
                $decoFrom = $c->computer_model ?: 'another computer';
                break;
            }
        }
        return ['profile' => $profile, 'tanks' => $tanks, 'pressure_from' => $pressureFrom, 'deco_from' => $decoFrom];
    }

    /**
     * The other computer's cylinder list in place of the main log's
     * pressure-less one (one per transmitter), keeping the main log's role
     * for each (diluent) and, on a rebreather, the remaining transmitter
     * cylinder as the oxygen (Pablo's NERD: diluent + O2 sensors).
     */
    public static function borrowTanks(Dive $dive, ?array $tanks, Dive $c, int $width): ?array
    {
        $mainHasPressures = collect($tanks ?? [])->contains(fn ($t) => ($t['beginPressure'] ?? 0) > 0 || ($t['endPressure'] ?? 0) > 0);
        if ($mainHasPressures || !$c->tanks) return $tanks;
        $borrowed = array_values($c->tanks);
        while (count($borrowed) < $width) $borrowed[] = ['usage' => 'none', 'volume' => 0, 'workPressure' => 0];
        foreach ($borrowed as $ti => &$bt) {
            $mainUsage = $tanks[$ti]['usage'] ?? null;
            if ($mainUsage && $mainUsage !== 'none') $bt['usage'] = $mainUsage;
            if (isset($tanks[$ti]['gasMix'])) $bt['gasMix'] = $tanks[$ti]['gasMix'];
        }
        unset($bt);
        if ($dive->isCcr()) {
            $roles = array_column($borrowed, 'usage');
            $hasDil = in_array('diluent', $roles, true); $hasO2 = in_array('oxygen', $roles, true);
            foreach ($borrowed as $ti => $bt) {
                if (($bt['usage'] ?? 'none') !== 'none') continue;
                if ($hasDil && !$hasO2) { $borrowed[$ti]['usage'] = 'oxygen'; $hasO2 = true; }
                elseif (!$hasDil) { $borrowed[$ti]['usage'] = 'diluent'; $hasDil = true; }
            }
        }
        return $borrowed;
    }

    /**
     * Just the cylinders (no profile work) for the app's sync feed: the main
     * log's own when it has pressures, else the first attached log's that
     * does. Works on dives loaded without their profile JSON.
     *
     * @return array{tanks: ?array, pressure_from: ?string}
     */
    public static function tanksFor(Dive $dive, $logs): array
    {
        $own = collect($dive->tanks ?? [])->contains(fn ($t) => ($t['beginPressure'] ?? 0) > 0 || ($t['endPressure'] ?? 0) > 0);
        if ($own) return ['tanks' => $dive->tanks, 'pressure_from' => null];
        foreach ($logs as $c) {
            $has = collect($c->tanks ?? [])->contains(fn ($t) => ($t['beginPressure'] ?? 0) > 0 || ($t['endPressure'] ?? 0) > 0);
            if (!$has) continue;
            return ['tanks' => self::borrowTanks($dive, $dive->tanks, $c, count($c->tanks)), 'pressure_from' => $c->computer_model ?: 'another computer'];
        }
        return ['tanks' => $dive->tanks, 'pressure_from' => null];
    }
}

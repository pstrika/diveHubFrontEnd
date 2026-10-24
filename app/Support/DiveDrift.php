<?php

namespace App\Support;

use App\Models\Dive;

/**
 * Entry & Exit (Pablo, 2026-10-24): a computer that fixes GPS on the way
 * in and back at the surface (Garmin Descent) gives the dive two points.
 * From them: the distance drifted, and the current in knots over the time
 * the diver was actually drifting - from leaving the wreck on a wreck
 * dive (the start of the final ascent), from the start of the dive
 * otherwise. Any attached log may own the points (a Descent worn next
 * to a Petrel): the card follows that log's own timeline.
 *
 * location = {latitude, longitude, exit_latitude, exit_longitude} on the
 * dive row; without both points there is no card.
 */
class DiveDrift
{
    /** Fallback only (no usable profile shape): the last time below this share of the max depth. */
    private const BOTTOM_SHARE = 0.6;
    /** The final ascent is under way while the diver climbs at least this much per minute. */
    private const ASCENT_M_PER_MIN = 3.0;

    /**
     * @param iterable<Dive>|null $logs the dive's attached logs, when already loaded
     * @return array{entry: array{lat: float, lon: float}, exit: array{lat: float, lon: float}, distance_m: float, from: ?string,
     *               wreck: bool, start_seconds: int, exit_seconds: int, drift_seconds: int, knots: ?float}|null
     */
    public static function resolve(Dive $dive, $logs = null): ?array
    {
        $owner = null;
        if (self::hasBoth($dive->location)) $owner = $dive;
        else {
            foreach ($logs ?? $dive->companions()->get() as $c) {
                if (self::hasBoth($c->location)) { $owner = $c; break; }
            }
        }
        if (!$owner) return null;
        $loc = $owner->location;
        $entry = ['lat' => (float) $loc['latitude'], 'lon' => (float) $loc['longitude']];
        $exit = ['lat' => (float) $loc['exit_latitude'], 'lon' => (float) $loc['exit_longitude']];
        $distance = self::meters($entry['lat'], $entry['lon'], $exit['lat'], $exit['lon']);

        $wreck = self::isWreckDive($dive);
        $profile = $owner->profile ?? [];
        $exitAt = max((int) $owner->duration_seconds, $profile ? (int) (end($profile)['time'] ?? 0) : 0);
        $start = $wreck ? self::bottomEnd($profile) : 0;
        if ($start >= $exitAt) $start = 0;
        $hasDeco = $wreck && collect($profile)->contains(fn ($s) => ($s['decoKind'] ?? null) === 'deco_stop' && ($s['decoStop'] ?? 0) > 0);
        $drift = $exitAt - $start;
        $knots = $drift >= 60 ? round(($distance / 1852) / ($drift / 3600), 2) : null;

        return [
            'entry' => $entry,
            'exit' => $exit,
            'distance_m' => round($distance, 1),
            'from' => $owner->id === $dive->id ? null : ($owner->computer_model ?: 'another computer'),
            'wreck' => $wreck,
            'start_seconds' => $start,
            'ascent_end_seconds' => $hasDeco ? $start : null, // non-null: the walk-back ran from the first deco stop
            'exit_seconds' => $exitAt,
            'drift_seconds' => $drift,
            'knots' => $knots,
        ];
    }

    public static function hasBoth($loc): bool
    {
        return is_array($loc) && isset($loc['latitude'], $loc['longitude'], $loc['exit_latitude'], $loc['exit_longitude']);
    }

    /** The site is a wreck, or the diver tagged the dive as a wreck dive. */
    public static function isWreckDive(Dive $dive): bool
    {
        if (in_array('wreck', $dive->conditions['dive_type'] ?? [], true)) return true;
        return ($dive->siteInfo()['type'] ?? null) === 'wreck';
    }

    /**
     * When the diver left the wreck (seconds into the log): the start of
     * the final ascent. Found from where that ascent ends - the arrival
     * at the first deco stop, or on a no-deco dive the first time after
     * the deepest point the diver is above a third of the max depth - by
     * walking back while the climb stays at least 3 m per minute. Where
     * the climb was slower than that the diver was still on the wreck
     * (Pablo, 2026-10-24, Clinton: left at 27:31, first stop at 33:30).
     * Falls back to the last time below 60% of the max depth.
     */
    public static function bottomEnd(array $profile): int
    {
        $pts = [];
        foreach ($profile as $s) if (isset($s['depth'], $s['time'])) $pts[] = ['t' => (int) $s['time'], 'd' => (float) $s['depth'], 'kind' => $s['decoKind'] ?? null, 'stop' => isset($s['decoStop']) ? (float) $s['decoStop'] : 0.0];
        if (count($pts) < 3) return 0;
        $max = max(array_column($pts, 'd'));
        if ($max <= 0) return 0;

        // Last time near the max depth: the final ascent starts after it.
        $deepIdx = 0;
        foreach ($pts as $i => $p) if ($p['d'] >= $max * 0.85) $deepIdx = $i;

        // Where the final ascent ends.
        $arrival = null;
        for ($i = $deepIdx + 1, $n = count($pts); $i < $n; $i++) {
            $p = $pts[$i];
            if ($p['kind'] === 'deco_stop' && $p['stop'] > 0 && $p['d'] <= $p['stop'] + 1.5) { $arrival = $i; break; }
        }
        if ($arrival === null) {
            $target = max(6.0, $max / 3);
            for ($i = $deepIdx + 1, $n = count($pts); $i < $n; $i++) {
                if ($pts[$i]['d'] <= $target) { $arrival = $i; break; }
            }
        }
        if ($arrival === null) return self::bottomEndByShare($pts, $max);

        // Walk back while the previous half minute was a real climb. A pause
        // on the way up (a deep stop, a gas switch - Clinton: a minute at
        // 32 m between the wreck at 49 m and the first stop) is bridged when
        // the diver was 3 m deeper within the 4 minutes before it; a stall
        // near the max depth is the wreck itself, so the walk ends there.
        $i = $arrival;
        while ($i > 0) {
            $t = $pts[$i]['t'];
            $j = $i;
            while ($j > 0 && $pts[$j]['t'] > $t - 30) $j--;
            $span = $t - $pts[$j]['t'];
            if ($span <= 0) break;
            $rate = ($pts[$j]['d'] - $pts[$i]['d']) * 60 / $span;
            if ($rate >= self::ASCENT_M_PER_MIN) { $i--; continue; }
            $deeper = null;
            if ($pts[$i]['d'] < $max * 0.85) {
                for ($k = $i - 1; $k >= 0 && $pts[$k]['t'] >= $t - 240; $k--) {
                    if ($pts[$k]['d'] >= $pts[$i]['d'] + 3.0) { $deeper = $k; break; }
                }
            }
            if ($deeper === null) break;
            $i = $deeper;
        }
        // The first seconds of an ascent are slow: step back while each
        // sample is deeper than the next, up to a minute.
        $t0 = $pts[$i]['t'];
        while ($i > 0 && $pts[$i - 1]['d'] >= $pts[$i]['d'] + 0.05 && $pts[$i - 1]['t'] >= $t0 - 60) $i--;
        return $pts[$i]['t'];
    }

    private static function bottomEndByShare(array $pts, float $max): int
    {
        $last = 0;
        foreach ($pts as $p) if ($p['d'] >= $max * self::BOTTOM_SHARE) $last = $p['t'];
        return $last;
    }

    public static function meters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $r = 6371000;
        $dLat = deg2rad($lat2 - $lat1); $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        return 2 * $r * atan2(sqrt($a), sqrt(1 - $a));
    }
}

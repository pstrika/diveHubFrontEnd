<?php

namespace App\Support;

use App\Models\Dive;

/**
 * Is this the same dive another computer already logged? (Pablo,
 * 2026-10-16: Petrel on the rEvo, Perdix with the transmitters and a NERD
 * all log one dive; "computers may have different hours configured. So
 * probably you need to add date, time, similar depth and similar dive
 * duration".)
 *
 * A candidate must agree on max depth and duration. Then:
 *   strong   - starts within STRONG_WINDOW: same dive, clocks agree.
 *   possible - starts up to CLOCK_WINDOW apart (a computer still on home
 *              time, or never set): asked, never assumed. Ranked by how
 *              close the gap is to whole hours and by profile shape.
 * Profile shape (both depth curves resampled over their own duration)
 * separates two similar dives on one day, e.g. a 20 m/45 min morning dive
 * and a 20 m/45 min afternoon dive.
 *
 * Same-computer matches (same serial) are NOT this class's business - that
 * is the existing exact/time duplicate logic.
 */
class SameDiveMatcher
{
    public const STRONG_WINDOW = 300;      // s
    public const CLOCK_WINDOW = 24 * 3600; // s - same day or next, any clock setting

    /** Different physical computers? Only when both serials are known. */
    public static function differentComputers(?string $a, ?string $b): bool
    {
        $a = strtolower(trim((string) $a)); $b = strtolower(trim((string) $b));
        return $a !== '' && $b !== '' && $a !== $b;
    }

    public static function sameComputer(?string $a, ?string $b): bool
    {
        $a = strtolower(trim((string) $a)); $b = strtolower(trim((string) $b));
        return $a !== '' && $a === $b;
    }

    public static function depthsAgree(float $a, float $b): bool
    {
        return abs($a - $b) <= max(1.5, 0.07 * max($a, $b));
    }

    public static function durationsAgree(int $a, int $b): bool
    {
        return abs($a - $b) <= max(240, 0.12 * max($a, $b));
    }

    /** Tight agreement: what two computers on the same diver actually show. */
    public static function depthsMatchClosely(float $a, float $b): bool
    {
        // A few feet between computers is normal (sensor calibration,
        // where each is worn) - Pablo, 2026-10-16. Duration is the tight
        // one: every computer starts on contact with water.
        return abs($a - $b) <= max(1.5, 0.05 * max($a, $b));
    }

    public static function durationsMatchClosely(int $a, int $b): bool
    {
        return abs($a - $b) <= max(120, 0.04 * max($a, $b));
    }

    /**
     * 0..1, how alike two depth curves are over their own duration, each
     * scaled to its own max depth (so a 0.3 m calibration difference
     * between computers doesn't count). null without usable profiles.
     */
    public static function shapeSimilarity(?array $pa, ?array $pb): ?float
    {
        $ra = self::resample($pa ?? []); $rb = self::resample($pb ?? []);
        if (!$ra || !$rb) return null;
        $ma = max($ra) ?: 1.0; $mb = max($rb) ?: 1.0;
        $diff = 0.0;
        foreach ($ra as $i => $v) $diff += abs($v / $ma - $rb[$i] / $mb);
        return max(0.0, 1 - ($diff / count($ra)) * 4);
    }

    /** Pearson correlation of the two resampled depth curves (-1..1), null without profiles. */
    public static function correlation(?array $pa, ?array $pb): ?float
    {
        $ra = self::resample($pa ?? []); $rb = self::resample($pb ?? []);
        if (!$ra || !$rb) return null;
        $n = count($ra); $ma = array_sum($ra) / $n; $mb = array_sum($rb) / $n;
        $c = 0.0; $va = 0.0; $vb = 0.0;
        foreach ($ra as $i => $v) { $c += ($v - $ma) * ($rb[$i] - $mb); $va += ($v - $ma) ** 2; $vb += ($rb[$i] - $mb) ** 2; }
        return ($va > 0 && $vb > 0) ? $c / sqrt($va * $vb) : null;
    }

    /** Depth at 60 evenly spaced points through the dive. */
    private static function resample(array $profile, int $n = 60): ?array
    {
        $pts = [];
        foreach ($profile as $s) if (isset($s['time'], $s['depth'])) $pts[] = [(float) $s['time'], (float) $s['depth']];
        if (count($pts) < 5) return null;
        $t0 = $pts[0][0]; $t1 = end($pts)[0];
        if ($t1 - $t0 <= 0) return null;
        $out = []; $j = 0;
        for ($i = 0; $i < $n; $i++) {
            $t = $t0 + ($t1 - $t0) * $i / ($n - 1);
            while ($j < count($pts) - 2 && $pts[$j + 1][0] < $t) $j++;
            [$ta, $da] = $pts[$j]; [$tb, $db] = $pts[$j + 1] ?? $pts[$j];
            $out[] = $tb > $ta ? $da + ($db - $da) * ($t - $ta) / ($tb - $ta) : $da;
        }
        return $out;
    }

    /**
     * Score a candidate against a dive-in-hand. The clock is NOT trusted
     * (Pablo, 2026-10-16: "do not use the time (unless you see a match).
     * You need to be looking at depth profile and duration"):
     *   strong   - depth and duration agree tightly and the depth curves
     *              correlate >= 0.97, whatever the clocks say; or the
     *              clocks agree (<= 5 min) and depth/duration agree.
     *   possible - looser resemblance: asked, never assumed.
     * Measured on Pablo's Petrel/NERD pairs (NERD clock 28 min ahead):
     * true pairs 0.2-0.8 m, 1-22 s, r 0.98-0.999; the closest different
     * dive 0.9 m, 161 s, r 0.94.
     * Returns null, or ['level', 'offset' (candidate - incoming, s),
     * 'shape' (0..1|null), 'corr', 'score'].
     */
    public static function compare(array $in, Dive $cand): ?array
    {
        if (!$cand->started_at) return null;
        $offset = $cand->started_at->getTimestamp() - strtotime($in['started_at']);
        if (abs($offset) > self::CLOCK_WINDOW) return null;
        $dIn = (float) $in['max_depth_m']; $dC = (float) $cand->max_depth_m;
        $tIn = (int) $in['duration_seconds']; $tC = (int) $cand->duration_seconds;
        if (!self::depthsAgree($dIn, $dC) || !self::durationsAgree($tIn, $tC)) return null;
        $shape = self::shapeSimilarity($in['profile'] ?? null, $cand->profile);
        $corr = self::correlation($in['profile'] ?? null, $cand->profile);
        $close = self::depthsMatchClosely($dIn, $dC) && self::durationsMatchClosely($tIn, $tC);
        $clockAgrees = abs($offset) <= self::STRONG_WINDOW;
        // Closeness for ranking several candidates: profile first, then
        // depth/duration agreement, a little for agreeing clocks.
        $score = ($corr ?? 0.5) * 2 + ($shape ?? 0.5)
            - abs($dIn - $dC) / max(1.0, $dC) - abs($tIn - $tC) / max(60, $tC)
            + ($clockAgrees ? 0.3 : 0);

        if ($close && $corr !== null && $corr >= 0.97 && ($shape === null || $shape >= 0.8)) {
            return ['level' => 'strong', 'offset' => $offset, 'shape' => $shape, 'corr' => $corr, 'score' => $score + 2];
        }
        // Durations within seconds of each other (every computer starts on
        // contact with water) carry the match even for a shallow, flat
        // dive whose curve correlates less cleanly - Pablo's second dives
        // of the day: 11-13 s apart, r 0.88-0.90.
        $tDiff = abs($tIn - $tC);
        if ($close && $tDiff <= max(30, 0.01 * max($tIn, $tC)) && $corr !== null && $corr >= 0.85) {
            return ['level' => 'strong', 'offset' => $offset, 'shape' => $shape, 'corr' => $corr, 'score' => $score + 2];
        }
        if ($clockAgrees && ($corr === null || $corr >= 0.8)) {
            return ['level' => 'strong', 'offset' => $offset, 'shape' => $shape, 'corr' => $corr, 'score' => $score + 2];
        }
        if (($corr !== null && $corr >= 0.9) || ($corr === null && $close)) {
            return ['level' => 'possible', 'offset' => $offset, 'shape' => $shape, 'corr' => $corr, 'score' => $score];
        }
        return null;
    }

    /**
     * Best dive of this diver that another computer logged and that looks
     * like the dive in hand. Candidates are main dives (not attached logs);
     * a dive that already has a log from THIS computer is skipped (that's
     * a plain duplicate, not a second computer).
     *
     * @param array $in started_at, duration_seconds, max_depth_m, computer_serial, profile?
     */
    public static function find(int $userId, array $in, ?int $excludeId = null): ?array
    {
        $ts = strtotime($in['started_at']);
        $q = Dive::where('user_id', $userId)
            ->whereBetween('started_at', [date('Y-m-d H:i:s', $ts - self::CLOCK_WINDOW), date('Y-m-d H:i:s', $ts + self::CLOCK_WINDOW)])
            ->with(['companions' => fn ($c) => $c->select('id', 'merged_into_id', 'computer_serial')]);
        if ($excludeId) $q->where('id', '!=', $excludeId);
        $best = null;
        foreach ($q->get() as $cand) {
            if (!self::differentComputers($in['computer_serial'] ?? null, $cand->computer_serial)) continue;
            if ($cand->companions->contains(fn ($c) => self::sameComputer($c->computer_serial, $in['computer_serial'] ?? null))) continue;
            $m = self::compare($in, $cand);
            if ($m && (!$best || $m['score'] > $best['score'])) $best = $m + ['dive' => $cand];
        }
        return $best;
    }

    /** "2 h 03 min" for a clock offset in seconds. */
    public static function offsetLabel(int $seconds): string
    {
        $s = abs($seconds);
        if ($s < 60) return $s . ' s';
        $h = intdiv($s, 3600); $m = intdiv($s % 3600, 60);
        return $h ? sprintf('%d h %02d min', $h, $m) : $m . ' min';
    }
}

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
    public const CLOCK_WINDOW = 14 * 3600; // s

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

    /**
     * 0..1, how alike two depth curves are over their own duration (1 =
     * identical). null when either has no usable profile.
     */
    public static function shapeSimilarity(?array $pa, ?array $pb): ?float
    {
        $ra = self::resample($pa ?? []); $rb = self::resample($pb ?? []);
        if (!$ra || !$rb) return null;
        $scale = max(max($ra), max($rb), 1.0);
        $diff = 0.0;
        foreach ($ra as $i => $v) $diff += abs($v - $rb[$i]);
        return max(0.0, 1 - ($diff / count($ra)) / $scale * 4); // 25% mean gap -> 0
    }

    /** Depth at 40 evenly spaced points through the dive. */
    private static function resample(array $profile, int $n = 40): ?array
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
     * Score a candidate against a dive-in-hand. Returns null when they
     * can't be the same dive, else ['level' => strong|possible,
     * 'offset' => seconds (candidate - incoming), 'shape' => 0..1|null,
     * 'score' => higher is better].
     */
    public static function compare(array $in, Dive $cand): ?array
    {
        if (!$cand->started_at) return null;
        $offset = $cand->started_at->getTimestamp() - strtotime($in['started_at']);
        if (abs($offset) > self::CLOCK_WINDOW) return null;
        if (!self::depthsAgree((float) $in['max_depth_m'], (float) $cand->max_depth_m)) return null;
        if (!self::durationsAgree((int) $in['duration_seconds'], (int) $cand->duration_seconds)) return null;
        $shape = self::shapeSimilarity($in['profile'] ?? null, $cand->profile);
        if ($shape !== null && $shape < 0.55) return null; // clearly a different dive shape

        if (abs($offset) <= self::STRONG_WINDOW) {
            return ['level' => 'strong', 'offset' => $offset, 'shape' => $shape, 'score' => 2 + ($shape ?? 0.5) - abs($offset) / 3600];
        }
        // Clock set wrong: usually whole hours (time zones) off, give or
        // take a few minutes. Anything else needs a convincing shape.
        $fromHour = abs(abs($offset) - round(abs($offset) / 3600) * 3600);
        $hourish = $fromHour <= 600;
        if (!$hourish && ($shape === null || $shape < 0.8)) return null;
        return ['level' => 'possible', 'offset' => $offset, 'shape' => $shape, 'score' => 1 + ($shape ?? 0.4) + ($hourish ? 0.3 : 0)];
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

<?php

namespace App\Support;

use App\Models\Dive;
use App\Models\DiveChange;
use Illuminate\Support\Facades\DB;

/**
 * Merge two consecutive dives into one (Pablo, 2026-10-16: "not unusual
 * that the diver comes to the surface, the computer considers it's the
 * end of the dive and then goes back again. The computer will upload two
 * dives, in reality was only one").
 *
 * The FIRST dive is kept and extended (same id, uuid and fingerprint, so
 * the computer's re-sync of it stays a duplicate); the SECOND is soft-
 * deleted with merged_into_id set, so its fingerprint stays on file and
 * a re-upload lands in DiveUploadController's `deleted` bucket instead of
 * resurrecting the half. Both halves' profiles are stitched with the
 * surface interval filled at depth 0, so the chart shows the dive as it
 * happened.
 */
class DiveMerger
{
    /** How long after the first dive ends the second may start. */
    public const MAX_GAP_SECONDS = 3600;

    /**
     * The next dive this diver logged that started within MAX_GAP_SECONDS
     * of this one's end - on the same computer when both say which.
     */
    public static function nextCandidate(Dive $dive): ?Dive
    {
        if (!$dive->started_at) return null;
        $end = $dive->started_at->copy()->addSeconds((int) $dive->duration_seconds);

        $q = Dive::where('user_id', $dive->user_id)
            ->where('id', '!=', $dive->id)
            ->where('started_at', '>', $dive->started_at)
            ->where('started_at', '<=', $end->copy()->addSeconds(self::MAX_GAP_SECONDS))
            ->orderBy('started_at');
        if ($dive->computer_serial) {
            $q->where(fn ($w) => $w->whereNull('computer_serial')->orWhere('computer_serial', $dive->computer_serial));
        }
        // Id first, then the row: sorting whole rows (profile JSON) can
        // overflow MySQL's sort buffer (2026-10-17).
        $id = $q->value('id');
        return $id ? Dive::find($id) : null;
    }

    /** Seconds between the first dive's end and the second's start (never negative). */
    public static function gapSeconds(Dive $first, Dive $second): int
    {
        $end = $first->started_at->copy()->addSeconds((int) $first->duration_seconds);
        return max(0, $second->started_at->getTimestamp() - $end->getTimestamp());
    }

    public static function merge(Dive $first, Dive $second): Dive
    {
        if ($first->user_id !== $second->user_id) {
            throw new \InvalidArgumentException('Dives belong to different divers.');
        }
        if ($second->started_at->lt($first->started_at)) {
            [$first, $second] = [$second, $first];
        }

        return DB::transaction(function () use ($first, $second) {
            $gap = self::gapSeconds($first, $second);

            // Gas list: the first's, plus any of the second's it lacks;
            // the second's sample currentGas indexes are remapped.
            $mixes = $first->gas_mixes ?? [];
            $gasMap = [];
            foreach ($second->gas_mixes ?? [] as $i => $mix) {
                $found = null;
                foreach ($mixes as $j => $m) {
                    if (self::sameMix($m, $mix)) { $found = $j; break; }
                }
                if ($found === null) { $mixes[] = $mix; $found = count($mixes) - 1; }
                $gasMap[$i] = $found;
            }

            // Profile: first + surface fill + second shifted.
            $a = $first->profile ?? [];
            $b = $second->profile ?? [];
            $interval = self::sampleInterval($a) ?? self::sampleInterval($b) ?? 10;
            $aEnd = (int) $first->duration_seconds;
            if ($a) $aEnd = max($aEnd, (int) ($a[count($a) - 1]['time'] ?? 0));
            $merged = $a;
            $tankCount = max(count($first->tanks ?? []), count($second->tanks ?? []));
            for ($t = $aEnd + $interval; $t < $aEnd + $gap; $t += $interval) {
                $merged[] = ['time' => $t, 'depth' => 0, 'surface' => true];
            }
            $offset = $aEnd + $gap;
            foreach ($b as $s) {
                $s['time'] = (int) round(($s['time'] ?? 0) + $offset);
                if (isset($s['currentGas']) && array_key_exists($s['currentGas'], $gasMap)) {
                    $s['currentGas'] = $gasMap[$s['currentGas']];
                }
                $merged[] = $s;
            }

            // Tanks: index-aligned (same physical cylinders on both halves) -
            // start pressure from the first, end pressure from the second.
            $tanks = [];
            for ($i = 0; $i < $tankCount; $i++) {
                $ta = $first->tanks[$i] ?? null;
                $tb = $second->tanks[$i] ?? null;
                if ($ta && $tb) {
                    $tank = $ta;
                    if (isset($tb['endPressure'])) $tank['endPressure'] = $tb['endPressure'];
                    $tanks[] = $tank;
                } else {
                    $tanks[] = $ta ?? $tb;
                }
            }

            $durA = (int) $first->duration_seconds;
            $durB = (int) $second->duration_seconds;
            $total = $durA + $gap + $durB;
            $avg = null;
            if ($first->avg_depth_m !== null || $second->avg_depth_m !== null) {
                $avg = $total > 0 ? round((((float) $first->avg_depth_m) * $durA + ((float) $second->avg_depth_m) * $durB) / $total, 2) : null;
            }

            $first->duration_seconds = $total;
            $first->max_depth_m = max((float) $first->max_depth_m, (float) $second->max_depth_m);
            $first->avg_depth_m = $avg;
            $first->min_water_temp_c = self::minOf($first->min_water_temp_c, $second->min_water_temp_c);
            $first->max_water_temp_c = self::maxOf($first->max_water_temp_c, $second->max_water_temp_c);
            $first->gas_mixes = $mixes ?: null;
            $first->tanks = $tanks ?: null;
            $first->profile = $merged;
            $first->tags = array_values(array_unique(array_merge($first->tags ?? [], $second->tags ?? [])));
            if ($second->notes && $second->notes !== $first->notes) {
                $first->notes = trim(($first->notes ? $first->notes . "\n\n" : '') . $second->notes);
            }
            // Conditions: the first's win, the second's fill the blanks.
            $conditions = $first->conditions ?? [];
            foreach ($second->conditions ?? [] as $k => $v) {
                if (!isset($conditions[$k]) || $conditions[$k] === '' || $conditions[$k] === []) $conditions[$k] = $v;
            }
            if (!$first->site_id && !$first->user_site_id) {
                $first->site_id = $second->site_id;
                $first->user_site_id = $second->user_site_id;
            }
            if ($first->site_id || $first->user_site_id) unset($conditions['site_name_custom']);
            $first->conditions = $conditions ?: null;
            $first->save();
            DiveChange::record($first->user_id, $first->uuid, 'updated');

            $second->merged_into_id = $first->id;
            $second->save();
            $second->deleteAndLog();

            return $first;
        });
    }

    private static function sameMix(array $a, array $b): bool
    {
        return (int) round($a['oxygen'] ?? 0) === (int) round($b['oxygen'] ?? 0)
            && (int) round($a['helium'] ?? 0) === (int) round($b['helium'] ?? 0)
            && ($a['usage'] ?? 0) === ($b['usage'] ?? 0);
    }

    private static function sampleInterval(array $profile): ?int
    {
        if (count($profile) < 2) return null;
        $d = ($profile[1]['time'] ?? 0) - ($profile[0]['time'] ?? 0);
        return $d > 0 ? (int) $d : null;
    }

    private static function minOf($a, $b)
    {
        if ($a === null) return $b;
        if ($b === null) return $a;
        return min((float) $a, (float) $b);
    }

    private static function maxOf($a, $b)
    {
        if ($a === null) return $b;
        if ($b === null) return $a;
        return max((float) $a, (float) $b);
    }
}

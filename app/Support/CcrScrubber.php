<?php

namespace App\Support;

use App\Models\CcrChecklistSession;
use App\Models\CcrScrubberEvent;
use App\Models\CcrUnit;

/**
 * Time on the scrubbers for one rebreather (Zach, 2026-11-11).
 *
 * The model is the one from Zach's first checklist tool: the canisters are
 * one stack with one clock. Dive time uses the TOP canister's allowance
 * first. Only once that is spent does time land on the BOTTOM canister,
 * which is a reserve and never plan time. Rotate (one fresh canister) or
 * repack both, and the clock goes back to zero. Once any time has landed
 * on the reserve a rotate is off the table: both must be repacked.
 *
 * It is a record of time, not a measurement of sorb. Three rules keep it
 * from saying more than it knows:
 *   1. No data is "unknown", never "full". Until a repack, a rotate or a
 *      starting total has been recorded there is no "time left" at all,
 *      and after a reset there is none until dive time has been recorded.
 *   2. A reset is never assumed. It comes from "Refilled #" on a saved
 *      build check (both = repack, one = rotate) or from the diver saying
 *      so on the hub. One fresh canister after the reserve was used does
 *      not reset anything.
 *   3. Every entry is listed with where it came from, so time counted
 *      twice is visible and can be taken out.
 *
 * Read from two places and folded in time order:
 *   saved checklists   post-dive `dive_time.minutes`, build
 *                      `scrubber_time.refilled`
 *   ccr_scrubber_events  what the diver entered by hand
 */
class CcrScrubber
{
    /** Minutes as the rEvo shows them: 94 -> "1:34". */
    public static function hm(?int $minutes): string
    {
        $m = max(0, (int) $minutes);
        return intdiv($m, 60) . ':' . str_pad((string) ($m % 60), 2, '0', STR_PAD_LEFT);
    }

    /** Hours and minutes boxes to minutes; null when both are empty. */
    public static function minutes($hours, $minutes): ?int
    {
        if (($hours === null || $hours === '') && ($minutes === null || $minutes === '')) {
            return null;
        }
        return max(0, (int) $hours) * 60 + max(0, (int) $minutes);
    }

    /**
     * [top, reserve] in minutes, either may be null. The rEvo starts at
     * 3:00 and 1:30, the numbers Zach dives; every other rebreather starts
     * with none, because Divers Hub does not know that unit's limits.
     */
    public static function limits(CcrUnit $unit): array
    {
        $s = $unit->settings['scrubber'] ?? [];
        $revo = $unit->model === 'revo';
        return [
            array_key_exists('top', $s) ? $s['top'] : ($revo ? 180 : null),
            array_key_exists('reserve', $s) ? $s['reserve'] : ($revo ? 90 : null),
        ];
    }

    public static function status(CcrUnit $unit): array
    {
        [$top, $reserve] = self::limits($unit);

        // Checklists saved before rebreathers had names have no unit_id
        // and count as the diver's oldest unit (same rule as the hub).
        $oldest = CcrUnit::where('user_id', $unit->user_id)->whereNull('retired_at')->orderBy('id')->value('id');
        $sessions = CcrChecklistSession::where('user_id', $unit->user_id)
            ->whereIn('stage', ['build', 'postdive'])
            ->where(function ($w) use ($unit, $oldest) {
                $w->where('unit_id', $unit->id);
                if ($unit->id === $oldest) {
                    $w->orWhereNull('unit_id');
                }
            })
            ->get(['id', 'stage', 'build_date', 'answers']);

        $entries = [];
        foreach ($sessions as $s) {
            if (($s->stage ?: 'build') === 'postdive') {
                $min = (int) ($s->answers['dive_time']['minutes'] ?? 0);
                if ($min > 0) {
                    $entries[] = ['at' => $s->build_date, 'type' => 'add', 'minutes' => $min, 'source' => 'Post-dive check', 'session' => $s->id, 'event' => null];
                }
            } else {
                $n = count(array_filter((array) ($s->answers['scrubber_time']['refilled'] ?? [])));
                if ($n > 0) {
                    $entries[] = ['at' => $s->build_date, 'type' => $n >= 2 ? 'repack' : 'rotate', 'minutes' => null, 'source' => 'Build check', 'session' => $s->id, 'event' => null];
                }
            }
        }
        foreach (CcrScrubberEvent::where('unit_id', $unit->id)->get() as $e) {
            $entries[] = ['at' => $e->occurred_at, 'type' => $e->type, 'minutes' => $e->minutes, 'source' => 'Entered by hand', 'session' => null, 'event' => $e->id];
        }
        usort($entries, fn ($a, $b) => [$a['at']->getTimestamp(), $a['event'] ?? 0] <=> [$b['at']->getTimestamp(), $b['event'] ?? 0]);

        $used = 0;          // minutes on the stack since the last reset
        $started = false;   // a reset or a starting total has been recorded
        $timed = false;     // dive time has been recorded since then
        $since = null;      // [label, when] of the last reset
        foreach ($entries as $i => $e) {
            $dipped = $top !== null && $used > $top;
            $label = '';
            switch ($e['type']) {
                case 'repack':
                    $used = 0; $started = true; $timed = false;
                    $since = ['Both repacked', $e['at']];
                    $label = 'Both repacked';
                    break;
                case 'rotate':
                    if ($dipped) {
                        $label = 'One fresh canister (not counted: the reserve had been used, both need repacking)';
                        break;
                    }
                    $used = 0; $started = true; $timed = false;
                    $since = ['Rotated', $e['at']];
                    $label = 'Rotated, one fresh canister';
                    break;
                case 'adjust':
                    $used = max(0, (int) $e['minutes']); $started = true; $timed = true;
                    $label = 'Time on the scrubbers set to ' . self::hm($used);
                    break;
                default:
                    $used += max(0, (int) $e['minutes']); $timed = true;
                    $label = 'Dive time ' . self::hm($e['minutes']);
            }
            $entries[$i]['label'] = $label;
        }

        $topLeft = $top === null ? null : max(0, $top - $used);
        $reserveUsed = $top === null ? 0 : max(0, $used - $top);
        $reserveLeft = $reserve === null ? null : max(0, $reserve - $reserveUsed);

        // What the diver is told. `left` is only set when it is honest to
        // show a number: limits known, a starting point, and time recorded.
        if (!$started && !$timed) {
            $state = 'unknown';
        } elseif (!$started) {
            $state = 'nostart';
        } elseif (!$timed) {
            $state = 'nodata';
        } elseif ($top === null) {
            $state = 'nolimit';
        } elseif ($reserveUsed > 0) {
            $state = 'reserve';
        } elseif ($topLeft === 0) {
            $state = 'rotate';
        } else {
            $state = 'ok';
        }

        return [
            'state' => $state,
            'used' => $used,
            'top' => $top,
            'reserve' => $reserve,
            'left' => in_array($state, ['ok', 'rotate', 'reserve'], true) ? $topLeft : null,
            'reserveLeft' => $state === 'reserve' ? $reserveLeft : null,
            'pct' => ($state === 'ok' && $top) ? (int) round($topLeft / $top * 100) : null,
            'since' => $since,
            'history' => array_slice(array_reverse($entries), 0, 8),
        ] + self::words($state, $used, $top, $reserve, $topLeft, $reserveLeft, $since);
    }

    /** The headline and the line under it, the same on the hub and on the build check. */
    private static function words(string $state, int $used, ?int $top, ?int $reserve, ?int $topLeft, ?int $reserveLeft, ?array $since): array
    {
        $when = $since ? strtolower($since[0]) . ' ' . $since[1]->format('M j') : null;
        $limits = $top === null ? null : 'Limit ' . self::hm($top) . ' on the top canister' . ($reserve ? ', ' . self::hm($reserve) . ' reserve' : '') . '.';

        switch ($state) {
            case 'unknown':
                return ['headline' => 'Not tracking yet', 'tone' => 'none',
                    'detail' => 'Record a repack or a rotate, or set the time already on your scrubbers, and Divers Hub will count from there.'];
            case 'nostart':
                return ['headline' => self::hm($used) . ' of dive time recorded', 'tone' => 'none',
                    'detail' => 'No repack or rotate has been recorded, so the time left is not known. Record one, or set the time already on your scrubbers.'];
            case 'nodata':
                return ['headline' => 'No dive time recorded since they were ' . $when, 'tone' => 'none',
                    'detail' => trim('Time left shows once a dive is recorded. ' . $limits)];
            case 'nolimit':
                return ['headline' => self::hm($used) . ' on the scrubbers', 'tone' => 'none',
                    'detail' => 'Since they were ' . $when . '. Set a limit for this rebreather to see time left.'];
            case 'reserve':
                return ['headline' => 'Reserve canister has been used', 'tone' => 'poor',
                    'detail' => self::hm($used) . ' since they were ' . $when . ', ' . self::hm($used - $top) . ' of it on the reserve'
                        . ($reserveLeft !== null ? ' (' . self::hm($reserveLeft) . ' of reserve left)' : '') . '. Repack both before diving.'];
            case 'rotate':
                return ['headline' => 'Top canister is used up', 'tone' => 'avg',
                    'detail' => self::hm($used) . ' since they were ' . $when . '. Rotate or repack before the next dive.' . ($reserve ? ' Reserve ' . self::hm($reserve) . ', not plan time.' : '')];
            default:
                return ['headline' => self::hm($topLeft) . ' left on the top canister', 'tone' => 'good',
                    'detail' => self::hm($used) . ' used of ' . self::hm($top) . ' since they were ' . $when . '.' . ($reserve ? ' Reserve ' . self::hm($reserve) . ', not plan time.' : '')];
        }
    }
}

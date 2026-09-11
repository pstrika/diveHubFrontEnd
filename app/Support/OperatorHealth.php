<?php

namespace App\Support;

/**
 * Reads operators._status, ._runsRemaining and ._cron for the Platform
 * Health dashboard (admin only). Spec from the crawler team, 2026-09-11:
 *
 * _status is a varchar but is now a signed integer code (rolled out
 * incrementally, Pura Vida Divers id 3 first - other operators still send
 * old-style 0/1/ad hoc 2/3 until redeployed, so a "1" can mean either the
 * old "succeeded" or the new "site unreachable" today; there is no field
 * that says which contract a given row is on, so this renders every row
 * against the new table below as instructed and leaves that ambiguity to
 * clear up as operators redeploy):
 *
 *   -2  waiting (paused before hitting the remote site)
 *   -1  running
 *    0  last run OK
 *    1  last run failed to reach the remote site
 *    2  last run failed writing to the database
 *    3  last run hit an unexpected code error
 *  else unknown/legacy value
 *
 * _runsRemaining (nullable int) only applies to the Fareharbor-style
 * scrapers that paginate a multi-day window (queryMaxDaySpan/
 * queryCurrentIndex both set) - NULL means "not applicable", not zero.
 *
 * _cron is the operator's Azure Function NCRONTAB timer: six fields,
 * SECONDS MINUTES HOURS day month day-of-week (not the usual 5-field Unix
 * cron), all in UTC.
 */
final class OperatorHealth
{
    private const STATUSES = [
        -2 => ['label' => 'Waiting',           'icon' => 'pause_circle',    'tone' => 'wait'],
        -1 => ['label' => 'Running',           'icon' => 'autorenew',       'tone' => 'run'],
        0  => ['label' => 'OK',                'icon' => 'check_circle',    'tone' => 'good'],
        1  => ['label' => 'Site unreachable',  'icon' => 'wifi_off',        'tone' => 'poor'],
        2  => ['label' => 'Database error',    'icon' => 'dns',             'tone' => 'poor'],
        3  => ['label' => 'Error',             'icon' => 'bug_report',      'tone' => 'poor'],
    ];

    /** @return array{code:int|null,label:string,icon:string,tone:string} */
    public static function status($raw): array
    {
        $code = self::asInt($raw);
        if ($code !== null && isset(self::STATUSES[$code])) {
            return ['code' => $code] + self::STATUSES[$code];
        }
        return ['code' => null, 'label' => 'Unknown', 'icon' => 'help', 'tone' => 'none'];
    }

    /** NULL (not applicable) unless this operator paginates a multi-day window. */
    public static function runsRemainingLabel($runsRemaining, $queryMaxDaySpan): ?string
    {
        if ($queryMaxDaySpan === null || $queryMaxDaySpan === '') {
            return null;
        }
        return $runsRemaining === null || $runsRemaining === ''
            ? 'pending'
            : (string) ((int) $runsRemaining) . ' of ' . (string) ((int) $queryMaxDaySpan);
    }

    /** Human sentence for a 6-field NCRONTAB expression, all times UTC. Falls back to the raw string. */
    public static function cronLabel(?string $cron): ?string
    {
        if (!$cron || trim($cron) === '') {
            return null;
        }
        $parts = preg_split('/\s+/', trim($cron));
        if (count($parts) !== 6) {
            return $cron;
        }
        [$sec, $min, $hour, $day, $month, $dow] = $parts;
        if ($day !== '*' || $month !== '*' || $dow !== '*') {
            // Restricted to specific days/months - outside the schedules seen
            // so far; show the raw expression rather than guess a sentence.
            return $cron;
        }

        $step = self::minuteStep($min);
        $singleMinute = ctype_digit($min) ? (int) $min : null;
        $singleHour = ctype_digit($hour) ? (int) $hour : null;

        if ($step === null && $singleMinute !== null && $singleHour !== null) {
            return 'Runs daily at ' . self::clock($singleHour, $singleMinute) . ' UTC';
        }

        if ($step !== null) {
            if ($hour === '*') {
                return "Runs every {$step} min UTC";
            }
            if (preg_match('/^(\d{1,2})-(\d{1,2})$/', $hour, $m)) {
                return "Runs every {$step} min, " . self::hourLabel((int) $m[1]) . '-' . self::hourLabel((int) $m[2]) . ' UTC';
            }
            if ($singleHour !== null) {
                return "Runs every {$step} min during the " . self::hourLabel($singleHour) . ' hour UTC';
            }
        }

        return $cron;
    }

    /** Step size for a "star-slash-N" minute field or an evenly-spaced comma list; null for a single value or anything irregular. */
    private static function minuteStep(string $min): ?int
    {
        if (preg_match('/^\*\/(\d+)$/', $min, $m)) {
            return (int) $m[1];
        }
        if (str_contains($min, ',')) {
            $values = array_map('intval', explode(',', $min));
            sort($values);
            if (count($values) >= 2) {
                $step = $values[1] - $values[0];
                for ($i = 2; $i < count($values); $i++) {
                    if ($values[$i] - $values[$i - 1] !== $step) {
                        return null;
                    }
                }
                return $step;
            }
        }
        return null;
    }

    private static function hourLabel(int $hour): string
    {
        $h = $hour % 24;
        $suffix = $h < 12 ? 'am' : 'pm';
        $display = $h % 12;
        if ($display === 0) {
            $display = 12;
        }
        return $display . $suffix;
    }

    private static function clock(int $hour, int $minute): string
    {
        $h = $hour % 24;
        $suffix = $h < 12 ? 'am' : 'pm';
        $display = $h % 12;
        if ($display === 0) {
            $display = 12;
        }
        return $display . ':' . str_pad((string) $minute, 2, '0', STR_PAD_LEFT) . $suffix;
    }

    private static function asInt($raw): ?int
    {
        if ($raw === null || $raw === '') {
            return null;
        }
        return ctype_digit(ltrim((string) $raw, '-')) ? (int) $raw : null;
    }
}

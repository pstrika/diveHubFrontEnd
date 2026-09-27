<?php

namespace App\Support;

use App\Models\Weatherday;
use App\Models\WeatherLocation;

/**
 * Drafts the newsletter's "this weekend on the water" line from real
 * forecast data (Pablo, 2026-09-22: "auto generate the content...a
 * summary of conditions for the coming weekend") - a starting point the
 * admin can still edit before sending, not a guarantee of accuracy.
 *
 * Weatherday.conditionsAM_score/conditionsPM_score are the same 0-4.7
 * scale conditionsAM_text/conditionsPM_text are derived from
 * (Perfect/Good/Average/Poor/No Dive) - see scoreLabel() for the observed
 * band boundaries. Argentina locations are excluded, same as the Sites
 * Explorer map and the profile page's locations picker - this app's
 * "weekend" is South Florida.
 */
class WeekendConditions
{
    public static function summary(): ?string
    {
        $saturday = now()->startOfWeek()->addDays(5)->toDateString();
        $sunday = now()->startOfWeek()->addDays(6)->toDateString();

        $floridaLocations = WeatherLocation::all()
            ->reject(fn ($l) => Coast::forCode($l->short) === 'argentina')
            ->pluck('location');

        $byDate = Weatherday::whereIn('date', [$saturday, $sunday])
            ->whereIn('location', $floridaLocations)
            ->get()
            ->groupBy('date');

        $satLabel = isset($byDate[$saturday]) ? self::dayLabel($byDate[$saturday]) : null;
        $sunLabel = isset($byDate[$sunday]) ? self::dayLabel($byDate[$sunday]) : null;

        if (!$satLabel && !$sunLabel) {
            return null;
        }

        if ($satLabel && $sunLabel) {
            return $satLabel === $sunLabel
                ? "{$satLabel} diving expected both days this weekend across South Florida."
                : "{$satLabel} diving Saturday, {$sunLabel} conditions Sunday.";
        }

        return $satLabel
            ? "{$satLabel} diving expected Saturday - Sunday's forecast isn't in yet."
            : "{$sunLabel} diving expected Sunday - Saturday's forecast isn't in yet.";
    }

    private static function dayLabel($rows): ?string
    {
        $scores = $rows->flatMap(fn ($r) => [$r->conditionsAM_score, $r->conditionsPM_score])
            ->filter(fn ($v) => $v !== null);

        return $scores->isEmpty() ? null : self::scoreLabel((float) $scores->avg());
    }

    /** Boundaries checked against real data (2026-09-22): Perfect 4-4.7, Good 3.06-3.99, Average 2.19-2.94, Poor 1.35-1.97, No Dive 0.8-0.9. */
    private static function scoreLabel(float $score): string
    {
        if ($score >= 4) return 'Perfect';
        if ($score >= 3) return 'Good';
        if ($score >= 2) return 'Average';
        if ($score >= 1) return 'Poor';
        return 'Not diveable';
    }
}

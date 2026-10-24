<?php

namespace App\Support;

/**
 * Values in an imported dive that no dive can have, with the correction
 * the review step suggests (Pablo, 2026-10-23: "if a dive has a bad
 * format, suggest corrections at the import time"). Seen in the wild:
 * MacDive writes 4699.85 K (4426.7 °C) as a water temperature when the
 * computer logged none - that overflowed the dives table and failed a
 * diver's 86-dive import.
 *
 * review() returns the payload with the suggestions applied, plus one
 * row per corrected field so the review page can show the file's value
 * and let the diver change the suggestion before the import runs.
 */
class DiveImportChecks
{
    public const FIELDS = [
        'min_water_temp_c' => ['label' => 'Water temperature (min)', 'unit' => '°C', 'min' => -5, 'max' => 60],
        'max_water_temp_c' => ['label' => 'Water temperature (max)', 'unit' => '°C', 'min' => -5, 'max' => 60],
        'surface_temp_c' => ['label' => 'Air temperature', 'unit' => '°C', 'min' => -40, 'max' => 60],
        'max_depth_m' => ['label' => 'Max depth', 'unit' => 'm', 'min' => 0, 'max' => 350],
        'avg_depth_m' => ['label' => 'Average depth', 'unit' => 'm', 'min' => 0, 'max' => 350],
        'duration_seconds' => ['label' => 'Duration', 'unit' => 'min', 'min' => 0, 'max' => 86400],
    ];

    /**
     * @return array{payload: array, issues: array<int, array{field:string, label:string, unit:string, value:float, suggested:float, why:string}>}
     */
    public static function review(array $d): array
    {
        $issues = [];
        $profile = $d['profile'] ?? [];

        // Per-sample temperatures: an impossible one is dropped (no way to
        // ask about thousands of samples); the plausible ones give the
        // suggestion for the summary fields.
        $sampleTemps = [];
        foreach ($profile as $i => $s) {
            if (!isset($s['temperature'])) continue;
            $t = (float) $s['temperature'];
            if ($t < -5 || $t > 60) unset($profile[$i]['temperature']); else $sampleTemps[] = $t;
        }
        $d['profile'] = array_values($profile);

        foreach (['min_water_temp_c', 'max_water_temp_c', 'surface_temp_c'] as $f) {
            if (!isset($d[$f]) || self::within($f, (float) $d[$f])) continue;
            $value = (float) $d[$f];
            $suggested = 0.0;
            if ($sampleTemps && $f !== 'surface_temp_c') $suggested = round($f === 'min_water_temp_c' ? min($sampleTemps) : max($sampleTemps), 1);
            $issues[] = self::issue($f, $value, $suggested, $sampleTemps && $f !== 'surface_temp_c'
                ? 'No dive is at that temperature - the computer logged no reading. Taken from the profile samples instead.'
                : 'No dive is at that temperature - the computer logged no reading.');
            $d[$f] = $suggested;
        }

        $depths = array_filter(array_map(fn ($s) => isset($s['depth']) ? (float) $s['depth'] : null, $d['profile']), fn ($v) => $v !== null && $v >= 0 && $v <= 350);
        if (isset($d['max_depth_m']) && !self::within('max_depth_m', (float) $d['max_depth_m'])) {
            $suggested = $depths ? round(max($depths), 2) : 0.0;
            $issues[] = self::issue('max_depth_m', (float) $d['max_depth_m'], $suggested, $depths ? 'Deeper than any dive - taken from the deepest profile sample instead.' : 'Deeper than any dive, and the file has no profile to read it from.');
            $d['max_depth_m'] = $suggested;
        }
        if (isset($d['avg_depth_m']) && (!self::within('avg_depth_m', (float) $d['avg_depth_m']) || (float) $d['avg_depth_m'] > (float) ($d['max_depth_m'] ?? 350))) {
            $suggested = count($d['profile']) > 1 ? round((float) UddfParser::averageDepth($d['profile']), 2) : round(((float) ($d['max_depth_m'] ?? 0)) / 2, 2);
            $issues[] = self::issue('avg_depth_m', (float) $d['avg_depth_m'], $suggested, count($d['profile']) > 1 ? 'Deeper than the dive\'s max depth - worked out from the profile instead.' : 'Deeper than the dive\'s max depth.');
            $d['avg_depth_m'] = $suggested;
        }
        if (isset($d['duration_seconds']) && !self::within('duration_seconds', (float) $d['duration_seconds'])) {
            $last = count($d['profile']) > 1 ? (int) (end($d['profile'])['time'] ?? 0) : 0;
            $suggested = $last > 0 && $last <= 86400 ? (float) $last : 0.0;
            $issues[] = self::issue('duration_seconds', (float) $d['duration_seconds'], $suggested, $suggested > 0 ? 'Longer than a day - the profile\'s last sample gives the duration instead.' : 'Longer than a day, and the file has no profile to read it from.');
            $d['duration_seconds'] = (int) $suggested;
        }

        return ['payload' => $d, 'issues' => $issues];
    }

    public static function within(string $field, float $v): bool
    {
        $r = self::FIELDS[$field];
        return $v >= $r['min'] && $v <= $r['max'];
    }

    private static function issue(string $field, float $value, float $suggested, string $why): array
    {
        $f = self::FIELDS[$field];
        return ['field' => $field, 'label' => $f['label'], 'unit' => $f['unit'], 'value' => $value, 'suggested' => $suggested, 'why' => $why];
    }

    /** A value the diver typed on the review page, in the field's own unit, as the payload stores it. */
    public static function fromInput(string $field, $input): ?float
    {
        if ($input === null || trim((string) $input) === '' || !is_numeric($input)) return null;
        $v = (float) $input;
        if ($field === 'duration_seconds') $v = round($v * 60);
        return self::within($field, $v) ? $v : null;
    }

    /** The payload's value shown in the field's unit on the review page. */
    public static function display(string $field, float $v): string
    {
        if ($field === 'duration_seconds') return rtrim(rtrim(number_format($v / 60, 1, '.', ','), '0'), '.');
        return number_format($v, $field === 'max_depth_m' || $field === 'avg_depth_m' ? 2 : 1, '.', ',');
    }
}

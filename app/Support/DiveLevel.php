<?php

namespace App\Support;

/**
 * The single source of truth for dive site certification levels.
 *
 * The `sites.level` column stores an integer 0 to 4. Before this class the
 * meaning of each number lived in three places: the icon file names
 * (icons_level_N.png), the "Site levels" legend table copied into several
 * Blade views, and a comment or two in controllers. Any new place that shows
 * a level (site lists, trip cards, site cards, filters) should read from
 * here so the names, codes, depths and icons can never drift apart.
 *
 * Usage:
 *   DiveLevel::all()            every level in ascending order, keyed by value
 *   DiveLevel::get(2)           one level, or null for an unknown value
 *   DiveLevel::name(2)          "Technical Air"
 *   DiveLevel::code(2)          "Ta"
 *   DiveLevel::iconPath(2)      "img/icons/icons_level_2.png" (relative to assets)
 *   DiveLevel::isValid('3')     true for 0..4, false for anything else
 */
final class DiveLevel
{
    /**
     * Levels in ascending order of training required. `maxDepth` is the
     * recommended maximum depth in feet and matches the legend that has
     * always been shown on the sites pages. `null` means no fixed limit
     * (the legend shows it as "330+").
     */
    private const LEVELS = [
        0 => ['code' => 'OW',  'name' => 'Open Water',               'short' => 'Open Water', 'maxDepth' => 60],
        1 => ['code' => 'AOW', 'name' => 'Advanced Open Water',      'short' => 'Advanced',   'maxDepth' => 130],
        2 => ['code' => 'Ta',  'name' => 'Technical Air',            'short' => 'Tech Air',   'maxDepth' => 150],
        3 => ['code' => 'Tn',  'name' => 'Technical Normoxic Trimix', 'short' => 'Tech Trimix', 'maxDepth' => 200],
        4 => ['code' => 'Th',  'name' => 'Technical Hypoxic Trimix',  'short' => 'Tech Hypoxic', 'maxDepth' => null],
    ];

    /** @return array<int, array{value:int, code:string, name:string, short:string, maxDepth:?int, icon:string}> */
    public static function all(): array
    {
        $out = [];
        foreach (self::LEVELS as $value => $row) {
            $out[$value] = self::get($value);
        }
        return $out;
    }

    /** @return array{value:int, code:string, name:string, short:string, maxDepth:?int, icon:string}|null */
    public static function get($value): ?array
    {
        if (!self::isValid($value)) {
            return null;
        }
        $value = (int) $value;
        return self::LEVELS[$value] + [
            'value' => $value,
            'icon'  => self::iconPath($value),
        ];
    }

    public static function isValid($value): bool
    {
        // Accepts ints and numeric strings from query strings, rejects "abc", "", null.
        return is_numeric($value) && (string) (int) $value === (string) $value && isset(self::LEVELS[(int) $value]);
    }

    public static function name($value): string
    {
        return self::get($value)['name'] ?? 'Unknown level';
    }

    public static function code($value): string
    {
        return self::get($value)['code'] ?? '?';
    }

    /** Path under public/assets. Kept here so a future icon rename is a one line change. */
    public static function iconPath($value): string
    {
        return 'img/icons/icons_level_' . (int) $value . '.png';
    }

    /** Human readable depth for legends: "60 ft", "330+ ft". */
    public static function depthLabel($value): string
    {
        $level = self::get($value);
        if (!$level) {
            return '';
        }
        return $level['maxDepth'] === null ? '330+ ft' : $level['maxDepth'] . ' ft';
    }
}

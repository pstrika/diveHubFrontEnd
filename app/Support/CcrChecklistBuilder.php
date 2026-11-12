<?php

namespace App\Support;

/**
 * Turns a checklist template into the checklist a diver sees (Zach,
 * 2026-11-10 and 2026-11-11).
 *
 * Used with the same rules in three places, so a checklist reads the same
 * when it is being filled in, when it is looked at afterwards, and on the
 * PDF: the live form (template + the diver's current steps and layout), and
 * the saved page and PDF (template + the copies kept on that session).
 *
 * Two ways in:
 *   merge()  the standard order, with the diver's own steps woven in after
 *            the built in step each one follows.
 *   build()  the same, unless the diver has a layout: then their order and
 *            their headings.
 *
 * What never changes, whichever way in: a built in step cannot be removed
 * or reworded, it keeps its original number, and it is on the checklist
 * exactly once. normalize() enforces that last one against whatever a saved
 * layout holds.
 *
 * Every item that comes out carries `section`, the heading to print above
 * it. Views print that, never `group`: `group` stays what the template
 * said, because Create.blade.php lays out the gas rows by it.
 */
class CcrChecklistBuilder
{
    /** The checklist as the diver should see it. $rows null = standard order. */
    public static function build(array $template, array $customs, ?array $rows): array
    {
        $merged = self::merge($template, $customs);
        if ($rows === null) {
            return $merged;
        }

        $map = [];
        foreach ($merged['items'] as $item) {
            $map[$item['key']] = $item;
        }

        $items = [];
        $section = null;
        foreach (self::normalize($template, $customs, $rows) as $row) {
            if ($row['t'] === 'h') {
                // A heading with no text is a section break: the steps
                // under it have no heading (Pablo's build sheet has two).
                $section = $row['text'] === '' ? null : $row['text'];
                continue;
            }
            $item = $map[$row['k']];
            $item['section'] = $section;
            $items[] = $item;
        }

        $out = $template;
        $out['items'] = $items;
        if (self::differs($merged['items'], $items)) {
            // Shown where the "follows the manufacturer step for step" line
            // was, on the form, the saved page and the PDF.
            $out['reordered'] = true;
            $out['source'] = 'Your own order. The order or the headings of this checklist were changed by the diver. Built in steps keep their original numbers.';
        }
        return $out;
    }

    /**
     * The standard order with the diver's own steps woven in.
     *
     * @param array $template one entry of config ccr_checklist_templates.versions
     * @param array $customs  list of ['key','text','after_key','position']
     */
    public static function merge(array $template, array $customs): array
    {
        $builtIn = $template['items'] ?? [];
        $out = $builtIn;

        if ($customs) {
            // Group the diver's steps by the built in step they follow.
            usort($customs, fn ($a, $b) => [$a['position'] ?? 0, $a['key']] <=> [$b['position'] ?? 0, $b['key']]);
            $keys = array_column($builtIn, 'key');
            $byAnchor = [];
            $orphans = [];
            foreach ($customs as $c) {
                $anchor = $c['after_key'] ?? null;
                if ($anchor !== null && !in_array($anchor, $keys, true)) {
                    // The step it followed is gone from this template version.
                    // Keep the diver's step rather than lose it: it goes last.
                    $orphans[] = $c;
                    continue;
                }
                $byAnchor[$anchor ?? ''][] = $c;
            }

            $firstGroup = $builtIn[0]['group'] ?? null;
            $out = [];
            foreach ($byAnchor[''] ?? [] as $c) {
                $out[] = self::item($c, $firstGroup);
            }
            foreach ($builtIn as $item) {
                $out[] = $item;
                foreach ($byAnchor[$item['key']] ?? [] as $c) {
                    // Same section as the step it follows, so it sits under
                    // that section's heading instead of starting a new one.
                    $out[] = self::item($c, $item['group'] ?? null);
                }
            }
            $lastGroup = $builtIn ? ($builtIn[count($builtIn) - 1]['group'] ?? null) : null;
            foreach ($orphans as $c) {
                $out[] = self::item($c, $lastGroup);
            }
        }

        foreach ($out as $i => $item) {
            $out[$i]['section'] = $item['group'] ?? null;
        }
        $template['items'] = $out;
        return $template;
    }

    /** The standard order as layout rows: where a diver's layout starts from. */
    public static function defaultRows(array $template, array $customs): array
    {
        $rows = [];
        $prev = null;
        $n = 0;
        foreach (self::merge($template, $customs)['items'] as $item) {
            $section = $item['section'] ?? null;
            if ($section !== $prev) {
                $prev = $section;
                $text = (string) $section;
                // No heading at the very top needs no row. Further down it
                // is a section break (empty text), so the steps after it
                // do not fall under the heading above.
                if ($text !== '' || $rows) {
                    // Same id every time for the same heading in the same
                    // place, so a link to a row still lands before anything
                    // has been saved.
                    $rows[] = ['t' => 'h', 'id' => 'd' . substr(md5($text . '|' . $n++), 0, 7), 'text' => $text];
                }
            }
            $rows[] = ['t' => 's', 'k' => $item['key']];
        }
        return $rows;
    }

    /**
     * A layout made safe to use. Whatever $rows holds, what comes back has
     * every step of the checklist exactly once:
     *  - a row for a step that no longer exists is dropped,
     *  - a step listed twice is kept once, where it first appears,
     *  - a step missing from the rows (added to the template later, or
     *    added by the diver since) goes in after the step it follows in the
     *    standard order.
     * Headings need an id of their own; one with no text is a section break.
     */
    public static function normalize(array $template, array $customs, array $rows): array
    {
        $standard = array_column(self::merge($template, $customs)['items'], 'key');
        $known = array_flip($standard);

        $out = [];
        $seen = [];
        $ids = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            if (($row['t'] ?? null) === 'h') {
                $text = trim((string) ($row['text'] ?? ''));
                $id = (string) ($row['id'] ?? '');
                if (!preg_match('/^[a-z0-9]{4,12}$/', $id) || isset($ids[$id])) {
                    continue;
                }
                $ids[$id] = true;
                $out[] = ['t' => 'h', 'id' => $id, 'text' => mb_substr($text, 0, 80)];
            } elseif (($row['t'] ?? null) === 's') {
                $key = (string) ($row['k'] ?? '');
                if (!isset($known[$key]) || isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;
                $out[] = ['t' => 's', 'k' => $key];
            }
        }

        foreach ($standard as $i => $key) {
            if (isset($seen[$key])) {
                continue;
            }
            $at = null;
            // Right after the step it follows in the standard order...
            for ($j = $i - 1; $j >= 0 && $at === null; $j--) {
                $at = self::indexOf($out, $standard[$j]);
                if ($at !== null) $at++;
            }
            // ...or, if it is the first, right before the one that follows it.
            for ($j = $i + 1; $j < count($standard) && $at === null; $j++) {
                $at = self::indexOf($out, $standard[$j]);
            }
            array_splice($out, $at ?? count($out), 0, [['t' => 's', 'k' => $key]]);
            $seen[$key] = true;
        }

        return $out;
    }

    /** How a row is named in a form field: a step by its key, a heading as h:<id>. */
    public static function ref(array $row): string
    {
        return $row['t'] === 'h' ? 'h:' . $row['id'] : $row['k'];
    }

    /** Where in $rows the row named $ref is, or null. */
    public static function find(array $rows, ?string $ref): ?int
    {
        if ($ref === null || $ref === '') {
            return null;
        }
        foreach ($rows as $i => $row) {
            if (self::ref($row) === $ref) {
                return $i;
            }
        }
        return null;
    }

    private static function indexOf(array $rows, string $key): ?int
    {
        foreach ($rows as $i => $row) {
            if ($row['t'] === 's' && $row['k'] === $key) {
                return $i;
            }
        }
        return null;
    }

    /**
     * Has the diver changed the standard checklist? Only the built in steps
     * count: their order, and the heading each one sits under. Adding a
     * step of their own does not make it a different checklist.
     */
    private static function differs(array $standard, array $items): bool
    {
        $shape = fn (array $list) => array_values(array_map(
            fn ($i) => [$i['key'], $i['section'] ?? null],
            array_filter($list, fn ($i) => empty($i['custom']))
        ));
        $a = $shape($standard);
        return $a && $a !== $shape($items);
    }

    /** A diver's step in the same shape as a template item. Tick only. */
    private static function item(array $c, ?string $group): array
    {
        return [
            'key' => $c['key'],
            // Built in steps keep the manufacturer's own numbering; the
            // diver's are marked instead of numbered, so adding one never
            // renumbers the steps around it.
            'number' => '+',
            'text' => $c['text'],
            'group' => $group,
            'fields' => [],
            'custom' => true,
        ];
    }

    /** How many of the items in a merged template are the diver's own. */
    public static function customCount(array $template): int
    {
        return count(array_filter($template['items'] ?? [], fn ($i) => !empty($i['custom'])));
    }
}

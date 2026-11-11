<?php

namespace App\Support;

/**
 * Weaves a diver's own steps into a checklist template (Zach, 2026-11-10).
 *
 * Used in three places with the same rule, so a checklist reads the same
 * when it is being filled in, when it is looked at afterwards, and on the
 * PDF: the live form (template + the diver's current steps), and the saved
 * page and PDF (template + the copy of the steps kept on that session).
 *
 * Built in steps are never removed, reordered or reworded by this. A
 * diver's steps are only ever added around them.
 */
class CcrChecklistBuilder
{
    /**
     * @param array $template one entry of config ccr_checklist_templates.versions
     * @param array $customs  list of ['key','text','after_key','position']
     * @return array the template with its `items` including the diver's steps
     */
    public static function merge(array $template, array $customs): array
    {
        $builtIn = $template['items'] ?? [];
        if (!$customs) {
            return $template;
        }

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

        $template['items'] = $out;
        return $template;
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

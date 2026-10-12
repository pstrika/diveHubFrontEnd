<?php

namespace App\Support;

/**
 * Trims text to fit search-result snippets (SEO spot check, 2026-10-12).
 * Google cuts descriptions at about 160 characters; blog excerpts and
 * group descriptions are written for the page and often run longer.
 */
final class MetaText
{
    /**
     * Whole sentences up to $max characters. If the first sentence alone is
     * longer, cut at the last word that fits and add an ellipsis.
     */
    public static function description(?string $text, int $max = 160): ?string
    {
        $text = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $text)));
        if ($text === '') {
            return null; // let the page template fall back to its default description
        }
        if (mb_strlen($text) <= $max) {
            return $text;
        }

        $sentences = preg_split('/(?<=[.!?])\s+/u', $text) ?: [$text];
        $out = '';
        foreach ($sentences as $sentence) {
            $candidate = $out === '' ? $sentence : $out . ' ' . $sentence;
            if (mb_strlen($candidate) > $max) {
                break;
            }
            $out = $candidate;
        }
        if ($out !== '') {
            return $out;
        }

        $cut = mb_substr($text, 0, $max - 1);
        $space = mb_strrpos($cut, ' ');
        if ($space !== false && $space > $max * 0.6) {
            $cut = mb_substr($cut, 0, $space);
        }
        return rtrim($cut, " ,;:-–—") . '…';
    }
}

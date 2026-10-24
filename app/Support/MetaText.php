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

        // A sentence ends at . ! ? followed by a capital, digit or opening
        // quote - not at an abbreviation (SEO audit, 2026-10-24: "the best
        // places in the U.S. to dive" was cut after "U.S.", leaving an
        // 85-character description). Initials (U.S., D.C.) and the
        // abbreviations Florida dive copy uses (Ft., St., Mt.) never end one.
        $sentences = preg_split('/(?<!\b[A-Z]\.[A-Z]\.)(?<!\b[A-Z]\.)(?<!\bFt\.)(?<!\bSt\.)(?<!\bMt\.)(?<!\bMr\.)(?<!\bDr\.)(?<!\bvs\.)(?<!\bapprox\.)(?<=[.!?])\s+(?=[\p{Lu}0-9"“‘(])/u', $text) ?: [$text];
        $out = '';
        foreach ($sentences as $sentence) {
            $candidate = $out === '' ? $sentence : $out . ' ' . $sentence;
            if (mb_strlen($candidate) > $max) {
                break;
            }
            $out = $candidate;
        }
        // Whole sentences, unless they come to well under the limit: then a
        // longer description cut at a word reads better in results.
        if ($out !== '' && mb_strlen($out) >= $max * 0.6) {
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

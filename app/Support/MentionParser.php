<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * Finds @mentions of group members in a chat message's body.
 *
 * One combined regex, member names as alternatives ordered longest first,
 * rather than testing each member's pattern against the body independently -
 * "@John Smith" must consume the whole name in one match so a separate
 * "John" in the same group can't also register a match inside it (PCRE
 * alternation tries earlier alternatives first at a given position, and
 * preg_match_all resumes searching after a match ends, so once "John Smith"
 * matches there's nothing left at that position for plain "John" to match
 * against). The chat UI's own autocomplete always inserts a member's exact
 * name after "@"; this also covers anyone who types the mention by hand.
 */
final class MentionParser
{
    /**
     * @param string $body the message text
     * @param Collection $members each entry ['id' => int, 'name' => string]
     * @return Collection matched member ids, each once
     */
    public static function detect(string $body, Collection $members): Collection
    {
        $sorted = $members
            ->filter(fn ($m) => trim((string) ($m['name'] ?? '')) !== '')
            ->sortByDesc(fn ($m) => mb_strlen($m['name']))
            ->values();

        if ($sorted->isEmpty()) {
            return collect();
        }

        $nameToId = [];
        foreach ($sorted as $m) {
            // Longest (first-seen, since sorted) wins if two members somehow share a name.
            $key = mb_strtolower($m['name']);
            if (!isset($nameToId[$key])) {
                $nameToId[$key] = $m['id'];
            }
        }

        $alternatives = $sorted->map(fn ($m) => preg_quote($m['name'], '/'))->implode('|');
        // \b doesn't reliably follow a multi-byte/accented name boundary, so
        // require what comes after "@Name" to simply not be another word
        // character - punctuation, whitespace, or end of string.
        $pattern = '/@(' . $alternatives . ')(?!\w)/iu';

        if (!preg_match_all($pattern, $body, $matches)) {
            return collect();
        }

        return collect($matches[1])
            ->map(fn ($name) => $nameToId[mb_strtolower($name)] ?? null)
            ->filter()
            ->unique()
            ->values();
    }
}

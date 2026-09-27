<?php

namespace App\Support;

/**
 * Converts a small, deliberate Markdown subset into the Quill Delta JSON
 * every Post::body actually needs - built for the blog API (Pablo,
 * 2026-09-20: "call it from anywhere with the right JSON"), so a caller
 * writes ordinary Markdown instead of hand-assembling Delta ops the way
 * every article up to this one was built.
 *
 * Deliberately not a general Markdown parser: only what every existing
 * article on this site actually uses.
 *
 *   ## Heading            -> header: 2
 *   - list item           -> list: bullet (consecutive lines group)
 *   > blockquote          -> blockquote: true
 *   **bold**               -> bold: true
 *   [text](url)            -> link: url, bold: true (matches this site's own
 *                             convention - every internal link in an
 *                             existing article is rendered bold)
 *   blank line              -> paragraph break
 *   anything else           -> a plain paragraph
 */
class QuillMarkdown
{
    public static function toDelta(string $markdown): string
    {
        $ops = [];
        $lines = preg_split('/\r\n|\r|\n/', trim($markdown));

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '') {
                $ops[] = ['insert' => "\n"];
                continue;
            }

            if (preg_match('/^##\s+(.*)$/', $trimmed, $m)) {
                $ops = array_merge($ops, self::inline($m[1]));
                $ops[] = ['insert' => "\n", 'attributes' => ['header' => 2]];
                continue;
            }

            if (preg_match('/^-\s+(.*)$/', $trimmed, $m)) {
                $ops = array_merge($ops, self::inline($m[1]));
                $ops[] = ['insert' => "\n", 'attributes' => ['list' => 'bullet']];
                continue;
            }

            if (preg_match('/^>\s+(.*)$/', $trimmed, $m)) {
                $ops = array_merge($ops, self::inline($m[1]));
                $ops[] = ['insert' => "\n", 'attributes' => ['blockquote' => true]];
                continue;
            }

            $ops = array_merge($ops, self::inline($trimmed));
            $ops[] = ['insert' => "\n"];
        }

        return json_encode(['ops' => $ops], JSON_UNESCAPED_SLASHES);
    }

    /**
     * Splits one line into Delta ops, applying **bold** and [text](url)
     * (rendered bold, matching every existing article's own convention)
     * wherever they appear - the only two inline styles this site's
     * articles actually use.
     *
     * @return array<int, array>
     */
    private static function inline(string $text): array
    {
        $pattern = '/\*\*(.+?)\*\*|\[(.+?)\]\((.+?)\)/';
        $ops = [];
        $lastEnd = 0;

        if (preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $i => $full) {
                [$matchText, $offset] = $full;

                if ($offset > $lastEnd) {
                    $ops[] = ['insert' => substr($text, $lastEnd, $offset - $lastEnd)];
                }

                if ($matches[1][$i][0] !== '') {
                    // **bold**
                    $ops[] = ['insert' => $matches[1][$i][0], 'attributes' => ['bold' => true]];
                } else {
                    // [text](url)
                    $ops[] = ['insert' => $matches[2][$i][0], 'attributes' => ['link' => $matches[3][$i][0], 'bold' => true]];
                }

                $lastEnd = $offset + strlen($matchText);
            }
        }

        if ($lastEnd < strlen($text)) {
            $ops[] = ['insert' => substr($text, $lastEnd)];
        }

        if (empty($ops)) {
            $ops[] = ['insert' => ''];
        }

        return $ops;
    }
}

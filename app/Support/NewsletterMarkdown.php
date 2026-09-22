<?php

namespace App\Support;

/**
 * The same small Markdown subset App\Support\QuillMarkdown uses (##
 * heading, - bullet, > blockquote, blank line = paragraph break,
 * **bold**, [text](url) link), plus ![alt](url) for an image (QuillMarkdown
 * has no equivalent - a Post's cover image is a separate field, but a
 * newsletter has no such field, so an inline image needs its own syntax)
 * - converted to real HTML instead of Quill Delta, since a newsletter
 * issue's body is sent as raw HTML in an email rather than stored as a
 * Post's Delta body.
 *
 * Inline text is escaped BEFORE bold/link markers are turned into real
 * tags, so anything the admin types is neutralized first and only the
 * small set of tags this class inserts itself ever reaches the output -
 * see inline(). ![alt](url) is matched as its own block (not through
 * inline()) since an <img> isn't inline text - its src/alt are escaped
 * directly instead.
 */
class NewsletterMarkdown
{
    public static function toHtml(string $markdown): string
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($markdown));
        $html = [];
        $listOpen = false;

        $closeList = function () use (&$html, &$listOpen) {
            if ($listOpen) {
                $html[] = '</ul>';
                $listOpen = false;
            }
        };

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '') {
                $closeList();
                continue;
            }

            if (preg_match('/^##\s+(.*)$/', $trimmed, $m)) {
                $closeList();
                $html[] = '<h2 style="font-family:Georgia,\'Times New Roman\',serif;font-size:20px;color:#0b2a3a;margin:24px 0 10px;">' . self::inline($m[1]) . '</h2>';
                continue;
            }

            if (preg_match('/^-\s+(.*)$/', $trimmed, $m)) {
                if (!$listOpen) {
                    $html[] = '<ul style="margin:0 0 14px;padding-left:20px;">';
                    $listOpen = true;
                }
                $html[] = '<li style="margin-bottom:6px;">' . self::inline($m[1]) . '</li>';
                continue;
            }

            if (preg_match('/^>\s+(.*)$/', $trimmed, $m)) {
                $closeList();
                $html[] = '<blockquote style="margin:0 0 14px;padding-left:14px;border-left:3px solid #0e7c9e;color:#5a6b78;">' . self::inline($m[1]) . '</blockquote>';
                continue;
            }

            if (preg_match('/^!\[(.*?)\]\((\S+)\)$/', $trimmed, $m)) {
                $closeList();
                $html[] = '<img src="' . e($m[2]) . '" alt="' . e($m[1]) . '" style="max-width:100%;height:auto;border-radius:8px;display:block;margin:0 0 14px;">';
                continue;
            }

            $closeList();
            $html[] = '<p style="margin:0 0 14px;">' . self::inline($trimmed) . '</p>';
        }

        $closeList();

        return implode("\n", $html);
    }

    private static function inline(string $text): string
    {
        $escaped = e($text);

        $escaped = preg_replace('/\*\*(.+?)\*\*/', '<b>$1</b>', $escaped);

        $escaped = preg_replace_callback('/\[(.+?)\]\((.+?)\)/', function ($m) {
            // $m[1]/$m[2] are already-escaped substrings (the whole string
            // went through e() above) - do not escape them again here.
            return '<a href="' . $m[2] . '" style="color:#0e7c9e;font-weight:bold;text-decoration:none;">' . $m[1] . '</a>';
        }, $escaped);

        return $escaped;
    }
}

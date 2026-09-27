<?php

namespace App\Support;

/**
 * Inline one of Pablo's SVG icons with fill="currentColor" added to the
 * root, so it can be recolored from CSS like the rest of the theme's icons.
 * Used wherever a PNG icon with a fixed color was replaced by an SVG (site
 * type, wreck vessel type, ship dimensions, drawer calendar icons).
 *
 * Two export shapes exist in the wild: some files have no fill at all
 * (default to black, currentColor is all they need); others - most of the
 * boat/vessel set - carry an Illustrator <style>.cls-1{fill:#xxxxxx}</style>
 * block with every <path class="cls-1">, which overrides currentColor
 * outright since a class rule beats an inherited attribute. Both are
 * stripped down to plain currentColor here rather than handled per file.
 */
final class IconSvg
{
    /** @param string $publicRelativePath Path under public/, e.g. "assets/img/icons/reef_icon.svg". */
    public static function themed(string $publicRelativePath): ?string
    {
        $path = public_path($publicRelativePath);
        if (!is_file($path)) {
            return null;
        }
        $svg = file_get_contents($path);
        $svg = preg_replace('/<defs>.*?<\/defs>/s', '', $svg);
        $svg = preg_replace('/\s+class="cls-\d+"/', '', $svg);
        $svg = preg_replace('/<svg /', '<svg fill="currentColor" ', $svg, 1);
        return $svg;
    }
}

@props(['file'])
@php
    // Same inline-SVG recoloring trick as menu.blade.php's $iconHtml - strip
    // Illustrator's hardcoded fill:#fff <style>/.cls-1 class and drive the
    // color from CSS (currentColor) instead, so these sit next to Material
    // icons and inherit the same color/sizing rules.
    $path = public_path('assets/img/icons/' . $file);
    $svg = is_file($path) ? file_get_contents($path) : '';
    $svg = preg_replace('/<defs>.*?<\/defs>/s', '', $svg);
    // Some of the icons_calendar_*.svg exports also carry an embedded
    // <metadata> block (C2PA content-credentials, several KB of base64) -
    // menu.blade.php's own $iconHtml doesn't hit this today since none of
    // the files it currently references have one, but 3 of the 5 icons
    // this component uses do (checked directly, 2026-10-15) - inlining it
    // ships that whole blob as text content on every page load otherwise.
    $svg = preg_replace('/<metadata>.*?<\/metadata>/s', '', $svg);
    $svg = str_replace(' class="cls-1"', '', $svg);
    $svg = preg_replace('/<svg /', '<svg fill="currentColor" ', $svg, 1);
@endphp
<span {{ $attributes->merge(['class' => 'dh-svg-icon']) }} aria-hidden="true">{!! $svg !!}</span>

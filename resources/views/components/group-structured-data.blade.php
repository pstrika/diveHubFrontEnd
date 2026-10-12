{{--
    Structured data for a public group's page: a BreadcrumbList
    (Divers Hub > Groups > this group), which Google can show in place of
    the raw URL (SEO spot check, 2026-10-12: group pages had none).
    JSON_HEX_TAG because group names are user-entered: a "</script>" in a
    name must not be able to close this tag.
--}}
@props(['group', 'SEO'])
@php
    $groupCrumbs = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Divers Hub', 'item' => url('/')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Diving groups', 'item' => route('Groups.public')],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $group->name, 'item' => $SEO['canonical'] ?? route('Groups.show', ['group' => $group->slug])],
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($groupCrumbs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>

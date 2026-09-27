{{--
    Structured data for a dive site page: schema.org TouristAttraction and a
    BreadcrumbList. Moved here from the top of SiteDetails.blade.php so the
    redesign of that page cannot touch it. The PHP below is unchanged.
--}}
@props(['site', 'photos', 'SEO'])

    @php
        try {
            $jsonLdDesc = trim(strip_tags($site->getPlainTextDesc()));
        } catch (\Throwable $e) {
            $jsonLdDesc = trim(strip_tags($site->desc ?? ''));
        }
        if (mb_strlen($jsonLdDesc) > 300) {
            $jsonLdDesc = mb_substr($jsonLdDesc, 0, 297) . '...';
        }

        $jsonLdImages = collect($photos)->take(5)->map(function ($photo) {
            return asset('assets') . '/img/sites/' . $photo->file;
        })->values()->all();

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'TouristAttraction',
            'name' => $site->name,
            'description' => $jsonLdDesc,
            'url' => $SEO['canonical'] ?? url()->current(),
        ];

        if (!empty($jsonLdImages)) {
            $jsonLd['image'] = $jsonLdImages;
        }

        if (!empty($site->gpsLat) && !empty($site->gpsLon)) {
            $latParts = sscanf($site->gpsLat, "%d° %f' %c");
            $lonParts = sscanf($site->gpsLon, "%d° %f' %c");
            if (count(array_filter($latParts, fn($v) => $v !== null)) === 3 && count(array_filter($lonParts, fn($v) => $v !== null)) === 3) {
                [$latDeg, $latMin, $latDir] = $latParts;
                [$lonDeg, $lonMin, $lonDir] = $lonParts;
                $jsonLd['geo'] = [
                    '@type' => 'GeoCoordinates',
                    'latitude' => round(($latDeg + $latMin / 60) * ($latDir === 'N' ? 1 : -1), 6),
                    'longitude' => round(($lonDeg + $lonMin / 60) * ($lonDir === 'E' ? 1 : -1), 6),
                ];
            }
        }

        // Note: aggregateRating is intentionally omitted. Google's Rich Results
        // system does not support the review/star-rating feature for
        // TouristAttraction/Place types, so including it here would be flagged
        // as an invalid field-type combination rather than produce a rich result.

        $breadcrumbJsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Dive Sites', 'item' => route('DiveSites')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $site->name, 'item' => $SEO['canonical'] ?? url()->current()],
            ],
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    <script type="application/ld+json">{!! json_encode($breadcrumbJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>

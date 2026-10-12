{{--
    Structured data for the trip finder (/Trips): a CollectionPage that
    belongs to the Divers Hub site and organization declared on the
    homepage (SEO spot check, 2026-10-12: the page had none).

    Deliberately not one schema.org Event per trip: Google's event rich
    results need a full location address and a ticket/booking URL for
    every event, and trips only carry a site name and the operator, so
    ~100 Events a day would come back as Search Console errors.
--}}
@php
    $tripsLd = [
        '@context' => 'https://schema.org',
        '@type' => 'CollectionPage',
        'name' => 'South Florida dive trips',
        'description' => 'Every dive boat going out in South Florida on one calendar, from Stuart to Key West: departure times, dive sites and open spots.',
        'url' => route('Trips'),
        'isPartOf' => ['@id' => url('/') . '/#website'],
        'publisher' => ['@id' => url('/') . '/#org'],
        'about' => ['@type' => 'Thing', 'name' => 'Scuba diving trips in South Florida and the Florida Keys'],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($tripsLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>

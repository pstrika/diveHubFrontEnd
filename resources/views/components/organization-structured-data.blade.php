{{--
    Organization + WebSite structured data for the homepage (SEO audit,
    2026-09-28, finding #3: "The homepage has no Organization/WebSite").
    Same json_encode convention as site-structured-data.blade.php. Address
    and support email match the real ones already used in every outbound
    email footer (trip-reminder-content, newsletter-content, wishlist-trip-
    content) and the Contact page.
--}}
@php
    $orgJsonLd = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'Organization',
                '@id' => url('/') . '/#org',
                'name' => 'Divers Hub',
                'url' => url('/') . '/',
                'logo' => asset('assets/img/pwa/icon-512.png'),
                'email' => 'support@divers-hub.com',
                'address' => [
                    '@type' => 'PostalAddress',
                    'streetAddress' => '681 Ranch Rd',
                    'addressLocality' => 'Weston',
                    'addressRegion' => 'FL',
                    'postalCode' => '33326',
                    'addressCountry' => 'US',
                ],
                'areaServed' => 'South Florida and the Florida Keys',
            ],
            [
                '@type' => 'WebSite',
                '@id' => url('/') . '/#website',
                'name' => 'Divers Hub',
                'url' => url('/') . '/',
                'publisher' => ['@id' => url('/') . '/#org'],
            ],
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($orgJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>

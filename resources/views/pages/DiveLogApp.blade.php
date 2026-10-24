{{--
    Public page for the Logbook app (SEO audit, 2026-10-24). /Logbook itself
    is members-only and redirects to sign-in, so there was no page that said
    what the app does. Store buttons follow config('divehub.app_store_url')
    and config('divehub.play_store_url'): "coming soon" until each is set.
    Only features that exist today are listed.
--}}
@php
    $appStore = config('divehub.app_store_url');
    $playStore = config('divehub.play_store_url');
    $features = [
        ['bluetooth', 'Download over Bluetooth', 'Shearwater (Perdix, Petrel, Teric, Peregrine), Oceanic and the other Bluetooth dive computers supported by the open-source libdivecomputer project. Download at the dock; only new dives come across.'],
        ['upload_file', 'Import your old logbook', 'MacDive (XML or UDDF), Subsurface and other UDDF apps, Shearwater Cloud exports and Garmin Descent .FIT files. Duplicates are spotted for you, and a value no dive can have is flagged before anything is saved.'],
        ['watch', 'Apple Watch and Garmin', 'Underwater dives from Apple Health (Oceanic+ and the Depth app) come in from the iPhone app; Garmin Descent dives bring heart rate and the GPS entry and exit points.'],
        ['devices', 'Every computer on one dive', 'Wore a Petrel and a NERD, or a backup Perdix? Divers Hub sees it is the same dive and offers to combine them: one dive, each computer\'s profile, tank pressure from the transmitter, deco from whichever computer had it.'],
        ['show_chart', 'A chart worth reading', 'Depth with the deco ceiling, PPO2, NDL, temperature, heart rate, tank pressure, and SAC and RMV per cylinder once you pick the tank, in feet or metres.'],
        ['route', 'Entry, exit and current', 'With a GPS fix going in and coming out, the dive shows a map of both points, the distance you drifted and the current in knots.'],
        ['photo_camera', 'Photos, videos and marine life', 'Add photos and short videos to each dive, tag the species you saw, or ask "What\'s this?" to identify a fish from a photo. Share any of them on the dive site\'s public page if you like.'],
        ['inventory_2', 'Your gear', 'Keep your regulators, computers, tanks, suits and fins, attach them to dives, and save the configurations you dive most.'],
        ['map', 'Sites you know', 'Dives link to the Divers Hub catalog of South Florida reefs and wrecks, or to your own sites anywhere in the world, all on one map.'],
    ];
    $faq = [
        ['Is it free?', 'Yes. The logbook, the app and the imports are free, with no ads and no subscription.'],
        ['Which dive computers work?', 'Over Bluetooth: Shearwater, Oceanic and the other Bluetooth computers libdivecomputer supports. By file: Garmin Descent (.FIT), Shearwater Cloud (XML), MacDive, Subsurface and anything that exports UDDF.'],
        ['Is there an Android app?', $playStore ? 'Yes, on Google Play.' : 'It is in testing and coming soon to Google Play. Until then, everything the app does on the phone you can also do on the website.'],
        ['Who can see my dives?', 'Only you. Dives, photos and videos are private unless you choose to share a photo or video on a dive site\'s public page.'],
    ];
    $ld = [
        '@context' => 'https://schema.org',
        '@graph' => [
            array_filter([
                '@type' => 'SoftwareApplication',
                'name' => 'Logbook by Divers Hub',
                'applicationCategory' => 'SportsApplication',
                'operatingSystem' => 'iOS, Android, Web',
                'description' => 'A free dive log: Bluetooth downloads from dive computers, imports from Garmin, Shearwater, MacDive, UDDF and Apple Watch, multi-computer dives and detailed profile charts.',
                'url' => route('DiveLogApp'),
                'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'USD'],
                'publisher' => ['@id' => url('/') . '/#org'],
                'downloadUrl' => $appStore ?: null,
            ]),
            [
                '@type' => 'FAQPage',
                'mainEntity' => array_map(fn ($q) => ['@type' => 'Question', 'name' => $q[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $q[1]]], $faq),
            ],
        ],
    ];
@endphp
<x-page-template bodyClass='dh-shell bg-gray-200' :SEO="$SEO">
    <x-shell.nav active="" />
    <script type="application/ld+json">{!! json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>

    <main class="main-content position-relative h-100 border-radius-lg">
        <x-shell.header title="Logbook by Divers Hub" icon="phone_iphone" />

        <div class="container-fluid py-0 dh-board">
            <section class="dh-app-hero">
                <div>
                    <h1>A free dive log that reads your computer</h1>
                    <p>Logbook by Divers Hub keeps every dive in one place: downloaded over Bluetooth at the dock, imported from the logbook you already have, and combined when you dive with more than one computer. On your iPhone and on the web, with the same account.</p>
                    <div class="dh-app-stores">
                        @if ($appStore)
                            <a class="dh-btn dh-btn-primary" href="{{ $appStore }}" target="_blank" rel="noopener"><span class="material-icons-round" aria-hidden="true">phone_iphone</span> Download on the App Store</a>
                        @else
                            <span class="dh-app-soon"><span class="material-icons-round" aria-hidden="true">phone_iphone</span> iPhone: in beta, coming soon to the App Store</span>
                        @endif
                        @if ($playStore)
                            <a class="dh-btn dh-btn-primary" href="{{ $playStore }}" target="_blank" rel="noopener"><span class="material-icons-round" aria-hidden="true">android</span> Get it on Google Play</a>
                        @else
                            <span class="dh-app-soon"><span class="material-icons-round" aria-hidden="true">android</span> Android: coming soon to Google Play</span>
                        @endif
                    </div>
                    <p class="dh-app-web">Already have an account? <a href="{{ route('Logbook') }}">Open your logbook on the web</a>. New here? <a href="{{ route('create-account') }}">Create a free account</a>.</p>
                </div>
            </section>

            <section class="dh-app-features" aria-labelledby="dhAppFeatures">
                <h2 id="dhAppFeatures">What it does</h2>
                <ul>
                    @foreach ($features as [$icon, $title, $text])
                        <li>
                            <span class="material-icons-round" aria-hidden="true">{{ $icon }}</span>
                            <div><h3>{{ $title }}</h3><p>{{ $text }}</p></div>
                        </li>
                    @endforeach
                </ul>
            </section>

            <section class="dh-panel dh-app-how">
                @include('pages.logbook.app-steps')
            </section>

            <section class="dh-panel dh-legal" aria-labelledby="dhAppFaq">
                <h2 id="dhAppFaq">Questions</h2>
                @foreach ($faq as [$q, $a])
                    <p><strong>{{ $q }}</strong><br>{{ $a }}</p>
                @endforeach
                <p>More in <a href="{{ route('Support') }}">Support</a> and the <a href="{{ route('PrivacyPolicy') }}">Privacy Policy</a>.</p>
            </section>

            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>
</x-page-template>

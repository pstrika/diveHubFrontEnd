{{--
    One indexable schedule page (SEO audit, 2026-10-24): an area or a kind of
    dive, the next 7 days from every operator, same trip cards as /Trips.
    Data from DiveTripsController::data(); nothing personal on the page.
--}}
<x-page-template bodyClass='dh-shell bg-gray-200' :SEO="$SEO">
    <x-shell.nav active="today" />

    <main class="main-content position-relative h-100 border-radius-lg">
        <x-shell.header :title="$page['h1']" :h1="true" />
        @php
            $first = array_key_first($days);
            $last = array_key_last($days);
            $ld = [
                '@context' => 'https://schema.org',
                '@graph' => [
                    [
                        '@type' => 'CollectionPage',
                        'name' => $page['h1'],
                        'description' => $page['desc'],
                        'url' => route('DiveTrips.show', $slug),
                        'isPartOf' => ['@id' => url('/') . '/#website'],
                        'publisher' => ['@id' => url('/') . '/#org'],
                        'about' => ['@type' => 'Thing', 'name' => $page['kind'] === 'area' ? 'Scuba diving trips in ' . $page['name'] . ', Florida' : $page['name'] . ' in South Florida'],
                    ],
                    [
                        '@type' => 'BreadcrumbList',
                        'itemListElement' => [
                            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Dive trips', 'item' => route('Trips')],
                            ['@type' => 'ListItem', 'position' => 2, 'name' => 'By area and type', 'item' => route('DiveTrips')],
                            ['@type' => 'ListItem', 'position' => 3, 'name' => $page['name'], 'item' => route('DiveTrips.show', $slug)],
                        ],
                    ],
                ],
            ];
        @endphp
        <script type="application/ld+json">{!! json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>

        <div class="container-fluid py-0 dh-board">
            <section class="dh-dtrips-intro">
                <p>{{ $page['intro'] }}</p>
                <p class="dh-dtrips-meta">
                    {{ $count }} {{ Str::plural('trip', $count) }} from {{ \Carbon\Carbon::parse($first)->format('l, M j') }} to {{ \Carbon\Carbon::parse($last)->format('l, M j') }}
                    · <a href="{{ $fullBoard }}">Filter by level, seats or operator on the full schedule</a>
                    @if ($guide) · <a href="{{ route('Blog.show', $guide->slug) }}">{{ $guide->title }}</a>@endif
                </p>
            </section>

            {{-- Day strip: one cell per day, tap to jump to it. --}}
            <nav class="dh-daystrip" aria-label="Days on this page">
                @foreach ($days as $d => $dayBoard)
                    @php $c = \Carbon\Carbon::parse($d); @endphp
                    <a href="#day-{{ $d }}" class="{{ $dayBoard['shown'] === 0 ? 'is-empty' : '' }} {{ $c->isWeekend() ? 'is-weekend' : '' }}" title="{{ $c->format('l, F j') }}">
                        <span class="dh-ds-dow">{{ $c->format('D') }}</span>
                        <span class="dh-ds-day">{{ $c->format('j') }}</span>
                        <span class="dh-ds-n">{{ $dayBoard['shown'] ?: 'none' }}</span>
                    </a>
                @endforeach
            </nav>

            @if ($count === 0)
                <div class="dh-empty">
                    <span class="material-icons-round" aria-hidden="true">directions_boat</span>
                    <p>No {{ $page['kind'] === 'area' ? 'trips from ' . $page['name'] : strtolower($page['name']) }} are scheduled in the next {{ \App\Http\Controllers\DiveTripsController::DAYS }} days yet. Operators post new trips every day.</p>
                    <a class="dh-btn dh-btn-primary" href="{{ route('Trips') }}?range=30d">See the next 30 days</a>
                </div>
            @endif

            @foreach ($days as $d => $dayBoard)
                @continue($dayBoard['shown'] === 0)
                <section class="dh-day" id="day-{{ $d }}">
                    <h2 class="dh-day-title">
                        {{ \Carbon\Carbon::parse($d)->format('l, F j') }}
                        <span class="dh-region-count">{{ $dayBoard['shown'] }} {{ Str::plural('trip', $dayBoard['shown']) }}</span>
                    </h2>
                    @include('pages.trips._regions', ['board' => $dayBoard, 'date' => $d, 'query' => '', 'limit' => null, 'favMode' => null])
                </section>
            @endforeach

            @if (count($operators))
                <section class="dh-dtrips-ops">
                    <h2 class="dh-day-title">Operators running these trips</h2>
                    <ul>
                        @foreach ($operators as $op)
                            <li>@if ($op['url'])<a href="{{ $op['url'] }}">{{ $op['name'] }}</a>@else{{ $op['name'] }}@endif <span>{{ $op['n'] }} {{ Str::plural('trip', $op['n']) }}</span></li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <section class="dh-dtrips-more">
                <h2 class="dh-day-title">More dive trips</h2>
                <ul>
                    @foreach ($others as $s => $p)
                        <li><a href="{{ route('DiveTrips.show', $s) }}">{{ $p['kind'] === 'area' ? $p['name'] . ' dive trips' : $p['name'] }}</a></li>
                    @endforeach
                    <li><a href="{{ route('Trips') }}">Today's full schedule</a></li>
                </ul>
            </section>

            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>
</x-page-template>

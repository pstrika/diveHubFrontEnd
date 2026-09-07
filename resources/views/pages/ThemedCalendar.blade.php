<x-page-template bodyClass='dh-shell bg-gray-200' :SEO="$SEO ?? []">
    <x-shell.nav active="me" />

    <main class="main-content position-relative h-100 border-radius-lg">
        <x-shell.header :title="$calendar['title']" />

        <div class="container-fluid py-0 dh-board">
            {{--
                Themed calendars (Technical, Recreational, Wreck, Shark, Lobster).
                Five near identical table views collapsed into this one, driven by
                $calendar (title, intro, routeBase, chip) from CalendarTController.
                The trips are the same card arrays the trip board uses
                (App\Support\TripBoard::card), grouped by day for the month shown.
                The board's type chips are the new front door to these lists; the
                URLs stay because they are linked and bookmarked.
            --}}

            <div class="dh-datebar">
                <a class="dh-btn dh-btn-ghost-dark dh-datebtn" href="{{ $calendar['routeBase'] }}/{{ $prevMonthS }}" aria-label="Previous month" @if($controlNav === 'disabled') aria-disabled="true" tabindex="-1" @endif>
                    <span class="material-icons-round">chevron_left</span>
                </a>
                <div class="dh-datebar-center">
                    <h1 class="dh-date-title">{{ $calendar['title'] }} <span class="text-muted fw-normal">{{ $currentMonthS }} {{ $year }}</span></h1>
                </div>
                <a class="dh-btn dh-btn-ghost-dark dh-datebtn" href="{{ $calendar['routeBase'] }}/{{ $nextMonthS }}" aria-label="Next month">
                    <span class="material-icons-round">chevron_right</span>
                </a>
            </div>

            @if(!empty($calendar['intro']))
                <p class="dh-board-count">{{ $calendar['intro'] }}
                    <a href="{{ route('Trips') }}{{ $calendar['boardQuery'] ?? '' }}">See these trips on today's board</a>
                </p>
            @endif

            @forelse($days as $day)
                <section class="dh-region">
                    <header class="dh-region-head">
                        <h2 class="dh-region-title">{{ \Carbon\Carbon::parse($day['date'])->format('l, F j') }}
                            <span class="dh-region-count">{{ count($day['trips']) }} {{ Str::plural('trip', count($day['trips'])) }}</span>
                        </h2>
                        <a class="dh-why" href="{{ route('Trips') }}/{{ $day['date'] }}">Full board for this day</a>
                    </header>
                    <div class="dh-region-trips">
                        @foreach($day['trips'] as $trip)
                            <x-trip-card :trip="$trip" />
                        @endforeach
                    </div>
                </section>
            @empty
                <div class="dh-empty">
                    <span class="material-icons-round" aria-hidden="true">event_busy</span>
                    <p>No {{ strtolower($calendar['title']) }} trips are listed for {{ $currentMonthS }} yet.</p>
                    <a class="dh-btn dh-btn-primary" href="{{ $calendar['routeBase'] }}/{{ $nextMonthS }}">Try next month</a>
                </div>
            @endforelse

            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>
</x-page-template>

<x-page-template bodyClass='dh-shell bg-gray-200' :SEO="$SEO">
    <x-shell.nav active="today" />

    <main class="main-content position-relative h-100 border-radius-lg">
        <x-shell.header :title="$mode === 'day' ? 'Dive Today' : 'Find a dive'" />

        <div class="container-fluid py-0 dh-board">
            {{--
                Trip finder (proposal W2, extended 2026-09-10). One page, two modes:

                  day    /Trips and /Trips/{date}: one day's board grouped by coast,
                         the stepper as the primary control. Unchanged URLs.
                  range  /Trips?range=weekend|nextweekend|7d|30d or ?from=&to=:
                         several days, each rendered as its own board with up to a
                         few cards per coast and a link to the full day. A strip of
                         day cells shows where the matching trips are.

                Filters (region, level, type, seats) are the same chips in both
                modes and travel in the query string, so every view is a shareable
                URL and works without JavaScript. Data comes from
                App\Support\TripBoard as plain arrays ($board, $days).
            --}}

            @include('pages.trips._dates')

            {{-- Filter chips (W2 note 2). Counts cover the whole range in range mode. --}}
            <div class="dh-filters">
                <x-query-chips param="region" :options="$board['regionOptions']" :selected="$board['filters']['region']" all="All regions" label="Region" />
                <x-dive-level.filter-chips :selected="$board['filters']['level']" :counts="$board['levelCounts']" />
                @if(count($board['typeOptions']) > 1 || $board['filters']['type'])
                    <x-query-chips param="type" :options="$board['typeOptions']" :selected="$board['filters']['type']" all="All trips" label="Type" />
                @endif
                <x-query-chips param="seats" :options="['1' => 'Seats available only']" :selected="$board['filters']['seats'] ? '1' : null" all="" label="" toggle />
            </div>

            @php
                $clearUrl = $mode === 'day'
                    ? route('Trips') . '/' . $date
                    : route('Trips') . '?' . http_build_query($rangeKey ? ['range' => $rangeKey] : ['from' => $from, 'to' => $to]);
            @endphp
            <p class="dh-board-count">
                @if($board['shown'] === $board['total'])
                    {{ $board['total'] }} {{ Str::plural('trip', $board['total']) }}
                @else
                    {{ $board['shown'] }} of {{ $board['total'] }} trips
                    <a href="{{ $clearUrl }}">clear filters</a>
                @endif
            </p>

            @if($mode === 'day')
                @if(count($board['groups']))
                    @include('pages.trips._regions', ['board' => $board, 'date' => $date, 'query' => $query, 'limit' => null])
                @else
                    <div class="dh-empty">
                        <span class="material-icons-round" aria-hidden="true">sailing</span>
                        <p>No trips match on this day.</p>
                        @if($board['total'] > 0)
                            <a class="dh-btn dh-btn-primary" href="{{ route('Trips') }}/{{ $date }}">Clear filters</a>
                        @else
                            <a class="dh-btn dh-btn-primary" href="{{ route('Trips') }}/{{ $nextDay }}">Try the next day</a>
                        @endif
                    </div>
                @endif
            @else
                {{-- Day strip: one cell per day in the range, count of matching trips, tap to open that day. --}}
                <nav class="dh-daystrip" aria-label="Days in this range">
                    @foreach($days as $d => $dayBoard)
                        @php $c = \Carbon\Carbon::parse($d); @endphp
                        <a href="{{ route('Trips') }}/{{ $d }}{{ $query }}" class="{{ $dayBoard['shown'] === 0 ? 'is-empty' : '' }} {{ $c->isWeekend() ? 'is-weekend' : '' }}" title="{{ $c->format('l, F j') }}">
                            <span class="dh-ds-dow">{{ $c->format('D') }}</span>
                            <span class="dh-ds-day">{{ $c->format('j') }}</span>
                            <span class="dh-ds-n">{{ $dayBoard['shown'] ?: 'none' }}</span>
                        </a>
                    @endforeach
                </nav>

                @php $anyShown = collect($days)->sum('shown'); @endphp
                @if($anyShown === 0)
                    <div class="dh-empty">
                        <span class="material-icons-round" aria-hidden="true">sailing</span>
                        <p>No trips match between these dates.</p>
                        @if($board['total'] > 0)
                            <a class="dh-btn dh-btn-primary" href="{{ $clearUrl }}">Clear filters</a>
                        @else
                            <a class="dh-btn dh-btn-primary" href="{{ route('Trips') }}?range=30d{{ $query ? '&' . ltrim($query, '?') : '' }}">Try the next 30 days</a>
                        @endif
                    </div>
                @endif

                @foreach($days as $d => $dayBoard)
                    @continue($dayBoard['shown'] === 0)
                    <section class="dh-day" id="day-{{ $d }}">
                        <h2 class="dh-day-title">
                            {{ \Carbon\Carbon::parse($d)->format('l, F j') }}
                            <span class="dh-region-count">{{ $dayBoard['shown'] }} {{ Str::plural('trip', $dayBoard['shown']) }}</span>
                            <a class="dh-why" href="{{ route('Trips') }}/{{ $d }}{{ $query }}">Full board for this day</a>
                        </h2>
                        {{-- Three cards per coast keeps a 30 day search light; the link above has the rest. --}}
                        @include('pages.trips._regions', ['board' => $dayBoard, 'date' => $d, 'query' => $query, 'limit' => 3])
                    </section>
                @endforeach
            @endif

            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>
</x-page-template>

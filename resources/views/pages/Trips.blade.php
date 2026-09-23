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
                <x-query-chips param="seats" :options="['1' => 'Seats available only']" :selected="$board['filters']['seats'] ? '1' : null" all="" label="Availability:" toggle />
                @include('pages.trips._operators')
            </div>

            @php
                // cleared=1 tells the filter-memory script below not to
                // restore the diver's saved filters onto this link - an
                // explicit "clear filters" click means they want none,
                // not last time's picks reapplied (Pablo, 2026-09-22).
                $clearUrl = $mode === 'day'
                    ? route('Trips') . '/' . $date . '?cleared=1'
                    : route('Trips') . '?' . http_build_query(($rangeKey ? ['range' => $rangeKey] : ['from' => $from, 'to' => $to]) + ['cleared' => 1]);
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
                        <span class="material-icons-round" aria-hidden="true">directions_boat</span>
                        <p>No trips match on this day.</p>
                        @if($board['total'] > 0)
                            <a class="dh-btn dh-btn-primary" href="{{ $clearUrl }}">Clear filters</a>
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
                        <span class="material-icons-round" aria-hidden="true">directions_boat</span>
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

    @push('js')
    <script>
        (function () {
            // Remember the finder's filter chips (region/level/type/seats/op)
            // in localStorage - not sessionStorage - so they survive a new
            // tab or a closed browser too, not just staying inside the same
            // tab's session (Pablo, 2026-09-22: "I used to store the data
            // locally so the filters don't reset every time the page is
            // loaded"). Landing here fresh (e.g. from the bottom nav)
            // reapplies the diver's last picks. Any URL that already
            // carries a filter is a deliberate/shared link and is left
            // alone; it just becomes the new memory.
            //
            // Date wasn't part of this at all (Pablo, 2026-09-24: "if I set
            // a date filter, it's not persistent"): range/from/to are query
            // params like the other filters, so they just needed adding to
            // FIELDS below - but the single-day picker lives in the URL
            // PATH (/Trips/{date}), not a query param, so it needs its own
            // small key and its own redirect-with-path-segment restore.
            var KEY = 'dh-trips-filters';
            var DATE_KEY = 'dh-trips-date';
            var FIELDS = ['region', 'level', 'type', 'seats', 'op', 'range', 'from', 'to'];
            var params = new URLSearchParams(window.location.search);
            var hasAny = FIELDS.some(function (f) { return params.has(f); });

            var basePath = (new URL(@json(route('Trips')), window.location.origin)).pathname.replace(/\/+$/, '');
            var currentPath = window.location.pathname.replace(/\/+$/, '');
            var isBareDay = currentPath === basePath;

            // An explicit "Clear filters" click carries this - without it,
            // the restore below would just reapply the very filters the
            // diver clicked to clear (Pablo, 2026-09-22, found while
            // fixing the above: this is the intended way to lose a
            // saved filter set at all).
            if (params.has('cleared')) {
                try { localStorage.removeItem(KEY); localStorage.removeItem(DATE_KEY); } catch (e) {}
                params.delete('cleared');
                var cleanUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
                if (cleanUrl !== window.location.pathname + window.location.search) {
                    window.location.replace(cleanUrl);
                    return;
                }
            } else if (!hasAny && isBareDay) {
                var saved = null, savedDate = null;
                try {
                    saved = localStorage.getItem(KEY);
                    savedDate = localStorage.getItem(DATE_KEY);
                } catch (e) {}
                if (saved) {
                    new URLSearchParams(saved).forEach(function (v, k) {
                        if (FIELDS.indexOf(k) !== -1) { params.set(k, v); }
                    });
                }
                // A saved range/custom range wins over a saved single day -
                // they're mutually exclusive modes, and picking a range was
                // the more recent, more deliberate choice.
                if (params.has('range') || params.has('from') || params.has('to')) {
                    window.location.replace(basePath + '?' + params.toString());
                    return;
                }
                if (savedDate) {
                    window.location.replace(basePath + '/' + savedDate + (params.toString() ? '?' + params.toString() : ''));
                    return;
                }
                if (params.toString()) {
                    window.location.replace(window.location.pathname + '?' + params.toString());
                    return;
                }
            }

            var current = new URLSearchParams();
            FIELDS.forEach(function (f) {
                if (params.has(f)) current.set(f, params.get(f));
            });
            try { localStorage.setItem(KEY, current.toString()); } catch (e) {}

            // Day-mode date is only ever a path segment - save it separately
            // whenever we're not in range mode (range/from/to already
            // covered above via FIELDS). Bare (today) always clears any
            // stale saved date, regardless of whether other filters are
            // active - otherwise clicking "Today" while a region/level
            // filter is still on wouldn't stop a later bare landing from
            // jumping back to the old date.
            if (!params.has('range') && !params.has('from') && !params.has('to')) {
                if (isBareDay) {
                    try { localStorage.removeItem(DATE_KEY); } catch (e) {}
                } else {
                    var seg = currentPath.slice(basePath.length).replace(/^\/+/, '');
                    if (seg) { try { localStorage.setItem(DATE_KEY, seg); } catch (e) {} }
                }
            }
        })();
    </script>
    @endpush
</x-page-template>

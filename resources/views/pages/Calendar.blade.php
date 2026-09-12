<x-page-template bodyClass='dh-shell bg-gray-200' :SEO="$SEO">
    <x-shell.nav active="today" />

    <main class="main-content position-relative h-100 border-radius-lg">
        <x-shell.header :title="$pageTitle" :icon="$headerIcon" />

        <div class="container-fluid py-0 dh-board">
            {{--
                Themed calendars (proposal restored 2026-09-11, extended
                2026-09-12): a real grid, one per theme (recreational,
                technical, wreck, shark, lobster), 3 days on phones, a week on
                tablets, a month on desktop (App\Support divershub-calendar.js,
                shared by every calendar in the app). Prev/next step by
                whichever of those is active and land on a fresh page for that
                day, which is why they're buttons with a click handler rather
                than plain links - the step size is only known once the
                viewport is. The trip list below always covers the whole
                month the grid's day belongs to, paginated 20 dives at a time,
                regardless of which of the three views is showing.
            --}}
            @if($about)
                <details class="dh-dash-collapse dh-about-card">
                    <summary>
                        <span class="material-icons-round" aria-hidden="true">info</span>
                        {{ $about['title'] }}
                    </summary>
                    <div class="dh-about-body">
                        <img src="{{ asset('assets') }}/{{ $about['image'] }}" alt="{{ $about['title'] }}" class="dh-about-img">
                        <div class="dh-about-text">
                            @foreach($about['paragraphs'] as $p)
                                <p>{{ $p }}</p>
                            @endforeach
                        </div>
                    </div>
                </details>
            @endif

            <div class="dh-datebar">
                <button type="button" class="dh-btn dh-btn-ghost-dark dh-datebtn" id="dhCalPrev" aria-label="Previous" @if($prevDisabled) aria-disabled="true" style="visibility:hidden" @endif>
                    <span class="material-icons-round">chevron_left</span>
                </button>
                <div class="dh-datebar-center">
                    <h1 class="dh-date-title">{{ $monthLabel }}</h1>
                </div>
                <button type="button" class="dh-btn dh-btn-ghost-dark dh-datebtn" id="dhCalNext" aria-label="Next">
                    <span class="material-icons-round">chevron_right</span>
                </button>
            </div>

            <section class="dh-panel dh-calendar-panel">
                <div class="calendar" id="themedCalendar"></div>
                @if(count($legend))
                    <div class="dh-cal-legend">
                        <span class="dh-cal-legend-label">Operators</span>
                        @foreach($legend as $l)
                            <span class="dh-cal-legend-item"><span class="dh-cal-legend-swatch" style="background:{{ $l['color'] }}"></span>{{ $l['name'] }}</span>
                        @endforeach
                        @if($hasOtherOperators)
                            <span class="dh-cal-legend-item"><span class="dh-cal-legend-swatch" style="background:{{ $otherColor }}"></span>Other operators</span>
                        @endif
                    </div>
                @endif
            </section>

            @if($totalMonthTrips === 0)
                <div class="dh-empty">
                    <span class="material-icons-round" aria-hidden="true">sailing</span>
                    <p>No {{ strtolower($title) }} trips this month.</p>
                </div>
            @else
                <p class="dh-board-count" id="dhCalMore">{{ $shownCount }} of {{ $totalMonthTrips }} {{ Str::plural('trip', $totalMonthTrips) }} this month</p>

                @foreach($byDate as $date => $dayCards)
                    <section class="dh-day" id="day-{{ $date }}">
                        <h2 class="dh-day-title">
                            {{ \Carbon\Carbon::parse($date)->format('l, F j') }}
                            <span class="dh-region-count">{{ count($dayCards) }} {{ Str::plural('trip', count($dayCards)) }}</span>
                            <a class="dh-why" href="{{ route('Trips') }}/{{ $date }}?type={{ $type }}">Full board for this day</a>
                        </h2>
                        @foreach($dayCards as $card)
                            <x-trip-card :trip="$card" />
                        @endforeach
                    </section>
                @endforeach

                @if($remaining > 0)
                    <a class="dh-showmore" href="{{ url($moreUrl) }}#dhCalMore">Show {{ min($remaining, 20) }} more {{ Str::plural('trip', $remaining) }} this month</a>
                @endif
            @endif

            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>

    @push('js')
    <script src="{{ asset('assets') }}/js/plugins/fullcalendar.min.js"></script>
    <script src="{{ asset('assets') }}/js/divershub-calendar.js"></script>
    <script>
        (function () {
            var calendarUrl = '{{ url($calendarUrl) }}';
            var anchor = '{{ $anchorDate }}';
            var today = '{{ $today }}';

            var calendar = new FullCalendar.Calendar(document.getElementById('themedCalendar'), {
                initialView: getResponsiveView(),
                windowResize: function () { calendar.changeView(getResponsiveView()); },
                initialDate: anchor,
                firstDay: {{ $firstDayOfWeek }},
                contentHeight: 'auto',
                headerToolbar: { start: '', center: '', end: '' },
                dayMaxEvents: 5,
                selectable: false,
                editable: false,
                dateClick: function (info) {
                    window.location.href = '{{ route('Trips') }}/' + info.dateStr + '?type={{ $type }}';
                },
                events: [
                    @foreach($events as $card)
                    {
                        title: {!! json_encode($card['title']) !!},
                        start: {!! json_encode($card['date'] . ' ' . $card['time24']) !!},
                        url: {!! json_encode($card['detailsUrl']) !!},
                        color: {!! json_encode($card['color']) !!},
                        className: '{{ $card['availability']['state'] === 'departed' ? 'dh-tripfc-departed' : '' }}'
                    },
                    @endforeach
                ],
                views: dhResponsiveViews
            });
            calendar.render();

            dhWireCalendarNav('dhCalPrev', 'dhCalNext', calendarUrl, anchor, today);
        })();
    </script>
    @endpush
</x-page-template>

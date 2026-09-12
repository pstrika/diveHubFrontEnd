<x-page-template bodyClass='dh-shell bg-gray-200'>
    <x-shell.nav active="me" />

    {{-- Edit event: eventClick on the grid opens this with book/waiver/remove actions for that trip. --}}
    <div class="modal fade" id="modal-calendar" tabindex="-1" role="dialog" aria-labelledby="modal-title-notification-calendar" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-normal" id="modal-title-notification-calendar">Trip details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">
                        <span id="span-booked" class="chip chip-static chip-yes" hidden>Booked</span>
                        <span id="span-not-booked" class="chip chip-static" hidden>Not booked yet</span>
                        <span id="span-waiver-signed" class="chip chip-static chip-yes" hidden>Waiver signed</span>
                    </p>
                </div>
                <div class="modal-footer flex-wrap gap-2 justify-content-start">
                    <a id="button-go" href="" class="dh-btn dh-btn-ghost-dark"><span class="material-icons-round">visibility</span>View trip</a>
                    <a id="button-link" href="" target="_blank" class="dh-btn dh-btn-ghost-dark"><span class="material-icons-round">link</span>Booking page</a>
                    <a id="button-waiver" href="" target="_blank" class="dh-btn dh-btn-ghost-dark"><span class="material-icons-round">description</span>Sign waiver</a>
                    <a id="button-waiver-signed" href="" class="dh-btn dh-btn-ghost-dark"><span class="material-icons-round">assignment_turned_in</span>Mark waiver signed</a>
                    <a id="button-book" href="" class="dh-btn dh-btn-primary"><span class="material-icons-round">check</span>I'm booked</a>
                    <a id="button-remove" href="" class="dh-btn dh-btn-danger"><span class="material-icons-round">delete</span>Remove from calendar</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Subscribe to .ics feed --}}
    @if($calendarFeedUrl)
    <div class="modal fade" id="modalSubscribeIcs" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-normal"><span class="material-icons-round align-middle">event_available</span> Subscribe to my dive calendar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if (session('calendarTokenRegenerated'))
                        <div class="dh-form-error mb-2">Your calendar link was regenerated. The old link no longer works — re-share the new one below.</div>
                    @endif
                    <p class="text-sm text-secondary mb-2">
                        Paste this link into Google Calendar, Apple Calendar or Outlook (as a
                        subscribed/"from URL" calendar) to keep your dives in sync automatically.
                        Anyone you give this link to can see your upcoming dives, so only share it with people you trust.
                    </p>
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <input type="text" id="calendarFeedUrl" class="form-control border w-auto flex-grow-1"
                               value="{{ $calendarFeedUrl }}" readonly onclick="this.select();" style="min-width: 200px;">
                        <button type="button" class="dh-btn dh-btn-primary" onclick="copyCalendarFeedUrl()">
                            <span class="material-icons-round align-middle">content_copy</span> Copy
                        </button>
                    </div>
                    <div class="mt-3 text-end">
                        <form action="{{ route('MyCalendar.regenerateToken') }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Regenerate your calendar link? Anyone using the current link will lose access.');">
                            @csrf
                            <button type="submit" class="dh-link-danger">Regenerate link</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <main class="main-content position-relative h-100 border-radius-lg">
        <x-shell.header title="My Calendar" icon="event" />

        <div class="container-fluid py-0 dh-board">
            {{--
                Retheme (2026-09-12) onto the same criteria as every other
                calendar in the app: anchor day defaults to today (not the
                1st of the month), prev/next step by whichever of 3 day/week/
                month is active (App\Support divershub-calendar.js), and the
                trip list below always covers the whole month the anchor
                falls in, built from App\Support\TripBoard::card() like the
                trip finder and the themed calendars so a trip looks the same
                everywhere. No operator legend here (booked/not booked is the
                only color this calendar needs) and no list pagination (a
                personal saved-trips list is small by nature).
            --}}
            @if($calendarFeedUrl)
                <div class="d-flex justify-content-end mb-2">
                    <button type="button" class="dh-btn dh-btn-ghost-dark" data-bs-toggle="modal" data-bs-target="#modalSubscribeIcs">
                        <span class="material-icons-round" aria-hidden="true">event</span>Subscribe
                    </button>
                </div>
            @endif

            @if(!auth()->user()->isNotGuest())
                {{-- Guest empty state (show, then gate). The calendar below still renders, just empty. --}}
                <div class="dh-panel dh-guest-note">
                    <span class="material-icons-round" aria-hidden="true">event_available</span>
                    <div>
                        <h2 class="dh-panel-title mb-1">This is your dive calendar</h2>
                        <p class="mb-2">Save any trip from <a href="{{ route('Trips') }}">Dive Today</a> and it shows up here, with a link you can subscribe to from Google or Apple Calendar.</p>
                        <a class="dh-btn dh-btn-primary" href="{{ route('create-account') }}">Create a free account</a>
                        <a class="dh-btn dh-btn-ghost-dark" href="{{ route('login') }}">Sign in</a>
                    </div>
                </div>
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
                <div class="calendar" id="myCalendar"></div>
                <p class="text-xs text-secondary mb-0 mt-2"><span class="dh-cal-key is-booked"></span> booked &nbsp; <span class="dh-cal-key is-open"></span> not booked yet</p>
            </section>

            @if($totalTrips === 0)
                <div class="dh-empty">
                    <span class="material-icons-round" aria-hidden="true">sailing</span>
                    <p>No saved trips this month.</p>
                </div>
            @else
                @foreach($byDate as $date => $dayCards)
                    <section class="dh-day" id="day-{{ $date }}">
                        <h2 class="dh-day-title">
                            {{ \Carbon\Carbon::parse($date)->format('l, F j') }}
                            <span class="dh-region-count">{{ count($dayCards) }} {{ Str::plural('trip', count($dayCards)) }}</span>
                        </h2>
                        @foreach($dayCards as $card)
                            <x-trip-card :trip="$card" />
                        @endforeach
                    </section>
                @endforeach
            @endif

            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>

    @push('js')
    <script src="{{ asset('assets') }}/js/plugins/fullcalendar.min.js"></script>
    <script src="{{ asset('assets') }}/js/divershub-calendar.js"></script>
    <script>
        (function () {
            var calendarUrl = '{{ url(route('MyCalendar')) }}';
            var anchor = '{{ $anchorDate }}';
            var today = '{{ $today }}';

            var calendar = new FullCalendar.Calendar(document.getElementById('myCalendar'), {
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
                    window.location.href = '{{ route('Trips') }}/' + info.dateStr;
                },
                eventClick: function (info) {
                    var p = info.event.extendedProps;
                    document.getElementById('modal-title-notification-calendar').innerHTML =
                        info.event.title + '<br><span class="text-sm text-secondary">' + p.operator + '</span>';

                    document.getElementById('button-go').href = '{{ url('TripDetails') }}/' + p.myId;
                    document.getElementById('button-book').href = '{{ url('SetEventBook') }}/' + p.eventId;
                    document.getElementById('button-remove').href = '{{ url('RemoveFromCalendar') }}/' + p.eventId;
                    document.getElementById('button-link').href = p.linkToBook;
                    document.getElementById('button-waiver').href = p.waiver;
                    document.getElementById('button-waiver-signed').href = '{{ url('SetEventWaiverSigned') }}/' + p.eventId;

                    var isBooked = p.booked == '1';
                    var isWaiverSigned = p.waiverSigned == '1';

                    document.getElementById('button-book').hidden = isBooked;
                    document.getElementById('button-link').hidden = isBooked;
                    document.getElementById('span-booked').hidden = !isBooked;
                    document.getElementById('span-not-booked').hidden = isBooked;

                    document.getElementById('button-waiver').hidden = !p.waiver;

                    // "Mark waiver signed" only makes sense once booked and only while
                    // there's actually a waiver to sign; once signed, show a chip instead.
                    document.getElementById('button-waiver-signed').hidden = !(isBooked && p.waiver && !isWaiverSigned);
                    document.getElementById('span-waiver-signed').hidden = !(isBooked && p.waiver && isWaiverSigned);

                    new bootstrap.Modal(document.getElementById('modal-calendar')).show();
                },
                events: [
                    @foreach($events as $card)
                    {
                        title: {!! json_encode($card['title']) !!},
                        start: {!! json_encode($card['date'] . ' ' . $card['time24']) !!},
                        color: '{{ $card['booked'] ? '#0f7b3f' : '#c0392b' }}',
                        extendedProps: {
                            myId: {!! json_encode($card['id']) !!},
                            operator: {!! json_encode($card['operatorName']) !!},
                            eventId: {!! json_encode($card['eventId']) !!},
                            booked: {!! json_encode($card['booked'] ? '1' : '0') !!},
                            waiverSigned: {!! json_encode($card['waiverSigned'] ? '1' : '0') !!},
                            linkToBook: {!! json_encode($card['bookUrl']) !!},
                            waiver: {!! json_encode($card['waiver']) !!}
                        }
                    },
                    @endforeach
                ],
                views: dhResponsiveViews
            });
            calendar.render();

            dhWireCalendarNav('dhCalPrev', 'dhCalNext', calendarUrl, anchor, today);
        })();
    </script>

    @if($calendarFeedUrl)
    <script>
        function copyCalendarFeedUrl() {
            var input = document.getElementById('calendarFeedUrl');
            input.select();
            input.setSelectionRange(0, 99999); // mobile
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(input.value);
            } else {
                document.execCommand('copy'); // fallback for older browsers
            }
        }
    </script>
    @endif
    @endpush
</x-page-template>

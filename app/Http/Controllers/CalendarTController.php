<?php

namespace App\Http\Controllers;

use App\Models\Operator;
use App\Models\Site;
use App\Models\Trip;
use App\Support\TripBoard;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CalendarTController extends Controller
{
    /** Trip list below the grid: dives shown before "Show more", and how many more each click reveals. */
    private const PAGE_SIZE = 20;

    /** Grid pills shipped to the browser per day, before FullCalendar's own "+N more" popover takes over. */
    private const GRID_MAX_PER_DAY = 20;

    /**
     * Grid pills are colored by operator (the old calendars did this; the
     * finder's own state colors are still there in the trip cards below). A
     * fixed palette rather than one row per operator in the DB: any month can
     * have a couple dozen operators, so only the busiest few earn their own
     * color and the rest share "Other operators" like the old legend's "OTHER" -
     * and only operators with a trip actually in view make the legend at all.
     */
    private const OPERATOR_PALETTE = ['#0e7c9e', '#b0532a', '#2e7d4f', '#9c7c15', '#5b4fc9', '#c2417a', '#1f6f6f', '#4062bb'];
    private const OPERATOR_OTHER_COLOR = '#8a95a1';

    /**
     * Page heading (2026-09-12): the full name plus the same theme-colored
     * icon shown for this calendar in the drawer (App\Support\IconSvg),
     * rather than the short noun $title uses elsewhere on this page (SEO
     * text, the empty state, "no dives found" sentences).
     */
    private const HEADER = [
        'rec'     => ['title' => 'Recreational Diving Calendar', 'icon' => 'icons_calendar_rec.svg'],
        'tec'     => ['title' => 'Technical Diving Calendar', 'icon' => 'icons_calendar_tec.svg'],
        'wreck'   => ['title' => 'Wreck Diving Calendar', 'icon' => 'wreck_icon.svg'],
        'shark'   => ['title' => 'Shark Diving Calendar', 'icon' => 'icons_calendar_shark.svg'],
        'lobster' => ['title' => 'Lobster Hunting Calendar', 'icon' => 'icons_calendar_lobster.svg'],
    ];

    /** Shark and lobster get a collapsible "About" card above the calendar, carried over from the old front end. */
    private const ABOUT = [
        'shark' => [
            'title' => 'About Shark diving',
            'image' => 'img/illustrations/sharkDiving.webp',
            'paragraphs' => [
                'South Florida boasts more shark species than any other area globally. During your shark diving adventure, you might encounter hammerheads, nurse sharks, and other fascinating creatures. These baited trips take place beyond three miles from shore, where the Gulf Stream’s deep waters host some of the ocean’s largest predatory sharks. So, if you’re ready for an adrenaline-packed underwater encounter, South Florida is the place to be!',
                'You’re aboard a boat, venturing beyond three miles from the shore. The deep waters of the Gulf Stream surround you, and anticipation fills the air. These baited trips are carefully orchestrated to attract sharks. As you descend into the ocean depths, you’ll find yourself face-to-face with some of the ocean’s largest predatory sharks. It’s an adrenaline-packed experience that leaves a lasting impression.',
            ],
        ],
        'lobster' => [
            'title' => 'About Lobster hunting',
            'image' => 'img/illustrations/lobster.webp',
            'paragraphs' => [
                'Sport Season (Mini-Season): The recreational fishery for spiny lobsters begins with a two-day sport season. This season occurs on the last consecutive Wednesday and Thursday of July each year. During the sport season, divers and lobster enthusiasts can head out to catch these delicious crustaceans. Keep in mind that night diving is prohibited in Monroe County during this period.',
                'Regular Season: Following the sport season, the regular spiny lobster season starts on August 6 and runs through March 31. During this time, lobster enthusiasts can continue their pursuit of these tasty creatures. The daily bag limit varies depending on the location: 6 per person for Monroe County and Biscayne National Park, and 12 per person for the rest of Florida. Remember to measure the carapace (shell) size, as it must be larger than 3 inches in the water. Also, possession and use of a measuring device are required at all times.',
            ],
        ],
    ];

    /**
     * The themed calendars (recreational, technical, wreck, shark, lobster) are
     * full month grids, one card per trip, built the same way as the trip
     * finder (App\Support\TripBoard::card()) so a trip looks the same
     * everywhere. Restored 2026-09-11 after a finder-preset version proved to
     * not be what divers wanted from a "calendar"; extended 2026-09-12:
     *
     *   - $date is the exact anchor day (defaults to today), not forced to the
     *     1st of its month - the grid's initialDate needs to be today so the
     *     3 day/week views actually open on today, and prev/next step by the
     *     active view's own width (client-side, see the Blade view's script)
     *     landing back here on whatever day that lands on.
     *   - The trip list below always covers the WHOLE month containing that
     *     anchor day, however the grid itself is currently sliced, paginated
     *     20 at a time (?show=N) rather than capped per day.
     *   - Grid pills are not capped per day either: FullCalendar's own
     *     dayMaxEvents folds a busy day into a native "+N more" popover.
     *
     * @param \Closure(Builder):void $applyTypeFilter narrows the trip query to this theme
     */
    private function month(string $type, string $title, ?string $date, \Closure $applyTypeFilter, string $calendarUrl): View
    {
        $today = Carbon::today();
        $target = $date ? Carbon::parse($date) : $today->copy();
        if ($target->lt($today)) {
            $target = $today->copy();
        }
        $monthStart = $target->copy()->startOfMonth();
        $monthEnd = $target->copy()->endOfMonth();

        // FullCalendar's month grid shows the leading/trailing days of neighbouring
        // weeks too; query that wider window so those cells (and a week/3-day
        // view landing near a month edge) are not empty.
        $gridFrom = $monthStart->copy()->startOfWeek(Carbon::SUNDAY);
        $gridTo = $monthEnd->copy()->endOfWeek(Carbon::SUNDAY);

        $query = Trip::whereBetween('date', [$gridFrom->toDateString(), $gridTo->toDateString()])
            ->whereDate('date', '>=', $today->toDateString());
        $applyTypeFilter($query);
        $trips = $query->get()->sortBy('departureTime');

        $sites = Site::select('id', 'name', 'type', 'slug', 'maxDepth', 'level')->get()->keyBy('id');
        $operators = Operator::select('id', 'location', 'phone')->get()->keyBy('id')->all();

        $now = Carbon::now();
        $cards = [];
        foreach ($trips as $trip) {
            $siteIds = $trip->siteId ? explode(',', $trip->siteId) : [];
            $trip->site = array_values(array_filter(array_map(fn ($id) => $sites->get((int) trim($id)), $siteIds)));
            $cards[] = TripBoard::card($trip, $now, $operators);
        }

        // Colors identify the operator, same as the old calendars: the busiest
        // handful (among trips actually on the grid) get their own color,
        // everyone else shares "Other operators". An operator with nothing in
        // view this page load just never appears in the legend.
        $operatorCounts = [];
        $operatorNames = [];
        foreach ($cards as $c) {
            $oid = (int) $c['operatorId'];
            if (!$oid) {
                continue;
            }
            $operatorCounts[$oid] = ($operatorCounts[$oid] ?? 0) + 1;
            $operatorNames[$oid] = $c['operatorName'];
        }
        arsort($operatorCounts);
        $operatorColor = [];
        $legend = [];
        foreach (array_keys($operatorCounts) as $oid) {
            if (count($legend) >= count(self::OPERATOR_PALETTE)) {
                break;
            }
            $color = self::OPERATOR_PALETTE[count($legend)];
            $operatorColor[$oid] = $color;
            $legend[] = ['name' => $operatorNames[$oid], 'color' => $color];
        }
        $hasOtherOperators = count($operatorCounts) > count($legend);

        // Grid events, colored by operator. FullCalendar's own dayMaxEvents
        // folds a busy day into its native "+N more" popover, but the events
        // still have to reach the browser first - a Recreational month can
        // carry 2,500+ trips, and shipping all of them as inline JSON was a
        // ~900KB page (measured 2026-09-12). Capped per day at GRID_MAX_PER_DAY;
        // a day past that cap shows "+N more" for fewer than its true total,
        // but that day's own full board is one click away either way.
        $gridEvents = [];
        foreach (collect($cards)->groupBy('date') as $dayCards) {
            foreach ($dayCards->take(self::GRID_MAX_PER_DAY) as $card) {
                $card['color'] = $operatorColor[(int) $card['operatorId']] ?? self::OPERATOR_OTHER_COLOR;
                $gridEvents[] = $card;
            }
        }

        // Trip list: always the whole month, oldest first, paginated 20 at a
        // time regardless of which grid view (3 day/week/month) is showing.
        $monthCards = array_values(array_filter($cards, fn ($c) => $c['date'] >= $monthStart->toDateString() && $c['date'] <= $monthEnd->toDateString()));
        usort($monthCards, fn ($a, $b) => [$a['date'], $a['sortKey']] <=> [$b['date'], $b['sortKey']]);
        $show = max(self::PAGE_SIZE, (int) request()->query('show', self::PAGE_SIZE));
        $totalMonthTrips = count($monthCards);
        $shownCards = array_slice($monthCards, 0, $show);
        $remaining = max(0, $totalMonthTrips - count($shownCards));
        $byDate = collect($shownCards)->groupBy('date');

        $SEO = [
            'title' => $title . ' calendar, ' . $monthStart->format('F Y'),
            'desc' => 'A month calendar of ' . strtolower($title) . ' scuba diving trips in South Florida.',
            'canonical' => url($calendarUrl . '/' . $target->toDateString()),
            'robots' => 'noindex, follow',
        ];

        return view('pages.Calendar', [
            'title'        => $title,
            'pageTitle'    => self::HEADER[$type]['title'] ?? $title . ' Calendar',
            'headerIcon'   => self::HEADER[$type]['icon'] ?? null,
            'type'         => $type,
            'calendarUrl'  => $calendarUrl,
            'monthLabel'   => $monthStart->format('F Y'),
            'anchorDate'   => $target->toDateString(),
            'today'        => $today->toDateString(),
            'firstDayOfWeek' => (int) (auth()->user()->firstDayOfWeek ?? 0),
            'prevDisabled' => $target->toDateString() === $today->toDateString(),
            'byDate'       => $byDate,
            'events'       => $gridEvents,
            'totalMonthTrips' => $totalMonthTrips,
            'shownCount'   => count($shownCards),
            'remaining'    => $remaining,
            'moreUrl'      => $calendarUrl . '/' . $target->toDateString() . '?show=' . ($show + self::PAGE_SIZE),
            'legend'       => $legend,
            'hasOtherOperators' => $hasOtherOperators,
            'otherColor'   => self::OPERATOR_OTHER_COLOR,
            'about'        => self::ABOUT[$type] ?? null,
            'SEO'          => $SEO,
            'calendarFeedUrl' => route('CalendarT.feed', ['type' => $type]),
        ]);
    }

    public function show($tripType = null, $date = null)
    {
        $isTec = $tripType === 'tec';
        $type = $isTec ? 'tec' : 'rec';
        return $this->month(
            $type,
            $isTec ? 'Technical' : 'Recreational',
            $date,
            $this->typeFilter($type),
            'CalendarT/' . $type
        );
    }

    public function showShark($date = null)
    {
        return $this->month('shark', 'Shark', $date, $this->typeFilter('shark'), 'CalendarShark');
    }

    public function showLobster($date = null)
    {
        return $this->month('lobster', 'Lobster', $date, $this->typeFilter('lobster'), 'CalendarLobster');
    }

    public function showWreck($date = null)
    {
        return $this->month('wreck', 'Wreck', $date, $this->typeFilter('wreck'), 'CalendarWreck');
    }

    /**
     * The same per-type trip filter used both to render a calendar's own
     * grid/list (month(), above) and to build its public .ics feed (feed(),
     * below) - one source of truth so the feed can never drift from what
     * the page itself shows (Pablo, 2026-10-13: calendar subscribe feeds
     * for the 5 themed calendars).
     *
     * @return \Closure(Builder):void
     */
    private function typeFilter(string $type): \Closure
    {
        return match ($type) {
            'tec' => fn (Builder $q) => $q->where('tripType', 'like', '%Technical%'),
            'rec' => fn (Builder $q) => $q->where('tripType', 'like', '%Recreational%'),
            'shark' => fn (Builder $q) => $q->where('tags', 'like', '%SHA%'),
            'lobster' => fn (Builder $q) => $q->where('tags', 'like', '%LOB%'),
            'wreck' => function (Builder $q) {
                $q->where('siteIdStatus', 'confirmed')->whereExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('sites')
                        ->whereRaw('FIND_IN_SET(sites.id, trips.siteId)')
                        ->where('sites.type', 'wreck');
                });
            },
            default => throw new \InvalidArgumentException("Unknown calendar type: {$type}"),
        };
    }

    /**
     * Public iCalendar subscription feed for one themed calendar - every
     * upcoming trip of that type, same content for every visitor (unlike
     * EventController::feed()/GroupController::feed(), which are per-user/
     * per-group tokened feeds of saved trips). No token: the type itself
     * (validated against HEADER's known keys) is the only input, and the
     * route constrains it to the 5 real values besides.
     *
     * Capped to the next FEED_WINDOW_DAYS: an unbounded feed only grows,
     * and a calendar client re-polls this URL on its own schedule anyway,
     * so there is no reason to ever hand it years of trips at once.
     */
    private const FEED_WINDOW_DAYS = 120;

    public function feed($type)
    {
        if (!isset(self::HEADER[$type])) {
            abort(404);
        }

        $today = Carbon::today();
        // ->with('operator'): buildTripIcs() below reads $trip->operator for
        // every trip - without eager-loading it that's one query per trip,
        // which is fine for a single month (month()'s own usual scope) but
        // timed out at 30s for the wreck filter's wider FEED_WINDOW_DAYS
        // (1000+ trips, each a separate round trip) when first tested
        // locally (Pablo, 2026-10-13).
        $query = Trip::with('operator')
            ->whereDate('date', '>=', $today->toDateString())
            ->whereDate('date', '<=', $today->copy()->addDays(self::FEED_WINDOW_DAYS)->toDateString());
        ($this->typeFilter($type))($query);
        $trips = $query->get()->sortBy(['date', 'departureTime']);

        $ics = $this->buildTripIcs($type, $trips);

        return response($ics, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'inline; filename="divehub-' . $type . '-calendar.ics"',
        ]);
    }

    /**
     * Same RFC 5545 shape as EventController::buildIcs()/GroupController::
     * feed() - a self-contained VTIMEZONE so DST is handled by the
     * calendar client - built from Trip rows directly rather than a
     * user's saved Events or a group's dives, since this feed has no
     * owner to source "saved" trips from.
     */
    private function buildTripIcs(string $type, \Illuminate\Support\Collection $trips): string
    {
        $tz = 'America/New_York';
        $calName = (self::HEADER[$type]['title'] ?? ucfirst($type)) . ' - Divers Hub';

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//DiveHub//Calendar' . ucfirst($type) . '//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:' . $calName,
            "X-WR-TIMEZONE:{$tz}",
            'BEGIN:VTIMEZONE',
            'TZID:America/New_York',
            'BEGIN:DAYLIGHT',
            'TZOFFSETFROM:-0500',
            'TZOFFSETTO:-0400',
            'TZNAME:EDT',
            'DTSTART:19700308T020000',
            'RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=2SU',
            'END:DAYLIGHT',
            'BEGIN:STANDARD',
            'TZOFFSETFROM:-0400',
            'TZOFFSETTO:-0500',
            'TZNAME:EST',
            'DTSTART:19701101T020000',
            'RRULE:FREQ=YEARLY;BYMONTH=11;BYDAY=1SU',
            'END:STANDARD',
            'END:VTIMEZONE',
        ];

        $stamp = Carbon::now('UTC')->format('Ymd\THis\Z');

        foreach ($trips as $trip) {
            $operator = $trip->operator;
            $operatorName = $operator->operatorName ?? null;
            $cityAddress = $operator->cityAddress ?? null;

            $start = Carbon::parse(
                Carbon::parse($trip->date)->format('Y-m-d') . ' ' . ($trip->departureTime ?: '00:00'),
                $tz
            );
            $end = (clone $start)->addHours(3);

            $summary = trim($trip->tripName . ($operatorName ? ' — ' . $operatorName : ''));
            $location = $cityAddress ?: $operatorName;

            $descParts = [];
            if ($trip->linkToBook) {
                $descParts[] = 'Booking: ' . $trip->linkToBook;
            }
            $descParts[] = 'End time is an estimate (3h) — DiveHub trips have no set end time.';

            $lines[] = 'BEGIN:VEVENT';
            $lines[] = "UID:divehub-trip-{$trip->id}-{$type}@divers-hub.com";
            $lines[] = "DTSTAMP:{$stamp}";
            $lines[] = "DTSTART;TZID={$tz}:" . $start->format('Ymd\THis');
            $lines[] = "DTEND;TZID={$tz}:" . $end->format('Ymd\THis');
            $lines[] = 'SUMMARY:' . $this->icsEscape($summary);
            if ($location) {
                $lines[] = 'LOCATION:' . $this->icsEscape($location);
            }
            if ($trip->linkToBook) {
                $lines[] = 'URL:' . $this->icsEscape($trip->linkToBook);
            }
            $lines[] = 'DESCRIPTION:' . $this->icsEscape(implode("\n", $descParts));
            $lines[] = 'STATUS:CONFIRMED';
            $lines[] = 'END:VEVENT';
        }

        $lines[] = 'END:VCALENDAR';

        return implode("\r\n", $lines) . "\r\n";
    }

    /** Same escaping rules as EventController::icsEscape()/GroupController::icsEscape(). */
    private function icsEscape($text)
    {
        return str_replace(
            ['\\', ',', ';', "\n", "\r"],
            ['\\\\', '\\,', '\\;', '\\n', ''],
            $text ?? ''
        );
    }

    // showHydrotherapy moved to HydrotherapyCalendarController so this file can never
    // change the client's embedded calendar. Do not add it back here.
}

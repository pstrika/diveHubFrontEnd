<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Trip;
use App\Models\Operator;
use App\Models\User;
use App\Models\Site;
use App\Support\TripBoard;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class EventController extends Controller
{
    //
    public function addEventToCalendar($tripId) {
        $user = User::findorFail(auth()->user()->id);
        $trip = Trip::findorFail($tripId);

        $newEvent = Event::create([
            'userId' => $user->id,
            'operatorId' => $trip->operatorId,
            'date' => $trip->date,
            'time' => $trip->departureTime,
            'tripName' => $trip->tripName,
            'booked' => false,
        ]);

        return redirect()->back()->with('alreadyInCalendar', true);


    }


    /**
     * The personal "My Calendar" - same grid/anchor/pagination criteria as the
     * themed calendars (App\Http\Controllers\CalendarTController), just
     * scoped to this diver's own saved trips instead of a type filter, and
     * with no operator legend or month-list cap (a personal saved list is
     * small by nature, unlike a month of every Recreational trip).
     */
    public function show($date = null) {
        $user = User::findorFail(auth()->user()->id);
        // Make sure this user has a calendar-feed token so the subscribe URL
        // can be shown on the page (generated once, on first visit).
        // NOT for guests: the 'guest' middleware logs anonymous visitors in as
        // user 5, so gating here keeps them from seeing/minting user 5's token.
        $calendarFeedUrl = null;
        if ($user->isNotGuest()) {
            $user->ensureCalendarToken();
            $calendarFeedUrl = route('MyCalendar.feed', ['token' => $user->calendar_token]);
        }

        $today = Carbon::today();
        $target = $date ? Carbon::parse($date) : $today->copy();
        if ($target->lt($today)) {
            $target = $today->copy();
        }
        $monthStart = $target->copy()->startOfMonth();
        $monthEnd = $target->copy()->endOfMonth();
        $gridFrom = $monthStart->copy()->startOfWeek(Carbon::SUNDAY);
        $gridTo = $monthEnd->copy()->endOfWeek(Carbon::SUNDAY);

        // Show, then gate (proposal F-04): guests see the calendar page with an
        // empty state instead of a login wall. The shared guest user must never
        // list events, even if some end up on user 5 by accident.
        $events = $user->isNotGuest()
            ? Event::whereBetween('date', [$gridFrom->toDateString(), $gridTo->toDateString()])
                ->where('userId', $user->id)
                ->whereDate('date', '>=', $today->toDateString())
                ->get()->sortBy('date')
            : collect();

        $sites = Site::select('id', 'name', 'type', 'slug', 'maxDepth', 'level')->get()->keyBy('id');
        $operators = Operator::select('id', 'location', 'phone')->get()->keyBy('id')->all();
        $now = Carbon::now();

        $cards = [];
        foreach ($events as $event) {
            $trip = Trip::tripInEvent($event);
            if (!$trip) {
                continue; // re-scraped daily; an old saved trip can age out of today's data
            }
            $siteIds = $trip->siteId ? explode(',', $trip->siteId) : [];
            $trip->site = array_values(array_filter(array_map(fn ($id) => $sites->get((int) trim($id)), $siteIds)));
            $card = TripBoard::card($trip, $now, $operators);
            $card['booked'] = (bool) $event->booked;
            $card['eventId'] = $event->id;
            $card['waiverSigned'] = (bool) $event->waiver_signed;
            $card['waiver'] = $trip->operator->waiverLink ?? null;
            $cards[] = $card;
        }
        usort($cards, fn ($a, $b) => [$a['date'], $a['sortKey']] <=> [$b['date'], $b['sortKey']]);

        $monthCards = array_values(array_filter($cards, fn ($c) => $c['date'] >= $monthStart->toDateString() && $c['date'] <= $monthEnd->toDateString()));
        $byDate = collect($monthCards)->groupBy('date');

        return view('pages.MyCalendar', [
            'monthLabel'   => $monthStart->format('F Y'),
            'anchorDate'   => $target->toDateString(),
            'today'        => $today->toDateString(),
            'firstDayOfWeek' => (int) ($user->firstDayOfWeek ?? 0),
            'prevDisabled' => $target->toDateString() === $today->toDateString(),
            'byDate'       => $byDate,
            'events'       => $cards,
            'totalTrips'   => count($monthCards),
            'calendarFeedUrl' => $calendarFeedUrl,
        ]);
 
    }

/**
     * Public iCalendar subscription feed. Authenticated purely by the token in
     * the URL — no session/auth middleware — so any calendar app can poll it.
     */
    public function feed($token) {
        $user = User::where('calendar_token', $token)->first();
        if (!$user) {
            abort(404);
        }
 
        $events = Event::where('userId', $user->id)
            ->whereDate('date', '>=', Carbon::today())
            ->get()->sortBy('date');
 
        $ics = $this->buildIcs($events);
 
        return response($ics, 200, [
            'Content-Type'        => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'inline; filename="divehub-calendar.ics"',
        ]);
    }

    public function setEventBook($eventId) {
        $event = Event::findOrFail($eventId);
        $event->booked = true;

        $event->save();

        return redirect()->back();
    }

    public function setEventWaiverSigned($eventId) {
        $event = Event::findOrFail($eventId);
        $event->waiver_signed = true;

        $event->save();

        return redirect()->back();
    }

    public function removeFromCalendar($eventId) {
        $event = Event::findOrFail($eventId);
        $event->delete();

        return redirect()->back();
    }

    /**
     * Issue a fresh token, which immediately invalidates the old feed URL
     * (i.e. revokes anyone the user had shared the previous link with).
     */
    public function regenerateToken() {
        $user = User::findOrFail(auth()->user()->id);
        // Guests are user 5 via the 'guest' middleware remap — don't let them
        // rotate that shared account's token.
        if (!$user->isNotGuest()) {
            abort(403);
        }
        $user->calendar_token = bin2hex(random_bytes(24));
        $user->save();
 
        return redirect()->route('MyCalendar')->with('calendarTokenRegenerated', true);
    }
 
    /**
     * Build an RFC 5545 iCalendar document from a collection of Events.
     * Times are emitted in America/New_York (the app's user base) via a
     * self-contained VTIMEZONE, so DST is handled by the calendar client.
     */
    private function buildIcs($events) {
        $tz = 'America/New_York';
 
        $lines = [
            "BEGIN:VCALENDAR",
            "VERSION:2.0",
            "PRODID:-//DiveHub//MyCalendar//EN",
            "CALSCALE:GREGORIAN",
            "METHOD:PUBLISH",
            "X-WR-CALNAME:DiveHub - My Dives",
            "X-WR-TIMEZONE:{$tz}",
            // America/New_York DST rules (US, 2007+): DST 2nd Sun Mar, STD 1st Sun Nov.
            "BEGIN:VTIMEZONE",
            "TZID:America/New_York",
            "BEGIN:DAYLIGHT",
            "TZOFFSETFROM:-0500",
            "TZOFFSETTO:-0400",
            "TZNAME:EDT",
            "DTSTART:19700308T020000",
            "RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=2SU",
            "END:DAYLIGHT",
            "BEGIN:STANDARD",
            "TZOFFSETFROM:-0400",
            "TZOFFSETTO:-0500",
            "TZNAME:EST",
            "DTSTART:19701101T020000",
            "RRULE:FREQ=YEARLY;BYMONTH=11;BYDAY=1SU",
            "END:STANDARD",
            "END:VTIMEZONE",
        ];
 
        $stamp = Carbon::now('UTC')->format('Ymd\THis\Z');
 
        foreach ($events as $event) {
            $trip = Trip::tripInEvent($event);
 
            // Prefer the live Trip row for richer data; fall back to the event's
            // own snapshot fields if the trip no longer exists.
            $tripName     = $trip ? $trip->tripName : $event->tripName;
            $operator     = $trip ? $trip->operator : Operator::find($event->operatorId);
            $operatorName = $trip ? $trip->operatorName : optional($operator)->operatorName;
            $linkToBook   = $trip ? $trip->linkToBook : null;
            $cityAddress  = $trip ? optional($trip->operator)->cityAddress : null;
            $waiverLink   = $event->booked ? optional($operator)->waiverLink : null;
 
            // events.date is DATETIME ('Y-m-d 00:00:00') so normalize to the date part
            // before appending time, else Carbon throws 'Double time specification'.
            // time is 'H:i'. Trips carry no duration → assume 3h.
            $start = Carbon::parse(
                Carbon::parse($event->date)->format('Y-m-d') . ' ' . ($event->time ?: '00:00'),
                $tz
            );
            $end   = (clone $start)->addHours(3);
 
            $summary  = trim($tripName . ($operatorName ? ' — ' . $operatorName : ''));
            $location = $cityAddress ?: $operatorName;
 
            $descParts = [$event->booked ? 'Status: Booked' : 'Status: Not yet booked'];
            if ($linkToBook) {
                $descParts[] = 'Booking: ' . $linkToBook;
            }
            if ($waiverLink) {
                $descParts[] = 'Waiver (' . ($event->waiver_signed ? 'signed' : 'not yet signed') . '): ' . $waiverLink;
            }
            $descParts[] = 'End time is an estimate (3h) — DiveHub trips have no set end time.';
 
            $lines[] = "BEGIN:VEVENT";
            $lines[] = "UID:divehub-event-{$event->id}@divers-hub.com";
            $lines[] = "DTSTAMP:{$stamp}";
            $lines[] = "DTSTART;TZID={$tz}:" . $start->format('Ymd\THis');
            $lines[] = "DTEND;TZID={$tz}:" . $end->format('Ymd\THis');
            $lines[] = "SUMMARY:" . $this->icsEscape($summary);
            if ($location) {
                $lines[] = "LOCATION:" . $this->icsEscape($location);
            }
            if ($linkToBook) {
                $lines[] = "URL:" . $this->icsEscape($linkToBook);
            }
            $lines[] = "DESCRIPTION:" . $this->icsEscape(implode("\n", $descParts));
            $lines[] = "STATUS:" . ($event->booked ? "CONFIRMED" : "TENTATIVE");
            $lines[] = "END:VEVENT";
        }
 
        $lines[] = "END:VCALENDAR";
 
        // RFC 5545 requires CRLF line endings.
        return implode("\r\n", $lines) . "\r\n";
    }
 
    /**
     * Escape text for an iCalendar value: backslash, comma, semicolon and
     * newlines are special. (Escape backslash first so we don't double-escape.)
     */
    private function icsEscape($text) {
        return str_replace(
            ["\\", ",", ";", "\n", "\r"],
            ["\\\\", "\\,", "\\;", "\\n", ""],
            $text ?? ''
        );
    }
}

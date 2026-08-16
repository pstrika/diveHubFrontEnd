<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Trip;
use App\Models\Operator;
use App\Models\User;
use App\Models\Site;
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
        // if we didn't receive $date, we just put today's
        if (!$date) {
            $date = Carbon::today()->toDateString();
        }
 
        $month = date('m', strtotime($date)); // Extract the month from the 'date' variable
        $year = date('Y', strtotime($date)); // Extract the year from the 'date' variable
       
        $dateFrom = Carbon::parse($date)->format('Y-m-d');
        $dateTo = Carbon::parse($date)->addWeek(6)->format('Y-m-d');
        $events = Event::whereBetween('date', [$dateFrom, $dateTo])
            ->where('userId', auth()->user()->id)
            //->where('userId', '8')
            ->whereDate('date', '>=', Carbon::today())
            ->get()->sortBy("date");
 
        Log::debug("Got " . str(count($events)) . " event for user " . $user->name);
        $trips = [];
        foreach($events as $event) {
            $trip = Trip::tripInEvent($event);
            if($trip) {
                $trip->booked = $event->booked;
                $trip->eventId = $event->id;
                $trip->waiver = $trip->operator->waiverLink;
                //Log::debug("waiver: " . $trip->waiver);
                $trips[] = $trip;
            }
        }
 
        $sites = collect(Site::select('id', 'maxDepth', 'level')->get());
       
        Log::debug("size of sites: " . count($sites));
        Log::debug("Size of trips: " . count($trips));
        /* why did I put this codde in here???!?!?!?!?!?!?
        foreach($trips as $i => $trip) {
            Log::debug("content of siteId: " . $trip->siteId);
            if($trip->siteId != null) {
                $siteIds = explode(',', $trip->siteId);
                $relatedSites = $sites->whereIn('id', $siteIds)->all();
                Log::debug("relatedSites: " . count($relatedSites));
               
                //$j=0;
                foreach($relatedSites as $relatedSite) {
                    Log::debug("index i,j: " . $i . ", " . $j);
                    //$trips[$i]->site[$j]->id = $relatedSite->id;
                    //$trips[$i]->site[$j]->maxDepth = $relatedSite->maxDepth;
                    //$trips[$i]->site[$j]->level = $relatedSite->level;
                    //$j++;
 
                    $tempSite = [];
                    $tempSite['id'] = $relatedSite->id;
                    $tempSite['maxDepth'] = $relatedSite->maxDepth;
                    $tempSite['level'] = $relatedSite->level;
                   
                   
                }
           
            }
        }
            */
        $dateF = Carbon::parse($date);
       
        // Get the next month....
        $nextMonthS = $dateF->addMonth()->startOfMonth()->toDateString(); // Add a month
        $thisMonth = Carbon::today()->startOfMonth();
        $prevMonth = $dateF->sub(new \DateInterval('P2M'));
 
        $controlNav = "";
        if($prevMonth < $thisMonth) {
            $prevMonth = $thisMonth;
            $controlNav = "disabled";
        }
        $prevMonthS = $prevMonth->toDateString();
 
        $currentMonthS = Carbon::parse($date)->format('F');
        $year = Carbon::parse($date)->format('Y');
        $currentDate = Carbon::parse($date)->startOfMonth()->toDateString();
 
       
        return view('pages.MyCalendar', compact('trips', 'currentDate', 'currentMonthS', 'year', 'prevMonthS', 'nextMonthS', 'controlNav', 'calendarFeedUrl'));
 
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
            $operatorName = $trip ? $trip->operatorName : optional(Operator::find($event->operatorId))->operatorName;
            $linkToBook   = $trip ? $trip->linkToBook : null;
            $cityAddress  = $trip ? optional($trip->operator)->cityAddress : null;
 
            // date is 'Y-m-d', time is 'H:i'. Trips carry no duration → assume 3h.
            $start = Carbon::parse($event->date . ' ' . $event->time, $tz);
            $end   = (clone $start)->addHours(3);
 
            $summary  = trim($tripName . ($operatorName ? ' — ' . $operatorName : ''));
            $location = $cityAddress ?: $operatorName;
 
            $descParts = [$event->booked ? 'Status: Booked' : 'Status: Not yet booked'];
            if ($linkToBook) {
                $descParts[] = 'Booking: ' . $linkToBook;
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

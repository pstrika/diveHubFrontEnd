# Redirect map

Every public URL and what happens to it in the redesign. The default is
"unchanged". Anything else needs a reason here and a 301 in `routes/web.php`
before it merges. Check this list against `sitemap.xml` before the switchover.

Legend: **unchanged** keeps URL and content type. **redirect** returns a 301 to
the target. **noindex** means the page is not indexed today and stays that way.

## Content pages (indexed today)

| URL | Disposition | Notes |
|---|---|---|
| `/` | unchanged | Home, redesigned in chunk 2 |
| `/home` | unchanged | |
| `/Trips`, `/Trips/{date}` | unchanged | Trip board, redesigned in chunk 2 |
| `/DiveSites` | unchanged | Becomes the explorer in chunk 3 |
| `/WreckSites` | unchanged | wreckWiki, renders through the explorer with the wreck preset |
| `/SiteDetails/{slug}` | unchanged | Numeric ids already 301 to the slug |
| `/BeachDiving` | unchanged | |
| `/Operators` | unchanged | |
| `/OperatorDetails/{slug}` | unchanged | |
| `/Waivers` | unchanged | |
| `/CalendarHydrotherapy`, `/CalendarHydrotherapy/{date}` | unchanged | |
| `/Weather`, `/Weather/{location}` | unchanged | |
| `/gasplanning` | unchanged | |
| `/AboutUs` | unchanged | |
| `/PrivacyPolicy` | unchanged | |
| `/TermsOfUse` | unchanged | |
| `/sitemap.xml` | unchanged | |

## Themed calendars (stay live, render the trip board pre filtered)

| URL | Disposition |
|---|---|
| `/CalendarT`, `/CalendarT/{tripType}`, `/CalendarT/{tripType}/{date}` | unchanged |
| `/CalendarWreck`, `/CalendarWreck/{date}` | unchanged |
| `/CalendarShark`, `/CalendarShark/{date}` | unchanged |
| `/CalendarLobster`, `/CalendarLobster/{date}` | unchanged |

## Tools (noindex today)

| URL | Disposition | Notes |
|---|---|---|
| `/DiveSitesMap` | redirect to `/DiveSites?view=map` | Chunk 3. Map becomes a view of the explorer (W4). Not indexed, so no equity lost. |
| `/DiveSitesSearch` | redirect to `/DiveSites` | Chunk 3. Search becomes the explorer omnibox (W4). Not indexed. |
| `/DiveSitesAll` | unchanged | |
| `/DecoPlanner*` | unchanged | |
| `/WeatherAR*` | unchanged | |
| `/MyCalendar`, `/MyDashboard`, `/MyVisitedSites` | unchanged | Account pages |
| `/TripDetails/{tripId}` | unchanged | noindex, follow today |
| `/calendar/feed/{token}.ics` | unchanged | |
| `/Landing` | unchanged | |

## Not public

Admin and template routes (`/DiveSitesAdmin`, `/items`, `/roles`, `/category`,
`/tag`, `/overview`, template demo pages) are behind auth and out of scope.

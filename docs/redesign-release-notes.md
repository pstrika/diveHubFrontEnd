# Redesign release notes

One entry per deploy to the redesign slot, newest first. `docs/whats-new.md`
is the full narrative walkthrough of everything through the first beta
(10.0.0) with test checklists; this is the short log to keep going after it,
so a run of small deploys doesn't just disappear into git history.

Every entry below deployed to the same place: **https://divehub-redesign.azurewebsites.net**
(GitHub Actions, `.github/workflows/redesign_divehub.yml`, triggers on every
push to `redesign`). Production (`divers-hub.com`) only gets these changes
when `redesign` is merged to `main` - see `docs/deployment.md` for that step.

## 10.4.1 — 2026-09-14

Deployed: commit `39fc024`.

- iOS install: a real centered modal (steps: Share, Add to Home Screen,
  Add) instead of the small bottom bar it used to share with Android -
  that bar's two-line text hint read as "nothing happened" on a phone,
  since iOS has no install API to hang an actual button off of. Shown by
  the same two triggers as before (the automatic phone/tablet popup on
  iOS Safari, and the drawer's "Install as App" link).
- Two changes aimed at "the 10-page re-show rule isn't working": the
  service worker registration is now wrapped in a try/catch (a
  synchronous throw there - some locked-down in-app browsers refuse the
  API outright - used to silently abort the whole install feature before
  the bar or the drawer link ever got wired up), and a corrupted/
  non-numeric stored dismissal count is now treated as "never dismissed"
  instead of getting permanently stuck comparing against NaN.
- The Android/desktop bar now slides up from the bottom instead of just
  appearing.

## 10.4.0 — 2026-09-13

Deployed: commits `1bc52e3`, `eb780b7`, `103e931`, `b255846`, `aaca8ee`.

### Small fixes
- Contact email switched from info@ to support@divers-hub.com everywhere
  it's shown to a diver (Terms of Use, Privacy Policy, Data Deletion,
  About Us, the liveaboard inquiry cc).
- Footer's "Contact" link removed - it pointed at About Us anyway, not a
  real Contact page, and nothing else in the app links there.
- **Found the actual cause of "some pages start with more padding than
  others."** The page container's own padding-top is every page's whole
  top gap, but several first-on-the-page components (the Weather/Beach
  Diving verdict card, the Dive Sites/Operators explorer header, Trip
  Details' summary strip, Platform Health's summary card, each calendar's
  "about" panel, the welcome wizard) also carry their own margin-top,
  meant to space repeated instances of themselves apart from whatever
  came before - correct everywhere except when one of them is the very
  first thing on the page, where it just doubled the gap. Zeroed the
  redundant margin generically for whatever ends up first on a page,
  rather than patching each component by name.
- "Install as App" (the drawer link) did nothing on desktop Chrome
  whenever the browser hadn't yet handed the page a native install
  prompt to trigger directly - its fallback bar was being swallowed by
  the same "never on desktop" rule that (correctly, by design) keeps the
  *automatic* popup phone/tablet only. The manual drawer action now gets
  through as a small bottom-right toast on desktop instead.

### Deco Planner
- The input card gets a real header, titled "Inputs" (it never had
  one), collapsible by tapping it.
- Hitting "Calculate Decompression Profile" now collapses it once real
  results are on screen; changing any input re-opens it automatically
  instead of leaving a stale, collapsed form next to an invalidated
  result.
- Every slider on the page (26 of them - depth, times, gradient factors,
  ascent/descent rate, bottom gas and up to four deco gases' O2/He/switch
  depth) now has a plain number field next to it for divers who find
  dragging fiddly. It drives the slider through noUiSlider's own public
  API, the same calls the page's own scripts already use to chain
  sliders together, so every existing calculation and validation path
  fires exactly as if the slider itself had been dragged - no calculator
  markup, math or event wiring was touched. The same numeric fields also
  showed up on Best Gases for free, since the enhancement is generic to
  any slider on any page. A deeper visual redesign of Best Gases (it has
  several interleaved input/output calculators sharing one card, not the
  single input-card/result-card shape Deco Planner has) needs a live
  design pass rather than an overnight, unverified restructure of a
  safety-relevant tool - flagged for next time.

### Notifications
- Full redesign: a two-pane, Gmail-style list and reading pane on
  desktop (the reading pane takes over the screen on phones, with a Back
  button), each row showing the sender's avatar, subject, a snippet, and
  the date - not just a subject line.
- Checkboxes, "select all," and bulk Delete/Restore/Delete forever.
- A real Bin. The soft-delete flag the trash icon already set was going
  nowhere before - deleted just meant gone. It now lands in the Bin,
  where it can be restored or permanently removed (both genuinely new on
  the backend, not just new buttons over old behavior).

### WhatsApp
- SMS (Twilio) was already fully wired end to end from an earlier
  session - consent UI, consent timestamps, sending, the group dive
  reminder cron. WhatsApp had the same consent UI and database columns
  in place, but nothing that actually sent a message. Added
  `App\Services\WhatsAppService` (Meta Cloud API, same no-op-until-
  configured safety rules as the SMS service) and wired it into the
  group dive reminder job, gated on each member's opt-in and on a
  dive-reminder message template actually being configured - Meta
  requires a pre-approved template for any business-initiated message,
  and that approval can land after the account/API credentials do.
  Nothing sends yet on this branch; no credentials are configured. Once
  the Twilio and Meta values are added as Azure App Settings, both
  channels start working with no further code changes - the WhatsApp
  template's exact parameter order will need a one-line tweak to match
  whatever Meta actually approved.

## 10.3.0 — 2026-09-12

Deployed: commits `95a08dd`, `278138f`, `f0be2cd`, `cedc850`, `22451e1`.

### Calendars and Trips
- The five type calendars (recreational, technical, wreck, shark, lobster)
  had quietly become a redirect into the Trips finder earlier in the
  redesign - real month/week/3-day views are back, rebuilt against the old
  pre-redesign implementation as a baseline: today-anchored by default,
  prev/next paging that steps by the active view (3 days / week / month)
  and won't go before today, a trip list below that always covers the
  visible range (up to 20 dives, "Show more" to page further), a day's
  events collapsing into FullCalendar's native "+N more" past five, and
  responsive by breakpoint (3-day on phone, week on tablet, month on
  desktop) via one shared script (`divershub-calendar.js`) all five use.
  Each calendar's title now carries its themed icon, matching its sidebar
  entry ("Wreck Diving Calendar", etc.).
- My Calendar (the personal one) got the same today-anchored logic and
  trip-card rendering, fixing a long-standing bug where a dive's site data
  never made it onto its own card.
- Trips finder: region chips reordered (Fort Lauderdale, Miami, Florida
  Keys, Palm Beach, Treasure Coast), the seats chip is labelled
  "Availability:", and the finder now remembers a diver's last filter
  picks for the session instead of resetting on every visit.

### Beach Diving
- Full redesign, in the style of the Weather page: Fort Lauderdale and West
  Palm Beach verdict cards share the top row (today's sky conditions, next
  high tide countdown, three-state go/no-go - good, poor, or a yellow "not
  a great day" when AM and PM disagree), one combined 5-day dive/no-dive
  table for both locations, the old underwater webcam back in a
  collapsible panel, and each location's map/site list collapsing
  independently.
- Fixed a real discrepancy where the verdict headline and the days table
  could disagree on borderline days - both now read the same score
  threshold, and per standing instruction for this page, beach diving's
  go/no-go always comes from this page's own 5-day data, never from
  Weather's marine-forecast text for the location.

### Icons, avatars and page chrome
- New `<x-site-type-icon>` component put Pablo's new wreck/reef/other SVGs
  (themed to the site's color, not a fixed color baked into the file)
  everywhere the old flat icons were still showing - Site Details, Trip
  Details, Operator Details, Operators, Dive Sites admin, Group upcoming
  dives, My Wishlist.
- Sidebar: Wreck Diving gets its new icon; Marine Forecast switches to the
  same cloud used on the Weather page.
- Drawer header now shows a signed-in member's uploaded avatar (blue theme
  ring) next to their name, nothing if none is set, and an initials circle
  if the file is missing on disk - centralized in `App\Support\UserAvatar`,
  also now used for group member and dive-RSVP avatars (fixing a broken
  fallback to a nonexistent default-avatar image in one of them).
- Page-title icons added to Dive Operators, Marine Forecast, Online
  Waivers, Deco Planner, Best Gases, My Groups, My Calendar, My Dashboard
  and Beach Diving, each matching its sidebar entry.
- One consistent top padding under the page header everywhere - Weather
  had a leftover duplicate wrapper giving it extra space others didn't
  have.

### Retheme pass
- Messages (notifications): off DataTables onto a plain themed list; fixed
  a duplicate element-id bug that broke per-row read/delete actions.
- My Visited Sites: **fixed the kanban board not rendering at all.** A
  site-type icon's raw multi-line SVG was landing straight inside a JS
  single-quoted string for each card's title, so any wreck/reef card broke
  the string and silently killed the whole script before the board ever
  built. Retheme also included.
- Groups (My Groups, Show, Create, Facebook page picker, chat/message
  partials) onto the shared card and button system.
- Online Waivers: fixed a stale "Dive Operators" header left over from a
  copy-paste, tiles reskinned.
- Best Gases and Deco Planner: outer shell only - hero banner removed,
  wrapped in a themed card. Left the internal calculators (markup, JS,
  color-coded state classes) untouched on purpose: both are
  safety-relevant tools whose color logic is tightly interconnected, and a
  full retheme risked a silent calculation bug for a low-value visual
  change.
- About Us, Terms of Use, Privacy Policy and Data Deletion moved off the
  old guest-landing shell onto the standard signed-in-style shell; content
  unchanged.
- Platform Health: Deco Divers (a dead operator) and Argentina weather
  locations no longer show up or raise alarms - out of scope for this
  dashboard.

### PWA
- **Fixed the install prompt never coming back after a dismissal.**
  Re-showing it lived entirely inside the browser's `beforeinstallprompt`
  event handler, which doesn't reliably refire on every page load - so
  even once the 10-page re-eligibility rule correctly flipped back to
  true, nothing actually triggered the bar on Android. Showing the bar is
  now decoupled from that event and runs whenever the page-view count says
  it's eligible; iOS's separate instructions path was unaffected.
  Dismissing now re-shows after 10 more page views instead of never again,
  via a durable view counter, phone/tablet only.
- New "Install as App" entry at the very top of the drawer (above My
  Diving), hidden automatically once already running installed - which
  also permanently silences the auto-popup on that device.
- A cold launch of the installed app showed a blank black screen for a few
  seconds while the page loaded; a pure-CSS boot splash (app icon, small
  pulse) now covers that gap, invisible in a normal browser tab.

## 10.2.0 — 2026-09-11

Deployed: commit `d70d84f`, followed by a same-day, same-version fix in
`9caa391` (see note at the end of this entry).

### Weather
- Pablo's original Florida map is back, replacing the Mapbox pin map from
  earlier in the redesign - re-measured pin-by-pin against the actual current
  `Florida1.png` (the coordinates on file were calibrated against an older
  export of that image and had drifted off the printed city labels).
- Live current-reading widget restored for Miami Beach and Fort Lauderdale
  (the two locations with a NOAA current buoy) - shares a row with "Good
  diving today," with a new plain-SVG compass replacing the old raster
  bg/needle PNGs.
- Argentina fully removed from location queries, the "All locations" list,
  and the coast hint text (data untouched, just filtered out - see
  `App\Http\Controllers\WeatherController`).
- "Along the coast today" now groups a signed-in member's favourite
  locations to the top, each group independently sorted by real latitude;
  the N/S sort toggle still works within each group.
- Extended Forecast rebuilt as a real `<table>` with genuine AM/PM
  sub-columns (was two values crammed into one cell) - and a Chart.js sizing
  bug that had silently broken the waves chart is fixed.

### Dive Sites (the explorer / "Top Rated")
- **Major performance fix.** The explorer was running `SELECT *` against
  `sites`, a table with several large JSON/rich-text columns (`desc`,
  `history`, `wreckData`, `videos`, `pics`) the cards never touch - and for
  Top Rated specifically, that meant the *entire* ~380-row table, in full,
  on every single load, before ranking and slicing to 50. Narrowed to an
  explicit column list.
- Site-wide search restored - name, description, wreck data, and operator
  names, with a results page (`pages.DiveSitesSearch`) - replacing the
  name-only filter that had quietly replaced it earlier in the redesign. A
  search that can only mean one site skips the results page and goes there
  directly.
- Card thumbnails marked `fetchpriority="low"` so they don't compete with
  whatever the browser fetches next when a user clicks through.

### Site Details
- Best Gas, Site Description, Route + Typical Conditions (merged into one
  card), Wreck Details, Wreck History, Divers' Uploaded Pictures and Divers'
  Reviews all reskinned onto the shared `.dh-panel` card system (were still
  the old dark-gradient Material Dashboard cards).
- Wreck vessel type and site type (reef/wreck/other) icons switched to
  Pablo's new SVGs, recolored to the theme's blue via `App\Support\IconSvg`
  instead of a fixed color baked into the file.
- Photo gallery now opens the full-size image in a dismissable modal instead
  of a new browser tab - a real tab has no way back in the installed PWA.
- Fixed the certification gauge sitting with a large gap above it, and a
  card-alignment bug on reef/other sites (was inheriting a gutter meant only
  for the two-column wreck layout).

### Platform Health (admin)
- Rethemed onto the shared card/table system.
- Wired up the crawler team's new `operators._status` code contract (-2
  waiting through 3 error, see `App\Support\OperatorHealth`), the new
  `_runsRemaining` column for Fareharbor-paginated operators, and
  human-readable schedules parsed from each operator's 6-field `_cron`
  (Azure NCRONTAB, seconds first).

### Shared / shell
- One universal 1100px content cap (`body.dh-shell .main-content >
  .container-fluid`) so a page's cards and the bottom Me-tab row buttons
  stop growing at the same width everywhere, instead of each page needing
  its own opt-in class.
- Header pinned to the true left/right window edges at every size (was
  centering inside a 1280px column on wide screens).
- Messages moved from the Me-tab rows to a bell icon with an unread badge,
  next to the avatar in the top bar - where a notification center is
  expected. Mobile gets a search icon that opens the same search bar in a
  modal, since there's no room for it inline below 768px.

**Follow-up fix, same day, still under 10.2.0** (`9caa391`, not a separate
version bump): dropped "- drop in Florida" from the Top Rated title, and
found the deeper cause of "it takes forever to click through from Top
Rated" - even trivial queries were paying 100-700ms of round-trip latency
to the database regardless of row count, and the explorer ran five of them
on every load. Two barely ever change (level-filter counts, the 13-location
name lookup) and a third is stable for the plain unfiltered view (total site
count); cached all three. Cut the query count from 5 to 2 and roughly halved
load time in testing.

## 10.1.0 — 2026-09-11

Deployed: commit `9d359ec`.

- Bottom tab bar: Dives gets its own `scuba_diving` icon (was reusing
  Sites'), Sites gets `pin_drop`, Weather gets `cloud`.
- The Me-tab shortcut rows now render on every signed-in page, not just the
  dashboard - fixed the tab-bar clearance bug that briefly hid the last row
  behind the fixed bottom bar once they moved.
- Page titles restyled to match Dive Sites' large bold look everywhere, and
  a real duplicate-`<h1>` bug this exposed (Dive Sites and Operators were
  each rendering their own title a second time) was found and fixed before
  it shipped.
- One shared horizontal inset (`--dh-content-x`) so every page's cards, its
  title, and the Me-tab rows line up on the same edge - several pages had a
  stray `.row.mx-1` whose `!important` broke Bootstrap's usual gutter
  cancellation and threw the alignment off.
- Sites "Load more": the explorer used to fetch and render every matching
  site at once; now it queries only the visible page ($show rows), growing
  via a plain shareable `?show=` link. Popular sort and map view still fetch
  every candidate on purpose - SiteRank needs the full set to rank
  correctly, and the map needs every pin regardless of list length.
- Fixed a pre-existing bug in `divershub.js`: chip-row overflow arrows were
  only evaluated once, so resizing a browser window wider could leave stale
  arrows showing on a row that no longer overflowed.

## Earlier

Everything through the first beta walkthrough (10.0.0 and before - the trip
finder rebuild, site ranking, the welcome wizard, communication consent, and
the full test checklist) is in `docs/whats-new.md`.

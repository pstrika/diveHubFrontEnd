# Redesign release notes

One entry per deploy to the redesign slot, newest first. `docs/whats-new.md`
is the full narrative walkthrough of everything through the first beta
(10.0.0) with test checklists; this is the short log to keep going after it,
so a run of small deploys doesn't just disappear into git history.

Every entry below deployed to the same place: **https://divehub-redesign.azurewebsites.net**
(GitHub Actions, `.github/workflows/redesign_divehub.yml`, triggers on every
push to `redesign`). Production (`divers-hub.com`) only gets these changes
when `redesign` is merged to `main` - see `docs/deployment.md` for that step.

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

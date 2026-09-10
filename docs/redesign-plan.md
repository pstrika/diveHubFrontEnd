# Divers Hub redesign: working plan

This is the shared reference for the redesign work. It follows the UI/UX audit
and redesign proposal (findings F-01 to F-10, wireframes W1 to W5) with the
corrections below applied. When this document and the proposal disagree, this
document wins, because it was checked against the code.

## Ground rules

1. Nothing merges to `main` until the whole redesign is ready. Work lands on the
   `redesign` branch in reviewed pull requests, one per chunk below.
2. Public URLs do not change. Every route that exists today keeps working and
   keeps serving the same kind of content. The only exceptions are listed in
   `docs/seo/redirect-map.md` with a reason and a 301.
3. SEO plumbing on the dive site pages (slugs, canonicals, titles, schema.org
   markup, sitemap entries) is not touched. Presentation changes only.
4. No database schema changes. Everything here is views, controllers, and
   assets.
5. Comments in the code explain why, not just what. Blade comments use the
   `{{-- --}}` form so they never reach the browser.

## What the proposal got wrong or what has changed since August

- The site catalog is already indexable. Site detail pages have slugs, a 301
  from numeric ids to slugs, canonical tags, titles, and schema.org markup, and
  they are in the sitemap. F-08 is done. Only the Map and Search pages are
  noindex, which is correct for tools.
- Dive site level is a five value field (0 Open Water, 1 Advanced Open Water,
  2 Technical Air, 3 Technical Normoxic Trimix, 4 Technical Hypoxic Trimix),
  not three. `App\Support\DiveLevel` is the single definition and the inline
  legend uses the depth table already shown on the sites pages.
- The load time guest modal is gone. In its place: locked sidebar items call
  `showModalGuest()` on every page, but the modal markup only exists on the
  Beach Diving page, so the click does nothing elsewhere.
- "Create account" pointing at sign out is deliberate. Guests are logged in as
  user 5, so the guest has to be logged out before sign up will accept them.
  The fix is one route that does both steps, not an href change.
- The footer year is written by JavaScript, so the missing year in the proposal
  was a no script view. Moving it server side is trivial and included.
- Clean URLs such as `/sites/spiegel-grove` are dropped. Existing URLs stay.

## Chunks

Each chunk is one pull request into `redesign`.

### Chunk 1: Foundation

- Hygiene: create account route, version stamp moved to the footer and read
  from one config value, guest modal moved into the shared layout, real alt
  text on level icons, server side copyright year.
- Shared dive level helper: one class that maps level values to names, short
  codes, icons, and depth ranges. Reused by the sites lists, trip cards, and
  site cards.
- Level filter chips and a sort control on Top Rated and wreckWiki, legend
  inline. This is the live user request. Type chips arrive with the explorer
  in chunk 3, since Top Rated already splits by type and wreckWiki is wrecks only.
- Redirect map document.
- Design tokens stylesheet, added but not yet applied, so the visual direction
  can be reviewed before it lands everywhere.

### Chunk 2: Shell, trip board, home (W1, W2, W5)

Built mobile first: the phone layout is the base and desktop grows from it.
Also lays PWA groundwork at no cost to this work: web app manifest, theme
color and home screen icons in the page template; the trip board is built
from plain card arrays (`App\Support\TripBoard`) so a JSON feed for the
offline app is one controller method later; the shell is a single component
a service worker can cache as the app shell. Service worker, IndexedDB and
the offline queue belong to the PWA epic after the redesign.

- Top nav with Dive Today, Dive Sites, Operators, and a profile menu. Bottom
  tab bar on mobile. One layout component swap propagates to every page.
- Trip board as region grouped cards with conditions pills, filter chips, and
  one availability vocabulary (Seats open, Few seats, Full, Call to book).
  Themed calendar URLs stay live and render the board pre filtered.
- Homepage with conditions strip, photo hero, featured sites, and an in page
  account invitation.

### Chunk 3: Sites explorer and site detail (W3, W4)

- One explorer at the existing Dive Sites URL: search, list and map toggle,
  type and level chips, sort. wreckWiki keeps its own indexed page and renders
  through the same explorer with the wreck filter preset. Map and Search
  redirect into the explorer (see redirect map).
- Site detail re skin: gallery header, facts and live diveability, reviews,
  next boats to this site. Head metadata unchanged and diffed before and after.
- Image pass: web sized copies of the photos the new pages use, and the social
  preview image replaced. Fate of the originals folder is Pablo's call.

### Chunk 4: Show then gate, switchover (built 2026-09-06, awaiting merge)

- Show, then gate: Deco Planner and My Calendar render for guests (routes moved
  from `auth` to `guest`); the saving actions (add to calendar, book, remove,
  regenerate feed link) stay behind `auth`. Guests get an empty calendar with a
  create account prompt and never see the shared guest user's events. Weather
  was already open. Menu items unlocked accordingly.
- Dashboard "Recommended for this weekend" is ranked: favorites first, the top
  of the diver's level range first, better rated sites, boats with seats, then
  date and time. Departed trips drop off, and once this weekend has no boats
  left the card rolls to next weekend and says so in its title. This ordering
  was Zach's open question; done on the "take it home" go, revert is one block
  in `MyDashboardController`.
- Photos: `SitePhoto::makeCopies()` (PHP GD, no package) makes the web and
  thumbnail copies at upload time, best effort, and `php artisan
  photos:web-copies` backfills the photos uploaded before this release. This
  retires the Python tool for server use.
- Deploy workflow: one more post deploy step clears cached config and routes so
  `config/divehub.php` is always read. The trigger (push to `main`) is unchanged.
- Pre launch checks run locally against the production dump: every parameterless
  GET route as anonymous, guest, member and admin; every sitemap URL 200 with a
  self canonical; redirect targets 200; head tags and JSON-LD of indexed pages
  compared with production; PHP lint on every changed file; component, view and
  asset references checked for exact filename case (Linux is case sensitive).
- Merge `redesign` into `main` is Pablo's step (fast forward, no conflicts as of
  the last check). After deploy: run the photo backfill command once.

### Server tasks after the first deploy

1. Kudu console, `site/wwwroot`: `php artisan photos:web-copies` (a few minutes,
   safe to rerun). Needs PHP GD with WebP; the command says so if it is missing.
2. Confirm the beta or new host is in `APP_URL`, or `EnforceCanonicalHost` will
   redirect it to the live site.

## Scope added after the proposal (agreed with Zach, 2026-09-06)

- **Operators explorer and operator detail.** Not in the proposal. The Operators
  page was a table with client side location tabs, unsortable and unsearchable.
  It is now the same explorer pattern as Dive Sites (search box, coast chips
  with counts, feature chips that show only what is present, sort chips, list or
  map view, all in the query string) built on `App\Support\OperatorBoard`.
  Operator detail follows the site and trip page layout. URL, title, description,
  canonical and JSON-LD are unchanged.
- **Template palette remap.** Material Dashboard's "info" blue and "primary" pink
  are repainted with the Divers Hub palette on shell pages (one block at the end
  of `divershub.css`).
- **Operators map view still geocodes in the browser.** Operators have no stored
  coordinates. A lat/lon pair on the operators table (set once from the admin)
  would remove one Mapbox geocoding call per operator per map view.

- **Site ranking blend (2026-09-07, Zach: "build the ranking blend now").** Trip
  counts over twelve months blended with damped ratings (`App\Support\SiteRank`),
  default order on the explorer and the home picks, trip count shown on cards,
  home picks capped at a member's level range. No schema change.

- **Add to home screen (2026-09-07, Zach: "go").** Install only service worker
  (`public/sw.js`), install bar component shown on phones from the second visit:
  native prompt on Android via beforeinstallprompt, Share then Add to Home Screen
  hint on iOS Safari. Never on desktop, never when installed, dismissal remembered.

- **Trip finder with date ranges (2026-09-10, Zach: "go").** `/Trips` keeps its
  day board and gains range mode: preset chips (this weekend, next weekend, next
  7 and 30 days) and a custom from/to, capped at 31 days, all in the query string.
  Range results show a day strip with counts, then each day's board with three
  cards per coast and a link to the full day. The five themed calendars keep their
  URLs and render the finder with their type preset (`CalendarTController`
  delegates to `TripsController`; the ThemedCalendar view is gone).
- **Consistent gating (same day).** Tools and calendars are open to everyone
  (Deco planner, Best gases, the five calendars); only personal data needs an
  account (dashboard, groups, saving to the calendar). Menu locks removed.
- **Bottom tabs (same day).** Five: Dives, Sites, Operators, Groups, Me. "Today"
  was not read as the place to find a dive, and "Boats" read as trips. Groups
  opens the account prompt for guests.
- **Dashboard favorites calendar (same day).** Rendered nothing for any member
  without a first day of week preference (a stray comma killed the script) and
  filtered operators by class name substring; now guarded and matched on a data
  attribute. Same guard applied to the Hydrotherapy calendar and the group page.

## Open questions

1. Originals folder: delete, resize in place, or move out of the repo.
2. Are `site_ratings` and `site_comments` populated enough to sort by rating
   by default?
3. Beta hosting: deployment slot or separate Web App.

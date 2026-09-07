# Divers Hub roadmap after the redesign

Ideas agreed between Zach and Pablo on 2026-09-07, parked here so they do not
get lost. None of this is in the redesign release. Anything that needs a schema
change is marked, because the redesign ships with zero migrations and that rule
holds until it is merged.

The thread running through all of it: more content from divers, shown to the
divers who are deciding where to go next. Trips are the hook, dive sites are the
destination pages, groups are where the content already gets made.

## 1. Site ranking: popularity blended with ratings (shipped in the redesign branch, 2026-09-07)

Built as `App\Support\SiteRank`: home "Popular dive sites", the explorer's default
"Popular" sort with the trip count on every card, the dashboard weekend picks, and
a certification cap on the home picks for members with a level range. Trip counts
are cached for an hour. What follows is the original note.

**Problem.** "Top rated dive sites" is really "our tech divers' favorite
sites". Most people who rate today are technical divers, so the deep wrecks win
and the reefs most people actually dive never appear. Pablo's words: mix the
ratings with what is popular.

**Idea.** Rotten Tomatoes style, two signals shown and blended:

- **Popularity**: how many trips go to a site. The data already exists:
  `trips.siteId` (comma separated list) over a rolling window, say the last
  twelve months, counted per site. The operator detail page already does this
  per operator for "most visited sites"; this is the same count across all
  operators. Trips are rebuilt by the crawlers, so the count is computed from
  what is in the table at the time, never stored as a foreign key.
- **Rating**: the existing `siteratings`, damped for low vote counts the way the
  home page already does (`(rate * votes + 4.2 * 3) / (votes + 3)`), so one
  five star vote does not beat fifty trips.
- **Blend**: a single score for default ordering, with both numbers visible on
  the card ("dived 84 times this year", "4.6 from 12 divers") so nobody has to
  trust the formula.

**Cap by certification.** When we know the diver's level (`users.certLevel`,
`users.showLevel`), do not recommend sites above it. Guests see everything.

**Schema.** The first version needs **no migration**: counts come from
`trips`, ratings from `siteratings`, levels from `users`. If the count is slow at
scale, cache it (Laravel cache, like the sitemap) before adding a column.

**Where it shows.** Home "Top rated" (rename to something honest, "Popular dive
sites"), the explorer's default sort, the dashboard's weekend picks.

## 2. Notifications: reminders and the post dive ask

Pablo is building push notifications. Nothing here should duplicate that; email
and SMS are additional channels through the same events.

**Before the trip.** A diver with a trip on their calendar gets a reminder:
the day before, with departure time, marina, and the waiver link if they have
not marked it signed.

**After the trip.** The next day: "How was it?" with a one tap link into the
dive report form (section 3). If they say they have photos but not yet, offer
"remind me in a week" and follow up once.

**Channels.**

- Email first: it is already configured (invites and reminders exist for
  groups), cheap, and nobody opts out of the whole site over an email.
- SMS through Twilio for the trip reminder only. Zach's rule: do not use SMS for
  the feedback ask, because one annoyed opt out kills every future reminder.
- Push: Pablo's work; hook the same events when it lands.

**Schema.** Yes. At minimum a `notifications_sent` log (user, event, channel,
sent at) so nobody gets the same message twice, and per user channel
preferences (email, SMS, push, quiet hours). The `events` table already gives
us the trip on the calendar. Twilio adds a Composer package and env keys.

## 3. Dive reports: structured "how was it" instead of a free text review

Reviews today are a star and a paragraph. What the next diver wants to know is
current conditions: visibility, current, temperature at depth, whether the wreck
has collapsed further, what was seen (the goliath grouper in the tower, the
turtles). Prompt for those as fields, keep the free text as a note.

- Tied to a site and, when it came from the calendar, to the trip (date,
  operator) so the report carries a date and reads as "last dived on".
- Surfaces on the site page as a "Recent reports" strip above the reviews, newest
  first, and feeds the conditions pill ("divers reported 60 ft visibility on
  Saturday").
- Photos attached to a report land in the site gallery (section 4).

**Schema.** Yes. A `divereports` table (user, site, event or trip natural key,
date, visibility, current, temp, wildlife tags, structure notes, text) and a
`divereport_photos` table, or reuse `photos` with a source and a user column.

## 4. Groups as the content engine

Groups already exist (Pablo added them) and that is where photos get shared
today. Make the group the place to post media, and make tagging the site part of
posting, so the same photo shows up on the dive site page for someone planning
the same dive.

- Post a photo to a group dive: it is already tied to a date and an operator,
  so the site is usually known; confirm it with one tap.
- Tagged photos appear on the site page as "From divers" with the date and the
  group name, behind a light moderation step (site owner approves or hides).
- The resize on upload from the redesign (`SitePhoto::makeCopies`) already
  makes web and thumbnail copies, so user photos cost nothing extra to serve.

**Schema.** Yes. Group media needs its own table (group, dive, user, file,
site, approved) or the `photos` table needs user, source and approval columns.

## 5. Smaller items noted along the way

- **Operators map** geocodes each shop in the browser. A `lat` and `lon` on
  `operators` set once from the admin removes that. Schema: yes, two columns.
- **One search** across sites, operators and trips from the top bar box. No
  schema; needs ranking rules.
- **Recreational month calendar** renders every recreational trip in the month
  (about 2,600 cards). Paginate by week or point the menu at the trip board.
- **Originals folder**: once every photo has copies, decide whether the camera
  originals stay in the web root.
- **Offline app** (manifest, icons, an install only service worker and the add to home
  screen bar already ship): caching service worker, cached
  trip board and saved sites, queued actions. Its own epic; the rEvo companion
  (section 6) is the natural first page to make offline.

## 6. rEvo dive companion: a tool for the boat, offline first

Zach and Pablo both dive rEvo rebreathers, and Zach already uses a single file
web app for it on every dive: `docs/tools/revo-dive-companion.html` (72 KB, no
dependencies, no network). Tabs: Home (current dive, recent dives), Dive (plan
and gases, per dive checklists, finish dive), Scrubbers (unit wide status, log
time by hand, service, history), Gas (diluent, bailout MOD and END, scrubber
check, check a mix), Settings (units, gas planning defaults, scrubber limits,
save and restore as JSON). Everything lives in page memory, so it resets every
time the tab closes. That is the problem to solve.

**Why Divers Hub.** The app is the natural home for it: an account to persist
scrubber hours and dive history against, the trip and site already known when
you are on a boat from the board, and a user base of exactly the divers who
would use it. Scrubber management is the feature nobody else has and it is
specific to the rEvo (two canisters, hours per canister, swap order, temp stick
reading), so start with rEvo and generalize later if there is demand.

**Offline is not optional.** On the boat there is no signal. The tool must
open, work and save with no network, then sync when back on shore:

- Ship as a page inside Divers Hub under the shell, installable from the
  manifest we already ship, with a service worker that precaches the page and
  its assets (this is the first real use of the PWA groundwork).
- State in IndexedDB on the device: current dive, checklists in progress,
  scrubber hours, settings. Writes never wait on the network.
- A small sync layer: when online and signed in, push changes to the account
  and pull the latest; last write wins per record with a timestamp. A guest can
  use it fully on one device and pick up an account later without losing data.
- Pre dive checklist and post dive finish already exist in the prototype; tie
  "finish dive" to the trip on the calendar so the dive report (roadmap item 3)
  is prefilled with site, date and operator.

**Gas planning stays Pablo's.** Divers Hub already has a real deco planner
(`NDLController`, the `/DecoPlanner` pages with gradient factors, gas loading and
the metric and imperial views) and the gas planning page. The companion's Gas tab
is a convenience calculator by comparison and must not become a second planner.
Rule: the companion calls into the existing planner for anything that is a plan
(deco profile, gas needs, MOD and END for a mix), and keeps only what is rEvo
specific and boat side (scrubber check, checklists, timings). Either the planner
is embedded in the companion as the Gas tab, or the companion links out to it
with the dive's gases prefilled. Same numbers, one implementation, Pablo's.

**Schema.** Yes. Tables for rebreather units (user, model, serial, scrubber
canisters with hours and service dates), companion dives (user, unit, trip
natural key, plan, gases, checklist results, timings) and a per user settings
blob. All additive, nothing touches the existing tables.

**Steps.**

1. Port the prototype into a Blade page under the shell with its state moved to
   IndexedDB (no server yet), dropping its own gas math in favor of the existing
   planner. Already useful and already offline.
2. Service worker precache for that page only.
3. Sign in sync with the new tables.
4. Later: other rebreathers, sharing a unit's scrubber log between buddies.

## Suggested order

1. Ranking blend with certification cap (no migration, visible win, answers
   Pablo's point directly).
2. Dive reports (the content we want most, and the thing notifications ask for).
3. Notifications by email, then SMS reminders, aligned with Pablo's push work.
4. Group media to site pages.
5. rEvo dive companion, offline first (Zach and Pablo use it every dive).
6. Operator coordinates and one search whenever convenient.

# What changed on the redesign branch

Written 2026-09-10 for testing the beta and walking Pablo through it.

Everything below is live at https://divehub-redesign.azurewebsites.net.

## Where the branch stands

| | |
|---|---|
| Branch | `redesign`, head `4e1726f` |
| Version in the footer | 10.0.0 (09/10/26) |
| Commits on the branch | 55, of which 43 landed after the first beta walkthrough |
| Beta deploys | 21. Four failed early on package size and overlapping deploys, both fixed. The last ten are green |
| Main merged in | three times, latest at 9.24.3. Merging to main is a fast forward |
| Migrations from the redesign | one, four columns on `users` for communication consent. Run by hand after deploy, named with `--path` so nothing else can run |

## What you and Pablo already walked (the first four chunks)

Recap only, so the new list below has context.

1. **Foundation.** Version number read from one config value and shown in the
   footer, guest prompt rendered once from the layout, level filter chips and a
   sort on the site lists, the design tokens stylesheet, and the redirect map.
2. **Shell, trip board, home.** The Material sidebar replaced by a top bar plus
   a mobile tab bar on every page, the trip board rebuilt as region grouped
   cards with sea state fused into each region header, and a home page that
   leads with today's conditions and departures.
3. **Dive Sites explorer and site detail.** One explorer at the existing URL
   with search, chips, sort and a map view. Site pages rebuilt around the photos.
   Map and Search URLs fold into the explorer. Web sized copies of the whole
   photo library.
4. **Show then gate, switchover prep.** Deco Planner and My Calendar render for
   guests, weekend recommendations ranked, photo copies made in PHP with a
   backfill command, and the deployment runbook.

## New since that walkthrough

### 1. Dive Today became a trip finder

The single biggest change and the one to test first.

- `/Trips` keeps the day board and the stepper exactly as before. Same URLs.
- A **Dates** row adds Today, This weekend, Next weekend, Next 7 days, Next 30
  days, and **Pick dates** with two date inputs. Custom ranges are capped at 31
  days.
- A range search shows a **strip of day cells** with the matching trip count on
  each, then each day as its own board grouped by coast, three cards per coast
  with a link to that day's full board.
- An **Operators** row: All operators, **My favorites** pulled from the profile,
  and a picker listing every operator that has trips in the current selection
  with its count. Tick, Apply.
- Every filter lives in the URL, so any search is a link you can text to a buddy
  and it works with JavaScript off. Example:
  `/Trips?range=nextweekend&type=tec&region=palm&op=1,7`
- The **five themed calendars keep their URLs** and now render the finder with
  their type preset and the next 30 days. `/CalendarShark`, `/CalendarLobster`,
  `/CalendarWreck`, `/CalendarT`, `/CalendarT/tec`. One page instead of five, one
  card, one set of filters. They were behind login and never indexed, so they are
  open to guests now like the board itself.
- Hydrotherapy keeps its own page because it is indexed.

### 2. Personalisation

- **Site ranking.** "Top rated" was really "our tech divers' favourites",
  because tech divers are the ones who rate. Sites are now ordered by how often
  boats actually go there blended with the ratings, damped so one five star vote
  does not beat fifty boats. Both numbers show on the card. Counts come from the
  trips table over the last twelve months and are cached for an hour. This drives
  the home picks, the explorer's default sort and the dashboard weekend list.
  The home page heading changed from "Top rated dive sites" to "Popular dive
  sites". No schema change.
- **Certification cap.** Members with a level range in their profile do not get
  recommended sites above it. Guests see everything.
- **Weekend recommendations ranked.** Was date and time order. Now favourites
  first, then the top of the diver's level range, then site rating, then boats
  with seats, then time. Departed trips drop off, and once this weekend has no
  boats left the card rolls to next weekend and says so in its title.
- **Welcome wizard at `/welcome`.** A member whose profile has no certification
  level and no favourite places or boats gets walked through four short steps the
  first time they hit the dashboard: name and photo, level, places grouped by
  coast, boats with logos plus one question about whether weekend picks follow
  places or boats. A done page links to the dashboard, this weekend's boats
  already filtered to their favourites, and their groups. Every step is skippable,
  "Skip for now" goes quiet for two weeks, and the drawer keeps a "Finish setting
  up your profile" link until it is complete. It writes the same user columns the
  profile page writes, so no migration.

### 3. Navigation and consistency

- **Five bottom tabs, settled**: Dives, Sites, Weather, Groups, Me, and the bar
  is identical whether you are signed in or not. Was Today, Sites, Boats, Me for
  a while, then Dives, Sites, Operators, Groups, Me. "Today" was not read as the
  place to find a dive and "Boats" read as trips. Operators gave its slot to
  Weather on 2026-09-10: in South Florida the go or no go call is sea state, so
  the forecast is a nightly visit, while the operator list is a directory people
  read once and every real path to a shop is a trip card, a site page or the
  finder's operator filter. Operators stays one tap away in the drawer and as a
  row on the dashboard. Groups opens the account prompt for guests and carries
  the unread count for members; almost every notification comes from a group.
- **Me is a page, not a pop out.** The Me tab opens the dashboard, with the
  diver's own avatar as the icon. The drawer still exists for everything else,
  behind the avatar in the top bar, and it is what a guest's Me tab opens since
  a guest has no dashboard yet. The rows a member used to reach from the pop out
  (calendar, visited sites, messages, operators, tools, profile, settings, log
  out) sit at the foot of the dashboard.
- **Weather opens on your water.** With no location in the URL a member lands on
  the first of their favourite places (the same list the dashboard and the trip
  finder use), everyone else on Fort Lauderdale. An unknown location in the URL,
  a typo or an old link, used to be a 500 on production too; it now falls back
  to the default.
- **Every tab tap is a GA event** (`dh_tab_tap`, with the tab name), so the
  Weather over Operators call can be checked against real use in a month
  rather than argued.
- **One gating rule.** Tools and calendars are open to everyone: Deco Planner,
  Best Gases, and all five calendars. Only personal data needs an account:
  dashboard, groups, saving a trip, rating. Before this, Deco Planner was open
  but Best Gases was locked, which made no sense.
- **Filter chip rows** scroll sideways on phones with a round arrow on each edge
  that appears only when there is more in that direction. They used to wrap into
  four lines or run off the screen.
- **Operators explorer.** The old table with client side tabs became the same
  explorer pattern as Dive Sites: search across name, city, area, marina and boat
  names, coast chips with counts, feature chips for Technical, Nitrox, Trimix and
  private charters, sort by name, price, rating or tech first, list or map view.
  Operator detail rebuilt to match the site and trip pages.
- **Palette.** The Material template's blue and pink still showed on about forty
  pages we did not redesign. Those classes are repainted with our palette on any
  page using the new shell, so old and new sections match.

### 4. Install and notifications

- **Add to home screen.** On Android an install bar appears from the second visit
  with an Add button that opens the native one tap install sheet. On iPhone it
  shows the Share then Add to Home Screen hint, because Apple gives no install
  API. Never on desktop, never when already installed, and a dismissal is
  remembered on the device.
- Pablo's service worker and manifest are the ones that ship. Our placeholders
  were dropped in favour of his so push notifications keep working, and his
  notification toggle was carried into the Me drawer.

### 5. Pablo's work, merged in three times

His branch kept moving, so main was merged into the redesign at three points and
nothing of his was overwritten. What came across:

- 9.15.2 and 9.16.0: privacy policy, terms of use, the Data Deletion page.
- 9.17.0 to 9.22.0: web push notifications with the notification centre, the PWA
  manifest and icons, "Remember me" wired up with a longer session, the landing
  page redirect for signed in users, group avatars, the mobile group layout, and
  his own mobile bottom nav.
- 9.22.1 to 9.24.3: SMS trip reminders through Twilio with the A2P opt in
  language, the reminder email template fix, the desktop sidebar overlap fix, and
  the removal of the old generated sitemap files.

Two of his migrations ride along, both already his: the push subscriptions table
and a from user column on messages.

### 6. Bugs we found and fixed

Several of these were broken on the live site too, not just on the branch.

- **Profile picture upload.** Broken in production. The page loaded Cropper.js
  from a CDN with no version, the CDN moved to version 2, and the Confirm button
  called a method that no longer exists. The library is now vendored at 1.6.2.
- **Favourites dive calendar on the dashboard was blank** for any member without
  a first day of week preference. A null value written straight into JavaScript
  became a syntax error that killed the whole script block, calendar included,
  with nothing in the logs. The same line was on the Hydrotherapy calendar and
  the group page. All three fixed. The operator filter also matched on a class
  name substring and now matches an exact data attribute.
- **`/Landing` returned a 500** on the branch.
- **`/CalendarT` took 24 seconds** and shipped 3.5 MB, about 2,600 cards. It fetched
  six weeks to show one month and scanned all 380 sites for every trip. Fixed,
  then replaced by the finder.
- **Site cards had a stray box** around the level, depth and rating, because the
  card shared a class name with the site detail page header and inherited its
  border and padding. The home page also had its own copy of the card markup that
  loaded full size camera photos.
- **151 photos referenced in the database were missing** from the repo because
  they were uploaded through the admin on the server. Web copies for all of them
  were generated from production, so nothing is missing at launch.
- **Coast tiles on the home page** hung left with an orphan fifth tile. Centred.
- **Boat photos on trip details** pointed at the wrong folder.
- **The push toggle in the drawer** printed its JavaScript as text, because the
  block lost its opening script tag when it was moved out of the sidebar.
- **`/DiveSitesAll` rendered a deleted view.** It now redirects into the explorer
  sorted A to Z.
- **Site pages 500'd** when a review's author had been deleted.
- **The explorer loaded all 379 thumbnails at once** because the card painted the
  photo as a CSS background, which cannot be lazy loaded.

### 7. Deploy and infrastructure

- **Package size.** Azure rejects a deploy package of 1 GB or more with a 400,
  which is what killed two runs. The zip step now leaves out about 350 MB the app
  never serves: stray chunk upload files at the public root, the illustration
  source originals, and the screenshots folder. The package is about 650 MB.
- **Overlapping deploys.** Azure also answers 400 when a deploy starts while
  another is running on the same slot, which killed two more runs when pushes
  landed minutes apart. Both workflows now queue.
- **Cached config.** Both workflows delete the cached config and route files
  after deploying, so the release version is always read.
- The production workflow trigger is unchanged. It still only fires on a push to
  main.

### 8. Documents in the repo

- `docs/deployment.md` is the runbook for Pablo: what changes in production in
  one screen, the merge steps, what to do right after deploying, what to watch
  for two weeks, the full URL change table, and how to roll back.
- `docs/redesign-plan.md` is the running record of every chunk and scope add.
- `docs/seo/redirect-map.md` is every public URL and its disposition.
- `docs/roadmap.md` is what comes after: notifications, dive reports, groups as
  the content engine, the rEvo companion, operator coordinates.
- `docs/ux-review.md` is the pass over the whole app with the dashboard rebuild
  as the first item.
- `docs/tools/revo-dive-companion.html` is the working prototype of the rEvo tool.

### 9. From the 2026-09-10 call with Pablo

- **Communication preferences.** Email, SMS and WhatsApp each get their own
  checkbox with the consent wording beside it, under a "Communication
  preferences" heading on the profile page and as a step in the welcome wizard.
  One shared component, so the wording is the same in both places and the same
  wording we would produce if asked. Opting in stamps the date on the user,
  opting out clears it, and the profile page shows "Agreed 10 Sep 2026" beside
  each channel. If Twilio ever audits the A2P 10DLC registration, the answer is
  that screen plus the timestamp, and git history gives the exact wording as of
  that date.
- **It all lives on the users table**, four columns, no new table: Pablo's call,
  because every other preference a diver has is already there. My first pass used
  a separate audit table; that was over built for three switches.
- **Add to home screen shows more than once.** It was a one time bar: dismissing
  it hid it forever on that device. Now the decision is made on every page load
  from whether the app is actually installed, so it keeps offering until they
  install it and it comes back if they uninstall. Dismissing quiets it for the
  rest of that browser session only. It still never shows on desktop, and it now
  never shows inside an iframe.
- **The Hydrotherapy calendar is frozen.** It renders in an iframe on the
  client's own website, so it has to look exactly as it did before the redesign.
  It had picked up three changes during the redesign: the shared controller was
  tuned for speed and that changed its date range from six weeks to one month,
  and the shared footer had gained the release version badge. All reverted. It
  now has its own controller, and the shared layout gives it none of the redesign
  chrome. Verified line by line against production: identical apart from live
  trip data.
- **Dashboard reordered.** The banner photo and the "My Dashboard" title card
  are gone; on a phone they filled the whole first screen and said nothing. Order
  is now my upcoming dives, recommended for the weekend, my wishlist, and the
  month grid of favourite operator calendars last and collapsed behind one line.
  That calendar stays because some divers use nothing else. The wishlist gained
  two ways in, since it only earns its place if people fill it.
- **Upcoming dives read as dives** (Zach, second pass). Each row is the dive:
  title, date and operator all open the trip page, and the operator name is
  plain text, because tapping it used to land on the shop when you wanted the
  boat. Booked or not is now said in words on a pill, green "Booked" or red "Not
  booked yet", with "Options" beside it, instead of a red or green tank icon that
  nobody knew was tappable. The tank icon only says the trip type now.
- **The options sheet has words.** Go to the dive, Book with the operator, I am
  booked, Sign the waiver, Remove from my calendar. Before it was five icon
  buttons with hover tooltips, and phones do not hover.
- **My calendar is on the dashboard.** The month grid at the foot of the page is
  the diver's own calendar first, booked in green and not booked in red, with
  "Open full calendar" in the header; subscribing lives on the full calendar
  page, one tap away, rather than a second copy of the link on the dashboard.
  The favourite operators' calendar is one switch away on the same card for the
  divers who plan from it, rather than sitting there pretending to be your
  calendar.
- **My groups card**, small on purpose, and it sits right under upcoming dives
  (Zach: above the weekend picks). Each group with its member count and its
  next planned dive, pending invites called out at the top, and "Create or join
  a group" when there are none. This is the slot the group feed grows into once
  group chat has read tracking; today it answers "what is my group doing next".
- **Recommended and wishlist are rows, not tables.** Both were seven and nine
  column tables that scrolled sideways and looked nothing like the rest of the
  page. Recommended is now a date block, the trip as the link, the operator as
  plain text, level and depth as small facts, and the seats as the one action,
  capped at six with "See all" into the finder's weekend preset. The wishlist is
  one row per saved site with its next boat on the second line, or "No boat
  scheduled yet" when nothing is confirmed.
- **Save and dived from the site cards.** Every card in the explorer has a heart
  and a check over the photo: heart saves the site to your wishlist, check marks
  it as dived. Both toggle in place over fetch, no page reload on a 379 card
  page. Guests see the buttons and get the account prompt. The site page keeps
  its own buttons, unchanged. This is the fastest way to fill a wishlist or to
  mark the sites you have dived; Visited sites keeps the bulk tick list.
- **"N boats this month" on the site cards.** A chip on any site with confirmed
  boat trips in the next 30 days, so a diver browsing can see where boats are
  actually going. One cached pass over the trips an hour for everybody, no per
  card cost: the explorer measured 1.9 to 2.4 seconds before and 1.9 to 2.2
  after on the local box. The shared guest user can no longer write to the
  wishlist or visited list even if a request reaches those routes.
- **The guest prompt offers sign in as well as sign up.** Tapping Groups as a
  guest used to offer only "Create an account", which is no use to somebody who
  already has one. It now offers both, and the sign in route drops the shared
  guest session first, otherwise sign in bounces because the guest is technically
  logged in.
- **Version is 10.0.0.**
- Operator coordinates were reviewed and deliberately left for later, because
  the pins land in the right place today.

## Test list

Sign out first, then work through as a guest, then sign in.

### As a guest, on a phone

| Do this | Expect |
|---|---|
| Open the site on a phone | The add to home screen bar on the first visit. Android shows Add, iPhone shows the Share hint |
| Dismiss it, then come back later | It is back. It only stays hidden for that browser session, and it stops entirely once installed |
| `/CalendarHydrotherapy` | Renders exactly as production: no install bar, no version badge, nothing from the redesign |
| Dives tab | Today's board, region groups, sea state pills |
| Dates row: This weekend | Two day cells with counts, then Saturday and Sunday boards |
| Dates row: Pick dates, choose a week | The day strip fills, each day shows three cards per coast |
| Operators row: Choose operators, tick two, Apply | The count drops, the chip says 2 selected |
| Swipe the Region and Level chip rows | They scroll sideways, arrows appear on the edges |
| Sites tab, then a site | Popular order, trip counts on the cards, photo led detail page |
| Weather tab | The Fort Lauderdale forecast, the Weather tab lit |
| `/Weather/nowhere` | The Fort Lauderdale forecast, not an error |
| Menu (top right), Dive operators | Cards with logos, coast and feature chips, map view |
| Me tab | The menu slides in with Create a free account and Sign in |
| Me drawer, Deco planner and Best gases | Both open, no lock |
| Me drawer, My Groups | The account prompt, not a login page |
| `/CalendarShark` | The finder with Shark selected and the next 30 days |

### Signed in

| Do this | Expect |
|---|---|
| First sign in with an empty profile | The welcome wizard. Walk it and check the values land under My Profile |
| Skip for now | Back to the dashboard, and no wizard for two weeks |
| Dashboard, favourites calendar | Events for the selected operator, and switching operators changes them |
| Dashboard, recommended card | Ranked, favourites first, title names the weekend |
| Trip finder, My favorites chip | Only your operators, count matches |
| Profile, upload a photo | Crop and confirm work, the photo shows in the header |
| Profile, Communication preferences | Three checkboxes with consent wording, and a note if you have no phone number |
| Tick WhatsApp, Save, then untick and Save | Both decisions are recorded; ask me and I can show you the audit rows |
| Welcome wizard, last step before done | "How should we reach you?" with the same three checkboxes |
| Dashboard | Upcoming dives, my groups, weekend picks, wishlist, my calendar, menu rows; nothing scrolls sideways |
| Dashboard, an upcoming dive | Tap the title: the trip page. Tap the pill: the options sheet with five labelled rows |
| Dashboard, "I am booked" in the sheet | The pill turns green, the dive on the month grid turns green |
| Dashboard, Favorite operators switch | The operators' month grid, same as before, full width |
| Dashboard, Recommended | Rows with a date block, trip title, operator, seats pill; "See all" opens the weekend finder |
| Dashboard, My wishlist | One row per site; "Next boat" with a date and seats, or "No boat scheduled yet" |
| Sites tab, heart on a card | Fills red without a reload; the site appears on the dashboard wishlist |
| Sites tab, check on a card | Turns green; the site appears under Visited sites |
| Sites tab, signed out, heart | The account prompt, nothing saved |
| Sites tab | "N boats this month" chip on sites with confirmed trips in the next 30 days |
| Me tab, signed in | Your avatar as the icon; it opens the dashboard, no pop out |
| Weather tab, signed in | Opens on the first of your favourite places |
| Groups tab with an unread message | A red count on the icon |
| Groups tab while signed out | Both "Create an account" and "I already have one, sign in" |
| Me drawer, Enable Notifications | Pablo's toggle, unchanged |
| Save a site, add a trip to your calendar | Both work, My Calendar shows the trip |

### Desktop spot checks

Home, the trip finder in both modes, a site page, an operator page, a trip page,
My Groups, and the footer version reading 10.0.0.

## What did not change

Worth saying to Pablo explicitly, because it is the part that protects his work.

- **One schema change from the redesign**, the approved one: four columns on
  `users` for communication consent (section 9). No new tables, nothing renamed.
  The other two migrations on the branch are his own.
- **No new dependencies from the redesign.** The one package added is his Twilio
  and web push work.
- **Public URLs are unchanged**, except three tool pages that were never indexed
  and now redirect into the explorer, and the themed calendars which keep their
  URLs and change what they render.
- **Titles, descriptions, canonicals, robots and JSON-LD** were compared page by
  page against production. The only difference is a stray duplicate title tag
  inside the old navbar, which is gone.
- The sitemap is the same 406 URLs.

## Still open

1. Latitude and longitude on operators, so the operators map stops geocoding in
   the browser. Two columns, so it waits for the next release (Pablo, 2026-09-10).
2. Removing the camera originals from the repo, agreed and held until after the
   release. The reasons and the checks are in `docs/roadmap.md`.
3. Whether the redesign slot and production share the users database. If they
   do, running the consent migration for beta testing is the production schema
   change; the columns are additive with defaults, so main keeps working either
   way, but it should be a decision rather than a side effect.

Settled since the first draft of this list: the version is 10.0.0; the dashboard
is reordered; the tab bar is Dives, Sites, Weather, Groups, Me.

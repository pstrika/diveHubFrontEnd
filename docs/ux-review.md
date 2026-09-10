# UX pass, 2026-09-10

A step back over the whole app after the redesign chunks, with Zach's ask in
mind: more intuitive, better looking, and the dashboard as the place everyone
lands. Ordered by how much it would change what a diver feels, not by effort.
Nothing here needs a migration unless marked.

## Fixed in this pass

- Dashboard favorites calendar was blank for anyone without a first day of
  week preference (a stray comma killed the whole script). Also on the
  Hydrotherapy calendar (guests) and the group page.
- Coast tiles on the home page are centred; an odd fifth tile no longer hangs left.
- Bottom tabs: Dives, Sites, Weather, Groups, Me, the same bar signed in or out
  (settled 2026-09-10). "Today" was not read as "find a dive", "Boats" read as
  trips, and Operators gave its slot to Weather: the go or no go call in South
  Florida is sea state, so the forecast is a nightly visit, while the operator
  list is read once. Me is the dashboard, not a pop out, with the diver's avatar
  as the icon; Groups carries the unread count.
- Tools open to everyone: Deco planner, Best gases, the five calendars.
  Account only for personal data. Consistent rule, consistent drawer.
- Trip finder with date ranges; themed calendars became presets of it.

## 1. The dashboard should be the diver's morning, not a set of tables

*Update, 2026-09-10: the banner is gone, the order is upcoming dives, weekend
picks, wishlist, my groups, my calendar. Items 3 and 5 below are partly done
(a groups card without the feed; the operators' calendar kept behind a switch on
the my calendar card). Items 1, 2 and 4 are still the plan; see the roadmap.*

Today it is a banner photo, a title card, then three template tables. On a phone
the first screen is the photo and the words "My Dashboard". Nothing a diver
needs is above the fold. Pablo is right that it could be better, and it is the
one page every member lands on now that `/` redirects there.

**Proposal, all from data we already have:**

1. **Next dive** as the top card: the soonest event on the calendar with a
   countdown, departure time, marina, the boat, the sea state for that location
   that day, waiver status, and the group if it came from one. One tap to the
   trip. If there is none: "Nothing booked. This weekend looks like…" with the
   ranked weekend picks.
2. **Conditions where you dive**: the coast tiles from the home page filtered
   to the member's favourite locations, with the boat count and next departure.
3. **Your groups**: unread messages, the next group dive with who is going, one
   tap to RSVP. Groups are where the community lives; the dashboard should
   surface them, not hide them behind a tab.
4. **Recommended for the weekend**: the ranked list we built, as trip cards,
   not a table, capped at six with "see all" into the finder preset.
5. **Favourite operators**: the operator picker and its calendar become a row
   of operator chips that open the finder filtered to that operator (needs the
   operator filter on the finder, small, no schema).
6. **Wishlist**: site cards with the next boat going there.

Drop the banner photo and the title card. Every section is a link into a full
page, so the dashboard is a launch pad, not a destination.

## 2. Account pages still wear the old template

Sign in, register, forgot password, profile ("My Profile"), and the messages
page are the template's look: pink and blue gradients, full page background
photo, centred card. A new diver goes from the redesigned home to a page that
looks like a different product exactly when we ask them to trust us with an
email. Re-skin under the shell: same forms, same controllers, our palette,
Google button prominent, the "Remember me" switch on by default now that it
works. Half a day, no risk.

## 3. Groups is a whole product; treat it like one

Pablo is actively building here (Facebook connect, push, WhatsApp next), so
the rule is coordinate, never overwrite. What a redesign pass would change, in
order, once his current work lands:

- **My Groups** as cards: banner, name, member avatars, next dive date, unread
  count. A "Create a group" card at the end instead of a button in a banner.
- **Group page** top: the next dive with RSVP, then chat. Members, settings and
  Facebook feed move into a "More" row or the right column on desktop. Chat
  should be the full height of the phone screen with the composer pinned to the
  bottom; today it is a 400 pixel box mid page.
- Same photo pipeline as the site gallery (already shared), plus the roadmap
  item: tag the site when posting a photo so it reaches the site page (schema).

## 4. Smaller things that add up

- **Departed trips on today's board.** At 2 pm the morning boats are still the
  first thing on the page. Collapse departed trips into one line ("14 boats
  already left today, show them") so the first card is the next departure.
- **Template page banners** on the remaining pages (Weather, Waivers, Beach
  diving, About, Messages): a 200 to 300 pixel stock photo with a title card
  over it. On phones that is the whole first screen. Replace with the shell
  header title or a thin strip.
- **Weather page** is the last big public page on the old template. Re-skin
  around the coast tiles and the pill vocabulary already used everywhere else.
- **Two search boxes.** The top bar says "Search sites", the operators page has
  its own. One box that searches sites, operators and trips is on the roadmap;
  until then rename the top bar to "Search dive sites" so it is honest.
- **Header label plus H1.** The small uppercase page label ("DIVE TODAY") sits
  right above the H1 on most pages. On phones drop the label when the H1 says
  the same thing.
- **Guest prompt timing.** The account modal appears when a guest first taps a
  locked action. Good. Add the same one line "Create a free account" band to the
  bottom of the site detail page, where guests spend the most time.
- **Operators map** still geocodes in the browser (roadmap: two columns).
- **Recreational month calendar** (`/CalendarT`) now renders the finder at
  three cards per coast per day; the old 3.5 MB page is gone.
- **Version in the footer** is fine, but the footer link row has seven links on
  two lines on phones. Legal links can collapse into one "Legal" link.

## 5. Visual polish, palette level

- The template's blue and pink are repainted on shell pages, but a few
  components still carry the template gradients: FullCalendar events on the
  dashboard and group page (grey and green), badge pills in tables, the
  `btn-info` buttons inside modals. One more remap block covers them.
- Card headers as dark gradient blocks ("My favorites dive calendars") are
  heavy on phones: a 90 pixel tall header for a 20 pixel title. Use the panel
  title style from the site page (plain bold text) everywhere.
- Icons: Material Rounded throughout, plus Pablo's calendar icons on badges.
  Keep it to those two families; the old `material-icons` (sharp) still appears
  on the dashboard and calendar pages.

## Suggested order

1. Dashboard rebuild (section 1) and departed trips collapse: biggest felt change, no schema.
2. Account pages re-skin (section 2): the funnel.
3. Template banners and Weather page (section 4).
4. Groups, after Pablo's current work, with him.

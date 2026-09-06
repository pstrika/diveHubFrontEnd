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
- Dive site level is a four value field (1 Open Water, 2 Advanced, 3 Technical
  Normoxic, 4 Technical Hypoxic), not three. The inline legend uses the depth
  table already shown on the sites pages.
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
- Level and type filter chips on Dive Sites and wreckWiki, legend inline.
  This is the live user request.
- Redirect map document.
- Design tokens stylesheet, added but not yet applied, so the visual direction
  can be reviewed before it lands everywhere.

### Chunk 2: Shell, trip board, home (W1, W2, W5)

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

### Chunk 4: Show then gate, switchover

- Guest previews of Weather, Deco Planner, and personal calendar, with sign up
  prompted at the moment of saving.
- Pre launch: redirect map checked against the sitemap, canonicals confirmed,
  robots and sitemap confirmed.
- Merge `redesign` into `main`.

## Open questions

1. Originals folder: delete, resize in place, or move out of the repo.
2. Are `site_ratings` and `site_comments` populated enough to sort by rating
   by default?
3. Beta hosting: deployment slot or separate Web App.

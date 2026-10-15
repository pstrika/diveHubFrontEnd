# Logbook design spec (for the uploader apps)

Source of truth for the native apps to mirror, per Pablo's scope rule
("the app stays a basic companion, richer stuff stays on the website").
Values below are pulled directly from `public/assets/css/divershub.css`
and the two pages themselves (`resources/views/pages/Logbook.blade.php`,
`resources/views/pages/DiveDetails.blade.php`), not approximated.
Screenshots: `docs/screenshots/logbook-list.png`,
`docs/screenshots/dive-detail-oc-imperial.png` (OC, dive 8),
`docs/screenshots/dive-detail-ccr-imperial.png` and
`docs/screenshots/dive-detail-ccr-metric.png` (CCR, dive 47) - all taken
2026-10-15 at 1200px wide with every card expanded (the earlier
`dive-detail.png` predates the Gases/Operator/Conditions work); imperial
units, real data from Pablo's account).

## 1. Colors

Light only - **this app has no site-wide dark mode.** The one exception
is the unrelated CCR Checklist feature, which has its own self-contained
dark toggle (`[data-theme="dark"]` scoped to `.dh-ccr-bg` only) - not a
pattern the Logbook/DiveDetails pages participate in or that's relevant
here. If the app wants a dark variant, derive it yourselves; there's
nothing to mirror.

| Token | Hex | Use |
|---|---|---|
| `--dh-sand` | `#fbfbf9` | Page background |
| `--dh-paper` | `#ffffff` | Card/row background |
| `--dh-ink` | `#0b2a3a` | Primary text, primary buttons |
| `--dh-deep` | `#0e4d68` | Headers, active states, dive-number badge fill |
| `--dh-sea` | `#0e7c9e` | Links, accents, icons |
| `--dh-shallow` | `#5fb4cf` | Highlights |
| `--dh-foam` | `#e3f1f6` | Soft fills, selected-chip background |
| `--dh-muted` | `#5a6b78` | Secondary text |
| `--dh-line` | `#d8dee3` | Borders/dividers |
| `--dh-good` | `#2e7d4f` | Success (green gas pills, good status) |
| `--dh-avg` | `#9c7c15` | Warning-ish/average status |
| `--dh-poor` | `#b0532a` | Poor status |
| `--dh-warn` | `#c2660f` | Warning |
| `--dh-danger` | `#b0322b` | Danger |

Gas pill colors specifically (not site-wide tokens, but used throughout
both screens): O2/green `#2e7d4f`-family, He/blue `#0e7c9e`-family, Air
grey `#5a6b78`-family - see section 3.

Chart card background is **not** `--dh-ink`'s text color, it's an actual
dark surface: `var(--dh-ink, #16232b)` (`.dh-dive-chart-card`), with
white (`#ffffff`) depth-line/axis text and `rgba(255,255,255,0.15)` grid
lines - the only dark-surface element on either page, intentional (makes
the profile chart read like an instrument display against the otherwise
light page).

## 2. Typography

Single font family site-wide, no secondary display face:

```
--dh-font: "Roboto", -apple-system, "Segoe UI", Arial, sans-serif;
```

No Roboto Slab anywhere in this app. Material Icons Round is the icon
font for every inline glyph that isn't one of the custom SVGs (section 5).

| Element | Size | Weight |
|---|---|---|
| Row title (site/classification name) | 1rem (body default) | 700 |
| Row subtitle (time · computer · operator) | .875rem (`text-sm`) | 400, `--dh-muted` |
| Section/card headings | ~1rem (`<h3>` in `.dh-calc-inputs-head`) | 700 |
| Header stat big numbers (max depth, duration, etc) | 1.15rem | 700, `--dh-deep` |
| Stat labels (caption under each number) | .68rem, uppercase, .03em letter-spacing | 400, `--dh-muted` |
| Dive-number badge | .82rem | 700, white on `--dh-deep` circle |

## 3. Logbook list row

Order, left to right (see `docs/screenshots/logbook-list.png`):

1. **Number badge** - circular, 30px, `--dh-deep` fill, white bold number. This is the diver's **chronological dive number** (oldest dive = 1), computed server-side by counting dives at-or-before this one by `started_at` - it is explicitly **not** the raw dive-computer-reported number, which is unreliable (null on a partial "New"/"Last N" sync, only trustworthy on a full "All" download). If the app wants to show a number locally before syncing with the server, derive it the same way (local chronological position), never trust the computer's own on-device index for this.
2. **Date block** - day number large (e.g. "10"), month+year small caps underneath (e.g. "OCT 2026").
3. **Title row** - a small icon (section 5) + "{Site name}" if the dive is tagged to a site (not built yet, effectively never true today), else **"Recreational Dive"** or **"Technical Dive"** (see section 5 for the classification rule).
4. **Subtitle** - `{start time, e.g. "9:15 AM"} · {computer model} · {operator name if set}`, muted/secondary color.
5. **Right-aligned stats** - max depth (unit-formatted, bold, label "max depth" beneath) and duration as `H:MM` (e.g. "00:55"), label "duration" beneath.
6. **Chevron** - plain right-pointing caret, muted color, purely decorative (whole row is the tap target).

No grouping by month/trip - a flat reverse-chronological list (newest
first), paginated 20 per page with simple Newer/Older links.

Units: depth is shown in the viewer's unit preference (imperial
default - see section 6), one decimal place, e.g. `33.0 ft` / `10.1 m`.
No combined "24.2 m · 36 min · 22°C" single-line format - each stat is
its own labeled block, not concatenated text.

Upload state / site tag: **nothing currently indicates either.** Site
tagging isn't built (`site_id` exists in the schema but nothing sets it
yet - don't build a "has site" indicator around it being commonly true).
There's no separate "uploaded"/"synced" badge on a row at all - every row
on this page is, by definition, already uploaded.

Stats strip above the list (also in the screenshot): three tiles, "Total
dives", "Total time" (`{h}h {m}m`), "Deepest dive" (unit-formatted) -
each with a small Material icon, green left accent currently (`is-good`
status styling, not really meaningful here, just the default tile look).

## 4. Dive detail

Order top to bottom (see `docs/screenshots/dive-detail.png`):

1. **Back button** (left) / **chips + tools** (right, stacked):
   - Top chip: Open Circuit vs Closed Circuit (`--dh-deep` filled pill, white text, icon - air glyph for OC, the CCR SVG for CC). **Computer-reported, never editable.**
   - Smaller chip below it: Recreational/Technical classification (`--dh-sand` fill, dark text, small rec/tec SVG icon, pencil-edit icon). **Diver-editable**, defaults from max depth (>130ft/39.624m = Technical) but an explicit edit always wins once set.
   - Below that: unit toggle button + a print button (**the print button is a placeholder today, no real action wired up - don't treat it as a feature to mirror functionally**).
2. **Header stat mosaic** - tiles in this order: Dive number, Max depth, Avg depth (omitted if not reported), Duration, Min water temp (omitted if not reported), Start time. Each tile has a small Material icon above the number.
3. **Computer/gas line** - `{computer model} · serial {serial}` then each gas mix as a pill (section 3's pill rules) with a small role label underneath/beside it: **Diluent**, **Bailout**, **Deco** (CCR dives), or **Backgas**, **Deco** (OC dives) - see the labeling rule below.
4. **Chart card** (dark surface, section 1) - toggle chips above a line chart, Depth always on and locked, everything else starts off:
   - **Depth** - always visible, white line, light fill under it, **drawn surface-down** (plotted as a negative value so it reads top-to-bottom on screen, not as an inverted/reversed axis - matters if you're replicating the look, the line itself goes negative).
   - **Temperature** - `#ff8a65` (orange), own right axis, only shown as a toggle if the dive has any temperature samples.
   - **PPO2** - `#4fc3f7` (light blue), shares a right axis with Setpoint, only if present.
   - **CCR Setpoint** - `#81c784` (green), dashed line, same axis as PPO2, only if present.
   - **NDL** - `#ba68c8` (purple), own axis, only if present.
   - **Tank pressure, per tank** - one toggle per tank the dive actually has pressure data for, color-cycled through `['#ffd54f', '#f06292', '#4db6ac', '#90a4ae']` (yellow/pink/teal/grey) in tank order, labeled by role (Diluent/Oxygen/Tank N - see tank labeling below), shares a right axis, unit-aware (bar/psi).
   - Gas switches are marked as thin dashed amber vertical lines (`rgba(255,193,7,0.6)`) with a small "Gas N" label at the top, detected from explicit gas-change events or a `currentGas` index change between samples.
   - X axis: time in **minutes** (not seconds), white text/grid at `rgba(255,255,255,0.15)`.
   - Toggle chips that have no data for this specific dive **don't render at all** (not greyed out, removed from the DOM) - e.g. a dive with no PPO2 samples shows no PPO2 chip.
5. **Three separate cards**, each with a dark header bar (icon + title) and white body, in this order:
   - **Dive Logistics**: Operator (autocomplete against existing operators, free text also accepted), Divemaster, Boat name, Boat captain.
   - **Dive Conditions**: Current, Surface conditions, Weather, Visibility, Entry type (all single-select dropdowns with a fixed option list - see the controller's validation for the exact value lists).
   - **Other**: Dive type (multi-select chips: Drift, Training, Photo/Video, Cave, Cavern, Wall, Reef, Wreck - any number can be picked), Buddies (free text), Notes (free text), one Save button at the bottom.

### Gas role labeling rule

`gas_mixes[].usage == 2` (the dive computer's own DILUENT tag) is always
labeled **Diluent** - reliable, computer-reported. Among the remaining
mixes, sorted by O2% ascending: the leanest is **Bailout** (CCR dive) or
**Backgas** (OC dive), everything richer is **Deco**. A single non-diluent
mix just gets that one label, no "Deco" contrast needed.

### Tank labeling rule

By `tanks[].usage` string: `"diluent"` → **Diluent**, `"oxygen"` →
**Oxygen**, anything else (including `"sidemount"`, which doesn't
distinguish which physical tank is which) → **Tank N** by array position
(1-indexed). `tanks[].gasMix` is never populated on Pablo's computer
(always libdivecomputer's `DC_GASMIX_UNKNOWN` sentinel) - don't rely on
it to pair a tank with a gas_mix.

A sample's tank-pressure value for tank index *i* is `profile[].tankPressures[i]`
positionally - confirmed as a real structural guarantee traced through
libdivecomputer's source (not a guess), **but the JSON shape varies**: a
sample where every tank reported comes back as an array, a sample
missing some tanks comes back as an object keyed by string index (e.g.
`{"1": 165.2}` when only tank 1 reported that sample). Handle both.

## 5. Icons

Dive-mode/category icons are custom SVGs in `public/assets/img/icons/`,
inlined and recolored via `currentColor` (not referenced as `<img>`):

- `icons_calendar_ccr.svg` - closed/semi-closed circuit dives.
- `icons_calendar_rec.svg` - the Recreational classification chip, and the list row for any non-CCR, non-freedive dive classified Recreational.
- `icons_calendar_tec.svg` - same, for Technical.
- `icons_calendar_freediving.svg` - `dive_mode == "freedive"`. Reachable today (a real DiveMode value), used in the list icon logic.
- `icons_calendar_snorkeling.svg` - **not reachable from any computer-synced dive today** - no DiveMode value maps to it. Only relevant once manual dive entry exists (unbuilt, docs/roadmap.md #9). Don't wire it into computer-sync logic.

The OC/CC header chip on the detail page does **not** use the rec/tec
icons (an earlier draft of this made that mistake and it was corrected)
- it's a plain Material Icons "air" glyph for Open Circuit, the CCR SVG
for Closed Circuit. The rec/tec SVGs belong on the classification chip,
not the circuit-type chip - don't conflate the two.

Site link: no site-link icon exists yet - site tagging isn't built.

## 6. Units

Default **imperial**, toggle persisted in `localStorage` (key
`dh-units`, value `"imperial"` or `"metric"`) - a pure per-viewer display
preference, not synced from the server or tied to any account setting.
Conversions: depth m→ft ×3.28084, temp C→F standard formula, pressure
bar→psi ×14.5038. Depth/temp to 1 decimal place, pressure/psi rounded to
whole numbers.

# CCR checklists inside Divers Hub: scope

Status: draft for Zach and Pablo, 2026-11-10. Nothing here is built. This file
is uncommitted on the local branch `revo-companion` (cut from `origin/main` at
`e5765d9`, v12.33.5).

## 0. Built so far (local only, uncommitted, 2026-11-10)

Decisions taken by Zach on 2026-11-10: extend Pablo's feature; write every
step in our own words rather than copying manufacturer text; "GBM" is the
Scubatron chest mount unit; an item ticks itself when all its fields are
filled.

First slice, on branch `revo-companion`:

- **Access** is the `ccr_checklists` early access list in `config/divehub.php`
  (Pablo and Zach) instead of one hardcoded email.
- **Four stages** for the rEvo: Pablo's build sheet untouched, plus closed
  check, pre-jump and post-dive as new template versions `c1.0`, `p1.0`,
  `d1.0`, flagged `draft` until a rEvo diver has read them line by line.
- **Hub page** at `/CcrChecklist`: optional unit setup, the stages as large
  buttons with when each was last done, Pablo's equipment dashboard and build
  history, and a second list for the other stages.
- **Warning modal** before every checklist, cannot be dismissed, acceptance
  stored on the session with the wording's version. Strip on the form, the
  saved page and the PDF.
- **Auto tick** when every field on an item is filled. A value in its red
  band does not tick the step for the diver.
- **Pre-breathe countdown**, saved with the step.
- **Save as you go** on the device, restored after a closed tab, cleared only
  once the server confirms the save. Retried saves cannot duplicate.
- **Schema:** one migration, `2026_11_10_000000_add_stages_and_units_to_ccr_checklists`,
  additive: new `ccr_units` table and five nullable or defaulted columns on
  `ccr_checklist_sessions`. Run locally only.

Added later the same day:

- **Wording rule, corrected.** The first pass at the three new stages changed
  steps, dropped details and stated outcomes the source did not. They were
  redone to a strict rule, now written at the top of the templates in
  `config/ccr_checklist_templates.php`: same steps, same order, nothing
  removed, rewording only ever more descriptive, never an outcome the source
  does not state, literal where the shorthand is ambiguous.
  `docs/ccr-checklists-wording-review.md` has every step beside its source line.
- **More than one rebreather.** A picker on the hub, add, edit (kind, name,
  serial) and retire. Each unit has its own stages, history, dashboard and
  device drafts. Sessions saved before units existed show under the diver's
  oldest unit, so nothing disappears. Retiring keeps the saved checklists.
- **Other unit kinds can be added and named** (JJ-CCR, Liberty, O2ptima CM,
  GBM, Other) but have no built in checklist yet. The hub says so and does
  not offer the rEvo's steps for them.

- **Your own steps.** On any rebreather and any stage, a diver can add steps
  anywhere in the checklist, reword them, move them one place at a time, and
  remove them (`/CcrChecklist/Customize/{stage}`). Built in steps cannot be
  removed, moved or reworded. A step is anchored to the built in step it
  follows, not to a row number. Each saved checklist keeps its own copy of
  the diver's steps, so later edits never change a saved record. Tick only
  for now; steps with typed values are a later pass.
- **Units with no built in checklist** (JJ-CCR, Liberty, O2ptima CM, GBM,
  Other) get all four stages made entirely of the diver's own steps.
- **Schema:** a second additive migration,
  `2026_11_11_000000_add_custom_items_to_ccr_checklists`: table
  `ccr_custom_items` and a nullable `custom_items` JSON column on sessions.

Not built yet: dive linking and the scrubber ledger (section 6), server side
in-progress sessions, opening with no signal.

Things to know before this ships:

- The three new templates need a line by line read by Zach and Pablo.
  Specifically: closed check step 4 drops a phrase from the prototype that
  was not understood ("re-open gap"), and steps 12, 16 and 19 spell out what
  the prototype only abbreviated.
- Pablo's build template still carries its original wording. It was left
  alone because changing it changes how his 196 saved sessions read.
- The saved page now shows tick times in the viewer's time zone. They were
  printing in UTC. The PDF still prints UTC, since a PDF cannot ask the
  browser what zone it is in.
- New sessions by anyone but Pablo no longer start with his sensor dates.
- `public/sw.js` serves `/assets/` cache first by URL. The new script and
  stylesheet carry their modified time in the URL so edits show up; every
  other asset only refreshes when the app version changes.

## 1. The problem, and what we actually know

Zach built a single file rEvo checklist app (`docs/tools/revo-dive-companion.html`)
and sent it to a few people as a proof of concept. People kept using it even
though it forgets everything when the tab closes. The ask: make it a real tool
inside Divers Hub, saved against the diver's account, tied to the dives they
log, and eventually covering more rebreathers than the rEvo.

What is verified and what is not:

| Claim | Status |
|---|---|
| The prototype stores nothing. State is one in-memory object, export and import as JSON is the only way to keep it. | Verified in the file. |
| People are using it. | Reported by Zach. No count, no list of who, no list of which units they dive. |
| Divers Hub has nothing for this yet. | **False.** See section 2. |

The second row matters. Unit priority in section 8 should follow what the
people already using it actually dive. Worth asking them before we pick.

## 2. What already exists in main (this is not a greenfield)

Pablo shipped a rEvo build-up checklist on 2026-10-15 and it is live in
production, locked to his own account.

| Piece | Where | What it does |
|---|---|---|
| Routes | `routes/web.php` 285 to 289 | `/CcrChecklist` list, new, save, view, PDF. |
| Access | `UserPolicy::ccrChecklistAccess` | Hardcoded to one email. Not even admins. |
| Table | `ccr_checklist_sessions` | `user_id`, `template_version`, `build_date`, `answers` JSON, `signature`. One row per completed build. Never edited after save. |
| Templates | `config/ccr_checklist_templates.php` | The checklist is data, not markup. 16 items for "v1.5", each with typed fields, alarm bands, and "replaced?" follow-ups. A saved session always renders against its own version. |
| Form behaviour | `public/assets/js/ccr-checklist.js` | Timestamp per tick, alarm colours, signature pad, progress bar, range clamps, scrubber time formula. |
| History page | `CcrChecklist/Index.blade.php` | Equipment dashboard: each O2 cell's install date, age and last millivolt reading, last scrubber time, which canister is on top, last battery change. |
| PDF | dompdf, one page | Date, time, name, every answer, signature. |
| Nav | `App\Support\NavTabs` | "rEvo CCR Checklist" is already a pickable bottom tab, gated by the same policy. |
| Account deletion | `App\Support\AccountDeleter` | Already removes a user's sessions. |

Pablo's comments say production holds 196 backfilled historical sessions of
his. That is real data in the existing shape and it has to keep rendering.

The surrounding features that did not exist when the roadmap section was
written, and that change the design:

- **The logbook is real now.** `dives` rows arrive from the dive computer
  through the iPhone app, from file imports, and from manual entry. Each has
  `started_at`, `duration_seconds`, a computer-reported `dive_mode`, and
  `Dive::isCcr()`.
- **Equipment is real now.** `equipment_items`, saved configurations, and
  `dive_equipment` linking gear to a dive. Open to every logbook user.
- **Early access flags.** `config/divehub.php` `early_access` turns a feature
  on for a list of emails or for everyone. That is the right gate for this,
  instead of a hardcoded address.
- **The disclaimer pattern.** The deco planner and multi dive planner open a
  modal that cannot be dismissed by clicking outside, on every page load. A
  code comment records that this is deliberate and should not be softened
  without Pablo.

## 3. Prototype versus Pablo's build

They overlap on one checklist and disagree on one concept.

| | Zach's prototype | Pablo's shipped feature |
|---|---|---|
| Checklists | Four per dive: Build, Closed (pressurised), Pre-jump, Post-dive. 70 or so items, sectioned. | One: Build-up, 16 items. |
| Source | rEvo checklists v0.4 and pre-jump tags A and B. Post-dive is Zach's own starter list. | The paper build sheet Pablo uses, "v1.5, reviewed Jan-22". |
| Recorded values | A few per list, with min and max. | Rich: pressures, mV per cell on two controllers, battery volts, sensor dates, with alarm bands. |
| Unit of work | A "dive" that owns all four checklists, a plan, and gases. | A "build session". No dive concept. |
| Scrubber | A running hours counter for the stack: 3h on top, 1.5h reserve on the bottom, rotate or repack. Logged by hand at "finish dive". | Per build: RCT from the controller, scaled by planned depth. Which canister is on top, which were refilled. |
| Persistence | None. | Server, permanent. |
| Extras | Pre-breathe timer, gas calculator, unit switch, dark mode. | Signature, PDF, sensor age dashboard, dark mode. |
| Offline | Works with no network because it is one file. | Needs the network to open and to save. |

The scrubber disagreement is a real diving-practice difference, not a bug in
either. A product for other people needs both: a ledger of time on the
canisters, and room for the controller's own reading where the unit has one.

## 4. The strongest argument against doing this

It should be stated before the plan.

1. **This is life-support equipment.** A trip finder that is wrong costs
   someone a morning. A checklist that is wrong, stale, or quietly skips a step
   is in a different category. Every design choice below is shaped by that, and
   it is the reason for section 7.
2. **Manufacturers own their checklists and revise them.** We would be
   republishing their text and taking on the job of keeping it current. See
   section 8 for what we could verify about each.
3. **It is Pablo's code and Pablo's data.** The cheapest wrong move is a
   second, parallel checklist system next to his.
4. **The evidence of demand is one sentence.** If three people use the
   prototype, a first phase that only persists the rEvo flow for early access
   users is the right size. Multi-unit support should wait for proof.

None of these says do not build it. Together they say: build on Pablo's
engine, ship rEvo to a short list first, and treat the wording and the
template provenance as part of the feature rather than decoration.

## 5. Recommended shape

One feature, "CCR Checklists", living with the logbook, built by extending
what Pablo shipped rather than porting the prototype's in-memory app.

**Keep from Pablo:** templates as config, answers as JSON keyed by item,
a timestamp on every tick, alarm bands, signature, PDF, completed sessions
are permanent, the sensor dashboard.

**Bring from the prototype:** the four stages and their order, sections
inside a checklist, the pre-breathe timer, the scrubber stack tracker, and
the idea that a set of checks belongs to a dive.

**Drop from the prototype:** the Gas tab. The roadmap already settled this:
planning numbers come from Pablo's deco planner and gas planning page, one
implementation. The checklist links out to them.

### How a diver uses it

1. **First visit:** pick the unit ("rEvo"), give it a name, optionally a
   serial. Accept the warning. That is all the setup.
2. **Home for the unit:** one card. Scrubber time left, age of each cell,
   last build. One large button whose label follows where they are:
   "Start build check", then "Closed check", then "Pre-jump".
3. **Doing a checklist:** one section at a time, large tap targets, values
   typed where the manufacturer asks for one. It saves as they go. If the
   phone sleeps or the tab dies, they come back to where they were.
4. **After the dive:** they log the dive the way they already do, from the
   computer or by hand. Divers Hub attaches the checks to it on its own and
   takes the dive time off the scrubber. Nothing to re-type.
5. **Later:** the dive page shows the checks that preceded it. The checklist
   history shows which dives each build covered.

Step 4 is the reason to do this inside Divers Hub instead of leaving it a
standalone file.

## 6. Tying checklists to logged dives

The two records are created at different times by different paths, so the
link has to be made after the fact and it has to tolerate dives that arrive
late, get merged, or get deleted.

**The natural relationships**

- A **build** covers every dive until the unit is next broken down. Often two
  dives on one boat day. One build, many dives.
- A **pre-jump** belongs to exactly one dive.
- A **post-dive** closes exactly one dive.

**Linking rule (proposed)**

When a dive with `isCcr()` true appears in the logbook, by any path:

1. Attach the most recent completed pre-jump for that user that finished in
   the three hours before the dive's `started_at` and is not already attached.
2. Attach the most recent completed build in the 36 hours before, even if it
   is already attached to an earlier dive.
3. Record that the link was made automatically. The diver can detach or
   re-attach from the dive page. A manual choice is never overwritten.

The windows are a starting guess to tune against real data, not a finding.

**Scrubber time follows from the same event.** The dive's `duration_seconds`
is written to a scrubber ledger for the unit. Because it is a ledger keyed by
dive and not a counter, a dive uploaded three days late, edited, merged with
its duplicate from a second computer, or deleted all recompute correctly.

**Where it shows**

- Dive detail page: a "Pre-dive checks" card. Build completed at 6:52, pre-jump
  at 8:40, all items ticked, view. If nothing is attached the card is simply
  absent. No red badge on a dive for having no checklist: the logbook is not
  the place to scold, and plenty of people will use paper.
- Checklist history: each row lists the dives it covered.
- The other direction: a completed pre-jump with no dive after a day offers
  "Log this dive", opening the logbook's manual entry with the date filled in.

**Schema, all additive, users database**

| Table | Purpose |
|---|---|
| `ccr_units` | One row per rebreather a user owns: model key, name, serial, scrubber limits, optional link to an `equipment_items` row. |
| `ccr_checklist_sessions` (existing) | Add nullable `unit_id`, `stage`, `status`, `completed_at`, `client_uuid`, `disclaimer_version`, `disclaimer_accepted_at`. Pablo's 196 rows stay valid as completed rEvo builds. |
| `ccr_session_dives` | Link table: session, dive, automatic or manual. No new column on `dives`. |
| `ccr_scrubber_events` | Ledger per unit: dive time, manual time, repack, rotate, adjustment. |

Adding a `rebreather` category to equipment is a one line constant, no
schema.

**Things that will bite, checked against the code**

- The logbook can merge two computers' records of one dive and can combine
  duplicates. A merge has to carry the links and must not double count
  scrubber time. This is the riskiest integration point.
- `dives` and the checklist tables are on the same connection, so real
  foreign keys work here. The "never FK to trips" rule does not apply.
- The deploy workflow does not run migrations. Pablo runs each by hand, by
  file name, as in the consent migration runbook.

## 7. Warnings: a tool, not an authority

Model it on the planner modal and go one step further, because the planner
shows the warning and this should also record that it was seen.

1. **Blocking modal when a checklist is started.** Same component and same
   non-dismissable behaviour as the multi dive planner (it uses the correct
   Bootstrap 5 attributes; the deco planner's own copy uses the old attribute
   names and may be dismissable by Escape, which is worth a separate fix).
   The button says "I understand", not "Close".
2. **Store the acceptance.** Version of the wording and the time, on the
   session row. Cheap, and it is the difference between "we showed it" and
   "this diver acknowledged it on this date".
3. **A permanent strip on every checklist screen.** Names the source document
   and its version, and says the manufacturer's current checklist and the
   diver's training take precedence.
4. **Never a verdict.** No "safe to dive", no "ready", no green screen at the
   end. A finished list says "Checklist complete: 34 of 34". An alarm says
   what was entered and what the manufacturer's range is, nothing about
   whether to dive.
5. **Nothing blocks.** The tool never refuses to let someone continue. It
   records and it warns. Refusing implies that when it does not refuse, things
   are fine.
6. **Template provenance in the data.** Each template carries source, version,
   the date we last checked it against the manufacturer, and who checked.
   Shown on the screen and printed on the PDF.
7. **PDF footer and Terms of Use.** The deco planner PDF already has a
   disclaimer builder to mirror. The Terms describe Divers Hub as a discovery
   and scheduling tool only and say nothing about planning or checklist
   tools. That gap exists today for the deco planner too.

Proposed wording for the modal, to be edited by Zach and Pablo and then read
by a lawyer. I am not one, and this should not ship on my wording alone:

> **Rebreather diving can kill you, and a checklist does not change that.**
> This tool helps you record the checks you perform. It does not perform
> them, verify them, or tell you whether your unit is fit to dive.
>
> **It does not replace your manufacturer's current checklist, your user
> manual, or your training.** The items here were transcribed from published
> manufacturer material and may be out of date or wrong for your
> configuration. Where they differ, the manufacturer and your instructor are
> right and this tool is wrong.
>
> **You are responsible for your unit and your dive.** Divers Hub accepts no
> responsibility for how this tool is used.

## 8. Other rebreathers

Web research, 2026-11-10. "Read" means the document itself was opened and
read. "Not read" means we know it exists but could not open the current
version, so nothing about its contents should be trusted yet.

### The finding that changes the plan: we may not be allowed to publish these

| Manufacturer | What their document says about reuse |
|---|---|
| JJ-CCR | Reproduction prohibited without written approval from JJ-CCR ApS. |
| Dive Rite (O2ptima CM) | Manual may not be reproduced, changed, or distributed without authorization from Lamartek, Inc. |
| SF2 (ScubaForce) | No reproduction without prior written permission of the publisher. |
| ISC (Megalodon) | Reprinting permitted "for sole use of owners of ISC Products". An owner printing it, not an app publishing it. |
| Divesoft, Fathom, KISS, Triton | No statement found. That is not permission. |
| rEvo | Not checked. The prototype and Pablo's form both already transcribe rEvo text. |

So "find the official checklist and load it" is not a plan we can execute on
our own for any unit, including the one we started with. Two routes, and the
product should support both:

1. **Ask.** Written permission from each manufacturer to publish their
   checklist in Divers Hub with attribution and revision date. Rebreather
   Forum 3 asked manufacturers to publish current checklists precisely so
   divers use them, so the request is aligned with what they say they want.
   An instructor's copy does not solve this: an instructor can give us the
   content, not the right to republish it.
2. **Bring your own.** A diver builds or edits their own checklist inside
   Divers Hub, starting from a neutral skeleton we write ourselves. This
   needs nobody's permission, works for every unit on day one, and matches
   how experienced CCR divers actually work, since most have a personal
   variant. It moves "personal checklists" from a late phase to the core.

This is a question for a lawyer alongside the warning text. I am flagging
the statements I found, not giving a legal opinion on fair use.

### What exists per unit

| Unit | Official checklist | Status | Notes for the data model |
|---|---|---|---|
| **JJ-CCR** | In the user manual, chapter 9. No standalone PDF. | Older manual (v2.00 rev 07, 2018) read. Current v3.04 not read. | Three lists: on land (about 24 steps), shortly before diving (8), just after entering the water (3). Tick only. Pre-breathe is 2 to 3 minutes, not 5. The old manual says used soda lime must never be reused, which conflicts with a running scrubber tracker. |
| **Divesoft Liberty** | Four places: the unit's own handset runs the pre-dive inspection, the manual, a standalone assembly checklist, and Divesoft's phone app. | 2016 assembly checklist read (30 items, signature footer). Current version and the sidemount list not read. | The unit itself gates dive mode on its checks. For this unit we record "unit pre-dive inspection passed", we do not duplicate it. Sidemount has its own assembly list and shares the pre-dive list. |
| **Dive Rite O2ptima CM** ("Choptima") | Yes, the most complete set found. Standalone assembly checklist plus manual Rev D appendix. | Read. | Four lists: Assembly Guide (30 items), Survival (6, before every dive of the day), Preflight (6, just before entry), Post Dive (immediate and end of day). Four O2 sensors. Scrubber is either a cartridge or packed granular, tracked in minutes used. |
| **"GBM"** | None found. | Not found. | Most likely the **Scubatron GBM**, "Generic Breathing Machine", a chest mount unit. It is literally named GBM and came up next to another chest mount. Worth confirming with whoever said it. Runner-up by sound: Fathom Gemini. Manual unit, four sensors of two different types, no built-in controller. |
| **Fathom** (Mk III, Mk V, Gemini) | Yes, standalone build checklist, updated May 2025. Also in their own app. | Read. | Five sections plus a periodic checklist. Per cell: date, mV in air, a computed value, mV in oxygen. A computed field, not just an input. No pre-jump list. |
| **ISC Megalodon** | Yes, pre-dive (42 items) and post-dive (18). | Read, but it is the 2009 form for old electronics. | Records three cells on two independent electronics. Keeps a running log of accumulated minutes for scrubber and both batteries. Nothing found for the Tiburon. |
| **KISS** (Spirit, Sidewinder) | Yes, in the manual. | Read. | Two parts, initialled per line. The richest set of recorded values found. Scrubber tracked in hours. Sidewinder has two canisters and no included PO2 display. |
| **SF2** | Yes, in the manual (v20, Oct 2024). | Read. | Three short blocks: assembly (7), pre-dive (5), pre-jump (3). Tick only. |
| **Triton** | Yes, one page. | Read. | 11 sections, about 27 ticks, no recorded values. |
| **AP Inspiration / Evolution** | Unknown. Only a TDI course appendix was found. | Manufacturer manual not read. | AP's own electronics. |
| **X-CCR, Hollis Prism 2, Poseidon SE7EN, Halcyon Symbios, Mares Horizon** | Believed to exist in manuals or the maker's app. | Not read. Files too large, blocked, or links dead. | Poseidon and Symbios lead with electronics or their own app, like the Liberty. |

Source links for every row are in the research notes; ask and they go in an
appendix.

**About the rEvo material we already have.** TDI's checklist library links a
rEvo file named "rEvo-checklist-v05-22092015". The prototype was transcribed
from v0.4. Not opened, so I cannot say what changed, but it means the
prototype is probably one revision behind, and it is exactly the staleness
problem section 4 warns about.

### What this means for the design

The template format Pablo built is close, and the survey shows what it still
needs before a second unit fits:

- **Stages vary.** Build, a condensed "every dive" list, pre-jump, in-water
  bubble check, post-dive, periodic. A unit declares which it has. Do not
  hardcode four.
- **Field types.** Tick, initials, number with a unit, date, pick-one,
  computed value, timed step, value with a pass range. Pablo's config already
  has most of these.
- **Repeating groups.** Cells (2, 3, 4, or three on each of two controllers),
  bailout cylinders (1 to 3 or more), canisters (1 or 2).
- **Scrubber tracking is per unit.** Minutes, hours, single use only, or
  cartridge. The ledger in section 6 holds for all of them. The limits and
  the wording do not.
- **Electronics-led units** need one "unit self-test passed" item.
- **Every template carries source, revision, and the date we checked it.**
  Rebreather Forum 3 asked for the revision date to be shown on any
  checklist. We should hold ourselves to that.

### What the standards bodies say

- **Rebreather Forum 3 (2012):** manufacturers should publish checklists for
  assembly, immediate pre-dive and post-dive, with the revision date.
- **Rebreather Forum 4 (2023), read in the journal:** strongly advocates a
  brief pre-entry checklist just before water entry, in check and response
  format where practical, covering critical items such as rebreather switched
  on, oxygen on, diluent on, inflation connected and working.
- **RESA:** use a checklist before every dive, electronic or paper, and do
  pre-jump tests.

The Forum 4 statement supports keeping pre-jump short and separate from the
build, which is how the prototype already works. A buddy "check and response"
mode is a natural later addition and fits a product that already has groups.

### Who to contact

| Unit | What we need | From whom |
|---|---|---|
| rEvo | Current checklist revision, and permission | rEvo |
| JJ-CCR | Manual v3.04 chapter 9, and permission | JJ-CCR ApS |
| Liberty | Current assembly checklist and sidemount list, and permission | Divesoft |
| O2ptima CM | Permission only, content is in hand | Dive Rite |
| GBM | Confirm which unit, then any checklist at all | Scubatron or an IANTD GBM instructor |

Instructors are the right people to confirm which revision is current and
how divers really use the lists. They cannot grant permission to republish.

## 9. Offline and saving

The roadmap called offline "not optional" and that is still right for a boat.
But the current users have no persistence at all, so order matters:

| Step | What the diver gets | Cost |
|---|---|---|
| Save as you go | Every tick writes to the device immediately and to the account when there is a connection. Closing the tab loses nothing. | Small. This alone fixes the reported problem. |
| Survive no signal mid-checklist | Ticks queue on the device and sync later. Each session carries a client generated id so a retry cannot create a duplicate. | Medium. |
| Open with no signal | The service worker (`public/sw.js`, which today caches only an offline page) precaches the checklist page, its script, and the diver's templates. | Medium, and it is the first real offline page in the app, so it sets the pattern. |

Pablo's rule that a finished checklist is permanent stays. What changes is
that a session can exist as "in progress" before it is finished.

The iPhone app exists now ("Logbook by Divers Hub"). If the save endpoints are
JSON from the start, the app can use them later without a rewrite. Building
the checklists into the app is out of scope here.

## 10. Who can use it

- **Members only.** The logbook already requires a real account, and the
  shared guest user cannot hold anyone's data.
- **Early access flag**, `ccr_checklists`, starting with Zach, Pablo, and the
  people already using the prototype. Opened to everyone by a config edit.
- **A public page** that explains the tool and asks for sign up, for the
  people arriving from the old link. It also gives search something to index.
- **Bring your data.** The prototype can export its state as JSON. Accepting
  that file once, to carry over scrubber hours and history, is a small
  courtesy to the people who made the case for this.

## 11. Phases

| Phase | Contents | Size | Schema |
|---|---|---|---|
| 0 | Decisions in section 12. Nothing built until Pablo agrees to the direction. | Conversation | None |
| 1 | Units, the four rEvo stages as templates, sessions that save as they go, the warning and its acceptance, early access gate, public page. Pablo's existing form keeps working throughout. | Large | `ccr_units`, columns on sessions |
| 2 | Dive linking, the dive page card, scrubber ledger fed by logged dives, merge handling. | Large | `ccr_session_dives`, `ccr_scrubber_events` |
| 3 | No signal: queue and sync, then service worker precache. | Medium | None |
| 4 | Personal checklists: a diver builds or edits their own from a neutral skeleton. Works for every unit and needs no manufacturer's permission. Moved up because of the licensing finding in section 8. | Large | One table for personal templates |
| 5 | Official checklists for a second and third unit, each one only after written permission. | Medium each | None |

Each phase is usable on its own. Phase 1 already replaces the prototype.

## 12. Decisions needed

1. **Pablo: is this the direction?** Opening his private feature, adding
   nullable columns to his table, and replacing the hardcoded email with an
   early access flag. Everything else waits on this.
2. **Pablo: scrubber tracking.** Ledger of time, controller RCT, or both per
   unit. Proposal is both.
3. **Zach: who uses the prototype and what do they dive?** Sets the unit
   order and the size of phase 1's audience.
4. **Which checklist is the rEvo build check?** The prototype's v0.4 list and
   Pablo's v1.5 sheet are different documents. One should be the default and
   we need to know which is current from rEvo.
5. **Legal read of the warning and a Terms of Use addition.**
6. **Guests.** Proposal is no anonymous use. Confirm.
7. **Permission to publish manufacturer checklists.** Including rEvo's, which
   both the prototype and Pablo's form already transcribe. Until this is
   settled, rEvo stays in early access and no second unit's official text
   goes in.
8. **Confirm "GBM".** Most likely the Scubatron GBM chest mount unit.

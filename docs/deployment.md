# Redesign release: deployment runbook

For Pablo. Everything needed to take the `redesign` branch to production and what
to watch afterwards. Written 2026-09-07 against `redesign` at the head that is
also running on the `divehub-redesign` slot.

## 1. What changes in production, in one screen

| Area | Change | Action needed |
|---|---|---|
| Database schema | **One approved migration, four columns on `users`** (Zach and Pablo, 2026-09-10): `whatsapp_notifications`, `email_consent_at`, `sms_consent_at`, `whatsapp_consent_at`. File `2026_09_10_210000_add_communication_consent_to_users_table.php`, users database. It also stamps anyone already opted in to email or SMS as consenting now, so nobody reads as never having consented. Plus Pablo's own two from main 9.17.0 and 9.19.0. | Run it by hand after deploy, see section 4 |
| Communication consent | Email, SMS and WhatsApp each have their own checkbox with the consent wording next to it, on the profile page under "Communication preferences" and as a step in the welcome wizard. Opting in stamps the date on the user, opting out clears it, and the profile page shows "Agreed 10 Sep 2026" beside each channel. If Twilio ever asks where a person consented, the answer is that screen plus the timestamp. | Nothing |
| Frozen client pages | `/CalendarHydrotherapy` renders in an iframe on the client's own site and is frozen to its pre redesign output. Its controller and view live apart (`HydrotherapyCalendarController`, `pages/CalendarHydrotherapy.blade.php`) and the shared layout gives it none of the redesign chrome. Verified against production: identical apart from live trip data. Do not edit either file, and check `App\Support\EmbeddedPage` before changing anything shared. | Nothing |
| Composer / npm | **Nothing from the redesign.** Pablo added `minishlink/web-push` on main (9.17.0); the workflow's composer install picks it up. No new PHP extensions beyond GD (already loaded). | Nothing |
| `.env` | **Nothing from the redesign.** Pablo's push notifications read `VAPID_PUBLIC_KEY`, `VAPID_PRIVATE_KEY`, `VAPID_SUBJECT` (main 9.17.0). Without them the notification toggle stays hidden and everything else works. | Set if not already set |
| Config | New file `config/divehub.php` (release version and date). | Bump the version before merging (section 3) |
| Routes | Three old tool URLs now 301 into the explorer. `/Landing` renders the home page. Deco Planner, My Calendar and the five themed calendars open to guests; the themed calendars render the trip finder with a type preset. `/Trips` accepts `range`, `from` and `to` (noindex). All other URLs unchanged. | Nothing; see section 6 |
| Public assets | New `divershub.css`, `divershub.js`, `manifest.webmanifest`, PWA icons, `og-default.jpg`, and 1,900 WebP copies of the site photos (109 MB) under `public/assets/img/sites/web/`. | Nothing; ships in the zip |
| Photos uploaded through the admin | 151 photos exist only on the server. Their WebP copies were generated from production and are in the repo, so nothing is missing at launch. | Optional: run the backfill command once after deploy to catch anything uploaded in between (section 4) |
| Site ranking | Home, explorer and dashboard order sites by trip counts blended with ratings (`App\Support\SiteRank`). Counts are computed from `trips` and cached one hour under the key `siterank.tripcounts.v1`. No schema change. | Nothing. `php artisan cache:clear` refreshes the counts early if ever needed |
| Add to home screen and push | `public/sw.js` and `public/manifest.json` are Pablo's (push notifications, 9.17.0 to 9.20.0); the redesign's placeholder worker and manifest were dropped in favour of his. The redesign adds the install bar on phones from the second visit (native one tap install on Android, the Share then Add to Home Screen hint on iOS) and carries his notification toggle into the Me drawer. | Nothing |
| Welcome wizard | Members whose profile lacks a certification level and any favourite places or boats are sent from the dashboard to `/welcome`: four short steps (name and photo, level, places, boats) that write the same `users` columns the profile page writes. "Skip for now" sets a two week cookie. No schema change. | Nothing. Expect most existing members to see it once after launch |
| Trip finder operators | `/Trips` accepts `op=1,7` (also `op[]`), a picker lists operators with trips in the selection, members get a "My favorites" shortcut from `users.favOperators`. | Nothing |
| `.env` (SMS and WhatsApp) | Pablo's SMS reminders (main 9.23.0 to 9.24.3) read the Twilio keys in `config/services.php`. WhatsApp sending uses the same Twilio account and the Meta business connection; the checkbox and the consent record ship now, the sending is Pablo's side. | Set if not already set |
| Deploy workflow | One extra post deploy step deletes cached config and routes. The zip step now leaves out three things the app never serves (see below). Trigger unchanged (push to `main`). | Nothing |
| Concurrent deploys | Azure answers `400 Bad Request` when a deployment starts while another is still running on the same app or slot. Both workflows now carry a concurrency group so runs queue. If a run fails with that message, just rerun it once the other finishes. The zip step also leaves out about 350 MB the app never serves (stray `public/assets<uuid>` chunk files, `illustrations/originals/`, `screens/`), so the package is about 650 MB. |

## 2. Before merging

1. Look at the beta slot once more: https://divehub-redesign.azurewebsites.net
   Home, Dive Today, Dive Sites, Operators, a site page, a trip page, sign in.
2. Confirm the merge is a fast forward (no conflicts to resolve):

   ```bash
   git fetch origin
   git merge-base --is-ancestor origin/main origin/redesign && echo "fast forward"
   ```

3. Decide the version number. It is one line in `config/divehub.php` and shows in
   every footer. Set to `10.0.0` (09/10/26). Pablo on the 2026-09-10 call: "release 10, it deserves it."

## 3. Merge and deploy

Option A, GitHub UI: open a pull request from `redesign` into `main` and merge it.
Option B, command line:

```bash
git checkout main
git pull origin main
git merge --ff-only origin/redesign
git push origin main
```

The push to `main` starts `.github/workflows/main_divehub.yml`. It runs composer
install on PHP 8.2, zips the tree, deploys to the `diveHub` app, then clears
compiled views, the application cache (sitemap), and now the cached config and
route files. About five to eight minutes. Watch it under Actions.

Do not run the production workflow by hand with `redesign` selected in the branch
picker. That deploys the branch straight to production.

## 4. Right after deploy

1. **Run the migration.** The deploy workflow does not run migrations, so this is
   a manual step. Kudu console for the production app, folder `site/wwwroot`:

   ```bash
   php artisan migrate --force
   ```

   Expected: the communication consent migration runs, plus Pablo's push
   subscriptions and messages ones if they have not already. Until it runs, the
   profile page and the welcome wizard will error on the missing columns, so do
   this before telling anybody the new site is live.

2. **Photo copies.** Kudu console for the production app, folder `site/wwwroot`:

   ```bash
   php artisan photos:web-copies
   ```

   Expected: a progress bar and a line like `Done: 0 written, 951 already up to
   date, 0 failed`. Anything "written" is a photo uploaded after 2026-09-07. Takes
   a minute or two. Safe to run again at any time. New uploads make their own
   copies from now on, so this is a one time catch up. If it says
   GD lacks WebP support, the site still works (pages fall back to the original
   photo); tell Zach and we will look at the PHP image on the host.

3. **Profile pictures.** The crop and upload flow on the profile page was broken
   on the live site too: it loaded Cropper.js from a CDN without a version, the
   CDN moved to Cropper.js 2, and the Confirm button called a method that no
   longer exists. The library is now vendored at 1.6.2. Avatars upload to
   `public/assets/img/users/` and the header, profile page and reviews read
   from there. Like the site photos, avatars uploaded on the server are not in
   the repo, so they show on production but not on the beta slot. No action.

4. **Config cache.** If you ever run `php artisan config:cache` on the server,
   note the workflow now deletes that cache on every deploy so the version in
   `config/divehub.php` is always read. Running `config:cache` again after a deploy
   is fine; running it and then editing config without a deploy is not.

5. **Smoke test on production** (each should return 200, footer shows the new version):

   - `/` and `/Landing`
   - `/Trips`, `/Trips/<next Saturday>`, `/Trips?range=weekend`, `/CalendarShark` (renders the finder)
   - `/DiveSites`, `/DiveSites?view=map`, `/WreckSites`
   - `/SiteDetails/spiegel-grove` (check the gallery loads WebP files from `img/sites/web/`)
   - `/Operators`, `/OperatorDetails/pura-vida-divers`
   - `/TripDetails/<any id from the board>`
   - `/DecoPlanner` and `/MyCalendar` while signed out (should render, not redirect)
   - `/MyDashboard` while signed in
   - `/sitemap.xml` (406 URLs, unchanged set)

6. **Redirects** (each should answer 301 to the target):

   - `/DiveSitesMap` to `/DiveSites?view=map`
   - `/DiveSitesSearch` to `/DiveSites`
   - `/DiveSitesAll` to `/DiveSites?sort=name`

## 5. What to watch in the first two weeks

- **Search Console.** Coverage should not move: every indexed URL is unchanged
  and the three redirected URLs were `noindex`. Titles, descriptions and
  canonicals were diffed page by page against production before the merge and
  match. The one difference is a stray `<title>shop</title>` inside an SVG in the
  old navbar, which is gone. `/DiveSites` now lists the whole catalog by rating
  instead of ten wrecks and ten reefs, same title and canonical.
- **Page speed.** Site pages and cards now serve WebP copies (a 17 MB original
  became a 42 KB gallery image). If a site page still pulls a multi megabyte JPEG,
  that photo has no copy: rerun the backfill command.
- **Mapbox usage.** The Operators map view geocodes each shop address in the
  browser when opened, as the old page did on every visit. It now only happens
  when someone opens the map view. If usage matters, a lat/lon pair on the
  `operators` table would remove it (open question below).
- **Logs.** `storage/logs/laravel.log` lines starting `SitePhoto:` mean a photo
  could not be resized (too large, unreadable, or missing). The upload still
  succeeded; the page serves the original.
- **Dashboard.** "Recommended for this weekend" is now ranked (favorites, then
  the top of the diver's level range, then site rating, seats, time) and rolls to
  next weekend once this weekend's boats have left. If members find it odd, the
  ordering is one block in `MyDashboardController::showDashboard`.
- **Recreational month calendar** (`/CalendarT`, signed in only) lists every
  recreational trip of the month, about 2,600 cards and 3.5 MB. It went from 24 to
  7 seconds but is still heavy. Candidate for a later cleanup.

## 6. URL changes, complete list

| URL | Before | After |
|---|---|---|
| `/DiveSitesMap` | Standalone map (noindex) | 301 to `/DiveSites?view=map` |
| `/DiveSitesSearch` | Search page (noindex), GET and POST | 301 to `/DiveSites` (search term forwarded as `?q=`) |
| `/DiveSitesAll` | Plain table of all sites (noindex) | 301 to `/DiveSites?sort=name` |
| `/Landing` | Old landing view | Same page as `/`, canonical stays on `/` |
| `/DecoPlanner*` | Login required | Renders for guests; calculations already allowed guests |
| `/MyCalendar`, `/MyCalendar/{date}` | Login required | Renders for guests with an empty state; add, book, remove, regenerate link still require login |

Everything else: unchanged URL, unchanged title, description, canonical, robots
and JSON-LD. Full table in `docs/seo/redirect-map.md`.

## 7. Rollback

The merge is a fast forward, so main's previous head is still there.

```bash
git checkout main
git revert -m 1 <merge commit sha>     # if merged with a merge commit
# or, for a fast forward merge:
git reset --hard <previous main sha>   # then push with --force-with-lease
git push origin main
```

Either push redeploys through the same workflow. No data to restore: the release
wrote nothing to either database. The WebP copies under `img/sites/web/` are
harmless to leave in place.

## 8. Open questions for you

1. **Originals folder.** The camera originals stay in `public/assets/img/sites`
   and are still served when a copy is missing. Keep them there, or move them out
   of the web root once every photo has copies?
2. **Operator coordinates.** A `lat` and `lon` on `operators` (set once from the
   admin) would let the operators map skip browser geocoding.
3. **Data Deletion page** is not in `sitemap.xml`. Intentional?

## 9. Not for production: local development note

The local users database (`material_2_pro`) is a hand built skeleton and was
missing `email_notifications` and `sms_notifications`, which production has.
Saving a profile locally threw a "column not found" error until they were added
by hand. This is a local artifact only; there is no migration to run anywhere.

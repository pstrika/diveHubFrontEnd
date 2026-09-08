# Redesign release: deployment runbook

For Pablo. Everything needed to take the `redesign` branch to production and what
to watch afterwards. Written 2026-09-07 against `redesign` at the head that is
also running on the `divehub-redesign` slot.

## 1. What changes in production, in one screen

| Area | Change | Action needed |
|---|---|---|
| Database schema | **None.** No migrations, no new columns, no new tables. Both databases are read and written exactly as before. | Nothing |
| Composer / npm | **None.** `composer.json`, `composer.lock`, `package.json` unchanged. No new PHP extensions beyond GD (already loaded). | Nothing |
| `.env` | **None.** No new keys. | Nothing |
| Config | New file `config/divehub.php` (release version and date). | Bump the version before merging (section 3) |
| Routes | Three old tool URLs now 301 into the explorer. `/Landing` renders the home page. Deco Planner and My Calendar pages open to guests. All other URLs unchanged. | Nothing; see section 6 |
| Public assets | New `divershub.css`, `divershub.js`, `manifest.webmanifest`, PWA icons, `og-default.jpg`, and 1,900 WebP copies of the site photos (109 MB) under `public/assets/img/sites/web/`. | Nothing; ships in the zip |
| Photos uploaded through the admin | 151 photos exist only on the server. Their WebP copies were generated from production and are in the repo, so nothing is missing at launch. | Optional: run the backfill command once after deploy to catch anything uploaded in between (section 4) |
| Site ranking | Home, explorer and dashboard order sites by trip counts blended with ratings (`App\Support\SiteRank`). Counts are computed from `trips` and cached one hour under the key `siterank.tripcounts.v1`. No schema change. | Nothing. `php artisan cache:clear` refreshes the counts early if ever needed |
| Add to home screen | `public/sw.js` (a service worker that caches nothing, it only makes the site installable) and an install bar on phones from the second visit: native one tap install on Android, the Share then Add to Home Screen hint on iOS. Dismissal is remembered on the device. | Nothing. Service workers require HTTPS, which the app already has |
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
   every footer. The branch currently says `9.16.0` to match main. A redesign
   release probably deserves `10.0.0`; your call. Set `released` on the same line.

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

1. **Photo copies.** Kudu console for the production app, folder `site/wwwroot`:

   ```bash
   php artisan photos:web-copies
   ```

   Expected: a progress bar and a line like `Done: 0 written, 951 already up to
   date, 0 failed`. Anything "written" is a photo uploaded after 2026-09-07. Takes
   a minute or two. Safe to run again at any time. New uploads make their own
   copies from now on, so this is a one time catch up. If it says
   GD lacks WebP support, the site still works (pages fall back to the original
   photo); tell Zach and we will look at the PHP image on the host.

2. **Profile pictures.** The crop and upload flow on the profile page was broken
   on the live site too: it loaded Cropper.js from a CDN without a version, the
   CDN moved to Cropper.js 2, and the Confirm button called a method that no
   longer exists. The library is now vendored at 1.6.2. Avatars upload to
   `public/assets/img/users/` and the header, profile page and reviews read
   from there. Like the site photos, avatars uploaded on the server are not in
   the repo, so they show on production but not on the beta slot. No action.

3. **Config cache.** If you ever run `php artisan config:cache` on the server,
   note the workflow now deletes that cache on every deploy so the version in
   `config/divehub.php` is always read. Running `config:cache` again after a deploy
   is fine; running it and then editing config without a deploy is not.

4. **Smoke test on production** (each should return 200, footer shows the new version):

   - `/` and `/Landing`
   - `/Trips` and `/Trips/<next Saturday>`
   - `/DiveSites`, `/DiveSites?view=map`, `/WreckSites`
   - `/SiteDetails/spiegel-grove` (check the gallery loads WebP files from `img/sites/web/`)
   - `/Operators`, `/OperatorDetails/pura-vida-divers`
   - `/TripDetails/<any id from the board>`
   - `/DecoPlanner` and `/MyCalendar` while signed out (should render, not redirect)
   - `/MyDashboard` while signed in
   - `/sitemap.xml` (406 URLs, unchanged set)

5. **Redirects** (each should answer 301 to the target):

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

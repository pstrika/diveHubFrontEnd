# Shop portal and home shop

For Pablo. Everything that is in this feature, where it runs today, what it changed in the database, and what I still need from you.

Written 2026-11-19 against the `redesign` branch (12.37.0).

## Where it is right now

| Place | Version | What is there |
|---|---|---|
| Redesign slot, https://divehub-redesign.azurewebsites.net | 12.37.0 | The whole feature, working. Sign in with your normal account. |
| Production, divers-hub.com | 12.36.3 | The database tables only. No code uses them. The site behaves exactly as 12.36.2. |

The slot and production share one database. That is why the tables had to go to production first: 12.36.3 is a release with nothing in it except seven migrations. They ran in batch 92. I checked afterwards that all seven are recorded, the twelve tables exist, and `users` still has all 538 rows.

Because the database is shared, anything you do on the slot (invite a shop, edit a shop's details, set a home shop) is real data. Divers on production do not see any of it, because production's code does not read these tables yet.

## What it is, in one paragraph

Shops get a login and keep their own page right. We decide who manages which shop. Divers pick a home shop, and that shop's trips show first and highlighted on their dive list and on their dashboard. Each shop gets QR codes: a diver who scans one lands on the shop's page, and if they sign up, that shop becomes their home shop. The point is that shops own their listing and have a reason to send us their divers.

## The three roles

1. **Platform admin (you and me).** Decides who manages which shop. Sees which shops are claimed and which are not.
2. **Shop admin.** A normal user who has been given one or more shops. Sees a "Shop portal" link in the More menu. It does not change their `role_id`.
3. **Diver.** Has at most one home shop. Can change or remove it any time.

One person can be more than one of these.

## What a platform admin can do

**More > Admin Tools > Shop management** (`/admin/shop-access`). One page:

- **Shop adoption.** Every operator, whether anyone manages it, whether it has a storefront and prices, and how many divers call it home. Search and filter by status. "Invite link" on any row makes a one-time link for that shop.
- **Requests waiting on you.** Someone used "Is this your shop?" on an operator page. Approve or decline.
- **Invite link tied to an email address.** Same link, but only that address can accept it.
- **Make an existing user a shop admin.** Search by name or email.
- **Who manages what**, with a button to remove access.
- **Invite links not used yet**, with a button to switch one off.

Two shortcuts to the same thing:

- **User Management > edit a user** has a "Shops this user manages" card.
- **Any operator page** shows "Who manages this shop" to admins.

How an invite works: the link is shown once and only its hash is stored. It works one time and expires in 14 days. Opening it grants nothing. The person signs in or creates an account, then presses Accept.

## What a shop admin can do

**More > My shop > Shop portal** (`/operator-admin`). Tabs:

| Tab | What they edit |
|---|---|
| Overview | Counts and a setup checklist. |
| Shop details | Name, phone, email, website, About text, marina addresses, online waiver link, which fills they have (air, nitrox, trimix, oxygen), trip prices, Google review link. |
| Locations and prices | Storefront address and hours. Services with prices: fills, rentals, classes, guide service, equipment service, retail. One tap restamps every price as "confirmed today". |
| Boats | Name, type, capacity, tech capacity, builder, length, beam, speed, power for each boat we list. |
| QR codes | One code per place they put it (register, boat, flyer, website). Each has a printable sign, a downloadable sign, and the code alone for their own flyers. They can switch a code off. |
| Insights | See below. |

Still locked, shown as "coming soon": Promotions, Text sign-up offer, Competitions.

Not editable by the shop yet: their logo, boat photos, and adding or removing a boat. Those come to us through the chat button.

## What a diver gets

- **More > My home shop** (`/home-shop`). Pick, change or remove a home shop. Only shops that have added a storefront can be picked.
- **Dive list.** Their home shop's trips are listed first in each region under "Your home shop", and the cards are tinted. Nothing is hidden and filters work as before.
- **Dashboard.** A "My home shop" card: address, today's hours, Call, Directions, and the shop's next four trips.
- **Operator page.** A "Make this my home shop" button, and the shop's storefront, hours, services and prices.

Home shop is only switched on for two accounts right now: yours and mine. Everyone else sees nothing. The list is `early_access.home_shop` in `config/divehub.php`.

## QR codes

- A code is a short link: `/go/CODE`.
- A scan is counted (bots are flagged), then the visitor lands on the shop's page.
- A cookie remembers the scan for 90 days. The last shop scanned wins.
- If they then **create an account**, that shop becomes their home shop.
- If they **already have an account**, they are asked. It is never set silently.
- The sign is drawn in the browser by one small script I added to the repo: `public/assets/js/plugins/qrcode-generator.min.js` (qrcode-generator 1.4.4, MIT). No composer or npm change.

## Insights, first pass

Numbers we already had a record of, each labelled for exactly what it counts. Totals only. A shop never sees a diver's name.

- Divers who call them home, divers who have them as a favorite, average rating, divers who joined from their QR codes.
- Upcoming trips listed, upcoming trip spots saved to a calendar, how many of those the diver marked as booked.
- Saved trips by day of the week, and morning against afternoon.
- Last 30 days: visits to their page, times one of their trips was opened, waiver clicks, QR scans.

"Marked as booked" is the diver's own tick. The page says so. It is not called a booking.

## Google reviews

A shop can paste its Google review link and tick "Ask my divers for a Google review". It is **off unless they tick it**, because some shops already ask.

When it is on, your "How was your dive?" follow-up ends with one more button on the thank-you screen: "Review {shop} on Google". Two rules are built in:

- It is offered to every diver who finishes, whatever they rated. Never only the happy ones.
- Nothing is given in return for a review.

We count taps on the button. We cannot see whether a review was posted.

This is the only change to your feedback wizard: one block on the thank-you screen, and one new route that counts the tap and redirects.

## The one design rule that matters

**A shop's edits are never written into `operators` or `boats`.** They live in their own tables and are laid over the operator when its page is drawn.

I did it this way because something outside the app writes to those rows (`_status`, `_lastUpdate` and so on) and I do not know which columns the crawlers touch. Overlaying is safe either way.

What that means in practice:

- If a shop sets a value, that value shows. If they leave it empty, ours shows.
- The four fills are yes, no, or "not answered". Only "not answered" falls back to ours.
- A boat override stores only the fields that differ from ours. Put a value back to ours and the override row is deleted.
- Once a shop has a storefront, its address and hours replace ours on the page, in the map, and in the structured data search engines read.
- **Trade off:** once a shop sets a field, a change you make to that field directly in the database stops showing on their page.
- **Limit:** the overlay is applied on the operator page and the trip details page. Trip cards, the operators list and emails still read `operators` directly.

## What it counts from now on

A new table, `operator_events`, records one row each time a diver:

- opens a shop's page
- opens one of a shop's trips (with the trip's date, time, name and first site)
- follows a shop's waiver link
- makes a shop a favorite, or stops
- sets or removes a home shop
- taps "Review on Google"

Search engines and link previews are not counted. A shop's own admins, and platform admins, are not counted. It stores the signed in user's id so one diver's ten visits can be told from ten divers. Signed out visitors get a one-way hash that changes every day. This is collected now so the shop has history when we build more insights.

Not copied into it, because we already store them: trips saved to a calendar, marked booked, QR scans, sign-ups from a scan, ratings.

## Database changes

All additive. Seven migrations, already run on production (batch 92). Each is safe to run twice.

| Migration | What it adds |
|---|---|
| `2026_11_14_000000_create_operator_admin_tables` | `operator_admins` (who manages which shop), `operator_invites`, `operator_claims`, `operator_admin_audit` (every change, who made it) |
| `2026_11_14_000100_create_operator_listing_tables` | `operator_profiles` (the shop's own details), `operator_locations`, `operator_offerings` (services and prices) |
| `2026_11_14_000200_create_referral_tables` | `referral_codes`, `referral_scans`, `user_referrals` |
| `2026_11_14_000300_add_home_shop_to_users_table` | Three nullable columns on `users`: `home_operator_location_id` (indexed), `home_shop_set_at`, `home_shop_source` |
| `2026_11_19_000000_add_shop_facts_and_operator_events` | Fills, marinas, waiver link and trip prices on `operator_profiles`. New table `operator_events`. |
| `2026_11_19_000100_create_operator_boat_overrides_table` | `operator_boat_overrides` |
| `2026_11_19_000200_add_google_review_url_to_operator_profiles` | `google_review_url` and `google_review_ask` on `operator_profiles` |

Everything is on `mysql_trips` except the three `users` columns. No foreign keys onto existing tables. Nothing existing was renamed, changed or dropped.

## Where the code is

- **Controllers:** `ShopPortalController`, `ShopAccessAdminController`, `OperatorInviteController`, `HomeShopController`
- **Support:** `App\Support\ShopAccess` (who can manage what, grants, audit), `App\Support\HomeShop` (home shop, the overlay), `App\Support\ShopEvents` (the event log)
- **Middleware:** `operator.admin` (404 for anyone without a grant), `PendingShopLinks` (carries an invite or a scan through sign-in)
- **Views:** `resources/views/pages/shop/`, `resources/views/components/shop/`
- **Styles:** `public/assets/css/shop-portal.css`, plus a few rules at the end of `divershub.css`

## What I touched of yours

Small, and each one does nothing unless a shop has data:

- `OperatorController`: the operator page gets the overlay and counts a view. The waiver redirect uses the shop's own link when it has one. Favorite on and off is counted.
- `TripsController`, `TripBoard`, `trip-card`, `trips/_regions`: home shop trips first, with a label. A card without the label renders the same bytes as before.
- `TripDetailsController`: counts the view, applies the boat overlay.
- `MyDashboardController`, `Dashboard.blade.php`: the home shop card.
- `DiveFeedbackController`, the feedback wizard: the Google review button.
- `shell/menu.blade.php`: three menu links.
- User Management edit page: the shop admin card.
- Error pages (404 and the other six): redone in the current style, since the 404 still had the old logo.

I compared the pages for shops with no shop data before and after (Hydrotherapy, Rainbow Reef, the dive list). They are byte for byte the same apart from the version number. The frozen `/CalendarHydrotherapy` page is untouched.

Every page that existed before also works if the new tables are missing. I tested that by hiding the tables locally before the first deploy.

## How to try it on the slot

1. Sign in at https://divehub-redesign.azurewebsites.net
2. **More > Shop management.** Pick a shop and press "Admins", then make yourself its admin. Or press "Invite link" and open the link in a private window to see what a shop sees.
3. **More > Shop portal.** Add a storefront under Locations and prices. A shop cannot be picked as a home shop until it has one.
4. Add a service with a price. Edit Shop details. Open the public page and check what changed.
5. **QR codes.** Make a code. Print or download the sign. Scan it with your phone.
6. **More > My home shop.** Pick the shop. Open Dives and your dashboard.
7. **Insights.** Mostly zeros until real divers use it. Your own visits are not counted, so use a private window to see the numbers move.

Remember it is the real database. If you make test data on a real shop, remove it after.

## Not built

- **Promotions, text sign-up offer, competitions.** Locked buttons only.
- **Texting.** Needs the Twilio answers below and a legal read first.
- **Booking clicks.** The best "when are they booking" number, but tracking it means changing the Book link on every trip card.
- **Logo and boat photo uploads, adding a boat.**
- **The shop's own name on trip cards and in emails.** See the limit above.

## What I need from you

1. **Do the crawlers write to `operators` or `boats`, and which columns?** If they leave the descriptive columns alone, we could write shop edits straight in and drop the overlay.
2. **Home shop on `users`, or its own table?** It is three nullable columns today. It can move without losing anything.
3. **Are you ok with the feedback wizard change?** One block on the thank-you screen.
4. **Does the database have point in time restore?** I assumed so and did not check.
5. **Twilio:** what is our campaign registered for, and can shop promos get their own sender so a STOP on a promo does not end someone's dive reminders?
6. **Anything here you want changed before this goes to production?** The code is only on the slot. I will not merge it to main without your ok.

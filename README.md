# Divers Hub

Divers Hub is a Laravel web application for the scuba diving community. It brings together dive site information, trip scheduling, dive operators, weather/tide conditions, and dive-planning tools in a single admin-driven platform.

Live app: [divers-hub.com](https://divers-hub.com/)

## Features

- **Dive sites** — browse dive sites with photos, ratings, and comments; mark sites as visited or wished
- **Trips & calendars** — dedicated trip calendars for different trip types (e.g. Shark, Lobster, Wreck, Hydrotherapy), plus a personal calendar for booked trips
- **Operators** — directory of dive operators with details and waivers
- **Weather & conditions** — location-based weather, including AR and metric/imperial views
- **Dive planning tools** — decompression (deco) planner in metric and imperial units, and gas planning
- **User & role management** — admin dashboard for managing users, roles, categories, tags, and items
- **Authentication** — standard login/registration plus Google OAuth (Laravel Socialite)
- **Messaging** — in-app user messaging
- **Platform health** — admin view for monitoring platform status

## Tech Stack

- **Backend:** Laravel 9 (PHP ^7.3 | ^8.0)
- **Frontend:** Material Dashboard PRO (Bootstrap 5), compiled with Laravel Mix / webpack
- **Auth:** Laravel Sanctum, Laravel Socialite (Google)
- **Other:** Mailgun (mail), Google Analytics 4 event tracking, Laravel Sitemap

## Prerequisites

- PHP 7.3+ or 8.x
- Composer
- Node.js and npm
- MySQL (or another Laravel-supported database)

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/pstrika/diveHubFrontEnd.git
   cd diveHubFrontEnd
   ```
2. Install PHP dependencies:
   ```bash
   composer install
   ```
3. Install JS dependencies and build assets:
   ```bash
   npm install
   npm run dev
   ```
4. Copy the environment file and configure it (database, mail, OAuth, etc.):
   ```bash
   cp .env.example .env
   ```
5. Generate the application key:
   ```bash
   php artisan key:generate
   ```
6. Run database migrations and seeders:
   ```bash
   php artisan migrate --seed
   ```
   > **Note:** the migrations in `database/migrations` only cover `users`, `roles`, `categories`, `tags`, and `items` (plus Laravel's defaults). Tables backing the diving-specific features — sites, trips, operators, boats, events, messages, photos, site ratings/comments, weather locations, and visited/wished sites — were created directly against the database and are **not** captured as migrations. Running `migrate --seed` on a fresh database will not reproduce the full schema; see [Database Schema](#database-schema) below.
7. Create the storage symlink:
   ```bash
   php artisan storage:link
   ```
8. Serve the application:
   ```bash
   php artisan serve
   ```

## Database Schema

The app actually spans **two databases** on the same MySQL server, configured via two separate Laravel connections in [config/database.php](config/database.php):

| Connection | Database | Tables | Source of truth |
| --- | --- | --- | --- |
| `mysql` (default) | `laravelpro` | `users`, `roles`, `categories`, `tags`, `items`, `item_tag`, `password_resets`, `failed_jobs`, `personal_access_tokens` | Laravel migrations in `database/migrations` |
| `mysql_trips` | `divehub-schema` | `sites`, `sitecomments`, `siteratings`, `photos`, `trips`, `events`, `boats`, `operators`, `visitedsites`, `wishedsites`, `weatherlocations`, `weatherday`, `weatherhour`, `messages`, `products` | Created manually — **no migrations**, until now captured only as a live schema dump |

Structure-only dumps (no data) of both databases are checked in under `database/`:

- [`database/laravelpro-schema.sql`](database/laravelpro-schema.sql)
- [`database/divehub-schema.sql`](database/divehub-schema.sql)

These were generated with:

```bash
mysqldump --no-data --triggers --skip-comments --ssl-mode=REQUIRED \
  -h <host> -P <port> -u <user> -p <database> > database/<database>-schema.sql
```

**To reproduce the schema on a new server**, create both databases and load each dump into it:

```sql
CREATE DATABASE laravelpro;
CREATE DATABASE `divehub-schema`;
```

```bash
mysql -h <host> -u <user> -p laravelpro < database/laravelpro-schema.sql
mysql -h <host> -u <user> -p divehub-schema < database/divehub-schema.sql
```

Then run `php artisan migrate --seed` only to apply anything new on top of `laravelpro` — the migrations will no-op on tables that already exist from the dump (Laravel tracks applied migrations in the `migrations` table, which is included in the dump).

**Recommended next step:** the `divehub-schema` tables still aren't backed by real migrations, so future schema changes there can't be tracked or applied automatically. Consider reverse-engineering migrations from the dump (e.g. with [`kitloong/laravel-migrations-generator`](https://github.com/kitloong/laravel-migrations-generator)) so that database gets the same version-controlled, repeatable setup as `laravelpro`. Until then, re-run the `mysqldump` command above periodically to keep `database/divehub-schema.sql` in sync with production.

## Deployment

The app deploys automatically to an Azure Web App (`diveHub`) via the GitHub Actions workflow in [.github/workflows/main_divehub.yml](.github/workflows/main_divehub.yml) on every push to `main`. A legacy shell-based deploy script is also kept at [deploy.sh](deploy.sh) for reference.

## Project Structure

- `app/Http/Controllers` — request handlers (trips, sites, operators, weather, calendars, dive planning, user/role management, etc.)
- `app/Models` — Eloquent models (Site, Trip, Operator, Boat, Event, Weatherday, User, and more)
- `routes/web.php` — application routes
- `resources` — Blade views and frontend assets
- `public` — compiled assets and public entry point

## License

Built on top of the [Material Dashboard PRO Laravel](https://material-dashboard-pro-laravel.creative-tim.com) template by Creative Tim. Refer to that project's licensing terms for the underlying UI kit; application code is proprietary to Divers Hub unless stated otherwise.

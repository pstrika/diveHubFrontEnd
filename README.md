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
7. Create the storage symlink:
   ```bash
   php artisan storage:link
   ```
8. Serve the application:
   ```bash
   php artisan serve
   ```

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

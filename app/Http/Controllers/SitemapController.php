<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Operator;
use App\Models\Post;
use App\Models\Site;
use App\Models\WeatherLocation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SitemapController extends Controller
{
    public function index()
    {
        $xml = Cache::remember('sitemap.xml', now()->addHours(6), function () {
            $sitemap = Sitemap::create();

            foreach ($this->staticPages() as $routeName => $page) {
                $sitemap->add(
                    Url::create(route($routeName))
                        ->setChangeFrequency($page['changefreq'])
                        ->setPriority($page['priority'])
                );
            }

            $siteHasSlug = Schema::connection('mysql_trips')->hasColumn('sites', 'slug');

            Site::where('_hidden', '<>', 1)
                ->select(array_filter(['id', $siteHasSlug ? 'slug' : null, 'updated_at']))
                ->orderBy('id')
                ->chunk(200, function ($sites) use ($sitemap) {
                    foreach ($sites as $site) {
                        $sitemap->add(
                            Url::create(route('SiteDetails') . '/' . ($site->slug ?? $site->id))
                                ->setLastModificationDate($site->updated_at ?? now())
                                // Each site page embeds that site's upcoming trip
                                // calendar, which is crawler-refreshed daily, not
                                // the site's own static content (Pablo, 2026-09-19).
                                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                                ->setPriority(0.8)
                        );
                    }
                });

            $operatorHasSlug = Schema::connection('mysql_trips')->hasColumn('operators', 'slug');

            Operator::where('private', '<>', 1)
                ->select(array_filter(['id', $operatorHasSlug ? 'slug' : null]))
                ->orderBy('id')
                ->chunk(200, function ($operators) use ($sitemap) {
                    foreach ($operators as $operator) {
                        $sitemap->add(
                            Url::create(route('OperatorDetails', ['id' => $operator->slug ?? $operator->id]))
                                // Same reasoning as sites above - each operator
                                // page includes their own trip calendar, refreshed
                                // daily by the crawler (Pablo, 2026-09-19).
                                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                                ->setPriority(0.6)
                        );
                    }
                });

            Post::published()
                ->select('slug', 'updated_at')
                ->orderBy('id')
                ->chunk(200, function ($posts) use ($sitemap) {
                    foreach ($posts as $post) {
                        $sitemap->add(
                            Url::create(route('Blog.show', $post->slug))
                                ->setLastModificationDate($post->updated_at ?? now())
                                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                                ->setPriority(0.6)
                        );
                    }
                });

            // Public groups only (Pablo, 2026-10-09) - a private group's URL
            // just redirects a non-member away, so listing it would point
            // crawlers at a dead end.
            Group::where('is_public', true)
                ->select('slug', 'updated_at')
                ->orderBy('id')
                ->chunk(200, function ($groups) use ($sitemap) {
                    foreach ($groups as $group) {
                        $sitemap->add(
                            Url::create(route('Groups.show', ['group' => $group->slug]))
                                ->setLastModificationDate($group->updated_at ?? now())
                                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                                ->setPriority(0.5)
                        );
                    }
                });

            // US only (SEO audit, 2026-09-28, finding #4): WeatherController@show
            // itself only serves country = 'US' locations, falling back to Fort
            // Lauderdale for anything else (the Argentina forecast was retired in
            // release 10 - see routes/web.php) - listing those rows here just
            // pointed crawlers at pages that silently rendered a duplicate of
            // Fort Lauderdale's forecast. Clean hyphenated slug, not %20s.
            WeatherLocation::select('location', 'country')
                ->where('country', 'US')
                ->orderBy('location')
                ->chunk(200, function ($locations) use ($sitemap) {
                    foreach ($locations as $weatherLocation) {
                        $routeName = 'Weather';
                        $sitemap->add(
                            Url::create(route($routeName) . '/' . \Illuminate\Support\Str::slug($weatherLocation->location))
                                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                                ->setPriority(0.5)
                        );
                    }
                });

            return $sitemap->render();
        });

        return response($xml, 200)->header('Content-Type', 'text/xml');
    }

    /**
     * Every real, indexable URL on the site as a flat list of strings -
     * same pages as the sitemap above, just without the XML/priority
     * wrapping. Used by IndexNowSubmitAll to push the whole site in one
     * batch (Pablo, 2026-09-29); kept as its own pass over the same tables
     * rather than reusing the cached sitemap.xml, so it can run on demand
     * without waiting on or invalidating that 6-hour cache.
     */
    public function allUrls(): array
    {
        $urls = [];

        foreach (array_keys($this->staticPages()) as $routeName) {
            $urls[] = route($routeName);
        }

        $siteHasSlug = Schema::connection('mysql_trips')->hasColumn('sites', 'slug');
        Site::where('_hidden', '<>', 1)
            ->select(array_filter(['id', $siteHasSlug ? 'slug' : null]))
            ->orderBy('id')
            ->chunk(200, function ($sites) use (&$urls) {
                foreach ($sites as $site) {
                    $urls[] = route('SiteDetails') . '/' . ($site->slug ?? $site->id);
                }
            });

        $operatorHasSlug = Schema::connection('mysql_trips')->hasColumn('operators', 'slug');
        Operator::where('private', '<>', 1)
            ->select(array_filter(['id', $operatorHasSlug ? 'slug' : null]))
            ->orderBy('id')
            ->chunk(200, function ($operators) use (&$urls) {
                foreach ($operators as $operator) {
                    $urls[] = route('OperatorDetails', ['id' => $operator->slug ?? $operator->id]);
                }
            });

        Post::published()->select('slug')->orderBy('id')
            ->chunk(200, function ($posts) use (&$urls) {
                foreach ($posts as $post) {
                    $urls[] = route('Blog.show', $post->slug);
                }
            });

        Group::where('is_public', true)->select('slug')->orderBy('id')
            ->chunk(200, function ($groups) use (&$urls) {
                foreach ($groups as $group) {
                    $urls[] = route('Groups.show', ['group' => $group->slug]);
                }
            });

        WeatherLocation::select('location')->where('country', 'US')->orderBy('location')
            ->chunk(200, function ($locations) use (&$urls) {
                foreach ($locations as $weatherLocation) {
                    $urls[] = route('Weather') . '/' . \Illuminate\Support\Str::slug($weatherLocation->location);
                }
            });

        return $urls;
    }

    private function staticPages(): array
    {
        return [
            '/' => ['priority' => 1.0, 'changefreq' => Url::CHANGE_FREQUENCY_WEEKLY],
            'Trips' => ['priority' => 0.9, 'changefreq' => Url::CHANGE_FREQUENCY_DAILY],
            'DiveSites' => ['priority' => 0.9, 'changefreq' => Url::CHANGE_FREQUENCY_WEEKLY],
            'WreckSites' => ['priority' => 0.9, 'changefreq' => Url::CHANGE_FREQUENCY_WEEKLY],
            'BeachDiving' => ['priority' => 0.8, 'changefreq' => Url::CHANGE_FREQUENCY_WEEKLY],
            'Operators' => ['priority' => 0.8, 'changefreq' => Url::CHANGE_FREQUENCY_WEEKLY],
            // Public groups directory (Pablo, 2026-10-09): every individual
            // public group page is in the sitemap too, below.
            'Groups.public' => ['priority' => 0.6, 'changefreq' => Url::CHANGE_FREQUENCY_WEEKLY],
            // SEO content marketing (Pablo, 2026-09-17 - see routes/web.php);
            // was never added when the Blog shipped.
            'Blog' => ['priority' => 0.6, 'changefreq' => Url::CHANGE_FREQUENCY_WEEKLY],
            // Both real planning tools, not marketing pages - bumped from
            // the previous 0.5/missing (Pablo, 2026-09-19: "Deco Planner
            // and Best Gases are VERY important pages...a HUGE asset to
            // divers"). DecoPlannerImperial/DecoPlannerMetric are the same
            // content in different units, so only the plain DecoPlanner URL
            // goes in the sitemap - its own canonical tag is what tells
            // crawlers the other two aren't separate pages.
            'gasplanning' => ['priority' => 0.8, 'changefreq' => Url::CHANGE_FREQUENCY_WEEKLY],
            'DecoPlanner' => ['priority' => 0.8, 'changefreq' => Url::CHANGE_FREQUENCY_WEEKLY],
            // 'home' deliberately excluded (SEO audit, 2026-09-28): it now
            // just 301s to '/', so listing it would point crawlers at a
            // redirect instead of a real page.
            'Waivers' => ['priority' => 0.4, 'changefreq' => Url::CHANGE_FREQUENCY_WEEKLY],
            'CalendarHydrotherapy' => ['priority' => 0.4, 'changefreq' => Url::CHANGE_FREQUENCY_DAILY],
            'AboutUs' => ['priority' => 0.3, 'changefreq' => Url::CHANGE_FREQUENCY_WEEKLY],
            'PrivacyPolicy' => ['priority' => 0.1, 'changefreq' => Url::CHANGE_FREQUENCY_WEEKLY],
            'TermsOfUse' => ['priority' => 0.1, 'changefreq' => Url::CHANGE_FREQUENCY_WEEKLY],
        ];
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Operator;
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
                                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                                ->setPriority(0.8)
                        );
                    }
                });

            Operator::where('private', '<>', 1)
                ->select('id')
                ->orderBy('id')
                ->chunk(200, function ($operators) use ($sitemap) {
                    foreach ($operators as $operator) {
                        $sitemap->add(
                            Url::create(route('OperatorDetails', ['id' => $operator->id]))
                                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                                ->setPriority(0.6)
                        );
                    }
                });

            WeatherLocation::select('location', 'country')
                ->orderBy('location')
                ->chunk(200, function ($locations) use ($sitemap) {
                    foreach ($locations as $weatherLocation) {
                        $routeName = $weatherLocation->country === 'AR' ? 'WeatherAR' : 'Weather';
                        $sitemap->add(
                            Url::create(route($routeName) . '/' . rawurlencode($weatherLocation->location))
                                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                                ->setPriority(0.5)
                        );
                    }
                });

            return $sitemap->render();
        });

        return response($xml, 200)->header('Content-Type', 'text/xml');
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
            'gasplanning' => ['priority' => 0.5, 'changefreq' => Url::CHANGE_FREQUENCY_WEEKLY],
            'PrivacyPolicy' => ['priority' => 0.1, 'changefreq' => Url::CHANGE_FREQUENCY_WEEKLY],
            'TermsOfUse' => ['priority' => 0.1, 'changefreq' => Url::CHANGE_FREQUENCY_WEEKLY],
        ];
    }
}

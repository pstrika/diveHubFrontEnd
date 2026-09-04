<?php

namespace App\Http\Controllers;

use App\Models\Operator;
use App\Models\Site;
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

            foreach ($this->staticPages() as $routeName => $priority) {
                $sitemap->add(
                    Url::create(route($routeName))
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                        ->setPriority($priority)
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

            return $sitemap->render();
        });

        return response($xml, 200)->header('Content-Type', 'text/xml');
    }

    private function staticPages(): array
    {
        return [
            '/' => 1.0,
            'DiveSites' => 0.9,
            'WreckSites' => 0.9,
            'BeachDiving' => 0.8,
            'Operators' => 0.8,
            'Trips' => 0.7,
            'gasplanning' => 0.5,
            'PrivacyPolicy' => 0.1,
            'TermsOfUse' => 0.1,
        ];
    }
}

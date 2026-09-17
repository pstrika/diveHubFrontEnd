<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Models\Site;

/**
 * Visual design pass only (Pablo, 2026-09-17): mock post content, no
 * `posts` table or admin authoring screen yet. Real routes and views so the
 * look can be reviewed in the actual theme, not a throwaway static file.
 * Once the design is approved, the mock arrays below become a Post model
 * and this controller's queries - same shape either way, so nothing here
 * gets thrown away.
 *
 * Authorship (Pablo, 2026-09-17: "anybody that is user type Creator") maps
 * onto the role that already exists on User - App\Models\User::isCreator()
 * (role_id == 2) - never previously used anywhere in the app.
 */
class BlogController extends Controller
{
    /** @return array<int, array> Newest first. */
    private function posts(): array
    {
        return [
            [
                'slug' => 'top-5-wrecks-fort-lauderdale',
                'category' => 'Site Guides',
                'title' => 'Top 5 Wrecks to Dive in Fort Lauderdale',
                'excerpt' => "Fort Lauderdale's artificial reef program put more than eighty wrecks within reach of a single tank of air. Here are the five worth building a trip around.",
                'image' => 'assets/img/illustrations/site_wreck.webp',
                'author' => 'Maria Torres',
                'authorRole' => 'Creator',
                'publishedAt' => '2026-09-10',
                'readMinutes' => 7,
                'featured' => true,
            ],
            [
                'slug' => 'nitrox-vs-air',
                'category' => 'Gear & Tips',
                'title' => 'Nitrox vs. Air: Which Should You Choose for Your Next Dive?',
                'excerpt' => 'Longer bottom times, shorter surface intervals, a bit more cost per fill. Here is how to actually decide, dive by dive.',
                'image' => 'assets/img/illustrations/dive-site.webp',
                'author' => 'Jordan Reyes',
                'authorRole' => 'Creator',
                'publishedAt' => '2026-09-05',
                'readMinutes' => 5,
                'featured' => false,
            ],
            [
                'slug' => 'lobster-season-2026',
                'category' => 'News',
                'title' => 'Lobster Season 2026: What Divers Need to Know',
                'excerpt' => "Regular season opens August 6. Bag limits, measuring gauges, and the sites that get crowded fastest - plan around it, not into it.",
                'image' => 'assets/img/illustrations/dive-site.webp',
                'author' => 'Maria Torres',
                'authorRole' => 'Creator',
                'publishedAt' => '2026-08-28',
                'readMinutes' => 4,
                'featured' => false,
            ],
        ];
    }

    public function index()
    {
        $posts = $this->posts();
        $categories = collect($posts)->pluck('category')->unique()->values();

        $SEO = [
            'title' => 'Diving guides, gear tips and news - Divers Hub Blog',
            'desc' => 'Wreck and reef guides, gear advice and season updates for diving South Florida, written by real divers.',
            'keywords' => 'scuba diving blog, florida dive guides, wreck diving, dive gear tips',
            'canonical' => route('Blog'),
        ];

        return view('pages.Blog.Index', compact('posts', 'categories', 'SEO'));
    }

    public function show(string $slug)
    {
        $posts = $this->posts();
        $post = collect($posts)->firstWhere('slug', $slug);
        abort_unless($post, 404);

        $related = collect($posts)->where('slug', '!=', $slug)->take(2)->values();

        // Real sites, so the "internal links" this post exists to drive
        // actually go somewhere - the whole point of an SEO guide post.
        $diveSites = Site::whereIn('slug', ['spiegel-grove', 'lady-luck', 'hydro-atlantic', 'ancient-mariner', 'princess-britney'])
            ->select('id', 'name', 'slug', 'type', 'level', 'maxDepth', 'rate', 'votes')
            ->get();
        $photos = Photo::whereIn('siteId', $diveSites->pluck('id'))->get()->groupBy('siteId');
        foreach ($diveSites as $site) {
            $site->photoFile = $photos->get($site->id)?->first()?->file;
        }

        $SEO = [
            'title' => $post['title'] . ' - Divers Hub Blog',
            'desc' => $post['excerpt'],
            'canonical' => route('Blog.show', $post['slug']),
            'image' => asset($post['image']),
        ];

        return view('pages.Blog.Show', compact('post', 'related', 'diveSites', 'SEO'));
    }
}

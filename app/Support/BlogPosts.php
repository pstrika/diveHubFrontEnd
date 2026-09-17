<?php

namespace App\Support;

/**
 * Mock blog content (Pablo, 2026-09-17) - shared by BlogController and the
 * My Dashboard carousel so both read the exact same posts. No `posts` table
 * yet; this is the visual/data-shape design pass. Once a real Post model
 * exists, this class's public methods are the seam - callers don't change.
 *
 * Tags vs. category: category is the one section a post lives in (shown as
 * the loud eyebrow chip); tags are the topical/audience labels a post can
 * carry several of, meant for targeting later ("if tag is tech diving, we
 * can prioritize the show of this article to Tech Air and above certified
 * users" - Pablo, 2026-09-17). `minLevel` is the first real piece of that:
 * an App\Support\DiveLevel value that says who a post is really written
 * for, if anyone in particular.
 */
final class BlogPosts
{
    /** @return array<int, array> Newest first. */
    public static function all(): array
    {
        return [
            [
                'slug' => 'top-5-wrecks-fort-lauderdale',
                'category' => 'Site Guides',
                'tags' => ['Wreck Diving', 'Tech Diving'],
                'minLevel' => 2, // Technical Air - the deepest/most current-prone wreck on the list drives this
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
                'tags' => ['Gear', 'Beginner Friendly'],
                'minLevel' => null,
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
                'tags' => ['Lobster Season', 'Local News'],
                'minLevel' => null,
                'title' => 'Lobster Season 2026: What Divers Need to Know',
                'excerpt' => "Regular season opens August 6. Bag limits, measuring gauges, and the sites that get crowded fastest - plan around it, not into it.",
                'image' => 'assets/img/illustrations/dive-site.webp',
                'author' => 'Maria Torres',
                'authorRole' => 'Creator',
                'publishedAt' => '2026-08-28',
                'readMinutes' => 4,
                'featured' => false,
            ],
            [
                'slug' => 'drift-diving-101',
                'category' => 'Site Guides',
                'tags' => ['Drift Diving', 'Beginner Friendly'],
                'minLevel' => 0, // Open Water
                'title' => 'Drift Diving 101: Let the Current Do the Work',
                'excerpt' => "No fins required, just good buoyancy and a SMB. Here's what to expect the first time you dive one of the Gulf Stream's reef drifts.",
                'image' => 'assets/img/illustrations/dive-site.webp',
                'author' => 'Jordan Reyes',
                'authorRole' => 'Creator',
                'publishedAt' => '2026-08-20',
                'readMinutes' => 6,
                'featured' => false,
            ],
            [
                'slug' => 'best-night-dives-florida-keys',
                'category' => 'Site Guides',
                'tags' => ['Night Diving', 'Wreck Diving'],
                'minLevel' => 1, // Advanced Open Water
                'title' => 'Best Night Dives in the Florida Keys',
                'excerpt' => 'Bioluminescence, hunting octopuses and a reef that looks nothing like it did an hour before sunset. Five sites worth the late boat.',
                'image' => 'assets/img/illustrations/site_wreck.webp',
                'author' => 'Maria Torres',
                'authorRole' => 'Creator',
                'publishedAt' => '2026-08-12',
                'readMinutes' => 6,
                'featured' => false,
            ],
        ];
    }

    public static function find(string $slug): ?array
    {
        return collect(self::all())->firstWhere('slug', $slug);
    }

    /**
     * Posts ranked for one viewer: matching their certification level
     * first (a post with no minLevel always qualifies), then newest first
     * within each group. This is the mock demonstration of the targeting
     * Pablo described - real ranking (and real engagement signals) is a
     * later problem once posts are a real model.
     *
     * @return array<int, array>
     */
    public static function forViewer(?int $certLevel, int $limit = 5): array
    {
        $posts = collect(self::all());

        $matches = $posts->filter(fn ($p) => $p['minLevel'] === null || $certLevel === null || $certLevel >= $p['minLevel']);
        $matchSlugs = $matches->pluck('slug');
        $rest = $posts->reject(fn ($p) => $matchSlugs->contains($p['slug']));

        return $matches->concat($rest)->take($limit)->values()->all();
    }
}

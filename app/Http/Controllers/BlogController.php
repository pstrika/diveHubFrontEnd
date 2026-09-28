<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Models\Post;

/**
 * Public-facing blog (Pablo, 2026-09-17/18). Authoring lives in
 * BlogAdminController; this only ever reads published posts.
 *
 * Authorship ("anybody that is user type Creator", then "both Creator and
 * Admins can create articles") is User::isCreator() (role_id == 2, never
 * previously used anywhere in the app before this) OR User::isAdmin()
 * (role_id == 1) - see App\Policies\PostPolicy.
 */
class BlogController extends Controller
{
    public function index()
    {
        $posts = Post::published()->latest('published_at')->get();
        $categories = $posts->pluck('category')->unique()->values();

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
        $post = Post::published()->where('slug', $slug)->first();
        abort_unless($post, 404);

        $related = Post::published()->where('slug', '!=', $slug)->latest('published_at')->take(2)->get();

        // Real sites, so the "internal links" a guide post exists to drive
        // actually go somewhere - not every post has these ("not in all
        // cases we will have rankings" - Pablo, 2026-09-17).
        $rankedSites = $post->rankedSites();
        if ($rankedSites->isNotEmpty()) {
            $covers = Photo::coversFor($rankedSites->pluck('site.id'));
            foreach ($rankedSites as $entry) {
                $entry['site']->photoFile = $covers->get($entry['site']->id)?->file;
            }
        }

        // 8 posts (SEO audit, 2026-09-28 re-audit) run past 60 chars once
        // " - Divers Hub Blog" is appended; drop the suffix first, same as
        // every other title fallback this pass. 4 of those have a title
        // long enough on its own that dropping the suffix isn't enough -
        // those get a shorter meta title here (the on-page H1 is untouched).
        static $shortBlogTitles = [
            'lionfish-invasion-what-divers-can-do' => 'The Lionfish Invasion: What Divers Can Do',
            'open-circuit-vs-closed-circuit' => 'Open Circuit vs. Closed Circuit Diving',
            'seasonal-diving-conditions-in-the-florida-keys-a-month-by-month-guide-for-local-divers' => 'Florida Keys Diving Conditions by Month',
            'lionfish-with-open-sores' => 'Lionfish With Open Sores: What Hunters Should Know',
        ];
        $metaTitleBase = $shortBlogTitles[$post->slug] ?? $post->title;
        $metaTitle = $metaTitleBase . ' - Divers Hub Blog';
        if (mb_strlen($metaTitle) > 60) {
            $metaTitle = $metaTitleBase;
        }

        $SEO = [
            'title' => $metaTitle,
            'desc' => $post->excerpt,
            'canonical' => route('Blog.show', $post->slug),
            'image' => $post->cover_image ? asset($post->cover_image) : null,
            // Blog posts are articles, not generic pages (SEO audit, 2026-09-28,
            // finding #3) - og:type defaults to "website" everywhere else.
            'ogType' => 'article',
        ];

        return view('pages.Blog.Show', compact('post', 'related', 'rankedSites', 'SEO'));
    }
}

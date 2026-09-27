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
            $photos = Photo::whereIn('siteId', $rankedSites->pluck('site.id'))->get()->groupBy('siteId');
            foreach ($rankedSites as $entry) {
                $entry['site']->photoFile = $photos->get($entry['site']->id)?->first()?->file;
            }
        }

        $SEO = [
            'title' => $post->title . ' - Divers Hub Blog',
            'desc' => $post->excerpt,
            'canonical' => route('Blog.show', $post->slug),
            'image' => $post->cover_image ? asset($post->cover_image) : null,
        ];

        return view('pages.Blog.Show', compact('post', 'related', 'rankedSites', 'SEO'));
    }
}

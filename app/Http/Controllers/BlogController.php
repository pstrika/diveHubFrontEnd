<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Models\Site;
use App\Support\BlogPosts;

/**
 * Visual design pass only (Pablo, 2026-09-17): mock post content lives in
 * App\Support\BlogPosts, no `posts` table or admin authoring screen yet.
 * Real routes and views so the look can be reviewed in the actual theme,
 * not a throwaway static file. Once the design is approved, BlogPosts'
 * arrays become a Post model and its static methods become queries - same
 * shape either way, so nothing here gets thrown away.
 *
 * Authorship (Pablo, 2026-09-17: "anybody that is user type Creator", then
 * "both Creator and Admins can create articles") is
 * User::isCreator() (role_id == 2, never previously used anywhere in the
 * app) OR User::isAdmin() (role_id == 1) - the same pairing Public Groups'
 * one-per-admin exemption already checks, so the future authoring screen's
 * gate is `$user->isCreator() || $user->isAdmin()`.
 */
class BlogController extends Controller
{
    public function index()
    {
        $posts = BlogPosts::all();
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
        $post = BlogPosts::find($slug);
        abort_unless($post, 404);

        $related = collect(BlogPosts::all())->where('slug', '!=', $slug)->take(2)->values();

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

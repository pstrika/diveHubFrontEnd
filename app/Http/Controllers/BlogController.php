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

        return $this->render($post);
    }

    /**
     * Shareable draft preview, reached via a signed URL generated from the
     * admin edit screen (BlogAdminController::previewLink()) - so a draft
     * can be handed to an outside reviewer with no Divers Hub login
     * (Pablo, 2026-10-04: "how do I share a blog in draft with someone").
     * Deliberately skips the published() scope the public route above
     * uses; the `signed` middleware (routes/web.php) is what gates this
     * instead, and the signature carries its own expiry.
     */
    public function previewSigned(string $slug)
    {
        $post = Post::where('slug', $slug)->first();
        abort_unless($post, 404);

        return $this->render($post, sharedDraftPreview: true);
    }

    private function render(Post $post, bool $sharedDraftPreview = false)
    {
        $related = Post::published()->where('slug', '!=', $post->slug)->latest('published_at')->take(2)->get();

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

        // Long titles run past the ~60 chars Google shows once " - Divers
        // Hub Blog" is appended; drop the suffix first. A post whose title is
        // too long even without it carries a shorter search title of its
        // own, set in the editor (posts.seo_title; SEO audit, 2026-10-24 -
        // it replaced a hard-coded list). The on-page H1 is untouched.
        $metaTitleBase = trim((string) $post->seo_title) !== '' ? trim($post->seo_title) : $post->title;
        $metaTitle = $metaTitleBase . ' - Divers Hub Blog';
        if (mb_strlen($metaTitle) > 60) {
            $metaTitle = $metaTitleBase;
        }

        $SEO = [
            'title' => $metaTitle,
            // Excerpts are written for the page and can run past the ~160
            // chars a search snippet shows (6 posts did); whole sentences
            // only (SEO spot check, 2026-10-12).
            'desc' => \App\Support\MetaText::description($post->excerpt),
            'canonical' => route('Blog.show', $post->slug),
            // Covers are often WebP, which some link previewers skip; serve a JPEG copy.
            'image' => $post->cover_image ? (\App\Support\ShareImage::jpeg($post->cover_image) ?? asset($post->cover_image)) : null,
            // Blog posts are articles, not generic pages (SEO audit, 2026-09-28,
            // finding #3) - og:type defaults to "website" everywhere else.
            'ogType' => 'article',
        ];
        // A shared draft has no business being crawled/indexed even though
        // the route itself has no auth wall.
        if ($sharedDraftPreview) {
            $SEO['robots'] = 'noindex, nofollow';
        }

        return view('pages.Blog.Show', [
            'post' => $post,
            'related' => $related,
            'rankedSites' => $rankedSites,
            'SEO' => $SEO,
            'preview' => $sharedDraftPreview,
            'sharedDraftPreview' => $sharedDraftPreview,
        ]);
    }
}

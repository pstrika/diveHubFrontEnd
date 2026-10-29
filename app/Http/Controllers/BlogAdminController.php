<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Site;
use App\Services\IndexNowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

/**
 * Creator/Admin authoring screen (Pablo, 2026-09-17/18). Since 2026-10-29
 * Creators no longer publish directly (Pablo: "submit for review and admins
 * approve"): a Creator saves a draft or submits it for review ('pending'),
 * every Admin is notified, and an Admin publishes it or sends it back to
 * draft with a note. An article that is already live stays live when its
 * Creator edits it; Admins are told about the edit. Admins publish their
 * own articles directly and can remove anyone's; Creators only their own -
 * see App\Policies\PostPolicy.
 */
class BlogAdminController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Post::class);
        $user = auth()->user();

        $posts = $user->isAdmin()
            ? Post::with('author')->latest()->get()
            : Post::with('author')->where('author_id', $user->id)->latest()->get();

        return view('pages.Blog.Manage.Index', compact('posts'));
    }

    public function create()
    {
        $this->authorize('create', Post::class);

        return view('pages.Blog.Manage.Form', [
            'post' => new Post(),
            'categories' => $this->categories(),
            'initialRelatedSites' => [],
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Post::class);

        $post = new Post();
        $data = $this->validated($request, $post);
        $data['author_id'] = auth()->id();
        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $this->storeCoverImage($request);
        }
        if ($data['status'] === 'published') {
            $data['published_at'] = now();
        }

        $post = Post::create($data);
        if ($post->status === 'pending') self::notifyAdminsSubmitted($post);

        // New content, not just a metadata tweak - worth telling Bing/Yandex
        // about right away rather than waiting for their next crawl or the
        // next indexnow:submit-all (Pablo, 2026-09-29).
        if ($post->status === 'published') {
            IndexNowService::submitOne(route('Blog.show', $post->slug));
        }

        return redirect()->route('Blog.manage.index')->with('success', 'Article saved.');
    }

    public function edit(Post $post)
    {
        $this->authorize('update', $post);

        $initialRelatedSites = $post->rankedSites()
            ->map(fn ($entry) => ['id' => $entry['site']->id, 'name' => $entry['site']->name, 'note' => $entry['note']])
            ->values();

        return view('pages.Blog.Manage.Form', [
            'post' => $post,
            'categories' => $this->categories(),
            'initialRelatedSites' => $initialRelatedSites,
        ]);
    }

    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        $data = $this->validated($request, $post);
        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $this->storeCoverImage($request);
        }
        if ($data['status'] === 'published' && !$post->published_at) {
            $data['published_at'] = now();
        }

        $wasStatus = $post->status;
        if (($data['status'] ?? null) !== 'draft') $data['review_note'] = null; // resubmitted: the old note is done
        $post->update($data);
        if ($post->status === 'pending' && $wasStatus !== 'pending') self::notifyAdminsSubmitted($post);
        if ($post->status === 'published' && $wasStatus === 'published' && !$request->user()->isAdmin()) {
            self::notifyAdmins($post, 'Published article edited', $post->author?->name . ' edited the live article "' . $post->title . '".');
        }

        // Same reasoning as store() above - covers both a draft going live
        // and a live post's content changing (title, body, etc).
        if ($post->status === 'published') {
            IndexNowService::submitOne(route('Blog.show', $post->slug));
        }

        return redirect()->route('Blog.manage.index')->with('success', 'Article saved.');
    }

    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        $post->delete();

        return redirect()->route('Blog.manage.index')->with('success', 'Article removed.');
    }

    /**
     * Renders the public article page from whatever is currently in the form,
     * including unsaved edits - nothing is written to the database. The form
     * posts here with target="_blank" (Form.blade.php) so it opens as a real
     * new tab rather than needing a JS-side render.
     */
    public function preview(Request $request)
    {
        $this->authorize('create', Post::class);

        $tags = array_values(array_filter(array_map('trim', explode(',', (string) $request->input('tags', '')))));
        $relatedSitesRaw = json_decode((string) $request->input('related_sites', ''), true) ?: [];

        $post = new Post([
            'title' => (string) $request->input('title', 'Untitled article'),
            'category' => (string) $request->input('category', ''),
            'tags' => $tags,
            'min_level' => $request->filled('min_level') ? (int) $request->input('min_level') : null,
            'excerpt' => (string) $request->input('excerpt', ''),
            'body' => (string) $request->input('body', ''),
            'cover_focus' => in_array($request->input('cover_focus'), ['top', 'center', 'bottom'], true) ? $request->input('cover_focus') : 'center',
            'related_sites' => $relatedSitesRaw,
            'status' => 'draft',
        ]);
        $post->exists = false;
        $post->author_id = $request->user()->id;
        $post->setRelation('author', $request->user());
        $post->published_at = now();

        $rankedSites = $post->rankedSites();
        if ($rankedSites->isNotEmpty()) {
            $covers = \App\Models\Photo::coversFor($rankedSites->pluck('site.id'));
            foreach ($rankedSites as $entry) {
                $entry['site']->photoFile = $covers->get($entry['site']->id)?->file;
            }
        }

        // A picked-but-unsaved cover comes in as a data: URL (Form.blade.php
        // reads the file with FileReader before submitting) so the preview
        // can show it without an upload; an existing cover just reuses its
        // real asset path, same as the live page.
        $previewCoverUrl = $request->input('cover_data_url') ?: null;

        $SEO = ['title' => $post->title . ' - Divers Hub Blog', 'desc' => $post->excerpt];

        return view('pages.Blog.Show', [
            'post' => $post,
            'related' => collect(),
            'rankedSites' => $rankedSites,
            'SEO' => $SEO,
            'preview' => true,
            'previewCoverUrl' => $previewCoverUrl,
        ]);
    }

    /**
     * A signed, expiring link to the SAVED article (draft or published),
     * for handing to an outside reviewer with no Divers Hub login - unlike
     * preview() above, this is the real persisted post, reachable without
     * re-submitting the form (Pablo, 2026-10-04: "how do I share a blog in
     * draft with someone that I want to review"). Same signed-route
     * pattern as Newsletter's unsubscribe link (NewsletterController).
     */
    public function previewLink(Post $post)
    {
        $this->authorize('update', $post);

        $url = URL::signedRoute('Blog.previewSigned', ['slug' => $post->slug], now()->addDays(7));

        return response()->json(['url' => $url, 'expiresInDays' => 7]);
    }

    /** Small JSON search behind the "related dive sites" picker in the form - name only, real Site records. */
    public function searchSites(Request $request)
    {
        $this->authorize('create', Post::class);

        $q = trim((string) $request->input('q', ''));
        if ($q === '') {
            return response()->json([]);
        }

        $sites = Site::where('name', 'LIKE', "%{$q}%")
            ->select('id', 'name', 'type', 'level')
            ->orderBy('name')
            ->take(8)
            ->get();

        return response()->json($sites);
    }

    /**
     * Known categories plus whatever's actually in use, so the field can
     * autocomplete without being a rigid enum. Kept deliberately small
     * (Pablo, 2026-09-20: "let's keep the categories of the articles
     * reduced") - site guides and itinerary guides both fall under Guides
     * rather than splitting hairs over what kind of guide a post is.
     */
    private function categories(): array
    {
        $inUse = Post::query()->whereNotNull('category')->distinct()->pluck('category');

        return $inUse->merge(['Guides', 'News', 'Gear & Tips', 'Conservation'])->unique()->sort()->values()->all();
    }

    private function storeCoverImage(Request $request): string
    {
        $file = $request->file('cover_image');
        $filename = time() . '_' . $file->getClientOriginalName();
        Storage::disk('siteAssets')->putFileAs('img/blog', $file, $filename);

        return 'assets/img/blog/' . $filename;
    }

    /** POST Blog/manage/{post}/approve - an Admin publishes a submitted article. */
    public function approve(Request $request, Post $post)
    {
        abort_unless($request->user()->isAdmin(), 403);
        $post->status = 'published';
        $post->review_note = null;
        $post->published_at ??= now();
        $post->save();
        IndexNowService::submitOne(route('Blog.show', $post->slug));
        if ($post->author_id && $post->author_id !== $request->user()->id) {
            \App\Services\NotificationService::notify([$post->author_id], 'Your article is published', '"' . $post->title . '" is now live on the Divers Hub blog.', route('Blog.show', $post->slug), null, $request->user()->id);
        }
        return redirect()->route('Blog.manage.index')->with('success', 'Published "' . $post->title . '".');
    }

    /** POST Blog/manage/{post}/send-back - an Admin returns a submitted article to its author as a draft, with a note. */
    public function sendBack(Request $request, Post $post)
    {
        abort_unless($request->user()->isAdmin(), 403);
        $v = $request->validate(['note' => 'nullable|string|max:2000']);
        $note = trim((string) ($v['note'] ?? ''));
        $post->status = 'draft';
        $post->review_note = $note !== '' ? $note : null;
        $post->save();
        if ($post->author_id && $post->author_id !== $request->user()->id) {
            \App\Services\NotificationService::notify([$post->author_id], 'Your article needs changes', '"' . $post->title . '" was sent back to you' . ($note !== '' ? ': ' . $note : '.'), route('Blog.manage.edit', $post), null, $request->user()->id);
        }
        return redirect()->route('Blog.manage.index')->with('success', 'Sent "' . $post->title . '" back to its author.');
    }

    private static function notifyAdminsSubmitted(Post $post): void
    {
        self::notifyAdmins($post, 'Article waiting for review', ($post->author?->name ?? 'A contributor') . ' submitted "' . $post->title . '" for review.');
    }

    /** In-app + push to every Admin except whoever made the change. */
    private static function notifyAdmins(Post $post, string $subject, string $body): void
    {
        $admins = \App\Models\User::where('role_id', 1)->pluck('id');
        \App\Services\NotificationService::notify($admins, $subject, $body, route('Blog.manage.edit', $post), auth()->id(), auth()->id());
    }

    private function validated(Request $request, Post $post): array
    {
        $isAdmin = $request->user()->isAdmin();

        $rules = [
            'title' => 'required|string|max:255',
            'seo_title' => 'nullable|string|max:70',
            'category' => 'required|string|max:100',
            'tags' => 'nullable|string|max:500',
            'min_level' => 'nullable|integer|min:0|max:4',
            'excerpt' => 'nullable|string|max:500',
            'body' => 'nullable|string',
            // 30720 KB (30M) matches public/.user.ini's upload_max_filesize -
            // no point allowing more here than PHP itself will accept.
            'cover_image' => 'nullable|image|max:30720',
            'cover_focus' => 'nullable|in:top,center,bottom',
            'related_sites' => 'nullable|string',
            // Creators: draft or submit for review. A Creator's article that
            // is already live may stay published (editing it doesn't take it
            // down) but a Creator can never publish one that isn't.
            'status' => $isAdmin ? 'required|in:draft,pending,published'
                : ('required|in:draft,pending' . ($post->exists && $post->status === 'published' ? ',published' : '')),
        ];

        // Only Admins can set/change the article's URL (Pablo, 2026-09-18:
        // "if you're a creator, I don't want them to be able to edit the...
        // slug field. If you're an admin, yes"). The form doesn't even
        // render the field for a Creator, but that's a UI nicety, not the
        // enforcement - a crafted request could still post a slug, so it's
        // ignored server-side for anyone who isn't an Admin, same as any
        // other authorization check in this app.
        if ($isAdmin) {
            $rules['slug'] = 'required|string|max:255|alpha_dash:ascii|unique:posts,slug,' . ($post->id ?: 'NULL') . ',id';
        }

        $data = $request->validate($rules);

        $data['tags'] = array_values(array_filter(array_map('trim', explode(',', $data['tags'] ?? ''))));
        $data['related_sites'] = json_decode($data['related_sites'] ?? '', true) ?: [];
        $data['cover_focus'] = $data['cover_focus'] ?? 'center';
        // The "Not set" option posts min_level as '' - nullable|integer lets
        // an empty string past validation (it just skips the integer check),
        // but the column itself rejects '' outright, so every post left at
        // "suits every level" would fail to save with a SQL error. Found
        // while testing the slug lockdown above, not caused by it.
        $data['min_level'] = $request->filled('min_level') ? (int) $data['min_level'] : null;
        unset($data['cover_image']); // handled separately in store()/update() - keeps the UploadedFile out of a plain update() call

        if (!$isAdmin) {
            // Editing: keep the slug exactly as it is. Creating: the server
            // picks one from the title - the same alpha_dash/unique shape an
            // Admin's manual slug would have to satisfy, just computed here
            // instead of validated from input.
            $data['slug'] = $post->exists ? $post->slug : Post::uniqueSlugFrom($data['title']);
        }

        return $data;
    }
}

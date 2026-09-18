<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Creator/Admin authoring screen (Pablo, 2026-09-17/18). Creators publish
 * directly - no pre-approval queue ("I'm expecting creators to create
 * reasonable content") - draft is just a save-for-later, not a moderation
 * gate. Admins can remove anyone's post; Creators only their own - see
 * App\Policies\PostPolicy.
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

        Post::create($data);

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

        $post->update($data);

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
            'related_sites' => $relatedSitesRaw,
            'status' => 'draft',
        ]);
        $post->exists = false;
        $post->author_id = $request->user()->id;
        $post->setRelation('author', $request->user());
        $post->published_at = now();

        $rankedSites = $post->rankedSites();
        if ($rankedSites->isNotEmpty()) {
            $photos = \App\Models\Photo::whereIn('siteId', $rankedSites->pluck('site.id'))->get()->groupBy('siteId');
            foreach ($rankedSites as $entry) {
                $entry['site']->photoFile = $photos->get($entry['site']->id)?->first()?->file;
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

    /** Known categories plus whatever's actually in use, so the field can autocomplete without being a rigid enum. */
    private function categories(): array
    {
        $inUse = Post::query()->whereNotNull('category')->distinct()->pluck('category');

        return $inUse->merge(['Site Guides', 'Gear & Tips', 'News'])->unique()->sort()->values()->all();
    }

    private function storeCoverImage(Request $request): string
    {
        $file = $request->file('cover_image');
        $filename = time() . '_' . $file->getClientOriginalName();
        Storage::disk('siteAssets')->putFileAs('img/blog', $file, $filename);

        return 'assets/img/blog/' . $filename;
    }

    private function validated(Request $request, Post $post): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|alpha_dash:ascii|unique:posts,slug,' . ($post->id ?: 'NULL') . ',id',
            'category' => 'required|string|max:100',
            'tags' => 'nullable|string|max:500',
            'min_level' => 'nullable|integer|min:0|max:4',
            'excerpt' => 'nullable|string|max:500',
            'body' => 'nullable|string',
            'cover_image' => 'nullable|image|max:8192',
            'related_sites' => 'nullable|string',
            'status' => 'required|in:draft,published',
        ]);

        $data['tags'] = array_values(array_filter(array_map('trim', explode(',', $data['tags'] ?? ''))));
        $data['related_sites'] = json_decode($data['related_sites'] ?? '', true) ?: [];
        unset($data['cover_image']); // handled separately in store()/update() - keeps the UploadedFile out of a plain update() call

        return $data;
    }
}

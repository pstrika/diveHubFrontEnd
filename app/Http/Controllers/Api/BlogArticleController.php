<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Site;
use App\Support\QuillMarkdown;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * The blog API (Pablo, 2026-09-20: "call it from anywhere with the right
 * JSON") - a Sanctum-authenticated route so an article can be published
 * without going through the admin form or a one-off script. The caller
 * writes body_markdown, a small deliberate Markdown subset
 * (App\Support\QuillMarkdown), instead of hand-assembling Quill Delta JSON.
 *
 * Same authorization as the admin form (PostPolicy) - a Creator can only
 * create/update their own posts, an Admin can touch anyone's. author_id is
 * always the token holder, never taken from the request body.
 */
class BlogArticleController extends Controller
{
    public function store(Request $request)
    {
        $this->authorize('create', Post::class);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|alpha_dash:ascii',
            'category' => 'required|string|max:100',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'min_level' => 'nullable|integer|min:0|max:4',
            'excerpt' => 'nullable|string|max:500',
            'body_markdown' => 'required|string',
            'cover_image_url' => 'nullable|url',
            'cover_focus' => 'nullable|in:top,center,bottom',
            'related_sites' => 'nullable|array',
            'related_sites.*.site_id' => 'nullable|integer',
            'related_sites.*.site_name' => 'nullable|string',
            'related_sites.*.note' => 'nullable|string',
            'status' => 'nullable|in:draft,published',
            'published_at' => 'nullable|date',
        ]);

        $user = $request->user();
        $status = $data['status'] ?? 'published';

        $post = null;
        if (!empty($data['slug'])) {
            $existing = Post::where('slug', $data['slug'])->first();
            if ($existing) {
                $this->authorize('update', $existing);
                $post = $existing;
            }
        }
        $isNew = $post === null;
        $post ??= new Post();

        $post->title = $data['title'];
        $post->slug = $post->exists ? $post->slug : ($data['slug'] ?? Post::uniqueSlugFrom($data['title']));
        $post->category = $data['category'];
        $post->tags = $data['tags'] ?? [];
        $post->min_level = $data['min_level'] ?? null;
        $post->excerpt = $data['excerpt'] ?? '';
        $post->body = QuillMarkdown::toDelta($data['body_markdown']);
        $post->cover_focus = $data['cover_focus'] ?? 'center';
        $post->related_sites = $this->resolveRelatedSites($data['related_sites'] ?? []);
        $post->author_id = $isNew ? $user->id : $post->author_id;
        $post->status = $status;
        $post->published_at = $status === 'published'
            ? (isset($data['published_at']) ? \Carbon\Carbon::parse($data['published_at']) : ($post->published_at ?? now()))
            : $post->published_at;

        if (!empty($data['cover_image_url'])) {
            $stored = $this->fetchCoverImage($data['cover_image_url']);
            if ($stored) {
                $post->cover_image = $stored;
            }
        }

        $post->save();

        return response()->json([
            'id' => $post->id,
            'slug' => $post->slug,
            'url' => route('Blog.show', $post->slug),
            'title' => $post->title,
            'status' => $post->status,
            'cover_image' => $post->cover_image ? asset($post->cover_image) : null,
            'created' => $isNew,
        ], $isNew ? 201 : 200);
    }

    /**
     * Accepts either a real site_id or a site_name to look up - an LLM
     * calling this endpoint knows a site's name, not its internal id.
     * Exact name match first, then a LIKE fallback only when it resolves
     * to exactly one row; anything that doesn't resolve is dropped rather
     * than failing the whole request (Post::rankedSites() already treats a
     * dangling site_id as a normal, expected state).
     */
    private function resolveRelatedSites(array $entries): array
    {
        $resolved = [];

        foreach ($entries as $entry) {
            $site = null;

            if (!empty($entry['site_id'])) {
                $site = Site::find($entry['site_id']);
            } elseif (!empty($entry['site_name'])) {
                $site = Site::whereRaw('LOWER(name) = ?', [strtolower($entry['site_name'])])->first();
                if (!$site) {
                    $matches = Site::where('name', 'LIKE', '%' . $entry['site_name'] . '%')->limit(2)->get();
                    $site = $matches->count() === 1 ? $matches->first() : null;
                }
            }

            if ($site) {
                $resolved[] = ['site_id' => $site->id, 'note' => $entry['note'] ?? null];
            }
        }

        return $resolved;
    }

    /**
     * Downloads a cover image the same way an admin-form upload lands
     * (BlogAdminController::storeCoverImage) - same disk, same folder,
     * same public/assets/img/blog/<timestamp>_<name> convention - so
     * either path produces an identical stored path.
     */
    private function fetchCoverImage(string $url): ?string
    {
        try {
            $response = Http::timeout(15)->get($url);
        } catch (\Throwable $e) {
            return null;
        }

        if (!$response->successful()) {
            return null;
        }

        $contentType = $response->header('Content-Type');
        $extension = match (true) {
            str_contains((string) $contentType, 'png') => 'png',
            str_contains((string) $contentType, 'webp') => 'webp',
            str_contains((string) $contentType, 'gif') => 'gif',
            default => 'jpg',
        };

        $name = Str::slug(pathinfo(parse_url($url, PHP_URL_PATH) ?? 'cover', PATHINFO_FILENAME)) ?: 'cover';
        $filename = time() . '_' . $name . '.' . $extension;

        Storage::disk('siteAssets')->put('img/blog/' . $filename, $response->body());

        return 'assets/img/blog/' . $filename;
    }
}

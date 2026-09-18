<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A blog post. Body is Quill Delta JSON, rendered client-side the same way
 * as sites.desc/route/typicalConditions/history (see edit-site.blade.php) -
 * same editor, same storage shape, same render pattern, nothing new.
 *
 * related_sites is a JSON array of {site_id, note} in rank order - the
 * site (Site model, mysql_trips connection - see the migration's docblock
 * for why this isn't a real FK) plus a short editorial line about why it
 * made the list ("Spiegel Grove - a 510-foot Navy landing ship..."), not
 * just a bare id list. Empty/null is a normal, expected state - "not in
 * all cases we will have rankings, meaning the site citations may not
 * always be used" (Pablo, 2026-09-17).
 */
class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'category',
        'tags',
        'min_level',
        'excerpt',
        'body',
        'cover_image',
        'cover_focus',
        'related_sites',
        'author_id',
        'status',
        'published_at',
    ];

    protected $casts = [
        'tags' => 'array',
        'related_sites' => 'array',
        'published_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Published posts ranked for one viewer: matching their certification
     * level first (a post with no min_level always qualifies), then newest
     * first within each group - the mock demonstration Pablo asked for
     * ("if tag is tech diving, we can prioritize the show of this article
     * to Tech Air and above certified users") is now the real thing.
     *
     * @return \Illuminate\Support\Collection<int, Post>
     */
    public static function forViewer(?int $certLevel, int $limit = 5)
    {
        $posts = self::published()->latest('published_at')->get();

        $matches = $posts->filter(fn ($p) => $p->min_level === null || $certLevel === null || $certLevel >= $p->min_level);
        $matchIds = $matches->pluck('id');
        $rest = $posts->reject(fn ($p) => $matchIds->contains($p->id));

        return $matches->concat($rest)->take($limit)->values();
    }

    /**
     * relatedSites() entries with a real Site model attached, in the same
     * rank order they were saved - a plain whereIn() comes back in
     * whatever order the database feels like, so this re-sorts to match.
     * Queries the mysql_trips connection directly since Post itself lives
     * on mysql (see migration docblock). Entries whose site was deleted
     * since this post was written are dropped rather than shown broken.
     *
     * @return \Illuminate\Support\Collection<int, array{site: Site, note: ?string}>
     */
    public function rankedSites()
    {
        $entries = collect($this->related_sites ?? []);
        if ($entries->isEmpty()) {
            return collect();
        }

        $ids = $entries->pluck('site_id')->map(fn ($id) => (int) $id);
        $sites = Site::whereIn('id', $ids)->get()->keyBy('id');

        return $entries
            ->map(fn ($entry) => ['site' => $sites->get((int) $entry['site_id']), 'note' => $entry['note'] ?? null])
            ->filter(fn ($entry) => $entry['site'] !== null)
            ->values();
    }

    /** Rough estimate from the Delta's plain text, same convention the mock posts used with hand-picked readMinutes. */
    public function getReadMinutesAttribute(): int
    {
        $text = '';
        $delta = json_decode($this->body ?? '', true);
        if (is_array($delta['ops'] ?? null)) {
            foreach ($delta['ops'] as $op) {
                if (is_string($op['insert'] ?? null)) {
                    $text .= ' ' . $op['insert'];
                }
            }
        }

        return max(1, (int) ceil(str_word_count($text) / 200));
    }
}

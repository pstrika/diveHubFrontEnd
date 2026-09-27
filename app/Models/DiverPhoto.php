<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * A registered diver's own picture of a site, pending admin review before
 * it shows publicly (Pablo, 2026-09-23: "we also need a way to approve
 * pictures"). Files are stored and resized exactly like admin-uploaded
 * site photos (App\Support\SitePhoto) - same public/assets/img/sites
 * directory, just named with a 'diver_' prefix so the two never collide.
 */
class DiverPhoto extends Model
{
    protected $connection = 'mysql_trips';
    protected $table = 'diver_photos';

    protected $fillable = [
        'siteId',
        'userId',
        'file',
        'status',
        'reviewedBy',
        'reviewedAt',
    ];

    protected $casts = [
        'reviewedAt' => 'datetime',
    ];

    public const STATUSES = ['pending', 'approved', 'rejected'];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'siteId', 'id');
    }

    /**
     * Cross-connection like SiteComment::user()/SiteRating::user() - User
     * lives on the default `mysql` connection, this model on `mysql_trips`,
     * Eloquent just issues two queries.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'userId', 'id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewedBy', 'id');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /** Removes the original, both WebP copies, and the row - the whole point of "remove" not just hiding a status. */
    public function deleteWithFile(): void
    {
        $stem = \App\Support\SitePhoto::stem($this->file);
        Storage::disk('siteAssets')->delete([
            'img/sites/' . $this->file,
            'img/sites/web/' . $stem . '.webp',
            'img/sites/web/thumb/' . $stem . '.webp',
        ]);
        $this->delete();
    }
}

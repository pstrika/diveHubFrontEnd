<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

/**
 * A photo on a logbook dive (Pablo, 2026-10-17). Private: the files live in
 * storage/app/dive-photos/{user_id}/ (not public/assets) as two WebP copies,
 * {uuid}.webp (1600px) and thumb/{uuid}.webp (480px) - the camera original
 * isn't kept, same as diver site photos. Served only to the owner by
 * DivePhotoController::file(). "Share to the site page" makes a DiverPhoto
 * copy that goes through the normal admin review.
 */
class DivePhoto extends Model
{
    public const MAX_PER_DIVE = 20;
    public const DIR = 'dive-photos';

    protected $fillable = ['uuid', 'user_id', 'dive_id', 'width', 'height', 'caption', 'taken_at', 'position', 'shared_diver_photo_id'];

    protected $casts = ['taken_at' => 'datetime'];

    protected static function booted()
    {
        static::creating(fn (DivePhoto $p) => $p->uuid ??= (string) Str::uuid());
    }

    public function dive(): BelongsTo
    {
        return $this->belongsTo(Dive::class);
    }

    public function species(): BelongsToMany
    {
        return $this->belongsToMany(Species::class, 'dive_photo_species')->withTimestamps();
    }

    /** Absolute path of a size ('web' or 'thumb'). */
    public function path(string $size = 'web'): string
    {
        return storage_path('app/' . self::DIR . '/' . $this->user_id . '/' . ($size === 'thumb' ? 'thumb/' : '') . $this->uuid . '.webp');
    }

    public function url(string $size = 'web'): string
    {
        return route('DivePhotos.file', [$this->uuid, $size === 'thumb' ? 'thumb' : 'web']);
    }

    /** The site-page copy, when shared (mysql_trips connection). */
    public function sharedCopy(): ?DiverPhoto
    {
        return $this->shared_diver_photo_id ? DiverPhoto::find($this->shared_diver_photo_id) : null;
    }

    public function deleteWithFiles(): void
    {
        @unlink($this->path('web'));
        @unlink($this->path('thumb'));
        $this->species()->detach();
        $this->delete();
    }

    /** What the API returns for a photo. */
    public function toApi(): array
    {
        $shared = $this->sharedCopy();
        return [
            'uuid' => $this->uuid,
            'dive_uuid' => $this->dive?->uuid,
            'url' => $this->url('web'),
            'thumb_url' => $this->url('thumb'),
            'width' => $this->width,
            'height' => $this->height,
            'caption' => $this->caption,
            'taken_at' => $this->taken_at?->toIso8601String(),
            'position' => $this->position,
            'shared' => $shared ? $shared->status : null, // null | pending | approved | rejected
            'species' => $this->species->pluck('uuid')->values(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

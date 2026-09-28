<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class Photo extends Model
{
    use HasFactory;
    protected $connection = 'mysql_trips'; // Use the new connection for this model
    protected $table = 'photos';

    protected $fillable = [
        'id',
        '_token', // Add _token to the fillable property
        'created_at',
        'updated_at',
        'file',
        'desc',
        'credit',
        'siteId',
    ];

    public function deletePhoto() {
        Storage::disk('siteAssets')->delete('img/sites/' . $this->file);
        $this->delete();
    }

    /**
     * Order a site's photos with its admin-picked hero first (sites.heroPhotoId,
     * Pablo 2026-09-28), then oldest first - the order every "first photo"
     * lookup has always used, so a site with no hero picked looks the same.
     */
    public function scopeHeroFirst($query)
    {
        return $query->select('photos.*')
            ->leftJoin('sites', 'sites.id', '=', 'photos.siteId')
            ->orderByRaw('COALESCE(photos.id = sites.heroPhotoId, 0) DESC')
            ->orderBy('photos.id');
    }

    /**
     * The cover photo of each of these sites: its hero, or its oldest photo
     * when none is picked. One query for a whole card grid.
     *
     * @return \Illuminate\Support\Collection<int, Photo> siteId => Photo
     */
    public static function coversFor($siteIds)
    {
        return static::heroFirst()->whereIn('photos.siteId', collect($siteIds)->all())->get()
            ->groupBy('siteId')->map->first();
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'pics', 'id');
    }
}

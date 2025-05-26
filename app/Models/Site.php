<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Site extends Model
{
    use HasFactory;

    protected $connection = 'mysql_trips'; // Use the new connection for this model
    protected $table = 'sites';

    protected $fillable = [
        'name',
        '_token', // Add _token to the fillable property
        'avgDepth',
        'maxDepth',
        'type',
        'tag',
        'desc',
        'route',
        'pics',
        'videos',
        'externalLink',
        'level',
        'visitingOperators',
        'typicalConditions',
        'access',
        'history',
        'rate',
        'relief',
        'wreckData',
        'location',
        'gpsLat',
        'gpsLon',
        'votes',
        // Other fields...
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class, 'id', 'siteId')->withDefault([
            'id' => 0,
            'maxDepth' => '60',
            'level' => 2,
        ]);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class, 'id', 'pics');
    }

    public function reviews(): HasMany {
        return $this->hasMany(SiteComment::class, 'siteid', 'id');
    }

    public function locationLong(): HasOne
    {
        return $this->hasOne(WeatherLocation::class, 'short', 'location');
    }

    public function getPlainTextDesc()
    {
        // Assuming 'content' is the Quill delta column in your database
        $delta = json_decode($this->desc);
        $plainText = '';

        foreach ($delta->ops as $op) {
            if (isset($op->insert) && is_string($op->insert)) {
                $plainText .= $op->insert;
            }
        }

        return $plainText;
    }

}

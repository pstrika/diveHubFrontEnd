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

    /**
     * The desc/route/typicalConditions/history columns store a Quill
     * "Delta" (rich text as JSON ops), rendered to HTML entirely
     * client-side today - a search crawler that doesn't execute that JS
     * sees empty containers (Pablo, 2026-09-16 SEO review: confirmed on
     * ~370 live site pages). Extracting plain text here lets a controller
     * server-render real words into the page immediately; the existing
     * client-side Quill script still overwrites that with the fully
     * formatted HTML afterward for real visitors, unchanged.
     */
    private function deltaToPlainText(?string $json): string
    {
        if (!$json) {
            return '';
        }
        $delta = json_decode($json);
        if (!$delta || !isset($delta->ops) || !is_array($delta->ops)) {
            return '';
        }
        $plainText = '';
        foreach ($delta->ops as $op) {
            if (isset($op->insert) && is_string($op->insert)) {
                $plainText .= $op->insert;
            }
        }
        return $plainText;
    }

    public function getPlainTextDesc()
    {
        return $this->deltaToPlainText($this->desc);
    }

    public function getPlainTextRoute()
    {
        return $this->deltaToPlainText($this->route);
    }

    public function getPlainTextTypicalConditions()
    {
        return $this->deltaToPlainText($this->typicalConditions);
    }

    public function getPlainTextHistory()
    {
        return $this->deltaToPlainText($this->history);
    }

}

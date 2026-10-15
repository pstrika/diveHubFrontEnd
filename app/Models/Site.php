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

    /**
     * gpsLat/gpsLon are stored as degrees + decimal minutes strings
     * ("26° 19.500' N", "80° 03.043' W"), not decimal degrees - confirmed
     * against all 379 populated rows (2026-10-14, for the dive logbook's
     * sync API: DiveSyncController needs plain decimal numbers so clients
     * don't each have to parse this format). The regex tolerates the two
     * real outliers found in that scan (a trailing comma, a missing `'`
     * before the hemisphere letter) rather than requiring the exact
     * well-formed pattern.
     */
    private function parseDms(?string $raw): ?float
    {
        if (!$raw || !preg_match('/(\d{1,3})\D+(\d{1,2}(?:\.\d+)?)\D*?([NSEW])/u', $raw, $m)) {
            return null;
        }
        $decimal = (float) $m[1] + ((float) $m[2] / 60);
        return in_array($m[3], ['S', 'W']) ? -$decimal : $decimal;
    }

    public function decimalLatitude(): ?float
    {
        return $this->parseDms($this->gpsLat);
    }

    public function decimalLongitude(): ?float
    {
        return $this->parseDms($this->gpsLon);
    }

    public function publicUrl(): string
    {
        return route('SiteDetails') . '/' . ($this->slug ?? $this->id);
    }
}

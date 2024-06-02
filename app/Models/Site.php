<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}

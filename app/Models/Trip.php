<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class Trip extends Model
{
    use HasFactory;

    protected $connection = 'mysql_trips'; // Use the new connection for this model
    protected $table = 'trips';

    public function weatherday()
    {
        return $this->hasOne(Weatherday::class, 'date', 'date');

    }

    public function site(): HasMany
    {
        // Explode the comma-separated site IDs and retrieve the related sites
        //return Site::whereIn('id', explode(',', $this->siteId))->get();
        return $this->hasMany(Site::class, 'id', 'siteId');
    }
}
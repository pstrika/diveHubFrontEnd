<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Log;
use App\Models\Event;
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

    public function operator(): HasOne
    {
        // Explode the comma-separated site IDs and retrieve the related sites
        //return Site::whereIn('id', explode(',', $this->siteId))->get();
        return $this->hasOne(Operator::class, 'id', 'operatorId');
    }

    public static function tripInEvent($event) {
        Log::debug("tripInEvent received event = " . str($event));

        $trip = Trip::where([
            [ 'date', '=', $event->date],
            [ 'departureTime', '=', $event->time],
            [ 'operatorId', '=', $event->operatorId],
            [ 'tripName', '=', $event->tripName]
        ])->get();

        Log::debug("trip info: " . $trip);
        if(count($trip) == 1)
            return $trip[0];
        else
            return 0;
        
    }
}
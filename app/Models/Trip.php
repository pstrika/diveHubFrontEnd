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
        return self::findByComposite($event->date, $event->time, $event->operatorId, $event->tripName);
    }

    /**
     * Re-resolves a GroupDive's snapshot back to today's live Trip row, the
     * same way tripInEvent() does for the personal calendar - trip ids churn
     * daily (re-scraped), so group_dives stores the identifying fields
     * instead of a raw tripId.
     */
    public static function tripInGroupDive($groupDive) {
        return self::findByComposite($groupDive->date, $groupDive->time, $groupDive->operatorId, $groupDive->tripName);
    }

    /**
     * Trip ids are not durable (re-scraped daily with new auto-increment
     * ids), so anything that needs to remember "this trip" long-term stores
     * this composite key instead and re-resolves the live row via this
     * lookup when needed.
     */
    private static function findByComposite($date, $time, $operatorId, $tripName) {
        $trip = Trip::where([
            [ 'date', '=', $date],
            [ 'departureTime', '=', $time],
            [ 'operatorId', '=', $operatorId],
            [ 'tripName', '=', $tripName]
        ])->get();

        if(count($trip) == 1)
            return $trip[0];
        else
            return 0;

    }
}
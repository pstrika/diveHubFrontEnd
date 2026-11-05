<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One measured leg between two trek wrecks. See WreckTrekData. */
class WreckTrekLeg extends Model
{
    protected $fillable = ['from_site_id', 'to_site_id', 'heading_deg', 'distance_ft', 'notes', 'verified_by', 'verified_on', 'gps_basis'];

    /** The two wrecks' GPS as it is now, to record what a leg was measured against. */
    public static function basisFor(int $fromId, int $toId): ?string
    {
        $sites = Site::whereIn('id', [$fromId, $toId])->get(['id', 'gpsLat', 'gpsLon'])->keyBy('id');
        if (!isset($sites[$fromId], $sites[$toId])) return null;

        return $sites[$fromId]->gpsLat . '|' . $sites[$fromId]->gpsLon . '|' . $sites[$toId]->gpsLat . '|' . $sites[$toId]->gpsLon;
    }

    protected $casts = ['verified_on' => 'date'];
}

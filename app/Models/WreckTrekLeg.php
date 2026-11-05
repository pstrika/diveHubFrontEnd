<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One measured leg between two trek wrecks. See WreckTrekData. */
class WreckTrekLeg extends Model
{
    protected $fillable = ['from_site_id', 'to_site_id', 'heading_deg', 'distance_ft', 'notes', 'verified_by', 'verified_on'];

    protected $casts = ['verified_on' => 'date'];
}

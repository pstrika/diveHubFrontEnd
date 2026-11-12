<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One scrubber entry a diver made by hand (Zach, 2026-11-11): dive time
 * added, the total corrected, a rotate, or a repack. The rest of the
 * scrubber record is read from saved checklists; see App\Support\CcrScrubber.
 */
class CcrScrubberEvent extends Model
{
    protected $table = 'ccr_scrubber_events';

    protected $fillable = ['unit_id', 'user_id', 'type', 'minutes', 'occurred_at'];

    protected $casts = ['occurred_at' => 'datetime', 'minutes' => 'integer'];
}

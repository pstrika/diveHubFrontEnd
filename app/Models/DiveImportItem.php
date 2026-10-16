<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One dive inside a staged import; `payload` is already in the `dives` row shape. */
class DiveImportItem extends Model
{
    protected $fillable = [
        'dive_import_id', 'position', 'started_at', 'duration_seconds', 'max_depth_m',
        'computer_model', 'computer_serial', 'fingerprint', 'site_key', 'has_profile',
        'duplicate_kind', 'duplicate_of_dive_id', 'duplicate_of_item_id', 'decision',
        'imported_dive_id', 'payload',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'has_profile' => 'boolean',
        'payload' => 'array',
    ];

    public function import(): BelongsTo
    {
        return $this->belongsTo(DiveImport::class, 'dive_import_id');
    }

    public function duplicateOfDive(): BelongsTo
    {
        return $this->belongsTo(Dive::class, 'duplicate_of_dive_id')->withTrashed();
    }
}

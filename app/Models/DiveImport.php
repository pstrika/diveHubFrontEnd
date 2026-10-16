<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** One staged logbook file (UDDF) going through the import wizard - see LogbookImportController. */
class DiveImport extends Model
{
    protected $fillable = ['user_id', 'filename', 'generator', 'status', 'summary', 'sites'];

    protected $casts = [
        'summary' => 'array',
        'sites' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(DiveImportItem::class)->orderBy('position');
    }
}

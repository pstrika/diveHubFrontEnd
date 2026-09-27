<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A diver's saved Decompression Dive Planner inputs ("Save Plan"), so a plan can be regenerated later. */
class DecoPlan extends Model
{
    use HasFactory;

    protected $table = 'deco_plans';

    protected $fillable = [
        'user_id',
        'label',
        'mode',
        'inputs',
    ];

    protected $casts = [
        'inputs' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

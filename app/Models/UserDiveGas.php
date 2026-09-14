<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A diver's saved custom gas mix (O2/He%) from the Decompression Dive Planner's "My Gases" pill. */
class UserDiveGas extends Model
{
    use HasFactory;

    protected $table = 'user_dive_gases';

    protected $fillable = [
        'user_id',
        'o2',
        'he',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

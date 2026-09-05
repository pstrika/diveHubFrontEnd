<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupDiveRsvp extends Model
{
    use HasFactory;

    protected $connection = 'mysql_trips';
    protected $table = 'group_dive_rsvps';

    protected $fillable = [
        'group_dive_id',
        'user_id',
    ];

    public function dive(): BelongsTo
    {
        return $this->belongsTo(GroupDive::class, 'group_dive_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

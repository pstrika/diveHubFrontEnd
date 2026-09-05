<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroupDive extends Model
{
    use HasFactory;

    protected $connection = 'mysql_trips';
    protected $table = 'group_dives';

    protected $fillable = [
        'group_id',
        'created_by',
        'operatorId',
        'date',
        'time',
        'tripName',
        'notes',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function rsvps(): HasMany
    {
        return $this->hasMany(GroupDiveRsvp::class, 'group_dive_id');
    }

    public function isGoing($userId): bool
    {
        return $this->rsvps()->where('user_id', $userId)->exists();
    }
}

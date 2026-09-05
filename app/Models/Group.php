<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    use HasFactory;

    protected $connection = 'mysql_trips';
    protected $table = 'groups';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'banner',
        'avatar',
        'calendar_token',
        'reminders_enabled',
        'created_by',
    ];

    protected $casts = [
        'reminders_enabled' => 'boolean',
    ];

    public function favoriteOperators(): BelongsToMany
    {
        return $this->belongsToMany(Operator::class, 'group_favorite_operators', 'group_id', 'operator_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(GroupMember::class, 'group_id');
    }

    public function activeMembers(): HasMany
    {
        return $this->members()->where('status', 'active');
    }

    public function dives(): HasMany
    {
        return $this->hasMany(GroupDive::class, 'group_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(GroupMessage::class, 'group_id');
    }

    public function isMember($userId): bool
    {
        return $this->members()->where('user_id', $userId)->where('status', 'active')->exists();
    }

    public function isAdmin($userId): bool
    {
        return $this->members()->where('user_id', $userId)->where('status', 'active')->where('role', 'admin')->exists();
    }

    public function ensureCalendarToken(): string
    {
        if (!$this->calendar_token) {
            $this->calendar_token = bin2hex(random_bytes(24));
            $this->save();
        }

        return $this->calendar_token;
    }
}

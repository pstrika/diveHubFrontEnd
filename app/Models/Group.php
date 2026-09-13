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
        'digest_enabled',
        'last_digest_sent_at',
        'notifications_muted',
        'allow_members_add_dives',
        'fb_page_id',
        'fb_page_name',
        'fb_page_access_token',
        'fb_connected_by',
        'fb_connected_at',
        'fb_auto_post',
        'created_by',
    ];

    protected $casts = [
        'reminders_enabled' => 'boolean',
        'digest_enabled' => 'boolean',
        'last_digest_sent_at' => 'datetime',
        'notifications_muted' => 'boolean',
        'allow_members_add_dives' => 'boolean',
        'fb_page_access_token' => 'encrypted',
        'fb_connected_at' => 'datetime',
        'fb_auto_post' => 'boolean',
    ];

    /**
     * Whether this user may add dives (real or custom) to the group
     * calendar - admins always can; regular members only when the group
     * allows it.
     */
    public function canAddDives($userId): bool
    {
        return $this->isAdmin($userId) || $this->allow_members_add_dives;
    }

    public function isFacebookConnected(): bool
    {
        return !empty($this->fb_page_id) && !empty($this->fb_page_access_token);
    }

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

    /**
     * True if this member won't get a group notification right now -
     * either an admin muted the whole group, or they muted it just for
     * themselves via the bell toggle (Pablo, 2026-09-14: "Group Admins
     * can mute ALL notifications for everybody... but users should be
     * able to mute for them[selves]").
     */
    public function isMemberMuted(int $userId): bool
    {
        if ($this->notifications_muted) {
            return true;
        }

        return (bool) $this->members()->where('user_id', $userId)->where('status', 'active')->value('notifications_muted');
    }

    /**
     * Active members eligible for a notification right now - empty if
     * the group itself is muted, otherwise everyone except whoever muted
     * it individually. Used anywhere a batch of group members needs to
     * be notified in one pass (dive reminders' email/SMS/WhatsApp loops).
     */
    public function unmutedActiveMembers(): \Illuminate\Support\Collection
    {
        if ($this->notifications_muted) {
            return collect();
        }

        return $this->activeMembers()->with('user')->get()->reject(fn ($m) => $m->notifications_muted)->values();
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

<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable=['name', 'email', 'password', 'role_id','password_confirmation', 'phone', 'location', 'old_password','picture', 'certLevel', 'favOperators', 'favLocations', 'showLevel', 'prefersLocation', 'firstDayOfWeek', 'google_id', 'show_visited', 'deco_unit', 'calendar_token', 'last_seen_at', 'whatsapp_notifications', 'email_consent_at', 'sms_consent_at', 'whatsapp_consent_at', 'phone_verified_at', 'pending_phone', 'phone_verification_code_hash', 'phone_verification_sent_at', 'deco_gf_low', 'deco_gf_high', 'deco_setpoint', 'pinch_zoom_enabled', 'wizard_completed_at', 'google_avatar_url', 'nav_slot_1', 'nav_slot_2', 'logbook_auto_merge', 'calendar_month_on_tablet'];

    protected $connection = 'mysql';
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'phone_verification_sent_at' => 'datetime',
        'newsletter_subscribed' => 'boolean',
    ];

    /**
     * Online means "seen within the last 3 minutes" - matches the heartbeat
     * interval set by UpdateLastSeen middleware.
     */
    public function isOnline(): bool
    {
        return $this->last_seen_at && $this->last_seen_at->gt(now()->subMinutes(3));
    }
    
    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = bcrypt($password);
    }

   /**
     * Check if the user has admin role
     */
    public function isAdmin()
    {
        return $this->role_id == 1;
    }

    /**
     * Check if the user has creator role
     */
    public function isCreator()
    {
        return $this->role_id == 2;
    }

    /**
     * Check if the user has user role
     */
    public function isMember()
    {
        return $this->role_id == 3;
    }

     /**
     * Check if the user has user role
     */
    public function isNotGuest()
    {
        return ($this->role_id != 4);
    }

     /**
     * Check if the user has user role
     */
    public function isGuest()
    {
        return $this->role_id == 4;
    }

    public function role(){

        return $this->belongsTo(Role::class);
    }

    /** Custom gas mixes saved from the Decompression Dive Planner's "My Gases" pill, up to 10 per diver. */
    public function diveGases()
    {
        return $this->hasMany(UserDiveGas::class);
    }

    /**
     * The bell's number: unread in the Inbox and Groups folders. Admin
     * notices (reviews waiting, inbound messages) are left out on purpose
     * (Zach, 2026-11-12): on an admin's account they were most of the
     * count and buried everything personal. They have their own folder
     * and unreadAdminNotifications().
     */
    public function unreadNotifications() {
        return Message::liveFor($this->id)->unread()
            ->where(fn ($w) => $w->whereNull('kind')->orWhere('kind', '<>', Message::KIND_ADMIN))
            ->count();
    }

    public function unreadAdminNotifications() {
        return Message::liveFor($this->id)->unread()->folder('admin')->count();
    }

    /**
     * Same as unreadNotifications() but scoped to Groups-folder messages
     * only (group_id set) - the Groups tab badge used to reuse the whole
     * inbox+groups total, which read wrong once the two folders actually
     * diverged (Pablo, 2026-09-17: "the message badge for groups...we want
     * here the message count for groups, not the entire unread inbox").
     */
    public function unreadGroupNotifications() {
        return Message::liveFor($this->id)->unread()->folder('groups')->count();
    }

    public function ensureCalendarToken()
    {
        if (!$this->calendar_token) {
            $this->calendar_token = bin2hex(random_bytes(24));
            $this->save();
        }
 
        return $this->calendar_token;
    }
}

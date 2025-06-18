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
    protected $fillable=['name', 'email', 'password', 'role_id','password_confirmation', 'phone', 'location', 'old_password','picture', 'certLevel', 'favOperators', 'favLocations', 'showLevel', 'prefersLocation', 'firstDayOfWeek', 'google_id', 'show_visited', 'deco_unit'];

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
    ];
    
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

    public function unreadNotifications() {
        return Message::where('userId', $this->id)->where('deleted', 0)->where('read', 0)->count();
    }
}

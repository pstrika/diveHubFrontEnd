<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Which code brought a diver in. Shop portal, 2026-11-13: see the 2026_11_14 migrations. */
class UserReferral extends Model
{
    protected $connection = 'mysql_trips';
    protected $table = 'user_referrals';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = ['attributed_at' => 'datetime'];
}

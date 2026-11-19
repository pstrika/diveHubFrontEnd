<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** The code behind /go/{code}. Shop portal, 2026-11-13: see the 2026_11_14 migrations. */
class ReferralCode extends Model
{
    protected $connection = 'mysql_trips';
    protected $table = 'referral_codes';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $casts = ['is_active' => 'boolean'];
}

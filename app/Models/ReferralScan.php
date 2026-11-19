<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One scan of a referral code. Never holds a raw IP. Shop portal, 2026-11-13: see the 2026_11_14 migrations. */
class ReferralScan extends Model
{
    protected $connection = 'mysql_trips';
    protected $table = 'referral_scans';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = ['scanned_at' => 'datetime', 'is_bot' => 'boolean'];
}

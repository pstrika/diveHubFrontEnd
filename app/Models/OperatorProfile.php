<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** The shop's own name and contact details, preferred over operators. Shop portal, 2026-11-13: see the 2026_11_14 migrations. */
class OperatorProfile extends Model
{
    protected $connection = 'mysql_trips';
    protected $table = 'operator_profiles';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $casts = ['trip_prices' => 'array', 'google_review_ask' => 'boolean'];
}

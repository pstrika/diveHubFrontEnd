<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A member asking to manage a shop; a platform admin decides. Shop portal, 2026-11-13: see the 2026_11_14 migrations. */
class OperatorClaim extends Model
{
    protected $connection = 'mysql_trips';
    protected $table = 'operator_claims';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $casts = ['decided_at' => 'datetime'];
}

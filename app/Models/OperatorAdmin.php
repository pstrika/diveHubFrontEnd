<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A grant: this user may manage this operator. Shop portal, 2026-11-13: see the 2026_11_14 migrations. */
class OperatorAdmin extends Model
{
    protected $connection = 'mysql_trips';
    protected $table = 'operator_admins';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $casts = ['revoked_at' => 'datetime'];
}

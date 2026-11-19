<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A service or price at one location. Shop portal, 2026-11-13: see the 2026_11_14 migrations. */
class OperatorOffering extends Model
{
    protected $connection = 'mysql_trips';
    protected $table = 'operator_offerings';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $casts = ['attributes' => 'array', 'price_confirmed_at' => 'datetime', 'is_active' => 'boolean'];
}

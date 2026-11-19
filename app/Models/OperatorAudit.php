<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One row per grant, revoke and shop edit. Shop portal, 2026-11-13: see the 2026_11_14 migrations. */
class OperatorAudit extends Model
{
    protected $connection = 'mysql_trips';
    protected $table = 'operator_admin_audit';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = ['changes' => 'array', 'created_at' => 'datetime'];
}

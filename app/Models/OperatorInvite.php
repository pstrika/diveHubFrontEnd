<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A single use link that ends in a grant. Holds the token hash only. Shop portal, 2026-11-13: see the 2026_11_14 migrations. */
class OperatorInvite extends Model
{
    protected $connection = 'mysql_trips';
    protected $table = 'operator_invites';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $casts = ['expires_at' => 'datetime', 'accepted_at' => 'datetime', 'revoked_at' => 'datetime'];
}

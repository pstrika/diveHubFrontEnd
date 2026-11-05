<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A predefined wreck trek. Its wrecks are the sites with this wreck_trek_id. */
class WreckTrek extends Model
{
    protected $fillable = ['slug', 'name', 'sort', 'location', 'notes'];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Weatherday extends Model
{
    use HasFactory;

    protected $connection = 'mysql_trips'; // Use the new connection for this model
    protected $table = 'weatherday';

    public function trip()
    {
        // This defines the inverse of the relationship
        return $this->belongsTo(Trip::class, 'date', 'date');
    }
}

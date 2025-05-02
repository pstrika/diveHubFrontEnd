<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteRating extends Model
{
    use HasFactory;
    protected $connection = 'mysql_trips'; // Use the new connection for this model
    protected $table = 'siteratings';

    protected $fillable = [
        'userId',
        'siteId', // Add _token to the fillable property
        'starRating',
        'comment',
        'timeStamp',
    ];
}

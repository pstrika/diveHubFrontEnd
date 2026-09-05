<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperatorRating extends Model
{
    use HasFactory;
    protected $connection = 'mysql_trips'; // Use the new connection for this model
    protected $table = 'operatorratings';

    protected $fillable = [
        'userId',
        'operatorId',
        'starRating',
        'comment',
        'timeStamp',
    ];
}

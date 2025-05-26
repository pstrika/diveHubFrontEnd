<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;
    protected $connection = 'mysql_trips'; // Use the new connection for this model
    protected $table = 'messages';

    protected $fillable = [
        'read',
        'mail_sent_on',
        'deleted'
    ];
}

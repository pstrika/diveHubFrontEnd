<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class SiteComment extends Model
{
    use HasFactory;
    protected $connection = 'mysql_trips'; // Use the new connection for this model
    protected $table = 'sitecomments';

    protected $fillable = [
        'userId',
        'siteId', // Add _token to the fillable property
        'likes',
        'comment',
        'childof',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'userid', 'id');
    }
}

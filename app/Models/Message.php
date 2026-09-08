<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;
    protected $connection = 'mysql_trips'; // Use the new connection for this model
    protected $table = 'messages';

    protected $fillable = [
        'userId',
        'from_user_id',
        'subject',
        'body',
        'read',
        'mail_sent_on',
        'deleted',
    ];

    /**
     * Who this notification is "from" - the group admin for an invite, the
     * sender for a chat message, the dive's creator for a new dive. Null
     * for system-generated ones (dive reminders, wishlist alerts) - the UI
     * falls back to "Divers Hub" in that case. Users live on a different
     * connection (mysql/laravelpro); Eloquent relations query the related
     * model's own connection, so this works fine despite that.
     */
    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }
}

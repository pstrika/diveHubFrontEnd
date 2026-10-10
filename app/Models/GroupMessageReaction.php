<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupMessageReaction extends Model
{
    use HasFactory;

    protected $connection = 'mysql_trips';
    protected $table = 'group_message_reactions';

    protected $fillable = [
        'group_message_id',
        'user_id',
        'emoji',
    ];

    /**
     * The only four reactions a chat message can get (Pablo, 2026-10-10) -
     * key => glyph/label, single source of truth for both the `emoji`
     * column's allowed values (GroupMessageController@react validation)
     * and the reaction row's rendering (messages.blade.php).
     */
    public const REACTIONS = [
        'up'       => ['glyph' => '👍', 'label' => 'Thumbs up'],
        'down'     => ['glyph' => '👎', 'label' => 'Thumbs down'],
        'heart'    => ['glyph' => '❤️', 'label' => 'Heart'],
        'thinking' => ['glyph' => '🤔', 'label' => 'Thinking'],
        'laugh'    => ['glyph' => '😂', 'label' => 'Laughing'],
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(GroupMessage::class, 'group_message_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

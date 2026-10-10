<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class GroupMessage extends Model
{
    use HasFactory;

    protected $connection = 'mysql_trips';
    protected $table = 'group_messages';

    protected $fillable = [
        'group_id',
        'user_id',
        'body',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(GroupMessagePhoto::class, 'group_message_id');
    }

    public function siteMentions(): HasMany
    {
        return $this->hasMany(GroupMessageSiteMention::class, 'group_message_id');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(GroupMessageReaction::class, 'group_message_id');
    }

    /**
     * The chat poll's change-detection signature for a set of messages -
     * shared between GroupMessageController@poll (the live 5s poll) and
     * GroupController@show (the initial full page load), so the FIRST
     * poll after opening the chat has a real baseline to compare against
     * instead of starting from null (Pablo, 2026-10-10: without this, the
     * reaction-received sound fired on every page load that happened to
     * already have reactions in it, since "null" always looks different
     * from a real signature string). Two controllers computing this
     * independently is exactly how the switch-a-reaction bug this is
     * fixing happened in the first place - one single place now.
     *
     * @param Collection $messages must already have 'reactions' eager-loaded
     * @return array{count: int, signature: string, reactionSignature: string}
     */
    public static function chatSignature(Collection $messages): array
    {
        $messageIds = $messages->pluck('id');
        $lastReactionId = GroupMessageReaction::whereIn('group_message_id', $messageIds)->max('id');
        $lastReactionChange = GroupMessageReaction::whereIn('group_message_id', $messageIds)->max('updated_at');
        // max(id) alone misses a switch to a different emoji - that
        // UPDATEs the existing row in place (one reaction per user now)
        // rather than deleting and re-inserting it, so the id never
        // changes; max(updated_at) catches it instead.
        $reactionSignature = $lastReactionId . ':' . $lastReactionChange;

        return [
            'count' => $messages->count(),
            'signature' => $messages->count() . ':' . optional($messages->last())->id . ':' . $reactionSignature,
            'reactionSignature' => $reactionSignature,
        ];
    }
}

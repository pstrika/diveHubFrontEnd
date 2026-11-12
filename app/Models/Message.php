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

    /** What a notification is. Null means a plain system notice. */
    public const KIND_ADMIN = 'admin';       // sent to every admin: reviews, inbound messages
    public const KIND_MENTION = 'mention';   // @name in a group chat
    public const KIND_DIVES = 'dives';       // dive(s) added to a group
    public const KIND_CHAT = 'chat';
    public const KIND_REMINDER = 'reminder';
    public const KIND_INVITE = 'invite';

    public const FOLDERS = ['inbox', 'groups', 'admin'];

    protected $fillable = [
        'userId',
        'from_user_id',
        'group_id',
        'kind',
        'subject',
        'body',
        'url',
        'expires_at',
        'read',
        'mail_sent_on',
        'deleted',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
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

    /** Which group this is about, if any. */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    /**
     * Which folder this shows in on the Messages page (Zach, 2026-11-12).
     * The one place that rule lives; scopeFolder() below is the same rule
     * in SQL and the two must agree.
     *
     *   admin   kind = admin
     *   groups  about a group, and not a mention
     *   inbox   everything else, including a mention. A mention keeps its
     *           group_id so My Groups can say which group named you, but it
     *           reads as personal (Pablo, 2026-09-14), so it stays here.
     */
    public function folder(): string
    {
        if ($this->kind === self::KIND_ADMIN) {
            return 'admin';
        }
        if ($this->group_id && $this->kind !== self::KIND_MENTION) {
            return 'groups';
        }
        return 'inbox';
    }

    public function scopeFolder($query, string $folder)
    {
        // kind is null on every older row, and NULL <> 'x' is not true in
        // SQL, so each "is not this kind" spells out the null case.
        if ($folder === 'admin') {
            return $query->where('kind', self::KIND_ADMIN);
        }
        if ($folder === 'groups') {
            return $query->whereNotNull('group_id')
                ->where(fn ($w) => $w->whereNull('kind')->orWhereNotIn('kind', [self::KIND_MENTION, self::KIND_ADMIN]));
        }
        return $query
            ->where(fn ($w) => $w->whereNull('kind')->orWhere('kind', '<>', self::KIND_ADMIN))
            ->where(fn ($w) => $w->whereNull('group_id')->orWhere('kind', self::KIND_MENTION));
    }

    /** One diver's notifications that are not in the Bin and have not expired. */
    public function scopeLiveFor($query, int $userId)
    {
        return $query->where('userId', $userId)
            ->where('deleted', 0)
            ->where(fn ($w) => $w->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    public function scopeUnread($query)
    {
        return $query->where('read', 0);
    }

    /**
     * Marks everything unread about one group as read, @mentions included.
     * Used when the diver opens the group, and when they stop being in it
     * (declined, removed, group deleted) so a count is never left pointing
     * at a group they cannot open. Pass no user to clear it for everyone.
     */
    public static function clearGroup(int $groupId, ?int $userId = null): int
    {
        return static::where('group_id', $groupId)->where('read', 0)
            ->when($userId, fn ($q) => $q->where('userId', $userId))
            ->where(fn ($w) => $w->whereNull('kind')->orWhere('kind', '<>', self::KIND_ADMIN))
            ->update(['read' => 1]);
    }

    /**
     * Every count the Messages page, the bell and My Groups show, from one
     * place, so two screens can never disagree about the same number
     * (they used to: the page counted the 200 rows it had loaded, the nav
     * counted the table).
     *
     * @return array{inbox:int,groups:int,admin:int,bin:int,byGroup:array<int,int>,mentions:array<int,int>}
     */
    public static function countsFor(int $userId): array
    {
        $unread = fn () => static::liveFor($userId)->unread();

        return [
            'inbox' => $unread()->folder('inbox')->count(),
            'groups' => $unread()->folder('groups')->count(),
            'admin' => $unread()->folder('admin')->count(),
            'bin' => static::where('userId', $userId)->where('deleted', 1)->count(),
            'byGroup' => $unread()->folder('groups')
                ->selectRaw('group_id, count(*) as n')->groupBy('group_id')
                ->pluck('n', 'group_id')->map(fn ($n) => (int) $n)->all(),
            'mentions' => $unread()->where('kind', self::KIND_MENTION)->whereNotNull('group_id')
                ->selectRaw('group_id, count(*) as n')->groupBy('group_id')
                ->pluck('n', 'group_id')->map(fn ($n) => (int) $n)->all(),
        ];
    }
}

<?php

namespace App\Services;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Message;
use Illuminate\Support\Facades\Log;

/**
 * Single entry point for "notify these users about something" - writes one
 * row per recipient into the existing in-app notification center (the
 * `messages` table behind the bell icon / Messages page) and fires a
 * browser push via PushNotificationService. Both are best-effort: a
 * failure in either must never block the action that triggered it.
 *
 * Deliberately does not set send_on_email/send_on_sms - these are
 * real-time pings, not the wishlist-style digest email the external
 * emailQueueFlush function sends for rows with send_on_email=1. Anything
 * here that also needs an email (e.g. trip reminders) sends that email
 * itself via Mailgun directly, same as before.
 */
class NotificationService
{
    /**
     * @param iterable $mentionedUserIds Recipients who were personally
     *   @-mentioned in the message this notification is about (Pablo,
     *   2026-09-14: "general group [notifications] go to Groups... a
     *   message sent to a user using @name... put it in the inbox").
     *   Their own copy of this notification gets group_id = null (Inbox)
     *   even though $groupId is set for everyone else's copy - a direct
     *   ping reads as personal, not general group chatter.
     */
    public static function notify(iterable $userIds, string $subject, string $body, ?string $url = null, ?int $excludeUserId = null, ?int $fromUserId = null, ?int $groupId = null, iterable $mentionedUserIds = []): void
    {
        $userIds = collect($userIds)->filter(fn ($id) => $id != $excludeUserId)->unique()->values();

        if ($groupId) {
            $userIds = self::filterMutedGroupMembers($groupId, $userIds);
        }

        if ($userIds->isEmpty()) {
            return;
        }

        $mentioned = collect($mentionedUserIds)->map(fn ($id) => (int) $id)->all();

        foreach ($userIds as $userId) {
            try {
                Message::create([
                    'userId' => $userId,
                    'from_user_id' => $fromUserId,
                    'group_id' => in_array((int) $userId, $mentioned, true) ? null : $groupId,
                    'subject' => $subject,
                    'body' => $body,
                    'read' => 0,
                    'deleted' => 0,
                ]);
            } catch (\Throwable $e) {
                Log::error('Failed to write in-app notification for user ' . $userId . ': ' . $e->getMessage());
            }
        }

        PushNotificationService::notify($userIds, $subject, $body, $url);
    }

    /**
     * A group admin's "mute all" silences every notification for every
     * member of that group; short of that, each member can mute it just
     * for themselves via their own bell toggle (App\Models\Group,
     * GroupMember - Pablo, 2026-09-14). Applies to every notify() call
     * that passes a $groupId, so no individual call site needs to
     * remember to check this itself.
     */
    private static function filterMutedGroupMembers(int $groupId, $userIds)
    {
        $group = Group::find($groupId);
        if (!$group || $group->notifications_muted) {
            return collect();
        }

        $mutedIds = GroupMember::where('group_id', $groupId)->where('notifications_muted', true)->pluck('user_id');

        return $userIds->reject(fn ($id) => $mutedIds->contains((int) $id))->values();
    }
}

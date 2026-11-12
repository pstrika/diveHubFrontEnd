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
     *   Their own copy of this notification is marked kind = mention,
     *   which puts it in the Inbox even though everyone else's copy goes
     *   to Groups - a direct ping reads as personal, not general group
     *   chatter. It keeps the group_id (Zach, 2026-11-12) so My Groups can
     *   show which group named you and opening that group clears it.
     * @param string|null $kind One of the Message::KIND_* values. Decides
     *   the folder together with $groupId (Message::folder()). Pass
     *   Message::KIND_ADMIN for anything sent to every admin, so it lands
     *   in the Admin folder and stays out of the bell's number.
     */
    public static function notify(iterable $userIds, string $subject, string $body, ?string $url = null, ?int $excludeUserId = null, ?int $fromUserId = null, ?int $groupId = null, iterable $mentionedUserIds = [], ?string $kind = null): void
    {
        $userIds = collect($userIds)->filter(fn ($id) => $id != $excludeUserId)->unique()->values();

        if ($groupId) {
            $userIds = self::filterMutedGroupMembers($groupId, $userIds);
        }

        if ($userIds->isEmpty()) {
            return;
        }

        $mentioned = collect($mentionedUserIds)->map(fn ($id) => (int) $id)->all();

        // Saved as a path ("/Groups/lobster-hunters"), not with the host it
        // was built on. The same row is opened later from whatever host the
        // diver is on, and a link written by a cron hit or the CLI carries
        // that caller's host, where the diver is not signed in. The push
        // below keeps the full address, which it needs.
        $savedUrl = null;
        if ($url) {
            $root = rtrim(url('/'), '/');
            $savedUrl = str_starts_with($url, $root . '/') ? substr($url, strlen($root)) : $url;
            $savedUrl = mb_substr($savedUrl, 0, 500);
        }

        foreach ($userIds as $userId) {
            try {
                Message::create([
                    'userId' => $userId,
                    'from_user_id' => $fromUserId,
                    'group_id' => $groupId,
                    'kind' => $groupId && in_array((int) $userId, $mentioned, true) ? Message::KIND_MENTION : $kind,
                    'subject' => $subject,
                    'body' => $body,
                    // The link used to go to the push only, so a row on the
                    // Messages page could not take you to what it was about.
                    'url' => $savedUrl,
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

<?php

namespace App\Services;

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
    public static function notify(iterable $userIds, string $subject, string $body, ?string $url = null, ?int $excludeUserId = null): void
    {
        $userIds = collect($userIds)->filter(fn ($id) => $id != $excludeUserId)->unique()->values();
        if ($userIds->isEmpty()) {
            return;
        }

        foreach ($userIds as $userId) {
            try {
                Message::create([
                    'userId' => $userId,
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
}

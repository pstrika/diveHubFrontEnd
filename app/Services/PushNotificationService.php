<?php

namespace App\Services;

use App\Models\PushSubscription;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

/**
 * Thin wrapper around minishlink/web-push. There's no queue worker running
 * in this app (QUEUE_CONNECTION=sync), so sends happen inline on the
 * request that triggers them - WebPush::flush() still dispatches all
 * queued pushes concurrently, so this stays fast even with several
 * subscribers. Every call site treats this as best-effort: a failure here
 * must never block the action that triggered it (posting a message,
 * adding a dive, etc).
 */
class PushNotificationService
{
    /**
     * Notifies every subscribed device for the given user ids (skipping
     * $excludeUserId, typically the person who caused the notification).
     * Silently does nothing if VAPID isn't configured or no one in the
     * list has a subscription.
     */
    public static function notify(iterable $userIds, string $title, string $body, ?string $url = null, ?int $excludeUserId = null): void
    {
        if (!config('services.webpush.public_key') || !config('services.webpush.private_key')) {
            return;
        }

        $userIds = collect($userIds)->filter(fn ($id) => $id != $excludeUserId)->unique()->values();
        if ($userIds->isEmpty()) {
            return;
        }

        $subscriptions = PushSubscription::whereIn('user_id', $userIds)->get();
        if ($subscriptions->isEmpty()) {
            return;
        }

        try {
            $webPush = new WebPush([
                'VAPID' => [
                    'subject' => config('services.webpush.subject'),
                    'publicKey' => config('services.webpush.public_key'),
                    'privateKey' => config('services.webpush.private_key'),
                ],
            ]);

            $payload = json_encode([
                'title' => $title,
                'body' => $body,
                'url' => $url,
            ]);

            foreach ($subscriptions as $subscription) {
                $webPush->queueNotification(
                    Subscription::create([
                        'endpoint' => $subscription->endpoint,
                        'publicKey' => $subscription->public_key,
                        'authToken' => $subscription->auth_token,
                        'contentEncoding' => $subscription->content_encoding,
                    ]),
                    $payload
                );
            }

            foreach ($webPush->flush() as $report) {
                if (!$report->isSuccess() && $report->isSubscriptionExpired()) {
                    PushSubscription::where('endpoint', $report->getEndpoint())->delete();
                }
            }
        } catch (\Throwable $e) {
            Log::error('Push notification send failed: ' . $e->getMessage());
        }
    }
}

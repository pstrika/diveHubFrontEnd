<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Records communication consent so it can be produced on request.
 *
 * Twilio's A2P 10DLC registration means we may have to show where a specific
 * person agreed to be messaged. The user row holds the current state; this
 * class appends an immutable row to notification_consents every time a channel
 * changes, together with the exact wording that was on screen.
 *
 * Call it after saving the user, from any screen that offers the switches:
 *
 *     NotificationConsent::sync($user, $before, 'profile');
 *
 * where $before is the channel state read before the save. Nothing is written
 * when nothing changed, so the table reads as a history of decisions.
 */
final class NotificationConsent
{
    /** Channel key => the user column that stores it. */
    public const CHANNELS = [
        'email'    => 'email_notifications',
        'sms'      => 'sms_notifications',
        'whatsapp' => 'whatsapp_notifications',
    ];

    /** The consent wording shown on screen, kept here so the record and the page cannot drift apart. */
    public static function text(string $channel): string
    {
        switch ($channel) {
            case 'sms':
                return "Yes, I'd like to receive SMS trip reminders from Divers Hub about upcoming dives for the groups I belong to. "
                    . 'Message frequency varies (typically a few messages per month, depending on how many groups you are in). '
                    . 'Message and data rates may apply. Reply HELP for help or STOP to cancel at any time.';
            case 'whatsapp':
                return "Yes, I'd like Divers Hub to message me on WhatsApp about upcoming dives, trip reminders and my groups. "
                    . 'Message frequency varies (typically a few messages per month, depending on how many groups you are in). '
                    . 'Reply STOP in the chat to cancel at any time.';
            default:
                return 'Yes, email me about upcoming dives, trip reminders and news from my groups. '
                    . 'Every email has an unsubscribe link.';
        }
    }

    /** Channel state for a user, as ['email' => bool, 'sms' => bool, 'whatsapp' => bool]. */
    public static function state($user): array
    {
        $out = [];
        foreach (self::CHANNELS as $channel => $column) {
            $out[$channel] = (bool) ($user->{$column} ?? false);
        }
        return $out;
    }

    /**
     * Append a row for every channel whose value changed.
     *
     * @param array $before state() taken before the user was saved
     */
    public static function sync(User $user, array $before, string $source): void
    {
        $after = self::state($user);
        foreach ($after as $channel => $granted) {
            if (($before[$channel] ?? false) === $granted) {
                continue;
            }
            self::record($user, $channel, $granted, $source);
        }
    }

    /** Append one row. Never throws: a missing audit row must not lose the diver's setting. */
    public static function record(User $user, string $channel, bool $granted, string $source): void
    {
        try {
            DB::table('notification_consents')->insert([
                'userId'     => $user->id,
                'channel'    => $channel,
                'granted'    => $granted,
                'phone'      => in_array($channel, ['sms', 'whatsapp'], true) ? $user->phone : null,
                'text'       => self::text($channel),
                'source'     => $source,
                'ip'         => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 255),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning("NotificationConsent: could not record $channel for user {$user->id}: " . $e->getMessage());
        }
    }
}

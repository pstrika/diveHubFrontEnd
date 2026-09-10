<?php

namespace App\Support;

use App\Models\User;

/**
 * Communication consent: which channels a diver agreed to, and when.
 *
 * Everything lives on the users table with the rest of their config (Pablo,
 * 2026-09-10): three switches and three timestamps. Turning a channel on stamps
 * the time; turning it off clears it, so a timestamp that is set always means
 * "consent is current, given on this date".
 *
 * Twilio's A2P 10DLC registration can ask us to show when and where someone
 * agreed to be messaged. The timestamp answers when. The wording they saw is
 * the consent screen, resources/views/components/comms-preferences.blade.php,
 * and git history gives the exact wording as of that timestamp. Text lives in
 * one component used by every screen that offers the switches, so what is on
 * screen and what we would produce in an audit cannot drift apart.
 *
 * Usage, from any screen that offers the switches:
 *
 *     $before = NotificationConsent::state($user);
 *     ... set $user->email_notifications etc from the request ...
 *     NotificationConsent::stamp($user, $before);   // before saving
 *     $user->save();
 */
final class NotificationConsent
{
    /** Channel key => [switch column, consent timestamp column]. */
    public const CHANNELS = [
        'email'    => ['email_notifications', 'email_consent_at'],
        'sms'      => ['sms_notifications', 'sms_consent_at'],
        'whatsapp' => ['whatsapp_notifications', 'whatsapp_consent_at'],
    ];

    /** Just the switch columns, for looping over a request. */
    public static function switches(): array
    {
        return array_map(fn ($pair) => $pair[0], self::CHANNELS);
    }

    /** Current on/off per channel: ['email' => bool, 'sms' => bool, 'whatsapp' => bool]. */
    public static function state($user): array
    {
        $out = [];
        foreach (self::CHANNELS as $channel => [$switch, $stamp]) {
            $out[$channel] = (bool) ($user->{$switch} ?? false);
        }
        return $out;
    }

    /**
     * Set or clear the consent timestamp for every channel whose switch changed.
     * Call it after reading the request onto the user and before saving.
     *
     * @param array $before state() taken before the request was applied
     */
    public static function stamp(User $user, array $before): void
    {
        foreach (self::CHANNELS as $channel => [$switch, $stampColumn]) {
            $now = (bool) $user->{$switch};
            if (($before[$channel] ?? false) === $now) {
                continue;
            }
            $user->{$stampColumn} = $now ? now() : null;
        }
    }

    /** When they consented to a channel, or null if they have not. For the profile page and any audit answer. */
    public static function consentedAt($user, string $channel)
    {
        $stampColumn = self::CHANNELS[$channel][1] ?? null;
        return $stampColumn ? ($user->{$stampColumn} ?? null) : null;
    }
}

<?php

namespace App\Support;

use App\Models\ConversationMessage;
use App\Models\User;

/**
 * Single write path for every row in the admin Message Management
 * console's inbox (conversation_messages) - both directions, all three
 * channels. Centralizing this means the inbound webhook, the "Chat with
 * us" widget, and the admin's own replies can't drift into writing
 * slightly different shapes of row.
 */
final class ConversationLog
{
    public static function log(string $channel, string $direction, string $contact, ?string $body, array $extra = []): ConversationMessage
    {
        $contact = self::normalize($channel, $contact);

        return ConversationMessage::create(array_merge([
            'channel' => $channel,
            'direction' => $direction,
            'contact' => $contact,
            'body' => $body,
            'user_id' => self::findUserId($channel, $contact),
            'created_at' => now(),
        ], $extra));
    }

    /** Same shape every time a contact is stored, so grouping a console thread by it actually groups correctly. */
    public static function normalize(string $channel, string $contact): string
    {
        if ($channel === 'email') {
            return strtolower(trim($contact));
        }

        return PhoneNumber::toE164($contact) ?? $contact;
    }

    /**
     * Matches a phone number against users.phone regardless of which
     * format it's stored in (E.164 going forward, bare digits for
     * anything saved before that - see App\Support\PhoneNumber) - compares
     * by the last 10 digits for a phone contact, exact (case-insensitive)
     * for an email.
     */
    public static function findUserId(string $channel, string $contact): ?int
    {
        if ($channel === 'email') {
            $user = User::whereRaw('LOWER(email) = ?', [strtolower($contact)])->first();
            return $user?->id;
        }

        $digits = preg_replace('/\D/', '', $contact);
        $last10 = substr($digits, -10);
        if (strlen($last10) < 10) {
            return null;
        }

        $user = User::where('phone', 'like', "%{$last10}")->first();
        return $user?->id;
    }
}

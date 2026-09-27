<?php

namespace App\Support;

use App\Models\ConversationMessage;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Str;

/**
 * Single write path for every row in the admin Message Management
 * console's inbox (conversation_messages) - both directions, all three
 * channels. Centralizing this means the inbound webhook, the "Chat with
 * us" widget, and the admin's own replies can't drift into writing
 * slightly different shapes of row - and, since every inbound message
 * comes through here, this is also the one place that needs to notify
 * admins a new message arrived (Pablo, 2026-09-14: "admins... need to get
 * a notification if a message is incoming").
 */
final class ConversationLog
{
    public static function log(string $channel, string $direction, string $contact, ?string $body, array $extra = []): ConversationMessage
    {
        $contact = self::normalize($channel, $contact);

        $message = ConversationMessage::create(array_merge([
            'channel' => $channel,
            'direction' => $direction,
            'contact' => $contact,
            'body' => $body,
            'user_id' => self::findUserId($channel, $contact),
            'created_at' => now(),
        ], $extra));

        if ($direction === 'inbound') {
            self::notifyAdmins($channel, $contact, $body);
        }

        return $message;
    }

    /** In-app notification (+ push, via NotificationService) to every admin - the console itself has no other "someone just texted us" alert. */
    private static function notifyAdmins(string $channel, string $contact, ?string $body): void
    {
        $adminIds = User::where('role_id', 1)->pluck('id');
        if ($adminIds->isEmpty()) {
            return;
        }

        NotificationService::notify(
            $adminIds,
            'New ' . $channel . ' message',
            $contact . ': ' . Str::limit((string) $body, 100),
            route('admin.messages.index')
        );
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
     * A contact's user_id is snapshotted once, in log() above, at the
     * moment each message is written - so a contact that was unknown at
     * the time (not registered yet, or registered under a differently
     * formatted number) stays permanently unmatched even after they
     * later sign up or fix their number, unless something re-checks it.
     * Called from the admin console on every list load/poll and thread
     * open (Pablo, 2026-09-14: "refresh if they registered or included
     * their number, to show the avatar and name in the chat") - re-runs
     * findUserId() for a contact still unmatched and backfills every row
     * for it in one go, so it's a one-time catch-up per contact rather
     * than a re-check on every message forever.
     */
    public static function reconcileContact(string $contact): void
    {
        $sample = ConversationMessage::where('contact', $contact)->whereNull('user_id')->first();
        if (!$sample) {
            return;
        }

        $userId = self::findUserId($sample->channel, $contact);
        if ($userId) {
            ConversationMessage::where('contact', $contact)->whereNull('user_id')->update(['user_id' => $userId]);
        }
    }

    /** Same idea as reconcileContact(), for every contact with any unmatched row - the admin console's list view calls this once per load/poll. */
    public static function reconcileUnresolved(): void
    {
        ConversationMessage::whereNull('user_id')
            ->select('contact', 'channel')
            ->distinct()
            ->get()
            ->each(function ($row) {
                $userId = self::findUserId($row->channel, $row->contact);
                if ($userId) {
                    ConversationMessage::where('contact', $row->contact)->whereNull('user_id')->update(['user_id' => $userId]);
                }
            });
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

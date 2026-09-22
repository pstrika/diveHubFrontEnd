<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * The real support@divers-hub.com mailbox, via Microsoft Graph app-only
 * auth (client credentials - Mail.Read + Mail.Send Application
 * permissions, no interactive sign-in or mailbox password) - what the
 * admin Message Management console's email channel actually sends
 * through, and what SyncSupportInbox reads replies from (Pablo,
 * 2026-09-22: "use this email address inside the conversation...a legit
 * both way communication channel", replacing the old Mailgun-from-a-
 * different-address, send-only setup).
 */
class GraphMailService
{
    private static function mailbox(): ?string
    {
        return config('services.graph.mailbox');
    }

    /**
     * Cached just under the token's real ~60 minute lifetime so this isn't
     * requesting a fresh one on every send/sync call.
     */
    private static function accessToken(): ?string
    {
        $tenant = config('services.graph.tenant_id');
        $clientId = config('services.graph.client_id');
        $clientSecret = config('services.graph.client_secret');

        if (!$tenant || !$clientId || !$clientSecret) {
            return null;
        }

        return Cache::remember('graph_mail_access_token', now()->addMinutes(50), function () use ($tenant, $clientId, $clientSecret) {
            try {
                $response = Http::asForm()->post("https://login.microsoftonline.com/{$tenant}/oauth2/v2.0/token", [
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'scope' => 'https://graph.microsoft.com/.default',
                    'grant_type' => 'client_credentials',
                ]);

                if ($response->failed()) {
                    Log::error('Graph token request failed: ' . $response->body());
                    return null;
                }

                return $response->json('access_token');
            } catch (\Throwable $e) {
                Log::error('Graph token request exception: ' . $e->getMessage());
                return null;
            }
        });
    }

    public static function send(string $to, string $subject, string $bodyText): bool
    {
        $token = self::accessToken();
        $mailbox = self::mailbox();
        if (!$token || !$mailbox) {
            return false;
        }

        try {
            $response = Http::withToken($token)->post(
                'https://graph.microsoft.com/v1.0/users/' . $mailbox . '/sendMail',
                [
                    'message' => [
                        'subject' => $subject,
                        'body' => ['contentType' => 'Text', 'content' => $bodyText],
                        'toRecipients' => [['emailAddress' => ['address' => $to]]],
                    ],
                    'saveToSentItems' => true,
                ]
            );

            if ($response->failed()) {
                Log::error("Graph sendMail failed (to {$to}): " . $response->body());
            }

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error("Graph sendMail exception (to {$to}): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Inbox messages received at or after $since, oldest first. Callers
     * dedupe against conversation_messages by each message's Graph id
     * rather than this needing to track a cursor itself - simpler, and
     * cheap at this mailbox's volume to just re-scan a short overlap
     * window every run.
     */
    public static function fetchRecentInbound(\DateTimeInterface $since): array
    {
        $token = self::accessToken();
        $mailbox = self::mailbox();
        if (!$token || !$mailbox) {
            return [];
        }

        try {
            $response = Http::withToken($token)->get(
                'https://graph.microsoft.com/v1.0/users/' . $mailbox . '/mailFolders/Inbox/messages',
                [
                    '$filter' => 'receivedDateTime ge ' . gmdate('Y-m-d\TH:i:s\Z', $since->getTimestamp()),
                    '$orderby' => 'receivedDateTime asc',
                    '$select' => 'id,subject,from,receivedDateTime,body,bodyPreview',
                    '$top' => 50,
                ]
            );

            if ($response->failed()) {
                Log::error('Graph inbox fetch failed: ' . $response->body());
                return [];
            }

            return $response->json('value') ?? [];
        } catch (\Throwable $e) {
            Log::error('Graph inbox fetch exception: ' . $e->getMessage());
            return [];
        }
    }
}

<?php

namespace App\Console\Commands;

use App\Models\ConversationMessage;
use App\Services\GraphMailService;
use App\Support\ConversationLog;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Pulls new mail from the real support@divers-hub.com inbox into the
 * admin Message Management console, via Microsoft Graph - see
 * GraphMailService for why (Pablo, 2026-09-22). Triggered by an Azure
 * Logic App every few minutes, same pattern as the other cron jobs (see
 * CronController::syncSupportInbox).
 */
class SyncSupportInbox extends Command
{
    protected $signature = 'support:sync-inbox';

    protected $description = 'Pull new mail from support@divers-hub.com into the admin message console';

    public function handle()
    {
        // A wide overlap so a slow run or a missed cron tick never drops a
        // message - duplicates across runs are caught below by Graph's own
        // message id (external_id), not by this window being tight.
        $since = Carbon::now('UTC')->subMinutes(30);

        $messages = GraphMailService::fetchRecentInbound($since);

        $imported = 0;
        foreach ($messages as $message) {
            $externalId = $message['id'] ?? null;
            $from = $message['from']['emailAddress']['address'] ?? null;

            if (!$externalId || !$from) {
                continue;
            }

            if (ConversationMessage::where('external_id', $externalId)->exists()) {
                continue;
            }

            ConversationLog::log('email', 'inbound', $from, $this->plainTextBody($message), [
                'subject' => $message['subject'] ?? null,
                'external_id' => $externalId,
                'status' => 'received',
                'created_at' => isset($message['receivedDateTime']) ? Carbon::parse($message['receivedDateTime']) : now(),
            ]);

            $imported++;
        }

        $this->info("Imported {$imported} new message(s) from the support inbox.");

        return self::SUCCESS;
    }

    /**
     * The console's thread view renders body via textContent, not innerHTML
     * (it's a plain-text chat log, same as every SMS/WhatsApp row) - an
     * HTML email body gets its tags stripped down to readable text instead
     * of showing raw markup.
     */
    private function plainTextBody(array $message): string
    {
        $content = $message['body']['content'] ?? ($message['bodyPreview'] ?? '');

        if (($message['body']['contentType'] ?? 'text') === 'html') {
            $content = preg_replace('/<(br|\/p|\/div|\/tr)[^>]*>/i', "\n", $content);
            $content = html_entity_decode(strip_tags($content), ENT_QUOTES);
            $content = trim(preg_replace("/\n{3,}/", "\n\n", $content));
        }

        return $content;
    }
}

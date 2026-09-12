<?php

namespace App\Http\Controllers;

use App\Support\ConversationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Twilio's "A message comes in" webhook, shared by SMS and WhatsApp - both
 * ride the same Messages API, WhatsApp's From/To just carry a "whatsapp:"
 * prefix. Register this URL once against the Twilio number both channels
 * send from (Console -> Phone Numbers -> that number -> Messaging
 * configuration), and against the WhatsApp sender if it's configured
 * separately there.
 *
 * Twilio expects a 200 back with either an empty body or TwiML, not JSON.
 * This never auto-replies - a human answers from the admin Message
 * Management console (AdminMessagesController).
 */
class TwilioWebhookController extends Controller
{
    public function inbound(Request $request)
    {
        $from = (string) $request->input('From', '');
        $body = (string) $request->input('Body', '');
        $sid = (string) $request->input('MessageSid', '');

        if ($from === '') {
            return response('<Response></Response>', 200, ['Content-Type' => 'text/xml']);
        }

        $channel = str_starts_with($from, 'whatsapp:') ? 'whatsapp' : 'sms';
        $contact = str_replace('whatsapp:', '', $from);

        Log::info("Inbound {$channel} from {$contact}: " . Str::limit($body, 100));

        ConversationLog::log($channel, 'inbound', $contact, $body, [
            'external_id' => $sid ?: null,
        ]);

        return response('<Response></Response>', 200, ['Content-Type' => 'text/xml']);
    }
}

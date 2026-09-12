<?php

namespace App\Http\Controllers;

use App\Models\ConversationMessage;
use App\Services\SmsService;
use App\Services\WhatsAppService;
use App\Support\ConversationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Twilio's "A message comes in" webhook, shared by SMS and WhatsApp - both
 * ride the same Messages API, WhatsApp's From/To just carry a "whatsapp:"
 * prefix. Registered against the Twilio number both channels send from
 * (Console -> Phone Numbers -> that number -> Messaging configuration),
 * replacing a TwiML Bin that used to auto-reply "we are not actively
 * monitoring responses here" to everything - not true anymore now that
 * the admin Message Management console exists. Twilio's own STOP/HELP/
 * UNSUBSCRIBE handling for the number's A2P 10DLC-registered Messaging
 * Service runs at the platform level regardless of what this webhook
 * does, so that compliance keeps working either way (Pablo confirmed the
 * old bin had no keyword logic of its own, just this same blanket reply).
 *
 * Twilio expects a 200 back with either an empty body or TwiML, not JSON.
 * A human answers from the admin console - this only sends a short
 * automatic acknowledgment, and only for a contact's first message ever
 * or when their last message on file is over a week old (Pablo,
 * 2026-09-14) - not on every reply, which would get noisy fast.
 */
class TwilioWebhookController extends Controller
{
    private const ACK = "Thanks for reaching out to Divers Hub! We got your message and will reply here shortly.";

    public function inbound(Request $request)
    {
        $from = (string) $request->input('From', '');
        $body = (string) $request->input('Body', '');
        $sid = (string) $request->input('MessageSid', '');

        if ($from === '') {
            return response('<Response></Response>', 200, ['Content-Type' => 'text/xml']);
        }

        $channel = str_starts_with($from, 'whatsapp:') ? 'whatsapp' : 'sms';
        $contact = ConversationLog::normalize($channel, str_replace('whatsapp:', '', $from));

        Log::info("Inbound {$channel} from {$contact}: " . Str::limit($body, 100));

        // Checked before logging this message, or "first message ever"
        // would never be true - this one would already count as the prior message.
        $shouldAck = $this->shouldSendAck($contact);

        ConversationLog::log($channel, 'inbound', $contact, $body, [
            'external_id' => $sid ?: null,
        ]);

        if ($shouldAck) {
            $sent = $channel === 'sms' ? SmsService::send($contact, self::ACK) : WhatsAppService::sendText($contact, self::ACK);
            if ($sent) {
                ConversationLog::log($channel, 'outbound', $contact, self::ACK);
            }
        }

        return response('<Response></Response>', 200, ['Content-Type' => 'text/xml']);
    }

    /** True for a contact's very first message ever, or when nothing's been exchanged with them in over a week. */
    private function shouldSendAck(string $contact): bool
    {
        $last = ConversationMessage::where('contact', $contact)->orderByDesc('created_at')->first();

        if (!$last) {
            return true;
        }

        return $last->created_at->lt(now()->subWeek());
    }
}

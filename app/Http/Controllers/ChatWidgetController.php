<?php

namespace App\Http\Controllers;

use App\Services\SmsService;
use App\Services\WhatsAppService;
use App\Support\ConversationLog;
use App\Support\PhoneNumber;
use Illuminate\Http\Request;

/**
 * "Chat with us" widget (Pablo, 2026-09-14): a dismissible modal on every
 * page that starts a real SMS/WhatsApp thread with support, rather than a
 * form that just emails somewhere. Submitting it does two things: logs the
 * message in the admin Message Management console as if it were inbound,
 * and sends a real confirmation text back to the phone number given -
 * that's what actually opens a two-way thread, since our Twilio number now
 * has a text on record from them. From there they can just reply by
 * texting like normal; that reply arrives via TwilioWebhookController same
 * as any other inbound message.
 */
class ChatWidgetController extends Controller
{
    public function send(Request $request)
    {
        $request->validate([
            'channel' => 'required|in:sms,whatsapp',
            'phone' => 'required|string|max:32',
            'body' => 'required|string|max:1000',
        ]);

        $channel = $request->channel;
        $e164 = PhoneNumber::toE164($request->phone);

        if (!$e164) {
            return response()->json(['success' => false, 'message' => 'That doesn\'t look like a valid phone number.'], 422);
        }

        if ($channel === 'sms' && !PhoneNumber::isUs($e164)) {
            return response()->json(['success' => false, 'message' => 'SMS only works with a US number - try WhatsApp instead.'], 422);
        }

        // Logged as inbound: from the console's point of view this is the
        // diver reaching out, even though the actual bytes came through
        // our own web form rather than a real text message.
        ConversationLog::log($channel, 'inbound', $e164, $request->body, [
            'user_id' => auth()->user() && auth()->user()->isNotGuest() ? auth()->user()->id : null,
        ]);

        $confirmation = "Thanks for reaching out to Divers Hub! We got your message and will reply here shortly.";
        $sent = $channel === 'sms' ? SmsService::send($e164, $confirmation) : WhatsAppService::sendText($e164, $confirmation);

        if ($sent) {
            ConversationLog::log($channel, 'outbound', $e164, $confirmation);
        }

        // Success either way for the diver - their message is logged and
        // an admin will see it, even if the confirmation text itself failed
        // to send (e.g. a WhatsApp number outside any prior 24h window).
        return response()->json(['success' => true]);
    }
}

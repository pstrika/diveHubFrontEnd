<?php

namespace App\Http\Controllers;

use App\Services\GraphMailService;
use App\Services\SmsService;
use App\Support\ConversationLog;
use App\Support\PhoneNumber;
use Illuminate\Http\Request;

/**
 * "Chat with us" widget (Pablo, 2026-09-14): a dismissible modal on every
 * page that starts a real SMS/email thread with support, rather than a
 * form that just emails somewhere. Submitting it does two things: logs the
 * message in the admin Message Management console as if it were inbound,
 * and sends a real confirmation back on the same channel - that's what
 * actually opens a two-way thread (a text they can just reply to, or an
 * email from support@divers-hub.com they can reply to).
 *
 * WhatsApp isn't a channel here (Pablo, 2026-09-27: "we can't answer back
 * unless the user starts the conversation from its own WhatsApp
 * application") - see chat-with-us.blade.php for the mobile-only
 * wa.me link that replaces it, which starts the thread from the diver's
 * side instead of ours.
 */
class ChatWidgetController extends Controller
{
    public function send(Request $request)
    {
        $request->validate([
            'channel' => 'required|in:sms,email',
            'body' => 'required|string|max:1000',
            'phone' => 'required_if:channel,sms|nullable|string|max:32',
            'email' => 'required_if:channel,email|nullable|email|max:255',
        ]);

        $channel = $request->channel;
        $user = auth()->user() && auth()->user()->isNotGuest() ? auth()->user() : null;

        // A logged-in member never gets to supply their own contact info
        // here - whatever the request claims is ignored in favor of the
        // account's own value (Pablo, 2026-09-27: "it needs to be from
        // it's verified number"). This mirrors what chat-with-us.blade.php
        // already renders read-only, but is enforced again here since a
        // read-only input is only a UI suggestion to a real request.
        if ($channel === 'sms') {
            if ($user) {
                if (!$user->phone || !$user->phone_verified_at) {
                    return response()->json(['success' => false, 'message' => 'Verify your phone number in your profile first, or use email instead.'], 422);
                }
                $contact = $user->phone;
            } else {
                $contact = $request->phone;
            }

            $e164 = PhoneNumber::toE164($contact);
            if (!$e164) {
                return response()->json(['success' => false, 'message' => 'That doesn\'t look like a valid phone number.'], 422);
            }
            if (!PhoneNumber::isUs($e164)) {
                return response()->json(['success' => false, 'message' => 'SMS only works with a US number - try email instead.'], 422);
            }
            $contact = $e164;
        } else {
            $contact = $user ? $user->email : $request->email;
        }

        // Logged as inbound: from the console's point of view this is the
        // diver reaching out, even though the actual bytes came through
        // our own web form rather than a real text or email.
        ConversationLog::log($channel, 'inbound', $contact, $request->body, [
            'user_id' => $user?->id,
        ]);

        $confirmation = "Thanks for reaching out to Divers Hub! We got your message and will reply here shortly.";
        $sent = $channel === 'sms'
            ? SmsService::send($contact, $confirmation)
            : GraphMailService::send($contact, 'We got your message - Divers Hub', $confirmation);

        if ($sent) {
            ConversationLog::log($channel, 'outbound', $contact, $confirmation);
        }

        // Success either way for the diver - their message is logged and
        // an admin will see it, even if the confirmation itself failed to
        // send.
        return response()->json(['success' => true]);
    }
}

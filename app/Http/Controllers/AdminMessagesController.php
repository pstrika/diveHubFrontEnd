<?php

namespace App\Http\Controllers;

use App\Models\ConversationMessage;
use App\Models\User;
use App\Services\SmsService;
use App\Services\WhatsAppService;
use App\Support\ConversationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Mailgun\Mailgun;

/**
 * Admin Message Management console (Pablo, 2026-09-14): "I'm expecting I
 * can see all communications that went on with all users and being able
 * to reply. Also initiate communication via email and SMS."
 *
 * Everything reads/writes conversation_messages, one row per SMS/WhatsApp/
 * email in either direction - see that migration and App\Support\
 * ConversationLog for how a row gets there (the inbound Twilio webhook,
 * the "Chat with us" widget, or this controller's own send()).
 *
 * Email is outbound-only here - there's no inbound email webhook (that's
 * a Mailgun-side routing/DNS setup, its own separate piece of work), so a
 * diver's email reply lands in their inbox and Pablo's, same as any other
 * email, not in this console.
 */
class AdminMessagesController extends Controller
{
    public function index()
    {
        $this->authorize('manage-users', User::class);

        $latestIds = ConversationMessage::selectRaw('MAX(id) as id')->groupBy('contact')->pluck('id');
        $conversations = ConversationMessage::whereIn('id', $latestIds)
            ->with('user')
            ->orderByDesc('created_at')
            ->get();

        $unreadCounts = ConversationMessage::where('direction', 'inbound')
            ->whereNull('read_at')
            ->selectRaw('contact, count(*) as c')
            ->groupBy('contact')
            ->pluck('c', 'contact');

        return view('pages.AdminMessages', compact('conversations', 'unreadCounts'));
    }

    /**
     * Full history for one contact, and marks its unread inbound messages
     * read. $contact arrives already URL-decoded - Laravel's router does
     * that once for every route parameter - so decoding it again here
     * would turn a real "+" (the client encodes it as %2B) into a space,
     * the classic application/x-www-form-urlencoded footgun.
     */
    public function thread(string $contact)
    {
        $this->authorize('manage-users', User::class);

        $messages = ConversationMessage::where('contact', $contact)->orderBy('created_at')->with('admin')->get();

        ConversationMessage::where('contact', $contact)
            ->where('direction', 'inbound')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $user = optional($messages->first(fn ($m) => $m->user_id))->user;

        return response()->json([
            'contact' => $contact,
            'userName' => $user->name ?? null,
            'messages' => $messages->map(fn ($m) => [
                'id' => $m->id,
                'channel' => $m->channel,
                'direction' => $m->direction,
                'body' => $m->body,
                'subject' => $m->subject,
                'adminName' => $m->admin->name ?? null,
                'status' => $m->status,
                'createdAt' => $m->created_at->toIso8601String(),
            ])->values(),
        ]);
    }

    /** Reply to an existing contact, or start a brand new conversation - same action either way. */
    public function send(Request $request)
    {
        $this->authorize('manage-users', User::class);

        $request->validate([
            'channel' => 'required|in:sms,whatsapp,email',
            'contact' => 'required|string|max:190',
            'body' => 'required|string|max:2000',
            'subject' => 'nullable|string|max:200',
        ]);

        $channel = $request->channel;
        $contact = ConversationLog::normalize($channel, $request->contact);
        $body = $request->body;

        $ok = $this->sendVia($channel, $contact, $body, $request->subject);

        ConversationLog::log($channel, 'outbound', $contact, $body, [
            'subject' => $request->subject,
            'admin_id' => auth()->user()->id,
            'status' => $ok ? 'sent' : 'failed',
        ]);

        if (!$ok) {
            $hint = $channel === 'whatsapp'
                ? 'Send failed - WhatsApp only allows free-form replies within 24 hours of their last message; after that it needs an approved template.'
                : 'Send failed - check the logs.';
            return response()->json(['success' => false, 'message' => $hint], 422);
        }

        return response()->json(['success' => true]);
    }

    private function sendVia(string $channel, string $contact, string $body, ?string $subject): bool
    {
        if ($channel === 'sms') {
            return SmsService::send($contact, $body);
        }

        if ($channel === 'whatsapp') {
            return WhatsAppService::sendText($contact, $body);
        }

        // email
        try {
            $mg = Mailgun::create(env('MAILGUN_KEY'));
            $mg->messages()->send('mail.divers-hub.com', [
                'from' => 'Divers-Hub <postmaster@mail.divers-hub.com>',
                'to' => $contact,
                'subject' => $subject ?: 'A message from Divers Hub',
                'html' => nl2br(e($body)),
            ]);
            return true;
        } catch (\Throwable $e) {
            Log::error("Admin email send failed (to {$contact}): " . $e->getMessage());
            return false;
        }
    }
}

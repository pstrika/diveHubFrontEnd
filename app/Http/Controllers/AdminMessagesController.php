<?php

namespace App\Http\Controllers;

use App\Models\ConversationMessage;
use App\Models\User;
use App\Services\SmsService;
use App\Services\WhatsAppService;
use App\Support\ConversationLog;
use App\Support\UserAvatar;
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
 * the "Chat with us" widget, or this controller's own send()) - that same
 * ConversationLog::log() is also where every admin gets notified the
 * moment a new inbound message lands, so this controller doesn't need to
 * poll for that itself.
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

        return view('pages.AdminMessages', $this->listData());
    }

    /** Same shape as index(), as JSON - polled every few seconds by the console so it doesn't need a manual refresh. */
    public function poll()
    {
        $this->authorize('manage-users', User::class);

        $data = $this->listData();

        return response()->json([
            'conversations' => $data['conversations']->map(fn ($c) => [
                'contact' => $c->contact,
                'channel' => $c->channel,
                'direction' => $c->direction,
                'body' => $c->body,
                'createdAt' => $c->created_at->toIso8601String(),
                'userName' => $c->user->name ?? null,
                'unread' => $data['unreadCounts'][$c->contact] ?? 0,
                'avatarUrl' => $this->avatarUrl($c->user),
            ])->values(),
        ]);
    }

    private function listData(): array
    {
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

        return compact('conversations', 'unreadCounts');
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
            'hasAccount' => (bool) $user,
            'avatarUrl' => $this->avatarUrl($user),
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

    /** For the New Message modal's contact search - name, email or phone, real accounts only. */
    public function searchUsers(Request $request)
    {
        $this->authorize('manage-users', User::class);

        $q = trim((string) $request->query('q', ''));
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $users = User::where('name', 'like', "%{$q}%")
            ->orWhere('email', 'like', "%{$q}%")
            ->orWhere('phone', 'like', "%{$q}%")
            ->limit(8)
            ->get(['id', 'name', 'email', 'phone', 'picture']);

        return response()->json($users->map(fn ($u) => [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'phone' => $u->phone,
            'avatarUrl' => $this->avatarUrl($u),
        ])->values());
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

    /** A registration link, for a contact who isn't a Divers Hub account yet. */
    public function sendInvite(Request $request)
    {
        $this->authorize('manage-users', User::class);

        $request->validate([
            'channel' => 'required|in:sms,whatsapp,email',
            'contact' => 'required|string|max:190',
        ]);

        $channel = $request->channel;
        $contact = ConversationLog::normalize($channel, $request->contact);
        $body = 'Hi! Create your free Divers Hub account to get trip reminders and more: ' . route('create-account');

        $ok = $this->sendVia($channel, $contact, $body, 'Join Divers Hub');

        ConversationLog::log($channel, 'outbound', $contact, $body, [
            'subject' => 'Join Divers Hub',
            'admin_id' => auth()->user()->id,
            'status' => $ok ? 'sent' : 'failed',
        ]);

        return response()->json(['success' => $ok]);
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

    private function avatarUrl(?User $user): ?string
    {
        if (!$user || !UserAvatar::exists($user->picture)) {
            return null;
        }

        return UserAvatar::url($user->picture);
    }
}

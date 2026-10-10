<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupMessage;
use App\Models\GroupMessagePhoto;
use App\Models\GroupMessageReaction;
use App\Models\GroupMessageSiteMention;
use App\Models\Site;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\WhatsAppService;
use App\Support\MentionParser;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GroupMessageController extends Controller
{
    public function store(Request $request, $groupSlug)
    {
        $group = Group::where('slug', $groupSlug)->firstOrFail();

        if (!$group->isMember(auth()->user()->id)) {
            abort(403);
        }

        $request->validate([
            'body' => 'nullable|max:2000',
            'existing_photos' => 'nullable|array|max:5',
            'existing_photos.*' => 'string',
            'mentioned_site_ids' => 'nullable|array|max:10',
            'mentioned_site_ids.*' => 'integer',
        ]);

        // Photos are uploaded ahead of time via the chat dropzone (see
        // GroupController::uploadImage); only paths already stored under this
        // group's chat folder are trusted here.
        $photoPaths = collect($request->input('existing_photos', []))
            ->filter(fn ($path) => str_starts_with($path, 'img/groups/' . $group->id . '/chat/'))
            ->values();

        // #Site mentions (Pablo, 2026-10-10): the composer's "#" dropdown
        // already knows exactly which site was picked, so the client just
        // asserts the ids - no text re-parsing the way @mention needs
        // (MentionParser::detect() below), just a trust-but-verify check
        // against real sites, same spirit as the photo path check above.
        $mentionedSiteIds = Site::whereIn('id', $request->input('mentioned_site_ids', []))
            ->pluck('id');

        if (empty($request->body) && $photoPaths->isEmpty()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Write something or attach a photo.'], 422);
            }
            return redirect()->back()->with('msg', 'Write something or attach a photo.');
        }

        $message = GroupMessage::create([
            'group_id' => $group->id,
            'user_id' => auth()->user()->id,
            'body' => $request->body,
        ]);

        foreach ($photoPaths as $path) {
            GroupMessagePhoto::create([
                'group_message_id' => $message->id,
                'file' => $path,
            ]);
        }

        foreach ($mentionedSiteIds as $siteId) {
            GroupMessageSiteMention::create([
                'group_message_id' => $message->id,
                'site_id' => $siteId,
            ]);
        }

        $mentionedIds = $this->resolveMentionedUserIds($group, $request->body ?? '');
        $this->notifyNewMessage($group, $message, $mentionedIds);
        $this->notifyMentions($group, $request->body ?? '', $mentionedIds);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('Groups.show', ['group' => $group->slug])->with('msg', 'Message posted!');
    }

    /**
     * Polled by the chat UI every few seconds. Returns the rendered messages
     * partial plus a signature so the client only re-renders when something
     * actually changed - count alone (the only thing originally compared
     * here) never changes just because a reaction was added or removed, so
     * a reaction-only update folds a reaction signature in too (Pablo,
     * 2026-10-10).
     *
     * That reaction signature has to include max(updated_at), not just
     * max(id) - switching to a different emoji (one reaction per user now,
     * see react() below) UPDATEs the existing row in place rather than
     * deleting and re-inserting it, so its id never changes and a
     * max(id)-only signature silently missed every switch (caught live,
     * 2026-10-10: "if it's already there it's not refreshing" - it was
     * only ever "not refreshing" for a switch, add/remove were already
     * fine, since those really do change which ids exist).
     */
    public function poll($groupSlug)
    {
        $group = Group::where('slug', $groupSlug)->firstOrFail();

        if (!$group->isMember(auth()->user()->id)) {
            abort(403);
        }

        $messages = $group->messages()
            ->with(['user', 'photos', 'siteMentions.site', 'reactions.user'])
            ->orderBy('created_at')
            ->get();

        $sig = GroupMessage::chatSignature($messages);

        return response()->json([
            'count' => $sig['count'],
            'signature' => $sig['signature'],
            // Exposed separately (not just folded into signature) so the
            // frontend can tell "a reaction changed" apart from "a new
            // message arrived" - it plays a sound for either, but only
            // ever one sound, and needs to know which to decide that
            // without re-deriving it.
            'reactionSignature' => $sig['reactionSignature'],
            'html' => view('pages.Groups.partials.messages', compact('messages'))->render(),
        ]);
    }

    /**
     * Toggle the current viewer's reaction on one message (Pablo,
     * 2026-10-10) - on if they hadn't reacted with this emoji yet, off if
     * they had. A diver can stack more than one of the four reactions on
     * the same message, just never the same one twice (the unique index
     * on group_message_reactions backs that up too).
     */
    public function react(Request $request, $groupSlug, $messageId)
    {
        $group = Group::where('slug', $groupSlug)->firstOrFail();

        if (!$group->isMember(auth()->user()->id)) {
            abort(403);
        }

        $message = GroupMessage::where('id', $messageId)->where('group_id', $group->id)->firstOrFail();

        // Can't react to your own message (Pablo, 2026-10-10) - the
        // frontend already keeps the picker from opening on your own
        // bubble, this is the authoritative check against a direct call.
        if ((int) $message->user_id === (int) auth()->user()->id) {
            return response()->json(['message' => "You can't react to your own message."], 403);
        }

        $request->validate([
            'emoji' => 'required|string|in:' . implode(',', array_keys(GroupMessageReaction::REACTIONS)),
        ]);

        // One reaction per user per message, not one per (message, user,
        // emoji) - same as WhatsApp (Pablo, 2026-10-10). Tapping the emoji
        // you already gave removes it; tapping a different one switches
        // to it (never adds a second row - group_message_reactions now
        // has a unique index on just (group_message_id, user_id)).
        $existing = GroupMessageReaction::where('group_message_id', $message->id)
            ->where('user_id', auth()->user()->id)
            ->first();

        if ($existing && $existing->emoji === $request->emoji) {
            $existing->delete();
            $mine = null;
        } elseif ($existing) {
            $existing->update(['emoji' => $request->emoji]);
            $mine = $request->emoji;
        } else {
            GroupMessageReaction::create([
                'group_message_id' => $message->id,
                'user_id' => auth()->user()->id,
                'emoji' => $request->emoji,
            ]);
            $mine = $request->emoji;
        }

        $counts = GroupMessageReaction::where('group_message_id', $message->id)
            ->selectRaw('emoji, count(*) as c')
            ->groupBy('emoji')
            ->pluck('c', 'emoji')
            ->map(fn ($c) => (int) $c);

        return response()->json([
            // The viewer's own current reaction key, or null if they just
            // removed it - the frontend needs to know WHICH one to mark
            // active, not just whether some reaction exists.
            'mine' => $mine,
            // $counts on the left: PHP's array union keeps the LEFT side's
            // value on a key collision, so the real counts win over the
            // zero defaults filling in whichever emoji has none yet.
            'counts' => $counts->all() + array_fill_keys(array_keys(GroupMessageReaction::REACTIONS), 0),
        ]);
    }

    /**
     * Every active member's copy of this notification (see
     * MentionParser), minus whoever sent the message - shared by
     * notifyNewMessage() (decides Inbox vs Groups) and notifyMentions()
     * (decides who gets an immediate WhatsApp ping), so the two can't
     * disagree about who was actually named.
     */
    private function resolveMentionedUserIds(Group $group, string $body)
    {
        if (trim($body) === '') {
            return collect();
        }

        $members = $group->activeMembers()->with('user')->get()
            ->filter(fn ($m) => $m->user)
            ->map(fn ($m) => ['id' => $m->user->id, 'name' => $m->user->name]);

        return MentionParser::detect($body, $members)
            ->reject(fn ($id) => $id == auth()->user()->id)
            ->values();
    }

    /**
     * Notifies every other active member of the group - both the in-app
     * notification center and a browser push. Best-effort -
     * NotificationService swallows its own failures. Whoever was
     * @-mentioned gets their own copy in the Inbox instead of the Groups
     * folder (Pablo, 2026-09-14: a direct ping reads as personal, not
     * general group chatter) - everyone else's copy still goes to Groups.
     */
    private function notifyNewMessage(Group $group, GroupMessage $message, $mentionedIds)
    {
        $body = $message->body ? Str::limit($message->body, 100) : 'Sent a photo';

        NotificationService::notify(
            $group->activeMembers()->pluck('user_id'),
            $group->name,
            auth()->user()->name . ': ' . $body,
            route('Groups.show', ['group' => $group->slug]),
            auth()->user()->id,
            auth()->user()->id,
            $group->id,
            $mentionedIds
        );
    }

    /**
     * @Name in a chat message still goes to the whole group as a normal
     * message (notifyNewMessage above already put it in every member's
     * Inbox/Groups folder, and for the mentioned person specifically, the
     * Inbox) - this only adds the "ping them right now" layer for whoever
     * was actually named: an immediate WhatsApp message, gated on their
     * own opt-in, a phone number on file, and a mention Content template
     * actually existing and being approved (Twilio Content API/
     * content.twilio.com - no mention template has been created there
     * yet, only the trip-reminder one, so this no-ops until
     * TWILIO_WHATSAPP_MENTION_SID is set - see WhatsAppService and
     * config/services.php). No separate email.
     */
    private function notifyMentions(Group $group, string $body, $mentionedIds)
    {
        $contentSid = config('services.whatsapp.mention_content_sid');
        if (!$contentSid || $mentionedIds->isEmpty()) {
            return;
        }

        $snippet = Str::limit($body, 100);
        $url = route('Groups.show', ['group' => $group->slug]);

        foreach ($mentionedIds as $userId) {
            $user = User::find($userId);
            if (!$user || !$user->phone || !$user->whatsapp_notifications || $group->isMemberMuted($userId)) {
                continue;
            }

            // Placeholder variable keys/order - line these up with whatever
            // the mention template actually asks for once it's created and
            // approved (see the docblock above).
            WhatsAppService::sendTemplate($user->phone, $contentSid, [
                '1' => auth()->user()->name,
                '2' => $group->name,
                '3' => $snippet,
                '4' => $url,
            ]);
        }
    }
}

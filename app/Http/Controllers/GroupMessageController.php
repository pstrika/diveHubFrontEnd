<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupMessage;
use App\Models\GroupMessagePhoto;
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
        ]);

        // Photos are uploaded ahead of time via the chat dropzone (see
        // GroupController::uploadImage); only paths already stored under this
        // group's chat folder are trusted here.
        $photoPaths = collect($request->input('existing_photos', []))
            ->filter(fn ($path) => str_starts_with($path, 'img/groups/' . $group->id . '/chat/'))
            ->values();

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

        $this->notifyNewMessage($group, $message);
        $this->notifyMentions($group, $request->body ?? '');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('Groups.show', ['group' => $group->slug])->with('msg', 'Message posted!');
    }

    /**
     * Polled by the chat UI every few seconds. Returns the rendered messages
     * partial plus a count so the client only re-renders when it changes.
     */
    public function poll($groupSlug)
    {
        $group = Group::where('slug', $groupSlug)->firstOrFail();

        if (!$group->isMember(auth()->user()->id)) {
            abort(403);
        }

        $messages = $group->messages()->with(['user', 'photos'])->orderBy('created_at')->get();

        return response()->json([
            'count' => $messages->count(),
            'html' => view('pages.Groups.partials.messages', compact('messages'))->render(),
        ]);
    }

    /**
     * Notifies every other active member of the group - both the in-app
     * notification center and a browser push. Best-effort -
     * NotificationService swallows its own failures.
     */
    private function notifyNewMessage(Group $group, GroupMessage $message)
    {
        $body = $message->body ? Str::limit($message->body, 100) : 'Sent a photo';

        NotificationService::notify(
            $group->activeMembers()->pluck('user_id'),
            $group->name,
            auth()->user()->name . ': ' . $body,
            route('Groups.show', ['group' => $group->slug]),
            auth()->user()->id,
            auth()->user()->id,
            $group->id
        );
    }

    /**
     * @Name in a chat message still goes to the whole group as a normal
     * message (notifyNewMessage above already put it in every member's
     * Inbox/Groups folder) - this only adds the "ping them right now"
     * layer for whoever was actually named: an immediate WhatsApp message,
     * gated on their own opt-in, a phone number on file, and a mention
     * Content template actually existing and being approved (Twilio
     * Content API/content.twilio.com - no mention template has been
     * created there yet, only the trip-reminder one, so this no-ops until
     * TWILIO_WHATSAPP_MENTION_SID is set - see WhatsAppService and
     * config/services.php). No separate email or extra in-app row; the
     * one from notifyNewMessage covers it.
     */
    private function notifyMentions(Group $group, string $body)
    {
        $contentSid = config('services.whatsapp.mention_content_sid');
        if (!$contentSid || trim($body) === '') {
            return;
        }

        $members = $group->activeMembers()->with('user')->get()
            ->filter(fn ($m) => $m->user)
            ->map(fn ($m) => ['id' => $m->user->id, 'name' => $m->user->name]);

        $mentionedIds = MentionParser::detect($body, $members)
            ->reject(fn ($id) => $id == auth()->user()->id);

        if ($mentionedIds->isEmpty()) {
            return;
        }

        $snippet = Str::limit($body, 100);
        $url = route('Groups.show', ['group' => $group->slug]);

        foreach ($mentionedIds as $userId) {
            $user = User::find($userId);
            if (!$user || !$user->phone || !$user->whatsapp_notifications) {
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

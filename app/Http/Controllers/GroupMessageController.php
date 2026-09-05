<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupMessage;
use App\Models\GroupMessagePhoto;
use Illuminate\Http\Request;

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
}

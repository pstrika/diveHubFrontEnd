<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupMessage;
use App\Models\GroupMessagePhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'image|max:10240', // 10MB per photo
        ]);

        if (empty($request->body) && !$request->hasFile('photos')) {
            return redirect()->back()->with('msg', 'Write something or attach a photo.');
        }

        $message = GroupMessage::create([
            'group_id' => $group->id,
            'user_id' => auth()->user()->id,
            'body' => $request->body,
        ]);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $filename = time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
                Storage::disk('siteAssets')->putFileAs('img/groups/' . $group->id, $photo, $filename);

                GroupMessagePhoto::create([
                    'group_message_id' => $message->id,
                    'file' => 'img/groups/' . $group->id . '/' . $filename,
                ]);
            }
        }

        return redirect()->route('Groups.show', ['group' => $group->slug]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use Illuminate\Http\Request;

class GroupInviteController extends Controller
{
    /**
     * Simple name/email search for the admin's "invite someone" box,
     * excluding users who are already a member or already invited.
     */
    public function search(Request $request, $groupSlug)
    {
        $group = Group::where('slug', $groupSlug)->firstOrFail();

        if (!$group->isAdmin(auth()->user()->id)) {
            abort(403);
        }

        $q = trim((string) $request->input('q'));
        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $existingUserIds = $group->members()->pluck('user_id');

        $users = User::where(function ($query) use ($q) {
                $query->where('name', 'LIKE', "%$q%")
                    ->orWhere('email', 'LIKE', "%$q%");
            })
            ->whereNotIn('id', $existingUserIds)
            ->take(10)
            ->get(['id', 'name', 'email', 'picture']);

        return response()->json($users);
    }

    public function invite(Request $request, $groupSlug)
    {
        $group = Group::where('slug', $groupSlug)->firstOrFail();

        if (!$group->isAdmin(auth()->user()->id)) {
            abort(403);
        }

        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        if ($group->members()->where('user_id', $request->user_id)->exists()) {
            return redirect()->back()->with('msg', 'That user is already a member or has a pending invite.');
        }

        GroupMember::create([
            'group_id' => $group->id,
            'user_id' => $request->user_id,
            'role' => 'member',
            'status' => 'invited',
            'invited_by' => auth()->user()->id,
        ]);

        return redirect()->back()->with('msg', 'Invite sent!');
    }

    public function accept($memberId)
    {
        $member = GroupMember::where('user_id', auth()->user()->id)->findOrFail($memberId);

        $member->status = 'active';
        $member->save();

        return redirect()->route('Groups.show', ['group' => $member->group->slug])
            ->with('msg', 'Welcome to ' . $member->group->name . '!');
    }

    public function decline($memberId)
    {
        $member = GroupMember::where('user_id', auth()->user()->id)->findOrFail($memberId);
        $member->delete();

        return redirect()->route('MyGroups')->with('msg', 'Invite declined.');
    }
}

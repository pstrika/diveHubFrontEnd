<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GroupController extends Controller
{
    public function myGroups()
    {
        $userId = auth()->user()->id;

        $groups = Group::whereHas('members', function ($q) use ($userId) {
            $q->where('user_id', $userId)->where('status', 'active');
        })->get();

        $invites = GroupMember::with('group')
            ->where('user_id', $userId)
            ->where('status', 'invited')
            ->get();

        $SEO = [
            "robots" => "noindex, nofollow",
        ];

        return view('pages.Groups.MyGroups', compact('groups', 'invites', 'SEO'));
    }

    public function create()
    {
        $SEO = [
            "robots" => "noindex, nofollow",
        ];

        return view('pages.Groups.Create', compact('SEO'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|min:3|max:150',
            'description' => 'nullable|max:2000',
        ]);

        $base = Str::slug($request->name);
        $slug = $base;
        $suffix = 2;
        while (Group::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $suffix;
            $suffix++;
        }

        $group = Group::create([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'created_by' => auth()->user()->id,
        ]);

        GroupMember::create([
            'group_id' => $group->id,
            'user_id' => auth()->user()->id,
            'role' => 'admin',
            'status' => 'active',
        ]);

        return redirect()->route('Groups.show', ['group' => $group->slug])
            ->with('msg', 'Group "' . $group->name . '" created!');
    }

    public function show(Request $request, $groupSlug)
    {
        $group = Group::where('slug', $groupSlug)->firstOrFail();
        $userId = auth()->user()->id;

        if (!$group->isMember($userId)) {
            abort(403, "You're not a member of this group.");
        }

        $isAdmin = $group->isAdmin($userId);

        $members = $group->activeMembers()->with('user')->get();

        $dives = $group->dives()
            ->whereDate('date', '>=', now())
            ->orderBy('date')
            ->with('rsvps.user')
            ->get();

        foreach ($dives as $dive) {
            $dive->liveTrip = Trip::tripInGroupDive($dive);
        }

        // "Add a dive" trip browser: pick a date, see that day's live trips.
        $addDiveDate = $request->query('add_dive_date');
        $tripsForDate = null;
        if ($addDiveDate) {
            $tripsForDate = Trip::where('date', $addDiveDate)
                ->where('siteIdStatus', 'confirmed')
                ->get()->sortBy('departureTime');
        }

        $messages = $group->messages()->with(['user', 'photos'])->orderBy('created_at')->get();

        $SEO = [
            "robots" => "noindex, nofollow",
        ];

        return view('pages.Groups.Show', compact('group', 'isAdmin', 'members', 'dives', 'messages', 'addDiveDate', 'tripsForDate', 'SEO'));
    }

    public function removeMember(Request $request, $groupSlug, $memberId)
    {
        $group = Group::where('slug', $groupSlug)->firstOrFail();

        if (!$group->isAdmin(auth()->user()->id)) {
            abort(403);
        }

        $member = GroupMember::where('group_id', $group->id)->findOrFail($memberId);

        // Don't allow removing the last admin.
        if ($member->role === 'admin' && $group->members()->where('role', 'admin')->where('status', 'active')->count() <= 1) {
            return redirect()->back()->with('msg', 'A group must keep at least one admin.');
        }

        $member->delete();

        return redirect()->back()->with('msg', 'Member removed.');
    }

    public function destroy($groupSlug)
    {
        $group = Group::where('slug', $groupSlug)->firstOrFail();

        if (!$group->isAdmin(auth()->user()->id)) {
            abort(403);
        }

        $groupName = $group->name;

        foreach ($group->messages()->with('photos')->get() as $message) {
            foreach ($message->photos as $photo) {
                $photo->deletePhoto();
            }
            $message->delete();
        }

        foreach ($group->dives as $dive) {
            $dive->rsvps()->delete();
            $dive->delete();
        }

        $group->members()->delete();
        $group->delete();

        return redirect()->route('MyGroups')->with('msg', 'Group "' . $groupName . '" was deleted.');
    }
}

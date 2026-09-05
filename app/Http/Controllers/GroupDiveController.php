<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupDive;
use App\Models\GroupDiveRsvp;
use App\Models\Trip;
use Illuminate\Http\Request;

class GroupDiveController extends Controller
{
    public function store(Request $request, $groupSlug)
    {
        $group = Group::where('slug', $groupSlug)->firstOrFail();

        if (!$group->isMember(auth()->user()->id)) {
            abort(403);
        }

        $request->validate([
            'tripId' => 'required|integer',
            'notes' => 'nullable|max:1000',
        ]);

        $trip = Trip::findOrFail($request->tripId);

        $dive = GroupDive::create([
            'group_id' => $group->id,
            'created_by' => auth()->user()->id,
            'operatorId' => $trip->operatorId,
            'date' => $trip->date,
            'time' => $trip->departureTime,
            'tripName' => $trip->tripName,
            'notes' => $request->notes,
        ]);

        // The person who added the dive is automatically going.
        GroupDiveRsvp::create([
            'group_dive_id' => $dive->id,
            'user_id' => auth()->user()->id,
        ]);

        return redirect()->route('Groups.show', ['group' => $group->slug])
            ->with('msg', 'Dive added to the group calendar!');
    }

    public function join($diveId)
    {
        $dive = GroupDive::findOrFail($diveId);
        $group = $dive->group;

        if (!$group->isMember(auth()->user()->id)) {
            abort(403);
        }

        if (!$dive->isGoing(auth()->user()->id)) {
            GroupDiveRsvp::create([
                'group_dive_id' => $dive->id,
                'user_id' => auth()->user()->id,
            ]);
        }

        return redirect()->back();
    }

    public function leave($diveId)
    {
        $dive = GroupDive::findOrFail($diveId);
        $group = $dive->group;

        if (!$group->isMember(auth()->user()->id)) {
            abort(403);
        }

        GroupDiveRsvp::where('group_dive_id', $dive->id)
            ->where('user_id', auth()->user()->id)
            ->delete();

        return redirect()->back();
    }
}

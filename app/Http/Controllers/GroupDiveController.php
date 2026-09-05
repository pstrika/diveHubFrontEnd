<?php

namespace App\Http\Controllers;

use App\Models\Event;
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
        $this->addRsvp($dive, auth()->user()->id);

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

        $this->addRsvp($dive, auth()->user()->id);

        return redirect()->back()->with('msg', "You're in! Added to your personal calendar too.");
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

        // Remove the matching auto-added entry from their personal calendar too.
        Event::where('userId', auth()->user()->id)
            ->where('date', $dive->date)
            ->where('time', $dive->time)
            ->where('operatorId', $dive->operatorId)
            ->where('tripName', $dive->tripName)
            ->delete();

        return redirect()->back()->with('msg', 'You are no longer going on this dive.');
    }

    /**
     * RSVPs the user to a group dive, and mirrors it into their personal
     * calendar (Event) the same way EventController::addEventToCalendar
     * does for a directly-added trip, so "going" on a group dive shows up
     * everywhere the user tracks their dives.
     */
    private function addRsvp(GroupDive $dive, $userId)
    {
        if (!$dive->isGoing($userId)) {
            GroupDiveRsvp::create([
                'group_dive_id' => $dive->id,
                'user_id' => $userId,
            ]);
        }

        $alreadyOnPersonalCalendar = Event::where('userId', $userId)
            ->where('date', $dive->date)
            ->where('time', $dive->time)
            ->where('operatorId', $dive->operatorId)
            ->where('tripName', $dive->tripName)
            ->exists();

        if (!$alreadyOnPersonalCalendar) {
            Event::create([
                'userId' => $userId,
                'operatorId' => $dive->operatorId,
                'date' => $dive->date,
                'time' => $dive->time,
                'tripName' => $dive->tripName,
                'booked' => false,
            ]);
        }
    }
}

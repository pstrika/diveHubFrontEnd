<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Group;
use App\Models\GroupDive;
use App\Models\GroupDiveRsvp;
use App\Models\Trip;
use App\Services\GroupDiveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroupDiveController extends Controller
{
    public function store(Request $request, $groupSlug)
    {
        $group = Group::where('slug', $groupSlug)->firstOrFail();

        if (!$group->isMember(auth()->user()->id) || !$group->canAddDives(auth()->user()->id)) {
            abort(403);
        }

        $request->validate([
            'tripId' => 'required|integer',
            'notes' => 'nullable|max:1000',
        ]);

        $trip = Trip::findOrFail($request->tripId);

        $alreadyInGroup = $group->dives()
            ->where('operatorId', $trip->operatorId)
            ->where('date', $trip->date)
            ->where('time', $trip->departureTime)
            ->where('tripName', $trip->tripName)
            ->exists();

        if ($alreadyInGroup) {
            return redirect()->back()->with('msg', 'That trip is already on this group\'s calendar.');
        }

        $dive = (new GroupDiveService())->createFromTrip($group, $trip, auth()->user()->id, $request->notes);

        // The person who added the dive is automatically going.
        $this->addRsvp($dive, auth()->user()->id);

        return redirect()->route('Groups.show', ['group' => $group->slug])
            ->with('msg', 'Dive added to the group calendar!');
    }

    /**
     * A dive not backed by a scraped Trip (e.g. a private charter, or a site
     * not yet in our database). Since there's no live Trip to re-resolve, all
     * display fields are stored directly on the GroupDive row.
     */
    public function storeCustom(Request $request, $groupSlug)
    {
        $group = Group::where('slug', $groupSlug)->firstOrFail();

        if (!$group->isMember(auth()->user()->id) || !$group->canAddDives(auth()->user()->id)) {
            abort(403);
        }

        $request->validate([
            'date' => 'required|date',
            'time' => 'nullable',
            'operatorId' => 'nullable|integer|exists:mysql_trips.operators,id',
            'departingFrom' => 'nullable|max:150',
            'siteId' => 'nullable|integer|exists:mysql_trips.sites,id',
            'notes' => 'nullable|max:1000',
        ]);

        $site = $request->siteId ? \App\Models\Site::find($request->siteId) : null;
        $operator = $request->operatorId ? \App\Models\Operator::find($request->operatorId) : null;

        $tripName = $site->name ?? $operator->operatorName ?? 'Custom Dive';

        $dive = GroupDive::create([
            'group_id' => $group->id,
            'created_by' => auth()->user()->id,
            'operatorId' => $request->operatorId,
            'date' => $request->date,
            'time' => $request->time,
            'tripName' => $tripName,
            'siteId' => $request->siteId,
            'departingFrom' => $request->departingFrom,
            'notes' => $request->notes,
            'is_custom' => true,
        ]);

        $this->addRsvp($dive, auth()->user()->id);
        $diveService = new GroupDiveService();
        $diveService->postDiveToFacebook($group, $dive);
        $diveService->notifyNewDive($group, $dive, auth()->user()->id);

        return redirect()->route('Groups.show', ['group' => $group->slug])
            ->with('msg', 'Custom dive added to the group calendar!');
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

        // Only the entry this dive created (or adopted) - see addRsvp(). A
        // trip the diver saved separately from search has group_dive_id
        // null and is left alone (Pablo, 2026-09-19: this used to delete by
        // a blind date/time/operator/name match, which could wipe an
        // unrelated manual save of the same trip).
        Event::where('userId', auth()->user()->id)
            ->where('group_dive_id', $dive->id)
            ->delete();

        return redirect()->back()->with('msg', 'You are no longer going on this dive.');
    }

    /**
     * RSVPs the user to a group dive, and mirrors it into their personal
     * calendar (Event) the same way EventController::addEventToCalendar
     * does for a directly-added trip, so "going" on a group dive shows up
     * everywhere the user tracks their dives. Stamps events.group_dive_id
     * so leave()/EventController::removeFromCalendar() can find exactly
     * this row later instead of guessing by composite key (Pablo,
     * 2026-09-19).
     */
    private function addRsvp(GroupDive $dive, $userId)
    {
        if (!$dive->isGoing($userId)) {
            GroupDiveRsvp::create([
                'group_dive_id' => $dive->id,
                'user_id' => $userId,
            ]);
        }

        // Already linked to this dive - nothing to do.
        if (Event::where('userId', $userId)->where('group_dive_id', $dive->id)->exists()) {
            return;
        }

        // The diver had already saved this exact trip from search. Adopt
        // that row rather than creating a second one: one trip, one
        // calendar entry, and "Leaving" now takes it off the calendar the
        // way the product is supposed to work.
        $existing = Event::where('userId', $userId)
            ->whereDate('date', $dive->date)
            ->where('time', $dive->time)
            ->where('operatorId', $dive->operatorId)
            ->where('tripName', $dive->tripName)
            ->whereNull('group_dive_id')
            ->first();

        if ($existing) {
            $existing->group_dive_id = $dive->id;
            $existing->save();
            return;
        }

        Event::create([
            'userId' => $userId,
            'operatorId' => $dive->operatorId,
            'date' => $dive->date,
            'time' => $dive->time,
            'tripName' => $dive->tripName,
            'group_dive_id' => $dive->id,
            'booked' => false,
        ]);
    }

    /**
     * Best-effort removal of the Facebook post when its dive is deleted.
     * Same non-blocking contract as postDiveToFacebook().
     */
    private function deleteDiveFacebookPost(Group $group, GroupDive $dive)
    {
        if (!$group->isFacebookConnected() || !$dive->fb_post_id) {
            return;
        }

        try {
            $response = Http::delete('https://graph.facebook.com/' . GroupFacebookController::GRAPH_VERSION . '/' . $dive->fb_post_id, [
                'access_token' => $group->fb_page_access_token,
            ]);

            if ($response->failed()) {
                Log::error('Facebook post delete failed for dive ' . $dive->id . ': ' . $response->body());
            }
        } catch (\Throwable $e) {
            Log::error('Facebook post delete exception for dive ' . $dive->id . ': ' . $e->getMessage());
        }
    }

    /**
     * Admin-only: removes a dive from the group calendar entirely, along
     * with every attendee's personal-calendar entry for it.
     */
    public function destroy($diveId)
    {
        $dive = GroupDive::findOrFail($diveId);
        $group = $dive->group;

        if (!$group->isAdmin(auth()->user()->id)) {
            abort(403);
        }

        if ($dive->rsvps()->count() > 0) {
            return redirect()->route('Groups.show', ['group' => $group->slug])
                ->with('msg', 'Can\'t remove this dive - people are still going. Ask them to leave first.');
        }

        $this->deleteDiveFacebookPost($group, $dive);

        // Normally a no-op (the guard above already refuses to run while
        // anyone is RSVP'd), but a cheap indexed delete that closes the gap
        // for any row orphaned before events.group_dive_id existed, or by a
        // race (Pablo, 2026-09-19).
        Event::where('group_dive_id', $dive->id)->delete();

        $dive->delete();

        return redirect()->route('Groups.show', ['group' => $group->slug])
            ->with('msg', 'Dive removed from the group calendar.');
    }
}

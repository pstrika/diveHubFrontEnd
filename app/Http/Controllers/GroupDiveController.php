<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Group;
use App\Models\GroupDive;
use App\Models\GroupDiveRsvp;
use App\Models\Photo;
use App\Models\Trip;
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

        $dive = GroupDive::create([
            'group_id' => $group->id,
            'created_by' => auth()->user()->id,
            'operatorId' => $trip->operatorId,
            'date' => $trip->date,
            'time' => $trip->departureTime,
            'tripName' => $trip->tripName,
            'siteId' => !empty($trip->site[0]) ? $trip->site[0]->id : null,
            'notes' => $request->notes,
        ]);

        // The person who added the dive is automatically going.
        $this->addRsvp($dive, auth()->user()->id);
        $this->postDiveToFacebook($group, $dive);

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
        $this->postDiveToFacebook($group, $dive);

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

    /**
     * Best-effort announcement to the group's linked Facebook Page, if any.
     * Must never block adding the dive - a broken/revoked Page token or a
     * Graph API hiccup just gets logged. Posts as a photo (site photo, then
     * operator logo) when one is available, since photo posts get more
     * reach/engagement; falls back to a plain text post otherwise (e.g. a
     * custom dive with neither a known site nor operator).
     */
    private function postDiveToFacebook(Group $group, GroupDive $dive)
    {
        if (!$group->isFacebookConnected() || !$group->fb_auto_post) {
            return;
        }

        try {
            $when = \Carbon\Carbon::parse($dive->date)->format('l, F j') . ($dive->time ? ' at ' . $dive->time : '');
            $message = "New dive added to \"{$group->name}\": {$dive->tripName}\n{$when}\n\n"
                . route('Groups.show', ['group' => $group->slug]);

            $imageUrl = $this->resolveDiveImageUrl($dive);

            if ($imageUrl) {
                $response = Http::post('https://graph.facebook.com/' . GroupFacebookController::GRAPH_VERSION . '/' . $group->fb_page_id . '/photos', [
                    'url' => $imageUrl,
                    'caption' => $message,
                    'access_token' => $group->fb_page_access_token,
                ]);
            } else {
                $response = Http::post('https://graph.facebook.com/' . GroupFacebookController::GRAPH_VERSION . '/' . $group->fb_page_id . '/feed', [
                    'message' => $message,
                    'access_token' => $group->fb_page_access_token,
                ]);
            }

            if ($response->failed()) {
                Log::error('Facebook post failed for group ' . $group->id . ': ' . $response->body());
                return;
            }

            $postId = $response->json('post_id') ?? $response->json('id');
            if ($postId) {
                $dive->update(['fb_post_id' => $postId]);
            }
        } catch (\Throwable $e) {
            Log::error('Facebook post exception for group ' . $group->id . ': ' . $e->getMessage());
        }
    }

    /**
     * Picks the most relevant public image URL for a dive's Facebook post:
     * a photo of the dive site, then the operator's logo, or null (post
     * plain text) when neither is available - e.g. a custom dive.
     */
    private function resolveDiveImageUrl(GroupDive $dive): ?string
    {
        if ($dive->siteId) {
            $sitePhoto = Photo::where('siteId', $dive->siteId)->first();
            if ($sitePhoto) {
                return asset('assets') . '/img/sites/' . $sitePhoto->file;
            }
        }

        if ($dive->operator && $dive->operator->logoUrl) {
            return asset('assets') . $dive->operator->logoUrl;
        }

        return null;
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

        $dive->delete();

        return redirect()->route('Groups.show', ['group' => $group->slug])
            ->with('msg', 'Dive removed from the group calendar.');
    }
}

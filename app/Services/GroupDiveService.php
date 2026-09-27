<?php

namespace App\Services;

use App\Http\Controllers\GroupFacebookController;
use App\Models\Group;
use App\Models\GroupDive;
use App\Models\Photo;
use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Creating a GroupDive from a live Trip - shared by GroupDiveController's
 * manual "Add a dive" action and ApplyGroupAutoAddRules (Pablo,
 * 2026-09-22: auto-add rules have no human "adder", so this stops short
 * of the RSVP a manual add makes for whoever clicked - callers decide
 * that themselves). Extracted rather than duplicated so the Facebook
 * auto-post and member-notification behavior can never drift between the
 * two paths.
 */
class GroupDiveService
{
    public function createFromTrip(Group $group, Trip $trip, ?int $createdBy, ?string $notes = null): GroupDive
    {
        $dive = GroupDive::create([
            'group_id' => $group->id,
            'created_by' => $createdBy,
            'operatorId' => $trip->operatorId,
            'date' => $trip->date,
            'time' => $trip->departureTime,
            'tripName' => $trip->tripName,
            'siteId' => !empty($trip->site[0]) ? $trip->site[0]->id : null,
            'notes' => $notes,
        ]);

        $this->postDiveToFacebook($group, $dive);
        $this->notifyNewDive($group, $dive, $createdBy);

        return $dive;
    }

    /**
     * Must never block adding the dive - a broken/revoked Page token or a
     * Graph API hiccup just gets logged. Posts as a photo (site photo, then
     * operator logo) when one is available, since photo posts get more
     * reach/engagement; falls back to a plain text post otherwise.
     */
    public function postDiveToFacebook(Group $group, GroupDive $dive): void
    {
        if (!$group->isFacebookConnected() || !$group->fb_auto_post) {
            return;
        }

        try {
            $when = Carbon::parse($dive->date)->format('l, F j') . ($dive->time ? ' at ' . $dive->time : '');
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
     * plain text) when neither is available.
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
     * Notifies every active member of the group - both the in-app
     * notification center and a browser push. $actorId is null for an
     * auto-added dive (nobody to exclude or attribute it to) and the
     * adder's id for a manual one, same behavior as before this was
     * extracted. Best-effort - NotificationService swallows its own
     * failures.
     */
    public function notifyNewDive(Group $group, GroupDive $dive, ?int $actorId): void
    {
        $when = Carbon::parse($dive->date)->format('D, M j') . ($dive->time ? ' at ' . $dive->time : '');

        NotificationService::notify(
            $group->activeMembers()->pluck('user_id'),
            $group->name,
            'New dive added: ' . $dive->tripName . ' - ' . $when,
            route('Groups.show', ['group' => $group->slug]),
            $actorId,
            $actorId,
            $group->id
        );
    }
}

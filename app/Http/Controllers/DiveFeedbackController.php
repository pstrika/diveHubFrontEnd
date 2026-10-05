<?php

namespace App\Http\Controllers;

use App\Models\DiveConditionsReport;
use App\Models\DiverPhoto;
use App\Models\Operator;
use App\Models\OperatorRating;
use App\Models\PostDiveFeedbackRequest;
use App\Models\Site;
use App\Models\SiteComment;
use App\Models\SiteRating;
use App\Models\User;
use App\Support\SitePhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * The post-dive feedback wizard - conditions, photos, rate+review the site,
 * rate the operator - reached via a bare token link sent by
 * App\Console\Commands\SendPostDiveFeedbackRequests, with NO login required.
 *
 * The token itself is the authorization for every write below, same pattern
 * this app already uses for NewsletterController::unsubscribe()'s signed
 * link - a diver clicking from WhatsApp/SMS on their phone may not be
 * logged in there, and forcing a login first would just kill completion.
 * Unlike that link, this one authorizes several writes over up to 30 days,
 * hence the expires_at check on every request (Pablo, 2026-10-04).
 */
class DiveFeedbackController extends Controller
{
    public function show($token)
    {
        $feedbackRequest = PostDiveFeedbackRequest::where('token', $token)->first();

        if (!$feedbackRequest) {
            abort(404);
        }
        if ($feedbackRequest->isExpired()) {
            return view('pages.DiveFeedback.Expired');
        }

        $event = $feedbackRequest->event;
        $site = $feedbackRequest->site_id ? Site::find($feedbackRequest->site_id) : null;
        $operator = $feedbackRequest->operator_id ? Operator::find($feedbackRequest->operator_id) : null;
        $diver = $feedbackRequest->user;

        $conditions = DiveConditionsReport::where('event_id', $feedbackRequest->event_id)
            ->where('user_id', $feedbackRequest->user_id)
            ->first();
        $photosUploaded = $feedbackRequest->photos_uploaded_at !== null;
        // Global exists-check, same as the "Rate" link gating on
        // SiteDetails/OperatorDetails - a site/operator rated from any
        // earlier visit skips that step here too, not just a rating tied to
        // this specific dive.
        $alreadyRatedSite = !$site || SiteRating::where('userId', $feedbackRequest->user_id)->where('siteId', $site->id)->exists();
        $alreadyRatedOperator = !$operator || OperatorRating::where('userId', $feedbackRequest->user_id)->where('operatorId', $operator->id)->exists();

        return view('pages.DiveFeedback.Wizard', [
            'token' => $token,
            'feedbackRequest' => $feedbackRequest,
            'event' => $event,
            'site' => $site,
            'operator' => $operator,
            'diver' => $diver,
            'conditions' => $conditions,
            'photosUploaded' => $photosUploaded,
            'alreadyRatedSite' => $alreadyRatedSite,
            'alreadyRatedOperator' => $alreadyRatedOperator,
        ]);
    }

    public function submitConditions(Request $request, $token)
    {
        $feedbackRequest = $this->resolveOrFail($token);

        $request->validate([
            'visibility_ft' => 'nullable|integer|min:0|max:100',
            'waves_ft' => 'nullable|integer|min:0|max:6',
            'current_strength' => 'nullable|in:' . implode(',', DiveConditionsReport::CURRENT_STRENGTHS),
            'current_direction' => 'nullable|in:' . implode(',', DiveConditionsReport::CURRENT_DIRECTIONS),
        ]);

        DiveConditionsReport::updateOrCreate(
            ['event_id' => $feedbackRequest->event_id, 'user_id' => $feedbackRequest->user_id],
            [
                'site_id' => $feedbackRequest->site_id,
                'visibility_ft' => $request->visibility_ft,
                'waves_ft' => $request->waves_ft,
                'current_strength' => $request->current_strength,
                'current_direction' => $request->current_direction,
            ]
        );

        return response()->json(['success' => true]);
    }

    /**
     * Same storage logic as DiverPhotoController::store() (original kept
     * only until its WebP copies exist, filename prefix, etc.) but scoped
     * to this request's own site_id/user_id rather than auth()->id(), and
     * stamping eventId so the photo is traceable to this specific dive -
     * duplicated rather than extracted into a shared helper to keep that
     * production controller untouched (Pablo, 2026-10-04).
     */
    public function uploadPhoto(Request $request, $token)
    {
        $feedbackRequest = $this->resolveOrFail($token);

        if (!$feedbackRequest->site_id) {
            return response()->json(['success' => false, 'message' => 'No site on file for this dive.'], 422);
        }

        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:20480',
        ]);

        $site = Site::findOrFail($feedbackRequest->site_id);
        $file = $request->file('photo');
        $filename = 'diver_' . $site->id . '_' . time() . '_' . uniqid() . '.' . $file->extension();
        Storage::disk('siteAssets')->putFileAs('img/sites', $file, $filename);
        if (SitePhoto::makeCopies($filename) !== false) {
            Storage::disk('siteAssets')->delete('img/sites/' . $filename);
        }

        DiverPhoto::create([
            'siteId' => $site->id,
            'userId' => $feedbackRequest->user_id,
            'eventId' => $feedbackRequest->event_id,
            'file' => $filename,
            'status' => 'pending',
        ]);

        if (!$feedbackRequest->photos_uploaded_at) {
            $feedbackRequest->photos_uploaded_at = now();
            $feedbackRequest->save();
        }

        return response()->json(['success' => true]);
    }

    public function requestPhotoReminder($token)
    {
        $feedbackRequest = $this->resolveOrFail($token);
        $feedbackRequest->photo_reminder_requested = true;
        $feedbackRequest->save();

        return response()->json(['success' => true]);
    }

    /** Duplicates SiteRatingController::new()'s averaging formula rather than extracting it - see that controller for why. */
    public function submitSiteRating(Request $request, $token)
    {
        $feedbackRequest = $this->resolveOrFail($token);
        if (!$feedbackRequest->site_id) {
            return response()->json(['success' => false], 422);
        }

        $request->validate(['rate' => 'required|integer|min:1|max:5']);

        SiteRating::create([
            'userId' => $feedbackRequest->user_id,
            'siteId' => $feedbackRequest->site_id,
            'starRating' => $request->rate,
        ]);

        $site = Site::findOrFail($feedbackRequest->site_id);
        $newRating = ($site->rate * $site->votes + $request->rate) / ($site->votes + 1);
        $site->update(['rate' => $newRating, 'votes' => $site->votes + 1]);

        return response()->json(['success' => true]);
    }

    public function submitSiteReview(Request $request, $token)
    {
        $feedbackRequest = $this->resolveOrFail($token);
        if (!$feedbackRequest->site_id) {
            return response()->json(['success' => false], 422);
        }

        $request->validate(['review' => 'required|string|max:2000']);

        SiteComment::create([
            'siteId' => $feedbackRequest->site_id,
            'userId' => $feedbackRequest->user_id,
            'comment' => $request->review,
        ]);

        return response()->json(['success' => true]);
    }

    /** Duplicates OperatorRatingController::new()'s averaging formula - same reasoning as submitSiteRating(). */
    public function submitOperatorRating(Request $request, $token)
    {
        $feedbackRequest = $this->resolveOrFail($token);
        if (!$feedbackRequest->operator_id) {
            return response()->json(['success' => false], 422);
        }

        $request->validate(['rate' => 'required|integer|min:1|max:5']);

        OperatorRating::create([
            'userId' => $feedbackRequest->user_id,
            'operatorId' => $feedbackRequest->operator_id,
            'starRating' => $request->rate,
        ]);

        $operator = Operator::findOrFail($feedbackRequest->operator_id);
        $newRating = ($operator->rate * $operator->votes + $request->rate) / ($operator->votes + 1);
        $operator->update(['rate' => $newRating, 'votes' => $operator->votes + 1]);

        return response()->json(['success' => true]);
    }

    public function finish($token)
    {
        $feedbackRequest = $this->resolveOrFail($token);
        if (!$feedbackRequest->completed_at) {
            $feedbackRequest->completed_at = now();
            $feedbackRequest->save();
        }

        return response()->json(['success' => true]);
    }

    private function resolveOrFail(string $token): PostDiveFeedbackRequest
    {
        $feedbackRequest = PostDiveFeedbackRequest::where('token', $token)->first();
        if (!$feedbackRequest || $feedbackRequest->isExpired()) {
            abort(410, 'This link has expired.');
        }

        return $feedbackRequest;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\SiteVideo;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Videos tab of the Picture Management console (Pablo, 2026-10-20): the
 * same approve / reject / remove flow DiverPhotoAdminController runs for
 * pictures. Approving makes the video publicly playable; rejecting or
 * removing makes it private again.
 */
class SiteVideoAdminController extends Controller
{
    public function index()
    {
        $this->authorize('manage-items', User::class);

        $pending = SiteVideo::with(['site', 'user'])->pending()->latest()->get()->each->refreshStream();
        $history = SiteVideo::with(['site', 'user', 'reviewer'])->whereIn('status', ['approved', 'rejected'])->latest('reviewedAt')->get();

        return view('pages.Admin.SiteVideos.Index', compact('pending', 'history'));
    }

    public function approve(Request $request, SiteVideo $video)
    {
        $this->authorize('manage-items', User::class);
        $validated = $request->validate(['desc' => 'nullable|string|max:150']);
        $video->approve(auth()->id(), $validated['desc'] ?? null);
        return back()->with('success', 'Video approved.');
    }

    public function reject(SiteVideo $video)
    {
        $this->authorize('manage-items', User::class);
        $video->reject(auth()->id());
        return back()->with('success', 'Video rejected.');
    }

    public function destroy(SiteVideo $video)
    {
        $this->authorize('manage-items', User::class);
        $video->deleteWithStream();
        return back()->with('success', 'Video removed.');
    }
}

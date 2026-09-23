<?php

namespace App\Http\Controllers;

use App\Models\DiverPhoto;
use App\Models\User;

/**
 * Picture Management admin console (Pablo, 2026-09-23: "Admins should
 * have a simple console... platform admins will approve or reject
 * pictures. Show also a history of all pictures approved so we can have
 * the ability to remove pictures"). Same manage-items gate SiteController's
 * other dive-site-admin actions use.
 */
class DiverPhotoAdminController extends Controller
{
    public function index()
    {
        $this->authorize('manage-items', User::class);

        $pending = DiverPhoto::with(['site', 'user'])->pending()->latest()->get();
        $history = DiverPhoto::with(['site', 'user', 'reviewer'])->whereIn('status', ['approved', 'rejected'])->latest('reviewedAt')->get();

        return view('pages.Admin.DiverPhotos.Index', compact('pending', 'history'));
    }

    public function approve(DiverPhoto $photo)
    {
        $this->authorize('manage-items', User::class);

        $photo->update([
            'status' => 'approved',
            'reviewedBy' => auth()->id(),
            'reviewedAt' => now(),
        ]);

        return back()->with('success', 'Picture approved.');
    }

    public function reject(DiverPhoto $photo)
    {
        $this->authorize('manage-items', User::class);

        $photo->update([
            'status' => 'rejected',
            'reviewedBy' => auth()->id(),
            'reviewedAt' => now(),
        ]);

        return back()->with('success', 'Picture rejected.');
    }

    /** Admin can remove any picture, approved or not - not just reject a pending one. */
    public function destroy(DiverPhoto $photo)
    {
        $this->authorize('manage-items', User::class);

        $photo->deleteWithFile();

        return back()->with('success', 'Picture removed.');
    }
}

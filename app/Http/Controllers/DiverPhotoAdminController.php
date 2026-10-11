<?php

namespace App\Http\Controllers;

use App\Models\DiverPhoto;
use App\Models\User;
use Illuminate\Http\Request;

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

    public function approve(Request $request, DiverPhoto $photo)
    {
        $this->authorize('manage-items', User::class);

        // Admin-written, not diver-written (Pablo, 2026-10-11: "I don't
        // want the divers to add a description, I think that's something
        // that the admin (me) should do"). Optional - approving without
        // one leaves SiteDetails.blade.php's existing generic fallback
        // alt text in place, same as before this field existed.
        $validated = $request->validate([
            'desc' => 'nullable|string|max:150',
        ]);

        $photo->update([
            'status' => 'approved',
            'desc' => $validated['desc'] ?? null,
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

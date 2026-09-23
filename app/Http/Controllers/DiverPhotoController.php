<?php

namespace App\Http\Controllers;

use App\Models\DiverPhoto;
use App\Models\Site;
use App\Models\User;
use App\Services\NotificationService;
use App\Support\SitePhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Registered divers uploading their own site pictures, and removing their
 * own uploads (Pablo, 2026-09-23: "members should also be able to remove
 * their own pictures"). Admin review lives in DiverPhotoAdminController.
 */
class DiverPhotoController extends Controller
{
    public function store(Request $request, $siteId)
    {
        abort_unless(auth()->user()->isNotGuest(), 403);

        $site = Site::findOrFail($siteId);

        $request->validate([
            // No HEIC/HEIF: GD (App\Support\SitePhoto) can't decode it, and
            // browsers can't render it inline either - the original would
            // upload fine but look "broken" everywhere it's shown.
            'photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:8192',
        ]);

        $file = $request->file('photo');
        $filename = 'diver_' . $site->id . '_' . time() . '_' . uniqid() . '.' . $file->extension();
        Storage::disk('siteAssets')->putFileAs('img/sites', $file, $filename);
        // Unlike admin-uploaded site photos, the original camera file is
        // never kept - divers upload from phones, originals run several MB
        // each, and there was no admin curation step trimming the library
        // (Pablo, 2026-09-24: "we will be taking a lot of server space
        // otherwise"). Only delete it once the small WebP copies actually
        // exist - SitePhoto::web()/thumb() fall back to the original when
        // no copy exists, so keeping it on a resize failure means the
        // picture still shows instead of breaking silently.
        if (SitePhoto::makeCopies($filename) !== false) {
            Storage::disk('siteAssets')->delete('img/sites/' . $filename);
        }

        DiverPhoto::create([
            'siteId' => $site->id,
            'userId' => auth()->id(),
            'file' => $filename,
            'status' => 'pending',
        ]);

        $this->notifyAdmins($site);

        return back()->with('msg', "Thanks! Your picture is pending review and will show on {$site->name}'s page once approved.");
    }

    public function destroy($id)
    {
        $photo = DiverPhoto::findOrFail($id);
        abort_unless($photo->userId === auth()->id() || auth()->user()->isAdmin(), 403);

        $photo->deleteWithFile();

        return back()->with('msg', 'Picture removed.');
    }

    /** Same "notify every admin" pattern as App\Support\ConversationLog - the Picture Management console otherwise has no other "someone submitted a photo" alert. */
    private function notifyAdmins(Site $site): void
    {
        $adminIds = User::where('role_id', 1)->pluck('id');
        if ($adminIds->isEmpty()) {
            return;
        }

        NotificationService::notify(
            $adminIds,
            'New diver picture pending review',
            "A diver uploaded a picture of {$site->name}.",
            route('DiverPhotos.manage.index')
        );
    }
}

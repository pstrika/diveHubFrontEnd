<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Trip;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GroupController extends Controller
{
    /**
     * Selectable "Calling Card" avatar images (public/assets/img/icons/CC_*),
     * keyed by the token stored in Group::avatar. Replaces free-form avatar
     * uploads - every group picks one of these instead.
     */
    public const CALLING_CARDS = [
        'Recreational' => 'Recreational',
        'Technical' => 'Technical',
        'Cave' => 'Cave',
        'Wreck' => 'Wreck',
        'Shark' => 'Shark',
        'Lobster' => 'Lobster',
        'Lionfish' => 'Lionfish',
        'Beach' => 'Beach',
        'Training' => 'Training',
        'ReefConservartion' => 'Reef Conservation',
    ];

    public function myGroups()
    {
        $userId = auth()->user()->id;

        $groups = Group::whereHas('members', function ($q) use ($userId) {
            $q->where('user_id', $userId)->where('status', 'active');
        })->with('activeMembers.user')->get();

        $invites = GroupMember::with('group')
            ->where('user_id', $userId)
            ->where('status', 'invited')
            ->get();

        $SEO = [
            "robots" => "noindex, nofollow",
        ];

        return view('pages.Groups.MyGroups', compact('groups', 'invites', 'SEO'));
    }

    public function create()
    {
        $SEO = [
            "robots" => "noindex, nofollow",
        ];

        return view('pages.Groups.Create', compact('SEO'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|min:3|max:150',
            'description' => 'nullable|max:2000',
        ]);

        $base = Str::slug($request->name);
        $slug = $base;
        $suffix = 2;
        while (Group::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $suffix;
            $suffix++;
        }

        $group = Group::create([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'avatar' => 'img/icons/CC_Recreational.webp',
            'created_by' => auth()->user()->id,
        ]);

        GroupMember::create([
            'group_id' => $group->id,
            'user_id' => auth()->user()->id,
            'role' => 'admin',
            'status' => 'active',
        ]);

        return redirect()->route('Groups.show', ['group' => $group->slug])
            ->with('msg', 'Group "' . $group->name . '" created!');
    }

    public function show(Request $request, $groupSlug)
    {
        $group = Group::where('slug', $groupSlug)->firstOrFail();
        $userId = auth()->user()->id;

        if (!$group->isMember($userId)) {
            abort(403, "You're not a member of this group.");
        }

        $isAdmin = $group->isAdmin($userId);

        $members = $group->activeMembers()->with('user')->get();

        $invitedMembers = $isAdmin
            ? $group->members()->where('status', 'invited')->with('user')->get()
            : collect();

        $dives = $group->dives()
            ->whereDate('date', '>=', now())
            ->orderBy('date')
            ->with(['rsvps.user', 'site', 'operator'])
            ->get();

        foreach ($dives as $dive) {
            $dive->liveTrip = $dive->is_custom ? null : Trip::tripInGroupDive($dive);
        }

        // Existing dives in THIS group, keyed by composite key, so the "Add a
        // dive" browser can hide trips already on this group's calendar.
        $existingDiveKeys = $group->dives()->get()
            ->map(fn ($d) => $d->operatorId . '|' . $d->date . '|' . $d->time . '|' . $d->tripName)
            ->flip();

        // "Add a dive" trip browser: two independent ways to find a trip -
        // pick a single date, OR search by dive site across all upcoming
        // dates. Whichever the request actually sent wins; they don't combine.
        $addDiveDate = $request->query('add_dive_date');
        $addDiveSite = $request->query('add_dive_site');
        $tripsForDate = null;
        if ($addDiveSite) {
            $tripsQuery = Trip::where('date', '>=', now()->toDateString())
                ->where('siteIdStatus', 'confirmed')
                ->whereHas('site', function ($q) use ($addDiveSite) {
                    $q->where('name', 'LIKE', "%$addDiveSite%");
                });
            $tripsForDate = $tripsQuery->get()->sortBy(['date', 'departureTime'])->take(50)->map(function ($trip) use ($existingDiveKeys) {
                $trip->alreadyInThisGroup = $existingDiveKeys->has($trip->operatorId . '|' . $trip->date . '|' . $trip->departureTime . '|' . $trip->tripName);
                return $trip;
            });
        } elseif ($addDiveDate) {
            $tripsQuery = Trip::where('date', $addDiveDate)->where('siteIdStatus', 'confirmed');
            $tripsForDate = $tripsQuery->get()->sortBy('departureTime')->map(function ($trip) use ($existingDiveKeys) {
                $trip->alreadyInThisGroup = $existingDiveKeys->has($trip->operatorId . '|' . $trip->date . '|' . $trip->departureTime . '|' . $trip->tripName);
                return $trip;
            });
        }

        $messages = $group->messages()->with(['user', 'photos'])->orderBy('created_at')->get();

        $calendarFeedUrl = route('Groups.feed', ['group' => $group->slug, 'token' => $group->ensureCalendarToken()]);

        // All members (not just admins) need the operators list for the
        // Custom Dive form's operator picker.
        $operators = \App\Models\Operator::orderBy('operatorName')->get(['id', 'operatorName']);
        $favoriteOperatorIds = $isAdmin ? $group->favoriteOperators()->pluck('operators.id')->toArray() : [];

        $SEO = [
            "robots" => "noindex, nofollow",
        ];

        $callingCards = self::CALLING_CARDS;

        $fbFeed = $group->isFacebookConnected()
            ? app(GroupFacebookController::class)->getRecentPosts($group)
            : [];

        return view('pages.Groups.Show', compact('group', 'isAdmin', 'members', 'invitedMembers', 'dives', 'messages', 'addDiveDate', 'addDiveSite', 'tripsForDate', 'calendarFeedUrl', 'callingCards', 'operators', 'favoriteOperatorIds', 'fbFeed', 'SEO'));
    }

    /**
     * Public iCalendar subscription feed for a group's shared calendar - no
     * auth, authenticated purely by the token in the URL, same pattern as
     * the personal calendar feed (EventController::feed).
     */
    public function feed($groupSlug, $token)
    {
        $group = Group::where('slug', $groupSlug)->where('calendar_token', $token)->first();
        if (!$group) {
            abort(404);
        }

        $dives = $group->dives()->whereDate('date', '>=', now())->orderBy('date')->get();

        $tz = 'America/New_York';
        $lines = [
            "BEGIN:VCALENDAR",
            "VERSION:2.0",
            "PRODID:-//DiveHub//GroupCalendar//EN",
            "CALSCALE:GREGORIAN",
            "METHOD:PUBLISH",
            "X-WR-CALNAME:" . $group->name . " - Divers Hub",
            "X-WR-TIMEZONE:{$tz}",
            "BEGIN:VTIMEZONE",
            "TZID:America/New_York",
            "BEGIN:DAYLIGHT",
            "TZOFFSETFROM:-0500",
            "TZOFFSETTO:-0400",
            "TZNAME:EDT",
            "DTSTART:19700308T020000",
            "RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=2SU",
            "END:DAYLIGHT",
            "BEGIN:STANDARD",
            "TZOFFSETFROM:-0400",
            "TZOFFSETTO:-0500",
            "TZNAME:EST",
            "DTSTART:19701101T020000",
            "RRULE:FREQ=YEARLY;BYMONTH=11;BYDAY=1SU",
            "END:STANDARD",
            "END:VTIMEZONE",
        ];

        $stamp = Carbon::now('UTC')->format('Ymd\THis\Z');

        foreach ($dives as $dive) {
            $trip = Trip::tripInGroupDive($dive);
            $operatorName = $trip ? $trip->operatorName : null;

            $start = Carbon::parse(
                Carbon::parse($dive->date)->format('Y-m-d') . ' ' . ($dive->time ?: '00:00'),
                $tz
            );
            $end = (clone $start)->addHours(3);

            $summary = trim($dive->tripName . ($operatorName ? ' — ' . $operatorName : ''));

            $lines[] = "BEGIN:VEVENT";
            $lines[] = "UID:divehub-groupdive-{$dive->id}@divers-hub.com";
            $lines[] = "DTSTAMP:{$stamp}";
            $lines[] = "DTSTART;TZID={$tz}:" . $start->format('Ymd\THis');
            $lines[] = "DTEND;TZID={$tz}:" . $end->format('Ymd\THis');
            $lines[] = "SUMMARY:" . $this->icsEscape($summary);
            if ($operatorName) {
                $lines[] = "LOCATION:" . $this->icsEscape($operatorName);
            }
            $lines[] = "DESCRIPTION:" . $this->icsEscape(($dive->notes ?: '') . "\nEnd time is an estimate (3h).");
            $lines[] = "END:VEVENT";
        }

        $lines[] = "END:VCALENDAR";

        $ics = implode("\r\n", $lines) . "\r\n";

        return response($ics, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'inline; filename="' . $group->slug . '-calendar.ics"',
        ]);
    }

    private function icsEscape($text)
    {
        return str_replace(
            ["\\", ",", ";", "\n", "\r"],
            ["\\\\", "\\,", "\\;", "\\n", ""],
            $text ?? ''
        );
    }

    /**
     * Chunked image upload endpoint (Dropzone.js, same chunking pattern as
     * SiteController::upload) for group banners, avatars and chat photos.
     * Dropzone resizes images client-side before sending, and this still
     * re-encodes server-side as a safety net - together these mean phone
     * camera photos never get rejected for being "too large".
     */
    public function uploadImage(Request $request, $groupSlug)
    {
        $group = Group::where('slug', $groupSlug)->firstOrFail();
        $kind = $request->input('kind');

        if ($kind === 'banner') {
            if (!$group->isAdmin(auth()->user()->id)) {
                abort(403);
            }
        } elseif ($kind === 'chat') {
            if (!$group->isMember(auth()->user()->id)) {
                abort(403);
            }
        } else {
            abort(400, 'Invalid upload kind.');
        }

        $chunkNumber = $request->input('dzchunkindex');
        $totalChunks = $request->input('dztotalchunkcount');
        $fileUuid = $request->input('dzuuid');
        $file = $request->file('img_file');

        if ($chunkNumber === null || $chunkNumber === '') {
            return $this->finalizeGroupUpload($group, $kind, $file->getRealPath());
        }

        Storage::disk('siteAssets')->putFileAs(
            'img/groups/' . $group->id . '/temp/' . $fileUuid,
            $file,
            $chunkNumber
        );

        if ($chunkNumber == $totalChunks - 1) {
            $mergedRelative = 'img/groups/' . $group->id . '/temp_' . $fileUuid . '.upload';
            $mergedFullPath = config('filesystems.disks.siteAssets.root') . '/' . $mergedRelative;
            $this->combineGroupChunks($group->id, $fileUuid, $totalChunks, $mergedFullPath);
            Storage::disk('siteAssets')->deleteDirectory('img/groups/' . $group->id . '/temp/' . $fileUuid);

            $response = $this->finalizeGroupUpload($group, $kind, $mergedFullPath);
            if (file_exists($mergedFullPath)) {
                unlink($mergedFullPath);
            }
            return $response;
        }

        return response()->json(['message' => 'Chunk uploaded successfully']);
    }

    /**
     * Re-orients (EXIF), downsizes and re-encodes as a JPEG, then stores the
     * result and (for banner) attaches it to the group.
     */
    private function finalizeGroupUpload(Group $group, $kind, $sourcePath)
    {
        $maxWidths = ['banner' => 1600, 'chat' => 1200];
        $subdirs = ['banner' => '', 'chat' => '/chat'];

        $filename = $kind . '_' . time() . '_' . uniqid() . '.jpg';
        $dir = 'img/groups/' . $group->id . $subdirs[$kind];
        $fullDir = public_path('assets/' . $dir);
        if (!is_dir($fullDir)) {
            mkdir($fullDir, 0755, true);
        }

        try {
            \Intervention\Image\Facades\Image::make($sourcePath)
                ->orientate()
                ->resize($maxWidths[$kind], null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })
                ->encode('jpg', 85)
                ->save($fullDir . '/' . $filename);
        } catch (\Intervention\Image\Exception\NotReadableException $e) {
            // Most likely an unconverted HEIC/HEIF file (client-side
            // conversion failed) - our image library can't decode it.
            return response()->json([
                'message' => 'error',
                'error' => 'That image format isn\'t supported. Please try converting it to JPEG first.',
            ], 422);
        }

        $relativePath = $dir . '/' . $filename;

        if ($kind === 'banner') {
            if ($group->banner) {
                Storage::disk('siteAssets')->delete($group->banner);
            }
            $group->banner = $relativePath;
            $group->save();
        }

        return response()->json(['message' => 'ok', 'path' => $relativePath]);
    }

    /**
     * Sets the group's "Calling Card" - one of a fixed set of shared icon
     * images (public/assets/img/icons/CC_*.webp) - instead of an uploaded
     * avatar image.
     */
    public function setCallingCard(Request $request, $groupSlug)
    {
        $group = Group::where('slug', $groupSlug)->firstOrFail();

        if (!$group->isAdmin(auth()->user()->id)) {
            abort(403);
        }

        $request->validate([
            'card' => 'required|string|in:' . implode(',', array_keys(self::CALLING_CARDS)),
        ]);

        $group->avatar = 'img/icons/CC_' . $request->input('card') . '.webp';
        $group->save();

        return response()->json(['message' => 'ok', 'path' => $group->avatar]);
    }

    private function combineGroupChunks($groupId, $fileUuid, $totalChunks, $outputFilePath)
    {
        $outputFile = fopen($outputFilePath, 'ab');
        for ($i = 0; $i < $totalChunks; $i++) {
            $chunkPath = config('filesystems.disks.siteAssets.root') . '/img/groups/' . $groupId . '/temp/' . $fileUuid . '/' . $i;
            fwrite($outputFile, file_get_contents($chunkPath));
        }
        fclose($outputFile);
    }

    /**
     * Simple site-name search for the "Custom Dive" form's site picker.
     */
    public function searchSites(Request $request, $groupSlug)
    {
        $group = Group::where('slug', $groupSlug)->firstOrFail();

        if (!$group->isMember(auth()->user()->id)) {
            abort(403);
        }

        $q = trim((string) $request->input('q'));
        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $sites = \App\Models\Site::where('name', 'LIKE', "%$q%")
            ->take(10)
            ->get(['id', 'name', 'level']);

        return response()->json($sites);
    }

    public function updateSettings(Request $request, $groupSlug)
    {
        $group = Group::where('slug', $groupSlug)->firstOrFail();

        if (!$group->isAdmin(auth()->user()->id)) {
            abort(403);
        }

        $request->validate([
            'reminders_enabled' => 'nullable|boolean',
            'allow_members_add_dives' => 'nullable|boolean',
            'favorite_operators' => 'nullable|array',
            'favorite_operators.*' => 'integer|exists:mysql_trips.operators,id',
        ]);

        $group->reminders_enabled = $request->boolean('reminders_enabled');
        $group->allow_members_add_dives = $request->boolean('allow_members_add_dives');
        $group->save();

        $group->favoriteOperators()->sync($request->input('favorite_operators', []));

        return redirect()->route('Groups.show', ['group' => $group->slug])->with('msg', 'Group settings updated!');
    }

    public function removeMember(Request $request, $groupSlug, $memberId)
    {
        $group = Group::where('slug', $groupSlug)->firstOrFail();

        if (!$group->isAdmin(auth()->user()->id)) {
            abort(403);
        }

        $member = GroupMember::where('group_id', $group->id)->findOrFail($memberId);

        // Don't allow removing the last admin.
        if ($member->role === 'admin' && $group->members()->where('role', 'admin')->where('status', 'active')->count() <= 1) {
            return redirect()->back()->with('msg', 'A group must keep at least one admin.');
        }

        $wasInvite = $member->status === 'invited';
        $member->delete();

        return redirect()->back()->with('msg', $wasInvite ? 'Invite cancelled.' : 'Member removed.');
    }

    public function destroy($groupSlug)
    {
        $group = Group::where('slug', $groupSlug)->firstOrFail();

        if (!$group->isAdmin(auth()->user()->id)) {
            abort(403);
        }

        $groupName = $group->name;

        foreach ($group->messages()->with('photos')->get() as $message) {
            foreach ($message->photos as $photo) {
                $photo->deletePhoto();
            }
            $message->delete();
        }

        foreach ($group->dives as $dive) {
            $dive->rsvps()->delete();
            $dive->delete();
        }

        $group->members()->delete();
        $group->delete();

        return redirect()->route('MyGroups')->with('msg', 'Group "' . $groupName . '" was deleted.');
    }
}

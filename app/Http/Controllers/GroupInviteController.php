<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Mailgun\Mailgun;

class GroupInviteController extends Controller
{
    /**
     * Simple name/email search for the admin's "invite someone" box,
     * excluding users who are already a member or already invited.
     */
    public function search(Request $request, $groupSlug)
    {
        $group = Group::where('slug', $groupSlug)->firstOrFail();

        if (!$group->isAdmin(auth()->user()->id)) {
            abort(403);
        }

        $q = trim((string) $request->input('q'));
        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        // whereNotNull matters here: a NULL in a NOT IN list makes the whole
        // comparison UNKNOWN for every row in MySQL, silently returning zero
        // results - and email-invited members have a null user_id.
        $existingUserIds = $group->members()->whereNotNull('user_id')->pluck('user_id');

        $users = User::where(function ($query) use ($q) {
                $query->where('name', 'LIKE', "%$q%")
                    ->orWhere('email', 'LIKE', "%$q%");
            })
            ->whereNotIn('id', $existingUserIds)
            ->take(10)
            ->get(['id', 'name', 'email', 'picture']);

        return response()->json($users);
    }

    public function invite(Request $request, $groupSlug)
    {
        $group = Group::where('slug', $groupSlug)->firstOrFail();

        if (!$group->isAdmin(auth()->user()->id)) {
            abort(403);
        }

        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        if ($group->members()->where('user_id', $request->user_id)->exists()) {
            return redirect()->back()->with('msg', 'That user is already a member or has a pending invite.');
        }

        GroupMember::create([
            'group_id' => $group->id,
            'user_id' => $request->user_id,
            'role' => 'member',
            'status' => 'invited',
            'invited_by' => auth()->user()->id,
        ]);

        $this->sendInviteEmail(User::find($request->user_id), $group);

        return redirect()->back()->with('msg', 'Invite sent!');
    }

    /**
     * Invites someone who isn't found in the search box - either because
     * they're a Divers Hub user who just didn't match the name/email query,
     * or (the main case) they have no account at all yet. For the latter,
     * the invite is stored against their email with no user_id; both
     * RegisterController and GoogleController link it to their account
     * automatically the moment they sign up with that same email, so it
     * simply appears on their MyGroups page to accept/decline like any
     * other invite - no separate accept-by-token flow needed.
     */
    public function inviteByEmail(Request $request, $groupSlug)
    {
        $group = Group::where('slug', $groupSlug)->firstOrFail();

        if (!$group->isAdmin(auth()->user()->id)) {
            abort(403);
        }

        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $email = strtolower($request->email);

        $existingUser = User::whereRaw('LOWER(email) = ?', [$email])->first();

        if ($existingUser) {
            if ($group->members()->where('user_id', $existingUser->id)->exists()) {
                return redirect()->back()->with('msg', 'That user is already a member or has a pending invite.');
            }

            GroupMember::create([
                'group_id' => $group->id,
                'user_id' => $existingUser->id,
                'role' => 'member',
                'status' => 'invited',
                'invited_by' => auth()->user()->id,
            ]);

            $this->sendInviteEmail($existingUser, $group);

            return redirect()->back()->with('msg', 'Invite sent!');
        }

        if ($group->members()->where('invited_email', $email)->exists()) {
            return redirect()->back()->with('msg', 'That email already has a pending invite to this group.');
        }

        GroupMember::create([
            'group_id' => $group->id,
            'invited_email' => $email,
            'role' => 'member',
            'status' => 'invited',
            'invited_by' => auth()->user()->id,
        ]);

        $this->sendExternalInviteEmail($email, $group);

        return redirect()->back()->with('msg', 'Invite sent to ' . $email . '!');
    }

    public function accept($memberId)
    {
        $member = GroupMember::where('user_id', auth()->user()->id)->findOrFail($memberId);

        $member->status = 'active';
        $member->save();

        return redirect()->route('Groups.show', ['group' => $member->group->slug])
            ->with('msg', 'Welcome to ' . $member->group->name . '!');
    }

    public function decline($memberId)
    {
        $member = GroupMember::where('user_id', auth()->user()->id)->findOrFail($memberId);
        $member->delete();

        return redirect()->route('MyGroups')->with('msg', 'Invite declined.');
    }

    /**
     * Notifies an invited user by email so they know to log in and RSVP.
     * Best-effort: a mail failure must not block the in-app invite.
     */
    private function sendInviteEmail(User $invitedUser, Group $group)
    {
        try {
            $mg = Mailgun::create(env('MAILGUN_KEY'));

            $inviterName = auth()->user()->name;
            $myGroupsUrl = route('MyGroups');

            $html = '<p>Hi ' . e($invitedUser->name) . ',</p>'
                . '<p><b>' . e($inviterName) . '</b> invited you to join the diving group <b>' . e($group->name) . '</b> on Divers Hub.</p>'
                . '<p><a href="' . $myGroupsUrl . '">Log in to accept or decline this invite</a></p>'
                . '<p>See you underwater!<br>The Divers Hub team</p>';

            Log::info('Sending group invite email to: ' . $invitedUser->name . ' <' . $invitedUser->email . '>');

            $mg->messages()->send('mail.divers-hub.com', [
                'from' => 'Divers-Hub <postmaster@mail.divers-hub.com>',
                'to' => $invitedUser->name . ' <' . $invitedUser->email . '>',
                'subject' => 'You\'re invited to join "' . $group->name . '" on Divers Hub',
                'html' => $html,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to send group invite email: ' . $e->getMessage());
        }
    }

    /**
     * Invites someone with no Divers Hub account yet, via the Mailgun
     * "invite" template - its only variable is the inviter's name, since
     * (unlike sendInviteEmail above) there's no account to deep-link into
     * yet. They just need to register; the pending invite links to their
     * account automatically by matching email at that point.
     */
    private function sendExternalInviteEmail(string $email, Group $group)
    {
        try {
            $mg = Mailgun::create(env('MAILGUN_KEY'));

            $inviterName = auth()->user()->name;

            Log::info('Sending external group invite email to: ' . $email);

            $mg->messages()->send('mail.divers-hub.com', [
                'from' => 'Divers-Hub <postmaster@mail.divers-hub.com>',
                'to' => $email,
                'subject' => 'You\'re invited to join "' . $group->name . '" on Divers Hub',
                'template' => 'invite',
                'h:X-Mailgun-Variables' => json_encode(['name' => $inviterName]),
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to send external group invite email: ' . $e->getMessage());
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class GroupFacebookController extends Controller
{
    private const GRAPH_VERSION = 'v21.0';

    /**
     * Kicks off the OAuth flow. The group id has to survive the round-trip
     * via session, not the URL, since Meta's redirect URI is a fixed string
     * registered on the Meta App - it can't carry a per-group path segment.
     */
    public function connect($groupSlug)
    {
        $group = Group::where('slug', $groupSlug)->firstOrFail();

        if (!$group->isAdmin(auth()->user()->id)) {
            abort(403);
        }

        session(['fb_connect_group_id' => $group->id]);

        // Facebook Login for Business doesn't accept ad-hoc scopes - the
        // permissions are defined on a pre-created Configuration instead,
        // referenced here by its config_id.
        return Socialite::driver('facebook')
            ->setScopes([])
            ->with(['config_id' => config('services.facebook.config_id')])
            ->redirect();
    }

    public function callback()
    {
        $groupId = session('fb_connect_group_id');
        session()->forget('fb_connect_group_id');

        if (!$groupId) {
            return redirect()->route('MyGroups')->with('msg', 'Facebook connection expired - please try again.');
        }

        $group = Group::findOrFail($groupId);

        if (!$group->isAdmin(auth()->user()->id)) {
            abort(403);
        }

        try {
            $fbUser = Socialite::driver('facebook')->user();

            // Exchange the short-lived user token (~2 hours) for a long-lived
            // one (~60 days) - the Page token we derive from it inherits that
            // longevity and, per Meta, effectively never expires afterward.
            $exchange = Http::get('https://graph.facebook.com/' . self::GRAPH_VERSION . '/oauth/access_token', [
                'grant_type' => 'fb_exchange_token',
                'client_id' => config('services.facebook.client_id'),
                'client_secret' => config('services.facebook.client_secret'),
                'fb_exchange_token' => $fbUser->token,
            ])->json();

            $longLivedUserToken = $exchange['access_token'] ?? $fbUser->token;

            $pagesResponse = Http::get('https://graph.facebook.com/' . self::GRAPH_VERSION . '/me/accounts', [
                'access_token' => $longLivedUserToken,
            ])->json();

            $pages = $pagesResponse['data'] ?? [];
        } catch (\Throwable $e) {
            Log::error('Facebook connect failed: ' . $e->getMessage());
            return redirect()->route('Groups.show', ['group' => $group->slug])
                ->with('msg', 'Could not connect to Facebook. Please try again.');
        }

        if (empty($pages)) {
            return redirect()->route('Groups.show', ['group' => $group->slug])
                ->with('msg', 'No Facebook Pages found for that account. You need to be an admin of at least one Facebook Page first.');
        }

        if (count($pages) === 1) {
            $this->savePage($group, $pages[0]);
            return redirect()->route('Groups.show', ['group' => $group->slug])
                ->with('msg', 'Connected to Facebook Page "' . $pages[0]['name'] . '"!');
        }

        session(['fb_pages_to_pick' => $pages, 'fb_pick_group_id' => $group->id]);

        return view('pages.Groups.FacebookPagePicker', ['pages' => $pages, 'group' => $group]);
    }

    public function pickPage(Request $request, $groupSlug)
    {
        $group = Group::where('slug', $groupSlug)->firstOrFail();

        if (!$group->isAdmin(auth()->user()->id)) {
            abort(403);
        }

        $pages = session('fb_pages_to_pick', []);
        $pickGroupId = session('fb_pick_group_id');
        session()->forget(['fb_pages_to_pick', 'fb_pick_group_id']);

        if ($pickGroupId != $group->id) {
            return redirect()->route('Groups.show', ['group' => $group->slug])
                ->with('msg', 'That Facebook page selection has expired - please reconnect.');
        }

        $request->validate(['page_id' => 'required|string']);

        $chosen = collect($pages)->firstWhere('id', $request->input('page_id'));

        if (!$chosen) {
            return redirect()->route('Groups.show', ['group' => $group->slug])
                ->with('msg', 'That page wasn\'t in the list - please reconnect.');
        }

        $this->savePage($group, $chosen);

        return redirect()->route('Groups.show', ['group' => $group->slug])
            ->with('msg', 'Connected to Facebook Page "' . $chosen['name'] . '"!');
    }

    public function disconnect($groupSlug)
    {
        $group = Group::where('slug', $groupSlug)->firstOrFail();

        if (!$group->isAdmin(auth()->user()->id)) {
            abort(403);
        }

        $group->fb_page_id = null;
        $group->fb_page_name = null;
        $group->fb_page_access_token = null;
        $group->fb_connected_by = null;
        $group->fb_connected_at = null;
        $group->save();

        return redirect()->route('Groups.show', ['group' => $group->slug])->with('msg', 'Facebook Page disconnected.');
    }

    private function savePage(Group $group, array $page)
    {
        $group->fb_page_id = $page['id'];
        $group->fb_page_name = $page['name'];
        $group->fb_page_access_token = $page['access_token'];
        $group->fb_connected_by = auth()->user()->id;
        $group->fb_connected_at = now();
        $group->save();
    }
}

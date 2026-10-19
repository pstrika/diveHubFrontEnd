<?php

namespace App\Http\Controllers;

use App\Support\AccountDeleter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Delete my account (Pablo, 2026-10-19) - the website page the apps link
 * to (App Store guideline 5.1.1(v)). What's deleted vs. anonymized:
 * App\Support\AccountDeleter.
 */
class AccountDeletionController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        abort_unless($user->isNotGuest(), 403);
        return view('pages.account.delete', [
            'user' => $user,
            'isAdmin' => $user->isAdmin(),
            'diveCount' => \App\Models\Dive::where('user_id', $user->id)->count(),
            'SEO' => ['title' => 'Delete my account | Divers Hub', 'robots' => 'noindex, nofollow'],
        ]);
    }

    public function destroy(Request $request)
    {
        $user = $request->user();
        abort_unless($user->isNotGuest(), 403);
        // Platform admins are removed deliberately by another admin, not by a tap.
        if ($user->isAdmin()) {
            return back()->withErrors(['confirm' => 'Admin accounts can\'t be deleted here. Ask another admin, or email support@divers-hub.com.']);
        }
        $request->validate(['confirm' => 'required|in:DELETE'], ['confirm.in' => 'Type DELETE in capitals to confirm.']);

        AccountDeleter::delete($user);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('status', 'Your account and your data have been deleted.');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Personal access tokens for the native dive-computer uploader apps
 * (Pablo, 2026-10-14). A diver generates one here, pastes it into the app
 * once, and that's the app's auth from then on - deliberately not a
 * login screen inside the narrow uploader app itself, since Google-only
 * accounts have no password the diver would know (users.password is
 * still a required column, but a Google sign-up never gives them a value
 * to type), and this way the app never needs to carry any OAuth UI at
 * all. Same posture as Sanctum's existing api/blog-articles token, just
 * self-service instead of hand-minted.
 *
 * Also lists "app:<device_name>" tokens (Pablo, 2026-10-15, the app
 * sign-in flow - AppAuthController) alongside "uploader:<device_name>"
 * ones - different mint path (a real sign-in through the site vs.
 * generated here and pasted), same revoke UI; docs/APP-AUTH.md's
 * contract explicitly expects this page to list and revoke both.
 *
 * The plaintext token is only ever available once, right after creation
 * (Sanctum doesn't store it) - the index view keeps it in a flashed
 * session value for a single page load, same convention as any one-time
 * secret-reveal flow.
 */
class UploadTokenController extends Controller
{
    public function index(Request $request)
    {
        $tokens = $request->user()->tokens()
            ->where(fn ($q) => $q->where('name', 'like', 'uploader:%')->orWhere('name', 'like', 'app:%'))
            ->orderByDesc('created_at')
            ->get(['id', 'name', 'last_used_at', 'created_at']);

        return view('pages.UploadTokens', [
            'tokens' => $tokens,
            'newToken' => session('new_upload_token'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'device_name' => 'required|string|max:60',
        ]);

        $token = $request->user()->createToken('uploader:' . $data['device_name']);

        return redirect()->route('UploadTokens')
            ->with('new_upload_token', $token->plainTextToken);
    }

    public function destroy(Request $request, $id)
    {
        $request->user()->tokens()->where('id', $id)->delete();

        return redirect()->route('UploadTokens');
    }
}

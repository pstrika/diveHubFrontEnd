<?php

namespace App\Http\Controllers;

use App\Models\AppLoginCode;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * App sign-in (Pablo, 2026-10-15, via the uploader session's proposed
 * docs/APP-AUTH.md, confirmed as-is): the native app opens an in-app
 * browser sheet to login(), which rides the site's own `auth` middleware
 * - an unauthenticated visit bounces through the normal sign-in page
 * (password or Google) and back here automatically. Works for both
 * because the website does the actual authenticating, not the app.
 *
 * Depends on Authenticate middleware storing fullUrl() (not url()) as
 * the post-login redirect target - confirmed and fixed that bug
 * alongside this (app/Http/Middleware/Authenticate.php), since url()
 * silently drops the ?state=... query string this flow needs to survive
 * the round trip.
 */
class AppAuthController extends Controller
{
    /**
     * GET /app/login?state=<random> - `auth` middleware gates this, so by
     * the time this method runs the user is authenticated. Issues a
     * one-time code and redirects to the app's own custom scheme - never
     * anywhere else; `state` is only ever echoed back verbatim, never
     * interpreted server-side.
     */
    public function login(Request $request)
    {
        $state = mb_substr((string) $request->query('state', ''), 0, 128);

        $code = AppLoginCode::issue($request->user()->id);
        // Set by the Authenticate middleware when a guest was bounced from
        // here to the sign-in page (hides the install-as-app prompt there).
        $request->session()->forget('app_login');

        return redirect()->away(
            'divershub://auth?code=' . urlencode($code->code) . '&state=' . urlencode($state)
        );
    }

    /**
     * POST /api/app/token {code, device_name} - no auth, the code is the
     * credential (rate-limited at the route). 422 for a code that was
     * never issued (malformed/guessed); 410 for a real code that's
     * expired or already used - a meaningfully different failure (retry
     * the whole sign-in vs. a client bug sending garbage).
     */
    public function exchangeToken(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:40',
            'device_name' => 'required|string|max:60',
        ]);

        $loginCode = AppLoginCode::where('code', $data['code'])->first();
        if (!$loginCode) {
            return response()->json(['message' => 'Unknown code.'], 422);
        }
        if ($loginCode->isExpired()) {
            return response()->json(['message' => 'Code expired or already used.'], 410);
        }

        $loginCode->used_at = now();
        $loginCode->save();

        $user = User::find($loginCode->user_id);
        if (!$user) {
            return response()->json(['message' => 'Unknown code.'], 422);
        }

        // Same Sanctum token shape /upload-tokens already issues (just a
        // different name prefix) so that page lists and revokes this one
        // like any other device, with no changes needed there.
        $token = $user->createToken('app:' . $data['device_name']);

        return response()->json([
            'token' => $token->plainTextToken,
            'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
        ], 201);
    }

    /** DELETE /api/app/token - sign out, revokes only the bearer token this request authenticated with. */
    public function revokeToken(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }
}

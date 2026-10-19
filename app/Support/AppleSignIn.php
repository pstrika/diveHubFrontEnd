<?php

namespace App\Support;

use App\Models\GroupMember;
use App\Models\User;
use App\Services\NotificationService;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

/**
 * Sign in with Apple (Pablo, 2026-10-19 - App Review 4.8: an app offering
 * Google sign-in must offer Apple too). Shared by the website flow
 * (Auth\AppleController) and the iOS app's native button (AppAuthController).
 */
class AppleSignIn
{
    /**
     * Verify an Apple identity token (JWT): Apple's signature, issuer,
     * expiry, and that it was issued for US - the website's Services ID or
     * the iOS app's bundle id. (The socialite driver's own check skips the
     * audience, so any app's Apple token would pass it.) Returns the claims.
     */
    public static function verifyIdentityToken(string $jwt): array
    {
        $keys = Cache::remember('apple-signin-jwks', 3600, fn () => Http::timeout(10)->get('https://appleid.apple.com/auth/keys')->throw()->json());
        try {
            $claims = (array) JWT::decode($jwt, JWK::parseKeySet($keys, 'RS256'));
        } catch (\Throwable $e) {
            throw ValidationException::withMessages(['identity_token' => 'Apple sign-in could not be verified.']);
        }
        $audiences = array_filter([config('services.apple.client_id'), config('services.apple.ios_bundle_id')]);
        if (($claims['iss'] ?? null) !== 'https://appleid.apple.com' || !in_array($claims['aud'] ?? null, $audiences, true) || empty($claims['sub'])) {
            throw ValidationException::withMessages(['identity_token' => 'Apple sign-in was not issued for Divers Hub.']);
        }
        return $claims;
    }

    /**
     * The Divers Hub account for this Apple user: already linked (apple_id),
     * or an existing account with the same verified email (linked now), or
     * a new one. Apple only sends the name on the very first sign-in, and
     * the email may be a private relay (...@privaterelay.appleid.com).
     *
     * @return array{0: User, 1: bool} the user, and whether it was just created
     */
    public static function findOrCreate(string $sub, ?string $email, ?string $name): array
    {
        if ($user = User::where('apple_id', $sub)->first()) {
            return [$user, false];
        }
        $email = $email ? mb_strtolower(trim($email)) : null;
        if ($email && ($user = User::where('email', $email)->first())) {
            $user->apple_id = $sub;
            $user->save();
            return [$user, false];
        }

        $user = User::create([
            'name' => trim((string) $name) ?: 'Diver',
            // No email from Apple at all is very rare (it was withheld on an
            // earlier sign-in); keep the account usable with a placeholder.
            'email' => $email ?: ('apple-' . substr(hash('sha256', $sub), 0, 16) . '@privaterelay.divers-hub.com'),
            'password' => encrypt(\Illuminate\Support\Str::random(32)),
            'role_id' => 3,
        ]);
        $user->forceFill(['apple_id' => $sub, 'email_verified_at' => now()])->save();

        // Same as Google sign-up: pending group invites to this email become the member's.
        foreach (GroupMember::where('invited_email', $user->email)->whereNull('user_id')->with('group')->get() as $invite) {
            $invite->user_id = $user->id;
            $invite->save();
            if ($invite->group) {
                NotificationService::notify([$user->id], $invite->group->name, 'You\'ve been invited to join "' . $invite->group->name . '"',
                    route('MyGroups'), null, $invite->invited_by, $invite->group->id);
            }
        }
        return [$user, true];
    }

    public static function configured(): bool
    {
        return (bool) (config('services.apple.client_id') && config('services.apple.team_id') && config('services.apple.key_id') && config('services.apple.private_key'));
    }
}

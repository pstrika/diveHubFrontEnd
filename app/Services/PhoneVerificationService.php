<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * One-time SMS codes for verifying a US phone number, required at signup
 * and on every later change (Pablo, 2026-09-14: unlimited changes allowed
 * for now - a monthly cap is a later problem, once real SMS cost is at
 * stake). International numbers are never sent a code - there's no SMS
 * channel to send it on - so this only ever applies to a US/NANP number
 * (see App\Support\PhoneNumber::isUs()).
 *
 * The number being verified lives in users.pending_phone until the code
 * matches; users.phone (and phone_verified_at) only change on success, so
 * a diver mid-change never loses a number that already works.
 */
class PhoneVerificationService
{
    private const CODE_TTL_MINUTES = 15;
    private const RESEND_COOLDOWN_SECONDS = 30;

    /** Starts (or restarts) verification for $e164 - stores it as pending and texts a code. Returns false without sending if canResend() would say no. */
    public static function start(User $user, string $e164): bool
    {
        if (!self::canResend($user)) {
            return false;
        }

        $code = (string) random_int(100000, 999999);

        $user->pending_phone = $e164;
        $user->phone_verification_code_hash = Hash::make($code);
        $user->phone_verification_sent_at = now();
        $user->save();

        // Used to always return true here regardless of whether Twilio
        // actually accepted the message - SmsService::send() already
        // reports that (false on missing config, an invalid number, or a
        // rejected send), it just wasn't being checked. That's exactly how
        // a caller could show "code sent!" while nothing went out (Pablo,
        // 2026-09-16: "it said send OTP, but I didn't receive anything").
        // Rolling the pending state back on a real failure also means the
        // 30s resend cooldown doesn't block an immediate retry once
        // whatever was wrong (config, number) is fixed.
        $sent = SmsService::send($e164, "Your Divers Hub verification code is {$code}. It expires in " . self::CODE_TTL_MINUTES . ' minutes.');

        if (!$sent) {
            self::clearPending($user);
            $user->save();
            return false;
        }

        return true;
    }

    /** A basic cooldown against tapping "resend" repeatedly - not the monthly change cap, which isn't built yet. */
    public static function canResend(User $user): bool
    {
        if (!$user->phone_verification_sent_at) {
            return true;
        }

        return $user->phone_verification_sent_at->diffInSeconds(now()) >= self::RESEND_COOLDOWN_SECONDS;
    }

    /** Re-sends to whatever is currently pending, if there is one and the cooldown allows it. */
    public static function resend(User $user): bool
    {
        if (!$user->pending_phone) {
            return false;
        }

        return self::start($user, $user->pending_phone);
    }

    /** Checks $code against the pending phone's stored hash. On success, commits pending_phone -> phone and clears the pending state. */
    public static function verify(User $user, string $code): bool
    {
        if (!$user->pending_phone || !$user->phone_verification_code_hash || !$user->phone_verification_sent_at) {
            return false;
        }

        if ($user->phone_verification_sent_at->diffInMinutes(now()) > self::CODE_TTL_MINUTES) {
            return false;
        }

        if (!Hash::check($code, $user->phone_verification_code_hash)) {
            return false;
        }

        $user->phone = $user->pending_phone;
        $user->phone_verified_at = now();
        self::clearPending($user);
        $user->save();

        return true;
    }

    /** Abandons an in-progress change without touching the number already on file. */
    public static function clearPending(User $user): void
    {
        $user->pending_phone = null;
        $user->phone_verification_code_hash = null;
        $user->phone_verification_sent_at = null;
    }
}

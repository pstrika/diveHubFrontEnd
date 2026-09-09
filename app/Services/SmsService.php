<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Sends a plain-text SMS via Twilio's REST API. Silently no-ops if
     * Twilio isn't configured yet or the phone number can't be normalized -
     * this must never throw and block the caller's reminder loop.
     */
    public static function send(?string $rawPhone, string $body): void
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.auth_token');
        $from = config('services.twilio.from');

        if (!$sid || !$token || !$from) {
            return;
        }

        $to = self::toE164($rawPhone);
        if (!$to) {
            return;
        }

        try {
            $response = Http::asForm()
                ->withBasicAuth($sid, $token)
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                    'To' => $to,
                    'From' => $from,
                    'Body' => $body,
                ]);

            if (!$response->successful()) {
                Log::error('Twilio SMS failed (' . $response->status() . '): ' . $response->body());
            }
        } catch (\Throwable $e) {
            Log::error('Twilio SMS exception: ' . $e->getMessage());
        }
    }

    /**
     * Phone numbers in this app are entered as bare US digits (see
     * UserController's `phone` validation, max:10, no country code) - assume
     * NANP and normalize to E.164 for Twilio. Anything that doesn't look
     * like a valid US number is treated as unusable rather than guessed at.
     */
    private static function toE164(?string $rawPhone): ?string
    {
        if (!$rawPhone) {
            return null;
        }

        if (str_starts_with($rawPhone, '+')) {
            return $rawPhone;
        }

        $digits = preg_replace('/\D/', '', $rawPhone);

        if (strlen($digits) === 10) {
            return '+1' . $digits;
        }

        if (strlen($digits) === 11 && $digits[0] === '1') {
            return '+' . $digits;
        }

        return null;
    }
}

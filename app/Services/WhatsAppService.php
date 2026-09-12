<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * WhatsApp via Twilio, not Meta's Graph API directly (corrected 2026-09-14 -
 * Pablo's WhatsApp Business number is provisioned through Twilio, the same
 * account SmsService already sends SMS from). Twilio wraps Meta's WhatsApp
 * Business Platform behind its own regular Messages API: the same
 * Account SID / API Key used for SMS, the same /Messages.json endpoint,
 * just a "whatsapp:" prefix on To/From, and an approved template is a
 * Twilio "Content" resource (a ContentSid like HX..., created and reviewed
 * via content.twilio.com / the Twilio Console's Content Template Builder -
 * Meta still approves the content, Twilio just fronts it) rather than
 * Meta's raw template name/language/components shape.
 *
 * Same safety rules as SmsService: silently no-ops if not configured,
 * never throws, same US/NANP phone normalization (kept as its own copy
 * rather than a shared dependency, so either service stays independently
 * removable).
 *
 * Only two kinds of outbound message are allowed, and they are not
 * interchangeable:
 *   - sendTemplate(): a pre-approved Content template - required for
 *     anything the business starts, like a dive reminder or a chat
 *     mention. The ContentSid's variable count/order must match what that
 *     template actually asks for; there is no way to send free-form
 *     business-initiated text, unlike SMS.
 *   - sendText(): free-form text, only deliverable within 24 hours of the
 *     customer's last message TO the business. Useless for a proactive
 *     reminder; kept here for a future two-way chat feature.
 */
class WhatsAppService
{
    /**
     * A business-initiated notification - the only kind allowed outside a
     * live chat. Returns whether Twilio actually accepted it, for callers
     * that need to know (the admin Message Management console) -
     * background callers just ignore the return value, unchanged from before.
     */
    public static function sendTemplate(?string $rawPhone, string $contentSid, array $variables = []): bool
    {
        $config = self::config();
        if (!$config || !$contentSid) return false;

        $to = self::toE164($rawPhone);
        if (!$to) return false;

        $payload = [
            'To' => 'whatsapp:' . $to,
            'From' => 'whatsapp:' . $config['from'],
            'ContentSid' => $contentSid,
        ];
        if (!empty($variables)) {
            $payload['ContentVariables'] = json_encode($variables);
        }

        return self::post($config, $payload);
    }

    /**
     * Free-form text - only within Twilio/Meta's 24h customer-service
     * window (e.g. replying to an inbound chat). A send outside that
     * window comes back false (Twilio rejects it, logged by post()) - the
     * admin console's cue to use a template instead.
     */
    public static function sendText(?string $rawPhone, string $body): bool
    {
        $config = self::config();
        if (!$config) return false;

        $to = self::toE164($rawPhone);
        if (!$to) return false;

        return self::post($config, [
            'To' => 'whatsapp:' . $to,
            'From' => 'whatsapp:' . $config['from'],
            'Body' => $body,
        ]);
    }

    private static function config(): ?array
    {
        $accountSid = config('services.twilio.account_sid');
        $authUser = config('services.twilio.api_key_sid') ?: $accountSid;
        $authPass = config('services.twilio.api_key_secret');
        $from = config('services.twilio.from');

        if (!$accountSid || !$authUser || !$authPass || !$from) {
            return null;
        }

        return compact('accountSid', 'authUser', 'authPass', 'from');
    }

    private static function post(array $config, array $payload): bool
    {
        try {
            $response = Http::asForm()
                ->withBasicAuth($config['authUser'], $config['authPass'])
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$config['accountSid']}/Messages.json", $payload);

            if (!$response->successful()) {
                Log::error('WhatsApp (Twilio) send failed (' . $response->status() . '): ' . $response->body());
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('WhatsApp (Twilio) send exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Phone numbers in this app are entered as bare US digits (see
     * UserController's `phone` validation, max:10, no country code) - assume
     * NANP and normalize to E.164. Anything that doesn't look like a valid
     * US number is treated as unusable rather than guessed at.
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

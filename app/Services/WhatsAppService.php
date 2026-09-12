<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * WhatsApp Business Cloud API (Meta), mirroring SmsService's shape and
 * safety rules: silently no-ops if not configured, never throws, and phone
 * numbers go through the same US/NANP normalization SmsService uses (kept
 * as its own copy here rather than a shared dependency, so either service
 * stays independently removable).
 *
 * Meta's Cloud API only allows two kinds of outbound message, and they are
 * not interchangeable:
 *   - sendTemplate(): a pre-approved TEMPLATE message - required for
 *     anything the business starts, like a dive reminder. The template
 *     name, language and variable count/order must match one already
 *     reviewed and approved in Meta Business Manager; there is no way to
 *     send free-form business-initiated text, unlike SMS.
 *   - sendText(): free-form text, only deliverable within 24 hours of the
 *     customer's last message TO the business. Useless for a proactive
 *     reminder; kept here for a future two-way chat feature.
 *
 * The account and API credentials are one approval; a specific template
 * (e.g. a dive-reminder template) is a separate one that can land later -
 * see the `dive_reminder_template` config note.
 */
class WhatsAppService
{
    /** A business-initiated notification - the only kind allowed outside a live chat. */
    public static function sendTemplate(?string $rawPhone, string $template, string $languageCode = 'en_US', array $bodyParams = []): void
    {
        $config = self::config();
        if (!$config) return;

        $to = self::toE164($rawPhone);
        if (!$to) return;

        $components = [];
        if (!empty($bodyParams)) {
            $components[] = [
                'type' => 'body',
                'parameters' => array_map(fn ($p) => ['type' => 'text', 'text' => (string) $p], $bodyParams),
            ];
        }

        self::post($config, [
            'messaging_product' => 'whatsapp',
            'to' => ltrim($to, '+'),
            'type' => 'template',
            'template' => [
                'name' => $template,
                'language' => ['code' => $languageCode],
                'components' => $components,
            ],
        ]);
    }

    /** Free-form text - only within Meta's 24h customer-service window (e.g. replying to an inbound message). */
    public static function sendText(?string $rawPhone, string $body): void
    {
        $config = self::config();
        if (!$config) return;

        $to = self::toE164($rawPhone);
        if (!$to) return;

        self::post($config, [
            'messaging_product' => 'whatsapp',
            'to' => ltrim($to, '+'),
            'type' => 'text',
            'text' => ['body' => $body],
        ]);
    }

    private static function config(): ?array
    {
        $token = config('services.whatsapp.access_token');
        $phoneId = config('services.whatsapp.phone_number_id');

        if (!$token || !$phoneId) {
            return null;
        }

        return [
            'token' => $token,
            'phoneId' => $phoneId,
            'version' => config('services.whatsapp.graph_version', 'v21.0'),
        ];
    }

    private static function post(array $config, array $payload): void
    {
        try {
            $response = Http::withToken($config['token'])
                ->post("https://graph.facebook.com/{$config['version']}/{$config['phoneId']}/messages", $payload);

            if (!$response->successful()) {
                Log::error('WhatsApp send failed (' . $response->status() . '): ' . $response->body());
            }
        } catch (\Throwable $e) {
            Log::error('WhatsApp send exception: ' . $e->getMessage());
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

<?php

namespace App\Support;

/**
 * Phone number acceptance, normalization and display for the profile page.
 *
 * Distinct from SmsService/WhatsAppService's own toE164() (each keeps its
 * own copy, US/NANP-only, for whatever they're about to send) - this one is
 * the wider net at the point a diver actually types a number in: any of the
 * accepted US shapes, or a general international number for WhatsApp only.
 * Storage is always E.164; display formats a US number back out as
 * "+1 (954) 292-2846" for the form, and leaves anything else as the plain
 * E.164 string a diver typed.
 */
final class PhoneNumber
{
    /**
     * Parses free-form input into E.164, or null if it doesn't look like a
     * usable number at all. Accepts:
     *   - 10 bare US digits (9542922846)
     *   - 11 digits starting with 1 (19542922846)
     *   - any of the above with punctuation (dashes, spaces, parens, a
     *     leading +1)
     *   - a general international number: a leading "+" and 7-15 digits
     *     total (E.164's own maximum length) - usable for WhatsApp, never SMS
     */
    public static function toE164(?string $raw): ?string
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return null;
        }

        $hadPlus = str_starts_with($raw, '+');
        $digits = preg_replace('/\D/', '', $raw);

        if ($digits === '') {
            return null;
        }

        if (!$hadPlus) {
            if (strlen($digits) === 10) {
                return '+1' . $digits;
            }
            if (strlen($digits) === 11 && $digits[0] === '1') {
                return '+' . $digits;
            }
        }

        // A general international number: "+" plus 7-15 digits. A NANP
        // number typed with an explicit "+" (e.g. "+19542922846") is
        // already exactly this shape, so it falls through to here too.
        if ($hadPlus && strlen($digits) >= 7 && strlen($digits) <= 15) {
            return '+' . $digits;
        }

        return null;
    }

    /**
     * True when this number is a US/NANP one our SMS can reach - the same
     * rule SmsService/WhatsAppService use to decide whether they can send
     * at all. Accepts a pre-E.164 legacy value too (bare digits, no "+"),
     * same reasoning as display() below.
     */
    public static function isUs(?string $phone): bool
    {
        if (!$phone) {
            return false;
        }
        $e164 = str_starts_with($phone, '+') ? $phone : self::toE164($phone);
        return (bool) $e164 && (bool) preg_match('/^\+1\d{10}$/', $e164);
    }

    /**
     * "+1 (954) 292-2846" for a US number; the plain E.164 string for
     * anything else (or "" for none on file). Numbers saved before E.164
     * storage started (plain 10 digits, no "+") are normalized on the way
     * in here too, so an existing phone displays formatted the first time
     * this page loads it, not only after its next save/re-verification.
     */
    public static function display(?string $e164): string
    {
        if (!$e164) {
            return '';
        }

        if (!str_starts_with($e164, '+')) {
            $e164 = self::toE164($e164) ?? $e164;
        }

        if (self::isUs($e164)) {
            $digits = substr($e164, 2); // drop the leading "+1"
            return '+1 (' . substr($digits, 0, 3) . ') ' . substr($digits, 3, 3) . '-' . substr($digits, 6, 4);
        }

        return $e164;
    }
}

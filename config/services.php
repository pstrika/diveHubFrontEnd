<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URI'),
    ],

    'webpush' => [
        'public_key' => env('VAPID_PUBLIC_KEY'),
        'private_key' => env('VAPID_PRIVATE_KEY'),
        'subject' => env('VAPID_SUBJECT'),
    ],

    'twilio' => [
        'account_sid' => env('TWILIO_ACCOUNT_SID'),
        'api_key_sid' => env('TWILIO_API_KEY_SID'),
        'api_key_secret' => env('TWILIO_API_KEY_SECRET'),
        'from' => env('TWILIO_FROM_NUMBER'),
    ],

    // WhatsApp goes through the same Twilio account as SMS above, not
    // Meta's Graph API directly (2026-09-14: Pablo's WhatsApp Business
    // number is provisioned through Twilio, which wraps Meta's WhatsApp
    // Business Platform) - see App\Services\WhatsAppService. No separate
    // access token/phone number id to configure; just which of the
    // account's approved Content templates (content.twilio.com, confirmed
    // live via its Content API) to use for each kind of message. Content
    // SIDs aren't secret - they're just names - but stay env-overridable
    // in case a template is ever recreated.
    'whatsapp' => [
        // "trip_reminder_3" (whatsapp/card: header image, body with 5 vars -
        // name/site/operator/date/time - footer, and a "Trip Details" button
        // linking to the group with a 6th var for the slug). Originally
        // created straight in Meta Business Manager (as category MARKETING),
        // which does NOT sync into Twilio's Content API on its own -
        // recreated here as its own Twilio Content resource and submitted
        // 2026-09-14, category MARKETING to match the Meta original (a
        // first attempt submitted as UTILITY - wrong category, slower/wrong
        // review queue - was abandoned; that dead SID is
        // HXdb6d8053b6017f001e74d9787321dbdb, left unreferenced rather than
        // deleted). This submission needs its own Meta review even though
        // the wording was already approved directly in Meta; status was
        // "received" (pending) as of submission. Falls back to nothing (no
        // send, just a logged failure) until it comes back approved -
        // "trip_reminder_2" (HXe8039180c734c549ecc59fe2e0c343c1, plain
        // text, no image/button, category UTILITY, confirmed already
        // approved) is there to roll back to if this one stalls.
        'trip_reminder_content_sid' => env('TWILIO_WHATSAPP_TRIP_REMINDER_SID', 'HXc96f0ea171f85fca8b3ecaa3a00fb9a4'),
        // @mention in a group chat -> an immediate WhatsApp ping to the
        // person mentioned. No template exists for this yet - null until
        // one is created and approved, which is its own review separate
        // from the credentials/trip-reminder template above.
        'mention_content_sid' => env('TWILIO_WHATSAPP_MENTION_SID'),
    ],

];

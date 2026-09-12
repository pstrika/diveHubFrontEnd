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
        // "trip_reminder_2" (plain text, 5 vars: name, site, operator,
        // date, time) - approved and live.
        'trip_reminder_content_sid' => env('TWILIO_WHATSAPP_TRIP_REMINDER_SID', 'HXe8039180c734c549ecc59fe2e0c343c1'),
        // @mention in a group chat -> an immediate WhatsApp ping to the
        // person mentioned. No template exists for this yet - null until
        // one is created and approved, which is its own review separate
        // from the credentials/trip-reminder template above.
        'mention_content_sid' => env('TWILIO_WHATSAPP_MENTION_SID'),
    ],

];

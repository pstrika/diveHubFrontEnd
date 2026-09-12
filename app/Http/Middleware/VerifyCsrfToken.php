<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        // Twilio's own webhook POST - it has no CSRF token to send, and
        // isn't the browser session this protection is for.
        'webhooks/twilio/inbound',
    ];
}

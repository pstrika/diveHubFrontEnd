<?php

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array
     */
    protected $except = [
        // Set by the Logbook page's own JS when the diver closes the "get
        // the app" card, so it can't be encrypted.
        'dh_app_card_hidden',
    ];
}

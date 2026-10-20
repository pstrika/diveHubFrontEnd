<?php

/*
|--------------------------------------------------------------------------
| Divers Hub release info
|--------------------------------------------------------------------------
|
| This is the ONE place the version number lives. Every screen that shows
| it renders the <x-version /> Blade component, which reads these values.
| Bump the two values below on each release and nothing else needs to
| change. Keep the version in step with the git tag (v9.15.1 and so on).
|
*/

return [

    'version' => '12.20.13',

    // Release date shown next to the version. Any human readable format.
    'released' => '10/17/26',

    // App Store link for the companion app, "Logbook by Divers Hub". Null
    // while it's in TestFlight: the Logbook page then says "coming soon".
    'app_store_url' => null,

    // Features open to a few accounts only (App\Support\EarlyAccess).
    // Lower-case emails; '*' opens a feature to every logbook user.
    'early_access' => [
        // admin@material.com: the test logbook from 2026-10-18 on.
        // Open to every logbook user (Pablo, 2026-10-19); the logbook gate
        // (registered members, no guests) still applies on every route.
        'equipment' => ['*'],
        // Marine life open to every logbook user (Pablo, 2026-10-19). The AI
        // "What's this?" photo ID has its own switch - each use is billed;
        // opened to everyone too, no daily cap (Pablo, 2026-10-19).
        'species' => ['*'],
        'species_id' => ['*'],
    ],

];

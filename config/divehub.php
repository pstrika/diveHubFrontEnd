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

    'version' => '12.12.0',

    // Release date shown next to the version. Any human readable format.
    'released' => '10/16/26',

    // Early-access gate for the diver logbook (web pages + app API) - see
    // App\Http\Middleware\LogbookGate. Lower-case emails. Pablo only for
    // now (2026-10-15: "nobody else should have access to this feature").
    'logbook_allowed_emails' => [
        'pstrika@gmail.com',
    ],

];

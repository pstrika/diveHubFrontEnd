<?php

namespace App\Support;

/**
 * sites.location holds a 3-letter coast code; this is the one place it
 * turns into a city name (the Groups auto-add rule modal carries the same
 * list inline - lift it here as other screens need it).
 */
class SiteLocations
{
    public const NAMES = [
        'KLA' => 'Key Largo', 'ISM' => 'Islamorada', 'MAR' => 'Marathon', 'KWE' => 'Key West',
        'MIA' => 'Miami Beach', 'FLL' => 'Fort Lauderdale', 'POM' => 'Pompano', 'DEB' => 'Deerfield',
        'BOY' => 'Boynton', 'WPB' => 'West Palm', 'JUP' => 'Jupiter', 'STU' => 'Stuart', 'PSL' => 'Port St Lucie',
    ];

    public static function name(?string $code): ?string
    {
        if ($code === null || $code === '') return null;
        return self::NAMES[strtoupper($code)] ?? $code;
    }
}

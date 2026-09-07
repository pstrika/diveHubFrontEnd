<?php

namespace App\Support;

/**
 * Groups the fourteen weather locations into five coasts for the trip board.
 *
 * Trips are tagged with a weather location short code (KEY, FLL, WPB and so
 * on, the first token of trips.tags). Fourteen region headers are too many
 * to scan on a phone, so the board groups them north to south into coasts.
 * The location itself is still shown on each card and used for the sea
 * state pill, so nothing is lost, only the grouping changes.
 *
 * Unknown codes fall into "Other" so a new crawler location never hides a
 * trip. Argentina is its own group and only appears when it has trips.
 */
final class Coast
{
    /** Display order, north to south. key => [label, location short codes]. */
    /*
     * Codes are weatherlocations.short as stored in production (checked against
     * the 2026-09-06 dump): KLA Key Largo, ISM Islamorada, MAR Marathon, KWE Key
     * West, MIA Miami Beach, FLL Fort Lauderdale, POM Pompano, DEB Deerfield,
     * BOY Boynton, WPB West Palm, JUP Jupiter, STU Stuart, PSL Port St Lucie,
     * and MDQ, LGR, PMY, USH in Argentina.
     */
    private const COASTS = [
        'treasure'  => ['label' => 'Treasure Coast',   'codes' => ['STU', 'PSL']],
        'palm'      => ['label' => 'Palm Beach',       'codes' => ['JUP', 'WPB', 'BOY']],
        'broward'   => ['label' => 'Fort Lauderdale',  'codes' => ['DEB', 'POM', 'FLL']],
        'miami'     => ['label' => 'Miami',            'codes' => ['MIA']],
        'keys'      => ['label' => 'Florida Keys',     'codes' => ['KLA', 'ISM', 'MAR', 'KWE']],
        'argentina' => ['label' => 'Argentina',        'codes' => ['MDQ', 'LGR', 'PMY', 'USH']],
        'other'     => ['label' => 'Other',            'codes' => []],
    ];

    /** @return array<string, array{label:string, codes:string[]}> */
    public static function all(): array
    {
        return self::COASTS;
    }

    public static function isValid($key): bool
    {
        return is_string($key) && isset(self::COASTS[$key]);
    }

    public static function label(string $key): string
    {
        return self::COASTS[$key]['label'] ?? 'Other';
    }

    /** Coast key for a weather location short code such as "FLL". */
    public static function forCode(?string $code): string
    {
        $code = strtoupper(trim((string) $code));
        foreach (self::COASTS as $key => $coast) {
            if (in_array($code, $coast['codes'], true)) {
                return $key;
            }
        }
        return 'other';
    }

    /** Options for the region chips: key => label, only coasts that can have trips. */
    public static function chipOptions(): array
    {
        $out = [];
        foreach (self::COASTS as $key => $coast) {
            if ($key !== 'other' && $key !== 'argentina') {
                $out[$key] = $coast['label'];
            }
        }
        return $out;
    }
}

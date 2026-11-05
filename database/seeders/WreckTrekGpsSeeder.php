<?php

namespace Database\Seeders;

use App\Models\Site;
use App\Models\WreckTrek;
use App\Models\WreckTrekLeg;
use App\Models\WreckTrekRoute;
use App\Support\WreckTrekData;
use Illuminate\Database\Seeder;

/**
 * The predefined wreck treks. Every pair of wrecks in a trek gets a leg, so a
 * diver can pick any wreck as the next stop; its heading (true) and distance
 * are worked out from the two sites' GPS (Pablo, 2026-11-04: "use real
 * coordinates with real distances from our database"). verified_on stays null:
 * nobody has swum them yet, and the page says so. Re-running replaces
 * unverified legs and leaves measured ones alone.
 */
class WreckTrekGpsSeeder extends Seeder
{
    private const EARTH_RADIUS_FT = 20902231;

    // slug => chart icon, in order: each leg's heading runs from earlier to later.
    private const TREKS = [
        ['slug' => 'pompano', 'name' => 'Pompano Wreck Trek', 'wrecks' => [
            'rodeo-25' => 'ship', 'lady-luck' => 'ship', 'mariner-2-tug' => 'ship',
            'mariner-2-barge' => 'barge', 'union-express' => 'ship', 'qualmann-tugs' => 'ship',
        ]],
        ['slug' => 'key-biscayne', 'name' => 'Key Biscayne Wreck Trek', 'wrecks' => [
            'south-seas' => 'ship', 'princess-britney' => 'ship', 'paraiso-iii' => 'ship',
            'sheri-lyn' => 'ship', 'miami-river-barge' => 'barge', 'rio-miami' => 'ship',
            'dema-trader' => 'ship', 'unnamed-barge' => 'barge', 'biscayne' => 'ship',
            'proteus' => 'ship', 'ophelia-bryan' => 'ship',
        ]],
    ];

    public function run()
    {
        $slugs = array_merge(...array_map(fn ($t) => array_keys($t['wrecks']), self::TREKS));

        Site::whereNotNull('trek_type')->whereNotIn('slug', $slugs)->get()->each(fn ($s) => $s->forceFill([
            'trek_sand_ft' => null, 'trek_type' => null, 'wreck_trek_id' => null,
        ])->save());

        WreckTrekLeg::whereNull('verified_on')->delete();

        foreach (self::TREKS as $sort => $trek) {
            $row = WreckTrek::updateOrCreate(['slug' => $trek['slug']], ['name' => $trek['name'], 'sort' => $sort]);

            // Rotation, broken and sitting are never touched here: they are entered by hand.
            $sites = [];
            foreach ($trek['wrecks'] as $slug => $type) {
                $site = Site::where('slug', $slug)->firstOrFail();
                $site->forceFill(['trek_sand_ft' => (int) $site->maxDepth, 'trek_type' => $type, 'wreck_trek_id' => $row->id])->save();
                $sites[] = $site;
            }

            foreach ($sites as $i => $a) {
                foreach (array_slice($sites, $i + 1) as $b) {
                    [$hdg, $dist] = $this->bearingAndDistance($a, $b);
                    WreckTrekLeg::firstOrCreate(
                        ['from_site_id' => $a->id, 'to_site_id' => $b->id],
                        ['heading_deg' => $hdg, 'distance_ft' => $dist, 'verified_by' => 'GPS coordinates']
                    );
                }
            }
        }

        WreckTrekRoute::whereNull('user_id')->delete();
        WreckTrekRoute::create([
            'user_id' => null,
            'name' => 'Pompano Wreck Trek',
            'stops' => ['lady-luck', 'rodeo-25', 'mariner-2-tug', 'union-express', 'mariner-2-barge'],
            'is_public' => true,
        ]);

        WreckTrekData::forget();
    }

    private function bearingAndDistance(Site $a, Site $b): array
    {
        $la1 = deg2rad($a->decimalLatitude());
        $la2 = deg2rad($b->decimalLatitude());
        $dl = deg2rad($b->decimalLongitude() - $a->decimalLongitude());

        $hdg = rad2deg(atan2(sin($dl) * cos($la2), cos($la1) * sin($la2) - sin($la1) * cos($la2) * cos($dl)));
        $h = sin(($la2 - $la1) / 2) ** 2 + cos($la1) * cos($la2) * sin($dl / 2) ** 2;

        return [((int) round($hdg) + 360) % 360, (int) round(2 * self::EARTH_RADIUS_FT * asin(sqrt($h)))];
    }
}

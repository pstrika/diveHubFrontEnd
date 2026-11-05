<?php

namespace App\Support;

use App\Models\Photo;
use App\Models\Site;
use App\Models\WeatherLocation;
use App\Models\WreckTrek;
use App\Models\WreckTrekLeg;
use App\Models\WreckTrekRoute;
use Illuminate\Support\Facades\Cache;

/**
 * The JSON the Wreck Trek page runs on: the predefined treks, every wreck in
 * the database keyed by slug (no reefs), measured legs and curated routes. Cached 10 minutes; only an admin edit changes it,
 * and those call forget().
 */
final class WreckTrekData
{
    public const SITTINGS = ['upright' => 'Upright', 'upside_down' => 'Upside down', 'port' => 'On its port side', 'starboard' => 'On its starboard side'];

    private const CACHE_KEY = 'wrecktrek.data';
    private const SITE_PAGE_LEGS = 5;
    /** Ship types for objects that are not wrecks but have their own chart icon. */
    public const OBJECT_TYPES = ['Army Tank', 'Radio Tower'];
    private const EARTH_RADIUS_FT = 20902231;

    public static function load(): array
    {
        return Cache::remember(self::CACHE_KEY, 600, function () {
            $names = WeatherLocation::all()->mapWithKeys(fn ($l) => [$l->short => ucwords($l->location)]);
            $treks = WreckTrek::get()->sortBy(fn ($t) => [$names[$t->location] ?? 'zzz', $t->name])->values();
            $trekSlugs = $treks->pluck('slug', 'id');
            $sites = self::eligible()->whereNotNull('slug')->orderBy('name')->get();
            $slugs = $sites->pluck('slug', 'id');
            $covers = Photo::coversFor($sites->pluck('id'));

            $wrecks = [];
            foreach ($sites as $s) {
                $details = json_decode((string) $s->wreckData, true) ?: [];
                $wrecks[$s->slug] = [
                    'name' => $s->name,
                    'trek' => $trekSlugs[$s->wreck_trek_id] ?? null,
                    'sand' => (int) $s->maxDepth,
                    'type' => self::chartIcon($details['type'] ?? null),
                    'lie' => (int) $s->trek_lie_deg,
                    'length' => self::wreckFt($s->wreckData, 'length'),
                    'beam' => self::wreckFt($s->wreckData, 'beam'),
                    'broken' => (bool) $s->trek_broken,
                    'sitting' => $s->trek_sitting,
                    // A path, not a full URL: this is cached, and the address isn't always this site's.
                    'photo' => ($file = $covers->get($s->id)?->file) ? parse_url(SitePhoto::thumb($file), PHP_URL_PATH) : null,
                    'lat' => $s->decimalLatitude(),
                    'lon' => $s->decimalLongitude(),
                ];
            }

            $legs = [];
            foreach (WreckTrekLeg::orderBy('id')->get() as $l) {
                if (!isset($slugs[$l->from_site_id], $slugs[$l->to_site_id])) continue;
                $legs[] = [
                    'id' => $l->id,
                    'from' => $slugs[$l->from_site_id],
                    'to' => $slugs[$l->to_site_id],
                    'hdg' => $l->heading_deg,
                    'dist' => $l->distance_ft,
                    'notes' => $l->notes,
                    'verifiedBy' => $l->verified_by,
                    'verifiedOn' => $l->verified_on?->toDateString(),
                ];
            }

            $routes = WreckTrekRoute::where('is_public', true)->orderBy('name')->get()
                ->map(fn ($r) => $r->toApi())->all();

            return [
                // A trek with no wrecks yet stays out of the planner.
                'treks' => $treks->filter(fn ($t) => collect($wrecks)->contains('trek', $t->slug))
                    ->map(fn ($t) => ['slug' => $t->slug, 'name' => $t->name, 'location' => $names[$t->location] ?? 'Other', 'notes' => $t->notes])->values()->all(),
                'wrecks' => $wrecks,
                'legs' => $legs,
                'routes' => $routes,
            ];
        });
    }

    /** The sites the planner can use: wrecks, plus the odd non-wreck object with its own icon (an army tank, radio towers). */
    public static function eligible()
    {
        return Site::where('_hidden', 0)->where(function ($q) {
            $q->where('type', 'wreck');
            foreach (self::OBJECT_TYPES as $objectType) {
                $q->orWhere('wreckData', 'like', '%"type":"' . $objectType . '"%');
            }
        });
    }

    /** The chart icon, from the wreck's ship type. */
    private static function chartIcon(?string $wreckType): string
    {
        return match ($wreckType) {
            'Barge', 'Drydock' => 'barge',
            'Army Tank' => 'tank',
            'Radio Tower' => 'pyramids',
            default => 'ship',
        };
    }

    /** A size in feet from the site's wreckData JSON, or null if it has none. */
    private static function wreckFt(?string $wreckData, string $key): ?int
    {
        $value = json_decode($wreckData ?? '', true)[$key] ?? null;

        return is_numeric($value) && $value > 0 ? (int) round($value) : null;
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /** The first unknown stop in a route, as a message, or null when every stop is a wreck. */
    public static function stopsProblem(array $stops): ?array
    {
        $wrecks = self::load()['wrecks'];
        foreach ($stops as $slug) {
            if (!is_string($slug) || !isset($wrecks[$slug])) {
                return ['message' => 'Unknown wreck: ' . (is_string($slug) ? $slug : 'invalid stop') . '.', 'pair' => null];
            }
        }

        return null;
    }

    /** The name of the trek a wreck belongs to, for its site page. */
    public static function trekName(string $slug): ?string
    {
        $data = self::load();
        $trek = $data['wrecks'][$slug]['trek'] ?? null;

        return collect($data['treks'])->firstWhere('slug', $trek)['name'] ?? null;
    }

    /** Legs to the nearest wrecks in the same trek: the stored one if any, otherwise from GPS. */
    public static function legsFrom(string $slug): array
    {
        $data = self::load();
        $wreck = $data['wrecks'][$slug] ?? null;
        if (!$wreck || !$wreck['trek']) return [];

        $stored = [];
        foreach ($data['legs'] as $l) {
            if ($l['from'] === $slug) $stored[$l['to']] = [$l['hdg'], $l['dist'], $l['verifiedOn'] !== null];
            if ($l['to'] === $slug) $stored[$l['from']] = [($l['hdg'] + 180) % 360, $l['dist'], $l['verifiedOn'] !== null];
        }

        $out = [];
        foreach ($data['wrecks'] as $to => $other) {
            if ($to === $slug || $other['trek'] !== $wreck['trek']) continue;
            [$hdg, $dist, $verified] = $stored[$to] ?? [...self::bearingAndDistance($wreck, $other), false];
            $out[] = ['slug' => $to, 'name' => $other['name'], 'hdg' => $hdg, 'dist' => $dist, 'verified' => $verified];
        }

        usort($out, fn ($a, $b) => $a['dist'] <=> $b['dist']);

        return array_slice($out, 0, self::SITE_PAGE_LEGS);
    }

    /** Heading (true) and distance in feet between two wrecks' GPS positions. */
    private static function bearingAndDistance(array $a, array $b): array
    {
        $la1 = deg2rad($a['lat']);
        $la2 = deg2rad($b['lat']);
        $dl = deg2rad($b['lon'] - $a['lon']);

        $hdg = rad2deg(atan2(sin($dl) * cos($la2), cos($la1) * sin($la2) - sin($la1) * cos($la2) * cos($dl)));
        $h = sin(($la2 - $la1) / 2) ** 2 + cos($la1) * cos($la2) * sin($dl / 2) ** 2;

        return [((int) round($hdg) + 360) % 360, (int) round(2 * self::EARTH_RADIUS_FT * asin(sqrt($h)))];
    }
}

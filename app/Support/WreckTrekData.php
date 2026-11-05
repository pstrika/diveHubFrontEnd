<?php

namespace App\Support;

use App\Models\Photo;
use App\Models\Site;
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
    public const TYPES = ['ship', 'barge'];
    public const SITTINGS = ['upright' => 'Upright', 'upside_down' => 'Upside down', 'port' => 'On its port side', 'starboard' => 'On its starboard side'];

    private const CACHE_KEY = 'wrecktrek.data';
    private const SITE_PAGE_LEGS = 5;

    public static function load(): array
    {
        return Cache::remember(self::CACHE_KEY, 600, function () {
            $treks = WreckTrek::orderBy('sort')->get();
            $trekSlugs = $treks->pluck('slug', 'id');
            $sites = Site::where('type', 'wreck')->where('_hidden', 0)->whereNotNull('slug')->orderBy('name')->get();
            $slugs = $sites->pluck('slug', 'id');
            $covers = Photo::coversFor($sites->pluck('id'));

            $wrecks = [];
            foreach ($sites as $s) {
                $details = json_decode((string) $s->wreckData, true) ?: [];
                $wrecks[$s->slug] = [
                    'name' => $s->name,
                    'trek' => $trekSlugs[$s->wreck_trek_id] ?? null,
                    'sand' => (int) ($s->trek_sand_ft ?? $s->maxDepth),
                    'type' => $s->trek_type ?? self::chartIcon($details['type'] ?? null),
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
                'treks' => $treks->map(fn ($t) => ['slug' => $t->slug, 'name' => $t->name])->all(),
                'wrecks' => $wrecks,
                'legs' => $legs,
                'routes' => $routes,
            ];
        });
    }

    /** The chart icon for a wreck that hasn't been given one in the site editor. */
    private static function chartIcon(?string $wreckType): string
    {
        return in_array($wreckType, ['Barge', 'Drydock'], true) ? 'barge' : 'ship';
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

    /** The legs to a wreck's nearest neighbours, reverse legs derived, for its site page. */
    public static function legsFrom(string $slug): array
    {
        $data = self::load();
        $out = [];
        foreach ($data['legs'] as $l) {
            if ($l['from'] === $slug) {
                $to = $l['to'];
                $hdg = $l['hdg'];
            } elseif ($l['to'] === $slug) {
                $to = $l['from'];
                $hdg = ($l['hdg'] + 180) % 360;
            } else {
                continue;
            }
            $out[] = ['slug' => $to, 'name' => $data['wrecks'][$to]['name'], 'hdg' => $hdg, 'dist' => $l['dist'], 'verified' => $l['verifiedOn'] !== null];
        }

        usort($out, fn ($a, $b) => $a['dist'] <=> $b['dist']);

        return array_slice($out, 0, self::SITE_PAGE_LEGS);
    }
}

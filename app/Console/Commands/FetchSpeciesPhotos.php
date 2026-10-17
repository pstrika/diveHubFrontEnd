<?php

namespace App\Console\Commands;

use App\Models\Species;
use App\Support\SitePhoto;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Candidate pictures for the species catalog from Wikimedia Commons (Pablo,
 * 2026-10-17): each species' Wikipedia lead image, free licenses only,
 * commercial use allowed (public domain, CC0, CC BY, CC BY-SA - never NC
 * or ND). Saves an 800px WebP to public/assets/img/species/{uuid}.webp
 * with author/license/source, as photo_status 'candidate' for Pablo to
 * keep or skip on Logbook/species/photos. Approved and rejected species
 * are never touched again.
 */
class FetchSpeciesPhotos extends Command
{
    protected $signature = 'species:fetch-photos {--only= : one species uuid} {--refetch : redo candidates too}';
    protected $description = 'Fetch freely licensed Wikimedia Commons pictures for catalog species';

    private const UA = 'DiversHubSpeciesPhotos/1.0 (https://divers-hub.com)';

    public function handle(): int
    {
        $q = Species::whereNull('user_id');
        if ($this->option('only')) $q->where('uuid', $this->option('only'));
        elseif ($this->option('refetch')) $q->where(fn ($w) => $w->whereNull('photo_status')->orWhere('photo_status', 'candidate'));
        else $q->whereNull('photo_status');

        $dir = public_path('assets/img/species');
        if (!is_dir($dir)) mkdir($dir, 0775, true);

        $found = 0; $none = [];
        foreach ($q->orderBy('common_name')->get() as $s) {
            $pick = $this->leadImage($s);
            if (!$pick) { $none[] = $s->common_name; continue; }
            $tmp = tempnam(sys_get_temp_dir(), 'sp');
            $res = Http::withHeaders(['User-Agent' => self::UA])->timeout(60)->sink($tmp)->get($pick['url']);
            $dest = $dir . '/' . $s->uuid . '.webp';
            if (!$res->successful() || !SitePhoto::resizeTo($tmp, [$dest => 800])) {
                @unlink($tmp); $none[] = $s->common_name . ' (download failed)'; continue;
            }
            @unlink($tmp);
            $s->forceFill([
                'photo' => $s->uuid . '.webp', 'photo_status' => 'candidate',
                'photo_credit' => $pick['credit'], 'photo_license' => $pick['license'],
                'photo_license_url' => $pick['license_url'], 'photo_source_url' => $pick['source'],
            ])->save();
            $found++;
            $this->line("ok  {$s->common_name}  [{$pick['license']}] {$pick['credit']}");
            usleep(300000);
        }
        $this->info("$found pictures fetched; " . count($none) . ' without a usable free picture.');
        foreach ($none as $n) $this->line("  - $n");
        return 0;
    }

    /** The Wikipedia lead image (free only), with its Commons metadata, or null. */
    private function leadImage(Species $s): ?array
    {
        foreach (array_filter([$s->scientific_name, $s->common_name]) as $title) {
            $page = $this->api('https://en.wikipedia.org/w/api.php', [
                'action' => 'query', 'titles' => $title, 'redirects' => 1, 'prop' => 'pageimages',
                'piprop' => 'name', 'pilicense' => 'free', 'format' => 'json', 'formatversion' => 2,
            ]);
            $file = $page['query']['pages'][0]['pageimage'] ?? null;
            if (!$file) continue;

            $info = $this->api('https://commons.wikimedia.org/w/api.php', [
                'action' => 'query', 'titles' => 'File:' . $file, 'prop' => 'imageinfo',
                'iiprop' => 'url|extmetadata', 'iiurlwidth' => 1200, 'format' => 'json', 'formatversion' => 2,
            ]);
            $ii = $info['query']['pages'][0]['imageinfo'][0] ?? null;
            if (!$ii) continue;
            $meta = $ii['extmetadata'] ?? [];
            $license = trim(strip_tags($meta['LicenseShortName']['value'] ?? ''));
            if (!self::commercialOk($license)) continue;
            $credit = trim(html_entity_decode(strip_tags($meta['Artist']['value'] ?? ''), ENT_QUOTES)) ?: 'Unknown author';
            return [
                'url' => $ii['thumburl'] ?? $ii['url'],
                'license' => mb_substr($license, 0, 60),
                'license_url' => $meta['LicenseUrl']['value'] ?? null,
                'credit' => mb_substr(preg_replace('/\s+/', ' ', $credit), 0, 250),
                'source' => $ii['descriptionurl'] ?? null,
            ];
        }
        return null;
    }

    /** Public domain, CC0, CC BY, CC BY-SA. Never NonCommercial or NoDerivatives. */
    public static function commercialOk(string $license): bool
    {
        if ($license === '' || preg_match('/\bNC\b|\bND\b|non-?commercial|no-?deriv/i', $license)) return false;
        return (bool) preg_match('/public domain|^pd\b|cc0|^cc[ -]by([ -]sa)?[ -]?\d|^cc[ -]by([ -]sa)?$|attribution/i', $license);
    }

    private function api(string $url, array $params): array
    {
        $r = Http::withHeaders(['User-Agent' => self::UA])->timeout(30)->retry(2, 2000)->get($url, $params);
        return $r->successful() ? ($r->json() ?? []) : [];
    }
}

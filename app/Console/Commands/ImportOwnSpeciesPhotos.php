<?php

namespace App\Console\Commands;

use App\Models\Species;
use App\Support\SitePhoto;
use Illuminate\Console\Command;

/**
 * Pablo's own species photos (Graphical Assets/Species, 2026-10-17) replace
 * the Wikimedia Commons pictures. Files are matched to catalog species by
 * name (common name or other names, ignoring case, spaces and punctuation);
 * a few need a hand-made match, listed below. Shown straight away
 * (photo_status approved, photo_origin own), credited "Photo: <credit>".
 * The Commons copy stays on disk under its own name, so it can come back.
 */
class ImportOwnSpeciesPhotos extends Command
{
    protected $signature = 'species:import-own-photos {dir} {--credit=Pablo Strika} {--dry-run}';
    protected $description = 'Use your own photos (named after the species) as catalog species pictures';

    /** File (without extension) => catalog common name, where the name alone doesn't match. */
    private const MANUAL = [
        'PAS_3561' => 'Loggerhead sea turtle',     // unnamed file; a loggerhead
        'Giant Anemone' => 'Giant Caribbean anemone',
        'LongSpinnedSeaUrchin' => 'Long-spined sea urchin',
        'Lionfish' => 'Red lionfish',
        'Smooth trunkfish' => null,                 // the sharper Smooth_trunkfish.jpg is used instead
        'Atlantic Salefish' => 'Atlantic sailfish',  // spelling
        'Bonnethead shark' => 'Bonnethead',
    ];

    public function handle(): int
    {
        $dir = rtrim($this->argument('dir'), '/');
        $norm = fn ($s) => preg_replace('/[^a-z]/', '', mb_strtolower((string) $s));
        $species = Species::whereNull('user_id')->get();
        $byName = [];
        foreach ($species as $s) {
            $byName[$norm($s->common_name)] = $s;
            foreach ($s->other_names ?? [] as $o) $byName[$norm($o)] ??= $s;
        }

        $done = 0; $skipped = [];
        foreach (glob($dir . '/*.{jpg,jpeg,JPG,JPEG,png,PNG,webp,WEBP}', GLOB_BRACE) as $path) {
            $stem = pathinfo($path, PATHINFO_FILENAME);
            if (array_key_exists($stem, self::MANUAL)) {
                $name = self::MANUAL[$stem];
                if ($name === null) { $skipped[] = "$stem (duplicate, other file used)"; continue; }
                $s = $species->firstWhere('common_name', $name);
            } else {
                $s = $byName[$norm($stem)] ?? null;
            }
            if (!$s) { $skipped[] = "$stem (no matching species)"; continue; }

            $file = $s->uuid . '_own.webp';
            if (!$this->option('dry-run')) {
                if (!SitePhoto::resizeTo($path, [public_path('assets/img/species/' . $file) => 1200])) {
                    $skipped[] = "$stem (couldn't resize)"; continue;
                }
                // A picture you had mirrored (fish facing right) stays mirrored.
                $wasMirrored = $s->photo_origin === 'own' && $s->photo_mirrored;
                // Replace your own earlier (mirrored) file; a Commons copy stays on disk.
                if ($s->photo && $s->photo !== $file && str_contains($s->photo, '_own')) @unlink(public_path('assets/img/species/' . $s->photo));
                $s->forceFill([
                    'photo' => $file, 'photo_status' => 'approved', 'photo_origin' => 'own', 'photo_mirrored' => false,
                    'photo_credit' => $this->option('credit'), 'photo_license' => null,
                    'photo_license_url' => null, 'photo_source_url' => null,
                ])->save();
                if ($wasMirrored) \App\Support\SpeciesPhoto::mirror($s);
            }
            $done++;
            $this->line("ok  $stem -> {$s->common_name}");
        }
        $this->info("$done species now use your photo.");
        foreach ($skipped as $s) $this->line("  - skipped $s");
        return 0;
    }
}

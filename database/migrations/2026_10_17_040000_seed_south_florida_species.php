<?php

use App\Models\Species;
use Illuminate\Database\Migrations\Migration;

/**
 * Data only: the shared marine-life catalog, 182 species commonly seen
 * diving South Florida (Pablo, 2026-10-17). Ranks copied from each
 * species' English Wikipedia taxobox (database/data/species_south_florida.json);
 * pictures come later. Skips any already in the catalog, so re-running
 * after adding to the file only adds the new ones.
 */
return new class extends Migration
{
    public function up(): void
    {
        $rows = json_decode(file_get_contents(database_path('data/species_south_florida.json')), true);
        $have = Species::whereNull('user_id')->pluck('scientific_name')->map(fn ($n) => mb_strtolower((string) $n))->flip();
        foreach ($rows as $r) {
            if (isset($have[mb_strtolower((string) $r['species'])])) continue;
            Species::create([
                'common_name' => ucfirst($r['common_name']),
                'other_names' => $r['other_names'] ?: null,
                'category' => $r['category'],
                'kingdom' => $r['kingdom'], 'phylum' => $r['phylum'], 'class' => $r['class'],
                'subclass' => $r['subclass'], 'division' => $r['division'], 'taxon_order' => $r['order'],
                'family' => $r['family'], 'genus' => $r['genus'], 'scientific_name' => $r['species'],
                'description' => $r['description'],
            ]);
        }
    }

    public function down(): void
    {
    }
};

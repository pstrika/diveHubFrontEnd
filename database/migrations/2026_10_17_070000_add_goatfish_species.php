<?php

use App\Models\Species;
use Illuminate\Database\Migrations\Migration;

/**
 * Data only: species added to database/data/species_south_florida.json
 * after the first seed (Pablo, 2026-10-17: "Goat fish was not there") -
 * yellow and spotted goatfish. Skips anything already in the catalog.
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
                'common_name' => ucfirst($r['common_name']), 'other_names' => $r['other_names'] ?: null, 'category' => $r['category'],
                'kingdom' => $r['kingdom'], 'phylum' => $r['phylum'], 'class' => $r['class'], 'subclass' => $r['subclass'],
                'division' => $r['division'], 'taxon_order' => $r['order'], 'family' => $r['family'], 'genus' => $r['genus'],
                'scientific_name' => $r['species'], 'description' => $r['description'],
            ]);
        }
    }

    public function down(): void
    {
    }
};

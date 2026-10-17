<?php

use App\Models\Species;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Where a species picture came from (Pablo, 2026-10-17: his own photos
 * replace the Wikimedia Commons ones, and the review page filters the
 * species still without one of his): 'own' or 'commons'. Also adds any
 * species new in database/data/species_south_florida.json (clown wrasse).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('species', function (Blueprint $table) {
            $table->string('photo_origin', 12)->nullable()->after('photo_status');
        });
        Species::whereNotNull('photo_source_url')->update(['photo_origin' => 'commons']);

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
        Schema::table('species', function (Blueprint $table) {
            $table->dropColumn('photo_origin');
        });
    }
};

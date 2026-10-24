<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Optional search title for a blog post (SEO audit, 2026-10-24): the
 * title Google shows when the on-page title runs past ~60 characters.
 * Replaces the hard-coded short-title list in BlogController, and is
 * backfilled from it plus the two long posts added since.
 */
return new class extends Migration
{
    private const BACKFILL = [
        'lionfish-invasion-what-divers-can-do' => 'The Lionfish Invasion: What Divers Can Do',
        'open-circuit-vs-closed-circuit' => 'Open Circuit vs. Closed Circuit Diving',
        'seasonal-diving-conditions-in-the-florida-keys-a-month-by-month-guide-for-local-divers' => 'Florida Keys Diving Conditions by Month',
        'lionfish-with-open-sores' => 'Lionfish With Open Sores: What Hunters Should Know',
        'the-300-foot-rule-floridas-dive-flag-law-and-why-it-cuts-both-ways' => "The 300-Foot Rule: Florida's Dive Flag Law",
        'protecting-blue-heron-bridge-why-we-signed-the-petition-against-singer-island-gateway' => 'Why We Signed the Blue Heron Bridge Petition',
        'three-tips-for-better-underwater-photos' => '3 Tips for Better Underwater Photos',
    ];

    public function up(): void
    {
        if (!Schema::hasColumn('posts', 'seo_title')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->string('seo_title', 70)->nullable()->after('title');
            });
        }
        foreach (self::BACKFILL as $slug => $title) {
            DB::table('posts')->where('slug', $slug)->whereNull('seo_title')->update(['seo_title' => $title]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('posts', 'seo_title')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->dropColumn('seo_title');
            });
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A bare list of Site ids loses the one thing a real "Top 5" listicle
 * actually needs: a short editorial note per site ("Spiegel Grove - a
 * 510-foot Navy landing ship..."), not just its generic facts. Replaces
 * related_site_ids with related_sites, a JSON array of {site_id, note} in
 * rank order - same nullable/optional behavior (empty means no citations,
 * "not in all cases we will have rankings" - Pablo, 2026-09-17), same
 * cross-connection reasoning as before. Table has zero rows at this point
 * (previous migration in the same batch), so no data to migrate.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('related_site_ids');
        });
        Schema::table('posts', function (Blueprint $table) {
            $table->json('related_sites')->nullable()->after('cover_image');
        });
    }

    public function down()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('related_sites');
        });
        Schema::table('posts', function (Blueprint $table) {
            $table->string('related_site_ids')->nullable()->after('cover_image');
        });
    }
};

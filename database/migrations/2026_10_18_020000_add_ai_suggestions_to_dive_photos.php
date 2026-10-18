<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "What's this?" (Pablo, 2026-10-18): the AI's species suggestions for a
 * dive photo, kept so asking again is free and so we can see how well it
 * does. {model, at, candidates: [{uuid, confidence}], not_in_catalog}.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dive_photos', function (Blueprint $table) {
            $table->json('ai_suggestions')->nullable()->after('shared_diver_photo_id');
        });
    }

    public function down(): void
    {
        Schema::table('dive_photos', function (Blueprint $table) {
            $table->dropColumn('ai_suggestions');
        });
    }
};

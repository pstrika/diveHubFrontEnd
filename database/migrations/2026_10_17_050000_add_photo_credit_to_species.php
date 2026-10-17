<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Species pictures from Wikimedia Commons (Pablo, 2026-10-17): free
 * licenses need the author, license and source shown with the photo.
 * photo_status: candidate (fetched, waiting for Pablo's review), approved
 * (shown), rejected (skipped - leave blank for a community photo later).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('species', function (Blueprint $table) {
            $table->string('photo_status', 12)->nullable()->after('photo');
            $table->string('photo_credit', 255)->nullable()->after('photo_status');
            $table->string('photo_license', 60)->nullable()->after('photo_credit');
            $table->string('photo_license_url', 255)->nullable()->after('photo_license');
            $table->string('photo_source_url', 500)->nullable()->after('photo_license_url');
        });
    }

    public function down(): void
    {
        Schema::table('species', function (Blueprint $table) {
            $table->dropColumn(['photo_status', 'photo_credit', 'photo_license', 'photo_license_url', 'photo_source_url']);
        });
    }
};

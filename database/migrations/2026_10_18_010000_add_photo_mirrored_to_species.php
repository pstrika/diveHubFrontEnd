<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Species pictures mirrored so every fish faces right (Pablo, 2026-10-18).
 * A mirrored Commons picture is a change to the work, so its credit line
 * says so (CC BY / BY-SA ask for changes to be indicated).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('species', function (Blueprint $table) {
            $table->boolean('photo_mirrored')->default(false)->after('photo_origin');
        });
    }

    public function down(): void
    {
        Schema::table('species', function (Blueprint $table) {
            $table->dropColumn('photo_mirrored');
        });
    }
};

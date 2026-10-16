<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Logbook setting "Auto-merge dives logged by several computers" (Pablo,
 * 2026-10-16). Off by default: a dive that looks like another computer's
 * log of a dive already on file is kept and the diver is asked (844.a /
 * 844.b). On: sure matches are attached without asking.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'logbook_auto_merge')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('logbook_auto_merge')->default(false);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'logbook_auto_merge')) {
            Schema::table('users', fn (Blueprint $table) => $table->dropColumn('logbook_auto_merge'));
        }
    }
};

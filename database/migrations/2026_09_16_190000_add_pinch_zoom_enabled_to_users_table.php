<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Accessibility opt-in: lets a diver re-enable pinch-to-zoom in the
 * installed PWA, which is blocked by default there (Pablo, 2026-09-16:
 * "I like the behavior we see now...we can leave it as is, and add an
 * Accessibility Aid toggle...so users can choose to enable zoom in
 * screen"). Defaults to 0/off (today's behavior) for every existing and
 * new user - this is purely an opt-in escape hatch, not a policy change.
 *
 * Runs on the users database (default 'mysql' connection). The deploy
 * workflow does not run migrations, so this is run by hand after deploying.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'pinch_zoom_enabled')) {
                $table->boolean('pinch_zoom_enabled')->default(false)->after('deco_setpoint');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'pinch_zoom_enabled')) {
                $table->dropColumn('pinch_zoom_enabled');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets a registered diver save their preferred GF Low/High and CCR setpoint
 * from the Decompression Dive Planner, so they don't have to re-enter them
 * every visit (Pablo, 2026-09-18). Guests (the shared account the 'guest'
 * middleware auto-logs anonymous visitors in as) never get to save these -
 * see App\Http\Middleware\EnsureNotGuest.
 *
 * Runs on the users database (default 'mysql' connection). The deploy
 * workflow does not run migrations, so this is run by hand after deploying.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'deco_gf_low')) {
                $table->unsignedTinyInteger('deco_gf_low')->nullable()->after('deco_unit');
            }
            if (!Schema::hasColumn('users', 'deco_gf_high')) {
                $table->unsignedTinyInteger('deco_gf_high')->nullable()->after('deco_gf_low');
            }
            if (!Schema::hasColumn('users', 'deco_setpoint')) {
                $table->decimal('deco_setpoint', 3, 2)->nullable()->after('deco_gf_high');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['deco_gf_low', 'deco_gf_high', 'deco_setpoint'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

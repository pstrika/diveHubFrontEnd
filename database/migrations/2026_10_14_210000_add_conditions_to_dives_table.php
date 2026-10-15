<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dive conditions (Pablo, 2026-10-14): the diver-entered facts a dive
 * computer can't report - who ran the trip, water/weather conditions,
 * buddies, dive type, notes. One JSON blob rather than a column per field,
 * same reasoning as deco_plans.inputs - it's always read/edited as a whole
 * on the dive detail page, never individually queried or filtered on
 * (nobody searches "dives where visibility was poor" yet), and the exact
 * field set is still settling (see docs/roadmap.md #9 for what's next).
 * If filtering by one of these ever becomes a real feature, promote that
 * one field out to its own column then - not a reason to do it now.
 *
 * A SEPARATE, additive migration from the one that created `dives`
 * (2026_10_14_160000) rather than amending that one in place - unlike
 * earlier same-day schema tweaks, real dive data (47 rows, synced from
 * Pablo's actual Shearwater over real BLE hardware) now lives in that
 * table, so it's no longer safe to drop/recreate it.
 *
 * Runs on the users database (default 'mysql' connection), same as
 * `dives` itself. The deploy workflow does not run migrations, so this is
 * run by hand after deploying.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('dives', function (Blueprint $table) {
            // {operator_name, divemaster, boat_name, boat_captain, current,
            //  weather, surface_conditions, entry_type, visibility, buddies,
            //  dive_type}
            $table->json('conditions')->nullable()->after('notes');
        });
    }

    public function down()
    {
        Schema::table('dives', function (Blueprint $table) {
            $table->dropColumn('conditions');
        });
    }
};

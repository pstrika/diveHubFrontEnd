<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Up to 5 free-text tags per dive (Pablo, 2026-10-15: "we will use this
 * to group dives later"). A plain JSON array on `dives`, not a tags
 * table/pivot - there's no grouping/filtering feature built yet to
 * justify one (that's explicitly future work per Pablo's own framing),
 * and a handful of free-text strings is exactly the "read/written as a
 * whole, never individually queried" shape the rest of this table
 * already uses JSON columns for (conditions, sensor_data, etc). Promote
 * to a real table if/when tag-based filtering actually gets built.
 *
 * Runs on the users database (default 'mysql' connection), same as the
 * rest of `dives`. The deploy workflow does not run migrations, so this
 * is run by hand after deploying.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('dives', function (Blueprint $table) {
            $table->json('tags')->nullable()->after('conditions');
        });
    }

    public function down()
    {
        Schema::table('dives', function (Blueprint $table) {
            $table->dropColumn('tags');
        });
    }
};

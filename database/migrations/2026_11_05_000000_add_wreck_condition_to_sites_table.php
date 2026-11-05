<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * How a wreck lies on the bottom, for its chart icon: broken means the bow is
 * separated from the stern; sitting is upright, upside_down, port or starboard.
 * The chart now only has ship and barge icons, so tugs and the like become ships.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::connection('mysql_trips')->table('sites', function (Blueprint $table) {
            $table->boolean('trek_broken')->default(false);
            $table->string('trek_sitting', 12)->default('upright');
        });

        DB::connection('mysql_trips')->table('sites')
            ->whereNotNull('trek_type')->where('trek_type', '!=', 'barge')
            ->update(['trek_type' => 'ship']);
    }

    public function down()
    {
        Schema::connection('mysql_trips')->table('sites', function (Blueprint $table) {
            $table->dropColumn(['trek_broken', 'trek_sitting']);
        });
    }
};

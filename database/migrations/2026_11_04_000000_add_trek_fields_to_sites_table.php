<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Wreck Trek (spec 2026-11-04): a wreck is an existing dive site, not a new
 * table. A site with trek_type set is a trek wreck. Depths are feet, lie is
 * the direction the bow points on the bottom (degrees true).
 */
return new class extends Migration
{
    public function up()
    {
        Schema::connection('mysql_trips')->table('sites', function (Blueprint $table) {
            $table->unsignedSmallInteger('trek_top_ft')->nullable();
            $table->unsignedSmallInteger('trek_sand_ft')->nullable();
            $table->string('trek_type', 10)->nullable(); // ship|barge|bow|stern|tug|debris
            $table->unsignedSmallInteger('trek_lie_deg')->nullable();
        });
    }

    public function down()
    {
        Schema::connection('mysql_trips')->table('sites', function (Blueprint $table) {
            $table->dropColumn(['trek_top_ft', 'trek_sand_ft', 'trek_type', 'trek_lie_deg']);
        });
    }
};

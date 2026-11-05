<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Wreck Trek only uses a wreck's max depth, so the top depth (which was the
 * site's avgDepth, still on the site) is gone.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::connection('mysql_trips')->table('sites', function (Blueprint $table) {
            $table->dropColumn('trek_top_ft');
        });
    }

    public function down()
    {
        Schema::connection('mysql_trips')->table('sites', function (Blueprint $table) {
            $table->unsignedSmallInteger('trek_top_ft')->nullable();
        });
    }
};

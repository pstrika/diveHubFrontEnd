<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The admin-picked hero photo for a dive site (Pablo, 2026-09-28): the
 * first picture on the site page, the site card picture everywhere, and
 * the link preview image. Null means "not picked", which keeps the old
 * behavior (the site's oldest photo) - see Photo::scopeHeroFirst().
 * Nullable and additive, so code without this feature ignores it.
 */
return new class extends Migration
{
    protected $connection = 'mysql_trips';

    public function up()
    {
        Schema::connection('mysql_trips')->table('sites', function (Blueprint $table) {
            $table->unsignedBigInteger('heroPhotoId')->nullable();
        });
    }

    public function down()
    {
        Schema::connection('mysql_trips')->table('sites', function (Blueprint $table) {
            $table->dropColumn('heroPhotoId');
        });
    }
};

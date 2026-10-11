<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * An admin-written short description, used as the image's ALT text on the
 * site page - not diver-written (Pablo, 2026-10-11: "I don't want the
 * divers to add a description, I think that's something that the admin
 * (me) should do... in the image approval console"). SiteDetails.blade.php
 * already reads $p->desc for a diver photo's alt/aria-label - it's been
 * silently falling back to a generic "{site name} dive site photo" for
 * every diver photo until now, since this column never existed.
 */
return new class extends Migration
{
    protected $connection = 'mysql_trips';

    public function up()
    {
        Schema::connection('mysql_trips')->table('diver_photos', function (Blueprint $table) {
            $table->string('desc', 150)->nullable()->after('file');
        });
    }

    public function down()
    {
        Schema::connection('mysql_trips')->table('diver_photos', function (Blueprint $table) {
            $table->dropColumn('desc');
        });
    }
};

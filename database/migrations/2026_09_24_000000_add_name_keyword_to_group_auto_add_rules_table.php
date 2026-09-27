<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A free-text OR clause on the "Trip type" box (Pablo, 2026-09-24: "add an
 * 'OR' condition...a text box that the user can type something...to get
 * dives that may be labeled as 'hunting' or 'lionfish'"). A trip matches
 * the trip-type criterion when it has one of the selected type chips OR
 * its name contains this keyword/phrase (case-insensitive substring) - see
 * GroupAutoAddRule::matchesTripType().
 */
return new class extends Migration
{
    protected $connection = 'mysql_trips';

    public function up()
    {
        Schema::connection('mysql_trips')->table('group_auto_add_rules', function (Blueprint $table) {
            $table->string('name_keyword', 100)->nullable()->after('trip_types');
        });
    }

    public function down()
    {
        Schema::connection('mysql_trips')->table('group_auto_add_rules', function (Blueprint $table) {
            $table->dropColumn('name_keyword');
        });
    }
};

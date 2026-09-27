<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Customizable bottom nav bar (Pablo, 2026-09-24): Trips, Dashboard and
 * More stay fixed; the other two mobile tab-bar slots are pickable from
 * App\Support\NavTabs::OPTIONS. Null means "use the default" (weather /
 * groups) - see NavTabs::resolveSlot().
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nav_slot_1')->nullable()->after('pinch_zoom_enabled');
            $table->string('nav_slot_2')->nullable()->after('nav_slot_1');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nav_slot_1', 'nav_slot_2']);
        });
    }
};

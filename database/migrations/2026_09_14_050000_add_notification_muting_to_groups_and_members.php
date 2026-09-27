<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_trips';

    public function up()
    {
        Schema::connection('mysql_trips')->table('groups', function (Blueprint $table) {
            // Admin-only "mute all": overrides every member's own bell
            // toggle below - a group admin silencing the group silences
            // it for everyone (Pablo, 2026-09-14).
            $table->boolean('notifications_muted')->default(false)->after('digest_enabled');
        });

        Schema::connection('mysql_trips')->table('group_members', function (Blueprint $table) {
            // Per-member bell toggle - defaults off (notifications ON)
            // so an invited member starts subscribed, per Pablo: "by
            // default when invited notifications should be on."
            $table->boolean('notifications_muted')->default(false)->after('status');
        });
    }

    public function down()
    {
        Schema::connection('mysql_trips')->table('groups', function (Blueprint $table) {
            $table->dropColumn('notifications_muted');
        });

        Schema::connection('mysql_trips')->table('group_members', function (Blueprint $table) {
            $table->dropColumn('notifications_muted');
        });
    }
};

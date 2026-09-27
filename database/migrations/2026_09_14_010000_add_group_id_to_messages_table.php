<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_trips';

    public function up()
    {
        Schema::connection('mysql_trips')->table('messages', function (Blueprint $table) {
            // Which group this notification is about, so the notification
            // center can put it in its own "Groups" folder instead of the
            // general Inbox (Pablo, 2026-09-14: "Inbox is mostly for system
            // notifications and other notifications"). No DB-level foreign
            // key, matching every other group_id column in this schema
            // (group_members, group_dives, etc. are all plain integers too -
            // groups.id is an unsigned INT via increments(), not BIGINT, so
            // a real FK here would need a mismatched-width column to match
            // it). Null for anything not group-related (account/system
            // notifications) - those stay in the Inbox.
            $table->unsignedInteger('group_id')->nullable()->after('from_user_id');
            $table->index(['userId', 'group_id', 'deleted']);
        });
    }

    public function down()
    {
        Schema::connection('mysql_trips')->table('messages', function (Blueprint $table) {
            $table->dropIndex(['userId', 'group_id', 'deleted']);
            $table->dropColumn('group_id');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nullable: ordinary diver-gallery uploads via DiverPhotoController have no
 * specific dive occurrence, only a site. Photos submitted through the
 * post-dive feedback wizard set this so SendDivePhotoReminders can tell
 * whether a reminder is still needed (Pablo, 2026-10-04).
 */
return new class extends Migration
{
    protected $connection = 'mysql_trips';

    public function up()
    {
        Schema::connection('mysql_trips')->table('diver_photos', function (Blueprint $table) {
            // camelCase to match every other column on this legacy table
            // (siteId, userId, reviewedBy) - the new standalone tables in
            // this migration batch use snake_case instead, matching the
            // more recent group_dive_reminders_sent/group_auto_add_rules
            // convention.
            $table->unsignedBigInteger('eventId')->nullable()->index()->after('userId');
        });
    }

    public function down()
    {
        Schema::connection('mysql_trips')->table('diver_photos', function (Blueprint $table) {
            $table->dropColumn('eventId');
        });
    }
};

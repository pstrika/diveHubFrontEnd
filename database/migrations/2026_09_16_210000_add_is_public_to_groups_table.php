<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Public Groups (Pablo, 2026-09-16): the simplest version of the idea -
 * reuse the existing group entirely, just add a flag. A public group skips
 * the invite flow (anyone can find it and join directly); every other
 * setting - allow_members_add_dives, reminders, digest, mute, Facebook -
 * is completely unaffected and keeps whatever the group already had.
 *
 * "Only platform admins can create" was an earlier, more complex draft of
 * this feature (a whole new PublicGroupAdmin role) - dropped in favor of
 * "any group's own admin can flip this flag", with a one-public-group-per-
 * admin cap enforced in the controller (not the schema) to stop sprawl,
 * waived for platform admins (role_id 1).
 */
return new class extends Migration
{
    protected $connection = 'mysql_trips';

    public function up()
    {
        Schema::connection('mysql_trips')->table('groups', function (Blueprint $table) {
            $table->boolean('is_public')->default(false)->after('notifications_muted');
        });
    }

    public function down()
    {
        Schema::connection('mysql_trips')->table('groups', function (Blueprint $table) {
            $table->dropColumn('is_public');
        });
    }
};

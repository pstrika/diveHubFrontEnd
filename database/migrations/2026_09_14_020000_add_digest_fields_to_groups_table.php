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
            // Separate from reminders_enabled on purpose - that one is
            // specifically labelled "trip reminders" in the group's own
            // settings modal, and an admin who already made a choice there
            // shouldn't have it silently start covering a different email too.
            $table->boolean('digest_enabled')->default(true)->after('reminders_enabled');
            // Window start for "what happened since we last checked", not
            // "since we last actually sent an email" - a group with nothing
            // to report still advances this every run (see
            // SendGroupActivityDigest), so a quiet group doesn't build up a
            // growing backlog that dumps months of history the moment
            // something finally happens.
            $table->timestamp('last_digest_sent_at')->nullable()->after('digest_enabled');
        });
    }

    public function down()
    {
        Schema::connection('mysql_trips')->table('groups', function (Blueprint $table) {
            $table->dropColumn(['digest_enabled', 'last_digest_sent_at']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_trips';

    public function up()
    {
        // user_id must become nullable to represent an invite sent to an
        // email address that has no Divers Hub account yet. Raw SQL since
        // doctrine/dbal (required by Schema::table()->...->change()) isn't
        // installed.
        DB::connection('mysql_trips')->statement('ALTER TABLE group_members MODIFY user_id INT NULL');

        Schema::connection('mysql_trips')->table('group_members', function (Blueprint $table) {
            $table->string('invited_email')->nullable()->after('user_id');
            $table->unique(['group_id', 'invited_email'], 'group_members_group_invited_email_unique');
        });
    }

    public function down()
    {
        Schema::connection('mysql_trips')->table('group_members', function (Blueprint $table) {
            $table->dropUnique('group_members_group_invited_email_unique');
            $table->dropColumn('invited_email');
        });

        DB::connection('mysql_trips')->statement('ALTER TABLE group_members MODIFY user_id INT NOT NULL');
    }
};

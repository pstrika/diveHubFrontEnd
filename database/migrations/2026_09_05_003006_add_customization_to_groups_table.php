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
            $table->string('banner', 400)->nullable()->after('description');
            $table->string('avatar', 400)->nullable()->after('banner');
            $table->string('calendar_token', 64)->nullable()->unique()->after('avatar');
        });
    }

    public function down()
    {
        Schema::connection('mysql_trips')->table('groups', function (Blueprint $table) {
            $table->dropUnique(['groups_calendar_token_unique']);
            $table->dropColumn(['banner', 'avatar', 'calendar_token']);
        });
    }
};

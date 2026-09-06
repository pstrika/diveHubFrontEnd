<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_trips';

    public function up()
    {
        Schema::connection('mysql_trips')->table('group_dives', function (Blueprint $table) {
            $table->string('fb_post_id')->nullable()->after('is_custom');
        });
    }

    public function down()
    {
        Schema::connection('mysql_trips')->table('group_dives', function (Blueprint $table) {
            $table->dropColumn('fb_post_id');
        });
    }
};

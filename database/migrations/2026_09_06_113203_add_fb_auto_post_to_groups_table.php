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
            $table->boolean('fb_auto_post')->default(true)->after('fb_connected_at');
        });
    }

    public function down()
    {
        Schema::connection('mysql_trips')->table('groups', function (Blueprint $table) {
            $table->dropColumn('fb_auto_post');
        });
    }
};

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
            $table->string('fb_page_id')->nullable()->after('allow_members_add_dives');
            $table->string('fb_page_name')->nullable()->after('fb_page_id');
            $table->text('fb_page_access_token')->nullable()->after('fb_page_name');
            $table->unsignedBigInteger('fb_connected_by')->nullable()->after('fb_page_access_token');
            $table->timestamp('fb_connected_at')->nullable()->after('fb_connected_by');
        });
    }

    public function down()
    {
        Schema::connection('mysql_trips')->table('groups', function (Blueprint $table) {
            $table->dropColumn(['fb_page_id', 'fb_page_name', 'fb_page_access_token', 'fb_connected_by', 'fb_connected_at']);
        });
    }
};

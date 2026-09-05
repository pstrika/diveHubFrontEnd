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
            $table->boolean('reminders_enabled')->default(true)->after('calendar_token');
        });

        Schema::connection('mysql_trips')->table('group_dives', function (Blueprint $table) {
            $table->unsignedBigInteger('siteId')->nullable()->after('tripName');
            $table->string('departingFrom')->nullable()->after('siteId');
            $table->boolean('is_custom')->default(false)->after('departingFrom');
        });

        Schema::connection('mysql_trips')->create('group_favorite_operators', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id');
            $table->unsignedBigInteger('operator_id');
            $table->timestamps();
            $table->unique(['group_id', 'operator_id']);
        });

        Schema::connection('mysql_trips')->create('group_dive_reminders_sent', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_dive_id');
            $table->string('type', 10); // '3day' or '1day'
            $table->timestamp('sent_at')->useCurrent();
            $table->unique(['group_dive_id', 'type']);
        });
    }

    public function down()
    {
        Schema::connection('mysql_trips')->dropIfExists('group_dive_reminders_sent');
        Schema::connection('mysql_trips')->dropIfExists('group_favorite_operators');

        Schema::connection('mysql_trips')->table('group_dives', function (Blueprint $table) {
            $table->dropColumn(['siteId', 'departingFrom', 'is_custom']);
        });

        Schema::connection('mysql_trips')->table('groups', function (Blueprint $table) {
            $table->dropColumn('reminders_enabled');
        });
    }
};

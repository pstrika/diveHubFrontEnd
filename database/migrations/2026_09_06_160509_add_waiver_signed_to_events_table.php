<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_trips';

    public function up()
    {
        Schema::connection('mysql_trips')->table('events', function (Blueprint $table) {
            $table->boolean('waiver_signed')->default(false)->after('booked');
        });
    }

    public function down()
    {
        Schema::connection('mysql_trips')->table('events', function (Blueprint $table) {
            $table->dropColumn('waiver_signed');
        });
    }
};

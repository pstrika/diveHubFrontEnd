<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * This migration targets divehub-schema (mysql_trips), not the default
     * connection, so its own tracking record lives in that database too.
     */
    protected $connection = 'mysql_trips';

    public function up()
    {
        Schema::connection('mysql_trips')->table('operators', function (Blueprint $table) {
            // Mirrors sites.rate / sites.votes exactly.
            $table->float('rate')->nullable()->after('tec');
            $table->integer('votes')->nullable()->after('rate');
        });

        Schema::connection('mysql_trips')->create('operatorratings', function (Blueprint $table) {
            // Mirrors the siteratings table exactly.
            $table->increments('id');
            $table->integer('userId')->nullable();
            $table->integer('operatorId')->nullable();
            $table->integer('starRating')->nullable();
            $table->text('comment')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('created_at')->nullable();
        });
    }

    public function down()
    {
        Schema::connection('mysql_trips')->dropIfExists('operatorratings');

        Schema::connection('mysql_trips')->table('operators', function (Blueprint $table) {
            $table->dropColumn(['rate', 'votes']);
        });
    }
};

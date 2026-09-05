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
        Schema::connection('mysql_trips')->create('groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 150);
            $table->string('slug', 220)->unique();
            $table->text('description')->nullable();
            $table->integer('created_by');
            $table->timestamps();
        });

        Schema::connection('mysql_trips')->create('group_members', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('group_id');
            $table->integer('user_id');
            $table->enum('role', ['admin', 'member'])->default('member');
            $table->enum('status', ['invited', 'active'])->default('invited');
            $table->integer('invited_by')->nullable();
            $table->timestamps();

            $table->unique(['group_id', 'user_id']);
        });

        Schema::connection('mysql_trips')->create('group_dives', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('group_id');
            $table->integer('created_by');
            // Snapshot of the trip's identifying fields at add-time, matching
            // events.operatorId/date/time/tripName - trip ids churn daily
            // (re-scraped), so this composite key is the durable reference.
            $table->integer('operatorId')->nullable();
            $table->date('date');
            $table->string('time', 10)->nullable();
            $table->string('tripName', 150)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::connection('mysql_trips')->create('group_dive_rsvps', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('group_dive_id');
            $table->integer('user_id');
            $table->timestamps();

            $table->unique(['group_dive_id', 'user_id']);
        });

        Schema::connection('mysql_trips')->create('group_messages', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('group_id');
            $table->integer('user_id');
            $table->text('body')->nullable();
            $table->timestamps();
        });

        Schema::connection('mysql_trips')->create('group_message_photos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('group_message_id');
            $table->string('file', 400);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::connection('mysql_trips')->dropIfExists('group_message_photos');
        Schema::connection('mysql_trips')->dropIfExists('group_messages');
        Schema::connection('mysql_trips')->dropIfExists('group_dive_rsvps');
        Schema::connection('mysql_trips')->dropIfExists('group_dives');
        Schema::connection('mysql_trips')->dropIfExists('group_members');
        Schema::connection('mysql_trips')->dropIfExists('groups');
    }
};

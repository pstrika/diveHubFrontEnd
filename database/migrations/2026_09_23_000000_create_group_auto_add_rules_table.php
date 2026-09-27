<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One auto-add rule per group (Pablo, 2026-09-22): a trip matches when it
 * has one of trip_types, AND (its operator is in operator_ids OR its
 * location is in locations), AND (its site level is in levels OR one of
 * its sites is in site_ids) - see GroupAutoAddRule::matches().
 */
return new class extends Migration
{
    protected $connection = 'mysql_trips';

    public function up()
    {
        Schema::connection('mysql_trips')->create('group_auto_add_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id')->unique();
            $table->boolean('enabled')->default(false);
            $table->json('trip_types')->nullable();
            $table->json('operator_ids')->nullable();
            $table->json('locations')->nullable();
            $table->json('levels')->nullable();
            $table->json('site_ids')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::connection('mysql_trips')->dropIfExists('group_auto_add_rules');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Boats become the shop's to keep right (Zach, 2026-11-19).
 *
 * Same rule as operator_profiles: the shop's value is shown in place of
 * ours, and NULL means "not set, show what Divers Hub lists". The boats
 * table itself is not written to. Trips point at boats.id, and what else
 * writes to that table is not something the app can see, so a shop's
 * correction lives beside the boat, keyed by its id.
 *
 * Only the facts the operator page prints. Photos and adding or removing a
 * boat stay with Divers Hub for now.
 */
return new class extends Migration
{
    protected $connection = 'mysql_trips';

    public function up()
    {
        if (Schema::connection('mysql_trips')->hasTable('operator_boat_overrides')) {
            return;   // already there: safe to run twice
        }
        Schema::connection('mysql_trips')->create('operator_boat_overrides', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('operator_id')->index();
            $table->unsignedInteger('boat_id')->unique();
            $table->string('name', 100)->nullable();
            $table->string('type', 45)->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->unsignedInteger('tec_capacity')->nullable();
            $table->string('manufacturer', 45)->nullable();
            $table->float('length')->nullable();
            $table->float('beam')->nullable();
            $table->float('speed')->nullable();
            $table->string('power', 45)->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::connection('mysql_trips')->dropIfExists('operator_boat_overrides');
    }
};

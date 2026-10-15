<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Runs on production via cron/migrate (no php binary in Kudu) -
        // harmless to re-run if the table was ever created by hand.
        if (Schema::hasTable('sessions')) {
            return;
        }
        // MySQL 8.0.30+ with sql_generate_invisible_primary_key=ON (local
        // dev) adds its own invisible PK to the new table, and Laravel 9's
        // separate "add primary key (id)" statement then fails. Off for
        // this session only; older servers reject the variable, so ignore.
        try {
            DB::statement('SET SESSION sql_generate_invisible_primary_key = OFF');
        } catch (\Throwable $e) {
        }
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sessions');
    }
};

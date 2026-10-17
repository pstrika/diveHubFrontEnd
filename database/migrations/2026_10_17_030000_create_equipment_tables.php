<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dive gear (Pablo, 2026-10-17). equipment_items is the diver's gear
 * list; equipment_configs are named sets of it ("Sidemount CCR kit");
 * dive_equipment records exactly which items a dive used - applying a
 * configuration copies its items in (config_id kept for reference), so
 * editing the set later never rewrites past dives. A computer item whose
 * serial_number matches a dive's computer_serial counts as used on that
 * dive without a dive_equipment row.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('category', 20);
            $table->string('name', 120);
            $table->string('manufacturer', 120)->nullable();
            $table->string('model', 120)->nullable();
            $table->string('serial_number', 120)->nullable();
            $table->date('purchased_on')->nullable();
            $table->date('last_service_on')->nullable();
            $table->text('notes')->nullable();
            $table->string('photo')->nullable();
            $table->timestamp('retired_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('equipment_configs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('name', 120);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('equipment_config_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('equipment_config_id');
            $table->unsignedBigInteger('equipment_item_id')->index();
            $table->unique(['equipment_config_id', 'equipment_item_id'], 'eq_config_item_unique');
        });

        Schema::create('dive_equipment', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dive_id');
            $table->unsignedBigInteger('equipment_item_id')->index();
            $table->unsignedBigInteger('equipment_config_id')->nullable();
            $table->timestamps();
            $table->unique(['dive_id', 'equipment_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dive_equipment');
        Schema::dropIfExists('equipment_config_items');
        Schema::dropIfExists('equipment_configs');
        Schema::dropIfExists('equipment_items');
    }
};

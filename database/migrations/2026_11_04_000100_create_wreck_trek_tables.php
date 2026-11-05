<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Wreck Trek (spec 2026-11-04). A leg is measured once in one direction; its
 * reverse is derived at load time, never stored. Sites live on mysql_trips
 * and users on mysql, so these ids are plain columns, not foreign keys (same
 * as posts.related_sites). Route stops are ordered site slugs.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('wreck_trek_legs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('from_site_id');
            $table->unsignedBigInteger('to_site_id');
            $table->unsignedSmallInteger('heading_deg'); // degrees true, 0 to 359
            $table->unsignedInteger('distance_ft');
            $table->string('notes', 255)->nullable();
            // Null verified_on = worked out from GPS, not swum yet: the page says so.
            $table->string('verified_by', 100)->nullable();
            $table->date('verified_on')->nullable();
            $table->timestamps();
            $table->unique(['from_site_id', 'to_site_id']);
        });

        Schema::create('wreck_trek_routes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index(); // null = curated
            $table->string('name', 60);
            $table->json('stops');
            $table->boolean('is_public')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('wreck_trek_routes');
        Schema::dropIfExists('wreck_trek_legs');
    }
};

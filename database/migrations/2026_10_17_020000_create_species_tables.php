<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Marine life (Pablo, 2026-10-17). `species` holds the shared catalog
 * (user_id null, seeded with common South Florida species) and species a
 * diver adds themselves (user_id set, private to them). Ranks follow the
 * Wikipedia taxobox; division/subclass are often empty for animals.
 * Dives and dive photos are tagged through dive_species /
 * dive_photo_species. users.share_sightings: null = never asked,
 * false/true = the diver's answer to sharing sightings (species, site,
 * date - never the dive) with the community.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('species', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('common_name', 120);
            $table->json('other_names')->nullable();
            $table->string('category', 40)->index();
            $table->string('kingdom', 60)->nullable();
            $table->string('phylum', 60)->nullable();
            $table->string('class', 60)->nullable();
            $table->string('subclass', 60)->nullable();
            $table->string('division', 60)->nullable();
            $table->string('taxon_order', 60)->nullable();
            $table->string('family', 60)->nullable();
            $table->string('genus', 60)->nullable();
            $table->string('scientific_name', 120)->nullable()->index();
            $table->text('description')->nullable();
            $table->string('photo')->nullable();
            $table->timestamps();
        });

        Schema::create('dive_species', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dive_id');
            $table->unsignedBigInteger('species_id')->index();
            $table->unsignedSmallInteger('count')->nullable();
            $table->string('notes', 500)->nullable();
            $table->timestamps();
            $table->unique(['dive_id', 'species_id']);
        });

        Schema::create('dive_photo_species', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dive_photo_id');
            $table->unsignedBigInteger('species_id')->index();
            $table->timestamps();
            $table->unique(['dive_photo_id', 'species_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('share_sightings')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('share_sightings');
        });
        Schema::dropIfExists('dive_photo_species');
        Schema::dropIfExists('dive_species');
        Schema::dropIfExists('species');
    }
};

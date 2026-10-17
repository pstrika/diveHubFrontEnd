<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Photos on a logbook dive (Pablo, 2026-10-17): up to 20 per dive,
 * private to the diver (files in storage/app/dive-photos, served by
 * DivePhotoController after an owner check). "Share to the site page"
 * copies one into diver_photos (mysql_trips) for the usual admin review;
 * shared_diver_photo_id remembers that copy.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dive_photos', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('dive_id')->index();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('caption', 500)->nullable();
            $table->dateTime('taken_at')->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->unsignedBigInteger('shared_diver_photo_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dive_photos');
    }
};

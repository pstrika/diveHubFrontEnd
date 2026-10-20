<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Videos on a logbook dive (Pablo, 2026-10-20). The files live on Cloudflare
 * Stream, not here: a row holds Cloudflare's id (stream_uid) and what we
 * learn from it once it has processed the upload. "Share to the site page"
 * makes a site_videos row (mysql_trips) pointing at the same Cloudflare
 * video, which goes through the normal admin review.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('dive_videos', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('dive_id')->index();
            $table->string('stream_uid', 64)->unique();
            // uploading -> processing -> ready | failed
            $table->string('stream_status', 16)->default('uploading');
            $table->string('stream_error', 255)->nullable();
            $table->string('customer_host', 120)->nullable(); // customer-xxxx.cloudflarestream.com
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->string('original_name', 255)->nullable();
            $table->string('caption', 500)->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->unsignedBigInteger('shared_site_video_id')->nullable(); // site_videos.id (mysql_trips)
            $table->timestamp('checked_at')->nullable(); // last status poll of Cloudflare
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('dive_videos');
    }
};

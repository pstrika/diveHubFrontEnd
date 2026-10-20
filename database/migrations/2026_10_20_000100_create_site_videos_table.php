<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Divers' videos on a site page (Pablo, 2026-10-20), reviewed by an admin
 * like diver_photos. The video itself is on Cloudflare Stream: uploaded on
 * the site page (owns_stream = 1), or shared from a logbook dive (0 - the
 * dive video keeps the Cloudflare asset, this row only points at it).
 */
return new class extends Migration
{
    protected $connection = 'mysql_trips';

    public function up()
    {
        Schema::connection('mysql_trips')->create('site_videos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siteId')->index();
            $table->unsignedBigInteger('userId')->index();
            $table->string('stream_uid', 64)->unique();
            $table->boolean('owns_stream')->default(true);
            $table->string('stream_status', 16)->default('uploading'); // uploading | processing | ready | failed
            $table->string('stream_error', 255)->nullable();
            $table->string('customer_host', 120)->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->string('original_name', 255)->nullable();
            $table->string('status')->default('pending')->index(); // pending | approved | rejected
            $table->string('desc', 150)->nullable();
            $table->unsignedBigInteger('reviewedBy')->nullable();
            $table->timestamp('reviewedAt')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::connection('mysql_trips')->dropIfExists('site_videos');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Videos in a group chat (Pablo, 2026-10-20): the video twin of
 * group_message_photos. The file is on Cloudflare Stream (signed URLs, so
 * only the page - members only - can mint playback tokens). A row is
 * created when the upload starts and attached to a message when it's sent
 * (group_message_id null until then).
 */
return new class extends Migration
{
    protected $connection = 'mysql_trips';

    public function up()
    {
        Schema::connection('mysql_trips')->create('group_message_videos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id')->index();
            $table->unsignedBigInteger('group_message_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('stream_uid', 64)->unique();
            $table->string('stream_status', 16)->default('uploading'); // uploading | processing | ready | failed
            $table->string('stream_error', 255)->nullable();
            $table->string('customer_host', 120)->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->string('original_name', 255)->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::connection('mysql_trips')->dropIfExists('group_message_videos');
    }
};

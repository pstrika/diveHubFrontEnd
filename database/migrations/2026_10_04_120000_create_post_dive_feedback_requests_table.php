<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per "how was your dive?" send - see App\Console\Commands\
 * SendPostDiveFeedbackRequests. The token is the sole authorization for the
 * wizard at /dive-feedback/{token}: divers reach it from a WhatsApp/SMS/
 * email link, often not logged in on that device, so (like
 * NewsletterController::unsubscribe()'s signed link) the token itself is
 * treated as proof of identity rather than requiring a session. Unlike that
 * link, this one authorizes several writes over its lifetime, hence
 * expires_at (Pablo, 2026-10-04).
 */
return new class extends Migration
{
    protected $connection = 'mysql_trips';

    public function up()
    {
        Schema::connection('mysql_trips')->create('post_dive_feedback_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id')->unique();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('trip_id')->nullable();
            $table->unsignedBigInteger('site_id')->nullable();
            $table->unsignedBigInteger('operator_id')->nullable();
            $table->string('token', 64)->unique();
            $table->string('channel'); // whatsapp | sms | email
            $table->dateTime('sent_at');
            $table->dateTime('expires_at');
            $table->dateTime('photos_uploaded_at')->nullable();
            $table->boolean('photo_reminder_requested')->default(false);
            $table->dateTime('photo_reminder_sent_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::connection('mysql_trips')->dropIfExists('post_dive_feedback_requests');
    }
};

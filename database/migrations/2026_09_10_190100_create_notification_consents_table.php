<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit trail of communication consent, one row every time a diver turns a
 * channel on or off.
 *
 * Why this exists (Pablo, 2026-09-10): Twilio A2P 10DLC registration means we
 * can be asked to show where a given person agreed to be messaged. The switches
 * on the profile page record the current state; this table records the event,
 * with the exact wording that was on screen at the time, so an audit answer is
 * a query rather than a guess. Never updated or deleted, only appended.
 *
 * channel   email | sms | whatsapp
 * granted   1 when they opted in, 0 when they opted out
 * phone     the number as stored at that moment, for sms and whatsapp
 * text      the consent paragraph shown on screen, verbatim
 * source    which screen it came from (profile, welcome)
 *
 * Runs on the users database, next to the users table it refers to.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('notification_consents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userId')->index();
            $table->string('channel', 20)->index();
            $table->boolean('granted');
            $table->string('phone', 40)->nullable();
            $table->text('text')->nullable();
            $table->string('source', 30)->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('notification_consents');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per SMS/WhatsApp/email that ever crossed between Divers Hub and a
 * diver - the admin Message Management console (Pablo, 2026-09-14: "see all
 * communications... and being able to reply") reads this table, grouped by
 * contact, and every reply or new outbound message writes another row here.
 *
 * `contact` (the raw phone number or email address) is the join key, not
 * user_id alone - the "Chat with us" widget and an inbound text can both
 * arrive from a phone number that doesn't match any account (a guest, or a
 * typo'd number), and the conversation still needs somewhere to live.
 * `user_id` is filled in whenever that contact matches a real account, for
 * a name/avatar in the console and so the admin can jump to their profile.
 *
 * Runs on the users database (default 'mysql' connection) - this is about
 * accounts more than dive data.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('conversation_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->enum('channel', ['sms', 'whatsapp', 'email']);
            $table->enum('direction', ['inbound', 'outbound']);
            $table->string('contact', 190); // phone (E.164) or email address
            $table->text('body')->nullable();
            $table->string('subject')->nullable(); // email only
            $table->unsignedBigInteger('admin_id')->nullable(); // who sent it, for an outbound reply
            $table->string('external_id')->nullable(); // Twilio Message SID / Mailgun id
            $table->string('status', 20)->nullable(); // queued/sent/delivered/failed, when known
            $table->timestamp('read_at')->nullable(); // null = unread in the admin console
            $table->timestamp('created_at')->useCurrent();

            $table->index(['contact', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('conversation_messages');
    }
};

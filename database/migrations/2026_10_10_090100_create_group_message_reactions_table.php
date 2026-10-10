<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Emoji reactions on a group chat message (Pablo, 2026-10-10): thumbs
     * up/down, heart, thinking - see GroupMessageReaction::REACTIONS for
     * the fixed set of allowed `emoji` keys. One row per (message, user,
     * emoji) - a diver can react with more than one of the four, but never
     * twice with the same one (toggling it again removes the row).
     */
    public function up()
    {
        Schema::connection('mysql_trips')->create('group_message_reactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('group_message_id');
            $table->integer('user_id');
            $table->string('emoji', 20);
            $table->timestamps();

            $table->index('group_message_id');
            $table->unique(['group_message_id', 'user_id', 'emoji']);
        });
    }

    public function down()
    {
        Schema::connection('mysql_trips')->dropIfExists('group_message_reactions');
    }
};

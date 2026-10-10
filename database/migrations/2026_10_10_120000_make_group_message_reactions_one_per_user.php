<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One reaction per user per message, not per (message, user, emoji) -
     * same as WhatsApp (Pablo, 2026-10-10: "only one reaction per message
     * per user"). Switching to a different emoji now replaces the row
     * instead of adding a second one - see GroupMessageController@react.
     */
    public function up()
    {
        // Collapse any (message, user) pair that currently has more than
        // one reaction row - the new constraint can't allow it. Keep
        // whichever was given most recently, drop the rest.
        $duplicates = DB::connection('mysql_trips')->table('group_message_reactions')
            ->select('group_message_id', 'user_id')
            ->groupBy('group_message_id', 'user_id')
            ->havingRaw('count(*) > 1')
            ->get();

        foreach ($duplicates as $dup) {
            $keepId = DB::connection('mysql_trips')->table('group_message_reactions')
                ->where('group_message_id', $dup->group_message_id)
                ->where('user_id', $dup->user_id)
                ->orderByDesc('id')
                ->value('id');

            DB::connection('mysql_trips')->table('group_message_reactions')
                ->where('group_message_id', $dup->group_message_id)
                ->where('user_id', $dup->user_id)
                ->where('id', '!=', $keepId)
                ->delete();
        }

        Schema::connection('mysql_trips')->table('group_message_reactions', function (Blueprint $table) {
            $table->dropUnique(['group_message_id', 'user_id', 'emoji']);
            $table->unique(['group_message_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::connection('mysql_trips')->table('group_message_reactions', function (Blueprint $table) {
            $table->dropUnique(['group_message_id', 'user_id']);
            $table->unique(['group_message_id', 'user_id', 'emoji']);
        });
    }
};

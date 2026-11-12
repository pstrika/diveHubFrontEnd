<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Three things a notification could not say about itself (Zach, 2026-11-12):
 *
 *   kind        what it is: admin, mention, dives, chat, reminder, invite.
 *               Null on everything written before this and on plain system
 *               notices. The folder a notification shows in comes from this
 *               plus group_id; see App\Models\Message::scopeFolder().
 *   url         where tapping it goes. Until now the link was sent to the
 *               browser push only and never saved.
 *   expires_at  when it stops mattering. Nothing sets it yet. It is here so
 *               a dated notice (a shop offer, later) can drop out of the
 *               unread count by itself.
 *
 * Deliberately NOT here: a column for which shop or operator sent it. The
 * home shop spec makes a shop an operator location that does not exist as
 * a table yet, so a pointer to it now would be a guess.
 */
return new class extends Migration
{
    protected $connection = 'mysql_trips';

    public function up()
    {
        Schema::connection('mysql_trips')->table('messages', function (Blueprint $table) {
            $table->string('kind', 20)->nullable()->after('group_id');
            $table->string('url', 500)->nullable()->after('body');
            $table->dateTime('expires_at')->nullable()->after('url');
        });
    }

    public function down()
    {
        Schema::connection('mysql_trips')->table('messages', function (Blueprint $table) {
            $table->dropColumn(['kind', 'url', 'expires_at']);
        });
    }
};

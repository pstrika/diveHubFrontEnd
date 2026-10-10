<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A #site mention inserted via the group chat composer's dropdown
     * (Pablo, 2026-10-10). One row per mentioned site per message - the
     * composer knows exactly which site was picked, so the server only
     * verifies the id is real (see GroupMessageController@store), never
     * re-parses the body text the way @mention does.
     */
    public function up()
    {
        Schema::connection('mysql_trips')->create('group_message_site_mentions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('group_message_id');
            $table->integer('site_id');
            $table->timestamps();

            $table->index('group_message_id');
        });
    }

    public function down()
    {
        Schema::connection('mysql_trips')->dropIfExists('group_message_site_mentions');
    }
};

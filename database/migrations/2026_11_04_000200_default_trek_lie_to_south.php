<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Most Florida wrecks lie with the bow to the south, so every site starts at
 * 180 degrees true until its real rotation is entered.
 */
return new class extends Migration
{
    public function up()
    {
        $db = DB::connection('mysql_trips');
        $db->statement('UPDATE sites SET trek_lie_deg = 180 WHERE trek_lie_deg IS NULL');
        $db->statement('ALTER TABLE sites MODIFY trek_lie_deg SMALLINT UNSIGNED NOT NULL DEFAULT 180');
    }

    public function down()
    {
        DB::connection('mysql_trips')->statement('ALTER TABLE sites MODIFY trek_lie_deg SMALLINT UNSIGNED NULL DEFAULT NULL');
    }
};

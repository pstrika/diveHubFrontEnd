<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * An auto-added dive (GroupAutoAddRule) has no human creator, unlike every
 * dive added before now - created_by was NOT NULL (Pablo, 2026-09-22).
 * Confirmed safe: created_by isn't rendered anywhere in the current UI
 * (GroupDive::creator() exists but nothing calls it).
 *
 * Raw SQL rather than Blueprint::change() - this app doesn't have
 * doctrine/dbal installed (required for column-modification migrations),
 * and adding it just for this one column isn't worth the new dependency.
 */
return new class extends Migration
{
    protected $connection = 'mysql_trips';

    public function up()
    {
        DB::connection('mysql_trips')->statement('ALTER TABLE group_dives MODIFY created_by BIGINT UNSIGNED NULL');
    }

    public function down()
    {
        DB::connection('mysql_trips')->statement('ALTER TABLE group_dives MODIFY created_by BIGINT UNSIGNED NOT NULL');
    }
};

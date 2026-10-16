<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `dives.entered_deco`: whether any profile sample was a mandatory deco
 * stop, stored at write time (Dive::saving) instead of re-scanning the
 * profile JSON on every read. The logbook list classifies every dive
 * (Dive::classification) and with a full imported logbook (~900 dives)
 * loading every profile just for that cost 1.7 s and 90 MB per page view.
 * Backfilled here in chunks; null means "unknown, scan the profile".
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('dives', function (Blueprint $table) {
            $table->boolean('entered_deco')->nullable()->after('dive_mode');
        });

        DB::table('dives')->select(['id', 'profile'])->orderBy('id')->chunkById(100, function ($rows) {
            foreach ($rows as $row) {
                $entered = false;
                foreach (json_decode($row->profile ?? '[]', true) ?: [] as $s) {
                    if (($s['decoKind'] ?? null) === 'deco_stop') { $entered = true; break; }
                }
                DB::table('dives')->where('id', $row->id)->update(['entered_deco' => $entered]);
            }
        });
    }

    public function down()
    {
        Schema::table('dives', function (Blueprint $table) {
            $table->dropColumn('entered_deco');
        });
    }
};

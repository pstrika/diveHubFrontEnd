<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Bidirectional phone sync (Pablo, 2026-10-14) - server side of
 * DiversHub-Uploader/docs/SYNC-CONTRACT.md, proposed by the uploader
 * session and confirmed as-is (see reply in that thread for the one
 * naming clarification: this app's fields are `notes` + the `conditions`
 * JSON blob, not separate notes/buddy/rating columns - no `rating`
 * concept exists yet).
 *
 * Three changes, all from that contract's section 7:
 *
 * 1. `uuid` - client-generated identity that survives across devices
 *    (the integer `id` stays server-internal). Backfilled for the 47 real
 *    rows already synced from Pablo's Shearwater before this migration
 *    existed. Nullable at the DB level as a safety valve, but the
 *    application always populates it (DiveUploadController generates one
 *    for the rare case an old app build doesn't send one).
 *
 * 2. Unique key: `(user_id, fingerprint)` -> `(user_id, computer_serial,
 *    fingerprint)`. Reason (uploader session): libdivecomputer's
 *    fingerprint on Shearwater is just the dive's start timestamp, so two
 *    different physical computers can legitimately produce the same
 *    fingerprint bytes - the old key would have silently treated that as
 *    one dive. Safe to land now: all 47 existing rows share one
 *    computer_serial, so the new tuple is still unique for them.
 *
 * 3. `deleted_at` (soft deletes) - a web-side delete needs to propagate to
 *    phones instead of just vanishing, and a re-uploaded dive that was
 *    deleted on the web must not silently resurrect it. No delete button
 *    exists in the web UI yet (LogbookController has no destroy action) -
 *    this migration only adds the column the eventual feature needs,
 *    consistent with how `conditions`/`site_id`/`deco_plan_id` were added
 *    ahead of their own UI.
 *
 * Runs on the users database (default 'mysql' connection), same as
 * `dives` itself. The deploy workflow does not run migrations, so this is
 * run by hand after deploying.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('dives', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
            $table->softDeletes();
        });

        DB::table('dives')->whereNull('uuid')->orderBy('id')->each(function ($row) {
            DB::table('dives')->where('id', $row->id)->update(['uuid' => Str::uuid()->toString()]);
        });

        Schema::table('dives', function (Blueprint $table) {
            $table->unique('uuid');
            $table->dropUnique(['user_id', 'fingerprint']);
            $table->unique(['user_id', 'computer_serial', 'fingerprint']);
        });
    }

    public function down()
    {
        Schema::table('dives', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'computer_serial', 'fingerprint']);
            $table->unique(['user_id', 'fingerprint']);
            $table->dropUnique(['uuid']);
            $table->dropSoftDeletes();
            $table->dropColumn('uuid');
        });
    }
};

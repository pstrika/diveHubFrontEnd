<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Backfills a "created" dive_changes row for every dive that predates
 * that table (the uploader session, 2026-10-15: found via a fresh
 * simulator install that walking GET /api/dives/changes from since=0
 * only returned dives created after dive_changes existed - the sync
 * feed is a new phone's ONLY path to a user's existing history by
 * contract, so a dive with no change row is permanently invisible to
 * any install other than the one that originally uploaded it).
 *
 * Ordered by started_at (oldest first) rather than by id/upload order,
 * so a backfilled dive's `seq` in the feed roughly tracks its
 * chronological place in the diver's history - purely cosmetic (the
 * feed's actual contract only requires the cursor to be monotonic, not
 * meaningful), but there's no reason not to make it sensible when it's
 * free to do so.
 *
 * Idempotent - skips any dive that already has a 'created' row (there
 * shouldn't be any yet, since every dive synced after dive_changes
 * existed would already have one from DiveUploadController, but this
 * guards against ever re-running this migration safely). Only touches
 * non-deleted dives - a soft-deleted dive that predates dive_changes is
 * an edge case with no real instance in production today, not worth
 * guessing a correct backfill behavior for ahead of it actually
 * happening.
 *
 * Pure data fix, no schema change. Runs on the users database (default
 * 'mysql' connection), same as `dives`/`dive_changes`. The deploy
 * workflow does not run migrations, so this is run by hand after
 * deploying.
 */
return new class extends Migration
{
    public function up()
    {
        $alreadyLogged = DB::table('dive_changes')
            ->where('action', 'created')
            ->pluck('dive_uuid')
            ->flip();

        $dives = DB::table('dives')
            ->whereNull('deleted_at')
            ->orderBy('started_at')
            ->select('user_id', 'uuid')
            ->get();

        foreach ($dives as $dive) {
            if (isset($alreadyLogged[$dive->uuid])) {
                continue;
            }
            DB::table('dive_changes')->insert([
                'user_id' => $dive->user_id,
                'dive_uuid' => $dive->uuid,
                'action' => 'created',
                'created_at' => now(),
            ]);
        }
    }

    public function down()
    {
        // Backfilled rows are indistinguishable from real ones once
        // inserted (same shape, no marker column) - not reversible
        // without risking deleting legitimate sync history alongside
        // them, so this intentionally does nothing on rollback.
    }
};

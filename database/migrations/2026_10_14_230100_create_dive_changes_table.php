<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Append-only change feed for phone sync (SYNC-CONTRACT.md section 3).
 * One row per create/update/(soft)delete of a dive, written inside the
 * same transaction as the change itself (DiveUploadController /
 * LogbookController). `id` is the feed's monotonic cursor - deliberately
 * NOT a timestamp, which the contract calls out as the whole point:
 * timestamps have clock-skew and same-instant ordering problems a plain
 * autoincrement doesn't. A phone's `GET /api/dives/changes?since=<seq>`
 * is `WHERE user_id = ? AND id > ? ORDER BY id LIMIT 200` - filtered per
 * user but ordered by the single global sequence, so a user's own cursor
 * has gaps (other users' changes advance it too) and that's fine, it only
 * needs to be monotonic and comparable.
 *
 * Rows are never updated or deleted - it's a log, not current state.
 *
 * Runs on the users database (default 'mysql' connection), same as
 * `dives`. The deploy workflow does not run migrations, so this is run by
 * hand after deploying.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('dive_changes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->uuid('dive_uuid');
            $table->string('action', 10); // created|updated|deleted
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('dive_changes');
    }
};

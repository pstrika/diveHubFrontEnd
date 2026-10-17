<?php

use App\Models\Dive;
use App\Models\DiveChange;
use Illuminate\Database\Migrations\Migration;

/**
 * Catch-up for phones (uploader session, 2026-10-16): 12.14 added computed
 * fields to the app's dive JSON - same_dive (open "same dive, another
 * computer?" questions) and computers (logs attached to a dive) - without
 * logging 'updated', so phones that synced earlier never refetched those
 * dives. Every live dive that currently has either gets its updated_at
 * bumped and an 'updated' row in the change feed. Data only, idempotent
 * enough to re-run (an extra 'updated' just means one more refetch).
 */
return new class extends Migration
{
    public function up(): void
    {
        $ids = [];
        foreach (Dive::where('conditions', 'like', '%same_dive_suggestion%')->get(['id', 'conditions']) as $d) {
            $ids[] = $d->id;
            if ($t = (int) ($d->conditions['same_dive_suggestion']['dive_id'] ?? 0)) $ids[] = $t;
        }
        foreach (Dive::withoutGlobalScope('companions')->whereNotNull('merged_into_id')->whereNull('deleted_at')->pluck('merged_into_id') as $main) {
            $ids[] = (int) $main;
        }
        $ids = array_values(array_unique($ids));
        if (!$ids) return;
        foreach (Dive::whereIn('id', $ids)->get(['id', 'user_id', 'uuid']) as $d) {
            Dive::withoutGlobalScope('companions')->whereKey($d->id)->update(['updated_at' => now()]);
            DiveChange::record($d->user_id, $d->uuid, 'updated');
        }
    }

    public function down(): void
    {
        // Nothing to undo: feed rows only make phones refetch.
    }
};

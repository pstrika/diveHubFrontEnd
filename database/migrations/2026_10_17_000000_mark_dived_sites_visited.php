<?php

use App\Models\Dive;
use Illuminate\Database\Migrations\Migration;

/**
 * Data only: every catalog site someone has a dive at becomes visited,
 * dated with their first dive there (Pablo, 2026-10-17). New dives do it
 * themselves from now on (Dive::booted saved hook). Nothing to undo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Dive::whereNotNull('site_id')->whereNull('merged_into_id')
            ->selectRaw('user_id, site_id, MIN(started_at) AS first_at')
            ->groupBy('user_id', 'site_id')
            ->get()
            ->each(fn ($r) => Dive::markSiteVisited((int) $r->user_id, (int) $r->site_id, $r->first_at));
    }

    public function down(): void
    {
    }
};

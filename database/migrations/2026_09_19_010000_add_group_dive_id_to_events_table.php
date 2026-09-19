<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Provenance for a personal calendar entry: which group dive, if any, put it
 * there. Before this column, the only link between an Event and a GroupDive
 * was a blind composite match on (date, time, operatorId, tripName), which
 * could not tell "auto-added because I RSVP'd" from "I saved this myself
 * from search" - so leaving a group dive could delete an unrelated manual
 * save, and removing a trip from My Calendar had no way to clear the group
 * RSVP at all (Pablo, 2026-09-19: "if I remove it directly from my
 * calendar, the group should not show going").
 *
 * No foreign key: `events` is a legacy table created outside Laravel's
 * migrations (there is no create_events migration in this repo), so its
 * engine/collation/column types aren't guaranteed to match `group_dives`.
 * Every other cross-table reference in this schema (group_dive_rsvps.
 * group_dive_id, messages.group_id) is a plain indexed column too -
 * deletion is handled explicitly in the controllers instead of via
 * cascade.
 *
 * Runs by hand after deploy - see docs/deployment.md.
 */
return new class extends Migration
{
    protected $connection = 'mysql_trips';

    public function up()
    {
        if (!Schema::connection('mysql_trips')->hasColumn('events', 'group_dive_id')) {
            Schema::connection('mysql_trips')->table('events', function (Blueprint $table) {
                $table->unsignedBigInteger('group_dive_id')->nullable()->after('tripName');
                $table->index('group_dive_id');
            });
        }

        // Backfill: link the events that RSVPs already created. Only when
        // the user is actually RSVP'd to that dive AND exactly one of
        // their events matches it - two matches means we cannot tell which
        // row the RSVP created, and stamping the wrong one would delete a
        // manual save on a later "Leaving". Those stay null and behave as
        // manual saves, which is the safe default.
        $rsvps = DB::connection('mysql_trips')->table('group_dive_rsvps')
            ->join('group_dives', 'group_dives.id', '=', 'group_dive_rsvps.group_dive_id')
            ->select(
                'group_dive_rsvps.user_id',
                'group_dives.id as dive_id',
                'group_dives.date',
                'group_dives.time',
                'group_dives.operatorId',
                'group_dives.tripName'
            )
            ->get();

        foreach ($rsvps as $r) {
            $ids = DB::connection('mysql_trips')->table('events')
                ->where('userId', $r->user_id)
                ->whereDate('date', $r->date)
                ->where('time', $r->time)
                ->where('operatorId', $r->operatorId)
                ->where('tripName', $r->tripName)
                ->whereNull('group_dive_id')
                ->pluck('id');

            if ($ids->count() === 1) {
                DB::connection('mysql_trips')->table('events')
                    ->where('id', $ids->first())
                    ->update(['group_dive_id' => $r->dive_id]);
            }
        }
    }

    public function down()
    {
        if (Schema::connection('mysql_trips')->hasColumn('events', 'group_dive_id')) {
            Schema::connection('mysql_trips')->table('events', function (Blueprint $table) {
                $table->dropIndex(['group_dive_id']);
                $table->dropColumn('group_dive_id');
            });
        }
    }
};

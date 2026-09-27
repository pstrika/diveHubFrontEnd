<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Cancellation state for a saved trip, as columns on `events` rather than a
 * new table - the state is strictly 1:1 with an event row, the calendar
 * reads it on every render, and putting it here means the "notified?"
 * bookkeeping is naturally per diver. A `trip_cancellations` table keyed by
 * (date, time, operatorId, tripName) was considered and rejected: that
 * composite is exactly the fragile key the previous migration moves away
 * from, and it would still need a per-event "notified" flag on top of it.
 *
 * Pablo, 2026-09-19: "if a dive was added to the group and that dive is
 * then no longer available... send a cancellation notification by email
 * and in app... only to users that has included that trip in his
 * calendar" - see App\Console\Commands\DetectCancelledTrips.
 *
 *   missing_since       first check on which the live trips row stopped
 *                       resolving; cleared the moment it resolves again
 *   missing_checks      consecutive misses - cancellation needs 2 of them,
 *                       an hour apart, so one bad scrape can't cancel
 *                       anything
 *   cancelled_at        set once confirmed. Terminal: the trip stays on
 *                       the calendar with a Cancelled badge until the
 *                       diver removes it themselves
 *   cancel_notified_at  when the email/in-app notice actually went out.
 *                       Null with cancelled_at set means we deliberately
 *                       stayed quiet (the diver already got an SMS/
 *                       WhatsApp reminder for this dive, or has email
 *                       notifications off)
 *
 * Runs by hand after deploy - see docs/deployment.md.
 */
return new class extends Migration
{
    protected $connection = 'mysql_trips';

    private const COLUMNS = [
        'missing_since' => 'group_dive_id',
        'missing_checks' => 'missing_since',
        'cancelled_at' => 'missing_checks',
        'cancel_notified_at' => 'cancelled_at',
    ];

    public function up()
    {
        Schema::connection('mysql_trips')->table('events', function (Blueprint $table) {
            foreach (self::COLUMNS as $column => $after) {
                if (Schema::connection('mysql_trips')->hasColumn('events', $column)) {
                    continue;
                }
                if ($column === 'missing_checks') {
                    $table->unsignedSmallInteger($column)->default(0)->after($after);
                } else {
                    $table->dateTime($column)->nullable()->after($after);
                }
            }
        });

        if (!self::hasIndex('events_cancelled_at_index')) {
            Schema::connection('mysql_trips')->table('events', function (Blueprint $table) {
                $table->index('cancelled_at');
            });
        }
    }

    public function down()
    {
        Schema::connection('mysql_trips')->table('events', function (Blueprint $table) {
            if (self::hasIndex('events_cancelled_at_index')) {
                $table->dropIndex(['cancelled_at']);
            }
            foreach (array_keys(self::COLUMNS) as $column) {
                if (Schema::connection('mysql_trips')->hasColumn('events', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private static function hasIndex(string $name): bool
    {
        return DB::connection('mysql_trips')
            ->select('SHOW INDEX FROM events WHERE Key_name = ?', [$name]) !== [];
    }
};

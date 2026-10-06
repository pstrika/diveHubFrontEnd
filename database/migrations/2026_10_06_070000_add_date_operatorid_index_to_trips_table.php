<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `trips` (44.6k+ rows, legacy scraped table, no create-migration in this
 * repo) had no index beyond its primary key - every query filtering by
 * date/operatorId (TripsController's board, MyDashboardController's event/
 * wishlist lookups, Trip::tripInEvent()'s composite-key resolution used
 * all over this app) was a full table scan. Confirmed the real-world
 * impact 2026-10-06: a few of these back-to-back scans, on top of normal
 * traffic, saturated the single-vCPU App Service instance and made the
 * whole site briefly unresponsive during a routine performance
 * investigation (Pablo: "right now the site is unresponsive").
 *
 * (date, operatorId) covers the two heaviest patterns directly - a
 * date-range scan alone (leftmost prefix) and an exact date+operator
 * lookup (the full composite, as in slotCandidates()/tripInEvent()) -
 * without going as far as the siteId LIKE '%id,%' lookups in
 * SiteController, which can never use an index regardless of what's
 * added here and need a real schema change (a normalized pivot table) -
 * deliberately out of scope for this migration.
 */
return new class extends Migration
{
    protected $connection = 'mysql_trips';

    private const INDEX_NAME = 'trips_date_operatorId_index';

    public function up()
    {
        if (self::hasIndex(self::INDEX_NAME)) {
            return;
        }

        Schema::connection('mysql_trips')->table('trips', function (Blueprint $table) {
            $table->index(['date', 'operatorId'], self::INDEX_NAME);
        });
    }

    public function down()
    {
        if (!self::hasIndex(self::INDEX_NAME)) {
            return;
        }

        Schema::connection('mysql_trips')->table('trips', function (Blueprint $table) {
            $table->dropIndex(self::INDEX_NAME);
        });
    }

    private static function hasIndex(string $name): bool
    {
        return DB::connection('mysql_trips')
            ->select('SHOW INDEX FROM trips WHERE Key_name = ?', [$name]) !== [];
    }
};

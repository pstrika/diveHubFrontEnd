<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Normalized trip<->site pivot, replacing the slow `trips.siteId` LIKE
 * query in SiteController@show (a leading-wildcard LIKE against a
 * comma-separated string column can never use an index, confirmed during
 * the 2026-10-06 performance investigation - that pass only indexed
 * date/operatorId and explicitly left this one for a real schema fix).
 *
 * No foreign key, deliberately - same reasoning as
 * post_dive_feedback_requests.trip_id: `trips` is a legacy table scraped
 * by a process entirely outside this repo, re-scraped from scratch
 * periodically with NEW auto-increment ids each time (Trip.php's own
 * docblocks: "trip ids are not durable"). This table is rebuilt in full
 * by App\Console\Commands\SyncTripSitesPivot on the same cadence as the
 * other trip-dependent cron jobs, not incrementally maintained - a
 * foreign key would just mean a cascade of broken-constraint errors on
 * every rebuild for no real benefit, since nothing here needs to persist
 * past the next sync.
 */
return new class extends Migration
{
    protected $connection = 'mysql_trips';

    public function up()
    {
        Schema::connection('mysql_trips')->create('trip_sites', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trip_id');
            $table->unsignedBigInteger('site_id');
            $table->timestamps();

            $table->unique(['trip_id', 'site_id']);
            $table->index('site_id');
        });
    }

    public function down()
    {
        Schema::connection('mysql_trips')->dropIfExists('trip_sites');
    }
};

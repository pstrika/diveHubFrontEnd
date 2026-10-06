<?php

namespace App\Console\Commands;

use App\Models\Trip;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Rebuilds trip_sites from scratch every run, rather than diffing/
 * upserting incrementally - `trips` is scraped by a process entirely
 * outside this repo and its ids are NOT durable (re-scraped daily with
 * new auto-increment ids - see app/Models/Trip.php's own docblocks), so
 * there's no stable "what changed since last sync" to diff against. A
 * full rebuild is simple, always correct regardless of how the scraper
 * cycles ids, and fast enough at this table's size (~45k rows, a few
 * seconds) to run every few minutes (Pablo, 2026-10-06).
 */
class SyncTripSitesPivot extends Command
{
    protected $signature = 'trips:sync-site-pivot';

    protected $description = 'Rebuilds the trip_sites pivot table from the current trips.siteId comma-list column';

    public function handle()
    {
        $connection = DB::connection('mysql_trips');
        $pairs = 0;
        $trips = 0;

        $connection->transaction(function () use ($connection, &$pairs, &$trips) {
            // DELETE, not TRUNCATE: TRUNCATE auto-commits in MySQL/InnoDB,
            // which would briefly expose an empty table to concurrent
            // readers outside this transaction. DELETE respects it, so
            // SiteController's lookups during a rebuild just see the
            // pre-rebuild data via MVCC until this commits.
            $connection->table('trip_sites')->delete();

            // Only today-forward: the one consumer (SiteController@show)
            // only ever needs upcoming trips, and the unscoped version kept
            // every trip ever scraped - 9,714 all-time rows for this app's
            // single busiest site, which made the pivot lookup itself slow
            // again (found while timing this against the old query - a
            // whereIn() against that many ids, or even a join, erased most
            // of the win). Scoping here keeps trip_sites an order of
            // magnitude smaller and the sync itself faster too.
            Trip::select('id', 'siteId')
                ->whereNotNull('siteId')
                ->where('siteId', '!=', '')
                ->whereDate('date', '>=', now()->toDateString())
                ->chunkById(1000, function ($chunk) use ($connection, &$pairs, &$trips) {
                    $rows = [];
                    $now = now();
                    foreach ($chunk as $trip) {
                        $trips++;
                        // Keyed by "tripId:siteId" to dedupe a trip whose own
                        // siteId string repeats an id (e.g. "44,44") - every
                        // trip in this chunk has a distinct id, so this never
                        // collapses rows across different trips.
                        foreach (explode(',', $trip->siteId) as $rawId) {
                            $siteId = (int) trim($rawId);
                            if ($siteId <= 0) {
                                continue;
                            }
                            $rows["{$trip->id}:{$siteId}"] = [
                                'trip_id' => $trip->id,
                                'site_id' => $siteId,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        }
                    }
                    if (!empty($rows)) {
                        $connection->table('trip_sites')->insert(array_values($rows));
                        $pairs += count($rows);
                    }
                });
        });

        $this->info("Rebuilt trip_sites: {$pairs} trip/site pairs from {$trips} trips.");

        return self::SUCCESS;
    }
}

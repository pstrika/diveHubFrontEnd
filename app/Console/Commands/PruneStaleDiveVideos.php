<?php

namespace App\Console\Commands;

use App\Support\DiveVideoStore;
use Illuminate\Console\Command;

/**
 * Removes dive video uploads that never finished (the diver left the page
 * before the upload reached Cloudflare), so they stop showing "Uploading"
 * forever. The dive page does the same for its own dive on every load; this
 * sweeps every diver's. Only rows Cloudflare still reports as waiting for
 * their upload ("pendingupload", or gone) are removed - anything it has
 * received is left alone and updated.
 *
 * Reports only unless --force is passed:
 *
 *   php artisan videos:prune-stale             report
 *   php artisan videos:prune-stale --force     delete
 *   php artisan videos:prune-stale --minutes=120 --force
 */
class PruneStaleDiveVideos extends Command
{
    protected $signature = 'videos:prune-stale {--minutes=30 : Only uploads older than this} {--force : Delete them. Without it this only reports}';
    protected $description = 'Remove dive video uploads that never finished';

    public function handle(): int
    {
        $report = [];
        $n = DiveVideoStore::pruneStale(null, max(1, (int) $this->option('minutes')), !$this->option('force'), $report);
        foreach ($report as $r) {
            $this->line("video {$r['id']} (dive {$r['dive_id']}) {$r['name']} started {$r['created_at']}");
        }
        $this->info(($this->option('force') ? 'Removed ' : 'Would remove ') . $n . ' stuck upload(s).');
        return self::SUCCESS;
    }
}

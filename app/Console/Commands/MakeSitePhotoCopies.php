<?php

namespace App\Console\Commands;

use App\Support\SitePhoto;
use Illuminate\Console\Command;

/**
 * Backfill web sized copies for every photo in public/assets/img/sites.
 *
 * Photos uploaded through the admin before this release have no copies, so
 * their pages serve the camera original. Run this once on the server after
 * deploying (Kudu console, site/wwwroot):
 *
 *   php artisan photos:web-copies
 *
 * Idempotent: copies that already exist and are newer than the original are
 * skipped, so it is safe to run again at any time. New uploads make their own
 * copies (SiteController::upload), so this is only needed for the backlog.
 */
class MakeSitePhotoCopies extends Command
{
    protected $signature = 'photos:web-copies {--force : Rebuild copies even when they exist}';
    protected $description = 'Create WebP web and thumbnail copies of dive site photos that do not have them yet';

    public function handle(): int
    {
        if (!SitePhoto::canMakeCopies()) {
            $this->error('PHP GD with WebP support is required (check gd_info()). Nothing done.');
            return self::FAILURE;
        }

        $dir = public_path('assets/' . SitePhoto::DIR);
        $files = array_filter(scandir($dir) ?: [], function ($f) use ($dir) {
            return is_file($dir . '/' . $f) && in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), SitePhoto::SOURCE_EXTENSIONS, true);
        });

        $made = $skipped = $failed = 0;
        $bar = $this->output->createProgressBar(count($files));
        foreach ($files as $file) {
            $result = SitePhoto::makeCopies($file, (bool) $this->option('force'));
            if ($result === true) {
                $made++;
            } elseif ($result === null) {
                $skipped++;
            } else {
                $failed++;
            }
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();
        $this->info("Done: $made written, $skipped already up to date, $failed failed (see laravel.log).");

        return self::SUCCESS;
    }
}

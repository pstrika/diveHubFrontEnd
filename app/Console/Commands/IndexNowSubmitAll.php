<?php

namespace App\Console\Commands;

use App\Http\Controllers\SitemapController;
use App\Services\IndexNowService;
use Illuminate\Console\Command;

/**
 * Pushes every real, indexable URL to IndexNow in one batch - the same
 * list SitemapController::allUrls() builds. Meant to run once after a
 * site-wide metadata pass like the 2026-09-28 SEO audit fixes (titles,
 * descriptions, alt text changed on hundreds of pages with no per-page
 * "publish" event to hang an automatic submission off of), and can be
 * re-run any time the same way. Routine one-off content changes (a new
 * blog post, say) should submit just that URL instead - see
 * BlogAdminController.
 */
class IndexNowSubmitAll extends Command
{
    protected $signature = 'indexnow:submit-all {--dry-run : Print the URLs and payload without calling IndexNow}';

    protected $description = 'Submit every real URL on the site to IndexNow (Bing, Yandex, Seznam, Naver)';

    public function handle()
    {
        $urls = app(SitemapController::class)->allUrls();

        $this->info(count($urls) . ' URLs collected.');

        if ($this->option('dry-run')) {
            $this->line('--dry-run: nothing sent. First 10 URLs:');
            foreach (array_slice($urls, 0, 10) as $url) {
                $this->line('  ' . $url);
            }
            $this->line('Key: ' . (config('services.indexnow.key') ? 'configured' : 'MISSING - set INDEXNOW_KEY'));
            return self::SUCCESS;
        }

        $ok = IndexNowService::submit($urls);

        if ($ok) {
            $this->info('Submitted successfully.');
            return self::SUCCESS;
        }

        $this->error('Submission failed - check the log for details.');
        return self::FAILURE;
    }
}

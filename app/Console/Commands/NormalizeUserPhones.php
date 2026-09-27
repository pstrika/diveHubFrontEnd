<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\PhoneNumber;
use Illuminate\Console\Command;

/**
 * One-off data cleanup (Pablo, 2026-09-14): "parse all the user DB and
 * make sure all the numbers are in the format we expect to be stored."
 * The profile page's phone rewrite (PhoneNumber::toE164/display) only
 * normalizes legacy values on the way OUT for display - it never touched
 * what's actually sitting in the `phone` column, so most rows written
 * before that change are still in whatever shape the diver originally
 * typed (bare digits, dashes, parens, a stray leading "1", etc).
 *
 * Only ever narrows punctuation/shape into the same underlying digits via
 * PhoneNumber::toE164() - never invents or drops a number. A phone that
 * doesn't parse at all is left untouched and reported, not blanked out.
 */
class NormalizeUserPhones extends Command
{
    protected $signature = 'users:normalize-phones {--dry-run : Report counts without writing anything}';

    protected $description = 'Rewrites every user.phone into E.164, leaving anything that cannot be parsed untouched';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        $users = User::whereNotNull('phone')->where('phone', '!=', '')->get(['id', 'phone']);

        $unchanged = 0;
        $fixed = 0;
        $unparseable = [];

        foreach ($users as $user) {
            $e164 = PhoneNumber::toE164($user->phone);

            if ($e164 === null) {
                $unparseable[] = $user;
                continue;
            }

            if ($e164 === $user->phone) {
                $unchanged++;
                continue;
            }

            $this->line("#{$user->id}: {$user->phone} -> {$e164}");
            $fixed++;

            if (!$dryRun) {
                $user->forceFill(['phone' => $e164])->save();
            }
        }

        foreach ($unparseable as $user) {
            $this->warn("#{$user->id}: could not parse '{$user->phone}' - left as-is");
        }

        $this->info(($dryRun ? '[dry run] ' : '') . "Checked {$users->count()}: {$unchanged} already correct, {$fixed} " . ($dryRun ? 'would be fixed' : 'fixed') . ', ' . count($unparseable) . ' unparseable.');

        return self::SUCCESS;
    }
}

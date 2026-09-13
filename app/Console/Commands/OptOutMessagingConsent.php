<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * One-off consent reset (Pablo, 2026-09-14): "mark all the users with the
 * exception of pstrika@gmail.com, Zack Patterson and John Entwistle to
 * opt-out in both SMS and WhatsApp - even the ones that had put their
 * phone numbers. Once we go live with this version, we will put a modal
 * to ask them to opt-in again."
 *
 * Existing sms_notifications/whatsapp_notifications rows are what the
 * old profile page's checkboxes wrote, from before the new phone
 * verification flow and admin console existed - not real informed
 * consent for a Twilio-backed SMS/WhatsApp program. This flips them off
 * for everyone except the three names Pablo called out, ahead of a
 * future opt-in modal that will collect real consent going forward. Only
 * touches the two notification flags - phone numbers themselves and
 * verification state are left alone.
 */
class OptOutMessagingConsent extends Command
{
    protected $signature = 'users:opt-out-messaging {--dry-run : Report the count without writing anything}';

    protected $description = 'Sets sms_notifications and whatsapp_notifications to false for every user except a fixed allowlist';

    /** Pablo's own account, plus the two named exceptions - both existing accounts, since two share the "John Entwistle" name. */
    private const KEEP_OPTED_IN_EMAILS = [
        'pstrika@gmail.com',
        'zach.patterson0591@gmail.com',
        'jeinfll@me.com',
        'jeinfll@gmail.com',
    ];

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        $kept = User::whereIn('email', self::KEEP_OPTED_IN_EMAILS)->get(['id', 'name', 'email']);
        $this->line('Keeping opted in: ' . $kept->map(fn ($u) => "#{$u->id} {$u->name} <{$u->email}>")->implode(', '));

        $query = User::whereNotIn('id', $kept->pluck('id'))
            ->where(function ($q) {
                $q->where('sms_notifications', true)->orWhere('whatsapp_notifications', true);
            });

        $count = $query->count();

        if (!$dryRun) {
            $query->update(['sms_notifications' => false, 'whatsapp_notifications' => false]);
        }

        $this->info(($dryRun ? '[dry run] ' : '') . "{$count} users " . ($dryRun ? 'would be' : 'were') . ' opted out of SMS and WhatsApp.');

        return self::SUCCESS;
    }
}

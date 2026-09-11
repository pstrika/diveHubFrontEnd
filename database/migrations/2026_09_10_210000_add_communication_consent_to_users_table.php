<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Communication consent, on the users table with the rest of the config.
 *
 * The approved migration set for release 10 (Zach and Pablo, 2026-09-10). Four
 * columns on users, no new table: Pablo's call, because every other preference
 * a diver has already lives here.
 *
 *   whatsapp_notifications   third channel alongside the existing
 *                            email_notifications and sms_notifications
 *   email_consent_at         when they last opted in to that channel, null
 *   sms_consent_at           when they have not. Twilio's A2P 10DLC
 *   whatsapp_consent_at      registration can ask us to show when and where a
 *                            given person agreed to be messaged; the timestamp
 *                            answers when, the wording is the consent screen
 *                            (resources/views/components/comms-preferences.blade.php),
 *                            and git history gives the exact wording that was on
 *                            screen at that timestamp.
 *
 * Opting out clears the timestamp, so a set timestamp always means "consent is
 * current, given on this date".
 *
 * Runs on the users database (the default 'mysql' connection), not the dive
 * schema, which the crawlers own. The deploy workflow does not run migrations,
 * so this is run by hand after deploying (see docs/deployment.md).
 */
return new class extends Migration
{
    /** column => the column it goes after */
    private const COLUMNS = [
        'whatsapp_notifications' => 'sms_notifications',
        'email_consent_at'       => 'whatsapp_notifications',
        'sms_consent_at'         => 'email_consent_at',
        'whatsapp_consent_at'    => 'sms_consent_at',
    ];

    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (self::COLUMNS as $column => $after) {
                if (Schema::hasColumn('users', $column)) {
                    continue;
                }
                if ($column === 'whatsapp_notifications') {
                    $table->boolean($column)->default(0)->after($after);
                } else {
                    $table->dateTime($column)->nullable()->after($after);
                }
            }
        });

        // Anyone already opted in before this release consented at some earlier
        // point we did not record. Stamp them as of now rather than leaving a
        // gap that reads as "never consented".
        if (Schema::hasColumn('users', 'email_consent_at')) {
            \Illuminate\Support\Facades\DB::table('users')
                ->where('email_notifications', 1)->whereNull('email_consent_at')
                ->update(['email_consent_at' => now()]);
        }
        if (Schema::hasColumn('users', 'sms_consent_at')) {
            \Illuminate\Support\Facades\DB::table('users')
                ->where('sms_notifications', 1)->whereNull('sms_consent_at')
                ->update(['sms_consent_at' => now()]);
        }
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (array_keys(self::COLUMNS) as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

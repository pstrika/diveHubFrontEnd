<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phone verification (Pablo, 2026-09-14): a US number needs a one-time SMS
 * code before it's trusted, at signup and on every later change - reachable
 * international numbers (WhatsApp only, no SMS) are accepted without one,
 * since we can't text them a code in the first place.
 *
 * `phone` keeps carrying whatever's already verified (or, for an
 * international number, already saved - there's nothing to verify there).
 * A change in progress lives entirely in the pending_* columns until the
 * code matches, so a diver mid-change never loses a working number.
 *
 * Runs on the users database (default 'mysql' connection). The deploy
 * workflow does not run migrations, so this is run by hand after deploying.
 */
return new class extends Migration
{
    private const COLUMNS = [
        'phone_verified_at'            => 'phone',
        'pending_phone'                => 'phone_verified_at',
        'phone_verification_code_hash' => 'pending_phone',
        'phone_verification_sent_at'   => 'phone_verification_code_hash',
    ];

    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (self::COLUMNS as $column => $after) {
                if (Schema::hasColumn('users', $column)) {
                    continue;
                }
                if (str_ends_with($column, '_at')) {
                    $table->dateTime($column)->nullable()->after($after);
                } elseif ($column === 'phone_verification_code_hash') {
                    $table->string($column, 100)->nullable()->after($after); // bcrypt hashes are 60 chars
                } else {
                    $table->string($column, 32)->nullable()->after($after);
                }
            }
        });
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

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `wizard_completed_at` replaces the welcome wizard's old "missing profile
 * data" heuristic for whether to prompt a diver - that heuristic meant an
 * already-registered member (real data, or the old fake registration
 * defaults) could never be prompted at all (Pablo, 2026-09-16: "I want
 * already registered users to run the wizard when the first login into the
 * new version"). Left null for every existing user on purpose - a plain
 * nullable column with no backfill means every current member is, correctly,
 * "hasn't run it yet" the moment this ships, with zero extra data work.
 *
 * `google_avatar_url` is the profile photo URL Socialite returns on Google
 * sign-in - not persisted before now. Lets the wizard's photo step offer
 * "use your Google photo" instead of only "upload a new one" for a diver who
 * signed up with Google (Pablo, 2026-09-16).
 *
 * Runs on the users database (default 'mysql' connection). The deploy
 * workflow does not run migrations, so this is run by hand after deploying.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'wizard_completed_at')) {
                $table->timestamp('wizard_completed_at')->nullable()->after('pinch_zoom_enabled');
            }
            if (!Schema::hasColumn('users', 'google_avatar_url')) {
                $table->string('google_avatar_url')->nullable()->after('google_id');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['wizard_completed_at', 'google_avatar_url'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

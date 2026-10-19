<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * How an account was created - null (Divers Hub email/password), 'google'
 * or 'apple' - for User Management's origin badge (Pablo, 2026-10-19).
 * google_id/apple_id can't answer that: signing in with Google or Apple
 * later links them to an existing account too. Accounts with a Google link
 * were already shown as Google sign-ups, so they're backfilled that way.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('signup_method', 10)->nullable()->after('apple_id');
        });
        DB::table('users')->whereNotNull('google_id')->update(['signup_method' => 'google']);
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('signup_method');
        });
    }
};

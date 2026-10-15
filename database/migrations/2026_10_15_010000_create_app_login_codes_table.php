<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * App sign-in (docs/APP-AUTH.md in DiversHub-Uploader, proposed by the
 * uploader session 2026-10-15, confirmed here): replaces "create a token
 * on /upload-tokens and paste it" with a real sign-in through the
 * website, working for password and Google accounts alike since the
 * website itself does the login.
 *
 * One row per one-time code: GET /app/login (web, `auth` middleware)
 * creates a row and redirects to divershub://auth?code=...&state=...;
 * POST /api/app/token (no auth - the code IS the credential) looks the
 * code up, checks it's unused and under 60s old, marks it used, and
 * mints the app's Sanctum token. `code` is 40 random url-safe
 * characters - Str::random()'s default alphabet, effectively
 * unguessable, so `used_at`/expiry (not secrecy) are what make reuse or
 * a stale code fail.
 *
 * Runs on the users database (default 'mysql' connection), same as
 * `dives`/`dive_changes`. The deploy workflow does not run migrations,
 * so this is run by hand after deploying.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('app_login_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->unsignedBigInteger('user_id');
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('app_login_codes');
    }
};

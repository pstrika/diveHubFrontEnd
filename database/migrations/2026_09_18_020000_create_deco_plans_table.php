<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "Save Plan" on the Decompression Dive Planner (Pablo, 2026-09-18): saves
 * every input the diver entered (mode, depth, bottom time, rate, GFs,
 * setpoint, bottom gas, and every deco/bailout gas) as one JSON blob rather
 * than a column per field, so the plan can be regenerated later by
 * re-running the same inputs through the same client-side calculation -
 * there's no derived/computed data to keep in sync here, just what the
 * diver typed. A `label` lets a diver tell their saved plans apart later
 * (defaults to something readable if left blank). Guests never get a row
 * here - see App\Http\Middleware\EnsureNotGuest.
 *
 * Runs on the users database (default 'mysql' connection), same as
 * user_dive_gases. The deploy workflow does not run migrations, so this is
 * run by hand after deploying.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('deco_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('label')->nullable();
            $table->string('mode', 2); // "OC" or "CC"
            $table->json('inputs'); // the diveProfile object built client-side, verbatim
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('deco_plans');
    }
};

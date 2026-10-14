<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * rEvo CCR Build-up checklist (Pablo, 2026-10-15): one row per time he
 * builds his rebreather before a dive. Same shape as deco_plans - one JSON
 * `answers` blob keyed by the active template's field keys, rather than a
 * column per checklist field, since the paper template itself has already
 * changed once (v1.0 -> v1.5, different items/order) and will likely
 * change again; `template_version` says which config/ccr_checklist_templates.php
 * entry to render a given row against. No FK/columns-per-field to keep in
 * sync when the template evolves - the config is the single source of
 * truth for what a given version's fields mean.
 *
 * Each item's own answers inside the `answers` JSON carry a `checked_at`
 * timestamp alongside `checked: true` (Pablo, 2026-10-15: "it's important
 * to have a timestamp for the forms...that's how I pass to the next
 * row") - captured client-side the moment a row is ticked, not derived
 * from this row's own `created_at`/`updated_at` (which only mark when the
 * whole session was saved, at the very end of the build).
 *
 * `signature` (Pablo, 2026-10-15: "at last a final signature") is a
 * base64 PNG captured from an on-screen signature pad at submit time -
 * inlined in the row rather than a separate uploaded file, since a single
 * line-drawing signature is only a few KB and this avoids wiring up a
 * storage disk for what's otherwise a one-field, one-time-per-session
 * capture.
 *
 * Runs on the users database (default 'mysql' connection), same as
 * deco_plans. The deploy workflow does not run migrations, so this is run
 * by hand after deploying.
 *
 * `build_date` carries a time-of-day, not just a calendar date (Pablo,
 * 2026-10-15: "I want date and time registered") - a dateTime column, not
 * date, since a build's start time matters for the record same as each
 * item's own checked_at does.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('ccr_checklist_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('template_version', 10);
            $table->dateTime('build_date');
            $table->json('answers');
            $table->longText('signature')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'build_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('ccr_checklist_sessions');
    }
};

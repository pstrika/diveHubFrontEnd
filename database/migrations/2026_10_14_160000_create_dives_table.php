<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Diver logbook (Pablo, 2026-10-14): one row per real dive downloaded from
 * a dive computer by the native uploader apps (iOS first, Android later).
 * Registered users only - no guest rows, same posture as deco_plans.
 *
 * Same "structured summary columns + one JSON blob for the detailed,
 * always-read-as-a-whole payload" shape as deco_plans.inputs and
 * ccr_checklist_sessions.answers: `profile` holds the full per-sample
 * time series (depth/temperature/pressure/po2/events/live-deco-per-sample/
 * etc, verbatim from LibDC-Swift's DiveProfilePoint) because it's always
 * rendered as one depth-vs-time chart, never queried sample-by-sample -
 * normalizing it into a samples-per-row table would just be hundreds of
 * rows per dive with no query that benefits from it.
 *
 * `site_id` and `deco_plan_id` are deliberately nullable and unset by the
 * upload itself - tagging a dive's site and linking it to a saved deco
 * plan are later, web-UI-driven enhancements (Pablo: "we will enhance the
 * dive with site information and do analysis compare to deco planner
 * data"), not something the uploader app can know. `site_id` has no DB
 * foreign key because Site lives on the separate mysql_trips connection
 * (same reason Post::related_sites stores site_id as plain JSON instead
 * of a constrained column) - resolved via an Eloquent relationship at the
 * app level instead.
 *
 * `fingerprint` is libdivecomputer's own raw per-dive identifier (what it
 * uses internally to support "only download dives I don't have yet") -
 * stored base64 and uniqued per user so re-running a sync (re-plugging the
 * same computer, syncing again after new dives) never duplicates a dive
 * even if the app's own client-side dedup ever misses a case. Nullable
 * because a small number of older/unusual drivers don't report one - rows
 * with a null fingerprint simply fall outside the dedup guarantee, a
 * known and accepted gap rather than a blocker (MySQL unique indexes
 * allow unlimited NULLs).
 *
 * `source` distinguishes the upload path (ios-uploader today, likely
 * android-uploader and uddf-import later - DivePlanner.blade.php already
 * has a one-off UDDF-overlay feature that could eventually feed this same
 * table) without needing a separate table per origin.
 *
 * Runs on the users database (default 'mysql' connection), same as
 * deco_plans and ccr_checklist_sessions. The deploy workflow does not run
 * migrations, so this is run by hand after deploying.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('dives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');

            // Which computer this came from. Free-text, not a lookup table -
            // the uploader app already knows the human-readable model name
            // from DeviceConfiguration.supportedModels, no need to duplicate
            // that catalog server-side.
            $table->string('computer_family', 50); // e.g. "shearwaterPetrel"
            $table->string('computer_model', 100);  // e.g. "Shearwater Perdix 2"
            $table->string('computer_serial', 100)->nullable();

            $table->string('fingerprint', 512)->nullable(); // base64, libdivecomputer's own dedup key
            $table->unsignedInteger('dive_number')->nullable(); // the computer's own dive number, for display only

            $table->dateTime('started_at');
            $table->unsignedInteger('duration_seconds');
            $table->decimal('max_depth_m', 6, 2);
            $table->decimal('avg_depth_m', 6, 2)->nullable();

            $table->decimal('surface_temp_c', 5, 2)->nullable();
            $table->decimal('min_water_temp_c', 5, 2)->nullable();
            $table->decimal('max_water_temp_c', 5, 2)->nullable();

            // "freedive" | "gauge" | "open_circuit" | "closed_circuit" | "semi_closed_circuit"
            $table->string('dive_mode', 30)->nullable();

            $table->json('gas_mixes')->nullable();  // [{helium, oxygen, nitrogen, usage}, ...]
            $table->json('tanks')->nullable();       // [{volume, workingPressure, beginPressure, endPressure, gasMix, usage}, ...]
            $table->json('deco_model')->nullable();  // {type, conservatism, gfLow, gfHigh}
            $table->json('location')->nullable();    // {latitude, longitude, altitude} - only GPS-equipped computers report this

            // Dive-level fields DiveData carries that don't warrant their own
            // column - rarely populated (CCR setpoint/cns/ppo2Readings need
            // a rebreather, heartbeat needs a strap, bearing needs a
            // compass) and never individually queried, only ever displayed
            // as part of one dive's detail view. {salinity, atmospheric,
            // rbt, heartbeat, bearing, setpoint, cns, ppo2Readings}.
            // Deliberately excludes DiveData.vendorSamples (opaque
            // per-vendor bytes with no decoder on our side) - revisit if a
            // concrete use for a specific vendor's data shows up.
            $table->json('sensor_data')->nullable();

            // The full per-sample time series, verbatim from DiveProfilePoint:
            // [{time, depth, temperature, pressure, tankPressures, po2, pn2,
            //   phe, events, rawEvents, decoKind, ndl, decoStop, decoTime,
            //   tts, currentGas, cns, rbt, heartbeat, bearing, setpoint}, ...]
            $table->json('profile');

            $table->unsignedBigInteger('site_id')->nullable();      // mysql_trips.sites.id - no FK, cross-connection
            $table->unsignedBigInteger('deco_plan_id')->nullable(); // deco_plans.id - same connection, real FK below

            $table->text('notes')->nullable(); // diver's own notes, added later in the web UI

            $table->string('source', 30)->default('ios-uploader');

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('deco_plan_id')->references('id')->on('deco_plans')->onDelete('set null');

            $table->unique(['user_id', 'fingerprint']);
            $table->index(['user_id', 'started_at']);
            $table->index('site_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('dives');
    }
};

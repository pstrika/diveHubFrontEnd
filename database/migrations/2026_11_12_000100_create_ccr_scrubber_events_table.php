<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Time on the scrubbers, the entries a diver makes by hand (Zach,
 * 2026-11-11: "we need to track actual elapsed time as well as allowing
 * people to do this RMS calculation, because not everybody has an RMS").
 *
 * Most of the scrubber record is not in this table. It is read straight
 * from the saved checklists: the dive time on a post-dive check, and the
 * "Refilled #" on a build check. Those are permanent records already, so
 * copying them here would only give two places to disagree. This table
 * holds what has no checklist behind it:
 *   add     dive time added by hand (minutes)
 *   adjust  "the scrubbers have this much time on them" (minutes)
 *   rotate  one fresh canister, clock back to zero
 *   repack  both repacked, clock back to zero
 * App\Support\CcrScrubber folds both sources into one running total.
 *
 * The limits (top canister, reserve) live in ccr_units.settings, which
 * already exists. Additive, users database (default connection).
 */
return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('ccr_scrubber_events')) {
            Schema::create('ccr_scrubber_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('unit_id');
                $table->unsignedBigInteger('user_id');
                $table->string('type', 20);
                $table->integer('minutes')->nullable();
                $table->dateTime('occurred_at');
                $table->timestamps();

                $table->foreign('unit_id')->references('id')->on('ccr_units')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index(['unit_id', 'occurred_at']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('ccr_scrubber_events');
    }
};

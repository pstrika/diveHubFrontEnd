<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A diver's own steps in a CCR checklist (Zach, 2026-11-10: "letting users
 * add their own checklist items... for all rebreather types... in an
 * arbitrary position").
 *
 * ccr_custom_items - one row per step a diver wrote, for one rebreather and
 * one stage. Position is NOT a row number. It is two things:
 * - `after_key`: the key of the BUILT IN step this one follows (null = before
 *   everything). Anchoring to a named step rather than "row 7" means a
 *   diver's step stays next to the step it was written to follow when a
 *   template is revised and rows are renumbered.
 * - `position`: order among the diver's own steps that share an anchor.
 * A unit with no built in checklist has no built in steps to anchor to, so
 * every step there has after_key null and `position` alone is the order.
 *
 * ccr_checklist_sessions.custom_items - a copy of the diver's own steps as
 * they were when that checklist was saved. A saved checklist is a record of
 * what was done; it has to read the same next year even if the diver has
 * since reworded, moved or deleted a step. The template side of that is
 * already solved by template_version; this is the same guarantee for the
 * part of the checklist that is not in any template.
 *
 * Additive. Users database (default connection). Applied by the deploy
 * workflow's migrate step, after 2026_11_10_000000 (file name order).
 */
return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('ccr_custom_items')) {
            Schema::create('ccr_custom_items', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('unit_id');
                $table->string('stage', 20);
                $table->string('text', 500);
                $table->string('after_key', 60)->nullable();
                $table->integer('position')->default(0);
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('unit_id')->references('id')->on('ccr_units')->onDelete('cascade');
                $table->index(['unit_id', 'stage']);
            });
        }

        if (!Schema::hasColumn('ccr_checklist_sessions', 'custom_items')) {
            Schema::table('ccr_checklist_sessions', function (Blueprint $table) {
                $table->json('custom_items')->nullable()->after('answers');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('ccr_checklist_sessions', 'custom_items')) {
            Schema::table('ccr_checklist_sessions', function (Blueprint $table) {
                $table->dropColumn('custom_items');
            });
        }
        Schema::dropIfExists('ccr_custom_items');
    }
};

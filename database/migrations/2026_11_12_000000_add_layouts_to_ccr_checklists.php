<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A diver's own order and headings for a checklist (Zach, 2026-11-11: "as
 * long as everything gets done, I see no harm in allowing people to adjust
 * the order things are in", and headings "seem like they're arbitrary").
 *
 * ccr_layouts - one row per rebreather and stage, only once the diver has
 * changed something. `rows` is the whole checklist top to bottom as one
 * ordered list: a row is either a step (by key) or a heading (text the
 * diver can rename, move, add or remove). No row here means the standard
 * order. See App\Support\CcrChecklistBuilder, which also guarantees that
 * every built in step is on the checklist exactly once whatever this holds.
 *
 * ccr_checklist_sessions.layout - the copy of those rows a session was
 * saved with, so a saved checklist keeps the order and headings it had on
 * the day, the same guarantee custom_items gives for the diver's own steps.
 *
 * Additive, users database (default connection). Applied by the deploy
 * workflow's migrate step. Every step checks before it creates.
 */
return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('ccr_layouts')) {
            Schema::create('ccr_layouts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('unit_id');
                $table->string('stage', 20);
                // The template version the layout was last saved against.
                $table->string('template_version', 10)->nullable();
                $table->json('rows');
                $table->timestamps();

                $table->foreign('unit_id')->references('id')->on('ccr_units')->onDelete('cascade');
                $table->unique(['unit_id', 'stage'], 'ccr_layouts_unit_stage');
            });
        }

        if (!Schema::hasColumn('ccr_checklist_sessions', 'layout')) {
            Schema::table('ccr_checklist_sessions', function (Blueprint $table) {
                $table->json('layout')->nullable()->after('custom_items');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('ccr_checklist_sessions', 'layout')) {
            Schema::table('ccr_checklist_sessions', function (Blueprint $table) {
                $table->dropColumn('layout');
            });
        }
        Schema::dropIfExists('ccr_layouts');
    }
};

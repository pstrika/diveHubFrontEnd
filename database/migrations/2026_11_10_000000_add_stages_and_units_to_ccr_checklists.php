<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CCR checklists grow from Pablo's own rEvo build sheet into a tool other
 * rebreather divers can use (Zach, 2026-11-10, docs/ccr-checklists-scope.md).
 *
 * Everything here is additive. Pablo's existing rows stay valid without a
 * backfill: `stage` defaults to 'build', which is what every one of them is,
 * and every other new column is nullable.
 *
 * ccr_units - one row per rebreather a diver owns. `model` is a key into
 * config/ccr_checklist_templates.php 'units' (today only 'revo'), which says
 * which stages that unit has and which template each stage uses. A unit is
 * optional: a session saved by somebody who never set one up just has a null
 * unit_id, exactly like the historical rows.
 *
 * ccr_checklist_sessions gains:
 * - unit_id: which rebreather this was for.
 * - stage: 'build' | 'closed' | 'prejump' | 'postdive'. The table used to
 *   hold only build sessions; a day on the boat is a build, a pressurised
 *   check, and one pre-jump and post-dive per dive.
 * - disclaimer_version / disclaimer_accepted_at: the safety warning is shown
 *   before every checklist and cannot be dismissed without tapping "I
 *   understand". Storing which wording was accepted and when is the
 *   difference between "we showed it" and "this diver acknowledged it".
 * - client_uuid: generated in the browser when a checklist is opened. A
 *   phone on a boat retries a save when the signal comes back; with this the
 *   retry finds the row it already made instead of creating a second one.
 *
 * Runs on the users database (default 'mysql' connection), same as the
 * table it extends. The deploy workflow runs pending migrations itself
 * after the files land (main_divehub.yml, cron/migrate). Every step here
 * checks before it creates, so a second run is harmless.
 */
return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('ccr_units')) {
            Schema::create('ccr_units', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->unsignedBigInteger('user_id');
                $table->string('model', 40);
                $table->string('name', 120);
                $table->string('serial_number', 120)->nullable();
                // Optional link to the diver's own gear list (equipment_items),
                // so a rebreather can show up on a logged dive's equipment
                // without being entered twice. No FK: equipment rows soft delete.
                $table->unsignedBigInteger('equipment_item_id')->nullable();
                $table->json('settings')->nullable();
                $table->timestamp('retired_at')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index('user_id');
            });
        }

        Schema::table('ccr_checklist_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('ccr_checklist_sessions', 'unit_id')) {
                $table->unsignedBigInteger('unit_id')->nullable()->after('user_id');
                $table->foreign('unit_id')->references('id')->on('ccr_units')->onDelete('set null');
            }
            if (!Schema::hasColumn('ccr_checklist_sessions', 'stage')) {
                $table->string('stage', 20)->default('build')->after('template_version');
                $table->index(['user_id', 'stage', 'build_date'], 'ccr_sessions_user_stage_date');
            }
            if (!Schema::hasColumn('ccr_checklist_sessions', 'disclaimer_version')) {
                $table->string('disclaimer_version', 20)->nullable()->after('signature');
            }
            if (!Schema::hasColumn('ccr_checklist_sessions', 'disclaimer_accepted_at')) {
                $table->dateTime('disclaimer_accepted_at')->nullable()->after('disclaimer_version');
            }
            if (!Schema::hasColumn('ccr_checklist_sessions', 'client_uuid')) {
                $table->uuid('client_uuid')->nullable()->after('disclaimer_accepted_at');
                $table->unique(['user_id', 'client_uuid'], 'ccr_sessions_user_client_uuid');
            }
        });
    }

    public function down()
    {
        Schema::table('ccr_checklist_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('ccr_checklist_sessions', 'client_uuid')) {
                $table->dropUnique('ccr_sessions_user_client_uuid');
                $table->dropColumn('client_uuid');
            }
            foreach (['disclaimer_accepted_at', 'disclaimer_version'] as $col) {
                if (Schema::hasColumn('ccr_checklist_sessions', $col)) {
                    $table->dropColumn($col);
                }
            }
            if (Schema::hasColumn('ccr_checklist_sessions', 'stage')) {
                $table->dropIndex('ccr_sessions_user_stage_date');
                $table->dropColumn('stage');
            }
            if (Schema::hasColumn('ccr_checklist_sessions', 'unit_id')) {
                $table->dropForeign(['unit_id']);
                $table->dropColumn('unit_id');
            }
        });

        Schema::dropIfExists('ccr_units');
    }
};

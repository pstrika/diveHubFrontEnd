<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Profile > Preferences "Show month calendar on tablet" (Pablo, 2026-10-16:
 * "the iPad can really hold the month view... By default this is turn on").
 * On: tablets (768-991px) get the month view like desktop. Off: a 7 day week.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'calendar_month_on_tablet')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('calendar_month_on_tablet')->default(true);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'calendar_month_on_tablet')) {
            Schema::table('users', fn (Blueprint $table) => $table->dropColumn('calendar_month_on_tablet'));
        }
    }
};

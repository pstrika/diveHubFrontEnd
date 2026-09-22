<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Backs scheduled sends in the newsletter composer (Pablo, 2026-09-22).
 * A "scheduled" issue is just a draft with this set - no new status value,
 * since status only ever needs to distinguish "not sent yet" from "sent"
 * (see NewsletterIssue::isScheduled()). Cleared once actually sent - see
 * NewsletterService::sendIssueToAllSubscribers().
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('newsletter_issues', function (Blueprint $table) {
            $table->timestamp('scheduled_at')->nullable()->after('conditions');
            $table->index('scheduled_at');
        });
    }

    public function down()
    {
        Schema::table('newsletter_issues', function (Blueprint $table) {
            $table->dropIndex(['scheduled_at']);
            $table->dropColumn('scheduled_at');
        });
    }
};

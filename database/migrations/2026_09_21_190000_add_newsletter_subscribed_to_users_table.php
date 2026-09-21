<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Opt-out, not opt-in (Pablo, 2026-09-21) - this is a plain
            // marketing digest to existing account holders, not a
            // consent-gated channel like sms_notifications/
            // whatsapp_notifications, so everyone starts subscribed and can
            // one-click unsubscribe from the email itself.
            $table->boolean('newsletter_subscribed')->default(true)->after('whatsapp_notifications');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('newsletter_subscribed');
        });
    }
};

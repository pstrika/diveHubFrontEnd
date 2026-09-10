<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * WhatsApp joins email and SMS as a communication channel.
 *
 * users.email_notifications and users.sms_notifications already exist (Pablo,
 * main 9.23.0). This adds the third so the profile page and the welcome wizard
 * can offer one checkbox per channel, which is what Zach and Pablo agreed on
 * 2026-09-10. Default 0: nobody is opted in without asking.
 *
 * Runs on the users database (the default 'mysql' connection), not the dive
 * schema, which the crawlers own.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'whatsapp_notifications')) {
                $table->boolean('whatsapp_notifications')->default(0)->after('sms_notifications');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'whatsapp_notifications')) {
                $table->dropColumn('whatsapp_notifications');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * QR codes a shop puts on its register, boat or flyers (Zach, 2026-11-13).
 *
 *   referral_codes   the code behind /go/{code}, one per placement
 *   referral_scans   one row per scan. No raw IP is ever stored: the
 *                    visitor is a daily salted hash
 *   user_referrals   which code brought a diver in (one row per diver)
 *
 * Additive. Three new tables on mysql_trips, nothing existing is altered.
 */
return new class extends Migration
{
    protected $connection = 'mysql_trips';

    public function up()
    {
        $schema = Schema::connection('mysql_trips');

        if (!$schema->hasTable('referral_codes')) {
            $schema->create('referral_codes', function (Blueprint $table) {
                $table->id();
                $table->string('code', 12)->unique();
                $table->unsignedInteger('operator_id');
                $table->unsignedBigInteger('operator_location_id')->nullable();
                $table->string('placement', 40);            // register, boat, flyer, website, other
                $table->string('label', 80)->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->index('operator_id');
            });
        }

        if (!$schema->hasTable('referral_scans')) {
            $schema->create('referral_scans', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('referral_code_id');
                $table->dateTime('scanned_at');
                $table->char('visitor_hash', 64)->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->boolean('is_bot')->default(false);
                $table->string('user_agent_family', 40)->nullable();
                $table->index(['referral_code_id', 'scanned_at']);
            });
        }

        if (!$schema->hasTable('user_referrals')) {
            $schema->create('user_referrals', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->unique();
                $table->unsignedBigInteger('referral_code_id');
                $table->unsignedInteger('operator_id');
                $table->unsignedBigInteger('operator_location_id')->nullable();
                $table->dateTime('attributed_at');
                $table->string('attribution_source', 30);   // signup, existing_user_scan
                $table->index('operator_id');
            });
        }
    }

    public function down()
    {
        $schema = Schema::connection('mysql_trips');
        $schema->dropIfExists('user_referrals');
        $schema->dropIfExists('referral_scans');
        $schema->dropIfExists('referral_codes');
    }
};

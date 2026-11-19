<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Shop portal, second pass (Zach, 2026-11-19).
 *
 * 1. More of the operator page becomes the shop's to keep right: which
 *    fills they have, their marina addresses, their waiver link and their
 *    trip prices. Same rule as the rest of operator_profiles: a value here
 *    is shown in place of ours on operators, and NULL means "not set, show
 *    what Divers Hub lists". The fill columns are nullable on purpose so
 *    "the shop says no" (0) is different from "the shop has not said" (NULL).
 *
 * 2. operator_events: one row each time a diver does something that a shop
 *    will want counted later (opened their page, opened one of their
 *    trips, clicked their waiver, made them a favourite or home shop).
 *    Collected from now so that insights have history when they ship.
 *    Things already recorded elsewhere are NOT copied here: trips saved to
 *    a calendar and marked booked (events), QR scans (referral_scans),
 *    sign-ups from a scan (user_referrals), ratings (operatorratings).
 */
return new class extends Migration
{
    protected $connection = 'mysql_trips';

    public function up()
    {
        Schema::connection('mysql_trips')->table('operator_profiles', function (Blueprint $table) {
            $table->boolean('fill_air')->nullable()->after('description');
            $table->boolean('fill_nitrox')->nullable()->after('fill_air');
            $table->boolean('fill_trimix')->nullable()->after('fill_nitrox');
            $table->boolean('fill_o2')->nullable()->after('fill_trimix');
            $table->string('marina_address', 255)->nullable()->after('fill_o2');
            $table->string('marina_address_alt', 255)->nullable()->after('marina_address');
            $table->string('waiver_link', 255)->nullable()->after('marina_address_alt');
            // [{"type":"Recreational - 2 Tank","price":"95"}], the same shape as operators.tripPrice.
            $table->json('trip_prices')->nullable()->after('waiver_link');
        });

        Schema::connection('mysql_trips')->create('operator_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('operator_id');
            $table->string('type', 30);
            // Null for a visitor who is not signed in. Kept so repeat visits
            // by one diver can be told from many divers; never shown to a shop.
            $table->unsignedBigInteger('user_id')->nullable();
            // Signed-out visitors: a one-way hash that changes daily, so
            // "how many different people" can be counted without knowing who.
            $table->string('visitor_hash', 64)->nullable();
            // What the diver was looking at, when it was a trip. Trips are
            // rebuilt by the crawlers, so the trip is kept by what it is,
            // never by trips.id.
            $table->date('trip_date')->nullable();
            $table->string('trip_time', 10)->nullable();
            $table->string('trip_name', 200)->nullable();
            $table->unsignedInteger('site_id')->nullable();
            $table->json('meta')->nullable();
            $table->dateTime('occurred_at');
            $table->index(['operator_id', 'type', 'occurred_at']);
            $table->index(['operator_id', 'occurred_at']);
        });
    }

    public function down()
    {
        Schema::connection('mysql_trips')->dropIfExists('operator_events');
        Schema::connection('mysql_trips')->table('operator_profiles', function (Blueprint $table) {
            $table->dropColumn(['fill_air', 'fill_nitrox', 'fill_trimix', 'fill_o2', 'marina_address', 'marina_address_alt', 'waiver_link', 'trip_prices']);
        });
    }
};

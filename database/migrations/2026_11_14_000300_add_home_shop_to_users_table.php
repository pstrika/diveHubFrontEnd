<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A diver's home shop (Zach, 2026-11-13).
 *
 * Three nullable columns on users, default connection. The home shop is an
 * operator_locations id on the other database, so it is a plain indexed
 * integer, not a foreign key.
 *
 * OPEN (Pablo): the spec leaves "columns on users" against "its own
 * user_home_shops table" to Pablo. This is the users version. Additive
 * and nullable, so it can be moved later without losing anything.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'home_operator_location_id')) {
                $table->unsignedBigInteger('home_operator_location_id')->nullable()->index();
            }
            if (!Schema::hasColumn('users', 'home_shop_set_at')) {
                $table->dateTime('home_shop_set_at')->nullable();
            }
            if (!Schema::hasColumn('users', 'home_shop_source')) {
                $table->string('home_shop_source', 20)->nullable(); // referral, manual
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['home_shop_source', 'home_shop_set_at', 'home_operator_location_id'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

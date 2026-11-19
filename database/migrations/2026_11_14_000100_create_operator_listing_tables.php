<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * What a shop admin edits about their own listing (Zach, 2026-11-13).
 *
 * Everything a shop enters lives in these new tables. Nothing here writes
 * to operators or boats: nobody could tell from this repo whether the
 * crawlers overwrite those rows, so a shop's edits must not depend on it.
 *
 *   operator_profiles    one row per operator: the shop's own name, contact
 *                        details and description. The operator page prefers
 *                        a value here over the one on operators
 *   operator_locations   storefront, dock, fill station. A storefront can be
 *                        a diver's home shop
 *   operator_offerings   services and prices at one location, with who last
 *                        confirmed the price and when
 *
 * Additive. Three new tables on mysql_trips, nothing existing is altered.
 */
return new class extends Migration
{
    protected $connection = 'mysql_trips';

    public function up()
    {
        $schema = Schema::connection('mysql_trips');

        if (!$schema->hasTable('operator_profiles')) {
            $schema->create('operator_profiles', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('operator_id')->unique();
                $table->string('display_name', 120)->nullable();
                $table->string('phone', 40)->nullable();
                $table->string('email', 190)->nullable();
                $table->string('website', 255)->nullable();
                $table->text('description')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
            });
        }

        if (!$schema->hasTable('operator_locations')) {
            $schema->create('operator_locations', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('operator_id');
                $table->string('type', 20);                 // storefront, dock, fill_station, other
                $table->string('name', 120);
                $table->string('slug', 140);
                $table->string('address_line1', 160)->nullable();
                $table->string('address_line2', 160)->nullable();
                $table->string('city', 80)->nullable();
                $table->string('state', 40)->nullable();
                $table->string('postal_code', 20)->nullable();
                $table->string('country', 60)->nullable();
                $table->decimal('lat', 10, 7)->nullable();
                $table->decimal('lon', 10, 7)->nullable();
                $table->string('phone', 40)->nullable();
                $table->string('email', 190)->nullable();
                $table->string('website', 255)->nullable();
                $table->json('hours')->nullable();
                $table->text('description')->nullable();
                $table->string('photo_path', 255)->nullable();
                $table->boolean('is_home_eligible')->default(false);
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->unique(['operator_id', 'slug']);
            });
        }

        if (!$schema->hasTable('operator_offerings')) {
            $schema->create('operator_offerings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('operator_location_id');
                $table->string('category', 30);             // gas_fill, rental, class, guide_service, equipment_service, retail, other
                $table->string('name', 120);
                $table->text('description')->nullable();
                $table->string('gas_type', 20)->nullable(); // air, nitrox, trimix, oxygen, argon
                $table->json('attributes')->nullable();
                $table->unsignedInteger('price_cents')->nullable();
                $table->string('price_unit', 40)->nullable();
                $table->string('price_note', 160)->nullable();
                $table->char('currency', 3)->default('USD');
                $table->string('price_source', 20)->default('operator'); // divers_hub, operator
                $table->dateTime('price_confirmed_at')->nullable();
                $table->unsignedBigInteger('price_confirmed_by')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->index('operator_location_id');
                $table->index('gas_type');
            });
        }
    }

    public function down()
    {
        $schema = Schema::connection('mysql_trips');
        $schema->dropIfExists('operator_offerings');
        $schema->dropIfExists('operator_locations');
        $schema->dropIfExists('operator_profiles');
    }
};

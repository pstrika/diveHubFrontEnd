<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Logbook phase 2 (Pablo, 2026-10-16): delete, manual entry, custom user
 * sites, merge, print, UDDF import.
 *
 * - `user_sites`: a diver's own dive sites (name, GPS, type, min cert,
 *   water type, altitude, location, country) for places that aren't in
 *   the public catalog (mysql_trips.sites). Personal data, lives on the
 *   users connection next to `dives`.
 * - `dives.user_site_id`: a dive tagged with one of those instead of a
 *   catalog site (site_id). The two are exclusive - a dive has one site.
 * - `dives.merged_into_id`: set on the soft-deleted half of a merge
 *   (surface-break dives the computer split in two) so the tombstone
 *   explains itself and a re-upload of that fingerprint still lands in
 *   DiveUploadController's `deleted` bucket.
 * - `dive_imports` / `dive_import_items`: the UDDF wizard's staging area -
 *   a parsed file sits here (one normalized dive per item, already in the
 *   `dives` row shape) while the diver reviews duplicates and maps sites,
 *   then the run step turns items into real dives.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('user_sites', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('name', 150);
            $table->string('type', 20)->default('other'); // wreck, reef, cave, cavern, quarry, mine, lake, river, other
            $table->string('min_cert', 10)->nullable();    // OW, AOW, Ta, Tn, Th
            $table->string('water_type', 10)->nullable();  // fresh, salt
            $table->integer('altitude_m')->nullable();
            $table->decimal('latitude', 10, 6)->nullable();
            $table->decimal('longitude', 10, 6)->nullable();
            $table->string('location', 150)->nullable();   // town / area, free text
            $table->string('country', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'name']);
        });

        Schema::table('dives', function (Blueprint $table) {
            $table->unsignedBigInteger('user_site_id')->nullable()->after('site_id');
            $table->unsignedBigInteger('merged_into_id')->nullable()->after('deleted_at');
            $table->index('user_site_id');
        });
        // A dive logged by hand or imported without a computer has none.
        // Raw SQL: ->change() would need doctrine/dbal, which isn't installed.
        DB::statement('ALTER TABLE dives MODIFY computer_model VARCHAR(100) NULL');

        Schema::create('dive_imports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('filename', 255);
            $table->string('generator', 100)->nullable(); // "MacDive 2.16.3"
            $table->string('status', 20)->default('staged'); // staged, done, cancelled
            $table->json('summary')->nullable();   // counts, computers, date range
            $table->json('sites')->nullable();     // UDDF site id => {name, lat, lon, location, country, decision}
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('dive_import_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dive_import_id');
            $table->unsignedInteger('position');
            $table->dateTime('started_at');
            $table->unsignedInteger('duration_seconds');
            $table->decimal('max_depth_m', 6, 2);
            $table->string('computer_model', 100)->nullable();
            $table->string('computer_serial', 100)->nullable();
            $table->string('fingerprint', 512)->nullable();
            $table->string('site_key', 100)->nullable();       // UDDF site id
            $table->boolean('has_profile')->default(false);
            $table->string('duplicate_kind', 20)->nullable();  // fingerprint, time, file
            $table->unsignedBigInteger('duplicate_of_dive_id')->nullable();
            $table->unsignedBigInteger('duplicate_of_item_id')->nullable();
            $table->string('decision', 10)->default('import'); // import, skip
            $table->unsignedBigInteger('imported_dive_id')->nullable();
            $table->json('payload'); // normalized dive (dives row shape) + notes/buddies/tags
            $table->timestamps();

            $table->foreign('dive_import_id')->references('id')->on('dive_imports')->onDelete('cascade');
            $table->index(['dive_import_id', 'position']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('dive_import_items');
        Schema::dropIfExists('dive_imports');
        Schema::table('dives', function (Blueprint $table) {
            $table->dropIndex(['user_site_id']);
            $table->dropColumn(['user_site_id', 'merged_into_id']);
        });
        DB::statement("UPDATE dives SET computer_model = '' WHERE computer_model IS NULL");
        DB::statement('ALTER TABLE dives MODIFY computer_model VARCHAR(100) NOT NULL');
        Schema::dropIfExists('user_sites');
    }
};

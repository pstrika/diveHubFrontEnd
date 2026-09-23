<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Registered divers can upload their own pictures of a site (Pablo,
 * 2026-09-23/24). Every upload starts 'pending' - only 'approved' ones
 * show on the site page - see DiverPhotoController/DiverPhotoAdminController
 * and App\Models\DiverPhoto. Files live in the same public/assets/img/sites
 * directory admin-uploaded site photos already use (App\Support\SitePhoto),
 * just with a 'diver_' filename prefix, so the existing web/thumb WebP
 * copy generation works unchanged.
 */
return new class extends Migration
{
    protected $connection = 'mysql_trips';

    public function up()
    {
        Schema::connection('mysql_trips')->create('diver_photos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siteId')->index();
            $table->unsignedBigInteger('userId')->index();
            $table->string('file');
            $table->string('status')->default('pending')->index();
            $table->unsignedBigInteger('reviewedBy')->nullable();
            $table->timestamp('reviewedAt')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::connection('mysql_trips')->dropIfExists('diver_photos');
    }
};

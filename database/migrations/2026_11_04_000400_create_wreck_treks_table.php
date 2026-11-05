<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Predefined wreck treks, such as the Pompano Wreck Trek. A wreck belongs to
 * one trek. sites lives in another database, so there is no foreign key.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('wreck_treks', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 60)->unique();
            $table->string('name', 80);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::connection('mysql_trips')->table('sites', function (Blueprint $table) {
            $table->unsignedBigInteger('wreck_trek_id')->nullable()->index();
        });
    }

    public function down()
    {
        Schema::connection('mysql_trips')->table('sites', function (Blueprint $table) {
            $table->dropColumn('wreck_trek_id');
        });

        Schema::dropIfExists('wreck_treks');
    }
};

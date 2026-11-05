<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** An optional logo shown above the chart's scale, and where clicking it goes. */
return new class extends Migration
{
    public function up()
    {
        Schema::table('wreck_treks', function (Blueprint $table) {
            $table->string('logo', 255)->nullable();
            $table->string('logo_link', 255)->nullable();
        });
    }

    public function down()
    {
        Schema::table('wreck_treks', function (Blueprint $table) {
            $table->dropColumn(['logo', 'logo_link']);
        });
    }
};

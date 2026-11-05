<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Wreck pairs joined by rebar, and navigational aids (boulders, mitigation points, pipes) that belong to one trek. */
return new class extends Migration
{
    public function up()
    {
        Schema::table('wreck_treks', function (Blueprint $table) {
            $table->json('rebars')->nullable();
            $table->json('aids')->nullable();
        });
    }

    public function down()
    {
        Schema::table('wreck_treks', function (Blueprint $table) {
            $table->dropColumn(['rebars', 'aids']);
        });
    }
};

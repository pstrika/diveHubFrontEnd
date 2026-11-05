<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** A wreck trek's area: the same short location code a site uses (POM, MIA...). */
return new class extends Migration
{
    public function up()
    {
        Schema::table('wreck_treks', function (Blueprint $table) {
            $table->string('location', 10)->nullable();
        });

        DB::table('wreck_treks')->where('slug', 'pompano')->update(['location' => 'POM']);
        DB::table('wreck_treks')->where('slug', 'key-biscayne')->update(['location' => 'MIA']);
    }

    public function down()
    {
        Schema::table('wreck_treks', function (Blueprint $table) {
            $table->dropColumn('location');
        });
    }
};

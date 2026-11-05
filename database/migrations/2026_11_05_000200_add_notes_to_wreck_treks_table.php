<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('wreck_treks', function (Blueprint $table) {
            $table->text('notes')->nullable();
        });
    }

    public function down()
    {
        Schema::table('wreck_treks', function (Blueprint $table) {
            $table->dropColumn('notes');
        });
    }
};

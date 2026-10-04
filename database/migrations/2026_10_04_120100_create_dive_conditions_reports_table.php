<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The first, skippable step of the post-dive feedback wizard - see
 * App\Http\Controllers\DiveFeedbackController. One per event+user; the
 * wizard upserts rather than inserting twice if a diver reopens the link
 * (Pablo, 2026-10-04).
 */
return new class extends Migration
{
    protected $connection = 'mysql_trips';

    public function up()
    {
        Schema::connection('mysql_trips')->create('dive_conditions_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id')->unique();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('site_id')->nullable();
            $table->unsignedTinyInteger('visibility_ft')->nullable(); // 0-100
            $table->unsignedTinyInteger('waves_ft')->nullable(); // 0-6
            $table->string('current_strength', 10)->nullable(); // none|mild|moderate|strong
            $table->string('current_direction', 2)->nullable(); // N|S|E|W
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::connection('mysql_trips')->dropIfExists('dive_conditions_reports');
    }
};

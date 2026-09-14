<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A registered diver's saved custom gas mixes ("My Gases" pill on every gas
 * card in the Decompression Dive Planner), up to 10 per diver - enforced in
 * the controller, not here, same as every other "max N" rule in this app.
 * Guests never get one of these rows - see App\Http\Middleware\EnsureNotGuest.
 *
 * Runs on the users database (default 'mysql' connection, same as
 * push_subscriptions - another small per-user table). The deploy workflow
 * does not run migrations, so this is run by hand after deploying.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('user_dive_gases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedTinyInteger('o2');
            $table->unsignedTinyInteger('he');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_dive_gases');
    }
};

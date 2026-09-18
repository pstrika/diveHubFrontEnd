<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * object-fit: cover on a wide 16:9 hero crops the top and bottom of
 * whatever's uploaded, and a centered crop cuts off the subject whenever
 * it isn't dead center - the first real article's shark/diver sat low in
 * the frame and got trimmed out of the hero entirely (Pablo, 2026-09-18:
 * "the shark and diver are in the bottom part of the picture and they get
 * cut"). One of top/center/bottom, applied as object-position everywhere
 * the cover renders (hero, index cards, dashboard carousel).
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('cover_focus')->default('center')->after('cover_image');
        });
    }

    public function down()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('cover_focus');
        });
    }
};

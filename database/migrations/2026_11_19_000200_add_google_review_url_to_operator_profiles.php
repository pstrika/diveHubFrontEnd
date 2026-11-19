<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A shop's own "review us on Google" link (Zach, 2026-11-19).
 *
 * google_review_ask is the shop's switch for it, OFF until the shop turns
 * it on: a shop may already be asking its customers some other way, and
 * should not find out we started asking for them. With it on, the post-dive
 * follow-up offers the link to every diver who finishes, after they have
 * rated the shop on Divers Hub. Two rules live in the code that uses it,
 * not here: it is offered to everyone whatever they rated, and nothing is
 * ever given in return for a review.
 */
return new class extends Migration
{
    protected $connection = 'mysql_trips';

    public function up()
    {
        // Each column only if it is not there yet, so this is safe to run twice.
        $schema = Schema::connection('mysql_trips');
        if (!$schema->hasColumn('operator_profiles', 'google_review_url')) {
            $schema->table('operator_profiles', fn (Blueprint $table) => $table->string('google_review_url', 500)->nullable());
        }
        if (!$schema->hasColumn('operator_profiles', 'google_review_ask')) {
            $schema->table('operator_profiles', fn (Blueprint $table) => $table->boolean('google_review_ask')->default(false));
        }
    }

    public function down()
    {
        Schema::connection('mysql_trips')->table('operator_profiles', function (Blueprint $table) {
            $table->dropColumn(['google_review_url', 'google_review_ask']);
        });
    }
};

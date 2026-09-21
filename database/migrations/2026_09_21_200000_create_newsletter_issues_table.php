<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Backs the admin newsletter composer (Pablo, 2026-09-21: "an interface
 * to create newsletters content on demand...ad hoc newsletters are also
 * useful to communicate platform wide, not just a user specific digest").
 * Default connection (no override), matching Post - see Post's own
 * docblock note on why it lives there rather than mysql_trips.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('newsletter_issues', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->string('preheader')->nullable();
            $table->string('headline');
            $table->longText('body_markdown');
            $table->string('conditions')->nullable();
            $table->enum('status', ['draft', 'sent'])->default('draft');
            $table->unsignedBigInteger('created_by');
            $table->timestamp('sent_at')->nullable();
            $table->unsignedInteger('sent_count')->nullable();
            $table->timestamps();

            $table->index('created_by');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('newsletter_issues');
    }
};

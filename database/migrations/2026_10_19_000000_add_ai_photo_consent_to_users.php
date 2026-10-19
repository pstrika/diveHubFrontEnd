<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * When the diver agreed that "What's this?" may send their photo to our AI
 * provider (Anthropic) - asked once, before the first photo goes out (App
 * Store 5.1.2(i), 2026-10-19). Shared by the website and the app.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('ai_photo_consent_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('ai_photo_consent_at');
        });
    }
};

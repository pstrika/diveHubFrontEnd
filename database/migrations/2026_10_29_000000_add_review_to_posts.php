<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Contributors submit articles for review; an admin publishes them (Pablo,
 * 2026-10-29). Adds the 'pending' status and the note an admin can leave
 * when sending an article back to its author.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE posts MODIFY status ENUM('draft','pending','published') NOT NULL DEFAULT 'draft'");
        if (!Schema::hasColumn('posts', 'review_note')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->text('review_note')->nullable()->after('status');
            });
        }
    }

    public function down(): void
    {
        DB::statement("UPDATE posts SET status = 'draft' WHERE status = 'pending'");
        DB::statement("ALTER TABLE posts MODIFY status ENUM('draft','published') NOT NULL DEFAULT 'draft'");
        if (Schema::hasColumn('posts', 'review_note')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->dropColumn('review_note');
            });
        }
    }
};

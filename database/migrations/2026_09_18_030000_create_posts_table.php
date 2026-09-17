<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Blog posts (Pablo, 2026-09-17/18): replaces the mock arrays in
 * App\Support\BlogPosts once this ships. Runs on the users database
 * (default 'mysql' connection, same as user_dive_gases) since author_id
 * is a real foreign key to users - unlike related_site_ids below, which
 * points at Sites on the separate mysql_trips connection and so can't be a
 * real FK, the same cross-connection situation users.favOperators/
 * favLocations already live with.
 *
 * The deploy workflow does not run migrations, so this is run by hand
 * after deploying.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category');
            // Topical/audience labels, several per post, distinct from the
            // one category above (Pablo, 2026-09-17: "tags on the
            // articles...to show this articles to users by preference").
            $table->json('tags')->nullable();
            // App\Support\DiveLevel value - who the post is really written
            // for, if anyone in particular. Drives BlogPosts::forViewer()'s
            // ranking once this replaces the mock version.
            $table->unsignedTinyInteger('min_level')->nullable();
            $table->text('excerpt')->nullable();
            // Quill Delta JSON - same storage shape as sites.desc/route/
            // typicalConditions/history (see edit-site.blade.php), rendered
            // client-side the same way.
            $table->longText('body')->nullable();
            $table->string('cover_image')->nullable();
            // Comma-separated Site ids in rank order (mysql_trips
            // connection, so no real FK - see class docblock). Nullable:
            // "not in all cases we will have rankings, meaning the site
            // citations may not always be used" (Pablo, 2026-09-17) - a
            // gear or news post cites no sites at all.
            $table->string('related_site_ids')->nullable();
            $table->unsignedBigInteger('author_id');
            // Creators are trusted to publish directly, no pre-approval
            // queue (Pablo: "I'm expecting creators to create reasonable
            // content") - draft is just a Creator/Admin's own save-for-
            // later, not a moderation gate. Admins can remove any post
            // after the fact regardless of status - see PostPolicy.
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->foreign('author_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('posts');
    }
};

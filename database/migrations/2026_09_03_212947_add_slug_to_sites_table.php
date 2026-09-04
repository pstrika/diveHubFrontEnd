<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * This migration targets divehub-schema (mysql_trips), not the default
     * connection, so its own tracking record lives in that database too.
     */
    protected $connection = 'mysql_trips';

    public function up()
    {
        Schema::connection('mysql_trips')->table('sites', function (Blueprint $table) {
            $table->string('slug', 220)->nullable()->after('name');
        });

        $usedSlugs = [];

        DB::connection('mysql_trips')->table('sites')
            ->orderBy('id')
            ->select('id', 'name')
            ->chunkById(200, function ($sites) use (&$usedSlugs) {
                foreach ($sites as $site) {
                    $base = Str::slug($site->name ?: '');
                    if ($base === '') {
                        $base = 'site-' . $site->id;
                    }

                    $slug = $base;
                    $suffix = 2;
                    while (isset($usedSlugs[$slug])) {
                        $slug = $base . '-' . $suffix;
                        $suffix++;
                    }
                    $usedSlugs[$slug] = true;

                    DB::connection('mysql_trips')->table('sites')
                        ->where('id', $site->id)
                        ->update(['slug' => $slug]);
                }
            });

        Schema::connection('mysql_trips')->table('sites', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down()
    {
        Schema::connection('mysql_trips')->table('sites', function (Blueprint $table) {
            $table->dropUnique(['sites_slug_unique']);
            $table->dropColumn('slug');
        });
    }
};

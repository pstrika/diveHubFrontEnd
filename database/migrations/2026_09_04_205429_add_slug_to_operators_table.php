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
        Schema::connection('mysql_trips')->table('operators', function (Blueprint $table) {
            $table->string('slug', 220)->nullable()->after('operatorName');
        });

        $usedSlugs = [];

        DB::connection('mysql_trips')->table('operators')
            ->orderBy('id')
            ->select('id', 'operatorName')
            ->chunkById(200, function ($operators) use (&$usedSlugs) {
                foreach ($operators as $operator) {
                    $base = Str::slug($operator->operatorName ?: '');
                    if ($base === '') {
                        $base = 'operator-' . $operator->id;
                    }

                    $slug = $base;
                    $suffix = 2;
                    while (isset($usedSlugs[$slug])) {
                        $slug = $base . '-' . $suffix;
                        $suffix++;
                    }
                    $usedSlugs[$slug] = true;

                    DB::connection('mysql_trips')->table('operators')
                        ->where('id', $operator->id)
                        ->update(['slug' => $slug]);
                }
            });

        Schema::connection('mysql_trips')->table('operators', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down()
    {
        Schema::connection('mysql_trips')->table('operators', function (Blueprint $table) {
            $table->dropUnique(['operators_slug_unique']);
            $table->dropColumn('slug');
        });
    }
};

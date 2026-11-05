<?php

use App\Models\Site;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The GPS positions a measured leg was recorded against. If either wreck's
 * coordinates change later the leg no longer applies and is worked out from GPS.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('wreck_trek_legs', function (Blueprint $table) {
            $table->string('gps_basis', 120)->nullable();
        });

        // Existing legs were measured against the positions the wrecks have today.
        $sites = Site::whereIn('id', DB::table('wreck_trek_legs')->pluck('from_site_id')->merge(DB::table('wreck_trek_legs')->pluck('to_site_id'))->unique())
            ->get(['id', 'gpsLat', 'gpsLon'])->keyBy('id');
        foreach (DB::table('wreck_trek_legs')->get() as $leg) {
            $a = $sites[$leg->from_site_id] ?? null;
            $b = $sites[$leg->to_site_id] ?? null;
            if (!$a || !$b) continue;
            DB::table('wreck_trek_legs')->where('id', $leg->id)->update(['gps_basis' => $a->gpsLat . '|' . $a->gpsLon . '|' . $b->gpsLat . '|' . $b->gpsLon]);
        }
    }

    public function down()
    {
        Schema::table('wreck_trek_legs', function (Blueprint $table) {
            $table->dropColumn('gps_basis');
        });
    }
};

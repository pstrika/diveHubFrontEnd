<?php

use App\Support\AssetPath;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// operator_logo / boat_pic / operator_url inside dives.conditions were stored
// as absolute URLs of whichever host the picker ran against (localhost and a
// LAN address reached production via app testing). Store the path only;
// App\Support\AssetPath::absolute() re-homes it on output. Idempotent.
return new class extends Migration
{
    private const KEYS = ['operator_logo', 'boat_pic', 'operator_url'];

    public function up()
    {
        DB::table('dives')->where('conditions', 'like', '%http%')
            ->orderBy('id')
            ->each(function ($dive) {
                $conditions = json_decode($dive->conditions, true);
                if (!is_array($conditions)) return;
                $changed = false;
                foreach (self::KEYS as $key) {
                    $value = $conditions[$key] ?? null;
                    if (is_string($value) && $value !== '' && AssetPath::relative($value) !== $value) {
                        $conditions[$key] = AssetPath::relative($value);
                        $changed = true;
                    }
                }
                if ($changed) {
                    DB::table('dives')->where('id', $dive->id)->update(['conditions' => json_encode($conditions)]);
                }
            });
    }

    public function down()
    {
        // Paths stay valid; nothing to undo.
    }
};

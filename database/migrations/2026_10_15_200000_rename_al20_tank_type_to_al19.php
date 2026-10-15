<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// The catalog key AL-20 became AL-19 on 2026-10-15 (it is the 3 L bottle;
// the capacity never changed). Saved picks live inside dives.conditions,
// under tank_config.{tankIndex}.type and gas_tank_size_choice.{gasIndex}.type.
return new class extends Migration
{
    public function up()
    {
        $this->rename('AL-20', 'AL-19');
    }

    public function down()
    {
        $this->rename('AL-19', 'AL-20');
    }

    private function rename(string $from, string $to): void
    {
        DB::table('dives')->where('conditions', 'like', '%' . $from . '%')
            ->orderBy('id')
            ->each(function ($dive) use ($from, $to) {
                $conditions = json_decode($dive->conditions, true);
                if (!is_array($conditions)) {
                    return;
                }
                $changed = false;
                foreach (['tank_config', 'gas_tank_size_choice'] as $key) {
                    foreach (($conditions[$key] ?? []) as $i => $cfg) {
                        if (($cfg['type'] ?? null) === $from) {
                            $conditions[$key][$i]['type'] = $to;
                            $changed = true;
                        }
                    }
                }
                if ($changed) {
                    DB::table('dives')->where('id', $dive->id)->update(['conditions' => json_encode($conditions)]);
                }
            });
    }
};

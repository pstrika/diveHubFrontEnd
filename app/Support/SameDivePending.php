<?php

namespace App\Support;

use App\Models\Dive;

/**
 * Dives waiting for the diver's answer to "same dive, another computer?"
 * (Pablo, 2026-10-16: "keep asking the user to choose to combine and mark
 * the 'duplicate' dive with a bg in the card on the landing blade... While
 * the user has not answered, we can call the dives a, b, c etc (like
 * 844.a, 844.b)").
 *
 * The question lives on the dive that arrived second, as
 * conditions.same_dive_suggestion = {dive_id, offset, shape, corr, level}
 * pointing at the dive it matches. Until answered, the pair shares one
 * logbook number: the first dive is "844.a", the asking one "844.b" (a
 * third computer "844.c"), and the asking dives don't use up a number of
 * their own. Combine merges them (Dive::absorb); "different dives" stores
 * same_dive_dismissed and they become two ordinary dives.
 */
class SameDivePending
{
    /**
     * @return array{asks: array<int,int>, groups: array<int, int[]>}
     *   asks:   asking dive id => dive it asks about
     *   groups: dive asked about => its asking dives, oldest first
     */
    private static array $cache = [];

    public static function forget(): void
    {
        self::$cache = [];
    }

    public static function forUser(int $userId): array
    {
        return self::$cache[$userId] ??= self::build($userId);
    }

    private static function build(int $userId): array
    {
        $rows = Dive::where('user_id', $userId)
            ->where('conditions', 'like', '%same_dive_suggestion%')
            ->orderBy('started_at')->orderBy('id')
            ->get(['id', 'conditions']);
        $asks = [];
        foreach ($rows as $r) {
            $t = (int) ($r->conditions['same_dive_suggestion']['dive_id'] ?? 0);
            if ($t && $t !== $r->id) $asks[$r->id] = $t;
        }
        if (!$asks) return ['asks' => [], 'groups' => []];
        // Only questions about dives that are still there (and not
        // themselves asking - chains collapse onto the first dive).
        $live = Dive::where('user_id', $userId)->whereIn('id', array_values($asks))->pluck('id')->flip();
        $groups = [];
        foreach ($asks as $asker => $target) {
            while (isset($asks[$target]) && $asks[$target] !== $asker) $target = $asks[$target];
            if (!isset($live[$target])) { unset($asks[$asker]); continue; }
            $asks[$asker] = $target;
            $groups[$target][] = $asker;
        }
        return ['asks' => $asks, 'groups' => $groups];
    }

    /** "a", "b", ... for the n-th dive of a pending group (0 = the first dive). */
    public static function letter(int $i): string
    {
        return chr(ord('a') + min($i, 25));
    }

    /**
     * Logbook number with the pending letter: "844", "844.a", "844.b".
     * $base is the number of the dive the group belongs to.
     */
    /**
     * id => "844" / "844.b" for every dive the diver has, from one query -
     * the Duplicates page called sequenceNumber() (a COUNT each) twice per
     * pair, 300 ms a time, 331 pairs: a two-minute page (2026-10-20).
     * Same rules as LogbookController::index's own numbering.
     */
    public static function labelsForUser(int $userId): array
    {
        $all = Dive::where('user_id', $userId)->orderBy('started_at')->orderBy('id')->get(['id', 'started_at']);
        $pending = self::forUser($userId);
        $asks = $pending['asks']; $groups = $pending['groups'];
        $num = 0; $base = []; $out = [];
        foreach ($all as $d) {
            if (!isset($asks[$d->id])) $base[$d->id] = ++$num;
        }
        foreach ($all as $d) {
            if (isset($asks[$d->id])) {
                $t = $asks[$d->id];
                $i = array_search($d->id, array_merge([$t], $groups[$t] ?? []), true);
                $out[$d->id] = ($base[$t] ?? '?') . '.' . self::letter((int) $i);
            } elseif (isset($groups[$d->id])) {
                $out[$d->id] = $base[$d->id] . '.a';
            } else {
                $out[$d->id] = (string) $base[$d->id];
            }
        }
        return $out;
    }

    public static function label(Dive $dive, ?array $pending = null): string
    {
        $pending ??= self::forUser($dive->user_id);
        $target = $pending['asks'][$dive->id] ?? (isset($pending['groups'][$dive->id]) ? $dive->id : null);
        if ($target === null) return (string) $dive->sequenceNumber();
        $main = $target === $dive->id ? $dive : Dive::find($target);
        $base = $main ? $main->sequenceNumber() : $dive->sequenceNumber();
        $members = array_merge([$target], $pending['groups'][$target] ?? []);
        $i = array_search($dive->id, $members, true);
        return $base . '.' . self::letter($i === false ? 0 : $i);
    }

    /** The same_dive block of the app's dive JSON (see DiveSyncController). */
    public static function syncBlock(Dive $dive): ?array
    {
        $p = self::forUser($dive->user_id);
        $target = $p['asks'][$dive->id] ?? (isset($p['groups'][$dive->id]) ? $dive->id : null);
        if ($target === null) return null;
        $ids = array_values(array_diff(array_merge([$target], $p['groups'][$target] ?? []), [$dive->id]));
        $with = Dive::where('user_id', $dive->user_id)->whereIn('id', $ids)->get()
            ->map(fn ($d) => ['uuid' => $d->uuid, 'label' => self::label($d, $p), 'computer_model' => $d->computer_model])->values()->all();
        return ['label' => self::label($dive, $p), 'asking' => isset($p['asks'][$dive->id]), 'with' => $with];
    }
}

<?php

namespace App\Support;

use App\Models\Dive;
use App\Models\DivePhoto;
use App\Models\Species;
use Illuminate\Support\Facades\DB;

/**
 * Tagging dives and dive photos with species (Pablo, 2026-10-17: "the same
 * MacDive offers"). One place for the web page and the app. Tagging a
 * photo also tags its dive - if it's in the picture, it was seen.
 */
class SpeciesTagger
{
    /** A species this diver may use: the catalog or one of their own. */
    public static function find(int $userId, string $uuid): Species
    {
        return Species::visibleTo($userId)->where('uuid', $uuid)->firstOrFail();
    }

    public static function tagDive(Dive $dive, Species $species, ?int $count = null, ?string $notes = null): void
    {
        $exists = DB::table('dive_species')->where('dive_id', $dive->id)->where('species_id', $species->id)->exists();
        if ($exists) {
            $dive->species()->updateExistingPivot($species->id, array_filter(['count' => $count, 'notes' => $notes], fn ($v) => $v !== null));
        } else {
            $dive->species()->attach($species->id, ['count' => $count, 'notes' => $notes]);
        }
        $dive->logUpdated();
    }

    /** Off the dive, and off every photo of that dive. */
    public static function untagDive(Dive $dive, Species $species): void
    {
        $dive->species()->detach($species->id);
        DB::table('dive_photo_species')->where('species_id', $species->id)
            ->whereIn('dive_photo_id', $dive->photos()->pluck('id'))->delete();
        $dive->logUpdated();
    }

    public static function tagPhoto(DivePhoto $photo, Species $species): void
    {
        $photo->species()->syncWithoutDetaching([$species->id]);
        if ($photo->dive && !DB::table('dive_species')->where('dive_id', $photo->dive_id)->where('species_id', $species->id)->exists()) {
            $photo->dive->species()->attach($species->id);
        }
        $photo->touch();
        $photo->dive?->logUpdated();
    }

    /** Off the photo only - still seen on the dive. */
    public static function untagPhoto(DivePhoto $photo, Species $species): void
    {
        $photo->species()->detach($species->id);
        $photo->touch();
        $photo->dive?->logUpdated();
    }

    /**
     * The app's PUT: the dive's whole species list ({uuid => count|null}).
     * Species only on photos of this dive that aren't in the list come off
     * those photos too.
     */
    public static function syncDive(Dive $dive, array $countsByUuid): void
    {
        $ids = [];
        foreach ($countsByUuid as $uuid => $count) {
            $ids[self::find($dive->user_id, (string) $uuid)->id] = ['count' => $count ? (int) $count : null];
        }
        $dive->species()->sync($ids);
        DB::table('dive_photo_species')->whereIn('dive_photo_id', $dive->photos()->pluck('id'))
            ->whereNotIn('species_id', array_keys($ids))->delete();
        $dive->logUpdated();
    }

    /** The app's PUT: a photo's whole species list; each also lands on the dive. */
    public static function syncPhoto(DivePhoto $photo, array $uuids): void
    {
        $ids = array_map(fn ($u) => self::find($photo->user_id, (string) $u)->id, $uuids);
        $photo->species()->sync($ids);
        if ($photo->dive) $photo->dive->species()->syncWithoutDetaching($ids);
        $photo->touch();
        $photo->dive?->logUpdated();
    }
}

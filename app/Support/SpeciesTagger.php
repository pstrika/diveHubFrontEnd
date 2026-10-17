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
}

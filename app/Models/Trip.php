<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Log;
use App\Models\Event;
use Carbon\Carbon;

class Trip extends Model
{
    use HasFactory;

    protected $connection = 'mysql_trips'; // Use the new connection for this model
    protected $table = 'trips';

    public function weatherday()
    {
        return $this->hasOne(Weatherday::class, 'date', 'date');

    }

    public function site(): HasMany
    {
        // Explode the comma-separated site IDs and retrieve the related sites
        //return Site::whereIn('id', explode(',', $this->siteId))->get();
        return $this->hasMany(Site::class, 'id', 'siteId');
    }

    public function operator(): HasOne
    {
        // Explode the comma-separated site IDs and retrieve the related sites
        //return Site::whereIn('id', explode(',', $this->siteId))->get();
        return $this->hasOne(Operator::class, 'id', 'operatorId');
    }

    public static function tripInEvent($event) {
        return self::findByComposite($event->date, $event->time, $event->operatorId, $event->tripName, $event);
    }

    /**
     * Re-resolves a GroupDive's snapshot back to today's live Trip row, the
     * same way tripInEvent() does for the personal calendar - trip ids churn
     * daily (re-scraped), so group_dives stores the identifying fields
     * instead of a raw tripId.
     */
    public static function tripInGroupDive($groupDive) {
        return self::findByComposite($groupDive->date, $groupDive->time, $groupDive->operatorId, $groupDive->tripName, $groupDive);
    }

    /**
     * Known generic/placeholder trip names a scraper writes before an
     * operator fills in the real site - e.g. Pura Vida Divers publishes
     * "Technical Dive Charter" until the site is decided, then renames it
     * in place. Deliberately a short, explicit allowlist rather than a
     * broad heuristic (Pablo, 2026-09-19): a false match here would read a
     * genuine cancellation as a harmless rename.
     */
    private const PLACEHOLDER_NAMES = ['technical dive charter'];

    /**
     * Trip ids are not durable (re-scraped daily with new auto-increment
     * ids), so anything that needs to remember "this trip" long-term stores
     * this composite key instead and re-resolves the live row via this
     * lookup when needed. $model, when given, is the Event/GroupDive this
     * composite came from - if the match turns out to be a rename (see
     * resolveRenamedTrip()), its stored tripName is corrected in place so
     * future lookups hit the exact match above directly.
     */
    private static function findByComposite($date, $time, $operatorId, $tripName, $model = null) {
        $trimmedName = trim($tripName);
        $candidates = self::slotCandidates($date, $time, $operatorId);

        $exact = $candidates->filter(fn ($c) => trim($c->tripName) === $trimmedName);
        if ($exact->count() === 1) {
            return $exact->first();
        }
        if ($exact->count() > 1) {
            return 0; // duplicate scrape rows for this slot - genuinely ambiguous
        }

        $renamed = self::resolveRenamedTrip($trimmedName, $candidates);
        self::healRename($model, $operatorId, $date, $time, $trimmedName, $renamed);

        return $renamed ?: 0;
    }

    /**
     * Does the live schedule still carry this trip? Unlike findByComposite()
     * this answers true when the scraper wrote the same trip twice (it
     * happens) - "duplicated" must never read as "cancelled". Used by
     * App\Console\Commands\DetectCancelledTrips; pass $model (the Event
     * whose composite this is) to also self-heal a detected rename - the
     * command omits it during --dry-run so nothing gets written.
     */
    public static function existsForComposite($date, $time, $operatorId, $tripName, $model = null): bool
    {
        $trimmedName = trim($tripName);
        $candidates = self::slotCandidates($date, $time, $operatorId);

        if ($candidates->contains(fn ($c) => trim($c->tripName) === $trimmedName)) {
            return true; // exact (trimmed) match, duplicates included - definitely not cancelled
        }

        $renamed = self::resolveRenamedTrip($trimmedName, $candidates);
        self::healRename($model, $operatorId, $date, $time, $trimmedName, $renamed);

        return $renamed !== null;
    }

    private static function slotCandidates($date, $time, $operatorId)
    {
        return Trip::where('date', $date)
            ->where('departureTime', $time)
            ->where('operatorId', $operatorId)
            ->get();
    }

    /**
     * The operator kept the trip's slot (same operator, date, departure
     * time) but changed its name - a typo fixed, a cert suffix added, a
     * tech placeholder replaced with the real site once decided. Only
     * trusted when it resolves unambiguously: a slot filled by something
     * wholly unrelated (a real cancellation, replaced by a different trip)
     * must still read as missing, which is why this requires either a
     * close textual match or a recognized placeholder name - never just
     * "one trip happens to be left in this slot" on its own
     * (Pablo, 2026-09-19: found via Lady Luck gaining "(AOW)", Skycliffe's
     * scraper typo, Pura Vida's tech-placeholder rename - vs. Squalo,
     * where the operator swapped in a completely different dive at the
     * same slot, which is a real cancellation, not a rename).
     */
    private static function resolveRenamedTrip(string $trimmedName, $candidates): ?Trip
    {
        if ($candidates->isEmpty()) {
            return null;
        }

        // A near-identical name (typo fix, a couple of characters off) is
        // trustworthy regardless of how many other trips share the slot.
        $closeSpelling = $candidates->filter(function ($c) use ($trimmedName) {
            $liveName = trim($c->tripName);
            return strlen($liveName) >= 6 && levenshtein($trimmedName, $liveName) <= 2;
        });
        if ($closeSpelling->count() === 1) {
            return $closeSpelling->first();
        }

        // The saved name continues into (or is continued by) exactly one
        // candidate's name - a cert suffix added, a qualifier dropped.
        $continued = $candidates->filter(function ($c) use ($trimmedName) {
            $liveName = trim($c->tripName);
            if ($liveName === '' || min(strlen($liveName), strlen($trimmedName)) < 6) {
                return false;
            }
            return str_starts_with($liveName, $trimmedName) || str_starts_with($trimmedName, $liveName);
        });
        if ($continued->count() === 1) {
            return $continued->first();
        }

        // A known generic placeholder, with exactly one trip left in the
        // slot once the operator filled in the real site.
        if ($candidates->count() === 1 && in_array(strtolower($trimmedName), self::PLACEHOLDER_NAMES, true)) {
            return $candidates->first();
        }

        return null;
    }

    private static function healRename($model, $operatorId, $date, $time, string $trimmedName, ?Trip $renamed): void
    {
        if (!$renamed || !$model || trim($model->tripName) === $renamed->tripName) {
            return;
        }

        Log::info('Trip renamed in place: "' . $trimmedName . '" -> "' . $renamed->tripName . '" (operator ' . $operatorId . ', ' . $date . ' ' . $time . ')');
        $model->tripName = $renamed->tripName;
        $model->save();
    }
}
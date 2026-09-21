<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Trip;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Log;

class Event extends Model
{
    use HasFactory;

    protected $connection = 'mysql_trips'; // Use the new connection for this model
    protected $table = 'events';

    protected $fillable = [
        'userId',
        'date',
        'time',
        'operatorId',
        'tripName',
        'booked',
        'waiver_signed',
        '_token', // Add _token to the fillable property
        // Which group dive (if any) put this on the calendar - see
        // GroupDiveController::addRsvp()/leave() and
        // EventController::removeFromCalendar() (Pablo, 2026-09-19).
        'group_dive_id',
        // Cancellation tracking - see App\Console\Commands\DetectCancelledTrips.
        'missing_since',
        'missing_checks',
        'cancelled_at',
        'cancel_notified_at',

        // Other fields...
    ];

    protected $casts = [
        'missing_since' => 'datetime',
        'cancelled_at' => 'datetime',
        'cancel_notified_at' => 'datetime',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function operator(): HasOne
    {
        return $this->hasOne(Operator::class);
    }

    /** Which group dive auto-added this, if any. Null = the diver saved it themselves. */
    public function groupDive(): BelongsTo
    {
        return $this->belongsTo(GroupDive::class, 'group_dive_id');
    }

    /** Confirmed gone from the operator's schedule - see DetectCancelledTrips. */
    public function isCancelled(): bool
    {
        return $this->cancelled_at !== null;
    }

    /**
     * The signed-in user's own calendar entry for this trip, if any - used
     * both to detect "already saved" and to link a "Remove" action straight
     * at the real event id, rather than re-running the add action and
     * relying on it to no-op (Pablo, 2026-09-20: the "In my calendar"
     * button kept pointing at AddEventToCalendar, so clicking it twice
     * created a duplicate).
     */
    public static function findInCalendar($tripId): ?self
    {
        $trip = Trip::findOrFail($tripId);

        return Event::where([
            [ 'userId', '=', auth()->user()->id],
            [ 'date', '=', $trip->date],
            [ 'time', '=', $trip->departureTime],
            [ 'operatorId', '=', $trip->operatorId],
            [ 'tripName', '=', $trip->tripName]
        ])->first();
    }

    public static function alreadyInCalendar($tripId): bool
    {
        return self::findInCalendar($tripId) !== null;
    }

}

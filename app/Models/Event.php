<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Trip;
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
        '_token', // Add _token to the fillable property
        
        // Other fields...
    ];


    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function operator(): HasOne
    {
        return $this->hasOne(Operator::class);
    }

    public static function alreadyInCalendar($tripId) {
        $trip = Trip::findOrFail($tripId);

        return Event::where([
            [ 'userId', '=', auth()->user()->id],
            [ 'date', '=', $trip->date],
            [ 'time', '=', $trip->departureTime],
            [ 'operatorId', '=', $trip->operatorId],
            [ 'tripName', '=', $trip->tripName]
        ])->exists();

    }

}

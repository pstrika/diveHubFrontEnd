<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One "how was your dive?" send - see App\Console\Commands\
 * SendPostDiveFeedbackRequests and App\Http\Controllers\DiveFeedbackController.
 * The token, not a session, is what authorizes the wizard (Pablo, 2026-10-04).
 */
class PostDiveFeedbackRequest extends Model
{
    protected $connection = 'mysql_trips';
    protected $table = 'post_dive_feedback_requests';

    protected $fillable = [
        'event_id',
        'user_id',
        'trip_id',
        'site_id',
        'operator_id',
        'token',
        'channel',
        'sent_at',
        'expires_at',
        'photos_uploaded_at',
        'photo_reminder_requested',
        'photo_reminder_sent_at',
        'completed_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'expires_at' => 'datetime',
        'photos_uploaded_at' => 'datetime',
        'photo_reminder_requested' => 'boolean',
        'photo_reminder_sent_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site_id', 'id');
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class, 'operator_id', 'id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}

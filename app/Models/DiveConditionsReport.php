<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The conditions step of the post-dive feedback wizard - see
 * App\Http\Controllers\DiveFeedbackController (Pablo, 2026-10-04).
 */
class DiveConditionsReport extends Model
{
    protected $connection = 'mysql_trips';
    protected $table = 'dive_conditions_reports';

    public const CURRENT_STRENGTHS = ['none', 'mild', 'moderate', 'strong'];
    public const CURRENT_DIRECTIONS = ['N', 'S', 'E', 'W'];

    protected $fillable = [
        'event_id',
        'user_id',
        'site_id',
        'visibility_ft',
        'waves_ft',
        'current_strength',
        'current_direction',
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
}

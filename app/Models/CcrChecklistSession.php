<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One completed rEvo CCR build-up checklist (Pablo, 2026-10-15) - see config/ccr_checklist_templates.php for what `answers`' keys mean for a given `template_version`. */
class CcrChecklistSession extends Model
{
    use HasFactory;

    protected $table = 'ccr_checklist_sessions';

    protected $fillable = [
        'user_id',
        'template_version',
        'build_date',
        'answers',
        'signature',
    ];

    protected $casts = [
        'answers' => 'array',
        'build_date' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

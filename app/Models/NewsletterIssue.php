<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsletterIssue extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'subject', 'preheader', 'headline', 'body_markdown', 'conditions',
        'status', 'created_by', 'sent_at', 'sent_count', 'scheduled_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'scheduled_at' => 'datetime',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    /** A draft with a pending send time - not a separate status, see the migration's docblock. */
    public function isScheduled(): bool
    {
        return !$this->isSent() && $this->scheduled_at !== null;
    }
}

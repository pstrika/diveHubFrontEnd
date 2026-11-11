<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * One step a diver added to their own checklist (Zach, 2026-11-10). See the
 * migration for how position works, and App\Support\CcrChecklistBuilder for
 * how these are woven into a template.
 */
class CcrCustomItem extends Model
{
    protected $table = 'ccr_custom_items';

    protected $fillable = ['user_id', 'unit_id', 'stage', 'text', 'after_key', 'position'];

    protected static function booted()
    {
        static::creating(fn (CcrCustomItem $i) => $i->uuid ??= (string) Str::uuid());
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(CcrUnit::class, 'unit_id');
    }

    /** The key this step's tick is stored under in a session's answers. */
    public function answerKey(): string
    {
        return 'custom_' . $this->id;
    }

    /** The shape kept on a saved session, and handed to the builder. */
    public function snapshot(): array
    {
        return [
            'key' => $this->answerKey(),
            'text' => $this->text,
            'after_key' => $this->after_key,
            'position' => (int) $this->position,
        ];
    }
}

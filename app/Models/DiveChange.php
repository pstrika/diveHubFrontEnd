<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Append-only change feed row (SYNC-CONTRACT.md section 3) - one per
 * create/update/(soft)delete of a Dive, written in the same transaction
 * as the change itself. Never updated or deleted after insert; `id` is
 * the feed's monotonic cursor, not `created_at` - see the migration's
 * docblock for why a timestamp isn't used.
 */
class DiveChange extends Model
{
    const UPDATED_AT = null;

    protected $fillable = ['user_id', 'dive_uuid', 'action'];

    public static function record(int $userId, string $diveUuid, string $action): void
    {
        static::create([
            'user_id' => $userId,
            'dive_uuid' => $diveUuid,
            'action' => $action,
        ]);
    }
}

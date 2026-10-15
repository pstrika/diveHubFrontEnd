<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/** One-time code for the app sign-in exchange (docs/APP-AUTH.md). */
class AppLoginCode extends Model
{
    // No created_at/updated_at columns - expires_at/used_at already
    // capture everything that matters about this row's lifecycle.
    public $timestamps = false;

    protected $fillable = ['code', 'user_id', 'expires_at', 'used_at'];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public static function issue(int $userId): self
    {
        return static::create([
            'code' => Str::random(40),
            'user_id' => $userId,
            'expires_at' => now()->addSeconds(60),
        ]);
    }

    public function isExpired(): bool
    {
        return $this->used_at !== null || now()->greaterThan($this->expires_at);
    }
}

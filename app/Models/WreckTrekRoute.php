<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A saved Wreck Trek route: user_id null = curated, shown to everyone. */
class WreckTrekRoute extends Model
{
    protected $fillable = ['user_id', 'name', 'stops', 'is_public'];

    protected $casts = ['stops' => 'array', 'is_public' => 'boolean'];

    public function toApi(?int $viewerId = null): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'stops' => $this->stops,
            'isPublic' => $this->is_public,
            'mine' => $viewerId !== null && (int) $this->user_id === $viewerId,
        ];
    }
}

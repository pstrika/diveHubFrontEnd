<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class Photo extends Model
{
    use HasFactory;
    protected $connection = 'mysql_trips'; // Use the new connection for this model
    protected $table = 'photos';

    protected $fillable = [
        'id',
        '_token', // Add _token to the fillable property
        'created_at',
        'updated_at',
        'file',
        'desc',
        'credit',
        'siteId',
    ];

    public function deletePhoto() {
        Storage::disk('siteAssets')->delete('img/sites/' . $this->file);
        $this->delete();
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'pics', 'id');
    }
}

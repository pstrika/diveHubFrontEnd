<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class GroupMessagePhoto extends Model
{
    use HasFactory;

    protected $connection = 'mysql_trips';
    protected $table = 'group_message_photos';

    protected $fillable = [
        'group_message_id',
        'file',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(GroupMessage::class, 'group_message_id');
    }

    public function deletePhoto()
    {
        Storage::disk('siteAssets')->delete($this->file);
        $this->delete();
    }
}

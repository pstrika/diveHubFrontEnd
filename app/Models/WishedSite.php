<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Site;

class WishedSite extends Model
{
    use HasFactory;
    protected $connection = 'mysql_trips'; // Use the new connection for this model
    protected $table = 'wishedSites';

    protected $fillable = [
        'siteId',
        'userId',
        'notified_email',
        'notified_sms',
    ];

    public function site(): HasOne
    {
        return $this->hasOne(Site::class, 'id', 'siteId');
    }
}

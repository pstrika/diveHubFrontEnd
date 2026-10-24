<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Operator extends Model
{
    use HasFactory;

    protected $connection = 'mysql_trips'; // Use the new connection for this model
    protected $table = 'operators';

    // The operators table has no created_at/updated_at columns.
    public $timestamps = false;

    protected $fillable = [
        'rate',
        'votes',
    ];

    /** id => slug for every operator, loaded once per request (a few dozen rows). */
    private static ?array $slugs = null;

    /**
     * The operator page's canonical URL from an operator id: the slug,
     * not the numeric id (SEO audit, 2026-10-24: 114 trip cards on /Trips
     * linked to /OperatorDetails/48, each a 301, and Google kept listing
     * the numeric URL).
     */
    public static function urlFor($id): ?string
    {
        if (!$id) return null;
        self::$slugs ??= self::query()->pluck('slug', 'id')->all();
        return route('OperatorDetails', ['id' => self::$slugs[(int) $id] ?? null ?: $id]);
    }
}

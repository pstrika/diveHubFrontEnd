<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * A place an operator works from: storefront, dock, fill station (shop
 * portal, 2026-11-13). A storefront can be a diver's home shop.
 *
 * Never deleted once it exists: a location can be somebody's home shop,
 * so it is switched off (is_active) instead.
 */
class OperatorLocation extends Model
{
    protected $connection = 'mysql_trips';
    protected $table = 'operator_locations';
    protected $guarded = ['id'];
    protected $casts = [
        'hours' => 'array',
        'is_home_eligible' => 'boolean',
        'is_active' => 'boolean',
    ];

    public const TYPES = [
        'storefront'   => 'Storefront',
        'dock'         => 'Dock',
        'fill_station' => 'Fill station',
        'other'        => 'Other',
    ];

    public const DAYS = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

    public function offerings()
    {
        return $this->hasMany(OperatorOffering::class, 'operator_location_id')->orderBy('sort_order')->orderBy('id');
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? 'Other';
    }

    /** "455 S Federal Hwy, Deerfield Beach, FL 33441", skipping what is empty. */
    public function addressLine(): string
    {
        $street = trim($this->address_line1 . ' ' . $this->address_line2);
        $cityState = trim(implode(', ', array_filter([$this->city, $this->state])) . ' ' . $this->postal_code);
        return implode(', ', array_filter([$street, $cityState]));
    }

    /** A slug unique within the operator: "main-store", "main-store-2". */
    public static function slugFor(int $operatorId, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'location';
        $slug = $base;
        $n = 1;
        while (static::where('operator_id', $operatorId)->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '<>', $ignoreId))->exists()) {
            $slug = $base . '-' . (++$n);
        }
        return $slug;
    }
}

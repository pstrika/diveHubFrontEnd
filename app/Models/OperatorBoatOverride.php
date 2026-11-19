<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A shop's own facts about one of its boats, shown in place of boats. Shop portal, 2026-11-19. */
class OperatorBoatOverride extends Model
{
    protected $connection = 'mysql_trips';
    protected $table = 'operator_boat_overrides';
    protected $guarded = ['id'];

    /** Shop-editable column => label and unit, in the order the operator page prints them. */
    public const FIELDS = [
        'name' => ['Boat name', null],
        'type' => ['Type', null],
        'capacity' => ['Capacity', 'divers'],
        'tec_capacity' => ['Tech capacity', 'divers'],
        'manufacturer' => ['Builder', null],
        'length' => ['Length', 'ft'],
        'beam' => ['Beam', 'ft'],
        'speed' => ['Speed', 'kn'],
        'power' => ['Power', null],
    ];
}

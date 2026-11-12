<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A diver's own order and headings for one rebreather's checklist at one
 * stage (Zach, 2026-11-11). `rows` is the checklist top to bottom:
 *   ['t' => 's', 'k' => 'o2_open']                       a step, by key
 *   ['t' => 'h', 'id' => 'a1b2c3d4', 'text' => 'Oxygen'] a heading
 * Only exists once the diver has changed something. Read through
 * App\Support\CcrChecklistBuilder, never directly: the builder is what
 * guarantees every built in step is still on the checklist.
 */
class CcrLayout extends Model
{
    protected $table = 'ccr_layouts';

    protected $fillable = ['unit_id', 'stage', 'template_version', 'rows'];

    protected $casts = ['rows' => 'array'];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One completed rEvo CCR build-up checklist (Pablo, 2026-10-15) - see config/ccr_checklist_templates.php for what `answers`' keys mean for a given `template_version`. */
class CcrChecklistSession extends Model
{
    use HasFactory;

    protected $table = 'ccr_checklist_sessions';

    protected $fillable = [
        'user_id',
        'unit_id',
        'template_version',
        'stage',
        'build_date',
        'answers',
        'custom_items',
        'layout',
        'signature',
        'disclaimer_version',
        'disclaimer_accepted_at',
        'client_uuid',
    ];

    protected $casts = [
        'answers' => 'array',
        'custom_items' => 'array',
        'layout' => 'array',
        'build_date' => 'datetime',
        'disclaimer_accepted_at' => 'datetime',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(CcrUnit::class, 'unit_id');
    }

    /** "Build check", "Pre-jump"... Rows from before stages existed are builds. */
    public function stageLabel(): string
    {
        $stage = $this->stage ?: 'build';
        return config('ccr_checklist_templates.stages')[$stage]['label'] ?? ucfirst($stage);
    }

    /* -------------------------------------------------------------- */
    /* Build <-> closed check link (Zach, 2026-11-10).                 */
    /*                                                                 */
    /* Worked out from the dates, not stored. A build covers the unit  */
    /* until it is next built, so a closed check belongs to the most   */
    /* recent build before it, and a build's closed check is the first */
    /* one after it and before the next build. Nothing to keep in      */
    /* sync, and it is right for every row saved before this existed.  */
    /* 72 hours is the cut off: a closed check a week after a build is */
    /* not that build's closed check.                                  */
    /* -------------------------------------------------------------- */

    private const LINK_HOURS = 72;

    /**
     * Sessions of one stage for the same diver and the same rebreather.
     * Rows from before rebreathers could be named have no unit_id and are
     * shown under the diver's oldest unit, so they count as that unit here
     * too (same rule as CcrChecklistController::sessionsFor).
     */
    private function sameUnit(string $stage)
    {
        $oldest = CcrUnit::where('user_id', $this->user_id)->whereNull('retired_at')->orderBy('id')->value('id');
        $mine = $this->unit_id ?? $oldest;

        return static::where('user_id', $this->user_id)->where('stage', $stage)
            ->where(function ($w) use ($mine, $oldest) {
                $mine === null ? $w->whereNull('unit_id') : $w->where('unit_id', $mine);
                if ($mine !== null && $mine === $oldest) {
                    $w->orWhereNull('unit_id');
                }
            });
    }

    /** For a closed check: the build it belongs to, if there is one. */
    public function linkedBuild(): ?self
    {
        if (($this->stage ?: 'build') !== 'closed') {
            return null;
        }
        return $this->sameUnit('build')
            ->where('build_date', '<=', $this->build_date)
            ->where('build_date', '>=', $this->build_date->copy()->subHours(self::LINK_HOURS))
            ->orderByDesc('build_date')->first();
    }

    /** For a build: the closed check done on it, if there is one yet. */
    public function linkedClosed(): ?self
    {
        if (($this->stage ?: 'build') !== 'build') {
            return null;
        }
        $nextBuild = $this->sameUnit('build')->where('build_date', '>', $this->build_date)->min('build_date');
        $until = $this->build_date->copy()->addHours(self::LINK_HOURS);
        if ($nextBuild && \Carbon\Carbon::parse($nextBuild)->lt($until)) {
            $until = \Carbon\Carbon::parse($nextBuild);
        }
        return $this->sameUnit('closed')
            ->where('build_date', '>=', $this->build_date)
            ->where('build_date', '<', $until)
            ->orderBy('build_date')->first();
    }

    /** A build with no closed check yet, recent enough that one is still expected. */
    public function awaitsClosed(): bool
    {
        return ($this->stage ?: 'build') === 'build'
            && $this->build_date->gte(now()->subHours(self::LINK_HOURS))
            && !$this->linkedClosed()
            && !$this->sameUnit('build')->where('build_date', '>', $this->build_date)->exists();
    }

    /**
     * This session's checklist exactly as it was when saved: the template
     * version stored on the row, with the copy of the diver's own steps
     * stored beside it woven in (Zach, 2026-11-10). Never the live ones, so
     * rewording or deleting a step later cannot change a saved record.
     */
    public function mergedTemplate(): ?array
    {
        $template = config('ccr_checklist_templates.versions')[$this->template_version] ?? null;
        if (!$template) {
            return null;
        }
        // ...and in the order, under the headings, it was saved with (2026-11-11).
        $template = \App\Support\CcrChecklistBuilder::build($template, $this->custom_items ?? [], $this->layout);
        if (!empty($template['own'])) {
            $template['label'] = ($this->unit ? $this->unit->name . ': ' : '') . $this->stageLabel();
        }
        return $template;
    }

    /** How many of this session's steps were ticked, and out of how many. */
    public function tickCount(): array
    {
        $template = $this->mergedTemplate();
        $items = $template['items'] ?? [];
        $done = 0;
        foreach ($items as $item) {
            if (!empty($this->answers[$item['key']]['checked'])) $done++;
        }
        return [$done, count($items)];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

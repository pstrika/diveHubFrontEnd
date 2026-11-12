<?php

namespace App\Http\Controllers;

use App\Models\CcrChecklistSession;
use App\Models\CcrCustomItem;
use App\Models\CcrLayout;
use App\Models\CcrScrubberEvent;
use App\Models\CcrUnit;
use App\Support\CcrChecklistBuilder;
use App\Support\CcrScrubber;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * rEvo CCR Build-up checklist (Pablo, 2026-10-15): a digital version of the
 * 16-item paper checklist he fills out every time he builds his rebreather
 * before a dive. Gated to Pablo's own account only (Pablo, 2026-10-17:
 * "make ONLY available to my user pstrika@gmail.com (nobody else - not
 * even platform admins)") via $this->authorize('ccrChecklistAccess',
 * User::class) first in every action (UserPolicy::ccrChecklistAccess) -
 * deliberately NOT the isAdmin() gate every other admin tool in this app
 * uses, since this is a personal equipment log, not an admin tool.
 * routes/web.php uses 'auth' not 'guest'.
 *
 * The checklist's own structure (which items, which fields) lives in
 * config/ccr_checklist_templates.php, not here or in the blade views - see
 * that file's own docblock for why (the paper template has already
 * changed once, v1.0 -> v1.5, and will likely change again).
 */
class CcrChecklistController extends Controller
{
    /**
     * Looks up one template version's definition. NOT config('ccr_checklist_templates.versions.' . $version) -
     * version strings like "v1.5" contain a dot themselves, which
     * Laravel's dot-notation config() parser would misread as two nested
     * keys ("v1" then "5") instead of one literal key "v1.5", silently
     * returning null (caught locally: Create.blade.php 500'd on a null
     * $template before this fix). Indexing the 'versions' array directly
     * with the plain string key sidesteps that parsing entirely.
     */
    private function template(string $version): ?array
    {
        return config('ccr_checklist_templates.versions')[$version] ?? null;
    }

    /**
     * The diver's rebreathers, oldest first (Zach, 2026-11-10: "many divers
     * have multiple rebreathers"). Retired ones are left out everywhere
     * except on the sessions that were saved against them.
     */
    private function units()
    {
        return CcrUnit::where('user_id', auth()->id())->whereNull('retired_at')->orderBy('id')->get();
    }

    /**
     * Which rebreather the diver is looking at. ?unit=<uuid> picks one and
     * is remembered for the rest of the visit, so every link does not have
     * to carry it. Falls back to their first. Null when they have none:
     * everything still works then, as it did before units existed.
     */
    private function currentUnit($units = null): ?CcrUnit
    {
        $units = $units ?? $this->units();
        $wanted = request()->query('unit') ?: request()->input('unit') ?: session('ccr_unit');
        $unit = $wanted ? $units->firstWhere('uuid', $wanted) : null;
        $unit = $unit ?: $units->first();
        if ($unit) {
            session(['ccr_unit' => $unit->uuid]);
        }
        return $unit;
    }

    /**
     * stage => template version for a new session.
     *
     * With a unit: that unit's own stages, which may be none.
     * With no unit at all: the rEvo set, because that is what this feature
     * was for everybody who used it before units existed.
     */
    private function stageVersions(?CcrUnit $unit): array
    {
        if ($unit) {
            // A unit with no built in checklist still gets every stage,
            // each one made of the diver's own steps (the 'own' version).
            return $unit->stageVersions()
                ?: array_fill_keys(array_keys(config('ccr_checklist_templates.stages', [])), 'own');
        }
        $revo = config('ccr_checklist_templates.units', [])['revo'] ?? [];
        return $revo['stages'] ?? ['build' => config('ccr_checklist_templates.active')];
    }

    /** The diver's own steps for one rebreather and stage, as the builder wants them. */
    private function customsFor(?CcrUnit $unit, string $stage): array
    {
        if (!$unit) {
            return [];
        }
        return CcrCustomItem::where('unit_id', $unit->id)->where('stage', $stage)
            ->orderBy('position')->orderBy('id')->get()
            ->map(fn ($c) => $c->snapshot())->all();
    }

    /**
     * The diver's own order and headings for one rebreather and stage, or
     * null when they have never changed the standard checklist.
     */
    private function rowsFor(?CcrUnit $unit, string $stage): ?array
    {
        if (!$unit) {
            return null;
        }
        $layout = CcrLayout::where('unit_id', $unit->id)->where('stage', $stage)->first();
        return $layout ? (array) $layout->rows : null;
    }

    /**
     * A diver's sessions for one rebreather.
     *
     * Sessions saved before units existed have no unit_id. Rather than
     * rewrite those rows, they are shown under the diver's OLDEST unit
     * (and under "no unit" when they have none), so nothing saved earlier
     * ever disappears from the page when a first rebreather is added.
     */
    private function sessionsFor(?CcrUnit $unit, $units)
    {
        $q = CcrChecklistSession::where('user_id', auth()->id());
        if ($unit) {
            $isOldest = $units->first() && $units->first()->id === $unit->id;
            $q->where(function ($w) use ($unit, $isOldest) {
                $w->where('unit_id', $unit->id);
                if ($isOldest) {
                    $w->orWhereNull('unit_id');
                }
            });
        }
        return $q;
    }

    /**
     * The hub: which rebreather, its stages as buttons, the equipment
     * dashboard, and history.
     *
     * The dashboard and the build table read build-sheet answers (cells,
     * scrubber, battery), so they are only ever given build sessions. The
     * other stages get their own shorter list.
     */
    public function index()
    {
        $this->authorize('ccrChecklistAccess', User::class);

        $units = $this->units();
        $unit = $this->currentUnit($units);

        $all = $this->sessionsFor($unit, $units)
            ->orderByDesc('build_date')
            // custom_items too: the "N of M" count on each row is counted
            // against the diver's own steps as saved, and a checklist made
            // only of those read "0 of 0" without it.
            ->get(['id', 'unit_id', 'template_version', 'stage', 'build_date', 'answers', 'custom_items', 'created_at']);

        // The equipment dashboard and the build table are the rEvo build
        // sheet's (cells, scrubber canisters, the rEvo logo). A build on any
        // other rebreather has none of those answers, so it goes in the
        // plain history list with the other stages instead of a table of
        // dashes.
        $isRevo = !$unit || $unit->model === 'revo';
        $sessions = $isRevo ? $all->filter(fn ($s) => ($s->stage ?: 'build') === 'build')->values() : collect();
        $otherSessions = $isRevo ? $all->filter(fn ($s) => ($s->stage ?: 'build') !== 'build')->values() : $all;

        $stageDefs = config('ccr_checklist_templates.stages', []);
        $customCounts = $unit
            ? CcrCustomItem::where('unit_id', $unit->id)->selectRaw('stage, count(*) as c')->groupBy('stage')->pluck('c', 'stage')->all()
            : [];
        $stages = [];
        foreach ($this->stageVersions($unit) as $stage => $version) {
            $last = $all->first(fn ($s) => ($s->stage ?: 'build') === $stage);
            $builtIn = count($this->template($version)['items'] ?? []);
            $stages[] = [
                'n' => count($stages) + 1,
                'doneToday' => $last && $last->build_date->isToday(),
                'phase' => $stageDefs[$stage]['phase'] ?? null,
                'customCount' => (int) ($customCounts[$stage] ?? 0),
                'builtInCount' => $builtIn,
                'key' => $stage,
                'label' => $stageDefs[$stage]['label'] ?? ucfirst($stage),
                'icon' => $stageDefs[$stage]['icon'] ?? 'checklist',
                'when' => $stageDefs[$stage]['when'] ?? '',
                'last' => $last,
            ];
        }

        return view('pages.CcrChecklist.Index', [
            'sessions' => $sessions,
            'otherSessions' => $otherSessions,
            // Every stage, newest day first, in the order done within a day.
            'history' => $all->groupBy(fn ($s) => $s->build_date->format('Y-m-d'))
                ->map(fn ($day) => $day->sortBy('build_date')->values()),
            'equipment' => $this->equipmentDashboard($sessions),
            'units' => $units,
            'unit' => $unit,
            'isRevo' => $isRevo,
            // Time on the scrubbers, for any rebreather (Zach, 2026-11-11).
            'scrubberStatus' => $unit ? CcrScrubber::status($unit) : null,
            'unitOptions' => config('ccr_checklist_templates.units', []),
            'stages' => $stages,
        ]);
    }

    private function unitRules(): array
    {
        return [
            'model' => ['required', \Illuminate\Validation\Rule::in(array_keys(config('ccr_checklist_templates.units', [])))],
            'name' => 'required|string|max:120',
            'serial_number' => 'nullable|string|max:120',
            // Scrubber limits as hours and minutes boxes (2026-11-11).
            'top_h' => 'nullable|integer|min:0|max:24',
            'top_m' => 'nullable|integer|min:0|max:59',
            'reserve_h' => 'nullable|integer|min:0|max:24',
            'reserve_m' => 'nullable|integer|min:0|max:59',
        ];
    }

    /**
     * The validated rebreather form as columns. The scrubber limits go into
     * `settings` beside whatever else is there. Both boxes empty means "no
     * limit set": for a rEvo that falls back to 3:00 and 1:30, for anything
     * else it means time left is never shown.
     */
    private function unitData(array $v, ?CcrUnit $unit = null): array
    {
        $settings = $unit->settings ?? [];
        $scrubber = [];
        foreach (['top', 'reserve'] as $k) {
            $min = CcrScrubber::minutes($v[$k . '_h'] ?? null, $v[$k . '_m'] ?? null);
            if ($min !== null && ($min > 0 || $k === 'reserve')) {
                $scrubber[$k] = $min;
            }
        }
        if ($scrubber) {
            $settings['scrubber'] = $scrubber;
        } else {
            unset($settings['scrubber']);
        }
        return [
            'model' => $v['model'],
            'name' => $v['name'],
            'serial_number' => $v['serial_number'] ?? null,
            'settings' => $settings ?: null,
        ];
    }

    /** Adds a rebreather and switches to it. A diver can have as many as they own. */
    public function storeUnit(Request $request)
    {
        $this->authorize('ccrChecklistAccess', User::class);

        $unit = CcrUnit::create($this->unitData($request->validate($this->unitRules())) + ['user_id' => auth()->id()]);

        return redirect()->route('CcrChecklist', ['unit' => $unit->uuid]);
    }

    /**
     * Rename a rebreather, fix its serial, or change what kind it is.
     *
     * Changing the kind changes which checklists NEW sessions get. Saved
     * sessions are untouched: each one renders against the template version
     * stored on its own row, whatever the unit is called today.
     */
    public function updateUnit(Request $request, $uuid)
    {
        $this->authorize('ccrChecklistAccess', User::class);

        $unit = CcrUnit::where('user_id', auth()->id())->where('uuid', $uuid)->firstOrFail();
        $unit->update($this->unitData($request->validate($this->unitRules()), $unit));

        return redirect()->route('CcrChecklist', ['unit' => $unit->uuid]);
    }

    /**
     * Retire, not delete: the rebreather leaves the picker, its saved
     * checklists stay and still name it. A sold unit's records are still
     * that diver's records.
     */
    public function retireUnit($uuid)
    {
        $this->authorize('ccrChecklistAccess', User::class);

        $unit = CcrUnit::where('user_id', auth()->id())->where('uuid', $uuid)->firstOrFail();
        $unit->retired_at = now();
        $unit->save();
        session()->forget('ccr_unit');

        return redirect()->route('CcrChecklist');
    }

    /**
     * Equipment-status summary for the top of the history page (Pablo,
     * 2026-10-17: "mini dashboard...sensors (installation date, time to
     * replacement...and last read (mV). Last scrubber time and which
     * canister is on top. Also, last battery replacement"). $sessions is
     * already newest-build-first, so every "most recent session that..."
     * lookup below is just "first match walking the collection in order".
     *
     * Sensor install dates: prefer the newest session with that sensor's
     * own o2_sensor_life.sensorN_date set (only populated going forward,
     * via ccr-checklist.js's updates_sensor cascade - never on any of the
     * 196 historical backfilled sessions), else fall back to the newest
     * session where that cell's own *_replaced flag was ticked (true for
     * backfilled data too). Age thresholds (>12mo red, 9-12mo yellow,
     * <9mo green) mirror ccr-checklist.js's own refreshAgeBadge exactly,
     * so the dashboard agrees with the live form's own badges.
     */
    private function equipmentDashboard($sessions): array
    {
        if ($sessions->isEmpty()) {
            return ['sensors' => [], 'scrubberLabel' => null, 'onTop' => null, 'batteryDate' => null];
        }

        $sensorDefs = [
            ['label' => 'Petrel Cell 1', 'group' => 'petrel_mv', 'cell' => 'cell1', 'sensorDate' => 'sensor1_date'],
            ['label' => 'Petrel Cell 2', 'group' => 'petrel_mv', 'cell' => 'cell2', 'sensorDate' => 'sensor2_date'],
            ['label' => 'Petrel Cell 3', 'group' => 'petrel_mv', 'cell' => 'cell3', 'sensorDate' => 'sensor3_date'],
            ['label' => 'Nerd Cell 1', 'group' => 'nerd_mv', 'cell' => 'cell1', 'sensorDate' => 'sensor4_date'],
            ['label' => 'Nerd Cell 2', 'group' => 'nerd_mv', 'cell' => 'cell2', 'sensorDate' => 'sensor5_date'],
        ];

        $sensors = [];
        foreach ($sensorDefs as $def) {
            $installDate = null;
            foreach ($sessions as $session) {
                $dateVal = $session->answers['o2_sensor_life'][$def['sensorDate']] ?? null;
                if (!empty($dateVal)) {
                    $installDate = \Carbon\Carbon::parse($dateVal);
                    break;
                }
            }
            if (!$installDate) {
                foreach ($sessions as $session) {
                    if (!empty($session->answers[$def['group']][$def['cell'] . '_replaced'])) {
                        $installDate = $session->build_date;
                        break;
                    }
                }
            }

            $lastMv = null;
            foreach ($sessions as $session) {
                $mv = $session->answers[$def['group']][$def['cell']] ?? null;
                if ($mv !== null && $mv !== '') {
                    $lastMv = $mv;
                    break;
                }
            }

            $months = null;
            $tone = 'none';
            $replaceBy = null;
            if ($installDate) {
                $months = $installDate->diffInMonths(now());
                $replaceBy = $installDate->copy()->addYear();
                if ($months > 12) $tone = 'poor';
                elseif ($months >= 9) $tone = 'avg';
                else $tone = 'good';
            }

            $sensors[] = [
                'label' => $def['label'],
                'installDate' => $installDate?->format('M j, Y'),
                'replaceBy' => $replaceBy?->format('M j, Y'),
                'months' => $months,
                'tone' => $tone,
                'lastMv' => $lastMv,
            ];
        }

        $latest = $sessions->first();
        $scrubber = $latest->answers['scrubber_time'] ?? [];
        $scrubberLabel = isset($scrubber['result_minutes'])
            ? $scrubber['result_minutes'] . ' mins'
            : (!empty($scrubber['checked']) && $latest->template_version === 'v1.5' ? 'Max Fresh' : '—');
        $onTop = $latest->answers['install_scrubbers']['on_top'] ?? null;

        $batteryDate = null;
        foreach ($sessions as $session) {
            if (!empty($session->answers['ext_battery']['replaced_bat'])) {
                $batteryDate = $session->build_date;
                break;
            }
        }

        return [
            'sensors' => $sensors,
            'scrubberLabel' => $scrubberLabel,
            'onTop' => $onTop,
            'batteryDate' => $batteryDate?->format('M j, Y'),
        ];
    }

    /**
     * The live, fillable checklist for one stage - always the template
     * version that stage is currently mapped to for the diver's unit.
     * $stage defaults to 'build', which is what CcrChecklist/New always was.
     */
    public function create($stage = 'build')
    {
        $this->authorize('ccrChecklistAccess', User::class);

        $units = $this->units();
        $unit = $this->currentUnit($units);
        $versions = $this->stageVersions($unit);
        abort_unless(isset($versions[$stage]), 404);

        $activeVersion = $versions[$stage];
        $template = $this->template($activeVersion);
        abort_unless($template, 404);

        // The diver's own steps, woven in around the built in ones.
        // ...in the diver's own order, under their own headings, if they
        // have changed either (Zach, 2026-11-11).
        $customs = $this->customsFor($unit, $stage);
        $template = CcrChecklistBuilder::build($template, $customs, $this->rowsFor($unit, $stage));
        $stageLabel = config('ccr_checklist_templates.stages')[$stage]['label'] ?? ucfirst($stage);
        if (!empty($template['own'])) {
            // Nothing to tick until they have written a step.
            if (!$template['items']) {
                return redirect()->route('CcrChecklist.customize', ['stage' => $stage, 'unit' => $unit->uuid]);
            }
            $template['label'] = $unit->name . ': ' . $stageLabel;
        }

        // Sensor install dates (step 9) carry forward from the user's last
        // saved build rather than starting blank (Pablo, 2026-10-15: "the
        // dates will be taken from the immediate past form") - only 'date'
        // type fields read this in Create.blade.php, so prefilling the
        // whole previous answers blob here is harmless for every other
        // field, which ignores it.
        // Build sessions only: this carries sensor dates forward, and a
        // pre-jump saved after the last build has none to carry.
        // Per rebreather: one unit's cell dates are not another's.
        $previousSession = $stage !== 'build' ? null : $this->sessionsFor($unit, $units)
            ->where('stage', 'build')
            ->orderByDesc('build_date')
            ->first();

        // One-time seed (Pablo, 2026-10-15: "the current installation dates
        // for the sensors are...") for before any real session exists to
        // carry forward from - never consulted again once his first actual
        // checklist is saved, since $previousSession then takes over.
        $sensorDateSeed = [
            'o2_sensor_life' => [
                'sensor1_date' => '2026-09-01',
                'sensor2_date' => '2026-09-01',
                'sensor3_date' => '2026-09-01',
                'sensor4_date' => '2026-03-01',
                'sensor5_date' => '2026-08-01',
            ],
        ];

        return view('pages.CcrChecklist.Create', [
            'templateVersion' => $activeVersion,
            'template' => $template,
            // The seed above is Pablo's own cells' install dates. Now that
            // other divers can open this form it must not hand them his
            // dates as if they were theirs: anybody else starts blank.
            'previousAnswers' => $stage !== 'build' ? [] : ($previousSession->answers
                ?? (auth()->user()->email === 'pstrika@gmail.com' ? $sensorDateSeed : [])),
            'stage' => $stage,
            'stageLabel' => $stageLabel,
            'unit' => $unit,
            'customs' => $customs,
            'scrubber' => $unit ? CcrScrubber::status($unit) : null,
            'disclaimer' => config('ccr_checklist_templates.disclaimer'),
        ]);
    }

    /** Saves a completed checklist as a new, permanent record - paper checklists aren't editable once filled, so neither is this. */
    public function store(Request $request)
    {
        $this->authorize('ccrChecklistAccess', User::class);

        $validated = $request->validate([
            'template_version' => 'required|string|max:10',
            'stage' => ['nullable', \Illuminate\Validation\Rule::in(array_keys(config('ccr_checklist_templates.stages', [])))],
            'build_date' => 'required|date',
            'answers' => 'required|array',
            'signature' => 'nullable|string',
            'disclaimer_version' => 'nullable|string|max:20',
            'disclaimer_accepted_at' => 'nullable|date',
            'client_uuid' => 'nullable|uuid',
            // The diver's own steps exactly as the form showed them, as
            // JSON. Kept on the session so the record never changes.
            'custom_items' => 'nullable|string|max:60000',
        ]);

        // A phone on a boat can send the same save twice: once into a dead
        // connection, once when the signal comes back. The browser gives
        // every opened checklist one id, so the second attempt lands on the
        // row the first one made instead of a duplicate (Zach, 2026-11-10).
        if (!empty($validated['client_uuid'])) {
            $existing = CcrChecklistSession::where('user_id', auth()->id())
                ->where('client_uuid', $validated['client_uuid'])
                ->first();
            if ($existing) {
                return redirect()->route('CcrChecklist.show', $existing->id)->with('ccrSavedUuid', $existing->client_uuid);
            }
        }

        // The template decides the stage, not the form: a version belongs
        // to exactly one stage, so a tampered `stage` field cannot file a
        // pre-jump as a build.
        $template = $this->template($validated['template_version']);
        abort_unless($template, 422);
        // ...except the 'own' version, which serves every stage and so has
        // none of its own: there the (validated) form value is the stage.
        $stage = $template['stage'] ?? (!empty($template['own']) ? ($validated['stage'] ?? 'build') : 'build');

        // Only what the builder needs, and only well formed rows: this
        // came from the browser.
        $customItems = [];
        foreach ((array) json_decode($validated['custom_items'] ?? '[]', true) as $c) {
            if (!is_array($c) || !preg_match('/^custom_\d+$/', (string) ($c['key'] ?? '')) || !is_string($c['text'] ?? null)) {
                continue;
            }
            $customItems[] = [
                'key' => $c['key'],
                'text' => mb_substr($c['text'], 0, 500),
                'after_key' => isset($c['after_key']) ? mb_substr((string) $c['after_key'], 0, 60) : null,
                'position' => (int) ($c['position'] ?? 0),
            ];
            if (count($customItems) >= 100) break;
        }

        $session = CcrChecklistSession::create([
            'user_id' => auth()->id(),
            // The unit the form was opened for (its uuid rides along as a
            // hidden field), not whichever one is current now: a second tab
            // may have switched rebreather while this checklist was open.
            'unit_id' => $this->currentUnit()?->id,
            'template_version' => $validated['template_version'],
            'stage' => $stage,
            'build_date' => $validated['build_date'],
            'answers' => $validated['answers'],
            'custom_items' => $customItems ?: null,
            // The order and headings the diver had this checklist in, read
            // here rather than sent by the browser. Kept on the session so
            // changing the order later never rewrites a saved record.
            'layout' => $this->rowsFor($this->currentUnit(), $stage),
            'signature' => $validated['signature'] ?? null,
            'disclaimer_version' => $validated['disclaimer_version'] ?? null,
            'disclaimer_accepted_at' => !empty($validated['disclaimer_accepted_at'])
                ? \Carbon\Carbon::parse($validated['disclaimer_accepted_at'])
                : null,
            'client_uuid' => $validated['client_uuid'] ?? null,
        ]);

        // Show.blade.php drops this device's saved-as-you-go copy once it
        // sees this: the draft is only removed after the server has the row.
        return redirect()->route('CcrChecklist.show', $session->id)->with('ccrSavedUuid', $session->client_uuid);
    }

    /**
     * Read-only view of one saved session - rendered against THAT
     * session's own template_version (not whatever's currently `active`
     * in config), so an old record still displays correctly against its
     * own field layout even after a newer template version becomes active.
     */
    public function show($id)
    {
        $this->authorize('ccrChecklistAccess', User::class);

        $session = CcrChecklistSession::where('user_id', auth()->id())->where('id', $id)->firstOrFail();
        // With the diver's own steps as they were on the day (the copy on
        // the row), not as they are now.
        $template = $session->mergedTemplate();

        // Build and closed check point at each other (Zach, 2026-11-10).
        // "Continue to the closed check" is only offered when the diver's
        // rebreather actually has one.
        $hasClosed = isset($this->stageVersions($session->unit)['closed']);

        return view('pages.CcrChecklist.Show', [
            'session' => $session,
            'template' => $template,
            'linkedBuild' => $session->linkedBuild(),
            'linkedClosed' => $session->linkedClosed(),
            'offerClosed' => $hasClosed && $session->awaitsClosed(),
        ]);
    }

    /**
     * One-page PDF report of a saved session (Pablo, 2026-10-17: "it
     * should create a pdf with the document. All needs to fit in 1 page. I
     * want the date, time and name of the user that created it. Also the
     * signature if available") - a real downloadable file via dompdf, not
     * the earlier browser print-dialog approach. CcrChecklist/Pdf.blade.php
     * is its own minimal-CSS view (dompdf's HTML/CSS support is a subset
     * of what the rest of this app's dh- design system uses - no flexbox,
     * no CSS custom properties), laid out as a dense table so all 16 items
     * plus header/signature fit on one Letter page without scaling tricks.
     */
    public function pdf($id)
    {
        $this->authorize('ccrChecklistAccess', User::class);

        $session = CcrChecklistSession::where('user_id', auth()->id())->where('id', $id)->with('user')->firstOrFail();
        $template = $session->mergedTemplate();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pages.CcrChecklist.Pdf', [
            'session' => $session,
            'template' => $template,
            'disclaimerStrip' => config('ccr_checklist_templates.disclaimer.strip'),
        ])->setPaper('letter', 'portrait');

        $filename = \Illuminate\Support\Str::slug($session->unit ? $session->unit->modelLabel() : 'rEvo') . '-CCR-' . \Illuminate\Support\Str::slug($session->stageLabel()) . '-' . $session->build_date->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    /* ------------------------------------------------------------------ */
    /* The diver's own steps (Zach, 2026-11-10).                           */
    /*                                                                     */
    /* Add, reword, move and remove steps of their own, on any rebreather  */
    /* and any stage. Built in steps cannot be removed, moved or reworded  */
    /* here: taking a manufacturer's step out is the one edit that makes   */
    /* the tool less safe, so it is the one edit that is not offered.      */
    /* ------------------------------------------------------------------ */

    /** [unit, stage's base template] for the customise actions, or a 404. */
    private function customizeContext(string $stage): array
    {
        $this->authorize('ccrChecklistAccess', User::class);

        $units = $this->units();
        $unit = $this->currentUnit($units);
        abort_unless($unit, 404);
        $versions = $this->stageVersions($unit);
        abort_unless(isset($versions[$stage]), 404);
        $template = $this->template($versions[$stage]);
        abort_unless($template !== null, 404);

        return [$unit, $template];
    }

    /**
     * The edit page (Zach, 2026-11-10 and 2026-11-11): the whole checklist
     * top to bottom as one list of rows. Any row can be moved. A diver's
     * own steps and the headings can also be reworded or removed; built in
     * steps cannot, and keep their original number wherever they sit.
     */
    public function customize($stage)
    {
        // Needs a rebreather to hang the steps on. Without one, send them
        // to the hub, where adding one is the first thing on the page.
        if ($this->units()->isEmpty()) {
            $this->authorize('ccrChecklistAccess', User::class);
            return redirect()->route('CcrChecklist')->withErrors(['unit' => 'Add your rebreather first, then you can make its checklists your own.']);
        }
        [$unit, $template] = $this->customizeContext($stage);

        $live = CcrCustomItem::where('unit_id', $unit->id)->where('stage', $stage)->get()->keyBy(fn ($c) => $c->answerKey());
        $customs = $this->customsFor($unit, $stage);
        $rows = $this->currentRows($unit, $stage, $template, $customs);
        $built = CcrChecklistBuilder::build($template, $customs, $rows);
        $items = array_column($built['items'], null, 'key');

        // One entry per row, in order, ready for the page.
        $list = [];
        foreach ($rows as $i => $row) {
            $ref = CcrChecklistBuilder::ref($row);
            $entry = ['ref' => $ref, 'dom' => str_replace(':', '-', $ref)];
            if ($row['t'] === 'h') {
                $next = $rows[$i + 1] ?? null;
                $entry += [
                    'type' => 'heading',
                    'text' => $row['text'],
                    'label' => $row['text'] !== '' ? $row['text'] : 'Section break',
                    // A heading with no step under it is not printed.
                    'empty' => !$next || $next['t'] === 'h',
                ];
            } else {
                $item = $items[$row['k']];
                $entry += [
                    'type' => empty($item['custom']) ? 'builtin' : 'custom',
                    'text' => $item['text'],
                    'number' => $item['number'],
                    'label' => ($item['number'] === '+' ? '' : $item['number'] . '. ') . \Illuminate\Support\Str::limit($item['text'], 46),
                    'custom' => $live[$row['k']] ?? null,
                ];
            }
            $list[] = $entry;
        }

        return view('pages.CcrChecklist.Customize', [
            'unit' => $unit,
            'stage' => $stage,
            'stageLabel' => config('ccr_checklist_templates.stages')[$stage]['label'] ?? ucfirst($stage),
            'list' => $list,
            'stepCount' => count($built['items']),
            'ownCount' => $live->count(),
            'reordered' => !empty($built['reordered']),
            'isOwn' => !empty($template['own']),
        ]);
    }

    /** The rows the diver sees now: their saved layout made safe, or the standard order. */
    private function currentRows(CcrUnit $unit, string $stage, array $template, array $customs): array
    {
        $saved = $this->rowsFor($unit, $stage);
        return $saved !== null
            ? CcrChecklistBuilder::normalize($template, $customs, $saved)
            : CcrChecklistBuilder::defaultRows($template, $customs);
    }

    /**
     * Every change to a layout goes through here: load the rows, change
     * them, make them safe again, save.
     *
     * It also writes each of the diver's own steps back onto the built in
     * step it now follows. The layout is what places a step from here on,
     * but that keeps "reset to the standard order" from throwing their
     * steps to the top, and keeps the older records' rule working.
     */
    private function changeRows(CcrUnit $unit, string $stage, array $template, callable $change, bool $forget = false): void
    {
        $customs = $this->customsFor($unit, $stage);
        $rows = $change($this->currentRows($unit, $stage, $template, $customs));
        $rows = CcrChecklistBuilder::normalize($template, $customs, $rows);

        $builtIn = array_flip(array_column($template['items'] ?? [], 'key'));
        $anchor = null;
        $place = [];
        foreach ($rows as $row) {
            if ($row['t'] !== 's') {
                continue;
            }
            if (isset($builtIn[$row['k']])) {
                $anchor = $row['k'];
            } elseif (preg_match('/^custom_(\d+)$/', $row['k'], $m)) {
                $n = $place[$anchor ?? ''] = ($place[$anchor ?? ''] ?? -1) + 1;
                CcrCustomItem::where('unit_id', $unit->id)->where('id', (int) $m[1])
                    ->update(['after_key' => $anchor, 'position' => $n]);
            }
        }

        if ($forget) {
            CcrLayout::where('unit_id', $unit->id)->where('stage', $stage)->delete();
            return;
        }
        CcrLayout::updateOrCreate(
            ['unit_id' => $unit->id, 'stage' => $stage],
            ['template_version' => $this->stageVersions($unit)[$stage] ?? null, 'rows' => $rows]
        );
    }

    private function backToCustomize(CcrUnit $unit, string $stage, ?string $ref = null)
    {
        $url = route('CcrChecklist.customize', ['stage' => $stage, 'unit' => $unit->uuid]);
        return redirect($url . ($ref ? '#row-' . str_replace(':', '-', $ref) : ''));
    }

    /**
     * Add a step or a heading. `after` names the row it should follow (a
     * step's key, or h:<id> for a heading); empty puts it at the very top.
     */
    public function storeCustom(Request $request, $stage)
    {
        [$unit, $template] = $this->customizeContext($stage);
        $kind = $request->input('kind') === 'heading' ? 'heading' : 'step';
        $validated = $request->validate([
            'text' => 'required|string|max:' . ($kind === 'heading' ? 80 : 500),
            'after' => 'nullable|string|max:60',
        ]);
        $after = $validated['after'] ?? null;
        $text = trim($validated['text']);

        if ($kind === 'heading') {
            $row = ['t' => 'h', 'id' => strtolower(\Illuminate\Support\Str::random(8)), 'text' => $text];
        } else {
            abort_if(CcrCustomItem::where('unit_id', $unit->id)->where('stage', $stage)->count() >= 60, 422, 'That is a lot of steps. Remove one before adding another.');
            $item = CcrCustomItem::create([
                'user_id' => auth()->id(),
                'unit_id' => $unit->id,
                'stage' => $stage,
                'text' => $text,
                'after_key' => null,
                'position' => 0,
            ]);
            $row = ['t' => 's', 'k' => $item->answerKey()];
        }

        $this->changeRows($unit, $stage, $template, function (array $rows) use ($row, $after) {
            // A new step is already in the rows (at its standard place):
            // take it out, then put it where it was asked for.
            $rows = array_values(array_filter($rows, fn ($r) => CcrChecklistBuilder::ref($r) !== CcrChecklistBuilder::ref($row)));
            $at = CcrChecklistBuilder::find($rows, $after);
            array_splice($rows, $at === null ? 0 : $at + 1, 0, [$row]);
            return $rows;
        });

        return $this->backToCustomize($unit, $stage, CcrChecklistBuilder::ref($row));
    }

    /**
     * Move a row, rename or remove a heading, or go back to the standard
     * order. One endpoint: each is a small form on the customise page.
     */
    public function layoutAction(Request $request, $stage)
    {
        [$unit, $template] = $this->customizeContext($stage);
        $v = $request->validate([
            'do' => 'required|in:move,rename,unhead,reset',
            'ref' => 'nullable|string|max:60',
            'dir' => 'nullable|in:up,down',
            'to' => 'nullable|string|max:60',
            'text' => 'nullable|string|max:80',
        ]);
        $ref = $v['ref'] ?? null;

        if ($v['do'] === 'reset') {
            // Their own steps stay, each after the built in step it follows
            // now. Their headings and their order go.
            $this->changeRows($unit, $stage, $template, fn (array $rows) => $rows, true);
            return $this->backToCustomize($unit, $stage);
        }

        $back = $ref;
        $this->changeRows($unit, $stage, $template, function (array $rows) use ($v, $ref, &$back) {
            $at = CcrChecklistBuilder::find($rows, $ref);
            if ($at === null) {
                return $rows;
            }
            if ($v['do'] === 'rename' && $rows[$at]['t'] === 'h') {
                $rows[$at]['text'] = trim((string) ($v['text'] ?? ''));
            } elseif ($v['do'] === 'unhead' && $rows[$at]['t'] === 'h') {
                array_splice($rows, $at, 1);
                $back = isset($rows[$at]) ? CcrChecklistBuilder::ref($rows[$at]) : null;
            } elseif ($v['do'] === 'move' && !empty($v['dir'])) {
                $swap = $v['dir'] === 'up' ? $at - 1 : $at + 1;
                if (isset($rows[$swap])) {
                    [$rows[$at], $rows[$swap]] = [$rows[$swap], $rows[$at]];
                }
            } elseif ($v['do'] === 'move' && isset($v['to'])) {
                $row = $rows[$at];
                array_splice($rows, $at, 1);
                $to = $v['to'] === 'top' ? null : CcrChecklistBuilder::find($rows, $v['to']);
                array_splice($rows, $to === null ? 0 : $to + 1, 0, [$row]);
            }
            return $rows;
        });

        return $this->backToCustomize($unit, $stage, $back);
    }

    private function ownCustom(string $uuid): CcrCustomItem
    {
        $this->authorize('ccrChecklistAccess', User::class);
        return CcrCustomItem::where('user_id', auth()->id())->where('uuid', $uuid)->with('unit')->firstOrFail();
    }

    public function updateCustom(Request $request, $uuid)
    {
        $item = $this->ownCustom($uuid);
        $item->update(['text' => trim($request->validate(['text' => 'required|string|max:500'])['text'])]);
        return $this->backToCustomize($item->unit, $item->stage, $item->answerKey());
    }

    /** Removes the step from future checklists. Saved ones keep their own copy of it. */
    public function destroyCustom($uuid)
    {
        $item = $this->ownCustom($uuid);
        $unit = $item->unit;
        $stage = $item->stage;
        $item->delete();

        // Take its row out of the saved layout too, if there is one.
        $template = $this->template($this->stageVersions($unit)[$stage] ?? '');
        if ($template !== null && $this->rowsFor($unit, $stage) !== null) {
            $this->changeRows($unit, $stage, $template, fn (array $rows) => $rows);
        }
        return $this->backToCustomize($unit, $stage);
    }

    /* ------------------------------------------------------------------ */
    /* Time on the scrubbers, and the site picker (Zach, 2026-11-11)       */
    /* ------------------------------------------------------------------ */

    /**
     * What a diver enters by hand on the hub: dive time, a corrected
     * total, a rotate, a repack. Everything else in the scrubber record is
     * read from saved checklists by App\Support\CcrScrubber.
     */
    public function scrubberAction(Request $request)
    {
        $this->authorize('ccrChecklistAccess', User::class);
        $unit = $this->currentUnit();
        abort_unless($unit, 404);

        $v = $request->validate([
            'do' => 'required|in:add,adjust,rotate,repack',
            'hours' => 'nullable|integer|min:0|max:99',
            'minutes' => 'nullable|integer|min:0|max:999',
        ]);
        $minutes = CcrScrubber::minutes($v['hours'] ?? null, $v['minutes'] ?? null);
        $back = redirect(route('CcrChecklist', ['unit' => $unit->uuid]) . '#scrubber');

        if (in_array($v['do'], ['add', 'adjust'], true)) {
            if ($minutes === null || ($v['do'] === 'add' && $minutes === 0)) {
                return $back->withErrors(['scrubber' => 'Enter the time in hours and minutes.']);
            }
        } else {
            $minutes = null;
        }

        CcrScrubberEvent::create([
            'unit_id' => $unit->id,
            'user_id' => auth()->id(),
            'type' => $v['do'],
            'minutes' => $minutes,
            'occurred_at' => now(),
        ]);
        return $back;
    }

    /** Take a hand entry back out. Entries that come from a saved checklist are not removable here. */
    public function removeScrubberEvent($id)
    {
        $this->authorize('ccrChecklistAccess', User::class);
        $event = CcrScrubberEvent::where('user_id', auth()->id())->where('id', (int) $id)->firstOrFail();
        $unit = CcrUnit::where('user_id', auth()->id())->where('id', $event->unit_id)->firstOrFail();
        $event->delete();
        return redirect(route('CcrChecklist', ['unit' => $unit->uuid]) . '#scrubber');
    }

    /**
     * Dive sites by name, for "planned depth" on the build check. Hidden
     * sites stay hidden, same rule as the rest of the app. Depths are feet.
     */
    public function sites(Request $request)
    {
        $this->authorize('ccrChecklistAccess', User::class);
        $q = trim((string) $request->input('q'));
        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }
        $sites = \App\Models\Site::where('_hidden', '<>', 1)
            ->where('name', 'LIKE', '%' . addcslashes($q, '%_\\') . '%')
            ->orderBy('name')
            ->take(8)
            ->get(['id', 'name', 'maxDepth', 'location']);

        return response()->json($sites->map(fn ($s) => [
            'id' => $s->id,
            'name' => $s->name,
            'maxDepth' => (int) $s->maxDepth ?: null,
            'location' => $s->location,
        ])->values());
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\CcrChecklistSession;
use App\Models\CcrCustomItem;
use App\Models\CcrUnit;
use App\Support\CcrChecklistBuilder;
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
        ];
    }

    /** Adds a rebreather and switches to it. A diver can have as many as they own. */
    public function storeUnit(Request $request)
    {
        $this->authorize('ccrChecklistAccess', User::class);

        $unit = CcrUnit::create($request->validate($this->unitRules()) + ['user_id' => auth()->id()]);

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
        $unit->update($request->validate($this->unitRules()));

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
            : (!empty($scrubber['checked']) ? 'Max Fresh' : '—');
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
        $customs = $this->customsFor($unit, $stage);
        $template = CcrChecklistBuilder::merge($template, $customs);
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

    /** The edit page: the whole checklist, with "add a step here" between every row. */
    public function customize($stage)
    {
        // Needs a rebreather to hang the steps on. Without one, send them
        // to the hub, where adding one is the first thing on the page.
        if ($this->units()->isEmpty()) {
            $this->authorize('ccrChecklistAccess', User::class);
            return redirect()->route('CcrChecklist')->withErrors(['unit' => 'Add your rebreather first, then you can add your own steps to its checklists.']);
        }
        [$unit, $template] = $this->customizeContext($stage);

        $live = CcrCustomItem::where('unit_id', $unit->id)->where('stage', $stage)->get()->keyBy(fn ($c) => $c->answerKey());
        $merged = CcrChecklistBuilder::merge($template, $live->map(fn ($c) => $c->snapshot())->values()->all());

        return view('pages.CcrChecklist.Customize', [
            'unit' => $unit,
            'stage' => $stage,
            'stageLabel' => config('ccr_checklist_templates.stages')[$stage]['label'] ?? ucfirst($stage),
            'template' => $merged,
            'live' => $live,
            'isOwn' => !empty($template['own']),
        ]);
    }

    /** The diver's steps that follow one built in step (or none), in order, renumbered 0..n. */
    private function siblings(CcrUnit $unit, string $stage, ?string $anchor)
    {
        $q = CcrCustomItem::where('unit_id', $unit->id)->where('stage', $stage);
        $anchor === null ? $q->whereNull('after_key') : $q->where('after_key', $anchor);
        $rows = $q->orderBy('position')->orderBy('id')->get();
        foreach ($rows as $i => $row) {
            if ((int) $row->position !== $i) {
                $row->position = $i;
                $row->save();
            }
        }
        return $rows;
    }

    private function backToCustomize(CcrUnit $unit, string $stage, ?string $rowKey = null)
    {
        $url = route('CcrChecklist.customize', ['stage' => $stage, 'unit' => $unit->uuid]);
        return redirect($url . ($rowKey ? '#row-' . $rowKey : ''));
    }

    /**
     * Add a step. `after` is the key of the row it should follow: a built
     * in step, one of the diver's own, or empty for the very top.
     */
    public function storeCustom(Request $request, $stage)
    {
        [$unit, $template] = $this->customizeContext($stage);
        $validated = $request->validate([
            'text' => 'required|string|max:500',
            'after' => 'nullable|string|max:60',
        ]);
        abort_if(CcrCustomItem::where('unit_id', $unit->id)->where('stage', $stage)->count() >= 60, 422, 'That is a lot of steps. Remove one before adding another.');

        $after = $validated['after'] ?? null;
        $builtInKeys = array_column($template['items'] ?? [], 'key');
        $anchor = null;
        $insertAt = 0;

        if ($after !== null && $after !== '' && preg_match('/^custom_(\d+)$/', $after, $m)) {
            // After one of their own: same anchor, the slot right below it.
            $prev = CcrCustomItem::where('unit_id', $unit->id)->where('stage', $stage)->where('id', (int) $m[1])->first();
            if ($prev) {
                $anchor = $prev->after_key;
                $sibs = $this->siblings($unit, $stage, $anchor);
                $insertAt = $sibs->search(fn ($r) => $r->id === $prev->id) + 1;
            }
        } elseif ($after !== null && $after !== '' && in_array($after, $builtInKeys, true)) {
            $anchor = $after; // directly under that built in step
        }

        foreach ($this->siblings($unit, $stage, $anchor) as $i => $row) {
            if ($i >= $insertAt) {
                $row->position = $i + 1;
                $row->save();
            }
        }
        $item = CcrCustomItem::create([
            'user_id' => auth()->id(),
            'unit_id' => $unit->id,
            'stage' => $stage,
            'text' => trim($validated['text']),
            'after_key' => $anchor,
            'position' => $insertAt,
        ]);

        return $this->backToCustomize($unit, $stage, $item->answerKey());
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
        return $this->backToCustomize($unit, $stage);
    }

    /**
     * Move a step one place up or down the checklist.
     *
     * Among the diver's own steps under the same built in step it is a
     * swap. At the edge of that group it crosses the built in step next to
     * it: going up it becomes the LAST of the steps under the built in
     * step above (so it sits just above the one it crossed); going down it
     * becomes the FIRST under the one it crossed.
     */
    public function moveCustom(Request $request, $uuid)
    {
        $item = $this->ownCustom($uuid);
        $dir = $request->validate(['dir' => 'required|in:up,down'])['dir'];
        $unit = $item->unit;
        $stage = $item->stage;

        $versions = $this->stageVersions($unit);
        $keys = array_column($this->template($versions[$stage] ?? '')['items'] ?? [], 'key');

        // A step whose anchor no longer exists is shown last; treat it as
        // following the last built in step so it can be moved from there.
        if ($item->after_key !== null && !in_array($item->after_key, $keys, true)) {
            $item->after_key = $keys ? end($keys) : null;
            $item->position = 100000;
            $item->save();
        }

        $sibs = $this->siblings($unit, $stage, $item->after_key);
        $idx = $sibs->search(fn ($r) => $r->id === $item->id);
        $item = $sibs[$idx];

        if ($dir === 'up' && $idx > 0) {
            $other = $sibs[$idx - 1];
            [$item->position, $other->position] = [$other->position, $item->position];
            $item->save(); $other->save();
        } elseif ($dir === 'down' && $idx < $sibs->count() - 1) {
            $other = $sibs[$idx + 1];
            [$item->position, $other->position] = [$other->position, $item->position];
            $item->save(); $other->save();
        } elseif ($dir === 'up' && $item->after_key !== null) {
            $at = array_search($item->after_key, $keys, true);
            $newAnchor = $at > 0 ? $keys[$at - 1] : null;
            $item->after_key = $newAnchor;
            $item->position = $this->siblings($unit, $stage, $newAnchor)->count(); // last
            $item->save();
        } elseif ($dir === 'down') {
            $at = $item->after_key === null ? -1 : array_search($item->after_key, $keys, true);
            if (isset($keys[$at + 1])) {
                $newAnchor = $keys[$at + 1];
                foreach ($this->siblings($unit, $stage, $newAnchor) as $i => $row) {
                    $row->position = $i + 1;
                    $row->save();
                }
                $item->after_key = $newAnchor;
                $item->position = 0; // first
                $item->save();
            }
        }

        return $this->backToCustomize($unit, $stage, $item->answerKey());
    }
}

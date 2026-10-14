<?php

namespace App\Http\Controllers;

use App\Models\CcrChecklistSession;
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

    /** Past sessions for the signed-in admin, most recent build first. */
    public function index()
    {
        $this->authorize('ccrChecklistAccess', User::class);

        $sessions = CcrChecklistSession::where('user_id', auth()->id())
            ->orderByDesc('build_date')
            ->get(['id', 'template_version', 'build_date', 'answers', 'created_at']);

        return view('pages.CcrChecklist.Index', [
            'sessions' => $sessions,
            'equipment' => $this->equipmentDashboard($sessions),
        ]);
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

    /** The live, fillable checklist - always the config's current `active` template version. */
    public function create()
    {
        $this->authorize('ccrChecklistAccess', User::class);

        $activeVersion = config('ccr_checklist_templates.active');
        $template = $this->template($activeVersion);

        // Sensor install dates (step 9) carry forward from the user's last
        // saved build rather than starting blank (Pablo, 2026-10-15: "the
        // dates will be taken from the immediate past form") - only 'date'
        // type fields read this in Create.blade.php, so prefilling the
        // whole previous answers blob here is harmless for every other
        // field, which ignores it.
        $previousSession = CcrChecklistSession::where('user_id', auth()->id())
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
            'previousAnswers' => $previousSession->answers ?? $sensorDateSeed,
        ]);
    }

    /** Saves a completed checklist as a new, permanent record - paper checklists aren't editable once filled, so neither is this. */
    public function store(Request $request)
    {
        $this->authorize('ccrChecklistAccess', User::class);

        $validated = $request->validate([
            'template_version' => 'required|string|max:10',
            'build_date' => 'required|date',
            'answers' => 'required|array',
            'signature' => 'nullable|string',
        ]);

        $session = CcrChecklistSession::create([
            'user_id' => auth()->id(),
            'template_version' => $validated['template_version'],
            'build_date' => $validated['build_date'],
            'answers' => $validated['answers'],
            'signature' => $validated['signature'] ?? null,
        ]);

        return redirect()->route('CcrChecklist.show', $session->id);
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
        $template = $this->template($session->template_version);

        return view('pages.CcrChecklist.Show', [
            'session' => $session,
            'template' => $template,
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
        $template = $this->template($session->template_version);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pages.CcrChecklist.Pdf', [
            'session' => $session,
            'template' => $template,
        ])->setPaper('letter', 'portrait');

        $filename = 'rEvo-CCR-Checklist-' . $session->build_date->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }
}

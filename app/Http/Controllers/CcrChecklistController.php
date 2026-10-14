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
        ]);
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
}

<?php

namespace App\Http\Controllers;

use App\Models\Boat;
use App\Models\Dive;
use App\Models\DiveChange;
use App\Models\Operator;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Diver logbook (Pablo, 2026-10-14): browsing and annotating the dives the
 * native uploader apps sync in (see DiveUploadController for how they get
 * here). Registered users only, own dives only - no admin override, no
 * public sharing, same posture as deco_plans.
 *
 * Phase 1 (this): list + detail view with the depth/PPO2/temp/gas-switch
 * chart, plus editable "conditions" (operator, buddies, weather, etc - see
 * `conditions` on the Dive model). Manual dive entry, logbook import, own
 * dive sites, pictures/equipment/SAC, and marine life sightings are
 * deliberately out of scope here - see docs/roadmap.md #9 for the agreed
 * future phases instead of re-deciding scope ad hoc later.
 */
class LogbookController extends Controller
{
    /** Most recent dives first, paginated. */
    public function index(Request $request)
    {
        $dives = Dive::where('user_id', $request->user()->id)
            ->orderByDesc('started_at')
            ->paginate(20);

        $stats = [
            'total' => Dive::where('user_id', $request->user()->id)->count(),
            'total_time_seconds' => (int) Dive::where('user_id', $request->user()->id)->sum('duration_seconds'),
            'max_depth_m' => Dive::where('user_id', $request->user()->id)->max('max_depth_m'),
        ];

        return view('pages.Logbook', [
            'dives' => $dives,
            'stats' => $stats,
        ]);
    }

    public function show(Request $request, $id)
    {
        $dive = Dive::where('user_id', $request->user()->id)->findOrFail($id);

        // Operator search + its boats (Pablo, 2026-10-15: "if the user
        // chooses an operator, we can show the Boat names...can type
        // anything they want", "a small logo of the operator next to the
        // input...same with the boat and its picture"), same "pass the
        // whole small catalog, filter client-side" pattern edit-site.blade.php's
        // own operator search already uses - a few hundred operators/82
        // boats is cheap to ship whole, and it's the free-text-override
        // behavior that matters, not server-side search. logoUrl/pic come
        // along so the picker can show them without a round trip.
        $operators = Operator::orderBy('operatorName')->get(['id', 'operatorName', 'logoUrl', 'slug']);
        $boats = Boat::whereNotNull('operatorId')->orderBy('name')->get(['operatorId', 'name', 'pic']);
        $boatsByOperator = $boats->groupBy('operatorId')
            ->map(fn ($group) => $group->map(fn ($b) => [
                'name' => $b->name,
                'pic' => $b->pic ? asset('assets') . $b->pic : null,
            ])->values());

        // Buddy avatars (Pablo, 2026-10-15: "if known by DH") - buddies is
        // free-text comma-separated names, not structured picks, so this
        // is a best-effort match-by-name against the user directory, done
        // here rather than N+1 queries in the blade. A name collision just
        // shows whichever matches first - no stronger identity to match on
        // without turning buddies into a real multi-select like dive_type.
        $conditions = $dive->conditions ?? [];
        $buddyNames = array_filter(array_map('trim', explode(',', $conditions['buddies'] ?? '')));
        // Two users can share a display name (confirmed: two real "Pablo
        // Strika" rows exist) - plain pluck('picture','name') would let
        // whichever row sorts last silently overwrite an earlier one's
        // picture with null. Ordering pictured rows first and deduping by
        // name keeps a real picture whenever ANY matching user has one.
        $buddyAvatars = $buddyNames
            ? User::whereIn('name', $buddyNames)
                ->orderByRaw('picture IS NULL')
                ->get(['name', 'picture'])
                ->unique('name')
                ->mapWithKeys(fn ($u) => [$u->name => $u->picture ? asset('assets') . '/img/users/' . $u->picture : null])
            : collect();

        return view('pages.DiveDetails', [
            'dive' => $dive,
            'site' => $dive->site(),
            'operators' => $operators->map(fn ($o) => [
                'id' => $o->id,
                'name' => $o->operatorName,
                'logoUrl' => $o->logoUrl ? asset('assets') . $o->logoUrl : null,
                'url' => route('OperatorDetails', ['id' => $o->slug ?? $o->id]),
            ]),
            'boatsByOperator' => $boatsByOperator,
            'buddyAvatars' => $buddyAvatars,
            // Site-tagging pencil edit (Pablo, 2026-10-15: "search within
            // our database or free type") - same client-side-filtered
            // pattern, not a new search endpoint, for the same reason.
            'sites' => Site::orderBy('name')->get(['id', 'name', 'slug']),
        ]);
    }

    /**
     * Saves diver-entered data - everything else on a dive is read-only,
     * computer-reported data. Logs a `dive_changes` row (SYNC-CONTRACT.md)
     * so phones pick up the edit through the change feed.
     *
     * Eight distinct forms post here (`form` tells them apart), one per
     * editable surface (Pablo, 2026-10-15: "add a pencil edit button per
     * card...a save button per card"): the two quick-edit chips
     * (classification, site) above the dashboard, three per-card forms
     * (operator, conditions, other), the Dive Setup card's tags line, the
     * whole "Gases used" section as one more (Pablo, 2026-10-15: "use a
     * pencil icon to edit the whole Gases used section...show button
     * save") - gas_tank_choice carries the tank-with-AI pick, the bottom/
     * deco/travel (or bailout/deco) role, the delete checkboxes, AND the
     * per-tank cylinder type/twins all in one submit, since the whole
     * section now has a single pencil/Save instead of each row saving on
     * its own - and gas_row_order, the diver's own drag-to-reorder of
     * those same rows (Pablo: "put the rows in Kanban so the user can
     * sort them...just for visuals"), saved separately and immediately on
     * drop rather than gated behind that same pencil/Save, since it's
     * display order only, never a fact about the dive. Each form only
     * ever sends its own fields - MERGING into the existing conditions
     * rather than a wholesale replace is what keeps one card's save from
     * wiping out another's fields. `dive_type` is the one field the
     * 'other' form is allowed to clear entirely (an absent key there
     * means "every checkbox was unchecked", not "this form doesn't know
     * about dive_type").
     */
    public function updateConditions(Request $request, $id)
    {
        $dive = Dive::where('user_id', $request->user()->id)->findOrFail($id);

        $data = $request->validate([
            'form' => 'required|in:classification,site,operator,conditions,other,tags,gas_tank_choice,gas_row_order',
            'notes' => 'nullable|string|max:5000',
            'site_id' => 'nullable|integer',
            'conditions' => 'nullable|array',
            'conditions.operator_name' => 'nullable|string|max:150',
            'conditions.divemaster' => 'nullable|string|max:150',
            'conditions.boat_name' => 'nullable|string|max:150',
            'conditions.boat_captain' => 'nullable|string|max:150',
            'conditions.operator_logo' => 'nullable|string|max:500',
            'conditions.operator_url' => 'nullable|string|max:500',
            'conditions.boat_pic' => 'nullable|string|max:500',
            'conditions.current' => 'nullable|string|max:50',
            'conditions.current_direction' => 'nullable|in:N,NE,E,SE,S,SW,W,NW',
            'conditions.weather' => 'nullable|string|max:150',
            'conditions.surface_conditions' => 'nullable|string|max:50',
            // Meters, metric-native storage, regardless of which unit the
            // slider/input displayed - same convention as every other
            // measurement this app stores (Pablo, 2026-10-15: "a slider
            // for visibility and a text input, same we use in the deco
            // planner...make sure we use metric or imperial depending on
            // what's set" - a display concern, not a storage one).
            'conditions.visibility_m' => 'nullable|numeric|min:0|max:100',
            'conditions.buddies' => 'nullable|string|max:300',
            'conditions.dive_type' => 'nullable|array',
            'conditions.dive_type.*' => 'in:drift,training,photo_video,cave,cavern,wall,reef,wreck',
            'conditions.classification' => 'nullable|in:recreational,technical',
            // Free-typed site name when nothing in our catalog matches
            // (Pablo, 2026-10-15: "search within our database or free
            // type") - site_id above covers the catalog-match case.
            'conditions.site_name_custom' => 'nullable|string|max:150',
            'tags' => 'nullable|array|max:5',
            'tags.*' => 'string|max:40',
            // Tank type/twins per tank index (Pablo, 2026-10-15: "add one
            // row per tank... select the tank configuration they used").
            // Type is validated as a plain string, not an `in:` list of
            // every DH_TANK_TYPES key - that catalog lives client-side
            // only (dh-tanks.js, mirroring DecoPlanner's own frontend-only
            // tank state), so duplicating its keys here would just be a
            // second copy to keep in sync. An unrecognized type is
            // harmless: the chart/RMV math simply can't look it up and
            // skips that tank, same as no type being set at all.
            'conditions.tank_config' => 'nullable|array',
            'conditions.tank_config.*.type' => 'nullable|string|max:40',
            'conditions.tank_config.*.twins' => 'nullable|boolean',
            // Tank size logged for a gas with no AI-tracked tank to attach
            // it to (Pablo, 2026-10-15: "even if there's no more AI info
            // available for another tank, allow the user to select the
            // tank size. We won't be able to calculate the RMV, but at
            // least he can log what size tank he used") - keyed by GAS
            // index (there's no tank index to key tank_config off of in
            // this case), display-only, never fed into the RMV math.
            'conditions.gas_tank_size_choice' => 'nullable|array',
            'conditions.gas_tank_size_choice.*.type' => 'nullable|string|max:40',
            'conditions.gas_tank_size_choice.*.twins' => 'nullable|boolean',
            // Gas<->tank pairing overrides, keyed by GAS index (Pablo,
            // 2026-10-15: "I want the gases in each row, not the tank.
            // Then we can associate the tanks to the gases") - a whole
            // section-level edit now (Pablo: "use a pencil icon to edit
            // the whole Gases used section...show button save"), so this
            // one form submission also carries gas_role_choice (bottom/
            // deco/travel, or bailout/deco for CCR) and tank_config
            // (type/twins), not just the tank pick - GasTankMatcher::
            // suggest() computes a default fresh from $dive on every
            // render, these are only the diver's CONFIRMED picks.
            'conditions.gas_tank_choice' => 'nullable|array',
            'conditions.gas_tank_choice.*' => 'nullable|integer',
            'conditions.gas_role_choice' => 'nullable|array',
            'conditions.gas_role_choice.*' => 'nullable|in:bottom,deco,travel,bailout',
            'conditions.deleted_gas_mixes' => 'nullable|array',
            'conditions.deleted_gas_mixes.*' => 'integer',
            // Diver's own drag-to-reorder of the Gases used rows (Pablo,
            // 2026-10-15: "I will also put the rows in Kanban so the user
            // can sort them in any particular order - just for visuals")
            // - a row's KEY ('diluent'/'oxygen' for CCR's two fixed rows,
            // the gas index otherwise), not an array index, so a row that
            // later disappears (deleted, or the dive gets re-synced with
            // a different gas list) doesn't shift every other saved
            // position. Purely a display order - never touches which
            // tank/role/gas is matched to what.
            'conditions.gas_row_order' => 'nullable|array',
            'conditions.gas_row_order.*' => 'string|max:20',
        ]);

        $conditions = $dive->conditions ?? [];

        switch ($data['form']) {
            case 'classification':
                $conditions['classification'] = $data['conditions']['classification'] ?? null;
                break;
            case 'site':
                $dive->site_id = $data['site_id'] ?? null;
                // A real catalog pick always wins over and clears any
                // earlier free-typed name - a dive has one site, not both.
                $conditions['site_name_custom'] = $dive->site_id ? null : ($data['conditions']['site_name_custom'] ?? null);
                break;
            case 'operator':
                // operator_logo/boat_pic (Pablo, 2026-10-15: "a small logo
                // of the operator next to the input...same with the boat
                // and its picture") - the JS fills these hidden fields in
                // when a real catalog match is picked, so the image
                // persists across reloads without a live lookup.
                foreach (['operator_name', 'divemaster', 'boat_name', 'boat_captain', 'operator_logo', 'operator_url', 'boat_pic'] as $field) {
                    $conditions[$field] = $data['conditions'][$field] ?? null;
                }
                break;
            case 'conditions':
                foreach (['current', 'current_direction', 'weather', 'surface_conditions', 'visibility_m'] as $field) {
                    $conditions[$field] = $data['conditions'][$field] ?? null;
                }
                break;
            case 'other':
                $dive->notes = $data['notes'] ?? null;
                $conditions['buddies'] = $data['conditions']['buddies'] ?? null;
                $conditions['dive_type'] = $data['conditions']['dive_type'] ?? [];
                break;
            case 'tags':
                $dive->tags = $data['tags'] ?? [];
                break;
            case 'gas_tank_choice':
                // Mutual exclusivity (Pablo, 2026-10-15: "if a tank was
                // already assigned to a gas, then it cannot be assigned
                // to another gas") - the picker also disables an
                // already-taken tank client-side, but this is the real
                // enforcement: first gas (by index) to claim a tank wins,
                // any later claim on the same tank drops back to
                // unmatched rather than silently double-booking it.
                $seenTanks = [];
                $dedupedChoice = [];
                foreach (($data['conditions']['gas_tank_choice'] ?? []) as $gi => $ti) {
                    if ($ti !== null && !in_array($ti, $seenTanks, true)) {
                        $seenTanks[] = $ti;
                        $dedupedChoice[$gi] = $ti;
                    } else {
                        $dedupedChoice[$gi] = null;
                    }
                }
                $conditions['gas_tank_choice'] = $dedupedChoice;
                $conditions['gas_role_choice'] = $data['conditions']['gas_role_choice'] ?? [];
                $conditions['deleted_gas_mixes'] = $data['conditions']['deleted_gas_mixes'] ?? [];
                if (array_key_exists('tank_config', $data['conditions'] ?? [])) {
                    $conditions['tank_config'] = $data['conditions']['tank_config'];
                }
                if (array_key_exists('gas_tank_size_choice', $data['conditions'] ?? [])) {
                    $conditions['gas_tank_size_choice'] = $data['conditions']['gas_tank_size_choice'];
                }
                break;
            case 'gas_row_order':
                $conditions['gas_row_order'] = $data['conditions']['gas_row_order'] ?? [];
                break;
        }

        $dive->conditions = array_filter($conditions, fn ($v) => $v !== null && $v !== '' && $v !== []);
        $dive->save();

        DiveChange::record($dive->user_id, $dive->uuid, 'updated');

        return redirect()->route('DiveDetails', $dive->id);
    }

    /**
     * @-mention search for the Buddies field (Pablo, 2026-10-15: "if the
     * user types @ we can search the user list"). Unlike the operator/
     * site/boat catalogs (a few hundred rows, safe to ship whole and
     * filter client-side), the full user directory is personal data at
     * real scale - a live, rate-limited-by-debounce search endpoint
     * instead, same shape as GroupController::searchSites but with no
     * group-membership scope (a dive buddy isn't necessarily in a shared
     * group). Name only, never email - avoids turning this into an email
     * enumeration tool. Excludes guests and the searching user themself.
     */
    public function searchUsers(Request $request)
    {
        $q = trim((string) $request->input('q'));
        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $users = User::where('name', 'LIKE', "%$q%")
            ->where('id', '!=', $request->user()->id)
            ->where('role_id', '!=', 4) // guests - User::isGuest()'s own check
            ->orderBy('name')
            ->take(10)
            ->get(['id', 'name']);

        return response()->json($users);
    }
}

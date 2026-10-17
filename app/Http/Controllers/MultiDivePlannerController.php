<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\User;

/**
 * Multi Dive Planner (Pablo, 2026-10-13): up to 4 separate dives with a
 * surface interval between each, as opposed to the Deco Planner's "Multi
 * Level" tab (multiple depth/time segments within ONE dive). Fully wired
 * to the real MultiDivePlanner backend (decoplanningapi.azurewebsites.net)
 * as of 2026-10-14 - deliberately no CC/Trimix anywhere in this planner,
 * every dive is Open Circuit, gas is picked by PPO2 alone.
 *
 * Registered members only, no guest accounts (Pablo, 2026-10-16; was
 * platform admins only from 2026-10-15 for the initial rollout). 'auth'
 * middleware on the routes sends signed-out visitors to sign in; every
 * action below also refuses guest accounts.
 *
 * Same imperial/metric/site-id URL pattern as DecoPlanner (NDLController),
 * for the same reason: one plain canonical URL regardless of units, see
 * multiDivePlannerSeo()'s own comment.
 */
class MultiDivePlannerController extends Controller
{
    /**
     * This planner's own depth slider caps at 130ft (Pablo, 2026-10-13:
     * "Max depth is always 130ft"), so a site deeper than that can never
     * actually be dived here - filtered out at the source instead of merely
     * discouraged client-side, same "hard cap, not a warning" standard as
     * the O2%/PPO2 clamp below.
     */
    private function allSites()
    {
        return Site::where('_hidden', '<>', 1)
            ->where('maxDepth', '<=', 130)
            ->select('id', 'name', 'maxDepth', 'type', 'location', 'level')
            ->get()
            ->sortBy('name')
            ->values();
    }

    private function multiDivePlannerSeo(): array
    {
        return [
            'title' => 'Free Multi Dive Planner | Divers Hub',
            'desc' => 'Plan a full day of diving for free: up to 4 dives with surface intervals between them, depth, bottom time and nitrox gas for each - built for real dive planning, not a toy calculator.',
            'keywords' => 'multi dive planner, repetitive dive planner, surface interval calculator, two tank dive planner, nitrox dive planner, scuba dive planner florida',
            'canonical' => route('MultiDivePlanner'),
        ];
    }

    /**
     * "Normal" default - 70/85, typical modern recreational dive computer
     * conservatism (Pablo, 2026-10-14: "default to Conservativism Normal
     * and use 70/85"). Deliberately NOT the diver's own saved DecoPlanner
     * preference (that's a technical/staged-decompression setting) and NOT
     * 100/100 either - GF 100/100 matches PADI's own first-dive NDL almost
     * exactly (confirmed: 20min at 100ft/air) but runs noticeably more
     * generous than PADI on repetitive dives (confirmed: 35min vs PADI's
     * 22min at 70ft after a 43min interval), since PADI's own tables are
     * deliberately conservative on residual-nitrogen bookkeeping in a way
     * no single fixed GF reproduces everywhere. 100/100 is still available
     * as an explicit "Conservatism: Low" opt-in via the page's own gear
     * modal (multi-dive-planner.js), not the silent default.
     */
    private function gradientFactors(): array
    {
        return ['low' => 70, 'high' => 85];
    }

    public function show($id = null)
    {
        abort_unless(auth()->user()->isNotGuest(), 403);

        $deco_unit = auth()->user()->deco_unit ?? 0;
        $currentSite = $id ? Site::findOrFail((int) $id) : null;

        return view('pages.MultiDivePlanner', [
            'currentSite' => $currentSite,
            'allSites' => $this->allSites(),
            'deco_unit' => $deco_unit,
            'gradientFactors' => $this->gradientFactors(),
            'SEO' => $this->multiDivePlannerSeo(),
        ]);
    }

    public function showImperial($id = null)
    {
        abort_unless(auth()->user()->isNotGuest(), 403);

        $currentSite = $id ? Site::findOrFail((int) $id) : null;

        return view('pages.MultiDivePlanner', [
            'currentSite' => $currentSite,
            'allSites' => $this->allSites(),
            'deco_unit' => 0,
            'gradientFactors' => $this->gradientFactors(),
            'SEO' => $this->multiDivePlannerSeo(),
        ]);
    }

    public function showMetric($id = null)
    {
        abort_unless(auth()->user()->isNotGuest(), 403);

        $currentSite = $id ? Site::findOrFail((int) $id) : null;

        return view('pages.MultiDivePlanner', [
            'currentSite' => $currentSite,
            'allSites' => $this->allSites(),
            'deco_unit' => 1,
            'gradientFactors' => $this->gradientFactors(),
            'SEO' => $this->multiDivePlannerSeo(),
        ]);
    }
}

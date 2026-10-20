@php
    $conditions = $dive->conditions ?? [];
    $isCcr = $dive->isCcr();
    $classification = $dive->classification();
    // Fallback for the Gases used block below when this dive has no
    // tanks[] at all (Pablo, 2026-10-15: "dive #60...the gas is not
    // shown. It was an Air dive" - confirmed 25 of this diver's own dives
    // have real gas_mixes but an empty tanks[], so the per-tank rows
    // built from $tankLabels/$tanks below had nothing to iterate over and
    // silently showed nothing). gas_mixes doesn't need a real tank to
    // exist, so this is read regardless of $tanks.
    $labeledGases = $dive->labeledGasMixes();
    $seq = $dive->logbookNumber();
    // Catalog site / the diver's own site / free-typed name (Dive::siteInfo).
    $siteInfo = $dive->siteInfo();
    $site = $siteInfo['site'] ?? null;
    $siteName = $siteInfo['name'] ?? null;

    // Live catalog match for the Operator view row (Pablo, 2026-10-15: "if
    // operator is known by DH, you can link to OperatorDetails page" - then
    // "the operator Link is not shown or the logo"). operator_logo/
    // operator_url are only ever written into `conditions` at PICK time, so
    // relying on them alone meant every dive saved before that field
    // existed (or before a resave) never got a link - matching the stored
    // operator_name against $operators fresh on every view fixes that
    // retroactively, for any dive whose name still matches the catalog,
    // without needing every diver to re-save. Falls back to the stored
    // logo/url for a free-typed name that no longer resolves.
    $matchedOperator = $operators->firstWhere('name', $conditions['operator_name'] ?? null);

    // Tank chart-toggle labels (Pablo, 2026-10-15: "we should be able to
    // identify the Tank and the gas content"; 2026-10-15 follow-up:
    // "build some intelligence behind this"). CC still resolves the
    // "clean" way - "diluent"/"oxygen" usage tags vs. the diluent
    // gas_mix's own usage===2 tag - confirmed reliable across this
    // diver's dives, oxygen tanks are definitionally a pure O2 supply fed
    // to the loop (no gas_mix entry exists for them at all, by design).
    // OC has no such tag (libdivecomputer never links tanks[] to
    // gas_mixes[] structurally - confirmed against the actual Shearwater
    // parser source), so GasTankMatcher::suggest() infers a pairing from
    // per-tank pressure-drop timing; a diver's own confirmed pick in
    // conditions.gas_tank_map always wins over that suggestion.
    $diluentMix = collect($dive->gas_mixes ?? [])->firstWhere('usage', 2);
    // Tanks from another computer on this dive when the main one had none
    // with pressure (LogbookController::computerLogs).
    $tanks = ($chartTanks ?? null) ?: ($dive->tanks ?? []);
    $gasMatch = \App\Support\GasTankMatcher::suggest($dive);
    $deletedGasMixes = $conditions['deleted_gas_mixes'] ?? [];
    // conditions.gas_tank_choice is keyed by GAS index (Pablo, 2026-10-15:
    // "I want the gases in each row, not the tank. Then we can associate
    // the tanks to the gases") - the Gases used card below reads it
    // directly; this chart-toggle labeling still needs it the other way
    // around (given a TANK, which gas), so it's inverted once here rather
    // than redone inline on every tank.
    $gasTankChoice = $conditions['gas_tank_choice'] ?? [];
    $tankGasOverride = [];
    foreach ($gasTankChoice as $gi => $ti) {
        if ($ti !== null) {
            $tankGasOverride[$ti] = $gi;
        }
    }
    // Chip wording (Pablo, 2026-10-15: "Diluent Air instead of Diluent
    // 21/0...any gas that is x/0 just drop the /0 and do %") - o2/he only
    // when there's helium, "Air" for 21/0, "32%" otherwise.
    $chipMix = function (array $mix): string {
        $o2 = (int) round($mix['oxygen'] ?? 0);
        $he = (int) round($mix['helium'] ?? 0);
        if ($he > 0) {
            return $o2 . '/' . $he;
        }
        return $o2 === 21 ? 'Air' : $o2 . '%';
    };
    $tankLabels = collect($tanks)->map(function ($tank, $i) use ($diluentMix, $tanks, $dive, $gasMatch, $tankGasOverride, $chipMix) {
        $usage = $tank['usage'] ?? null;
        if ($usage === 'diluent' && $diluentMix) {
            return 'Diluent ' . $chipMix($diluentMix);
        }
        if ($usage === 'oxygen') {
            return 'Oxygen 100%';
        }
        if (count($tanks) === 1 && count($dive->gas_mixes ?? []) === 1) {
            return 'Tank ' . ($i + 1) . ' ' . $chipMix($dive->gas_mixes[0]);
        }
        // OC, ambiguous - a confirmed pick always wins over the suggestion.
        $resolvedGasIdx = array_key_exists($i, $tankGasOverride) ? $tankGasOverride[$i] : ($gasMatch['tankToGas'][$i] ?? null);
        if ($resolvedGasIdx === null && $usage === 'sidemount') {
            // The other sidemount cylinder: same bottom gas as its pair.
            $resolvedGasIdx = array_search('bottom', $gasMatch['roles'] ?? [], true);
            if ($resolvedGasIdx === false) $resolvedGasIdx = null;
        }
        if ($resolvedGasIdx !== null && isset($dive->gas_mixes[$resolvedGasIdx])) {
            return 'Tank ' . ($i + 1) . ' ' . $chipMix($dive->gas_mixes[$resolvedGasIdx]);
        }
        return 'Tank ' . ($i + 1);
    });

    $diveTypeOptions = [
        'drift' => 'Drift', 'training' => 'Training', 'photo_video' => 'Photo/Video',
        'cave' => 'Cave', 'cavern' => 'Cavern', 'wall' => 'Wall', 'reef' => 'Reef', 'wreck' => 'Wreck',
    ];
    $selectedDiveTypes = $conditions['dive_type'] ?? [];

    // Tank type catalog for the per-tank rows (Pablo, 2026-10-15: "use all
    // the tanks that we have configured in the deco planner gas
    // consumption section") - keys must match dh-tanks.js's DH_TANK_TYPES
    // exactly (the JS catalog those keys are looked up against for water
    // volume), same "no backend source of truth to import from, so this
    // is a faithful copy" situation as that file. Plain key=>key labels,
    // same as DecoPlanner's own tank-type <select> - the nominal volume is
    // shown separately next to the dropdown, not folded into the option
    // text.
    $tankTypeOptions = [
        'AL-13' => 'AL-13 (2L)', 'AL-19' => 'AL-19 (3L)', 'AL-30' => 'AL-30', 'AL-40' => 'AL-40',
        'AL-72' => 'AL-72', 'AL-80' => 'AL-80', 'ST-HP-2L' => 'ST-HP-2L', 'ST-HP-3L' => 'ST-HP-3L',
        'ST-LP-50' => 'ST-LP-50', 'ST-LP-85' => 'ST-LP-85', 'ST-HP-100' => 'ST-HP-100',
        'Carbon Fiber 6.8L' => 'Carbon Fiber 6.8L', 'Carbon Fiber 12L' => 'Carbon Fiber 12L',
    ];
    // The 2-3 L bottles (CCR onboard / stage size) render with the CCR
    // template in the Gases card (Pablo, 2026-10-15).
    $ccrTemplateTankTypes = ['AL-13', 'AL-19', 'ST-HP-2L', 'ST-HP-3L'];
    $tankConfig = $conditions['tank_config'] ?? [];
    // Fallback tank-size storage for a gas with no AI-tracked tank to
    // attach it to (Pablo, 2026-10-15: "even if there's no more AI info
    // available for another tank, allow the user to select the tank
    // size...at least he can log what size tank he used") - keyed by GAS
    // index, since there's no tank index to key $tankConfig off of here.
    $gasTankSizeChoice = $conditions['gas_tank_size_choice'] ?? [];

    // Gas-primary rows for "Gases used" (Pablo, 2026-10-15: "I want the
    // gases in each row, not the tank. Then we can associate the tanks to
    // the gases...for the gases use the split pills"). CCR's diluent/
    // oxygen are "fixed" - already resolved via the clean usage-tag
    // match, no tank-association picker needed (GasTankMatcher's own
    // docblock: "CC already works"). Every other configured gas - OC
    // always, and a CCR diver's own separate bailout/deco gas if they're
    // carrying one with its own AI-tracked cylinder - gets a row with a
    // tank picker, pre-filled from $gasMatch/conditions.gas_tank_choice.
    $gasRows = [];
    $fixedTankIndices = [];

    if ($isCcr) {
        $diluentTankIdx = null;
        foreach ($tanks as $ti => $t) {
            if (($t['usage'] ?? null) === 'diluent') {
                $diluentTankIdx = $ti;
                break;
            }
        }
        // Diluent and oxygen rows exist on EVERY CC dive, whether or not
        // the computer has air integration for them (Pablo, 2026-10-15:
        // "Oxygen tank is ALWAYS present on a CC dive - even if the data
        // from the computer does not show that"; a Petrel on a rEvo
        // reports no tanks[] at all). tankIndex stays null without AI -
        // the row still gets a size picker, stored under its rowKey.
        if ($diluentMix) {
            $gasRows[] = ['mix' => $diluentMix, 'gasIndex' => null, 'tankIndex' => $diluentTankIdx, 'tankIndices' => $diluentTankIdx !== null ? [$diluentTankIdx] : [], 'pair' => false, 'role' => 'Diluent', 'fixed' => true, 'deleted' => false, 'rowKey' => 'diluent'];
            if ($diluentTankIdx !== null) $fixedTankIndices[] = $diluentTankIdx;
        }

        $oxygenTankIdx = null;
        foreach ($tanks as $ti => $t) {
            if (($t['usage'] ?? null) === 'oxygen') {
                $oxygenTankIdx = $ti;
                break;
            }
        }
        // Synthesized pill - pure O2 fed to the loop has no gas_mixes
        // entry of its own, by design (same rule $tankLabels uses).
        $gasRows[] = ['mix' => ['oxygen' => 100, 'helium' => 0], 'gasIndex' => null, 'tankIndex' => $oxygenTankIdx, 'tankIndices' => $oxygenTankIdx !== null ? [$oxygenTankIdx] : [], 'pair' => false, 'role' => 'Oxygen', 'fixed' => true, 'deleted' => false, 'rowKey' => 'oxygen'];
        if ($oxygenTankIdx !== null) $fixedTankIndices[] = $oxygenTankIdx;
    }

    // Role per gas (Pablo, 2026-10-15: "below the gas pill...a small
    // dropdown to select if the gas is bottom gas, deco or travel in
    // open circuit...for CC, the dropdown will show bailout and deco") -
    // a confirmed pick always wins over GasTankMatcher's own inferred
    // classification, same pattern as the tank association itself.
    $gasRoleChoice = $conditions['gas_role_choice'] ?? [];
    $roleOptions = \App\Support\GasTankMatcher::roleOptions($isCcr);

    foreach ($dive->gas_mixes ?? [] as $gi => $mix) {
        if (($mix['usage'] ?? null) === 2) {
            continue; // the diluent already has its fixed row above
        }
        $deleted = in_array($gi, $deletedGasMixes);
        $resolvedTankIdx = array_key_exists($gi, $gasTankChoice) ? $gasTankChoice[$gi] : ($gasMatch['gasToTank'][$gi] ?? null);
        $resolvedRole = array_key_exists($gi, $gasRoleChoice) ? $gasRoleChoice[$gi] : ($gasMatch['roles'][$gi] ?? null);
        // A sidemount pair is two AI cylinders on one gas (Pablo,
        // 2026-10-15): the matcher hands both back, the row carries them
        // both and no tank picker is offered for it.
        $pair = ($gasMatch['gasToTanks'][$gi] ?? []);
        $isPair = count($pair) > 1;
        $gasRows[] = [
            'mix' => $mix,
            'gasIndex' => $gi,
            'tankIndex' => $deleted ? null : ($isPair ? $pair[0] : $resolvedTankIdx),
            'tankIndices' => $deleted ? [] : ($isPair ? array_values($pair) : ($resolvedTankIdx !== null ? [$resolvedTankIdx] : [])),
            'pair' => $isPair,
            'role' => $resolvedRole,
            'fixed' => false,
            'deleted' => $deleted,
            'rowKey' => (string) $gi,
        ];
    }

    // Diver's own drag-to-reorder (Pablo, 2026-10-15: "put the rows in
    // Kanban so the user can sort them in any particular order - just for
    // visuals") - sorted by saved position; a row with no saved position
    // (never dragged, or new since the last drag) keeps its natural order
    // and sorts after everything that HAS been positioned, rather than
    // jumping to the front.
    $gasRowOrder = $conditions['gas_row_order'] ?? [];
    if (count($gasRowOrder)) {
        $orderIndex = array_flip($gasRowOrder);
        usort($gasRows, function ($a, $b) use ($orderIndex) {
            $posA = $orderIndex[$a['rowKey']] ?? PHP_INT_MAX;
            $posB = $orderIndex[$b['rowKey']] ?? PHP_INT_MAX;
            return $posA <=> $posB;
        });
    }

    // Chart color per tank, by the role of the gas it carries (see
    // App\Support\DiveChartPalette) - read from the same rows the Gases
    // card shows, so a role change there recolors the chart too.
    $tankRoles = [];
    foreach ($tanks as $ti => $t) {
        $tankRoles[$ti] = null;
    }
    foreach ($gasRows as $r) {
        if ($r['tankIndex'] !== null && !$r['deleted'] && array_key_exists($r['tankIndex'], $tankRoles)) {
            $tankRoles[$r['tankIndex']] = $r['role'];
        }
    }
    // Sidemount cylinders carry the bottom gas between them - yellow,
    // even the one no gas row is matched to (one gas, two cylinders).
    foreach ($tanks as $ti => $t) {
        if (($t['usage'] ?? null) === 'sidemount' && !in_array($tankRoles[$ti], ['deco', 'travel', 'bailout'], true)) {
            $tankRoles[$ti] = 'bottom';
        }
    }
    $tankColors = \App\Support\DiveChartPalette::forTanks($tankRoles);
    $chartColors = [
        'depth' => \App\Support\DiveChartPalette::DEPTH,
        'temperature' => \App\Support\DiveChartPalette::TEMPERATURE,
        'po2' => \App\Support\DiveChartPalette::PPO2,
        'ndl' => \App\Support\DiveChartPalette::NDL,
        'tanks' => (object) $tankColors,
    ];

    // A sidemount pair is spoken for - its two cylinders never show in
    // another gas's "Tank with AI?" picker.
    foreach ($gasRows as $r) {
        if ($r['pair']) $fixedTankIndices = array_merge($fixedTankIndices, $r['tankIndices']);
    }
    $gasTankOptions = [];
    foreach ($tanks as $ti => $t) {
        if (!in_array($ti, $fixedTankIndices, true)) {
            $gasTankOptions[$ti] = 'Tank ' . ($ti + 1);
        }
    }

    // Computer/deco-algorithm/GFs - a small blue legend above the Tags
    // row now (Pablo, 2026-10-15: "above the tag row, aligned to the
    // right, put a small legend in blue with the dive computer and the
    // deco algorithm and GFs (remove it from the Gases card title)"),
    // not folded into any card's title. Still none of this is editable -
    // same "never editable, this came from the computer" posture as the
    // OC/CC chip - just identifying context, now living in its own
    // cardless row instead.
    $diveComputerLegendParts = [];
    if ($dive->computer_model) {
        $diveComputerLegendParts[] = $dive->computer_model;
    }
    if ($dive->deco_model) {
        $diveComputerLegendParts[] = ucfirst($dive->deco_model['type'] ?? 'unknown');
        if (($dive->deco_model['gfLow'] ?? null) !== null && ($dive->deco_model['gfHigh'] ?? null) !== null) {
            $diveComputerLegendParts[] = 'GF ' . $dive->deco_model['gfLow'] . '/' . $dive->deco_model['gfHigh'];
        }
    }
    // Setpoints Low/High on a rebreather dive (Pablo, 2026-10-16), from the
    // samples' own setpoint values.
    if ($isCcr) {
        $sps = collect($dive->profile ?? [])->pluck('setpoint')->filter(fn ($v) => $v !== null && $v > 0)->map(fn ($v) => round($v, 1))->unique()->sort()->values();
        if ($sps->count() === 1) $diveComputerLegendParts[] = 'SP ' . number_format($sps[0], 1);
        elseif ($sps->count() > 1) $diveComputerLegendParts[] = 'SP ' . number_format($sps->first(), 1) . '/' . number_format($sps->last(), 1);
    }
    $diveComputerLegend = implode(' · ', $diveComputerLegendParts);
@endphp
<x-page-template bodyClass='dh-shell dh-logbook'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-shell.header
            :title="'Dive #' . $seq . ' on ' . $dive->started_at->format('M j, Y') . ($siteName ? ' to ' . $siteName : '')"
            icon="menu_book" :h1="true" />

        <div class="container-fluid py-0">
            <div class="row">
                <div class="col-lg-9 col-12 m-auto pt-4">

                    {{-- One button, "Switch to {opposite unit}" - the exact DecoPlanner
                         convention (Pablo, 2026-10-15), toggled client-side; always
                         alone on its own line, top right (Pablo, 2026-10-17). --}}
                    <div class="dh-unit-row"><button type="button" class="dh-btn dh-btn-ghost-dark dh-unit-toggle" id="switchUnits"></button></div>
                    <div class="d-flex justify-content-between align-items-start">
                        <a href="{{ route('Logbook') }}" class="dh-btn dh-btn-ghost-dark">
                            <span class="material-icons-round" aria-hidden="true">arrow_back</span>
                            Logbook
                        </a>
                        <div class="dh-dive-header-actions">
                            <div class="dh-dive-header-tools">
                                {{-- Round icon button (Pablo, 2026-10-15: "like
                                     we have in other parts of the site, same
                                     height as the button switch to Metric") -
                                     .dh-btn-icon is normally white-on-dark (it
                                     lives in dark card headers elsewhere); the
                                     .dh-dive-header-tools scope in divershub.css
                                     recolors it to match the light
                                     dh-btn-ghost-dark button right next to it. --}}
                                {{-- One-page PDF of this dive (Pablo, 2026-10-16),
                                     in whichever units the page is showing. --}}
                                <a href="{{ route('DiveDetails.print', $dive->id) }}" class="dh-btn-icon" id="divePrintBtn" title="Print dive log (PDF)" target="_blank" rel="noopener" data-print-base="{{ route('DiveDetails.print', $dive->id) }}">
                                    <span class="material-icons-round" aria-hidden="true">print</span>
                                </a>
                                @if ($dive->factsEditable())
                                    <a href="{{ route('DiveDetails.edit', $dive->id) }}" class="dh-btn-icon" title="Edit depth, duration, gases...">
                                        <span class="material-icons-round" aria-hidden="true">tune</span>
                                    </a>
                                @endif
                                {{-- Delete (Pablo, 2026-10-16). Soft delete + change
                                     feed (Dive::deleteAndLog) - the computer's copy
                                     can't re-create it on the next sync. --}}
                                <form method="POST" action="{{ route('DiveDetails.destroy', $dive->id) }}" class="d-inline"
                                      onsubmit="return confirm('Delete this dive from your logbook?\n\nIt will disappear from the app too, and syncing your computer again will not bring it back.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dh-btn-icon dh-btn-icon-danger" title="Delete dive">
                                        <span class="material-icons-round" aria-hidden="true">delete</span>
                                    </button>
                                </form>
                            </div>

                            {{-- Single chip (Pablo, 2026-10-15: "a single chip <icon>
                                 <Open/Closed> Circuit <Technical/Recreational>...can
                                 only change Technical or Recreational", "right below
                                 the print button aligned right"). Icon follows
                                 Dive::iconFile()'s own rule (ccr.svg for CC regardless
                                 of classification; rec.svg/tec.svg for OC by
                                 classification) - same method the Logbook list already
                                 calls, not a separate hardcoded rule here. The OC/CC
                                 text is computer-reported and immutable; only the
                                 classification half has a quick-edit path, via its own
                                 `form=classification` submission so it can't clobber
                                 another card's fields. --}}
                            <span class="dh-dive-mode-chip" id="diveModeChip">
                                <x-svg-icon :file="$dive->iconFile()" />
                                {{ $dive->modeLabel() }}
                                <span id="diveClassChipText">{{ ucfirst($classification) }}</span>
                                <button type="button" id="diveClassEditBtn" aria-label="Edit classification">
                                    <span class="material-icons-round" aria-hidden="true">edit</span>
                                </button>
                            </span>
                            <form method="POST" action="{{ route('DiveDetails.updateConditions', $dive->id) }}" id="diveClassForm" class="dh-dive-class-form" hidden>
                                @csrf
                                <input type="hidden" name="form" value="classification">
                                @if ($dive->modeEditable())
                                    {{-- Not from a computer sync, so OC/CC is the diver's call. --}}
                                    <select name="dive_mode" onchange="this.form.submit()" aria-label="Dive mode">
                                        <option value="open_circuit" @selected(!$isCcr)>Open Circuit</option>
                                        <option value="closed_circuit" @selected($isCcr)>Closed Circuit</option>
                                    </select>
                                @endif
                                <select name="conditions[classification]" onchange="this.form.submit()">
                                    {{-- Breath-hold dives pick snorkeling/freediving instead (Pablo, 2026-10-19). --}}
                                    @foreach ($dive->classificationOptions() as $opt)
                                        <option value="{{ $opt }}" @selected($classification === $opt)>{{ ucfirst($opt) }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                    </div>

                    {{-- #<number> - <site name> chip (Pablo, 2026-10-15), also
                         where the site-tagging pencil edit lives - editing the
                         page's own <h1> title inline isn't practical, so this
                         chip is the one editable surface for it; the title
                         itself just re-renders from the same data on save.
                         Full width of the column, #<number> larger than the
                         site name next to it. --}}
                    <div class="dh-dive-site-chip-row">
                        <span class="dh-dive-site-chip" id="diveSiteChip">
                            <span class="dh-dive-site-chip-num">#{{ $seq }}</span>
                            <span id="diveSiteChipText">
                                @if ($siteName)
                                    -
                                    @if ($siteInfo['url'])
                                        <a href="{{ $siteInfo['url'] }}">{{ $siteName }}</a>
                                    @else
                                        {{ $siteName }}
                                    @endif
                                    @if ($siteInfo['type'])
                                        <x-site-type-icon :type="$siteInfo['type']" size="34" />
                                    @endif
                                    @if ($siteInfo['custom'] && $siteInfo['user_site'])
                                        <span class="dh-dive-site-chip-own" title="One of your own sites">yours</span>
                                    @endif
                                @else
                                    - Add dive site
                                @endif
                            </span>
                            <button type="button" id="diveSiteEditBtn" aria-label="Edit dive site">
                                <span class="material-icons-round" aria-hidden="true">edit</span>
                            </button>
                        </span>
                        <form method="POST" action="{{ route('DiveDetails.updateConditions', $dive->id) }}" id="diveSiteForm" class="dh-dive-site-form" hidden>
                            @csrf
                            <input type="hidden" name="form" value="site">
                            <input type="hidden" name="site_id" id="diveSiteIdInput" value="{{ $dive->site_id }}">
                            <input type="hidden" name="user_site_id" id="diveUserSiteIdInput" value="{{ $dive->user_site_id }}">
                            <div style="position: relative;">
                                <input type="text" id="diveSiteSearch" name="conditions[site_name_custom]" value="{{ $siteName }}" placeholder="Search our sites, your sites, or type a name" autocomplete="off">
                                <div id="diveSiteResults" class="dh-search-list dh-related-site-results" hidden></div>
                            </div>
                            <button type="submit" class="dh-btn dh-btn-primary dh-dive-site-save">Save</button>
                            {{-- A site that's in neither catalog: make it one of the
                                 diver's own, tagged on this dive right away
                                 (Pablo, 2026-10-16: custom user sites). --}}
                            <a href="{{ route('Logbook.sites.create', ['dive' => $dive->id]) }}" class="dh-btn dh-btn-ghost-dark dh-dive-site-create" id="diveSiteCreateLink" title="Create one of your own sites and tag this dive with it">
                                <span class="material-icons-round" aria-hidden="true">add_location_alt</span>
                                New site
                            </a>
                        </form>
                    </div>

                    @if (session('status'))
                        <div class="dh-logbook-flash" role="status">
                            <span class="material-icons-round" aria-hidden="true">check_circle</span>
                            {{ session('status') }}
                        </div>
                    @endif
                    {{-- A synced dive that looks like another computer's dive but
                         whose clock was hours off: asked, never assumed (Pablo,
                         2026-10-16: "we can always ask the user"). --}}
                    @foreach (($sameDive ?? []) as $sd)
                        @php $od = $sd['dive']; @endphp
                        <div class="dh-same-dive-banner" role="status">
                            <span class="material-icons-round" aria-hidden="true">devices</span>
                            <span class="dh-same-dive-text">
                                <strong>Same dive as <a href="{{ route('DiveDetails', $od->id) }}">#{{ $sd['label'] }}</a>?</strong>
                                Logged by the {{ $od->computer_model ?: 'other computer' }} &middot;
                                {{ intdiv((int) $od->duration_seconds, 60) }} min, <span data-unit-value="depth" data-metric="{{ $od->max_depth_m }}"></span>
                                @if ($sd['corr'] !== null) &middot; profiles {{ (int) round(max(0, $sd['corr']) * 100) }}% alike @endif
                                @if (abs($sd['offset']) > 300) <span class="dh-import-clock">clocks {{ \App\Support\SameDiveMatcher::offsetLabel($sd['offset']) }} apart</span> @endif
                            </span>
                            <span class="dh-same-dive-actions">
                                <form method="POST" action="{{ route('Logbook.combine', [$dive->id, $od->id]) }}">@csrf<button type="submit" class="dh-btn dh-btn-primary">Combine, keep {{ $dive->computer_model ?: 'this' }} as main</button></form>
                                <form method="POST" action="{{ route('Logbook.combine', [$od->id, $dive->id]) }}">@csrf<button type="submit" class="dh-btn dh-btn-ghost-dark">Keep {{ $od->computer_model ?: 'the other' }} as main</button></form>
                                <form method="POST" action="{{ route('Logbook.dismissPair', [$dive->id, $od->id]) }}">@csrf<input type="hidden" name="back" value="dive"><button type="submit" class="dh-btn dh-btn-ghost-dark">Different dives</button></form>
                            </span>
                        </div>
                    @endforeach
                    @if (session('error'))
                        <div class="dh-logbook-flash" role="alert" style="background: rgba(211,47,47,.08); color: var(--dh-danger);">
                            <span class="material-icons-round" aria-hidden="true">error</span>
                            {{ session('error') }}
                        </div>
                    @endif

                    {{-- Surface-break halves (Pablo, 2026-10-16: "the diver
                         comes to the surface, the computer considers it the
                         end of the dive and then goes back again") - offer to
                         fold the next dive into this one. DiveMerger decides
                         what counts as "next". --}}
                    @if ($mergeCandidate)
                        @php $mergeGap = \App\Support\DiveMerger::gapSeconds($dive, $mergeCandidate); @endphp
                        <div class="dh-dive-merge-banner">
                            <span>
                                <span class="material-icons-round" aria-hidden="true">call_merge</span>
                                Dive #{{ $mergeCandidate->sequenceNumber() }} started {{ $mergeGap < 60 ? 'right' : intdiv($mergeGap, 60) . ' min' }} after this one ended
                                ({{ $mergeCandidate->started_at->format('g:i A') }}, {{ gmdate('H:i', $mergeCandidate->duration_seconds) }}). Same dive?
                            </span>
                            <form method="POST" action="{{ route('DiveDetails.merge', $dive->id) }}" onsubmit="return confirm('Merge dive #{{ $mergeCandidate->sequenceNumber() }} into this one? The surface interval is kept as part of the dive and the second entry is removed.');">
                                @csrf
                                <input type="hidden" name="with_id" value="{{ $mergeCandidate->id }}">
                                <button type="submit" class="dh-btn dh-btn-ghost-dark">Merge into this dive</button>
                            </form>
                        </div>
                    @endif

                    {{-- Dark header dashboard (Pablo, 2026-10-15) - dive number
                         moved to the title/chip above instead of its own tile. --}}
                    <div class="dh-dive-header-stats">
                        <div class="dh-dive-header-stat">
                            <span class="material-icons-round" aria-hidden="true">vertical_align_bottom</span>
                            <div class="dh-dive-header-stat-value" data-unit-value="depth" data-metric="{{ $dive->max_depth_m }}"></div>
                            <div class="dh-dive-header-stat-label">Max depth</div>
                        </div>
                        @if ($dive->avg_depth_m)
                            <div class="dh-dive-header-stat">
                                <span class="material-icons-round" aria-hidden="true">height</span>
                                <div class="dh-dive-header-stat-value" data-unit-value="depth" data-metric="{{ $dive->avg_depth_m }}"></div>
                                <div class="dh-dive-header-stat-label">Avg depth</div>
                            </div>
                        @endif
                        <div class="dh-dive-header-stat">
                            <span class="material-icons-round" aria-hidden="true">schedule</span>
                            <div class="dh-dive-header-stat-value">{{ gmdate('H:i', $dive->duration_seconds) }}</div>
                            <div class="dh-dive-header-stat-label">Duration</div>
                        </div>
                        @if ($dive->min_water_temp_c)
                            <div class="dh-dive-header-stat">
                                <span class="material-icons-round" aria-hidden="true">device_thermostat</span>
                                <div class="dh-dive-header-stat-value" data-unit-value="temp" data-metric="{{ $dive->min_water_temp_c }}"></div>
                                <div class="dh-dive-header-stat-label">Min water temp</div>
                            </div>
                        @endif
                        <div class="dh-dive-header-stat">
                            <span class="material-icons-round" aria-hidden="true">wb_sunny</span>
                            <div class="dh-dive-header-stat-value">{{ $dive->started_at->format('g:i A') }}</div>
                            <div class="dh-dive-header-stat-label">Start time</div>
                        </div>
                    </div>

                    {{-- Cardless row (Pablo, 2026-10-15: "put a no card row in
                         between the dashboard and the Dive Setup Card") - the
                         computer/deco-algorithm/GFs legend (small, blue,
                         right-aligned, moved out of the Gases card's own
                         title) above Tags, its own small save, not gated
                         behind a pencil like the bigger cards - low-stakes,
                         quick-access data. --}}
                    {{-- Every computer worn on this dive (Pablo, 2026-10-16: the "dedup
                         suite"): the main one plus attached logs, each with Make main /
                         Separate. Pressure from a transmitter on another computer is
                         used on the chart when the main computer had none. --}}
                    @if (!empty($companions))
                        <div class="dh-dive-computers">
                            <span class="dh-dive-computers-label">Computers</span>
                            <span class="dh-computer-chip is-main" title="Main computer: its profile is the dive">{{ $dive->computer_model ?: 'Main computer' }} &middot; main</span>
                            @foreach ($companions as $c)
                                <span class="dh-computer-chip">
                                    <span class="dh-computer-swatch" style="--swatch: {{ $c['color'] }};" aria-hidden="true"></span>
                                    {{ $c['label'] }}
                                    <details>
                                        <summary aria-label="Options for {{ $c['label'] }}"><span class="material-icons-round" style="font-size: 16px;" aria-hidden="true">more_vert</span></summary>
                                        <div class="dh-computer-menu">
                                            <form method="POST" action="{{ route('Logbook.computerMain', [$dive->id, $c['id']]) }}">@csrf<button type="submit">Make {{ $c['label'] }} the main computer</button></form>
                                            <form method="POST" action="{{ route('Logbook.computerDetach', [$dive->id, $c['id']]) }}" onsubmit="return confirm('Make the {{ $c['label'] }} log a separate dive?');">@csrf<button type="submit">Not this dive - separate it</button></form>
                                        </div>
                                    </details>
                                </span>
                            @endforeach
                            @if (!empty($pressureFrom))
                                <span class="dh-computer-note">Tank pressure from the {{ $pressureFrom }}</span>
                            @endif
                            @if (!empty($decoFrom))
                                <span class="dh-computer-note">Deco stops from the {{ $decoFrom }}</span>
                            @endif
                        </div>
                    @endif

                    <div class="dh-dive-legend-row">
                        @if ($diveComputerLegend)
                            <div class="dh-dive-computer-legend">{{ $diveComputerLegend }}</div>
                        @endif
                        <form method="POST" action="{{ route('DiveDetails.updateConditions', $dive->id) }}" id="diveTagsForm">
                            @csrf
                            <input type="hidden" name="form" value="tags">
                            <div class="dh-dive-tags-row">
                                <label class="dh-dive-view-label" for="diveTagInput">Tags <span class="text-xs">(up to 5)</span></label>
                                <div class="dh-dive-tag-chips" id="diveTagChips">
                                    @foreach (($dive->tags ?? []) as $tag)
                                        <span class="dh-dive-tag-chip">
                                            {{ $tag }}
                                            <input type="hidden" name="tags[]" value="{{ $tag }}">
                                            <button type="button" aria-label="Remove tag">&times;</button>
                                        </span>
                                    @endforeach
                                </div>
                                <input type="text" id="diveTagInput" placeholder="Add a tag, press Enter" maxlength="40">
                            </div>
                        </form>
                    </div>

                    {{-- Gases (Pablo, 2026-10-15: "now the Card setup will be
                         called 'Gases'") - all computer-reported and
                         read-only EXCEPT the tank/role/delete picks below,
                         same "never editable, this came from the computer"
                         posture as the OC/CC chip for everything else. --}}
                    @php
                        // Same single-tank icon as the Recreational calendar's
                        // own drawer link and DecoPlanner's Gas Consumption
                        // header (Pablo, 2026-10-15: "not the single tank icon
                        // we use in the drawer for recreational calendar") -
                        // not a generic Material icon.
                        $gasesCardTankSvg = \App\Support\IconSvg::themed('assets/img/icons/icons_calendar_rec.svg');
                    @endphp
                    <div class="card p-0 position-relative mt-3 z-index-2 mb-4">
                        {{-- Collapsed on load (Pablo, 2026-10-15) - at a row per gas
                             it's the tallest card on the page and sits above the
                             profile chart. --}}
                        <div class="dh-calc-inputs-head" data-collapsible-toggle role="button" tabindex="0" aria-expanded="false" aria-controls="diveGasesBody">
                            @if ($gasesCardTankSvg)
                                <span class="dh-deco-section-head-icon-svg" aria-hidden="true">{!! $gasesCardTankSvg !!}</span>
                            @else
                                <span class="material-icons-round" aria-hidden="true">settings_suggest</span>
                            @endif
                            <h3>Gases</h3>
                            @if (count($gasRows))
                                <button type="button" class="dh-dive-card-edit-btn" data-gases-edit-btn aria-label="Edit Gases">
                                    <span class="material-icons-round" aria-hidden="true">edit</span>
                                </button>
                            @endif
                            <span class="material-icons-round dh-calc-inputs-chevron" aria-hidden="true">expand_more</span>
                        </div>
                        <div class="card-body" id="diveGasesBody" hidden>
                            {{-- Gases used, one row per GAS, laid out as a
                                 4-column grid (Pablo, 2026-10-15: "each gas
                                 row is col-12 as follows: col-2 for gas
                                 (and gas type below)...col-3 for tank with
                                 AI?...col-3 for tank size (and twin flag
                                 below)...col-4 for the total consumption
                                 and Avg RMV"):
                                   col-2 - the split pill + role (Diluent/
                                     Oxygen for CCR's fixed rows; Bottom
                                     gas/Deco/Travel or Bailout/Deco,
                                     editable, for everything else).
                                   col-3 - which physical tank has the AI
                                     transmitter (blank for CCR's fixed
                                     rows - nothing ambiguous to ask).
                                   col-3 - that tank's cylinder size, twins
                                     just below it (needed for RMV).
                                   col-4 - total consumption + average RMV,
                                     always read-only.
                                 All three pickers (role/tank/size) are
                                 pre-filled from GasTankMatcher's
                                 suggestion or the diver's confirmed pick.

                                 One pencil for the WHOLE section (Pablo:
                                 "use a pencil Icon to edit the whole Gases
                                 used section...show button save") rather
                                 than per-row - every row's picker is
                                 hidden until that pencil is clicked, one
                                 Save submits everything at once (the
                                 server also re-enforces "a tank can't be
                                 assigned to two gases" - see
                                 LogbookController@updateConditions - the
                                 client-side disabling in
                                 dive-details-form.js is just the live
                                 feedback, not the only guard).

                                 $tanks can be empty with real gas_mixes
                                 still present (a computer with no air
                                 integration - e.g. a Petrel on a rEvo).
                                 The rows still render with a size picker
                                 (Pablo, 2026-10-15: "even if Air
                                 integration info is not available, I would
                                 like to be able to select the tank size for
                                 the record"); only the Air Integr? column
                                 says "No AI data" and consumption stays
                                 blank, since there are no pressures. --}}
                            <div class="dh-dive-view-row" style="display: block;">
                                @if (count($gasRows))
                                    <form method="POST" action="{{ route('DiveDetails.updateConditions', $dive->id) }}" id="diveGasForm">
                                        @csrf
                                        <input type="hidden" name="form" value="gas_tank_choice">
                                        <div class="dh-dive-gas-rows-head row d-none d-md-flex" aria-hidden="true">
                                            <div class="col-md-3 dh-dive-gas-col-gas">Gas Content</div>
                                            <div class="col-md-2 dh-dive-gas-col-tank">Air Integr?</div>
                                            <div class="col-md-4 dh-dive-gas-col-size">Tank Size</div>
                                            <div class="col-md-3 dh-dive-gas-col-computed">Consumption</div>
                                        </div>
                                        <div class="dh-dive-gas-rows" id="diveGasRows">
                                            @foreach ($gasRows as $row)
                                                @php
                                                    $roleLabel = $row['role'] ?? null ? ($roleOptions[$row['role']] ?? ucfirst($row['role'])) : null;
                                                    // Tank-indexed config wins once a tank is resolved (it's
                                                    // what the chart/RMV math reads); otherwise fall back to
                                                    // the gas-keyed, display-only size.
                                                    // CCR's fixed rows have no gas index - their AI-less
                                                    // size is keyed by rowKey ('diluent'/'oxygen').
                                                    $sizeKey = $row['gasIndex'] ?? $row['rowKey'];
                                                    $sizeSource = $row['tankIndex'] !== null ? ($tankConfig[$row['tankIndex']] ?? null) : ($gasTankSizeChoice[$sizeKey] ?? null);
                                                    $sizeType = $sizeSource['type'] ?? null;
                                                    $sizeTwins = $sizeSource['twins'] ?? false;
                                                    $sizeText = $sizeSource
                                                        ? trim(($sizeType !== null ? ($tankTypeOptions[$sizeType] ?? $sizeType) : 'Tank size not set') . ($sizeTwins ? ' (Twins)' : ''))
                                                        : null;
                                                    // A sidemount pair takes ONE size (both cylinders) -
                                                    // the server copies it onto every sidemount tank.
                                                    $sizeFieldName = $row['pair']
                                                        ? 'conditions[sidemount_size]'
                                                        : ($row['tankIndex'] !== null
                                                            ? 'conditions[tank_config][' . $row['tankIndex'] . ']'
                                                            : 'conditions[gas_tank_size_choice][' . $sizeKey . ']');
                                                    // Which template image to show (Pablo, 2026-10-15:
                                                    // "if the user selected twin, show the twin template.
                                                    // AL-19, AL-13, ST-HP-2L and ST-HP-3L should be
                                                    // showing the tank ccr template. All other tanks show
                                                    // the single tank template. In CC, by default the
                                                    // diluent and oxygen show it with tank_ccr.png
                                                    // template. User may change that then follow the
                                                    // rules above") - twins always wins regardless of
                                                    // type; the 2-3 L bottles get the CCR look; a fixed
                                                    // CCR row with nothing picked yet defaults to
                                                    // tank_ccr.png rather than showing nothing.
                                                    if ($sizeTwins || $row['pair']) {
                                                        // Twins, or a sidemount pair - two cylinders either way.
                                                        $tankImage = 'tank_double.png';
                                                    } elseif (in_array($sizeType, $ccrTemplateTankTypes, true)) {
                                                        $tankImage = 'tank_ccr.png';
                                                    } elseif ($sizeType !== null) {
                                                        $tankImage = 'tank_single.png';
                                                    } elseif ($row['fixed']) {
                                                        $tankImage = 'tank_ccr.png';
                                                    } else {
                                                        $tankImage = 'tank_single.png';
                                                    }
                                                    // The template is shown only once a size is actually
                                                    // known - either picked, or (CC's fixed rows) the
                                                    // tank_ccr.png default above - never for a row with
                                                    // nothing to show at all (Pablo: "shown ONLY if the
                                                    // user selected a tank size").
                                                    $showTankMini = !$row['deleted'] && ($sizeType !== null || $row['fixed']);
                                                @endphp
                                                <div class="dh-dive-gas-row row @if($row['deleted']) is-deleted @endif" data-gas-index="{{ $row['gasIndex'] ?? '' }}" data-row-key="{{ $row['rowKey'] }}" draggable="true">
                                                    <span class="dh-dive-gas-drag-handle material-icons-round" aria-hidden="true" title="Drag to reorder">drag_indicator</span>
                                                    {{-- col-2: the gas itself - pill + role, role editable
                                                         below it in edit mode. --}}
                                                    <div class="col-12 col-md-3 dh-dive-gas-col-gas">
                                                        <x-gas-mix-pill :o2="$row['mix']['oxygen'] ?? null" :he="$row['mix']['helium'] ?? null" />
                                                        <span class="dh-dive-gas-role-text" data-view>{{ $roleLabel }}</span>
                                                        @if (!$row['fixed'])
                                                            <div data-edit hidden>
                                                                <x-dh-select
                                                                    id="gasRoleChoice{{ $loop->index }}"
                                                                    name="conditions[gas_role_choice][{{ $row['gasIndex'] }}]"
                                                                    :options="$roleOptions"
                                                                    :selected="$row['role']"
                                                                    placeholder="Role"
                                                                    :disabled="false"
                                                                />
                                                                <label class="dh-dive-gas-notused-toggle">
                                                                    <input type="checkbox" name="conditions[deleted_gas_mixes][]" value="{{ $row['gasIndex'] }}" @checked($row['deleted'])>
                                                                    Not used this dive
                                                                </label>
                                                            </div>
                                                        @endif
                                                    </div>

                                                    {{-- col-2: which physical tank has the AI transmitter -
                                                         nothing to ask for a fixed CCR row (diluent/oxygen
                                                         are already unambiguous). --}}
                                                    <div class="col-12 col-md-2 dh-dive-gas-col-tank">
                                                        @if ($row['pair'])
                                                            {{-- Sidemount: both cylinders, no picker. --}}
                                                            <span class="dh-dive-tank-row-view-text">Tank {{ implode(' + Tank ', array_map(fn ($t) => $t + 1, $row['tankIndices'])) }}</span>
                                                        @elseif ($row['tankIndex'] === null && ($row['fixed'] || !count($gasTankOptions)))
                                                            {{-- Nothing to match: the computer reported no
                                                                 air-integrated tank for this gas. --}}
                                                            <span class="dh-dive-no-ai">No AI data</span>
                                                        @elseif (!$row['fixed'])
                                                            <span class="dh-dive-tank-row-view-text" data-view>
                                                                @if ($row['deleted'])
                                                                    &mdash;
                                                                @elseif ($row['tankIndex'] !== null)
                                                                    Tank {{ $row['tankIndex'] + 1 }}
                                                                @else
                                                                    Not matched
                                                                @endif
                                                            </span>
                                                            <div data-edit hidden>
                                                                <x-dh-select
                                                                    id="gasTankChoice{{ $loop->index }}"
                                                                    name="conditions[gas_tank_choice][{{ $row['gasIndex'] }}]"
                                                                    :options="$gasTankOptions"
                                                                    :selected="$row['tankIndex']"
                                                                    placeholder="Tank with AI?"
                                                                    :disabled="false"
                                                                />
                                                            </div>
                                                        @else
                                                            {{-- Matched automatically via the computer's own
                                                                 diluent/oxygen usage tag - no name comes with
                                                                 the tank, so this mirrors that tag (Pablo,
                                                                 2026-10-15: "just put Tank D or Tank O"). --}}
                                                            <span class="dh-dive-tank-row-view-text">Tank {{ $row['rowKey'] === 'oxygen' ? 'O' : 'D' }}</span>
                                                        @endif
                                                    </div>

                                                    {{-- col-5: a small tank-with-fill graphic (desktop only,
                                                         hidden while editing - Pablo, 2026-10-15: "add the
                                                         template of the tanks...make it small so we don't
                                                         disrupt the format. While in edit mode, you just hide
                                                         the tank. And you don't show it on mobile phone
                                                         format"), that tank's cylinder size + twins (now
                                                         always pickable, even with no AI-tracked tank - Pablo:
                                                         "allow the user to select the tank size. We won't be
                                                         able to calculate the RMV, but at least he can log
                                                         what size tank he used" - $sizeFieldName above already
                                                         points this at tank_config or the gas-keyed fallback
                                                         depending on whether a tank is resolved), and start/
                                                         end pressure pills below it. --}}
                                                    <div class="col-12 col-md-4 dh-dive-gas-col-size">
                                                        @if ($showTankMini)
                                                            <div class="dh-dive-tank-mini is-{{ substr($tankImage, 5, -4) }}" data-view data-mobile-hide id="tankMini{{ $loop->index }}">
                                                                <div class="dh-dive-tank-mini-chart-wrap">
                                                                    <canvas id="tankMiniChart{{ $loop->index }}" data-o2="{{ round($row['mix']['oxygen'] ?? 0) }}" data-he="{{ round($row['mix']['helium'] ?? 0) }}" data-twins="{{ $tankImage === 'tank_double.png' ? 1 : 0 }}"></canvas>
                                                                </div>
                                                                <img src="{{ asset('assets') }}/img/{{ $tankImage }}" alt="" class="dh-dive-tank-mini-img">
                                                            </div>
                                                        @endif
                                                        <span class="dh-dive-tank-row-view-text" data-view>{{ $sizeText }}</span>
                                                        @if ($row['tankIndex'] !== null && !$row['deleted'])
                                                            {{-- One start/end pair per cylinder - two for sidemount. --}}
                                                            @foreach ($row['tankIndices'] as $ti)
                                                                <div class="dh-dive-tank-pressure-pills" data-view>
                                                                    @if ($row['pair'])<span class="dh-dive-tank-pressure-tag">T{{ $ti + 1 }}</span>@endif
                                                                    <span class="dh-gas-result-pill is-compact is-grey" id="tankStartPressure{{ $loop->parent->index }}_{{ $ti }}">&nbsp;</span>
                                                                    <span class="dh-gas-result-pill is-compact is-grey" id="tankEndPressure{{ $loop->parent->index }}_{{ $ti }}">&nbsp;</span>
                                                                </div>
                                                            @endforeach
                                                        @endif
                                                        <div data-edit hidden>
                                                            <div class="dh-dive-tank-size-wrap" data-tank-size-wrap data-tank-index="{{ $row['tankIndex'] ?? '' }}" data-tank-indices="{{ implode(',', $row['tankIndices']) }}">
                                                                <x-dh-select
                                                                    id="tankSizeRow{{ $loop->index }}"
                                                                    name="{{ $sizeFieldName }}[type]"
                                                                    :options="$tankTypeOptions"
                                                                    :selected="$sizeSource['type'] ?? null"
                                                                    placeholder="Tank size"
                                                                    :disabled="false"
                                                                />
                                                                <label class="dh-dive-tank-twins">
                                                                    <input type="checkbox" data-tank-twins-input name="{{ $sizeFieldName }}[twins]" value="1" @checked($sizeSource['twins'] ?? false)>
                                                                    Twins
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- col-3: total consumption + average RMV as pills
                                                         (Pablo, 2026-10-15: "grey pill for '155.0 cuft'...blue
                                                         pill (white font) for '0.33 cuft/min'. Add in front of
                                                         this pill Avg RMV in same blue pill color font"),
                                                         read-only either way - only meaningful with a real
                                                         AI-tracked tank, so this stays blank for the
                                                         AI-less-size-logged case above. --}}
                                                    <div class="col-12 col-md-3 dh-dive-gas-col-computed">
                                                        @if ($row['deleted'])
                                                            <span class="dh-dive-gas-notused">Not used this dive</span>
                                                        @elseif ($row['tankIndex'] !== null && $sizeType !== null)
                                                            <span class="dh-gas-result-pill is-compact is-grey" id="tankVolumeView{{ $loop->index }}">&nbsp;</span>
                                                            <span class="dh-dive-gas-rmv-row">
                                                                <span class="dh-dive-gas-rmv-label">Avg RMV</span>
                                                                <span class="dh-gas-result-pill is-compact" id="tankAvgRmvView{{ $loop->index }}">&nbsp;</span>
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <button type="submit" class="dh-btn dh-btn-primary mt-3" data-gases-save hidden>Save</button>
                                        <button type="button" class="dh-btn dh-btn-ghost-dark mt-3" data-gases-cancel hidden>Cancel</button>
                                    </form>
                                @else
                                    <span class="dh-dive-view-value">&mdash;</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Depth / PPO2 / temp / gas-switch / tank-pressure chart
                         (Pablo, 2026-10-14: "very powerful interface...the user
                         should select what to show"). Toggle chips drive dataset
                         visibility in dive-details.js; a chip removes itself if
                         this dive's profile has no data for that series at all.
                         Tank chips (Pablo, 2026-10-15: "semi-transparent, if
                         clicked show pressure on the chart vs time") are built
                         here since the label needs the tanks[] usage mapping,
                         but toggle the same way as the other series chips. --}}
                    @if ($dive->hasProfile())
                    <div class="dh-dive-chart-card mb-4">
                        <div class="dh-chart-toggles">
                            <button type="button" class="dh-chart-toggle is-active" data-series="depth" style="--toggle-color: {{ \App\Support\DiveChartPalette::DEPTH }};">Depth</button>
                            <button type="button" class="dh-chart-toggle" data-series="temperature" style="--toggle-color: {{ \App\Support\DiveChartPalette::TEMPERATURE }};">Temperature</button>
                            <button type="button" class="dh-chart-toggle" data-series="po2" style="--toggle-color: {{ \App\Support\DiveChartPalette::PPO2 }};">PPO2</button>
                            <button type="button" class="dh-chart-toggle" data-series="ndl" style="--toggle-color: {{ \App\Support\DiveChartPalette::NDL }};">NDL</button>
                            @foreach (($companions ?? []) as $ci => $c)
                                <button type="button" class="dh-chart-toggle is-active" data-series="computer{{ $ci }}" style="--toggle-color: {{ $c['color'] }};">{{ $c['label'] }}</button>
                            @endforeach
                            @foreach ($tankLabels as $i => $label)
                                <button type="button" class="dh-chart-toggle dh-chart-toggle-tank" data-series="tank{{ $i }}" style="--toggle-color: {{ $tankColors[$i]['pressure'] }};">{{ $label }}</button>
                                {{-- Instant consumption rate (Pablo, 2026-10-15: "can you
                                     calculate instant SAC using the pressure and the
                                     depth"). Genuinely NOT true SAC in L/min - tanks[].volume
                                     is always 0 on this driver (Shearwater doesn't report
                                     it), and SAC needs volume to convert bar to liters. This
                                     is the surface-normalized pressure consumption rate
                                     (bar/min at 1 ATM) between consecutive samples instead -
                                     labeled honestly as that, not oversold as SAC. --}}
                                {{-- No SAC (nor RMV, see dive-details.js) for a CCR
                                     diluent/oxygen supply (Pablo, 2026-10-15: "makes no
                                     sense...just show the chip for the pressure") - the
                                     loop meters those, not the diver's breathing. --}}
                                @if (!in_array($tanks[$i]['usage'] ?? null, ['diluent', 'oxygen'], true))
                                    <button type="button" class="dh-chart-toggle dh-chart-toggle-tank" data-series="sac{{ $i }}" style="--toggle-color: {{ $tankColors[$i]['sac'] }};">{{ $label }} SAC</button>
                                @endif
                            @endforeach
                        </div>
                        <div class="dh-dive-chart-canvas-wrap">
                            <canvas id="diveProfileChart"></canvas>
                        </div>
                    </div>
                    @endif

                    {{-- Photos (Pablo, 2026-10-17): up to 20, private to the diver;
                         each can be offered to the site's public page, which goes
                         through the normal photo review. dive-photos.js. --}}
                    <div class="card p-0 position-relative mt-3 z-index-2 mb-4" id="divePhotosCard">
                        <div class="dh-calc-inputs-head" data-collapsible-toggle role="button" tabindex="0" aria-expanded="true" aria-controls="divePhotosBody">
                            <span class="material-icons-round" aria-hidden="true">photo_library</span>
                            <h3>Photos <span class="dh-dive-photos-count" id="divePhotosCount">{{ count($photos) }}/{{ \App\Models\DivePhoto::MAX_PER_DIVE }}</span></h3>
                            <span class="material-icons-round dh-calc-inputs-chevron" aria-hidden="true">expand_less</span>
                        </div>
                        <div class="card-body" id="divePhotosBody">
                            <div class="dh-dive-photos-grid" id="divePhotosGrid"></div>
                            <p class="text-sm text-secondary mb-2" id="divePhotosEmpty" @if(count($photos)) hidden @endif>
                                No photos yet. They stay private to you{{ $dive->site_id ? ' - you can choose to share any of them on the site page' : '' }}.
                            </p>
                            <label class="dh-btn dh-btn-ghost-dark mb-0" id="divePhotosAdd">
                                <span class="material-icons-round" aria-hidden="true">add_a_photo</span>
                                Add photos
                                <input type="file" accept="image/jpeg,image/png,image/webp" multiple hidden id="divePhotosInput">
                            </label>
                            <span class="text-sm text-secondary ms-2" id="divePhotosStatus" role="status"></span>
                        </div>
                    </div>

                    {{-- Dive Operator (Pablo, 2026-10-15, renamed from "Dive
                         Logistics"): who ran the trip. View state is plain
                         text (Pablo: "go back to spans or labels, no inputs"),
                         the pencil reveals the real form - input style matches
                         the rEvo CCR checklist's own floating-label fields
                         (input-group input-group-outline), not the plain
                         bordered .dh-field look used elsewhere on this page.
                         Field order (Operator, Boat Name, Boat Captain,
                         Divemaster) matches what Pablo wants on mobile.
                         Operator is a search-as-you-type field over our own
                         catalog, free text still accepted; picking a real
                         operator shows its logo and fills in that operator's
                         boat names (with their own pictures) as quick picks
                         for Boat name below - still freely overridable. --}}
                    <div class="card p-0 position-relative mt-3 z-index-2 mb-4" id="diveOperatorCard">
                        <div class="dh-calc-inputs-head" data-collapsible-toggle role="button" tabindex="0" aria-expanded="true" aria-controls="diveOperatorBody">
                            <span class="material-icons-round" aria-hidden="true">directions_boat</span>
                            <h3>Dive Operator</h3>
                            <button type="button" class="dh-dive-card-edit-btn" data-edit-card="diveOperatorCard" aria-label="Edit Dive Operator">
                                <span class="material-icons-round" aria-hidden="true">edit</span>
                            </button>
                            <span class="material-icons-round dh-calc-inputs-chevron" aria-hidden="true">expand_less</span>
                        </div>
                        {{-- Two groups (Pablo, 2026-10-15): Operator + Boat in a
                             col-6 beside a col-6 boat picture with the operator
                             logo overlaid bottom-right (desktop and tablet);
                             Boat captain + Divemaster always full width below.
                             On a phone the picture drops to the bottom of the
                             card, under Divemaster, full width. One picture
                             frame serves both view and edit mode - the view
                             block and the edit form are display:contents so
                             their columns and the shared frame all sit in this
                             one grid, placed by order-* (mobile) / order-md-*
                             (tablet up). No small inline logo/pic badges next
                             to the names any more. --}}
                        @php
                            // Logo and picture: a live catalog match by name wins (so a
                            // boat picked by name alone - the app's PATCH - still gets
                            // its picture), else whatever was stored; either way
                            // re-homed on this host (AssetPath) since stored values
                            // used to carry the picker's own hostname.
                            $matchedBoat = null;
                            if ($matchedOperator && !empty($conditions['boat_name'])) {
                                $matchedBoat = collect($boatsByOperator->get($matchedOperator['id']) ?? [])
                                    ->first(fn ($b) => strcasecmp($b['name'], $conditions['boat_name']) === 0);
                            }
                            $frameLogo = \App\Support\AssetPath::absolute($matchedOperator['logoUrl'] ?? ($conditions['operator_logo'] ?? null));
                            $framePic = \App\Support\AssetPath::absolute($matchedBoat['pic'] ?? ($conditions['boat_pic'] ?? null));
                            // Three states (Pablo, 2026-10-15): both operator and boat
                            // known to DH -> col-6 inputs + col-6 picture with the logo
                            // overlaid; operator known, boat not -> col-12 inputs, no
                            // picture, small inline logo next to the operator; operator
                            // not known -> col-12, nothing. dive-details-form.js applies
                            // the same rules live as picks change in edit mode.
                            $opKnown = (bool) ($matchedOperator || !empty($conditions['operator_logo']));
                            $showFrame = $opKnown && (bool) $framePic;
                            $showInlineLogo = $opKnown && !$showFrame && (bool) $frameLogo;
                            $opColClass = $showFrame ? 'col-12 col-md-6' : 'col-12';
                        @endphp
                        <div class="card-body row dh-dive-operator-grid" id="diveOperatorBody">
                            <div data-view class="dh-dive-operator-block">
                                <div class="{{ $opColClass }} order-1" data-op-col>
                                    <div class="dh-dive-view-row">
                                        <span class="dh-dive-view-label">Operator</span>
                                        <span class="dh-dive-view-value dh-dive-operator-value">
                                            <img class="dh-dive-inline-logo" data-op-inline-logo src="{{ $frameLogo }}" alt="" @if (!$showInlineLogo) hidden @endif>
                                            @if ($matchedOperator)
                                                <a href="{{ $matchedOperator['url'] }}">{{ $conditions['operator_name'] }}</a>
                                            @elseif (!empty($conditions['operator_url']))
                                                <a href="{{ $conditions['operator_url'] }}">{{ $conditions['operator_name'] ?? '—' }}</a>
                                            @else
                                                {{ $conditions['operator_name'] ?? '—' }}
                                            @endif
                                        </span>
                                    </div>
                                    <div class="dh-dive-view-row">
                                        <span class="dh-dive-view-label">Boat name</span>
                                        <span class="dh-dive-view-value">{{ $conditions['boat_name'] ?? '—' }}</span>
                                    </div>
                                </div>
                                <div class="col-12 order-2 order-md-3">
                                    <div class="dh-dive-view-row">
                                        <span class="dh-dive-view-label">Boat captain</span>
                                        <span class="dh-dive-view-value">{{ $conditions['boat_captain'] ?? '—' }}</span>
                                    </div>
                                </div>
                                <div class="col-12 order-3 order-md-4">
                                    <div class="dh-dive-view-row">
                                        <span class="dh-dive-view-label">Divemaster</span>
                                        <span class="dh-dive-view-value">{{ $conditions['divemaster'] ?? '—' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6 order-4 order-md-2" id="diveBoatFrameCol" @if (!$showFrame) hidden @endif>
                                <div class="dh-dive-boat-frame">
                                    <img id="diveBoatFrameImg" class="dh-dive-boat-frame-pic" src="{{ $framePic }}" alt="" @if (!$framePic) hidden @endif>
                                    <img id="diveOperatorFrameLogo" class="dh-dive-boat-frame-logo" src="{{ $frameLogo }}" alt="" @if (!$frameLogo) hidden @endif>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('DiveDetails.updateConditions', $dive->id) }}" data-edit hidden class="dh-dive-operator-block">
                                @csrf
                                <input type="hidden" name="form" value="operator">
                                <input type="hidden" name="conditions[operator_logo]" id="diveOperatorLogoInput" value="{{ $conditions['operator_logo'] ?? '' }}">
                                <input type="hidden" name="conditions[operator_url]" id="diveOperatorUrlInput" value="{{ $conditions['operator_url'] ?? '' }}">
                                <input type="hidden" name="conditions[boat_pic]" id="diveBoatPicInput" value="{{ $conditions['boat_pic'] ?? '' }}">

                                <div class="{{ $opColClass }} order-1" data-op-col>
                                    <div class="input-group input-group-outline dh-dive-ig @if($conditions['operator_name'] ?? null) is-filled @endif" style="position: relative;">
                                        <label class="form-label">Operator</label>
                                        <img class="dh-dive-inline-logo" data-op-inline-logo src="{{ $frameLogo }}" alt="" @if (!$showInlineLogo) hidden @endif>
                                        <input type="text" class="form-control" id="diveOperatorSearch" name="conditions[operator_name]" value="{{ $conditions['operator_name'] ?? '' }}" placeholder="Search or type your own" autocomplete="off">
                                        <div id="diveOperatorResults" class="dh-search-list dh-related-site-results" hidden></div>
                                    </div>
                                    <div class="input-group input-group-outline dh-dive-ig @if($conditions['boat_name'] ?? null) is-filled @endif" style="position: relative;">
                                        <label class="form-label">Boat name</label>
                                        <input type="text" class="form-control" id="diveBoatName" name="conditions[boat_name]" value="{{ $conditions['boat_name'] ?? '' }}" autocomplete="off">
                                        <div id="diveBoatResults" class="dh-search-list dh-related-site-results" hidden></div>
                                    </div>
                                </div>
                                <div class="col-12 order-2 order-md-3">
                                    <div class="input-group input-group-outline dh-dive-ig @if($conditions['boat_captain'] ?? null) is-filled @endif">
                                        <label class="form-label">Boat captain</label>
                                        <input type="text" class="form-control" id="diveBoatCaptain" name="conditions[boat_captain]" value="{{ $conditions['boat_captain'] ?? '' }}">
                                    </div>
                                </div>
                                <div class="col-12 order-3 order-md-4">
                                    <div class="input-group input-group-outline dh-dive-ig @if($conditions['divemaster'] ?? null) is-filled @endif">
                                        <label class="form-label">Divemaster</label>
                                        <input type="text" class="form-control" id="diveDivemaster" name="conditions[divemaster]" value="{{ $conditions['divemaster'] ?? '' }}">
                                    </div>
                                </div>
                                <div class="col-12 order-5">
                                    <button type="submit" class="dh-btn dh-btn-primary mt-3">Save</button>
                                    <button type="button" class="dh-btn dh-btn-ghost-dark mt-3" data-cancel-edit>Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Dive Conditions (Pablo, 2026-10-15). Entry type removed
                         per explicit request; Visibility is a slider+text
                         input (DecoPlanner's own convention), unit-aware -
                         imperial: 0-100ft step 10, metric: 0-30m step 3,
                         always stored in meters regardless of what's shown. --}}
                    <div class="card p-0 position-relative mt-3 z-index-2 mb-4" id="diveConditionsCard">
                        <div class="dh-calc-inputs-head" data-collapsible-toggle role="button" tabindex="0" aria-expanded="true" aria-controls="diveConditionsBody">
                            <span class="material-icons-round" aria-hidden="true">water</span>
                            <h3>Dive Conditions</h3>
                            <button type="button" class="dh-dive-card-edit-btn" data-edit-card="diveConditionsCard" aria-label="Edit Dive Conditions">
                                <span class="material-icons-round" aria-hidden="true">edit</span>
                            </button>
                            <span class="material-icons-round dh-calc-inputs-chevron" aria-hidden="true">expand_less</span>
                        </div>
                        <div class="card-body" id="diveConditionsBody">
                            @php
                                $currentLabels = ['none' => 'None', 'light' => 'Light', 'moderate' => 'Moderate', 'strong' => 'Strong'];
                                $currentDirectionLabels = ['N' => 'N', 'NE' => 'NE', 'E' => 'E', 'SE' => 'SE', 'S' => 'S', 'SW' => 'SW', 'W' => 'W', 'NW' => 'NW'];
                                $surfaceLabels = ['calm' => 'Calm', 'slight_chop' => 'Slight chop', 'choppy' => 'Choppy', 'rough' => 'Rough'];
                                $weatherLabels = ['sunny' => 'Sunny', 'partly_cloudy' => 'Partly cloudy', 'cloudy' => 'Cloudy', 'rainy' => 'Rainy', 'windy' => 'Windy', 'stormy' => 'Stormy'];
                                // Same icon set the Weather page shows next to its
                                // forecast (weatherday.conditions_icon points at these
                                // weatherapi-coded files) - Pablo, 2026-10-15: "grab the
                                // weather icon from the weather blade". No wind code
                                // exists in that set; windy falls back to a Material icon.
                                $weatherIcons = ['sunny' => 113, 'partly_cloudy' => 116, 'cloudy' => 119, 'rainy' => 296, 'stormy' => 389];
                                $weatherKey = $conditions['weather'] ?? null;
                                $visibilityM = $conditions['visibility_m'] ?? null;
                            @endphp
                            <div data-view>
                                <div class="dh-dive-view-row">
                                    <span class="dh-dive-view-label">Current</span>
                                    <span class="dh-dive-view-value">{{ $currentLabels[$conditions['current'] ?? null] ?? '—' }}</span>
                                </div>
                                <div class="dh-dive-view-row">
                                    <span class="dh-dive-view-label">Current direction</span>
                                    <span class="dh-dive-view-value">{{ $currentDirectionLabels[$conditions['current_direction'] ?? null] ?? '—' }}</span>
                                </div>
                                <div class="dh-dive-view-row">
                                    <span class="dh-dive-view-label">Surface conditions</span>
                                    <span class="dh-dive-view-value">{{ $surfaceLabels[$conditions['surface_conditions'] ?? null] ?? '—' }}</span>
                                </div>
                                <div class="dh-dive-view-row">
                                    <span class="dh-dive-view-label">Weather</span>
                                    <span class="dh-dive-view-value dh-dive-weather-value">
                                        @if (isset($weatherIcons[$weatherKey]))
                                            <img src="{{ asset('assets') }}/img/weather/64x64/day/{{ $weatherIcons[$weatherKey] }}.png" alt="" class="dh-dive-weather-icon">
                                        @elseif ($weatherKey === 'windy')
                                            <span class="material-icons-round dh-dive-weather-icon" aria-hidden="true">air</span>
                                        @endif
                                        {{ $weatherLabels[$weatherKey] ?? '—' }}
                                    </span>
                                </div>
                                <div class="dh-dive-view-row">
                                    <span class="dh-dive-view-label">Visibility</span>
                                    <span class="dh-dive-view-value">
                                        @if ($visibilityM !== null)
                                            <span data-unit-value="visibility" data-metric="{{ $visibilityM }}"></span>
                                        @else
                                            —
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('DiveDetails.updateConditions', $dive->id) }}" data-edit hidden>
                                @csrf
                                <input type="hidden" name="form" value="conditions">
                                <div class="row dh-dive-conditions-fields">
                                    <div class="col-6 col-md-3 mb-3">
                                        <div class="dh-field mb-0">
                                            <label for="current-btn">Current</label>
                                            <x-dh-select id="current" name="conditions[current]" :options="$currentLabels" :selected="$conditions['current'] ?? null" placeholder="Not set" :disabled="false" />
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3 mb-3">
                                        <div class="dh-field mb-0">
                                            <label for="current_direction-btn">Current direction</label>
                                            <x-dh-select id="current_direction" name="conditions[current_direction]" :options="$currentDirectionLabels" :selected="$conditions['current_direction'] ?? null" placeholder="Not set" :disabled="false" />
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3 mb-3">
                                        <div class="dh-field mb-0">
                                            <label for="surface_conditions-btn">Surface conditions</label>
                                            <x-dh-select id="surface_conditions" name="conditions[surface_conditions]" :options="$surfaceLabels" :selected="$conditions['surface_conditions'] ?? null" placeholder="Not set" :disabled="false" />
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3 mb-3">
                                        <div class="dh-field mb-0">
                                            <label for="weather-btn">Weather</label>
                                            <x-dh-select id="weather" name="conditions[weather]" :options="$weatherLabels" :selected="$conditions['weather'] ?? null" placeholder="Not set" :disabled="false" />
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <label class="dh-gas-label d-block">Visibility</label>
                                    <div class="dh-gas-row">
                                        <div class="dh-gas-input-wrap">
                                            <div class="dh-gas-editable">
                                                <input type="text" inputmode="numeric" class="dh-gas-input" id="diveVisibilityInput">
                                                <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                            </div>
                                            <span class="dh-gas-unit" id="diveVisibilityUnit">ft</span>
                                        </div>
                                        {{-- data-dh-num-mirror opts out of divershub.js's global
                                             "number box next to every slider" helper: the text
                                             input on the left already is that box, and the
                                             helper's copy kept the imperial step=10 after a unit
                                             switch, so 18 m failed its own validation (Pablo,
                                             2026-10-15). --}}
                                        <div class="slider-styled" id="diveVisibilitySlider" data-dh-num-mirror="1"></div>
                                    </div>
                                    <input type="hidden" name="conditions[visibility_m]" id="diveVisibilityMeters" value="{{ $visibilityM }}">
                                </div>
                                <button type="submit" class="dh-btn dh-btn-primary mt-3">Save</button>
                                <button type="button" class="dh-btn dh-btn-ghost-dark mt-3" data-cancel-edit>Cancel</button>
                            </form>
                        </div>
                    </div>

                    {{-- Other (Pablo, 2026-10-15: "for now"). --}}
                    <div class="card p-0 position-relative mt-3 z-index-2 mb-4" id="diveOtherCard">
                        <div class="dh-calc-inputs-head" data-collapsible-toggle role="button" tabindex="0" aria-expanded="true" aria-controls="diveOtherBody">
                            <span class="material-icons-round" aria-hidden="true">more_horiz</span>
                            <h3>Other</h3>
                            <button type="button" class="dh-dive-card-edit-btn" data-edit-card="diveOtherCard" aria-label="Edit Other">
                                <span class="material-icons-round" aria-hidden="true">edit</span>
                            </button>
                            <span class="material-icons-round dh-calc-inputs-chevron" aria-hidden="true">expand_less</span>
                        </div>
                        <div class="card-body" id="diveOtherBody">
                            <div data-view>
                                <div class="dh-dive-view-row">
                                    <span class="dh-dive-view-label">Dive type</span>
                                    <span class="dh-dive-view-value">{{ $selectedDiveTypes ? collect($selectedDiveTypes)->map(fn($t) => $diveTypeOptions[$t] ?? $t)->implode(', ') : '—' }}</span>
                                </div>
                                <div class="dh-dive-view-row">
                                    <span class="dh-dive-view-label">Buddies</span>
                                    <span class="dh-dive-view-value">
                                        @php $buddyNames = array_filter(array_map('trim', explode(',', $conditions['buddies'] ?? ''))); @endphp
                                        @forelse ($buddyNames as $buddyName)
                                            <span class="dh-dive-buddy-pill">
                                                @if (!empty($buddyAvatars[$buddyName]))
                                                    <img src="{{ $buddyAvatars[$buddyName] }}" class="dh-dive-view-logo" alt="">
                                                @endif
                                                {{ $buddyName }}
                                            </span>
                                        @empty
                                            &mdash;
                                        @endforelse
                                    </span>
                                </div>
                                <div class="dh-dive-view-row">
                                    <span class="dh-dive-view-label">Notes</span>
                                    <span class="dh-dive-view-value">{{ $dive->notes ?? '—' }}</span>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('DiveDetails.updateConditions', $dive->id) }}" data-edit hidden>
                                @csrf
                                <input type="hidden" name="form" value="other">
                                <div class="mb-3">
                                    <label class="text-sm font-weight-bold d-block">Dive type</label>
                                    <div class="dh-dive-type-chips">
                                        @foreach ($diveTypeOptions as $val => $label)
                                            <label class="dh-dive-type-chip @if(in_array($val, $selectedDiveTypes)) is-active @endif">
                                                <input type="checkbox" name="conditions[dive_type][]" value="{{ $val }}" @checked(in_array($val, $selectedDiveTypes)) hidden>
                                                {{ $label }}
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="input-group input-group-outline dh-dive-ig @if($conditions['buddies'] ?? null) is-filled @endif" style="position: relative;">
                                    <label class="form-label">Buddies</label>
                                    <input type="text" class="form-control" id="diveBuddies" name="conditions[buddies]" value="{{ $conditions['buddies'] ?? '' }}" placeholder="Type @ to search Divers Hub members" autocomplete="off">
                                    <div id="diveBuddyResults" class="dh-search-list dh-related-site-results" hidden></div>
                                </div>
                                <div class="input-group input-group-outline dh-dive-ig @if($dive->notes) is-filled @endif">
                                    <label class="form-label">Notes</label>
                                    <textarea class="form-control" id="diveNotes" name="notes" rows="4">{{ $dive->notes }}</textarea>
                                </div>
                                <button type="submit" class="dh-btn dh-btn-primary mt-3">Save</button>
                                <button type="button" class="dh-btn dh-btn-ghost-dark mt-3" data-cancel-edit>Cancel</button>
                            </form>
                        </div>
                    </div>

                    @if ($speciesEnabled)
                        {{-- Marine life (Pablo, 2026-10-17): species seen on this dive.
                             dive-species.js. Early access. --}}
                        <div class="card p-0 position-relative mt-3 z-index-2 mb-4" id="diveSpeciesCard">
                            <div class="dh-calc-inputs-head" data-collapsible-toggle role="button" tabindex="0" aria-expanded="true" aria-controls="diveSpeciesBody">
                                <span class="dh-cat-icon" style="--icon:url('{{ asset('assets/img/icons/logbook/species_reef_fish.svg') }}')" aria-hidden="true"></span>
                                <h3>Marine life</h3>
                                <span class="material-icons-round dh-calc-inputs-chevron" aria-hidden="true">expand_less</span>
                            </div>
                            <div class="card-body" id="diveSpeciesBody">
                                <div class="dh-species-chips" id="diveSpeciesChips"></div>
                                <div class="dh-species-picker" id="diveSpeciesPicker"></div>
                                <a href="{{ route('Species.index') }}" class="text-sm">All marine life</a>
                            </div>
                        </div>
                    @endif

                    @if ($equipmentEnabled)
                        {{-- Equipment (Pablo, 2026-10-17): the gear on this dive. A
                             configuration adds its items; computers whose serial
                             matches this dive's are listed automatically. --}}
                        <div class="card p-0 position-relative mt-3 z-index-2 mb-4" id="diveEquipmentCard">
                            <div class="dh-calc-inputs-head" data-collapsible-toggle role="button" tabindex="0" aria-expanded="true" aria-controls="diveEquipmentBody">
                                <span class="material-icons-round" aria-hidden="true">handyman</span>
                                <h3>Equipment</h3>
                                <span class="material-icons-round dh-calc-inputs-chevron" aria-hidden="true">expand_less</span>
                            </div>
                            <div class="card-body" id="diveEquipmentBody">
                                <div class="dh-species-chips" id="diveEquipmentChips"></div>
                                @if ($equipmentItems->isEmpty())
                                    <p class="text-sm text-secondary mb-0">Your gear list is empty. <a href="{{ route('Equipment.index') }}">Add your equipment</a> to record what you used on each dive.</p>
                                @else
                                    <div class="dh-eq-add">
                                        @if ($equipmentConfigs->isNotEmpty())
                                            <select id="diveEquipmentConfig" aria-label="Add a configuration">
                                                <option value="">Add a configuration...</option>
                                                @foreach ($equipmentConfigs as $c)<option value="{{ $c->uuid }}">{{ $c->name }}</option>@endforeach
                                            </select>
                                        @endif
                                        <select id="diveEquipmentItem" aria-label="Add one piece of gear">
                                            <option value="">Add one piece of gear...</option>
                                            @foreach ($equipmentItems->groupBy('category') as $cat => $list)
                                                <optgroup label="{{ \App\Models\EquipmentItem::CATEGORIES[$cat][0] ?? 'Other' }}">
                                                    @foreach ($list as $i)<option value="{{ $i->uuid }}">{{ $i->name }}</option>@endforeach
                                                </optgroup>
                                            @endforeach
                                        </select>
                                    </div>
                                    <a href="{{ route('Equipment.index') }}" class="text-sm d-inline-block mt-2">My equipment</a>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Photo viewer: caption, share to the site page, species, delete. --}}
                    <div class="dh-photo-viewer" id="dhPhotoViewer" hidden role="dialog" aria-modal="true" aria-label="Photo">
                        <button type="button" class="dh-photo-viewer-close" data-pv-close aria-label="Close"><span class="material-icons-round" aria-hidden="true">close</span></button>
                        <button type="button" class="dh-photo-viewer-nav is-prev" data-pv-prev aria-label="Previous photo"><span class="material-icons-round" aria-hidden="true">chevron_left</span></button>
                        <button type="button" class="dh-photo-viewer-nav is-next" data-pv-next aria-label="Next photo"><span class="material-icons-round" aria-hidden="true">chevron_right</span></button>
                        <figure class="dh-photo-viewer-figure"><img alt="" id="dhPhotoViewerImg"></figure>
                        <div class="dh-photo-viewer-panel">
                            <div class="dh-photo-viewer-meta text-xs" id="dhPhotoViewerMeta"></div>
                            <div class="dh-photo-viewer-caption">
                                <input type="text" maxlength="500" placeholder="Add a caption" id="dhPhotoViewerCaption" aria-label="Caption">
                            </div>
                            @if ($speciesEnabled)
                                <div class="dh-photo-viewer-species" id="dhPhotoViewerSpecies"></div>
                            @endif
                            <div class="dh-photo-viewer-actions">
                                @if ($dive->site_id)
                                    <label class="dh-photo-share">
                                        <input type="checkbox" id="dhPhotoViewerShare">
                                        <span>Show on <strong>{{ $siteName }}</strong>'s page</span>
                                        <span class="dh-photo-share-status" id="dhPhotoViewerShareStatus"></span>
                                    </label>
                                @endif
                                <button type="button" class="dh-btn dh-btn-ghost-dark" id="dhPhotoViewerDelete"><span class="material-icons-round" aria-hidden="true">delete</span>Delete</button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>

    @push('js')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation"></script>
        <script src="{{ asset('assets') }}/js/plugins/nouislider.js"></script>
        <link href="{{ asset('assets') }}/css/nouislider.css" rel="stylesheet">
        <script>
            window.dhDiveProfile = @json($chartProfile ?? $dive->profile);
            window.dhDiveDuration = {{ (int) $dive->duration_seconds }};
            window.dhDiveTanks = @json($tanks);
            window.dhDiveCompanions = @json(collect($companions ?? [])->map(fn ($c) => ['label' => $c['label'], 'color' => $c['color'], 'points' => $c['points']])->values());
            window.dhDiveGasMixes = @json($dive->gas_mixes ?? []);
            window.dhDiveIsCcr = @json($isCcr);
            window.dhDiveChartColors = @json($chartColors);
            window.dhOperators = @json($operators);
            window.dhBoatsByOperator = @json($boatsByOperator);
            window.dhSites = @json($sites);
            window.dhBuddySearchUrl = "{{ route('Logbook.searchUsers') }}";
            window.dhDiveTankConfig = @json($tankConfig);
            window.dhDiveConditionsUrl = "{{ route('DiveDetails.updateConditions', $dive->id) }}";
            // One-page PDF export (dive-print.js), planner-style page
            // snapshot in portrait (Pablo, 2026-10-16: "something similar to
            // what we created for the pdf of the deco planning, portrait").
            @php
                $printLabel = fn ($k) => $k ? ucfirst(str_replace('_', ' ', $k)) : null;
                $printGases = [];
                foreach ($gasRows as $pi => $r) {
                    $printGases[] = [
                        'rowIdx' => $pi,
                        'o2' => (int) round($r['mix']['oxygen'] ?? 0),
                        'he' => (int) round($r['mix']['helium'] ?? 0),
                        'label' => $chipMix($r['mix']),
                        'role' => $roleOptions[$r['role']] ?? ($r['role'] ? ucfirst($r['role']) : ''),
                        'tankIndices' => array_values($r['tankIndices']),
                        'deleted' => (bool) $r['deleted'],
                    ];
                }
            @endphp
            @php
                // Blade's @json splits an inline array literal on commas - build it first.
                $printData = [
                'seq' => $seq,
                'dateLong' => $dive->started_at->format('l, F j, Y'),
                'dateShort' => $dive->started_at->format('Y-m-d'),
                'timeIn' => $dive->started_at->format('g:i A'),
                'timeOut' => $dive->started_at->copy()->addSeconds($dive->duration_seconds)->format('g:i A'),
                'siteName' => $siteName,
                'siteCity' => $siteInfo['city'] ?? null,
                'siteCountry' => ($siteInfo['user_site'] ?? null)?->country,
                'lat' => $siteInfo['latitude'] ?? null,
                'lon' => $siteInfo['longitude'] ?? null,
                'modeLabel' => $dive->modeLabel(),
                'isCcr' => $isCcr,
                'classification' => ucfirst($classification),
                'userName' => auth()->user()->name ?? '',
                'legend' => $diveComputerLegend,
                'assetsUrl' => asset('assets'),
                'maxDepthM' => (float) $dive->max_depth_m,
                'avgDepthM' => $dive->avg_depth_m !== null ? (float) $dive->avg_depth_m : null,
                'durationSeconds' => (int) $dive->duration_seconds,
                'minTempC' => $dive->min_water_temp_c !== null ? (float) $dive->min_water_temp_c : null,
                'tags' => $dive->tags ?? [],
                'conditions' => [
                    'current' => $printLabel($conditions['current'] ?? null),
                    'currentDirection' => $conditions['current_direction'] ?? null,
                    'surface' => $printLabel($conditions['surface_conditions'] ?? null),
                    'weather' => $printLabel($conditions['weather'] ?? null),
                    'visibilityM' => isset($conditions['visibility_m']) ? (float) $conditions['visibility_m'] : null,
                    'diveTypes' => collect($selectedDiveTypes)->map(fn ($t) => $diveTypeOptions[$t] ?? $t)->values()->all(),
                ],
                'operator' => [
                    'name' => $conditions['operator_name'] ?? null,
                    'boat' => $conditions['boat_name'] ?? null,
                    'captain' => $conditions['boat_captain'] ?? null,
                    'divemaster' => $conditions['divemaster'] ?? null,
                    'logoUrl' => $matchedOperator['logoUrl'] ?? null,
                ],
                'buddies' => array_values(array_filter(array_map('trim', explode(',', $conditions['buddies'] ?? '')))),
                'notes' => $dive->notes,
                'gases' => $printGases,
                'hasProfile' => $dive->hasProfile(),
                'serverPdfUrl' => route('DiveDetails.print', $dive->id),
            ];
            @endphp
            window.dhDivePrint = @json($printData);
            window.dhDivePhotos = {
                photos: @json($photos),
                max: {{ \App\Models\DivePhoto::MAX_PER_DIVE }},
                fishIcon: "{{ asset('assets/img/icons/logbook/species_reef_fish.svg') }}",
                uploadUrl: "{{ route('DivePhotos.store', $dive->id) }}",
                orderUrl: "{{ route('DivePhotos.reorder', $dive->id) }}",
                deleteUrl: "{{ url('Logbook/photos') }}/",
                csrf: "{{ csrf_token() }}",
            };
        </script>
        <script src="{{ asset('assets') }}/js/dh-select.js?v={{ config('divehub.version') }}"></script>
        <script src="{{ asset('assets') }}/js/dh-units.js?v={{ config('divehub.version') }}"></script>
        <script src="{{ asset('assets') }}/js/dh-tanks.js?v={{ config('divehub.version') }}"></script>
        <script src="{{ asset('assets') }}/js/dive-details.js?v={{ config('divehub.version') }}"></script>
        <script src="{{ asset('assets') }}/js/dive-details-form.js?v={{ config('divehub.version') }}"></script>
        @if ($speciesEnabled)
            <script>
                window.dhDiveSpecies = {
                    all: @json($speciesAll),
                    tagged: @json($speciesTagged),
                    tagUrl: "{{ route('DiveSpecies.tag', $dive->id) }}",
                    untagUrl: "{{ url('Logbook/' . $dive->id . '/species') }}/",
                    photoUrl: "{{ url('Logbook/photos') }}/",
                    shareSightings: @json($dive->user?->share_sightings ?? auth()->user()->share_sightings),
                    shareUrl: "{{ route('Species.shareSightings') }}",
                    newUrl: "{{ route('Species.create') }}",
                    // "What's this?" - only when the AI key is configured.
                    identify: @json(\App\Support\SpeciesIdentifier::enabled(auth()->user())),
                    csrf: "{{ csrf_token() }}",
                };
            </script>
            <script src="{{ asset('assets') }}/js/dive-species.js?v={{ config('divehub.version') }}"></script>
        @endif
        @if ($equipmentEnabled)
            <script>
                (function () {
                    var list = @json($diveEquipment);
                    var addUrl = "{{ route('DiveEquipment.add', $dive->id) }}";
                    var removeUrl = "{{ url('Logbook/' . $dive->id . '/equipment') }}/";
                    var csrf = "{{ csrf_token() }}";
                    var chips = document.getElementById('diveEquipmentChips');
                    function esc(s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
                    function send(method, url, body) {
                        return fetch(url, { method: method, credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' }, body: body ? JSON.stringify(body) : undefined })
                            .then(function (r) { if (!r.ok) throw new Error('Could not save'); return r.json(); });
                    }
                    function render() {
                        chips.innerHTML = list.length ? '' : '<p class="text-sm text-secondary mb-2">No gear recorded on this dive yet.</p>';
                        list.forEach(function (i) {
                            var c = document.createElement('span');
                            c.className = 'dh-species-chip';
                            c.title = i.category + (i.auto ? ' - matched by serial number' : '');
                            c.innerHTML = '<span class="dh-cat-icon" aria-hidden="true" style="color:var(--dh-sea);--icon:url(\'' + i.icon_url + '\')"></span><a href="' + i.url + '">' + esc(i.name) + '</a>'
                                + (i.auto ? '<span class="dh-species-mine ms-1">auto</span>&nbsp;' : '<button type="button" aria-label="Remove ' + esc(i.name) + '"><span class="material-icons-round" aria-hidden="true">close</span></button>');
                            var x = c.querySelector('button');
                            if (x) x.addEventListener('click', function () { send('DELETE', removeUrl + i.uuid).then(function (d) { list = d.equipment; render(); }).catch(function (e) { alert(e.message); }); });
                            chips.appendChild(c);
                        });
                    }
                    [['diveEquipmentConfig', 'config'], ['diveEquipmentItem', 'item']].forEach(function (pair) {
                        var sel = document.getElementById(pair[0]);
                        if (!sel) return;
                        sel.addEventListener('change', function () {
                            if (!sel.value) return;
                            var body = {}; body[pair[1]] = sel.value;
                            send('POST', addUrl, body).then(function (d) { list = d.equipment; render(); sel.value = ''; }).catch(function (e) { alert(e.message); });
                        });
                    });
                    render();
                })();
            </script>
        @endif
        <script src="{{ asset('assets') }}/js/dive-photos.js?v={{ config('divehub.version') }}"></script>
        {{-- PDF export, same libraries + approach as DivePlanner's own export. --}}
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
        <script src="{{ asset('assets') }}/js/dive-print.js?v={{ config('divehub.version') }}"></script>
    @endpush
</x-page-template>

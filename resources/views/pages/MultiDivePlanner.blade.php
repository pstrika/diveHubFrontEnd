<x-page-template bodyClass='dh-shell bg-gray-200' :SEO="$SEO ?? []">
    <x-shell.nav active="me" />

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <x-shell.header title="Multi Dive Planner" icon="layers" :h1="true" />

        {{-- Safety disclaimer (Pablo, 2026-10-14: "accept it, but add a
             modal disclaimer like we do for the deco planner") - same
             shape/tone/strength as DecoPlanner's own #modalWarning
             (DivePlanner.blade.php), shown unconditionally on every page
             load via data-backdrop="static" data-keyboard="false" (can't
             be dismissed by clicking outside or Escape), not just a
             one-time localStorage flag. Wording adapted for THIS tool's
             own real finding, not copy-pasted verbatim: this planner's
             calculated no-decompression/surface-interval numbers use real
             Bühlmann decompression modeling, which can diverge from
             printed recreational tables (PADI RDP etc.) in either
             direction depending on depth and conservatism setting -
             confirmed directly against the backend, 2026-10-14. Per
             [[decoplanner_disclaimer_intentional]]'s own standing
             feedback, this is a deliberate safety guardrail, not generic
             boilerplate - don't soften/shorten it as a UX-friction fix
             without reconfirming with Pablo first. --}}
        {{-- data-bs-backdrop/data-bs-keyboard, not the unprefixed
             data-backdrop/data-keyboard DecoPlanner's own #modalWarning
             uses (DivePlanner.blade.php) - this app runs Bootstrap 5.1.0
             (public/assets/js/core/bootstrap.min.js), which renamed these
             to the data-bs- prefix; the old names are silently ignored,
             so DecoPlanner's own copy is likely dismissible by clicking
             outside/Escape despite appearing to disable that (caught via
             Playwright on THIS modal, 2026-10-14, before it shipped -
             not fixing DecoPlanner's own copy here, out of scope). --}}
        <div class="modal fade" id="modalMultiDiveWarning" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="modal-multidive-warning-title" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header text-center">
                        <h6 class="modal-title font-weight-normal" id="modal-multidive-warning-title">Warning</h6>
                    </div>
                    <div class="modal-body">
                        <div class="py-3 text-center">
                            <i class="material-icons h1 text-primary">warning</i>
                            <h4 class="text-gradient text-danger text-sm mt-4" style="text-align: justify;">
                                <p><strong>Repetitive dive planning, even within no-decompression limits, is a risky activity.</strong> This tool calculates surface intervals and no-decompression times using a real mathematical decompression model (Bühlmann ZH-L16C), not a lookup from PADI or other printed recreational dive tables - the numbers it gives can differ from those tables, in either direction, depending on depth and the conservatism setting chosen.</p>
                                <p><strong>Divers should always use a reliable dive computer</strong> to monitor actual dive conditions, follow established safety guidelines from recognized diving organizations, dive within their training and experience level, and plan for contingencies and emergency procedures.</p>
                                <p><strong>No decompression model can fully eliminate risk.</strong> Proper dive planning, equipment redundancy, and conservative dive practices are essential for safer diving experiences. Use this tool as an informational aid, not a replacement for professional dive planning and real-time monitoring.</p>
                            </h4>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-info" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid py-0">
            <div class="row">
                <div class="col-md-12 m-auto">
                    {{-- Unit switch (Pablo, 2026-10-13: "Only do either ft or m
                         for the depth... add a button at the top switch to
                         metric or switch to imperial") - exact same link
                         pattern as DivePlanner.blade.php's own #switchUnits,
                         just pointed at this page's own Imperial/Metric
                         routes. $deco_unit already honors the signed-in
                         user's own deco_unit preference (MultiDivePlannerController@show),
                         defaulting to imperial for guests/unset. --}}
                    <div class="d-flex justify-content-end align-items-center mt-3" style="gap: 8px;">
                        {{-- Conservatism gear (Pablo, 2026-10-14) - same .dh-gear-btn
                             + modal pattern as DivePlanner's own "Advanced settings"
                             (#dhAdvancedSettingsBtn/#modalAdvancedSettings), but open
                             to guests too (this whole page is, by design) and holding
                             just the one Normal/Low choice instead of DecoPlanner's
                             full GF sliders - this planner never shows deco stops, so
                             there's nothing else here to tune. Not persisted to the
                             user's profile, same as DecoPlanner's own non-GF toggles
                             (Remove safety guards etc.) - resets to Normal on reload. --}}
                        <button type="button" class="dh-gear-btn" id="dhMultiDiveConservatismBtn" title="Conservatism" aria-label="Conservatism settings">
                            <span class="material-icons-round" aria-hidden="true">settings</span>
                        </button>
                        @if(!is_null($currentSite) && $deco_unit)
                            <a class="dh-btn dh-btn-ghost-dark" id="switchUnits" href="{{ route('MultiDivePlannerImperial') }}/{{ $currentSite->id }}">Switch to Imperial</a>
                        @elseif(is_null($currentSite) && $deco_unit)
                            <a class="dh-btn dh-btn-ghost-dark" id="switchUnits" href="{{ route('MultiDivePlannerImperial') }}">Switch to Imperial</a>
                        @elseif(!is_null($currentSite) && !$deco_unit)
                            <a class="dh-btn dh-btn-ghost-dark" id="switchUnits" href="{{ route('MultiDivePlannerMetric') }}/{{ $currentSite->id }}">Switch to Metric</a>
                        @else
                            <a class="dh-btn dh-btn-ghost-dark" id="switchUnits" href="{{ route('MultiDivePlannerMetric') }}">Switch to Metric</a>
                        @endif
                    </div>

                    {{-- Conservatism modal (Pablo, 2026-10-14: "add a config gear
                         button - like we did with the decoplanner... default to
                         Conservatism Normal and use 70/85, but add a check to
                         'Conservatism Low' and we use 100/100"). GF 100/100 matches
                         PADI's own first-dive NDL almost exactly but runs noticeably
                         more generous than PADI on repetitive dives (confirmed
                         2026-10-14: 35min vs PADI's 22min at 70ft after a 43min
                         interval) - Normal (70/85) is the safer recreational-computer-
                         like default, Low is an explicit opt-in for divers who want
                         the least-conservative numbers Bühlmann allows. --}}
                    <div class="modal fade" id="modalMultiDiveConservatism" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Conservatism</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-check mb-0">
                                        <input class="form-check-input dh-check" type="checkbox" id="dhConservatismLowToggle">
                                        <label class="form-check-label text-body mb-0" for="dhConservatismLowToggle">
                                            <span class="dh-check-title">Conservatism: Low</span>
                                            <div class="text-secondary text-xs">Unchecked (Normal, default) uses gradient factors 70/85 - typical modern dive computer conservatism. Checked (Low) uses 100/100 - no added conservatism, the most bottom time Bühlmann allows, which can run longer than PADI/table limits on repetitive dives.</div>
                                        </label>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{--
                        Up to 4 separate dives with a surface interval between
                        each (Pablo, 2026-10-13) - not to be confused with the
                        Deco Planner's own "Multi Level" tab, which is
                        multiple depth/time segments within ONE dive. Blade
                        only for now, no backend calc wiring yet.

                        Same on-demand "4 fixed pre-rendered slots, shown/
                        hidden with the hidden attribute" pattern as the Deco
                        Planner's level panels (DivePlanner.blade.php) - Dive
                        1 is the always-present base dive (no delete button),
                        Dives 2-4 each have a corner delete, and a surface
                        interval panel sits between every pair of visible
                        dives. See multi-dive-planner.js for the add/delete
                        (with value-shift, same as the level panels) logic.
                    --}}
                    <div class="card p-0 position-relative mt-3 z-index-2 mb-4" id="dh-multidive-inputs-card">
                        <div class="dh-calc-inputs-head" id="dh-multidive-inputs-toggle" role="button" tabindex="0" aria-expanded="true" aria-controls="dh-multidive-inputs-body">
                            <span class="material-icons-round" aria-hidden="true">tune</span>
                            <h3>Inputs</h3>
                            <button type="button" class="dh-btn-icon dh-btn-icon-accent" id="dhResetMultiDiveInputsBtn" title="Reset all inputs" aria-label="Reset all inputs" style="margin-left: auto;">
                                <span class="material-icons-round" aria-hidden="true">restart_alt</span>
                            </button>
                            <span class="material-icons-round dh-calc-inputs-chevron" id="dh-multidive-inputs-chevron" aria-hidden="true">expand_less</span>
                        </div>

                        <div class="card-body" id="dh-multidive-inputs-body">
                            <div class="row" id="multiDiveRow">
                                @for ($n = 1; $n <= 4; $n++)
                                    {{-- Horizontal, full-width dive card (Pablo, 2026-10-13:
                                         "horizontal cards taking all width for the dives") -
                                         col-12 regardless of breakpoint, unlike the Multi Level
                                         planner's own 4-across #multiLevelRow. Fields that used
                                         to stack vertically inside a narrow col-lg-3 now sit in
                                         one internal Bootstrap row instead. --}}
                                    <div class="col-12" id="divePanel{{ $n }}" @if($n > 1) hidden @endif>
                                        <div class="dh-level-panel">
                                            @if($n > 1)
                                                <button type="button" class="dh-corner-delete" id="dive{{ $n }}DeleteButton" onclick="hideDive{{ $n }}()" aria-label="Delete dive {{ $n }}">
                                                    <span class="material-icons-round" aria-hidden="true">delete</span>
                                                </button>
                                            @endif
                                            <table class="table align-items-center mb-0 mt-1">
                                                <tr><td class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center" style="border: none;">Dive {{ $n }}</td></tr>
                                            </table>

                                            <div class="row gx-3">
                                                {{-- Depth column (Pablo, 2026-10-13/14) - site search, then
                                                     the depth slider directly below it, then the depth
                                                     pill itself centered at the bottom of the column (so
                                                     Depth/Bottom time/Gas's pills line up on one baseline
                                                     regardless of how tall each column's other content
                                                     is - col-12 col-md-4 so it still stacks full-width on
                                                     mobile instead of compressing 3-across). Site search
                                                     is an optional shortcut that fills in the depth from a
                                                     real site's max depth; the depth stays a plain
                                                     editable number either way. Filtered client-side from
                                                     the full site list the page ships (already capped at
                                                     130ft or shallower server-side,
                                                     MultiDivePlannerController@allSites), same pattern as
                                                     the group chat's @mention picker - no search endpoint
                                                     needed. Single unit now, driven by the page-level
                                                     imperial/metric switch above rather than showing both
                                                     side by side. --}}
                                                <div class="col-12 col-md-4 mt-2 d-flex flex-column">
                                                    <label class="dh-gas-label text-center">Depth</label>
                                                    <label class="dh-gas-label" for="diveSiteSearch{{ $n }}">Site (optional)</label>
                                                    <div class="position-relative mb-2">
                                                        <input type="text" class="form-control border" id="diveSiteSearch{{ $n }}" placeholder="Search for a site..." autocomplete="off">
                                                        <div class="dh-mention-menu dh-mention-menu-down" id="diveSiteResults{{ $n }}" hidden></div>
                                                    </div>

                                                    <input type="hidden" id="depthSliderDive{{ $n }}-value" name="depthSliderDive{{ $n }}-value">
                                                    <div class="slider-styled mb-2" id="depthSliderDive{{ $n }}" data-dh-num-mirror="1"></div>

                                                    <div class="dh-gas-input-wrap align-self-center mt-auto">
                                                        <div class="dh-gas-editable">
                                                            <input type="text" inputmode="numeric" class="dh-gas-input" id="labelDepthDive{{ $n }}" value="{{ $deco_unit ? round((100 - ($n - 1) * 20) * 0.3048) : (100 - ($n - 1) * 20) }}">
                                                            <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                            @if($n > 1)
                                                                {{-- "Solve for" toggle (Pablo, 2026-10-13): a
                                                                     small badge overlaying this field's own
                                                                     pill, not a separate control - for the
                                                                     surface interval before this dive + this
                                                                     dive's own depth/bottom time, exactly one
                                                                     of the three can be the unknown. Group
                                                                     index is n-1: this dive is the one that
                                                                     follows surfaceIntervalPanel{{ $n - 1 }}.
                                                                     See dhSetSolveFor() in multi-dive-planner.js. --}}
                                                                <button type="button" class="dh-solve-toggle" id="solveToggleDepth{{ $n - 1 }}" title="Solve for depth" aria-label="Solve for depth" aria-pressed="false">
                                                                    <span class="material-icons-round" aria-hidden="true">calculate</span>
                                                                </button>
                                                            @endif
                                                        </div>
                                                        <span class="dh-gas-unit">{{ $deco_unit ? 'm' : 'ft' }}</span>
                                                    </div>
                                                </div>

                                                {{-- Bottom time column (Pablo, 2026-10-13/14) - slider,
                                                     then safety stop, then the bottom time pill itself
                                                     centered at the bottom of the column, same reasoning
                                                     as Depth above. --}}
                                                <div class="col-12 col-md-4 mt-2 d-flex flex-column">
                                                    <label class="dh-gas-label text-center" for="labelBottomTimeDive{{ $n }}">Bottom time</label>
                                                    <input type="hidden" id="bottomTimeSliderDive{{ $n }}-value" name="bottomTimeSliderDive{{ $n }}-value">
                                                    <div class="slider-styled mb-2" id="bottomTimeSliderDive{{ $n }}" data-dh-num-mirror="1"></div>

                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input dh-check" type="checkbox" id="safetyStopDive{{ $n }}" checked>
                                                        <label class="form-check-label text-body mb-0" for="safetyStopDive{{ $n }}">3 min stop @ 15ft</label>
                                                    </div>

                                                    <div class="dh-gas-input-wrap align-self-center mt-auto">
                                                        <div class="dh-gas-editable">
                                                            <input type="text" inputmode="numeric" class="dh-gas-input" id="labelBottomTimeDive{{ $n }}" value="{{ 40 - ($n - 1) * 5 }}">
                                                            <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                            @if($n > 1)
                                                                <button type="button" class="dh-solve-toggle" id="solveToggleTime{{ $n - 1 }}" title="Solve for bottom time" aria-label="Solve for bottom time" aria-pressed="false">
                                                                    <span class="material-icons-round" aria-hidden="true">calculate</span>
                                                                </button>
                                                            @endif
                                                        </div>
                                                        <span class="dh-gas-unit">min</span>
                                                    </div>
                                                </div>

                                                {{-- Gas - Open Circuit only, no Close Circuit/Trimix
                                                     anywhere in this planner (Pablo, 2026-10-13). Same
                                                     mental model as the Best Gas Calculator's single-tank
                                                     Nitrox section (GasPlanning.blade.php): the diver
                                                     thinks of this as setting PPO2, but the value that's
                                                     actually clamped and stored is O2% (21-40%, and the
                                                     quick presets below just set it directly) - PPO2 is
                                                     a synced, derived display. Hard-capped at both 40%
                                                     O2 AND 1.6 ATA PPO2 at this dive's own depth, not
                                                     just the soft color warning GasPlanning uses - see
                                                     dhUpdateGasRangeForDepth() in multi-dive-planner.js.
                                                     Its chips/pills centered and the O2% result pill
                                                     pushed to the bottom (Pablo, 2026-10-14), same
                                                     baseline as Depth/Bottom time's own pills above. --}}
                                                <div class="col-12 col-md-4 mt-2 d-flex flex-column">
                                                    <label class="dh-gas-label text-center">Gas</label>
                                                    <div class="dh-channel-picker dh-gas-picker dh-gas-preset-row justify-content-center" id="gasPresetDive{{ $n }}" data-dive="{{ $n }}">
                                                        <button type="button" class="dh-channel-chip" data-o2="21">Air</button>
                                                        <button type="button" class="dh-channel-chip" data-o2="32">Nitrox 32%</button>
                                                        <button type="button" class="dh-channel-chip" data-o2="36">Nitrox 36%</button>
                                                    </div>
                                                    <div class="mt-2">
                                                        <label class="dh-gas-label" for="labelPPO2Dive{{ $n }}">PPO&#8322; at max depth</label>
                                                        <input type="hidden" id="o2SliderDive{{ $n }}-value" name="o2SliderDive{{ $n }}-value">
                                                        <div class="dh-gas-row">
                                                            <div class="dh-gas-input-wrap">
                                                                <div class="dh-gas-editable">
                                                                    <input type="text" inputmode="decimal" class="dh-gas-input is-safe" id="labelPPO2Dive{{ $n }}" value="0.84">
                                                                    <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                                </div>
                                                                <span class="dh-gas-unit">atm</span>
                                                            </div>
                                                            <div class="slider-styled" id="o2SliderDive{{ $n }}" data-dh-num-mirror="1"></div>
                                                        </div>
                                                    </div>
                                                    <div class="text-center mt-auto pt-2">
                                                        <label class="dh-gas-result-pill is-o2" id="o2ResultDive{{ $n }}">21%</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    @if($n < 4)
                                        {{-- Small connector arrow, stage to stage (Pablo, 2026-10-13:
                                             "add a small arrow from 'stage' to 'stage'") - shown/hidden
                                             in lockstep with the panel it leads INTO, exactly like that
                                             panel's own surface-interval toggle below, via dhShowDive()/
                                             dhDeleteDive() in multi-dive-planner.js. --}}
                                        <div class="col-12 dh-stage-arrow" id="arrowBeforeInterval{{ $n }}" hidden>
                                            <span class="material-icons-round" aria-hidden="true">arrow_downward</span>
                                        </div>

                                        {{-- Surface interval between this dive and the next - no
                                             analog in the Deco Planner's level panels (a single dive
                                             has no interval of its own), closest existing building
                                             block is the single-dive planner's own Surface time slider
                                             (DivePlanner.blade.php, id surfaceTimeSlider). Hidden until
                                             the NEXT dive is added, same gate as that dive's own panel -
                                             there's no interval to set until there's a second dive to
                                             have it between. HH:MM display, 1-minute slider steps
                                             (Pablo, 2026-10-13), see multi-dive-planner.js. --}}
                                        <div class="col-12" id="surfaceIntervalPanel{{ $n }}" hidden>
                                            <div class="dh-level-panel dh-surface-interval-panel">
                                                <table class="table align-items-center mb-0 mt-1">
                                                    <tr><td class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center" style="border: none;">Surface Interval</td></tr>
                                                </table>
                                                <span class="material-icons-round dh-surface-interval-icon" aria-hidden="true">schedule</span>
                                                <div class="mt-2 d-inline-block">
                                                    <label class="dh-gas-label" for="labelSurfaceInterval{{ $n }}">Time on the surface (hh:mm)</label>
                                                    <input type="hidden" id="surfaceIntervalSlider{{ $n }}-value" name="surfaceIntervalSlider{{ $n }}-value">
                                                    <div class="dh-gas-row">
                                                        <div class="dh-gas-input-wrap">
                                                            <div class="dh-gas-editable">
                                                                <input type="text" inputmode="numeric" class="dh-gas-input dh-gas-input-wide" id="labelSurfaceInterval{{ $n }}" value="01:00">
                                                                {{-- "Solve for" toggle (Pablo, 2026-10-13) -
                                                                     small badge overlaying this input, not a
                                                                     separate control - this interval, the
                                                                     following dive's depth, or its bottom
                                                                     time: only one of the three can be the
                                                                     unknown. See dhSetSolveFor() in
                                                                     multi-dive-planner.js. --}}
                                                                <button type="button" class="dh-solve-toggle" id="solveToggleInterval{{ $n }}" title="Solve for surface interval" aria-label="Solve for surface interval" aria-pressed="false">
                                                                    <span class="material-icons-round" aria-hidden="true">calculate</span>
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <div class="slider-styled" id="surfaceIntervalSlider{{ $n }}" data-dh-num-mirror="1"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 dh-stage-arrow" id="arrowBeforeDive{{ $n + 1 }}" hidden>
                                            <span class="material-icons-round" aria-hidden="true">arrow_downward</span>
                                        </div>
                                    @endif
                                @endfor

                                <div class="col-12 mt-2">
                                    <button type="button" class="dh-gas-accordion-add" id="diveBtnAdd">
                                        <span class="material-icons-round" aria-hidden="true">add</span>
                                        Add a dive
                                    </button>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="text-center" style="border: none;">
                                        <a type="button" class="btn btn-info mt-0" id="calculateMultiDiveBtn">Calculate Multi Dive Plan</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Results - hidden until Calculate (placeholder only for
                         now, same as the inputs: real NDL/surface-interval
                         math isn't wired to the backend yet). Same card
                         shell + dark header class as the Deco Planner's own
                         results card (.dh-deco-section-head), for visual
                         consistency. --}}
                    <div class="row" id="multiDiveResults" style="display: none;">
                        <div class="col-12">
                            <div class="card p-0 position-relative mt-3 z-index-2 mb-4">
                                <div class="dh-deco-section-head">
                                    <span class="material-icons-round" aria-hidden="true">route</span>
                                    <div class="dh-deco-section-head-title-wrap">
                                        <h3>Multi dive plan</h3>
                                        <span class="dh-deco-section-head-meta" id="multiDiveResultsMeta"></span>
                                    </div>
                                </div>
                                <div class="card-body" id="multiDiveResultsBody">
                                    {{-- Dive 1's NDL clamp (Pablo, 2026-10-13: "if not, we will use
                                         the max time as NDL") - the backend silently shortens dive 1's
                                         bottom time to its own NDL when the entered time would need
                                         deco, and says so via dives[0].clampedFromBottomTime. Shown
                                         here rather than staying silent, so the diver knows the plan
                                         isn't running the bottom time they typed. --}}
                                    <div class="callout-info mb-3" id="multiDiveClampNotice" hidden></div>

                                    {{-- A transition can come back result:null (genuinely
                                         infeasible even at the full solve range) - same per-row
                                         "—" + message treatment the table rows below use, this is
                                         just the summary line above the table. --}}
                                    <div class="callout-warn mb-3" id="multiDiveInfeasibleNotice" hidden></div>

                                    {{-- One row per stage, Dive 1 / Surface Interval / Dive 2 / ...
                                         in sequence (Pablo, 2026-10-12). Whichever cell was the
                                         thing solved for renders via .dh-gas-result-pill-like blue
                                         badge (dhSolvedPillHtml() in multi-dive-planner.js) instead
                                         of plain text. --}}
                                    <div class="table-responsive mb-2">
                                        <table class="table align-items-center mb-0" id="multiDiveResultsTable">
                                            <thead>
                                                <tr>
                                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Dive or Surface Interval</th>
                                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Depth</th>
                                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Bottom time</th>
                                                    {{-- Pablo, 2026-10-14: a dive's Total Time is bottom
                                                         time + descend/ascend/safety-stop (the engine's own
                                                         totals.runtime, already includes all of that) - a
                                                         surface interval's Total Time is just its own
                                                         duration, moved here OUT of Bottom time (you're not
                                                         "at the bottom" during a surface interval). --}}
                                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Total Time</th>
                                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Gas</th>
                                                </tr>
                                            </thead>
                                            <tbody id="multiDiveResultsTableBody"></tbody>
                                        </table>
                                    </div>

                                    {{-- Pablo, 2026-10-14: "a pill extending all width at the
                                         bottom of the table saying 'Total excursion time:'...dark
                                         blue theme bg with white fonts" - sum of every row's own
                                         Total Time column above. --}}
                                    <div class="dh-total-excursion-pill mb-4" id="multiDiveTotalExcursionPill"></div>

                                    {{-- Combined depth/time profile (Pablo, 2026-10-12: "a depth
                                         chart vs time with all the results stiching all the dives
                                         together...for surface interval, we consider we are at 0
                                         feet depth") - backend now builds this whole-day array
                                         itself (combinedProfile), same Chart.js scatter-line pattern
                                         DecoPlanner's own renderProfileChart() uses. --}}
                                    <div style="position: relative; height: 320px;">
                                        <canvas id="multiDiveProfileChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>

    @push('js')
    <script src="{{ asset('assets') }}/js/plugins/nouislider.js"></script>
    <link href="{{ asset('assets') }}/css/nouislider.css" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        window.DH_MULTIDIVE = {
            unit: "<?php echo $deco_unit ? 'm' : 'ft'; ?>",
            modeImpOrMetric: "<?php echo $deco_unit ? 'met' : 'imp'; ?>",
            currentSite: @json($currentSite),
            allSites: @json($allSites),
            assetsUrl: "{{ asset('assets') }}",
            {{-- Same client-exposed-host-key pattern as DivePlanner.blade.php's
                 own window.DH_DECO.apiHostKey - the Azure Function host key is
                 base64-encoded at rest in .env, decoded server-side here, then
                 printed into the page so the client-side $.ajax call (same
                 pattern as deco-planner.js) can call decoplanningapi directly.
                 Not a secret in the security sense (CORS-open, rate-limited by
                 Azure, same key already shipped on every Deco Planner page). --}}
            apiHostKey: "<?php echo base64_decode(env('DIVE_PLANNING_API_V2_HOST_KEY')); ?>",
            gfLow: {{ $gradientFactors['low'] }},
            gfHigh: {{ $gradientFactors['high'] }},
        };
    </script>
    <script src="{{ asset('assets') }}/js/multi-dive-planner.js?v={{ config('divehub.version') }}"></script>
    @endpush
</x-page-template>

<x-page-template bodyClass='dh-shell bg-gray-200' :SEO="$SEO ?? []">
    <x-shell.nav active="me" />
    
    
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <!-- Navbar -->
        <x-shell.header title="Dive Planner" icon="timer" :h1="true" />
        <!-- End Navbar -->
        <div class="container-fluid py-0">

            <div class="d-none" data-color="info" id="sidebarColorDiv"></div>

            <!-- Material Symbols Rounded - a separate icon font from the
                 classic "Material Icons Round" loaded site-wide, needed just
                 for the What-if bubble's "Question Exchange" icon (Pablo,
                 2026-09-19), which doesn't exist in the classic set. Scoped
                 to this page only rather than added to the shared layout. -->
            <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=block">

            <style>
                .modal {
                z-index: 10050; /* Adjust this value to be higher than the sidebar's z-index */
                }

                .label-container {
                    display: flex;          /* Enable Flexbox layout */
                    justify-content: space-between; /* Push labels to opposite ends */
                    align-items: center;    /* Ensure vertical alignment */
                }

                .left-label {
                    text-align: left;       /* Align text on the left */
                    margin-right: auto;     /* Push it to the far left */
                    border: 2px solid #49a3f1; /* Add box for the label */
                    padding: 5px;           /* Add padding inside the box */
                    font-weight: bold;      /* Make the text bold */
                    border-radius: 4px;     /* Optional: Round the corners */
                }

                .left-label-white {
                    text-align: left;       /* Align text on the left */
                    margin-right: auto;     /* Push it to the far left */
                    border: 2px solid #ffffff; /* Add box for the label */
                    padding: 5px;           /* Add padding inside the box */
                    font-weight: bold;      /* Make the text bold */
                    border-radius: 4px;     /* Optional: Round the corners */
                }

                .right-label-normal {
                    text-align: right;      /* Align text on the right */
                    border: 2px solid #49a3f1; /* Add box for the label */
                    padding: 5px;           /* Add padding inside the box */
                    font-weight: bold;      /* Make the text bold */
                    border-radius: 4px;     /* Optional: Round the corners */
                    margin-left: auto;      /* Push it to the far right */
                }

                .right-label-normal-white {
                    text-align: right;      /* Align text on the right */
                    border: 2px solid #ffffff; /* Add box for the label */
                    padding: 5px;           /* Add padding inside the box */
                    font-weight: bold;      /* Make the text bold */
                    border-radius: 4px;     /* Optional: Round the corners */
                    margin-left: auto;      /* Push it to the far right */
                }

                .right-label-freeze {
                    text-align: right;      /* Align text on the right */
                    border: 2px solid #7b809a; /* Add box for the label */
                    padding: 5px;           /* Add padding inside the box */
                    font-weight: bold;      /* Make the text bold */
                    border-radius: 4px;     /* Optional: Round the corners */
                    margin-left: auto;      /* Push it to the far right */
                }

                .right-label-warning {
                    text-align: right;      /* Align text on the right */
                    border: 2px solid #fb8c00; /* Add box for the label */
                    padding: 5px;           /* Add padding inside the box */
                    font-weight: bold;      /* Make the text bold */
                    border-radius: 4px;     /* Optional: Round the corners */
                    margin-left: auto;      /* Push it to the far right */
                }
                .right-label-danger {
                    text-align: right;      /* Align text on the right */
                    border: 2px solid #f44335; /* Add box for the label */
                    padding: 5px;           /* Add padding inside the box */
                    font-weight: bold;      /* Make the text bold */
                    border-radius: 4px;     /* Optional: Round the corners */
                    margin-left: auto;      /* Push it to the far right */
                }
                .right-label-success {
                    text-align: right;      /* Align text on the right */
                    border: 2px solid #4caf50; /* Add box for the label */
                    padding: 5px;           /* Add padding inside the box */
                    font-weight: bold;      /* Make the text bold */
                    border-radius: 4px;     /* Optional: Round the corners */
                    margin-left: auto;      /* Push it to the far right */
                }
                .right-label-secondary {
                    text-align: right;      /* Align text on the right */
                    border: 2px solid #7b809a; /* Add box for the label */
                    padding: 5px;           /* Add padding inside the box */
                    font-weight: bold;      /* Make the text bold */
                    border-radius: 4px;     /* Optional: Round the corners */
                    margin-left: auto;      /* Push it to the far right */
                }

                .noUi-tick {
                    display: none;
                }

                .table {
                    width: 100%;
                    table-layout: fixed; /* Ensures column widths are respected */
                }

                .phase-column {
                    width: 10%; /* Set phase column width explicitly */
                    white-space: nowrap; /* Prevents text wrapping */
                    overflow: hidden;
                }

                .depth-column {
                    width: 15%; /* Set phase column width explicitly */
                    white-space: nowrap; /* Prevents text wrapping */
                    overflow: hidden;
                }

                th {
                    text-align: right; /* Aligns all table headers to the left */
                    padding-right: 0;
                }
                td {
                    vertical-align: middle; /* Ensures content is centered vertically */
                    text-align: right;
                }

                .dropdown-menu {
                    max-height: 300px; /* Adjust height as needed */
                    overflow-y: auto; /* Enables vertical scrolling */
                }

                #dropdownSearch {
                    border: none; /* Removes default borders */
                    border-bottom: 2px solid #49a3f1; /* Adds blue underline */
                    outline: none; /* Removes default focus outline */
                    padding: 5px; /* Adds spacing */
                }

                /*.table th, .table td {
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }*/

                .table-responsive {
                    overflow-x: auto;
                }

                @media (max-width: 768px) {
                    .hide-on-mobile {
                        display: none;
                    }
                    /* PPO2 used to be hide-on-mobile too, hiding it entirely on
                       phones (Pablo, 2026-09-26: "the deco table is hiding the
                       PPO2. You need to show it...squeeze the gas column to
                       make it fit") - it's no longer given that class (see
                       dhRenderDecoTableV2), and Gas gets narrower here, on
                       phones only, to make room. Desktop widths (set inline
                       per table) are untouched. */
                    .dh-decotable-gas-col { width: 24% !important; }
                    .dh-decotable-ppo2-col { width: 28% !important; }

                    /* Safety warning pills (high PPO2/END) go full-width on
                       phones instead of squeezing two into one row (Pablo,
                       2026-09-26: "in mobile let them be col-12"). The
                       container needs flex-wrap too - flex-basis:100% alone
                       just shrinks every pill to fit one row instead of
                       stacking them (nowrap is the flex default). */
                    #dhSafetyWarningsContainer {
                        flex-wrap: wrap;
                    }
                    #dhSafetyWarningsContainer .dh-deco-summary-pill {
                        flex: 1 1 100%;
                    }
                }

                #tissueChart {
                    height: 100px !important; /* Forces the height */
                    width: 100%;
                }

                

                #tissueChart {
                    /* Boundaries match the chart's own data scale exactly -
                       100/300 (ambient) and 295/300 (M-value) - rather than
                       the eyeballed 33%/95% this used to be (Pablo,
                       2026-09-25: "extend the green/yellow boundary to that
                       exact position...isn't that what the boundary is
                       supposed to be?"). The separate dashed "Ambient" line
                       annotation is gone now that the background itself
                       marks the real boundary precisely. */
                    background: linear-gradient(to right,
                                                #72D550 0% 33.3333%,
                                                #FBFF5F 33.3333% 98.3333%,
                                                #B7211C 98.3333% 100%);
                    border-radius: 0px; /* Optional rounded corners */
                }

                .dive-planner-header {
                    display: flex;
                    align-items: center; /* Vertically align the items */
                    justify-content: space-between; /* Push elements apart */
                }
                .UnitDropdown {
                    width: 150px; /* Adjust width to make it small */
                    margin-left: auto; /* Push it to the right */
                }



                

            </style>

            {{--modal warning--}}
            <div class="modal fade" id="modalWarning" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="modal-notification" aria-hidden="true">
                <div class="modal-dialog modal-danger modal-dialog-centered modal-" role="document">
                    <div class="modal-content">
                        <div class="modal-header text-center">
                            <h6 class="modal-title font-weight-normal" id="modal-title-notification">Warning</h6>
                            
                        </div>
                        <div class="modal-body">
                            <div class="py-3 text-center">
                            <i class="material-icons h1 text-primary">
                                warning
                            </i>
                            <h4 class="text-gradient text-danger text-sm mt-4" style="text-align: justify;">
                                
                                <p><strong>Diving, especially decompression diving, is a risky activity. </strong>The calculations provided by this tool are based on mathematical models and theoretical decompression algorithms. While they can help estimate decompression profiles, they do not guarantee prevention of decompression sickness (DCS) or other diving-related hazards.</p>

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

            {{-- Confirms saving a gas card's current O2/He as a custom gas
                 (Pablo, 2026-09-18: "add a small save icon where a modal
                 will show up and confirm he wants to save the gas"). One
                 shared modal for every gas card - dhSaveGasTargetSlot (set
                 when the save icon is clicked) says which slot's O2/He to
                 read and save. --}}
            <div class="modal fade" id="modalSaveGas" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h6 class="modal-title font-weight-normal">Save to My Gases</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center">
                            <p>Save <strong id="modalSaveGasMix">-</strong> as a custom gas?</p>
                            <p class="text-danger" id="modalSaveGasError" hidden></p>
                        </div>
                        <div class="modal-footer justify-content-center">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-info" id="modalSaveGasConfirm">Save</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- "My Gases" picker - up to 10 custom gas mixes a registered
                 diver saved from any gas card, reusable on any other one
                 (Pablo, 2026-09-18). dhMyGasesTargetSlot (set when a "My
                 Gases" pill is clicked) says which slot's sliders to drive
                 when one is picked. --}}
            <div class="modal fade" id="modalMyGases" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h6 class="modal-title font-weight-normal">My Gases</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="dh-mygases-empty" id="modalMyGasesEmpty" hidden>You haven't saved any custom gases yet - use the save icon on any gas card.</div>
                            <div id="modalMyGasesList"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Names a plan before it's saved (Pablo, 2026-09-19: "open a
                 modal to give the dive a name and save it with a date time
                 stamp") - replaces the earlier native prompt(). The
                 timestamp itself is just the row's created_at, set server-side. --}}
            <div class="modal fade" id="modalSaveDecoPlan" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h6 class="modal-title font-weight-normal">Save this dive</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <label class="dh-gas-label" for="modalSaveDecoPlanLabel">Name (optional)</label>
                            <input type="text" class="form-control" id="modalSaveDecoPlanLabel" placeholder="e.g. Hydro Atlantic - 180ft trimix" maxlength="255">
                            <p class="text-danger mt-2" id="modalSaveDecoPlanError" hidden></p>
                        </div>
                        <div class="modal-footer justify-content-center">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-info" id="modalSaveDecoPlanConfirm">Save</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Advanced settings (Pablo, 2026-09-25) - registered users
                 only (the gear button that opens this is itself hidden for
                 guests). Both toggles always come back to their safe
                 default (guardrails on) on a fresh page load - neither is
                 persisted. --}}
            @if(auth()->user()->isNotGuest())
            <div class="modal fade" id="modalAdvancedSettings" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h6 class="modal-title font-weight-normal">Advanced settings</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert d-flex align-items-start" role="alert" style="gap: 8px; font-size: .82rem; background: var(--dh-danger); color: #fff; border: none;">
                                <span class="material-icons-round" aria-hidden="true" style="color: #fff;">warning</span>
                                <span>Turning off safety guardrails can let you configure dangerous diving conditions. Only change these if you understand the risk.</span>
                            </div>

                            {{-- Square checkboxes, not pill toggles (Pablo,
                                 2026-10-09: "replace the toggles...by the
                                 square checkboxes we use everywhere else...
                                 the toggles are from the old front end") -
                                 .dh-check is the same reusable class the
                                 group settings modal and comms preferences
                                 use (see its definition in divershub.css). --}}
                            <div class="form-check mb-3">
                                <input class="form-check-input dh-check" type="checkbox" id="dhToggleRecommendGases" checked>
                                <label class="form-check-label text-body mb-0" for="dhToggleRecommendGases">
                                    <span class="dh-check-title">Recommend best gases</span>
                                    <div class="text-secondary text-xs">Auto-fill bottom gas, diluent and bailout as you move the depth sliders.</div>
                                </label>
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input dh-check" type="checkbox" id="dhToggleRemoveGuards">
                                <label class="form-check-label text-body mb-0" for="dhToggleRemoveGuards">
                                    <span class="dh-check-title">Remove safety guards</span>
                                    <div class="text-secondary text-xs">Allow diluent PPO&#8322; up to 2.0 ATA and fully independent GF Low/High.</div>
                                </label>
                            </div>

                            {{-- Off by default (Pablo, 2026-09-26: "this is a feature that
                                 will require a lot of testing and we are launching in two
                                 days") - the Tanks card and every gas-limited-bottom-time
                                 pill/overlay it drives are all still brand new this same
                                 week. Unchecked here matches #dhTanksCardRow's own hardcoded
                                 `hidden` attribute below, so there's no flash of the card
                                 before this modal's IIFE runs. --}}
                            <div class="form-check mb-3">
                                <input class="form-check-input dh-check" type="checkbox" id="dhToggleShowTanks">
                                <label class="form-check-label text-body mb-0" for="dhToggleShowTanks">
                                    <span class="dh-check-title">Show tanks &amp; gas-limited bottom time</span>
                                    <div class="text-secondary text-xs">Preview feature - tank/SAC inputs and the gas-limited warning pill, hidden until this has had more real-world testing.</div>
                                </label>
                            </div>

                            {{-- Off by default (Pablo, 2026-10-09). Sent to the
                                 deco API as part of the dive profile payload -
                                 see dhLastStopAt20ft in deco-planner.js. --}}
                            <div class="form-check">
                                <input class="form-check-input dh-check" type="checkbox" id="dhToggleLastStop20">
                                <label class="form-check-label text-body mb-0" for="dhToggleLastStop20">
                                    <span class="dh-check-title">Last stop at 20 ft</span>
                                    <div class="text-secondary text-xs">Round the shallowest stop to 20 ft instead of the default last-stop depth.</div>
                                </label>
                            </div>
                        </div>
                        <div class="modal-footer justify-content-center">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- How to overlay a real dive log (Pablo, 2026-09-22), opened from
                 the small "?" next to "Overlay your actual dive log" below the
                 profile chart. Static instructions, no JS state needed beyond
                 Bootstrap's own data-bs-toggle. --}}
            <div class="modal fade" id="uddfHelpModal" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h6 class="modal-title font-weight-normal">Overlay your actual dive</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>You can now overlay your actual dive with the plan:</p>
                            <ol class="ps-3 mb-3">
                                <li class="mb-1">Go to your Shearwater Cloud Desktop or MacDive</li>
                                <li class="mb-1">Export the dive as a UDDF</li>
                                <li>Click on "Overlay your actual dive log"</li>
                            </ol>
                            <p class="mb-3">You can now compare how the actual dive went against what you planned.</p>
                            <div class="d-flex align-items-center gap-3">
                                <img src="{{ asset('assets') }}/img/shearwater-cloud-logo.png" alt="Shearwater Cloud" style="width: 36px; height: 36px; border-radius: 8px;">
                                <img src="{{ asset('assets') }}/img/macdive-logo.webp" alt="MacDive" style="width: 36px; height: 36px; border-radius: 8px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- "Open a dive" (Pablo, 2026-09-19): lists every plan the diver
                 has saved so one can be reloaded (all inputs restored + the
                 calculation re-run) or removed. --}}
            <div class="modal fade" id="modalOpenDive" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h6 class="modal-title font-weight-normal">Open a dive</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="dh-mygases-empty" id="modalOpenDiveEmpty" hidden>You haven't saved any dives yet - use "Save Plan" on a calculated decompression plan first.</div>
                            <div id="modalOpenDiveList"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dh-panel-head-row mb-3">
                <h2 class="dh-panel-title mb-0">Decompression Dive Planner</h2>
                <div class="d-flex align-items-center" style="gap: 8px;">
                    @if(!is_null($currentSite) && $deco_unit)
                        <a class="dh-btn dh-btn-ghost-dark" id="switchUnits" href="{{ route('DecoPlannerImperial') }}/{{ $currentSite->id }}">Switch to Imperial</a>
                    @elseif(is_null($currentSite) && $deco_unit)
                        <a class="dh-btn dh-btn-ghost-dark" id="switchUnits" href="{{ route('DecoPlannerImperial') }}">Switch to Imperial</a>
                    @elseif(!is_null($currentSite) && !$deco_unit)
                        <a class="dh-btn dh-btn-ghost-dark" id="switchUnits" href="{{ route('DecoPlannerMetric') }}/{{ $currentSite->id }}">Switch to Metric</a>
                    @else
                        <a class="dh-btn dh-btn-ghost-dark" id="switchUnits" href="{{ route('DecoPlannerMetric') }}">Switch to Metric</a>
                    @endif
                    {{-- Advanced settings - moved out of the Inputs card to sit next
                         to Switch to Metric/Imperial instead (Pablo, 2026-09-27:
                         "put it to the right of switch to metric button - not
                         inside the input card"). Registered users only, not even
                         shown to guests (Pablo, 2026-09-25: "This is ONLY available
                         for registered users. For guest, don't even show the gear
                         icon"), unlike the lock-badge treatment used for Multi
                         Level/Export PDF - these settings can create genuinely
                         dangerous configurations, so there's no upsell value in
                         advertising them to a guest. --}}
                    @if(auth()->user()->isNotGuest())
                        <button type="button" class="dh-gear-btn" id="dhAdvancedSettingsBtn" title="Advanced settings" aria-label="Advanced settings">
                            <span class="material-icons-round" aria-hidden="true">settings</span>
                        </button>
                    @endif
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 m-auto">
                    <div class="card p-0 position-relative mt-3 z-index-2 mb-4" id="dh-deco-inputs-card">
                        <div class="dh-calc-inputs-head" id="dh-deco-inputs-toggle" role="button" tabindex="0" aria-expanded="true" aria-controls="dh-deco-inputs-body">
                            <span class="material-icons-round" aria-hidden="true">tune</span>
                            <h3>Inputs</h3>
                            {{-- Clears every input back to a fresh page load - depth, gases,
                                 mode, everything - except a saved GF/setpoint preference, which
                                 comes back the same way it does on a real refresh (Pablo,
                                 2026-09-26: "reset all the inputs to standard...if the user has
                                 saved its own setpoint and GFs we bring those up, but we clear
                                 depth, gases, etc...like we just refresh the page"). A real
                                 location.reload() - re-rendering from the server - is the only
                                 way to guarantee that "like a refresh" promise instead of hand-
                                 resetting dozens of sliders/toggles and risking missing one. --}}
                            <button type="button" class="dh-btn-icon dh-btn-icon-accent" id="dhResetInputsBtn" title="Reset all inputs" aria-label="Reset all inputs" style="margin-left: auto;">
                                <span class="material-icons-round" aria-hidden="true">restart_alt</span>
                            </button>
                            <span class="dh-calc-inputs-hint" id="dh-deco-inputs-hint" hidden>Tap to edit</span>
                            <span class="material-icons-round dh-calc-inputs-chevron" id="dh-deco-inputs-chevron" aria-hidden="true">expand_less</span>
                        </div>

                        <div class="card-body" id="dh-deco-inputs-body">
                            <div class="row">
                                <div class="col-12 d-flex align-items-center flex-wrap" style="gap: 8px;">
                                    <div class="dh-channel-picker dh-gas-picker" id="nav-tabs" style="margin-bottom: 0;">
                                        <button type="button" class="dh-channel-chip is-active" data-tag="OC">Open Circuit</button>
                                        <button type="button" class="dh-channel-chip" data-tag="CC">Close Circuit CCR</button>
                                    </div>
                                    {{-- Loads a previously saved plan's exact inputs and re-runs the
                                         calculation (Pablo, 2026-09-19: "a button to Open a dive in the
                                         input card in line with Open Circuit and Close Circuit CCR
                                         aligned to the right"). Same guest gating as Save Plan - there's
                                         nothing to list without a real profile. --}}
                                    @if(auth()->user()->isNotGuest())
                                        <button type="button" class="dh-channel-chip dh-deco-searchrow-pill" id="openDivePlanBtn" style="flex: 0 0 auto; margin-left:auto;" title="Load a previously saved dive plan">
                                            <span class="material-icons-round" aria-hidden="true" style="font-size: 15px; vertical-align: -3px;">folder_open</span>
                                            Open a dive
                                        </button>
                                    @endif
                                </div>
                            </div>
                            {{-- Multi-level dive planning (Pablo, 2026-09-25): a second pill
                                 row, independent of OC/CC, switching between the existing
                                 single-depth planner (untouched) and up to 4 depth/time
                                 levels. Front-end only for now - not wired to a
                                 MultiLevelDivePlanner call yet, so Calculate still runs the
                                 single-level flow regardless of this toggle. --}}
                            <div class="row mt-2">
                                <div class="col-12 d-flex align-items-center flex-wrap" style="gap: 8px;">
                                    <div class="dh-channel-picker dh-gas-picker" id="nav-tabs-level" style="margin-bottom: 0;">
                                        <button type="button" class="dh-channel-chip is-active" data-level-tag="single" id="singleLevelTab">Single Level</button>
                                        {{-- Guests can plan single-level dives freely, but multi-level
                                             (and PDF export, see the Export PDF button below) are
                                             registered-account features - a visible lock badge rather
                                             than hiding the tab outright, so guests discover it exists
                                             (Pablo, 2026-09-25: "locking the multilevel to registered
                                             users only...a small badge with a lock"). --}}
                                        <button type="button" class="dh-channel-chip @if(auth()->user()->isGuest()) is-locked @endif" data-level-tag="multi" id="multiLevelTab">
                                            Multi Level
                                            @if(auth()->user()->isGuest())
                                                <span class="material-icons-round" aria-label="Account required" style="font-size: 14px; margin-left: 4px; vertical-align: -2px;">lock</span>
                                            @endif
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-12 d-flex align-items-center flex-wrap" style="gap: 8px;">
                                    <div class="dropdown d-inline-flex align-items-center" id="decoSiteSearchWrap" style="gap: 8px;">
                                        {{-- One button doing both jobs instead of a button + a separate
                                             result pill next to it (Pablo, 2026-09-19: "replace the pill
                                             Search a dive with the result...add a magnifier glass on that
                                             pill. If the user clicks again, you open again the search
                                             dropdown...save space...prevent the save GFs pill to
                                             overflow"). data-bs-toggle="dropdown" stays on it either way,
                                             so Bootstrap's own toggle behavior reopens the menu on a
                                             second click with no extra JS. #dropdownMenuButtonContent is
                                             swapped between the two states by dhResetSiteSearchButton()/
                                             the dropdown-item click handler below. --}}
                                        <button type="button" class="dh-channel-chip dh-deco-searchrow-pill" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                            <span id="dropdownMenuButtonContent">
                                                <span class="material-icons-round" aria-hidden="true" style="font-size: 15px; vertical-align: -3px;">search</span>
                                                Search dive sites
                                            </span>
                                        </button>
                                        <ul class="dropdown-menu" id="dropdownMenu">
                                            <li>
                                                <input type="text" class="form-control mb-2" id="dropdownSearch" placeholder="Search..." style="display: block;">
                                            </li>
                                            <?php if (!is_null($currentSite)):
                                                $currentSiteLevel = \App\Support\DiveLevel::get($currentSite->level ?? null);
                                            ?>
                                                <li><a class="dropdown-item" href="#" data-depth="<?php echo htmlspecialchars($currentSite->maxDepth / ($deco_unit ? 3.28 : 1)); ?>" data-site-name="<?php echo htmlspecialchars($currentSite->name); ?>" data-level-icon="<?php echo $currentSiteLevel ? asset('assets') . '/' . $currentSiteLevel['icon'] : ''; ?>" data-level-name="<?php echo $currentSiteLevel ? htmlspecialchars($currentSiteLevel['code']) : ''; ?>">
                                                    Use <?php echo htmlspecialchars($currentSite->name); ?> depth
                                                </a></li>
                                            <?php endif; ?>
                                            <?php foreach ($allSites as $site):
                                                $siteLevel = \App\Support\DiveLevel::get($site->level ?? null);
                                            ?>
                                                <li><a class="dropdown-item" href="#" data-depth="<?php echo htmlspecialchars($site->maxDepth / ($deco_unit ? 3.28 : 1) ); ?>" data-site-name="<?php echo htmlspecialchars($site->name); ?>" data-level-icon="<?php echo $siteLevel ? asset('assets') . '/' . $siteLevel['icon'] : ''; ?>" data-level-name="<?php echo $siteLevel ? htmlspecialchars($siteLevel['code']) : ''; ?>">
                                                    <?php echo htmlspecialchars($site->name) . " (" . htmlspecialchars($site->type) . ")"; ?>
                                                </a></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    {{-- Saves GF Low/High + setpoint to the diver's profile so the
                                         planner remembers them next visit (Pablo, 2026-09-18) - a
                                         standalone pill on the Search dive sites row, aligned right
                                         (Pablo, 2026-09-19: "They need to be new pills. Put them at the
                                         line with search dive sites aligned to the right"), not icons
                                         embedded in the OC/CC pills. Guest-hidden entirely (Pablo,
                                         2026-09-25: "if the user is guest, do not show the Save GFs or
                                         Save GFs and Setpoint") - there's no profile to save to, so this
                                         reverses the earlier "always visible, guests get a message on
                                         click" call from 2026-09-19. The click handler below already
                                         null-checks the element, so guests simply never wire it up. --}}
                                    @if(auth()->user()->isNotGuest())
                                        <button type="button" class="dh-channel-chip dh-deco-searchrow-pill" id="saveDecoPrefsPill" style="flex: 0 0 auto; margin-left:auto;" title="Save GF Low/High and setpoint to your profile">
                                            <span class="material-icons-round" aria-hidden="true" style="font-size: 15px; vertical-align: -3px;">bookmark_border</span>
                                            {{-- Setpoint only applies to CC (OC has no CCR setpoint) - label
                                                 matches whichever the diver is currently looking at (Pablo,
                                                 2026-09-19: "the pill in OC needs to say Save GFs, the one in
                                                 CC is correct as is"). showOpenCircuit/showClosedCircuit swap
                                                 this text; default here matches the page's default OC mode. --}}
                                            <span id="saveDecoPrefsPillLabel">Save GFs</span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                            <div class="row mt-3 dh-deco-input-divider" style="padding-bottom: 10px;" id="singleLevelDepthRow">
                                <div class="col-12">
                                    <label class="dh-gas-label" for="labelDepth" id="maxDepthSliderTitle">Max Depth (ft)</label>
                                    <div class="dh-gas-row">
                                        <div id="maxDepthContainerImp" class="dh-gas-input-wrap">
                                            <div class="dh-gas-editable">
                                                <input type="text" inputmode="numeric" class="dh-gas-input" id="labelDepth" value="100">
                                                <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                            </div>
                                            <span class="dh-gas-unit" id="maxDepthInputLabel">ft</span>
                                        </div>
                                        <div id="maxDepthContainerMet" class="dh-gas-input-wrap">
                                            <div class="dh-gas-editable">
                                                <input type="text" inputmode="numeric" class="dh-gas-input" id="labelDepthMET" value="30">
                                                <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                            </div>
                                            <span class="dh-gas-unit">m</span>
                                        </div>
                                        <input type="hidden" id="depthSlider-value" name="depthSlider-value">
                                        <div class="slider-styled" id="depthSlider" data-dh-num-mirror="1"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row" id="containerSetPoint" style="display: none;">
                                <div class="col-lg-12 col-12 dh-deco-input-divider" style="padding-bottom: 10px;">
                                    <table class="table align-items-center mb-0 mt-1"> 
                                        <tr><td class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center" style="border: none;">CCR Setpoint</td> </tr>
                                    </table>

                                    <div class="mt-0">
                                        <input type="hidden" id="setpointSlider-value" name="setPointSlider-value">

                                        <label class="dh-gas-label" for="labelSetpoint">Setpoint</label>
                                        <div class="dh-gas-row">
                                            <div class="dh-gas-input-wrap">
                                                <div class="dh-gas-editable">
                                                    <input type="text" inputmode="decimal" class="dh-gas-input is-safe" id="labelSetpoint" value="1.3">
                                                    <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                </div>
                                                <span class="dh-gas-unit">atm</span>
                                            </div>
                                            <div class="slider-styled" id="setpointSlider" data-dh-num-mirror="1"></div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <!-- Row times, gradients and asc/des rates -->
                            <div class="row">
                                <div class="col-lg-4 col-12 dh-deco-input-divider" id="singleLevelTimingCol">
                                    <table class="table align-items-center mb-0 mt-1">
                                        <tr><td class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center" style="border: none;">Set Bottom time</td> </tr>
                                    </table>
                                    <div class="mt-0">
                                        <input type="hidden" id="bottomTimeSlider-value" name="bottomTimeSlider-value">

                                        <label class="dh-gas-label" for="labelBottomTime">Bottom time</label>
                                        <div class="dh-gas-row">
                                            <div class="dh-gas-input-wrap">
                                                <div class="dh-gas-editable">
                                                    <input type="text" inputmode="numeric" class="dh-gas-input" id="labelBottomTime" value="25">
                                                    <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                </div>
                                                <span class="dh-gas-unit">min</span>
                                            </div>
                                            <div class="slider-styled" id="bottomTimeSlider" data-dh-num-mirror="1"></div>
                                        </div>
                                    </div>

                                    <div class="mt-3 mb-2">
                                        <label class="dh-gas-label" for="labelSurfaceTime">Surface time</label>
                                        <input type="hidden" id="surfaceTimeSlider-value" name="surfaceTimeSlider-value">
                                        <div class="dh-gas-row">
                                            <div class="dh-gas-input-wrap">
                                                <div class="dh-gas-editable">
                                                    <input type="text" inputmode="decimal" class="dh-gas-input" id="labelSurfaceTime" value="1">
                                                </div>
                                                <span class="dh-gas-unit">hrs</span>
                                            </div>
                                            <div class="slider-styled" id="surfaceTimeSlider" data-dh-num-mirror="1"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Col gradients -->
                                <div class="col-lg-4 col-12 dh-deco-input-divider" id="gfCol">
                                    <table class="table align-items-center mb-0 mt-1"> 
                                        <tr><td class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center" style="border: none;">Set Gradient Factors</td> </tr>
                                    </table>
                                    <div class="mt-0">
                                        <input type="hidden" id="GFLTimeSlider-value" name="GFLSlider-value">

                                        <label class="dh-gas-label" for="labelGFL">GF Low</label>
                                        <div class="dh-gas-row">
                                            <div class="dh-gas-input-wrap">
                                                <div class="dh-gas-editable">
                                                    <input type="text" inputmode="numeric" class="dh-gas-input" id="labelGFL" value="50">
                                                    <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                </div>
                                                <span class="dh-gas-unit">%</span>
                                            </div>
                                            <div class="slider-styled" id="GFLSlider" data-dh-num-mirror="1"></div>
                                        </div>
                                    </div>

                                    <div class="mt-3 mb-2">
                                        <input type="hidden" id="GFHSlider-value" name="GFHSlider-value">

                                        <label class="dh-gas-label" for="labelGFH">GF High</label>
                                        <div class="dh-gas-row">
                                            <div class="dh-gas-input-wrap">
                                                <div class="dh-gas-editable">
                                                    <input type="text" inputmode="numeric" class="dh-gas-input" id="labelGFH" value="75">
                                                    <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                </div>
                                                <span class="dh-gas-unit">%</span>
                                            </div>
                                            <div class="slider-styled" id="GFHSlider" data-dh-num-mirror="1"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Col ascent descent -->
                                <div class="col-lg-4 col-12 dh-deco-input-divider" id="rateCol">
                                    <table class="table align-items-center mb-0 mt-1"> 
                                        <tr><td class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center" style="border: none;">Set des/asc rates</td> </tr>
                                    </table>
                                    <div class="mt-0">
                                        <input type="hidden" id="desSlider-value" name="desSlider-value">

                                        <label class="dh-gas-label">Descend rate</label>
                                        <div class="dh-gas-row">
                                            <div id="desRateContainerImp" class="dh-gas-input-wrap">
                                                <div class="dh-gas-editable">
                                                    <input type="text" inputmode="numeric" class="dh-gas-input" id="labelDes" value="60">
                                                    <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                </div>
                                                <span class="dh-gas-unit">ft/min</span>
                                            </div>

                                            <div id="desRateContainerMet" class="dh-gas-input-wrap">
                                                <div class="dh-gas-editable">
                                                    <input type="text" inputmode="numeric" class="dh-gas-input" id="labelDesMET" value="18">
                                                    <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                </div>
                                                <span class="dh-gas-unit">m/min</span>
                                            </div>

                                            <div class="slider-styled" id="desSlider" data-dh-num-mirror="1"></div>
                                        </div>
                                    </div>

                                    <div class="mt-3 mb-2">
                                        <input type="hidden" id="ascSlider-value" name="ascSlider-value">

                                        <label class="dh-gas-label">Ascend rate</label>
                                        <div class="dh-gas-row">
                                            <div id="ascRateContainerImp" class="dh-gas-input-wrap">
                                                <div class="dh-gas-editable">
                                                    <input type="text" inputmode="numeric" class="dh-gas-input" id="labelAsc" value="30">
                                                    <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                </div>
                                                <span class="dh-gas-unit">ft/min</span>
                                            </div>

                                            <div id="ascRateContainerMet" class="dh-gas-input-wrap">
                                                <div class="dh-gas-editable">
                                                    <input type="text" inputmode="numeric" class="dh-gas-input" id="labelAscMET" value="9">
                                                    <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                </div>
                                                <span class="dh-gas-unit">m/min</span>
                                            </div>

                                            <div class="slider-styled" id="ascSlider" data-dh-num-mirror="1"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Multi-level depth/time panels - hidden until "Multi Level" is
                                 picked above. 4 fixed slots pre-rendered and shown/hidden with
                                 "hidden", same on-demand pattern as the gas accordion's Add gas
                                 (dhNextGasSlot/showDecoGasN) rather than cloning DOM at runtime.
                                 Level 1 is the always-present base level (no delete button),
                                 matching the gas accordion's non-removable first slot. New
                                 levels are added to the RIGHT of Level 1 on desktop (Pablo,
                                 2026-09-25) and stack top-to-bottom in that same order on
                                 phones - both just fall out of plain DOM/source order, no CSS
                                 order utilities needed. --}}
                            <div class="row mt-2" id="multiLevelRow" style="display: none;">
                                <div class="col-12 mb-2">
                                    <span class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Set Levels</span>
                                </div>

                                <div class="col-lg-3 col-12" id="levelPanel1">
                                  <div class="dh-level-panel">
                                    <table class="table align-items-center mb-0 mt-1">
                                        <tr><td class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center" style="border: none;">Level 1</td></tr>
                                    </table>
                                    <div class="mt-0">
                                        <label class="dh-gas-label" for="labelDepthLevel1" id="depthLevel1Title">Depth (ft)</label>
                                        <div class="dh-gas-row">
                                            <div id="maxDepthContainerImpLevel1" class="dh-gas-input-wrap">
                                                <div class="dh-gas-editable">
                                                    <input type="text" inputmode="numeric" class="dh-gas-input" id="labelDepthLevel1" value="100">
                                                    <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                </div>
                                                <span class="dh-gas-unit">ft</span>
                                            </div>
                                            <div id="maxDepthContainerMetLevel1" class="dh-gas-input-wrap">
                                                <div class="dh-gas-editable">
                                                    <input type="text" inputmode="numeric" class="dh-gas-input" id="labelDepthLevel1MET" value="30">
                                                    <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                </div>
                                                <span class="dh-gas-unit">m</span>
                                            </div>
                                            <input type="hidden" id="depthSliderLevel1-value" name="depthSliderLevel1-value">
                                            <div class="slider-styled" id="depthSliderLevel1" data-dh-num-mirror="1"></div>
                                        </div>
                                    </div>
                                    <div class="mt-3 mb-2">
                                        <label class="dh-gas-label" for="labelBottomTimeLevel1">Bottom time</label>
                                        <input type="hidden" id="bottomTimeSliderLevel1-value" name="bottomTimeSliderLevel1-value">
                                        <div class="dh-gas-row">
                                            <div class="dh-gas-input-wrap">
                                                <div class="dh-gas-editable">
                                                    <input type="text" inputmode="numeric" class="dh-gas-input" id="labelBottomTimeLevel1" value="25">
                                                    <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                </div>
                                                <span class="dh-gas-unit">min</span>
                                            </div>
                                            <div class="slider-styled" id="bottomTimeSliderLevel1" data-dh-num-mirror="1"></div>
                                        </div>
                                    </div>
                                  </div>
                                </div>

                                <div class="col-lg-3 col-12" id="levelPanel2" hidden>
                                  <div class="dh-level-panel">
                                    <button type="button" class="dh-corner-delete" id="level2DeleteButton" onclick="hideLevel2()" aria-label="Delete level 2">
                                        <span class="material-icons-round" aria-hidden="true">delete</span>
                                    </button>
                                    <table class="table align-items-center mb-0 mt-1">
                                        <tr><td class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center" style="border: none;">Level 2</td></tr>
                                    </table>
                                    <div class="mt-0">
                                        <label class="dh-gas-label" for="labelDepthLevel2" id="depthLevel2Title">Depth (ft)</label>
                                        <div class="dh-gas-row">
                                            <div id="maxDepthContainerImpLevel2" class="dh-gas-input-wrap">
                                                <div class="dh-gas-editable">
                                                    <input type="text" inputmode="numeric" class="dh-gas-input" id="labelDepthLevel2" value="80">
                                                    <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                </div>
                                                <span class="dh-gas-unit">ft</span>
                                            </div>
                                            <div id="maxDepthContainerMetLevel2" class="dh-gas-input-wrap">
                                                <div class="dh-gas-editable">
                                                    <input type="text" inputmode="numeric" class="dh-gas-input" id="labelDepthLevel2MET" value="24">
                                                    <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                </div>
                                                <span class="dh-gas-unit">m</span>
                                            </div>
                                            <input type="hidden" id="depthSliderLevel2-value" name="depthSliderLevel2-value">
                                            <div class="slider-styled" id="depthSliderLevel2" data-dh-num-mirror="1"></div>
                                        </div>
                                    </div>
                                    <div class="mt-3 mb-2">
                                        <label class="dh-gas-label" for="labelBottomTimeLevel2">Bottom time</label>
                                        <input type="hidden" id="bottomTimeSliderLevel2-value" name="bottomTimeSliderLevel2-value">
                                        <div class="dh-gas-row">
                                            <div class="dh-gas-input-wrap">
                                                <div class="dh-gas-editable">
                                                    <input type="text" inputmode="numeric" class="dh-gas-input" id="labelBottomTimeLevel2" value="15">
                                                    <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                </div>
                                                <span class="dh-gas-unit">min</span>
                                            </div>
                                            <div class="slider-styled" id="bottomTimeSliderLevel2" data-dh-num-mirror="1"></div>
                                        </div>
                                    </div>
                                  </div>
                                </div>

                                <div class="col-lg-3 col-12" id="levelPanel3" hidden>
                                  <div class="dh-level-panel">
                                    <button type="button" class="dh-corner-delete" id="level3DeleteButton" onclick="hideLevel3()" aria-label="Delete level 3">
                                        <span class="material-icons-round" aria-hidden="true">delete</span>
                                    </button>
                                    <table class="table align-items-center mb-0 mt-1">
                                        <tr><td class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center" style="border: none;">Level 3</td></tr>
                                    </table>
                                    <div class="mt-0">
                                        <label class="dh-gas-label" for="labelDepthLevel3" id="depthLevel3Title">Depth (ft)</label>
                                        <div class="dh-gas-row">
                                            <div id="maxDepthContainerImpLevel3" class="dh-gas-input-wrap">
                                                <div class="dh-gas-editable">
                                                    <input type="text" inputmode="numeric" class="dh-gas-input" id="labelDepthLevel3" value="60">
                                                    <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                </div>
                                                <span class="dh-gas-unit">ft</span>
                                            </div>
                                            <div id="maxDepthContainerMetLevel3" class="dh-gas-input-wrap">
                                                <div class="dh-gas-editable">
                                                    <input type="text" inputmode="numeric" class="dh-gas-input" id="labelDepthLevel3MET" value="18">
                                                    <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                </div>
                                                <span class="dh-gas-unit">m</span>
                                            </div>
                                            <input type="hidden" id="depthSliderLevel3-value" name="depthSliderLevel3-value">
                                            <div class="slider-styled" id="depthSliderLevel3" data-dh-num-mirror="1"></div>
                                        </div>
                                    </div>
                                    <div class="mt-3 mb-2">
                                        <label class="dh-gas-label" for="labelBottomTimeLevel3">Bottom time</label>
                                        <input type="hidden" id="bottomTimeSliderLevel3-value" name="bottomTimeSliderLevel3-value">
                                        <div class="dh-gas-row">
                                            <div class="dh-gas-input-wrap">
                                                <div class="dh-gas-editable">
                                                    <input type="text" inputmode="numeric" class="dh-gas-input" id="labelBottomTimeLevel3" value="15">
                                                    <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                </div>
                                                <span class="dh-gas-unit">min</span>
                                            </div>
                                            <div class="slider-styled" id="bottomTimeSliderLevel3" data-dh-num-mirror="1"></div>
                                        </div>
                                    </div>
                                  </div>
                                </div>

                                <div class="col-lg-3 col-12" id="levelPanel4" hidden>
                                  <div class="dh-level-panel">
                                    <button type="button" class="dh-corner-delete" id="level4DeleteButton" onclick="hideLevel4()" aria-label="Delete level 4">
                                        <span class="material-icons-round" aria-hidden="true">delete</span>
                                    </button>
                                    <table class="table align-items-center mb-0 mt-1">
                                        <tr><td class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center" style="border: none;">Level 4</td></tr>
                                    </table>
                                    <div class="mt-0">
                                        <label class="dh-gas-label" for="labelDepthLevel4" id="depthLevel4Title">Depth (ft)</label>
                                        <div class="dh-gas-row">
                                            <div id="maxDepthContainerImpLevel4" class="dh-gas-input-wrap">
                                                <div class="dh-gas-editable">
                                                    <input type="text" inputmode="numeric" class="dh-gas-input" id="labelDepthLevel4" value="40">
                                                    <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                </div>
                                                <span class="dh-gas-unit">ft</span>
                                            </div>
                                            <div id="maxDepthContainerMetLevel4" class="dh-gas-input-wrap">
                                                <div class="dh-gas-editable">
                                                    <input type="text" inputmode="numeric" class="dh-gas-input" id="labelDepthLevel4MET" value="12">
                                                    <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                </div>
                                                <span class="dh-gas-unit">m</span>
                                            </div>
                                            <input type="hidden" id="depthSliderLevel4-value" name="depthSliderLevel4-value">
                                            <div class="slider-styled" id="depthSliderLevel4" data-dh-num-mirror="1"></div>
                                        </div>
                                    </div>
                                    <div class="mt-3 mb-2">
                                        <label class="dh-gas-label" for="labelBottomTimeLevel4">Bottom time</label>
                                        <input type="hidden" id="bottomTimeSliderLevel4-value" name="bottomTimeSliderLevel4-value">
                                        <div class="dh-gas-row">
                                            <div class="dh-gas-input-wrap">
                                                <div class="dh-gas-editable">
                                                    <input type="text" inputmode="numeric" class="dh-gas-input" id="labelBottomTimeLevel4" value="15">
                                                    <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                </div>
                                                <span class="dh-gas-unit">min</span>
                                            </div>
                                            <div class="slider-styled" id="bottomTimeSliderLevel4" data-dh-num-mirror="1"></div>
                                        </div>
                                    </div>
                                  </div>
                                </div>

                                <div class="col-12 mt-2">
                                    <button type="button" class="dh-gas-accordion-add" id="levelBtnAdd">
                                        <span class="material-icons-round" aria-hidden="true">add</span>
                                        Add a level
                                    </button>
                                </div>
                            </div>

                            <!-- Row gases -->
                            <div class="row mt-2">
                                <div class="col-12">
                                    <div class="dh-gas-accordion" id="gas-accordion">
                                    <div class="dh-gas-accordion-item">
                                        <button type="button" class="dh-gas-accordion-head" data-gas-tab="bottom" id="gasTabBtnBottom">
                                            <span class="dh-gas-accordion-head-title">Bottom gas</span>
                                            <span class="material-icons-round dh-gas-accordion-chevron" aria-hidden="true">expand_more</span>
                                        </button>
                                <div id="gasPanelBottom" class="dh-gas-accordion-body" hidden>
                                    <span id="labelBottomGasOrDiluent" hidden>Bottom gas</span>

                                    <!-- Diluent presets only make sense in CC - Bottom gas (OC) has none
                                         (Pablo, 2026-09-17: "for Diluent: Air, 21/35, 18/45 and 10/55").
                                         showOpenCircuit/showClosedCircuit toggle this row's visibility. -->
                                    <div class="dh-channel-picker dh-gas-picker dh-gas-preset-row" id="gasPresetDiluent" data-slot="bottom" hidden>
                                        <button type="button" class="dh-channel-chip" data-o2="21" data-he="0">Air</button>
                                        <button type="button" class="dh-channel-chip" data-o2="21" data-he="35">21/35</button>
                                        <button type="button" class="dh-channel-chip" data-o2="18" data-he="45">18/45</button>
                                        <button type="button" class="dh-channel-chip" data-o2="10" data-he="55">10/55</button>
                                        @if(auth()->user()->isNotGuest())
                                            <button type="button" class="dh-channel-chip dh-gas-mygases-chip" data-mygases-slot="bottom">My Gases</button>
                                            <button type="button" class="dh-btn-icon" data-save-slot="bottom" title="Save this gas to My Gases"><span class="material-icons-round" aria-hidden="true">bookmark_border</span></button>
                                        @endif
                                    </div>

                                    {{-- Bottom gas (OC) standard presets (Pablo, 2026-09-18: "Air, 32%,
                                         36%, 21/31, 18/45 and 10/55" - corrected 2026-09-19 to 21/35, the
                                         intended value) - visible to everyone, like every other card's
                                         presets; "My Gases" and the save icon stay behind isNotGuest()
                                         same as elsewhere, since only a registered diver has anywhere to
                                         save one. --}}
                                    <div class="dh-channel-picker dh-gas-picker dh-gas-preset-row" id="gasPresetBottomOC" data-slot="bottom">
                                        <button type="button" class="dh-channel-chip" data-o2="21" data-he="0">Air</button>
                                        <button type="button" class="dh-channel-chip" data-o2="32" data-he="0">32%</button>
                                        <button type="button" class="dh-channel-chip" data-o2="36" data-he="0">36%</button>
                                        <button type="button" class="dh-channel-chip" data-o2="21" data-he="35">21/35</button>
                                        <button type="button" class="dh-channel-chip" data-o2="18" data-he="45">18/45</button>
                                        <button type="button" class="dh-channel-chip" data-o2="10" data-he="55">10/55</button>
                                        @if(auth()->user()->isNotGuest())
                                            <button type="button" class="dh-channel-chip dh-gas-mygases-chip" data-mygases-slot="bottom">My Gases</button>
                                            <button type="button" class="dh-btn-icon" data-save-slot="bottom" title="Save this gas to My Gases"><span class="material-icons-round" aria-hidden="true">bookmark_border</span></button>
                                        @endif
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-3 col-12 text-center">
                                            <div style="position: relative; width: 105px; height: 210px; margin: 0 auto;">
                                                <!-- Overlaying image -->
                                                    <img id="tank_double" src="{{ asset("assets") }}/img/tank_double.png"   alt="Overlay Image"
                                                    style="position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 150%; height: 75%; z-index: 10;">

                                                    <img id="tank_ccr" src="{{ asset("assets") }}/img/tank_ccr.png"   alt="Overlay Image" display="none"
                                                    style="position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 150%; height: 75%; z-index: 10;">

                                                    <img id="unblendable_sign" src="{{ asset("assets") }}/img/unblendable_sign.png" hidden alt="Overlay Image"
                                                    style="position: absolute; top: 70%; left: 50%; transform: translate(-50%, -50%); z-index: 10;">


                                                <!-- Fixed-size chart canvas -->
                                                <div style="width: 210px; height: 156px; position: absolute; bottom: 0; left: 50%; transform: translateX(-50%);">
                                                    <canvas id="bottomGasStackedBar"
                                                            style="width: 100%; height: 156px; position: absolute; bottom: 0; left: 0; transform: none; z-index: 1;"></canvas>
                                                </div>
                                            </div>

                                            <div class="text-center mt-2 mb-1">
                                                <div class="dh-gas-split-pill">
                                                    <label class="dh-gas-result-pill is-o2" id="bottomGasSplitO2">21</label>
                                                    <label class="dh-gas-result-pill is-he" id="bottomGasSplitHe">35</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-9 col-12">
                                            <div class="mt-2">
                                                <label class="dh-gas-label do-not-translate">O&#8322;</label>
                                                <div class="dh-gas-row">
                                                    <div class="dh-gas-input-wrap">
                                                        <div class="dh-gas-editable">
                                                            <input type="text" inputmode="numeric" class="dh-gas-input" id="labelBottomGasO2" value="21">
                                                            <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                        </div>
                                                        <span class="dh-gas-unit">%</span>
                                                    </div>
                                                    <input type="hidden" id="bottomGasO2Slider-value" name="bottomGasO2Slider-value">
                                                    <div class="slider-styled" id="bottomGasO2Slider" data-dh-num-mirror="1"></div>
                                                </div>
                                            </div>

                                            <div class="mt-3">
                                                <label class="dh-gas-label do-not-translate">He</label>
                                                <div class="dh-gas-row">
                                                    <div class="dh-gas-input-wrap">
                                                        <div class="dh-gas-editable">
                                                            <input type="text" inputmode="numeric" class="dh-gas-input" id="labelBottomGasHe" value="35">
                                                            <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                        </div>
                                                        <span class="dh-gas-unit">%</span>
                                                    </div>
                                                    <input type="hidden" id="bottomGasHeSlider-value" name="bottomGasHeSlider-value">
                                                    <div class="slider-styled" id="bottomGasHeSlider" data-dh-num-mirror="1"></div>
                                                </div>
                                            </div>

                                            <div class="row mt-3">
                                                <div class="label-container">
                                                    <label class="do-not-translate" id="labelMaxDepthPPO2Description">Max depth PPO&#8322;</label>
                                                    <span class="d-inline-flex align-items-center" style="gap: 6px;">
                                                        <label class="dh-gas-result-pill is-compact" id="labelBottomGasPPO2">1.4</label>
                                                        <label class="text-info mb-0">atm</label>
                                                    </span>
                                                </div>

                                                <div class="label-container">
                                                    <label id="labelENDDescription">Equivalent Narcotic Depth</label>
                                                    <span class="d-inline-flex align-items-center" style="gap: 6px;">
                                                        <label class="dh-gas-result-pill is-compact" id="labelBottomGasEND">90</label>
                                                        <label id="labelBottomGasENDUnit" class="text-info mb-0">ft</label>
                                                    </span>
                                                </div>

                                                <div class="label-container">
                                                    <label id="labelGasDensityDescription">Gas density</label>
                                                    <span class="d-inline-flex align-items-center" style="gap: 6px;">
                                                        <label class="dh-gas-result-pill is-compact" id="labelBottomGasDensity">90</label>
                                                        <label class="text-info mb-0">g/l</label>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                    </div>

                                    <span id="titleDecoGases" hidden></span>

                                    <!-- Deco 1 -->
                                    <div class="dh-gas-accordion-item" id="gasAccordionItemDeco1" hidden>
                                        <button type="button" class="dh-gas-accordion-head" data-gas-tab="deco1" id="gasTabBtnDeco1">
                                            <span class="dh-gas-accordion-head-title">Deco 1</span>
                                            <span class="material-icons-round dh-gas-accordion-chevron" aria-hidden="true">expand_more</span>
                                        </button>
                                        <div class="col-12 dh-gas-accordion-body" id="deco1">
                                            <!-- Slot 1 is Deco 1 in OC, mandatory Bailout in CC - two separate
                                                 preset menus for the same underlying gas (Pablo, 2026-09-17: "for
                                                 decos: 32%, 50%, 80% and Oxygen" vs "for bailout: Air, 32%,
                                                 21/35, 18/45"). showOpenCircuit/showClosedCircuit toggle which
                                                 one is visible; both drive decoGas1's sliders via data-slot="1". -->
                                            <div class="dh-channel-picker dh-gas-picker dh-gas-preset-row" id="gasPreset1" data-slot="1">
                                                <button type="button" class="dh-channel-chip" data-o2="32" data-he="0">32%</button>
                                                <button type="button" class="dh-channel-chip" data-o2="50" data-he="0">50%</button>
                                                <button type="button" class="dh-channel-chip" data-o2="80" data-he="0">80%</button>
                                                <button type="button" class="dh-channel-chip" data-o2="100" data-he="0">Oxygen</button>
                                                @if(auth()->user()->isNotGuest())
                                                    <button type="button" class="dh-channel-chip dh-gas-mygases-chip" data-mygases-slot="1">My Gases</button>
                                                    <button type="button" class="dh-btn-icon" data-save-slot="1" title="Save this gas to My Gases"><span class="material-icons-round" aria-hidden="true">bookmark_border</span></button>
                                                @endif
                                                {{-- Only in this row (not #gasPresetBailout below), which is only
                                                     ever shown in OC - Deco 1 is deletable there, but slot 1 plays
                                                     CC's mandatory (non-deletable) Bailout role instead. --}}
                                                <button type="button" class="dh-corner-delete" id="deco1DeleteButton" onclick="hideDecoGas1()" aria-label="Delete Deco 1 gas">
                                                    <span class="material-icons-round" aria-hidden="true">delete</span>
                                                </button>
                                            </div>
                                            <div class="dh-channel-picker dh-gas-picker dh-gas-preset-row" id="gasPresetBailout" data-slot="1" hidden>
                                                <button type="button" class="dh-channel-chip" data-o2="21" data-he="0">Air</button>
                                                <button type="button" class="dh-channel-chip" data-o2="32" data-he="0">32%</button>
                                                <button type="button" class="dh-channel-chip" data-o2="21" data-he="35">21/35</button>
                                                <button type="button" class="dh-channel-chip" data-o2="18" data-he="45">18/45</button>
                                                @if(auth()->user()->isNotGuest())
                                                    <button type="button" class="dh-channel-chip dh-gas-mygases-chip" data-mygases-slot="1">My Gases</button>
                                                    <button type="button" class="dh-btn-icon" data-save-slot="1" title="Save this gas to My Gases"><span class="material-icons-round" aria-hidden="true">bookmark_border</span></button>
                                                @endif
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-3 col-12 text-center">
                                                    <div style="position: relative; width: 105px; height: 210px; margin: 0 auto;">
                                                        <!-- Overlaying image -->
                                                            <img id="tank_single_1" src="{{ asset("assets") }}/img/tank_single.png"   alt="Overlay Image"
                                                            style="position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 150%; height: 75%; z-index: 10;">

                                                        <img id="unblendable_sign_1" src="{{ asset("assets") }}/img/unblendable_sign.png" hidden alt="Overlay Image"
                                                            style="position: absolute; top: 70%; left: 50%; transform: translate(-50%, -50%); z-index: 10;">

                                                        <!-- Fixed-size chart canvas -->
                                                        <div style="width: 210px; height: 156px; position: absolute; bottom: 0; left: 50%; transform: translateX(-50%);">
                                                            <canvas id="decoGas1StackedBar"
                                                                    style="width: 100%; height: 156px; position: absolute; bottom: 0; left: 0; transform: none; z-index: 1;"></canvas>
                                                        </div>
                                                    </div>

                                                    <div class="text-center mt-2 mb-1">
                                                        <div class="dh-gas-split-pill">
                                                            <label class="dh-gas-result-pill is-o2" id="decoGas1SplitO2">21</label>
                                                            <label class="dh-gas-result-pill is-he" id="decoGas1SplitHe">35</label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9 col-12">
                                                    <div class="mt-2">
                                                        <label class="dh-gas-label do-not-translate">O&#8322;</label>
                                                        <div class="dh-gas-row">
                                                            <div class="dh-gas-input-wrap">
                                                                <div class="dh-gas-editable">
                                                                    <input type="text" inputmode="numeric" class="dh-gas-input" id="labelDecoGas1O2" value="21">
                                                                    <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                                </div>
                                                                <span class="dh-gas-unit">%</span>
                                                            </div>
                                                            <input type="hidden" id="decoGas1O2Slider-value" name="decoGas1O2Slider-value">
                                                            <div class="slider-styled" id="decoGas1O2Slider" data-dh-num-mirror="1"></div>
                                                        </div>
                                                    </div>

                                                    <div class="mt-3">
                                                        <label class="dh-gas-label do-not-translate">He</label>
                                                        <div class="dh-gas-row">
                                                            <div class="dh-gas-input-wrap">
                                                                <div class="dh-gas-editable">
                                                                    <input type="text" inputmode="numeric" class="dh-gas-input" id="labelDecoGas1He" value="35">
                                                                    <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                                </div>
                                                                <span class="dh-gas-unit">%</span>
                                                            </div>
                                                            <input type="hidden" id="decoGas1HeSlider-value" name="decoGas1HeSlider-value">
                                                            <div class="slider-styled" id="decoGas1HeSlider" data-dh-num-mirror="1"></div>
                                                        </div>
                                                    </div>

                                                    <div class="mt-3" id="containerDeco1OCInfo">
                                                        <label class="dh-gas-label">Switch PPO&#8322;</label>
                                                        <div class="dh-gas-row">
                                                            <div class="dh-gas-input-wrap">
                                                                <div class="dh-gas-editable">
                                                                    <input type="text" inputmode="decimal" class="dh-gas-input" id="labelDecoGas1SwitchPPO2" value="1.4">
                                                                    <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                                </div>
                                                                <span class="dh-gas-unit">atm</span>
                                                            </div>
                                                            <input type="hidden" id="decoGas1SwitchSlider-value" name="decoGas1SwitchSlider-value">
                                                            <div class="slider-styled" id="decoGas1SwitchSlider" data-dh-num-mirror="1"></div>
                                                        </div>
                                                        <div class="label-container mt-2">
                                                            <label class="dh-gas-label mb-0" id="switchDepthLabel1">Switch depth</label>
                                                            <span class="d-inline-flex align-items-center" style="gap: 6px;">
                                                                <label class="dh-gas-result-pill is-compact" id="labelDecoGas1Switch">2222</label>
                                                                <label class="text-info mb-0" id="labelDecoGas1SwitchUNIT">ft</label>
                                                            </span>
                                                        </div>
                                                    </div>

                                                    <div class="mt-3" id="containerDeco1CCInfo" style="display:none;">
                                                        <div class="label-container">
                                                            <label class="text-info">PPO&#8322;</label>
                                                            <label class="dh-gas-result-pill is-compact" id="labelBailoutSwitchPPO2">2222</label>
                                                        </div>
                                                        <div class="label-container">
                                                            <label class="text-info text-right">END</label>
                                                            <span class="d-inline-flex align-items-center" style="gap: 6px;">
                                                                <label class="dh-gas-result-pill is-compact" id="labelBailoutEND">2222</label>
                                                                <label class="text-info mb-0" id="labelBailoutENDUNIT">{{ $deco_unit ? "m" : "ft" }}</label>
                                                            </span>
                                                        </div>
                                                        <div class="label-container align-text-right justify-text-right">
                                                            <label class="do-not-translate text-secondary align-text-right text-right" id="switchDepthLabelBO">Switch Depth</label>
                                                            <span class="d-inline-flex align-items-center" style="gap: 6px;">
                                                                <label class="dh-gas-result-pill is-compact" id="labelBailoutSwitch">2222</label>
                                                                <label class="text-secondary mb-0" id="labelBailoutSwitchUNIT">ft</label>
                                                            </span>
                                                        </div>
                                                        <div class="label-container d-flex justify-content-center align-items-center" style="margin-top:20px;">
                                                            <label class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center">BAILOUT GAS</label>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Deco 2 -->
                                    <div class="dh-gas-accordion-item" id="gasAccordionItemDeco2" hidden>
                                        <button type="button" class="dh-gas-accordion-head" data-gas-tab="deco2" id="gasTabBtnDeco2">
                                            <span class="dh-gas-accordion-head-title">Deco 2</span>
                                            <span class="material-icons-round dh-gas-accordion-chevron" aria-hidden="true">expand_more</span>
                                        </button>
                                        <div class="col-12 dh-gas-accordion-body" id="deco2">
                                            <div class="dh-channel-picker dh-gas-picker dh-gas-preset-row" id="gasPreset2" data-slot="2">
                                                <button type="button" class="dh-channel-chip" data-o2="32" data-he="0">32%</button>
                                                <button type="button" class="dh-channel-chip" data-o2="50" data-he="0">50%</button>
                                                <button type="button" class="dh-channel-chip" data-o2="80" data-he="0">80%</button>
                                                <button type="button" class="dh-channel-chip" data-o2="100" data-he="0">Oxygen</button>
                                                @if(auth()->user()->isNotGuest())
                                                    <button type="button" class="dh-channel-chip dh-gas-mygases-chip" data-mygases-slot="2">My Gases</button>
                                                    <button type="button" class="dh-btn-icon" data-save-slot="2" title="Save this gas to My Gases"><span class="material-icons-round" aria-hidden="true">bookmark_border</span></button>
                                                @endif
                                                <button type="button" class="dh-corner-delete" id="deco2DeleteButton" onclick="hideDecoGas2()" aria-label="Delete Deco 2 gas">
                                                    <span class="material-icons-round" aria-hidden="true">delete</span>
                                                </button>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-3 col-12 text-center">
                                                    <div style="position: relative; width: 105px; height: 210px; margin: 0 auto;">
                                                        <!-- Overlaying image -->
                                                            <img id="tank_single_2" src="{{ asset("assets") }}/img/tank_single.png"   alt="Overlay Image"
                                                            style="position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 150%; height: 75%; z-index: 10;">

                                                        <img id="unblendable_sign_2" src="{{ asset("assets") }}/img/unblendable_sign.png" hidden alt="Overlay Image"
                                                            style="position: absolute; top: 70%; left: 50%; transform: translate(-50%, -50%); z-index: 10;">

                                                        <!-- Fixed-size chart canvas -->
                                                        <div style="width: 210px; height: 156px; position: absolute; bottom: 0; left: 50%; transform: translateX(-50%);">
                                                            <canvas id="decoGas2StackedBar"
                                                                    style="width: 100%; height: 156px; position: absolute; bottom: 0; left: 0; transform: none; z-index: 1;"></canvas>
                                                        </div>
                                                    </div>

                                                    <div class="text-center mt-2 mb-1">
                                                        <div class="dh-gas-split-pill">
                                                            <label class="dh-gas-result-pill is-o2" id="decoGas2SplitO2">21</label>
                                                            <label class="dh-gas-result-pill is-he" id="decoGas2SplitHe">35</label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9 col-12">
                                                    <div class="mt-2">
                                                        <label class="dh-gas-label do-not-translate">O&#8322;</label>
                                                        <div class="dh-gas-row">
                                                            <div class="dh-gas-input-wrap">
                                                                <div class="dh-gas-editable">
                                                                    <input type="text" inputmode="numeric" class="dh-gas-input" id="labelDecoGas2O2" value="21">
                                                                    <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                                </div>
                                                                <span class="dh-gas-unit">%</span>
                                                            </div>
                                                            <input type="hidden" id="decoGas2O2Slider-value" name="decoGas2O2Slider-value">
                                                            <div class="slider-styled" id="decoGas2O2Slider" data-dh-num-mirror="1"></div>
                                                        </div>
                                                    </div>

                                                    <div class="mt-3">
                                                        <label class="dh-gas-label do-not-translate">He</label>
                                                        <div class="dh-gas-row">
                                                            <div class="dh-gas-input-wrap">
                                                                <div class="dh-gas-editable">
                                                                    <input type="text" inputmode="numeric" class="dh-gas-input" id="labelDecoGas2He" value="35">
                                                                    <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                                </div>
                                                                <span class="dh-gas-unit">%</span>
                                                            </div>
                                                            <input type="hidden" id="decoGas2HeSlider-value" name="decoGas2HeSlider-value">
                                                            <div class="slider-styled" id="decoGas2HeSlider" data-dh-num-mirror="1"></div>
                                                        </div>
                                                    </div>

                                                    <div class="mt-3">
                                                        <label class="dh-gas-label">Switch PPO&#8322;</label>
                                                        <div class="dh-gas-row">
                                                            <div class="dh-gas-input-wrap">
                                                                <div class="dh-gas-editable">
                                                                    <input type="text" inputmode="decimal" class="dh-gas-input" id="labelDecoGas2SwitchPPO2" value="1.4">
                                                                    <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                                </div>
                                                                <span class="dh-gas-unit">atm</span>
                                                            </div>
                                                            <input type="hidden" id="decoGas2SwitchSlider-value" name="decoGas2SwitchSlider-value">
                                                            <div class="slider-styled" id="decoGas2SwitchSlider" data-dh-num-mirror="1"></div>
                                                        </div>
                                                        <div class="label-container mt-2">
                                                            <label class="dh-gas-label mb-0" id="switchDepthLabel2">Switch depth</label>
                                                            <span class="d-inline-flex align-items-center" style="gap: 6px;">
                                                                <label class="dh-gas-result-pill is-compact" id="labelDecoGas2Switch">2222</label>
                                                                <label class="text-info mb-0" id="labelDecoGas2SwitchUNIT">ft</label>
                                                            </span>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Deco 3 -->
                                    <div class="dh-gas-accordion-item" id="gasAccordionItemDeco3" hidden>
                                        <button type="button" class="dh-gas-accordion-head" data-gas-tab="deco3" id="gasTabBtnDeco3">
                                            <span class="dh-gas-accordion-head-title">Deco 3</span>
                                            <span class="material-icons-round dh-gas-accordion-chevron" aria-hidden="true">expand_more</span>
                                        </button>
                                        <div class="col-12 dh-gas-accordion-body" id="deco3">
                                            <div class="dh-channel-picker dh-gas-picker dh-gas-preset-row" id="gasPreset3" data-slot="3">
                                                <button type="button" class="dh-channel-chip" data-o2="32" data-he="0">32%</button>
                                                <button type="button" class="dh-channel-chip" data-o2="50" data-he="0">50%</button>
                                                <button type="button" class="dh-channel-chip" data-o2="80" data-he="0">80%</button>
                                                <button type="button" class="dh-channel-chip" data-o2="100" data-he="0">Oxygen</button>
                                                @if(auth()->user()->isNotGuest())
                                                    <button type="button" class="dh-channel-chip dh-gas-mygases-chip" data-mygases-slot="3">My Gases</button>
                                                    <button type="button" class="dh-btn-icon" data-save-slot="3" title="Save this gas to My Gases"><span class="material-icons-round" aria-hidden="true">bookmark_border</span></button>
                                                @endif
                                                <button type="button" class="dh-corner-delete" id="deco3DeleteButton" onclick="hideDecoGas3()" aria-label="Delete Deco 3 gas">
                                                    <span class="material-icons-round" aria-hidden="true">delete</span>
                                                </button>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-3 col-12 text-center">
                                                    <div style="position: relative; width: 105px; height: 210px; margin: 0 auto;">
                                                        <!-- Overlaying image -->
                                                            <img id="tank_single_3" src="{{ asset("assets") }}/img/tank_single.png"   alt="Overlay Image"
                                                            style="position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 150%; height: 75%; z-index: 10;">

                                                        <img id="unblendable_sign_3" src="{{ asset("assets") }}/img/unblendable_sign.png" hidden alt="Overlay Image"
                                                            style="position: absolute; top: 70%; left: 50%; transform: translate(-50%, -50%); z-index: 10;">

                                                        <!-- Fixed-size chart canvas -->
                                                        <div style="width: 210px; height: 156px; position: absolute; bottom: 0; left: 50%; transform: translateX(-50%);">
                                                            <canvas id="decoGas3StackedBar"
                                                                    style="width: 100%; height: 156px; position: absolute; bottom: 0; left: 0; transform: none; z-index: 1;"></canvas>
                                                        </div>
                                                    </div>

                                                    <div class="text-center mt-2 mb-1">
                                                        <div class="dh-gas-split-pill">
                                                            <label class="dh-gas-result-pill is-o2" id="decoGas3SplitO2">21</label>
                                                            <label class="dh-gas-result-pill is-he" id="decoGas3SplitHe">35</label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9 col-12">
                                                    <div class="mt-2">
                                                        <label class="dh-gas-label do-not-translate">O&#8322;</label>
                                                        <div class="dh-gas-row">
                                                            <div class="dh-gas-input-wrap">
                                                                <div class="dh-gas-editable">
                                                                    <input type="text" inputmode="numeric" class="dh-gas-input" id="labelDecoGas3O2" value="21">
                                                                    <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                                </div>
                                                                <span class="dh-gas-unit">%</span>
                                                            </div>
                                                            <input type="hidden" id="decoGas3O2Slider-value" name="decoGas3O2Slider-value">
                                                            <div class="slider-styled" id="decoGas3O2Slider" data-dh-num-mirror="1"></div>
                                                        </div>
                                                    </div>

                                                    <div class="mt-3">
                                                        <label class="dh-gas-label do-not-translate">He</label>
                                                        <div class="dh-gas-row">
                                                            <div class="dh-gas-input-wrap">
                                                                <div class="dh-gas-editable">
                                                                    <input type="text" inputmode="numeric" class="dh-gas-input" id="labelDecoGas3He" value="35">
                                                                    <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                                </div>
                                                                <span class="dh-gas-unit">%</span>
                                                            </div>
                                                            <input type="hidden" id="decoGas3HeSlider-value" name="decoGas3HeSlider-value">
                                                            <div class="slider-styled" id="decoGas3HeSlider" data-dh-num-mirror="1"></div>
                                                        </div>
                                                    </div>

                                                    <div class="mt-3">
                                                        <label class="dh-gas-label">Switch PPO&#8322;</label>
                                                        <div class="dh-gas-row">
                                                            <div class="dh-gas-input-wrap">
                                                                <div class="dh-gas-editable">
                                                                    <input type="text" inputmode="decimal" class="dh-gas-input" id="labelDecoGas3SwitchPPO2" value="1.4">
                                                                    <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                                </div>
                                                                <span class="dh-gas-unit">atm</span>
                                                            </div>
                                                            <input type="hidden" id="decoGas3SwitchSlider-value" name="decoGas3SwitchSlider-value">
                                                            <div class="slider-styled" id="decoGas3SwitchSlider" data-dh-num-mirror="1"></div>
                                                        </div>
                                                        <div class="label-container mt-2">
                                                            <label class="dh-gas-label mb-0" id="switchDepthLabel3">Switch depth</label>
                                                            <span class="d-inline-flex align-items-center" style="gap: 6px;">
                                                                <label class="dh-gas-result-pill is-compact" id="labelDecoGas3Switch">2222</label>
                                                                <label class="text-info mb-0" id="labelDecoGas3SwitchUNIT">ft</label>
                                                            </span>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Deco 4 -->
                                    <div class="dh-gas-accordion-item" id="gasAccordionItemDeco4" hidden>
                                        <button type="button" class="dh-gas-accordion-head" data-gas-tab="deco4" id="gasTabBtnDeco4">
                                            <span class="dh-gas-accordion-head-title">Deco 4</span>
                                            <span class="material-icons-round dh-gas-accordion-chevron" aria-hidden="true">expand_more</span>
                                        </button>
                                        <div class="col-12 dh-gas-accordion-body" id="deco4">
                                            <div class="dh-channel-picker dh-gas-picker dh-gas-preset-row" id="gasPreset4" data-slot="4">
                                                <button type="button" class="dh-channel-chip" data-o2="32" data-he="0">32%</button>
                                                <button type="button" class="dh-channel-chip" data-o2="50" data-he="0">50%</button>
                                                <button type="button" class="dh-channel-chip" data-o2="80" data-he="0">80%</button>
                                                <button type="button" class="dh-channel-chip" data-o2="100" data-he="0">Oxygen</button>
                                                @if(auth()->user()->isNotGuest())
                                                    <button type="button" class="dh-channel-chip dh-gas-mygases-chip" data-mygases-slot="4">My Gases</button>
                                                    <button type="button" class="dh-btn-icon" data-save-slot="4" title="Save this gas to My Gases"><span class="material-icons-round" aria-hidden="true">bookmark_border</span></button>
                                                @endif
                                                <button type="button" class="dh-corner-delete" id="deco4DeleteButton" onclick="hideDecoGas4()" aria-label="Delete Deco 4 gas">
                                                    <span class="material-icons-round" aria-hidden="true">delete</span>
                                                </button>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-3 col-12 text-center">
                                                    <div style="position: relative; width: 105px; height: 210px; margin: 0 auto;">
                                                        <!-- Overlaying image -->
                                                            <img id="tank_single_4" src="{{ asset("assets") }}/img/tank_single.png"   alt="Overlay Image"
                                                            style="position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 150%; height: 75%; z-index: 10;">

                                                        <img id="unblendable_sign_4" src="{{ asset("assets") }}/img/unblendable_sign.png" hidden alt="Overlay Image"
                                                            style="position: absolute; top: 70%; left: 50%; transform: translate(-50%, -50%); z-index: 10;">

                                                        <!-- Fixed-size chart canvas -->
                                                        <div style="width: 210px; height: 156px; position: absolute; bottom: 0; left: 50%; transform: translateX(-50%);">
                                                            <canvas id="decoGas4StackedBar"
                                                                    style="width: 100%; height: 156px; position: absolute; bottom: 0; left: 0; transform: none; z-index: 1;"></canvas>
                                                        </div>
                                                    </div>

                                                    <div class="text-center mt-2 mb-1">
                                                        <div class="dh-gas-split-pill">
                                                            <label class="dh-gas-result-pill is-o2" id="decoGas4SplitO2">21</label>
                                                            <label class="dh-gas-result-pill is-he" id="decoGas4SplitHe">35</label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-lg-9 col-12">
                                                    <div class="mt-2">
                                                        <label class="dh-gas-label do-not-translate">O&#8322;</label>
                                                        <div class="dh-gas-row">
                                                            <div class="dh-gas-input-wrap">
                                                                <div class="dh-gas-editable">
                                                                    <input type="text" inputmode="numeric" class="dh-gas-input" id="labelDecoGas4O2" value="21">
                                                                    <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                                </div>
                                                                <span class="dh-gas-unit">%</span>
                                                            </div>
                                                            <input type="hidden" id="decoGas4O2Slider-value" name="decoGas4O2Slider-value">
                                                            <div class="slider-styled" id="decoGas4O2Slider" data-dh-num-mirror="1"></div>
                                                        </div>
                                                    </div>

                                                    <div class="mt-3">
                                                        <label class="dh-gas-label do-not-translate">He</label>
                                                        <div class="dh-gas-row">
                                                            <div class="dh-gas-input-wrap">
                                                                <div class="dh-gas-editable">
                                                                    <input type="text" inputmode="numeric" class="dh-gas-input" id="labelDecoGas4He" value="35">
                                                                    <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                                </div>
                                                                <span class="dh-gas-unit">%</span>
                                                            </div>
                                                            <input type="hidden" id="decoGas4HeSlider-value" name="decoGas4HeSlider-value">
                                                            <div class="slider-styled" id="decoGas4HeSlider" data-dh-num-mirror="1"></div>
                                                        </div>
                                                    </div>

                                                    <div class="mt-3">
                                                        <label class="dh-gas-label">Switch PPO&#8322;</label>
                                                        <div class="dh-gas-row">
                                                            <div class="dh-gas-input-wrap">
                                                                <div class="dh-gas-editable">
                                                                    <input type="text" inputmode="decimal" class="dh-gas-input" id="labelDecoGas4SwitchPPO2" value="1.4">
                                                                    <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                                </div>
                                                                <span class="dh-gas-unit">atm</span>
                                                            </div>
                                                            <input type="hidden" id="decoGas4SwitchSlider-value" name="decoGas4SwitchSlider-value">
                                                            <div class="slider-styled" id="decoGas4SwitchSlider" data-dh-num-mirror="1"></div>
                                                        </div>
                                                        <div class="label-container mt-2">
                                                            <label class="dh-gas-label mb-0" id="switchDepthLabel4">Switch depth</label>
                                                            <span class="d-inline-flex align-items-center" style="gap: 6px;">
                                                            <label class="dh-gas-result-pill is-compact" id="labelDecoGas4Switch">2222</label>
                                                            <label class="text-info mb-0" id="labelDecoGas4SwitchUNIT">ft</label>
                                                            </span>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                        <button type="button" class="dh-gas-accordion-add" id="gasTabBtnAdd">
                                            <span class="material-icons-round" aria-hidden="true">add</span>
                                            Add gas
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Tanks: one row per OC-breathed gas currently configured -
                                 bottom gas for OC, bailout + deco gases for CC (never the
                                 diluent, same set Gas Consumption already tracks) - tank
                                 type/twins, a reserve+fill dual-handle slider, and a per-gas
                                 SAC that becomes Gas Consumption's starting SAC for that same
                                 gas (Pablo, 2026-09-26: "calculate the max bottom time
                                 possible with a given set of tanks...for starters, the SAC
                                 set for each of the gases here are the ones you will use as
                                 default for the Gas Consumption card"). Rows are built/kept in
                                 sync by dhRenderTanksTable() - frontend only for now, nothing
                                 here drives an actual max-bottom-time calculation yet. --}}
                            {{-- Same single-tank icon as the Recreational calendar's own
                                 drawer link (Pablo, 2026-09-26), not the generic propane_tank
                                 Material icon this used to be - same asset already reused for
                                 Gas Consumption's own header above. --}}
                            @php $tanksHeaderSvg = \App\Support\IconSvg::themed('assets/img/icons/icons_calendar_rec.svg'); @endphp
                            {{-- Hidden by default (Pablo, 2026-09-26: "by default no
                                 show... this is a feature that will require a lot of
                                 testing and we are launching in two days") - the Advanced
                                 Settings "Show tanks & gas-limited bottom time" toggle
                                 un-hides it. Hardcoded here rather than left to JS so
                                 there's no flash of the card before that toggle's IIFE runs. --}}
                            <div class="row mt-2" id="dhTanksCardRow" hidden>
                                <div class="col-12">
                                    {{-- Same collapsible dark-header treatment Gas Consumption's own
                                         section uses (dh-deco-section-head/-body) - collapsed by
                                         default, unlike Gas Consumption itself (Pablo, 2026-09-26:
                                         "wrap the card in the theme cards that we have (same as
                                         sections for gas)...by default this section should be
                                         collapsed"). Rotation-only chevron (CSS handles
                                         .is-collapsed), no icon-swap JS needed. --}}
                                    <div class="dh-deco-section-head is-toggle is-collapsed" id="dhTanksSectionHead" role="button" tabindex="0" aria-expanded="false" aria-controls="dhTanksSectionBody">
                                        @if($tanksHeaderSvg)
                                            <span class="dh-deco-section-head-icon-svg" aria-hidden="true">{!! $tanksHeaderSvg !!}</span>
                                        @else
                                            <span class="material-icons-round" aria-hidden="true">propane_tank</span>
                                        @endif
                                        <div class="dh-deco-section-head-title-wrap">
                                            <h3>Tanks</h3>
                                        </div>
                                        <span class="material-icons-round dh-deco-section-head-chevron" aria-hidden="true">expand_more</span>
                                    </div>
                                    <div class="dh-deco-section-body" id="dhTanksSectionBody" hidden>
                                        <div class="table-responsive">
                                            <table class="table table-sm align-middle mb-0" id="dhTanksTable">
                                                {{-- Column headers don't correspond to anything once each
                                                     row reflows into two lines on mobile (Pablo,
                                                     2026-09-26: "bleed into two rows per row in the
                                                     mobile view") - hidden there the same way the deco
                                                     table's own PPO2/GF headers are (.hide-on-mobile,
                                                     defined above for @media max-width:768px). --}}
                                                <thead class="hide-on-mobile">
                                                    <tr>
                                                        <th class="text-xs text-center" style="width: 14%;">Type</th>
                                                        <th class="text-xs text-center" style="width: 6%;">Twins</th>
                                                        <th class="text-xs text-center" style="width: 12%;">Gas</th>
                                                        <th class="text-xs text-center" style="width: 36%;">Reserve / Fill</th>
                                                        <th class="text-xs text-center" style="width: 14%;">Available gas</th>
                                                        <th class="text-xs text-center" style="width: 18%;">SAC</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="dhTanksTableBody"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>

                            <!-- Row for calculate button -->
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="text-center" style="border: none;"> <!-- Added text-center here -->
                                        <a type="button" class="btn btn-info mt-0" id="calculateDecoProfile">
                                            Calculate Decompression Profile
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Calculate is a real server-side round trip (the deco
                                 planning API), a few seconds long - shown immediately on
                                 click so the app doesn't read as frozen while it waits
                                 (Pablo, 2026-09-19: "show the splash immediately after the
                                 user clicks Calculate"). Same visual language as the boot
                                 splash, but its own independent element since the boot
                                 splash deletes itself from the DOM the first time it hides. -->
                            <div class="dh-action-splash" id="dhCalcSplash" aria-hidden="true">
                                <img src="{{ asset('assets') }}/img/pwa/icon-512.png" alt="">
                            </div>
                        
                        </div>

                    </div>
                    

                        

                        
                    
                </div>
            </div>

            

            <div class="row" id="profileChartAndTable" style="display: none;">
                <div class="col-12">
                    <div class="card p-0 position-relative mt-3 z-index-2 mb-4">
                        <div class="dh-deco-section-head">
                            <span class="material-icons-round" aria-hidden="true">route</span>
                            <div class="dh-deco-section-head-title-wrap">
                                <h3>Decompression plan</h3>
                                <span class="dh-deco-section-head-meta">Model <span id="labelModel">ZL</span> &middot; GFs <span id="labelGFs">40/70</span></span>
                            </div>
                            {{-- PDF export is a registered-account feature too (Pablo,
                                 2026-09-25: "Same thing for the export to pdf...I don't
                                 want anonymous users to be able to print plans from the
                                 platform") - the PDF itself is now attributed to the
                                 signed-in user (see dhBuildPdfPage's "created on...by"
                                 line), so an anonymous export wouldn't have anyone to
                                 attribute it to anyway. --}}
                            <button type="button" class="dh-deco-header-btn dh-deco-searchrow-pill @if(auth()->user()->isGuest()) is-locked @endif" id="exportDecoPlanPdfBtn" title="Export this decompression plan to PDF">
                                <span class="material-icons-round" aria-hidden="true">picture_as_pdf</span> Export PDF
                                @if(auth()->user()->isGuest())
                                    <span class="material-icons-round" aria-label="Account required" style="font-size: 14px; margin-left: 2px; vertical-align: -2px;">lock</span>
                                @endif
                            </button>
                            @if(auth()->user()->isNotGuest())
                                <button type="button" class="dh-deco-header-btn dh-deco-searchrow-pill" id="saveDecoPlanBtn" title="Save this plan's inputs so you can regenerate it later">
                                    <span class="material-icons-round" aria-hidden="true">save</span> Save Plan
                                </button>
                            @endif
                        </div>

                        <div class="card-body">
                            {{-- Multi-level only - the moment the diver was most
                                 committed to a stop, if they'd bailed right then
                                 (Pablo, 2026-09-25). See dhRenderCriticalPoint(). --}}
                            <div id="dhCriticalPointSummary" class="mb-3" hidden></div>
                            <div>
                                <!-- position:relative anchor for the What-if floating bubble
                                     below - scoped tightly to JUST the summary/table/chart
                                     block, not the whole card-body (which continues on further
                                     down into the Nitrogen tissue / Gas consumption sections) -
                                     otherwise the bubble's bottom-right anchoring would sit at
                                     the bottom of ALL of that instead of "over the Decompression
                                     results card" specifically (Pablo, 2026-09-19). -->
                                <div class="dh-whatif-anchor" style="position: relative;">
                                <!-- Row for summary - plain "row" (not
                                     mx-0), same as the table/chart row right
                                     below it, so both line up at the same
                                     edges (Pablo, 2026-09-19). -->
                                <div class="row">
                                    <div class="col-12">
                                        <div class="dh-deco-summary">
                                            <span class="dh-gas-result-pill dh-deco-summary-pill" style="position: relative;">
                                                <span class="dh-deco-summary-pill-label">Run time</span>
                                                <span id="labelTotalRunTime">67</span>
                                                <span class="dh-gas-pill-badge" id="labelWhatIfRunTimeDiff" hidden>-</span>
                                            </span>
                                            <span class="dh-gas-result-pill dh-deco-summary-pill" style="position: relative;">
                                                <span class="dh-deco-summary-pill-label">Deco time</span>
                                                <span id="labelTotalDecoTime">28</span>
                                                <span class="dh-gas-pill-badge" id="labelWhatIfDecoTimeDiff" hidden>-</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Safety warnings - red/yellow pills for an unsafe plan
                                     (PPO2 > 1.6, CNS% >= 100, END > 200ft red; END > 150ft
                                     yellow), populated by dhRenderSafetyWarnings() (Pablo,
                                     2026-09-26: "I want to flag when a dive plan is unsafe"). -->
                                <div class="row mt-2" id="dhSafetyWarningsRow" hidden>
                                    <div class="col-12">
                                        <div class="dh-deco-summary" id="dhSafetyWarningsContainer"></div>
                                    </div>
                                </div>

                                <!-- New RT / New Deco Time - a second pill row that only
                                     appears once a What-if scenario is picked from the bubble
                                     below, colored red/green by whether the delta is worse or
                                     better than baseline (Pablo, 2026-09-19 floating-bubble
                                     redesign: "We show the New RT and New Deco Time right below
                                     the baseline RT and Deco time...another row of pills").
                                     Below it, a small legend pill names the active scenario with
                                     an X to clear it back to baseline-only. -->
                                <div class="row" id="dhWhatIfSummaryRowWrap" hidden>
                                    <div class="col-12">
                                        <div class="dh-whatif-legend" id="dhWhatIfLegend" hidden>
                                            <span class="material-symbols-rounded" aria-hidden="true">question_exchange</span>
                                            <span id="dhWhatIfLegendText">-</span>
                                            <span class="material-icons-round dh-whatif-legend-clear" id="dhWhatIfLegendClear" role="button" tabindex="0" aria-label="Clear scenario">close</span>
                                        </div>
                                        <div class="dh-deco-summary dh-whatif-summary-row">
                                            <span class="dh-gas-result-pill dh-deco-summary-pill" id="labelWhatIfRunTimePill">
                                                <span class="dh-deco-summary-pill-label">New RT</span>
                                                <span id="labelWhatIfRunTime">-</span>
                                            </span>
                                            <span class="dh-gas-result-pill dh-deco-summary-pill" id="labelWhatIfDecoTimePill">
                                                <span class="dh-deco-summary-pill-label">New Deco Time</span>
                                                <span id="labelWhatIfDecoTime">-</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Row with deco table and profile chart -->
                                <div class="row mt-2">

                                    <div class="col-lg-6 col-12" id="decoTableCol">
                                        <div class="label-container d-flex justify-content-center align-items-center" style="margin-top:20px;">
                                            <label class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center" id="decoTableTitle">Decompression Table</label>
                                        </div>
                                        <div id="decoTableContainer"></div>
                                        <div id="BOTableContainer" style="display: none;"></div>
                                    </div>

                                    <div class="col-lg-6 col-12" id="profileChartContainer">
                                        {{-- Overlay a real dive computer log on top of the calculated
                                             profile (Pablo, 2026-09-22: "overlay a real dive profile
                                             coming from a shearwater computer...over the calculated
                                             chart"). UDDF is a standard XML export format (Shearwater
                                             Cloud, Subsurface, and others all produce it), so this
                                             isn't Shearwater-specific despite the request naming that
                                             brand. Parsed entirely client-side (DOMParser) - the file
                                             never leaves the browser, and the existing profileChart
                                             Chart.js instance just gets a second dataset pushed onto
                                             it in the same {x: minutes, y: -depth} shape
                                             renderProfileChart() already uses, so it overlays exactly
                                             on the same axes.

                                             This sits OUTSIDE the canvas's own wrapper below on
                                             purpose: that wrapper is the div Chart.js measures for
                                             responsive:true/maintainAspectRatio:false sizing, and it
                                             has no fixed height of its own - it's sized purely by its
                                             content, which must stay JUST the canvas. Any sibling
                                             content inside it adds fixed extra height that Chart.js's
                                             resize observer then folds into the canvas's height on
                                             every tick, growing it without bound (confirmed - this
                                             broke the chart in exactly that way before it was moved
                                             out here). --}}
                                        <div class="px-1 pb-2 d-flex align-items-center">
                                            <input type="file" id="uddfFileInput" accept=".xml,.uddf" class="d-none">
                                            <button type="button" id="uddfUploadBtn" class="dh-btn dh-btn-ghost-dark" style="padding: 6px 14px; font-size: .82rem;">
                                                <span class="material-icons-round" aria-hidden="true" style="font-size: 18px;">upload_file</span>Overlay your actual dive log
                                            </button>
                                            <button type="button" class="dh-uddf-help-btn" data-bs-toggle="modal" data-bs-target="#uddfHelpModal" aria-label="How to overlay your actual dive log" title="How do I get a UDDF file?">
                                                <span class="material-icons-round" aria-hidden="true">help_outline</span>
                                            </button>
                                            <span id="uddfStatus" style="font-size: .78rem; margin-left: 8px;"></span>
                                        </div>
                                        <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1" style="position: relative;">
                                            {{-- Collapse the table and let the chart take the full row
                                                 width on desktop (Pablo, 2026-09-25: "be able to
                                                 collapse the deco table (horizontally) and show the
                                                 chart in col-12...a panel collapse or arrow to the top
                                                 left of the chart", later: "I want this inside the
                                                 chart at the top left" + the Material Symbols
                                                 left_panel_close/left_panel_open glyphs). Phone widths
                                                 already stack the two columns, where collapsing one to
                                                 widen the other makes no sense - hidden below lg. --}}
                                            <button type="button" id="dhToggleDecoTableBtn" class="dh-btn-icon dh-btn-icon-accent d-none d-lg-inline-flex" title="Collapse the decompression table" aria-label="Collapse the decompression table" style="position: absolute; top: 10px; left: 10px; z-index: 5;">
                                                <span class="material-symbols-rounded" aria-hidden="true" id="dhToggleDecoTableIcon">left_panel_close</span>
                                            </button>
                                            <canvas id="profileChart" class="chart-canvas border-radius-lg" height="500px"></canvas>

                                            <!-- "What if...?" floating bubble (Pablo, 2026-09-19:
                                                 "show a floating bubble...open a modal and let the
                                                 user select the What if scenario"). One pill - icon
                                                 then label - after a fused circle+pill version
                                                 didn't land (Pablo, 2026-09-17: "let's avoid the
                                                 circle and do only a pill"). Position (top-right of
                                                 this chart) confirmed correct, unchanged. Not fixed
                                                 to the viewport like .dh-chat-fab, scoped to just
                                                 this chart card via the position:relative wrapper
                                                 above. Hidden until a plan is calculated (revealed
                                                 from the calculate button's success handler).
                                                 Picking a scenario is still strictly one-at-a-time -
                                                 unchanged from today. Icon per Pablo's explicit
                                                 choice: "Implement picking the icon Question
                                                 Exchange" (Material Symbols, not in the classic
                                                 Material Icons Round set already used elsewhere -
                                                 loaded separately, see the font link near the top
                                                 of this page). -->
                                            <button type="button" class="dh-whatif-fab" id="dhWhatIfFab" hidden aria-haspopup="dialog" aria-label="What if scenarios">
                                                <span class="material-symbols-rounded" aria-hidden="true">question_exchange</span>
                                                <span class="dh-whatif-fab-label">What if...?</span>
                                            </button>
                                        </div>

                                    </div>
                                </div>

                                <div class="dh-whatif-modal-backdrop" id="dhWhatIfModalBackdrop" hidden>
                                    <div class="dh-whatif-modal" role="dialog" aria-modal="true" aria-labelledby="dhWhatIfModalTitle">
                                        <div class="dh-whatif-modal-head">
                                            <span class="material-symbols-rounded" aria-hidden="true">question_exchange</span>
                                            <h4 id="dhWhatIfModalTitle">What if...?</h4>
                                            <button type="button" class="dh-whatif-modal-close" id="dhWhatIfModalClose" aria-label="Close">
                                                <span class="material-icons-round" aria-hidden="true">close</span>
                                            </button>
                                        </div>
                                        <div class="dh-whatif-modal-body">
                                            <p class="dh-whatif-modal-hint">Pick one scenario to overlay on the baseline plan.</p>
                                            <div class="dh-whatif-chips">
                                                <label class="dh-whatif-chip" for="filter1" id="filter1Container">
                                                    <input class="form-check-input" type="checkbox" id="filter1">
                                                    <span class="dh-whatif-chip-body" data-bs-toggle="tooltip" data-bs-placement="top" title="Extend the dive for 5 m, how's deco profile affected??">Extend bottom time 5 min</span>
                                                </label>

                                                <label class="dh-whatif-chip" for="filter2" id="filter2Container">
                                                    <input class="form-check-input" type="checkbox" id="filter2">
                                                    <span class="dh-whatif-chip-body" data-bs-toggle="tooltip" data-bs-placement="top" title="Dive 10 ft deeper, how is RT and DT changed?">Increase max depth by {{ $deco_unit ? "3 m" : "10 ft" }}</span>
                                                </label>

                                                <label class="dh-whatif-chip" for="filter3" id="filter3Container">
                                                    <input class="form-check-input" type="checkbox" id="filter3">
                                                    <span class="dh-whatif-chip-body" data-bs-toggle="tooltip" data-bs-placement="top" title="calculate RT and deco time using only backgas">Lost all deco gases</span>
                                                </label>

                                                <label class="dh-whatif-chip" for="filter4" id="filter4Container">
                                                    <input class="form-check-input" type="checkbox" id="filter4">
                                                    <span class="dh-whatif-chip-body" data-bs-toggle="tooltip" data-bs-placement="top" title="How's the RT affected if the dive is shorter?">Shorten bottom time 5 min</span>
                                                </label>

                                                <label class="dh-whatif-chip" for="filter5" id="filter5Container">
                                                    <input class="form-check-input" type="checkbox" id="filter5">
                                                    <span class="dh-whatif-chip-body" data-bs-toggle="tooltip" data-bs-placement="top" title="What's the impact of diving 10 ft shallower than planned?">Reduce max depth by {{ $deco_unit ? "3 m" : "10 ft" }}</span>
                                                </label>

                                                <label class="dh-whatif-chip" for="filter6">
                                                    <input class="form-check-input" type="checkbox" id="filter6">
                                                    <span class="dh-whatif-chip-body" data-bs-toggle="tooltip" data-bs-placement="top" title="Minimum deco time">Minimum deco (GFs=100%)</span>
                                                </label>

                                                <label class="dh-whatif-chip" for="filter7" id="filter7Container" style="display: none;">
                                                    <input class="form-check-input" type="checkbox" id="filter7">
                                                    <span class="dh-whatif-chip-body" data-bs-toggle="tooltip" data-bs-placement="top" title="calculate RT and deco time switching to BO">Bailout to OC</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                </div>

                                

                                <!-- Nitrogen gas consumption -->
                                <div class="row mt-2">
                                    <div class="col-12">
                                        <div class="dh-deco-section-head">
                                            <span class="material-icons-round" aria-hidden="true">bubble_chart</span>
                                            <h4 id="decoTableTitle">Nitrogen tissue compartments</h4>
                                        </div>
                                        <div class="dh-deco-section-body">
                                            <canvas id="tissueChart" class="chart-canvas border-radius-lg"></canvas>

                                            <div class="text-center mt-3">
                                                <span class="text-secondary text-sm font-weight-bold me-2">Depth ({{ $deco_unit ? "m" : "ft" }})</span>
                                                <label class="dh-gas-result-pill is-compact" id="labelTissueChartDepth">22</label>
                                                <label class="dh-gas-result-pill is-compact ms-2" id="labelTissueChartTime">22</label>
                                                <span class="text-secondary text-sm font-weight-bold ms-1">min</span>
                                            </div>
                                            <div class="mt-2">
                                                <div class="slider-styled" id="timeLapseSlider"></div>
                                            </div>
                                            <div class="text-center mt-3">
                                                <button type="button" class="dh-btn dh-btn-ghost-dark" id="playTissueAnimation" onclick="toggleSliderAnimation()">
                                                    Play
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- O2 toxicity (CNS%/OTU) - new o2Exposure block on the
                                     DecoPlanner API response (Pablo, 2026-09-24). Computed
                                     only from the baseline/conveyor profile per the API's own
                                     docs, so this always reflects the primary plan, not
                                     whichever what-if scenario is currently highlighted on
                                     the chart above. CNS gets a gauge (0-100% of the NOAA
                                     single-exposure limit, can run over); OTU is shown as a
                                     plain number since the API has no fixed OTU ceiling -
                                     it's meant to be tracked against a diver's own daily
                                     budget, not this dive alone. --}}
                                <div class="row mt-2">
                                    <div class="col-12">
                                        <div class="dh-deco-section-head">
                                            <span class="material-icons-round" aria-hidden="true">warning</span>
                                            <h4>O&#8322; toxicity</h4>
                                        </div>
                                        <div class="dh-deco-section-body">
                                            <div class="dh-o2tox-row">
                                                <div class="dh-o2tox-label">CNS</div>
                                                <div class="dh-o2tox-track"><div class="dh-o2tox-fill" id="o2ToxCnsFill"></div></div>
                                                <div class="dh-o2tox-value" id="o2ToxCnsValue">-</div>
                                            </div>
                                            <div class="dh-o2tox-row">
                                                <div class="dh-o2tox-label">OTU</div>
                                                <label class="dh-gas-result-pill is-compact" id="o2ToxOtuValue">-</label>
                                                <span class="dh-o2tox-otu-caption">cumulative pulmonary O&#8322; units - no fixed limit, track against your own daily budget</span>
                                            </div>
                                            <div class="dh-o2tox-sub" id="o2ToxSurfaceNote" hidden>
                                                After <span id="o2ToxSurfaceMinutes">-</span> min at the surface: CNS decays to <label class="dh-gas-result-pill is-compact" id="o2ToxResidualCns">-</label> &middot; OTU carries forward at <label class="dh-gas-result-pill is-compact" id="o2ToxCarriedOtu">-</label> (not decayed by surface time)
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                

                                {{-- Same single-tank icon as the Recreational calendar's own
                                     drawer link (Pablo, 2026-09-24), not the generic gas-pump
                                     Material icon this used to be. --}}
                                @php $gasConsumptionTankSvg = \App\Support\IconSvg::themed('assets/img/icons/icons_calendar_rec.svg'); @endphp
                                <!-- Row for gas consumption -->
                                <div id="gasConsumptionRow" class="row mt-2">
                                    <div class="col-lg-12 col-12">
                                        <div class="dh-deco-section-head">
                                            @if($gasConsumptionTankSvg)
                                                <span class="dh-deco-section-head-icon-svg" aria-hidden="true">{!! $gasConsumptionTankSvg !!}</span>
                                            @else
                                                <span class="material-icons-round" aria-hidden="true">local_gas_station</span>
                                            @endif
                                            <h4 id="gasConsumptionHeader">Gas consumption</h4>
                                        </div>

                                        <div class="dh-deco-section-body">

                                            {{-- Was mt-6 (4rem) - made sense with the bulk SAC slider
                                                 block that used to sit above "Bottom gas"/"Bailout
                                                 gases", but leaves a big empty gap under the header now
                                                 that block is gone (Pablo, 2026-09-24: "remove the space
                                                 between the legend...and the top of the card"). --}}
                                            <div class="row mt-2" style="padding:10px;">
                                                <div id="gasConsumptionBottomCol" class="col-lg-6 col-12">
                                                    <table class="table align-items-center mb-0 mt-1">
                                                        <tr><td class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center" style="border: none;">Bottom gas</td> </tr>
                                                    </table>
                                                    {{-- The bulk "SAC rate" slider that used to sit here is gone -
                                                         each gas below now gets its own inline slider instead
                                                         (Pablo, 2026-09-24: "one slider per gas...get rid of the
                                                         main sliders"). These hidden inputs stay only so
                                                         dhGetSacRateForGas() has somewhere to fall back to for a
                                                         gas with no per-gas override yet, mirroring the values
                                                         those sliders used to default to. --}}
                                                    <input type="hidden" id="labelSACBottomGas" value="0.8">
                                                    <input type="hidden" id="labelSACBottomGasLiters" value="23">
                                                    <div id="bottomGasConsumptionTableContainer"></div>
                                                </div>

                                                <div id="gasConsumptionDecoCol" class="col-lg-6 col-12">
                                                    <table class="table align-items-center mb-0 mt-1">
                                                        <tr><td id="gasConsumptionDecoOrBOHeader" class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center" style="border: none;">Decompression gases</td> </tr>
                                                    </table>
                                                    <input type="hidden" id="labelSACDecoGas" value="0.5">
                                                    <input type="hidden" id="labelSACDecoGasLiters" value="14">
                                                    <div id="decoGasConsumptionTableContainer"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
                


                
            
            
            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>
    
    
    {{--<x-plugins></x-plugins>--}}
    
    @push('js')
    
    <script src="{{ asset('assets') }}/js/plugins/jquery-3.6.0.min.js" type="text/javascript"></script>
    <script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/3.5.0/model-viewer.min.js"></script>
    {{-- <script src="../../assets/js/plugins/chartjs.min.js"></script> --}}
    {{-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> --}}
    
    
    
    <script src="{{ asset('assets') }}/js/plugins/nouislider.js"></script>
    <link href="{{ asset('assets') }}/css/nouislider.css" rel="stylesheet">

    

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation"></script>
    {{-- "Export to PDF" on the Decompression plan card (Pablo, 2026-09-18). --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    {{-- Renders the offscreen PDF page (built as real HTML/CSS reusing the
         app's own theme) to an image, dropped into the PDF as one full page -
         see dhExportDecoPlanToPDF (Pablo, 2026-09-19). --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    

    


    

    {{-- Script to communicate with server -> deco profile calculation --}}
    

    {{-- MultiLevelDivePlanner: request/response wiring, per
         https://claude.ai/artifact/6VxHGjo4WVgXxc2g1QZecM (Pablo,
         2026-09-25). No "what if" and no separate CC bailout-scenario
         array exist for this endpoint (the single ladder profile already
         accounts for CC's bailout gases), so this reuses the existing
         chart/table/O2-toxicity/gas-consumption renderers by feeding them
         response.profile wherever single-level feeds them response['baseline']
         (documented as "same per-10-second step shape" - phase, abs_p, time,
         gas, gf, tissue_p, ppo2 - confirmed against calculateGasConsumption's
         own field reads), and leaves the what-if filter/bailout-badge paths
         untouched (unreachable anyway - the What-if FAB stays hidden in
         multi-level mode). --}}
    

    {{-- Show modal warning --}}
    

    

    {{-- Gas density and END--}}
    
    {{-- Bottom gas chart --}}
    

    {{-- Script to reset calculation area --}}
    

    {{-- Deco gas 1 chart --}}
    

    {{-- Deco gas 2 chart --}}
     

    {{-- Deco gas 3 chart --}}
    

    {{-- Deco gas 4 chart --}}
    

    {{-- Scripts slider surface time --}}
    

    {{-- Scripts slider GFs --}}
    

    {{-- Scripts slider Asc/Des rates --}}
    



    {{-- Gas tabs: bottom gas / diluent + up to 4 deco/bailout gases, one shown
         at a time so the tank graphics and sliders get the full width instead
         of being squeezed into a 1/4-width card (Pablo, 2026-09-16: "we can
         do horizontally collapsable tabs...show them one at a time"). Deco
         slots 2-4 stay hidden - both their tab and panel - until "+ Add gas"
         reveals the next one; deleting a gas hides it again and frees the
         slot. Slot 1 doubles as CCR's mandatory Bailout gas: showClosedCircuit
         forces it visible and hides its own delete button so the diver can't
         leave a CCR plan with zero bailout gas (not a rule for Open Circuit,
         which can dive on backgas alone). --}}
    

    {{-- Scripts slider Bottom gas O2 and He --}}
    

    {{-- Scripts slider setPoint --}}
    



     {{-- Scripts slider time --}}
     

    {{-- Scripts slider Deco gas 1 O2, He and switch depth --}}
    

     {{-- Slider depth --}}
     

    {{-- Multi-level: Depth + Bottom Time sliders for each of the 4 levels.
         Same slider+input sync pattern as depthSlider/bottomTimeSlider
         above, deliberately WITHOUT depthSlider's gas-composition side
         effects (bottomGasO2/He, decoGas1 range updates) - those are
         single-level-only for now, until this is wired to
         MultiLevelDivePlanner (Pablo, 2026-09-25: front-end/layout only,
         no backend call yet). Level 1 defaults to the selected site's max
         depth exactly like the single-level slider does; levels 2-4 get
         arbitrary shallower defaults since they don't exist until added. --}}
    

    {{-- Scripts slider Deco gas 2 O2, He and switch depth --}}
    

    {{-- Scripts slider Deco gas 3 O2, He and switch depth --}}
    

    {{-- Scripts slider Deco gas 4 O2, He and switch depth --}}
    

    {{-- Re-sort the gas accordion only once a value is actually committed,
         not on every tick while a slider is being dragged (Pablo,
         2026-09-17: "wait until the user releases the slider...avoid too
         much movement while the user is playing with the O2 handle"). Per
         nouislider.js: dragging only fires 'update'/'slide' on every frame;
         'set' fires exactly once - on drag release, on a keyboard step, and
         on a programmatic .set() (typed input, preset pill, mode switch) -
         so it's the right event to hang the reorder on instead of 'update'. --}}
    

    {{-- Script to show profile chart --}}
    

    {{-- Overlay a real dive computer log (UDDF export) on the calculated
         profile chart above (Pablo, 2026-09-22). UDDF is a standard XML
         format several dive computer platforms export (Shearwater Cloud,
         Subsurface, MacDive...), not proprietary to one brand. Parsed
         entirely client-side - the file never reaches the server - and
         merged into the SAME profileChartInstance renderProfileChart()
         already built, using the identical {x: minutes, y: -depth} point
         shape, so it draws on the same axes and reuses that chart's
         existing tooltip/legend formatting for free. --}}
    

    {{-- Script to show decompression table --}}
    

    {{-- Script to update summary table --}}
    

    {{-- Scripts to manage WhatIf? checksboxes --}}
    

    {{-- Site selector dropdown scripts --}}
    



    {{-- Script to switch from OC to CC --}}
    

    {{-- Single Level / Multi Level toggle, independent of OC/CC above.
         Same is-active pill pattern; showSingleLevel()/showMultiLevel()
         just swap which depth-input block is visible - front-end only,
         Calculate still always runs the single-level flow for now (Pablo,
         2026-09-25). --}}
    

    {{--  Script to change the color of the sidemenu to theme --}}
    <script>

    </script>

    {{-- Scripts to create Tissue chart --}}
    

    {{-- Script to manage Tissue chart --}}
    

    {{--  Completly useless scripts to avoid the labels in the bottom gas to change the size of the frame :) --}}
    

    {{--  Script to avoid the pill selector OC CC to go back to default OC --}}
    

    {{-- Scripts to manage gas consumption --}}
    

    {{-- "Export to PDF" and "Save Plan" on the Decompression plan card
         (Pablo, 2026-09-18). Both read window.lastDiveProfile - the same
         object the calculate button already builds and sends to the
         calculation API - rather than re-scraping every input from the
         DOM a second time. --}}
    <script>
        window.DH_DECO = {
            unit: "<?php echo $deco_unit ? 'm' : 'ft'; ?>",
            modeImpOrMetric: "<?php echo $deco_unit ? 'met' : 'imp'; ?>",
            depthConversionFactor: {{ $deco_unit ? 3.28084 : 1 }},
            volumeUnitLabel: "{{ $deco_unit ? '(liters)' : '(cuft)' }}",
            volumeConversionFactor: {{ $deco_unit ? 28.3168 : 1 }},
            apiHostKey: "<?php echo base64_decode(env('DIVE_PLANNING_API_V2_HOST_KEY')); ?>",
            currentSite: @json($currentSite),
            allSites: @json($allSites),
            savedGases: @json($savedGases ?? []),
            gfLow: {{ $decoPrefs['gfLow'] ?? 40 }},
            gfHigh: {{ $decoPrefs['gfHigh'] ?? 75 }},
            setpoint: {{ $decoPrefs['setpoint'] ?? 1.3 }},
            isGuest: @json(auth()->user()->isGuest()),
            userName: "{{ addslashes(auth()->user()->name ?? '') }}",
            assetsUrl: "{{ asset('assets') }}",
            deleteGasUrlTemplate: "{{ route('DecoPlanner.deleteGas', ['id' => '__ID__']) }}",
            saveGasUrl: "{{ route('DecoPlanner.saveGas') }}",
            savePreferencesUrl: "{{ route('DecoPlanner.savePreferences') }}",
            savePlanUrl: "{{ route('DecoPlanner.savePlan') }}",
            deletePlanUrlTemplate: "{{ route('DecoPlanner.deletePlan', ['id' => '__ID__']) }}",
            listPlansUrl: "{{ route('DecoPlanner.listPlans') }}",
        };
    </script>
    <script src="{{ asset('assets') }}/js/deco-planner.js?v={{ config('divehub.version') }}"></script>

    
    @endpush
</x-page-template>

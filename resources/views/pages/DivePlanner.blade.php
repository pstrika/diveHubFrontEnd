<x-page-template bodyClass='dh-shell bg-gray-200' :SEO="$SEO ?? []">
    <x-shell.nav active="me" />
    
    
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <!-- Navbar -->
        <x-shell.header title="Dive Planner" icon="timer" />
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

                            <div class="form-check form-switch ps-0 mb-3">
                                <input class="form-check-input ms-auto" type="checkbox" id="dhToggleRecommendGases" checked>
                                <label class="form-check-label text-body ms-3 mb-0" for="dhToggleRecommendGases">
                                    Recommend best gases
                                    <div class="text-secondary text-xs">Auto-fill bottom gas, diluent and bailout as you move the depth sliders.</div>
                                </label>
                            </div>

                            <div class="form-check form-switch ps-0">
                                <input class="form-check-input ms-auto" type="checkbox" id="dhToggleRemoveGuards">
                                <label class="form-check-label text-body ms-3 mb-0" for="dhToggleRemoveGuards">
                                    Remove safety guards
                                    <div class="text-secondary text-xs">Allow diluent PPO&#8322; up to 2.0 ATA and fully independent GF Low/High.</div>
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
                @if(!is_null($currentSite) && $deco_unit)
                    <a class="dh-btn dh-btn-ghost-dark" id="switchUnits" href="{{ route('DecoPlannerImperial') }}/{{ $currentSite->id }}">Switch to Imperial</a>
                @elseif(is_null($currentSite) && $deco_unit)
                    <a class="dh-btn dh-btn-ghost-dark" id="switchUnits" href="{{ route('DecoPlannerImperial') }}">Switch to Imperial</a>
                @elseif(!is_null($currentSite) && !$deco_unit)
                    <a class="dh-btn dh-btn-ghost-dark" id="switchUnits" href="{{ route('DecoPlannerMetric') }}/{{ $currentSite->id }}">Switch to Metric</a>
                @else
                    <a class="dh-btn dh-btn-ghost-dark" id="switchUnits" href="{{ route('DecoPlannerMetric') }}">Switch to Metric</a>
                @endif
            </div>

            <div class="row">
                <div class="col-md-12 m-auto">
                    <div class="card p-0 position-relative mt-3 z-index-2 mb-4" id="dh-deco-inputs-card">
                        <div class="dh-calc-inputs-head" id="dh-deco-inputs-toggle" role="button" tabindex="0" aria-expanded="true" aria-controls="dh-deco-inputs-body">
                            <span class="material-icons-round" aria-hidden="true">tune</span>
                            <h3>Inputs</h3>
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
                                        {{-- Advanced settings - registered users only, not even shown
                                             to guests (Pablo, 2026-09-25: "This is ONLY available for
                                             registered users. For guest, don't even show the gear
                                             icon"), unlike the lock-badge treatment used for Multi
                                             Level/Export PDF - these settings can create genuinely
                                             dangerous configurations, so there's no upsell value in
                                             advertising them to a guest. --}}
                                        <button type="button" class="dh-gear-btn" id="dhAdvancedSettingsBtn" title="Advanced settings" aria-label="Advanced settings" style="margin-left: 6px;">
                                            <span class="material-icons-round" aria-hidden="true">settings</span>
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

            <script>
                (function () {
                    var card = document.getElementById('dh-deco-inputs-card');
                    var body = document.getElementById('dh-deco-inputs-body');
                    var toggle = document.getElementById('dh-deco-inputs-toggle');
                    var chevron = document.getElementById('dh-deco-inputs-chevron');
                    var hint = document.getElementById('dh-deco-inputs-hint');
                    if (!card || !body || !toggle) return;

                    function setOpen(open) {
                        body.hidden = !open;
                        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                        if (chevron) chevron.textContent = open ? 'expand_less' : 'expand_more';
                        if (hint) hint.hidden = open;
                    }

                    // Exposed globally: the calculate button's success handler collapses
                    // this once real results are on screen, and resetCalculationArea()
                    // (fired by every slider/toggle in the form) re-opens it, since a
                    // stale, collapsed input panel next to a stale result is confusing.
                    window.dhDecoInputsSetOpen = setOpen;

                    toggle.addEventListener('click', function () { setOpen(body.hidden); });
                    toggle.addEventListener('keydown', function (e) {
                        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); setOpen(body.hidden); }
                    });
                })();
            </script>

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

                                <script>
                                    // Bubble open/close. Picking a chip (or the legend's X)
                                    // closes the modal - the checkbox's own native 'change' event
                                    // still drives 100% of the existing calculation logic further
                                    // below, completely untouched.
                                    (function () {
                                        var fab = document.getElementById('dhWhatIfFab');
                                        var backdrop = document.getElementById('dhWhatIfModalBackdrop');
                                        if (!fab || !backdrop) return;

                                        // The Decompression card is a Bootstrap .card with its own
                                        // "position:relative; z-index:2" (a real stacking context),
                                        // which traps this backdrop's own z-index inside it no
                                        // matter how high it's set - it could never paint above the
                                        // topbar/sidebar outside the card. Re-parenting the backdrop
                                        // onto <body> escapes that trap so it lands in the root
                                        // stacking context, same trick the page's own
                                        // .modal{z-index:10050} comment already flags for "higher
                                        // than the sidebar's z-index". The FAB itself stays put -
                                        // it's meant to be anchored inside this specific card.
                                        document.body.appendChild(backdrop);

                                        function openModal() { backdrop.hidden = false; }
                                        function closeModal() { backdrop.hidden = true; }

                                        fab.addEventListener('click', openModal);
                                        var closeBtn = document.getElementById('dhWhatIfModalClose');
                                        if (closeBtn) closeBtn.addEventListener('click', closeModal);

                                        // Clicking the backdrop itself (not the modal card) closes
                                        // with no change (Pablo: "If user clicks outside the modal,
                                        // we do nothing and collapse bubble").
                                        backdrop.addEventListener('click', function (e) {
                                            if (e.target === backdrop) closeModal();
                                        });
                                        document.addEventListener('keydown', function (e) {
                                            if (e.key === 'Escape' && !backdrop.hidden) closeModal();
                                        });

                                        document.querySelectorAll('.dh-whatif-chip input[type=checkbox]').forEach(function (cb) {
                                            cb.addEventListener('change', function () {
                                                if (this.checked) closeModal();
                                            });
                                        });
                                    })();

                                    // Mirrors the checkboxes' own state onto the new result-pill
                                    // row, the baseline pills' delta badges, and the legend pill -
                                    // purely presentational, doesn't touch a single filterN
                                    // handler. Deferred with setTimeout(0): this listener is
                                    // attached to each checkbox right now, at initial page parse,
                                    // which is BEFORE filterN's own "change" listener gets
                                    // attached later in the page (down where filter1..7's handlers
                                    // are registered) - same-event listeners fire in attachment
                                    // order, so without deferring, this would run and read state
                                    // BEFORE filterN's handler had set it. A setTimeout(fn, 0)
                                    // always runs after the whole synchronous listener chain for
                                    // this event has finished, regardless of attachment order.
                                    (function () {
                                        var rowWrap = document.getElementById('dhWhatIfSummaryRowWrap');
                                        var legend = document.getElementById('dhWhatIfLegend');
                                        var legendText = document.getElementById('dhWhatIfLegendText');
                                        var legendClear = document.getElementById('dhWhatIfLegendClear');
                                        var rtBadge = document.getElementById('labelWhatIfRunTimeDiff');
                                        var dtBadge = document.getElementById('labelWhatIfDecoTimeDiff');
                                        var rtPill = document.getElementById('labelWhatIfRunTimePill');
                                        var dtPill = document.getElementById('labelWhatIfDecoTimePill');
                                        var checkboxes = document.querySelectorAll('.dh-whatif-chip input[type=checkbox]');
                                        if (!rowWrap || !checkboxes.length) return;

                                        function mirrorColor(badge, pill) {
                                            if (!badge || !pill) return;
                                            pill.classList.toggle('is-danger', badge.classList.contains('is-danger'));
                                            pill.classList.toggle('is-ideal', badge.classList.contains('is-ideal'));
                                        }

                                        function update() {
                                            var checked = document.querySelector('.dh-whatif-chip input[type=checkbox]:checked');
                                            rowWrap.hidden = !checked;
                                            if (rtBadge) rtBadge.hidden = !checked;
                                            if (dtBadge) dtBadge.hidden = !checked;
                                            mirrorColor(rtBadge, rtPill);
                                            mirrorColor(dtBadge, dtPill);

                                            if (legend) legend.hidden = !checked;
                                            if (checked && legendText) {
                                                var chipBody = document.querySelector('label[for="' + checked.id + '"] .dh-whatif-chip-body');
                                                legendText.textContent = chipBody ? chipBody.textContent : '';
                                            }
                                        }

                                        checkboxes.forEach(function (cb) {
                                            cb.addEventListener('change', function () { setTimeout(update, 0); });
                                        });

                                        // The legend's X is the exact equivalent of unchecking the
                                        // active chip today - uncheck it and fire the same native
                                        // 'change' event its own handler already listens for
                                        // (equivalent to "unclicking the pill in the current
                                        // design").
                                        if (legendClear) {
                                            legendClear.addEventListener('click', function () {
                                                var checked = document.querySelector('.dh-whatif-chip input[type=checkbox]:checked');
                                                if (!checked) return;
                                                checked.checked = false;
                                                checked.dispatchEvent(new Event('change'));
                                            });
                                        }

                                        window.dhUpdateWhatIfVisibility = update;
                                        update();
                                    })();
                                </script>

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

                                <script>
                                    // Populates the O2 toxicity card above from the DecoPlanner
                                    // API response's o2Exposure block (Pablo, 2026-09-24) - see
                                    // https://claude.ai/artifact/T2K7STtvwK4osq49DoXv7G for the
                                    // field reference this is built against. Called from the
                                    // main $.ajax success handler right after `conveyor` is set.
                                    function renderO2Toxicity(o2Exposure) {
                                        var cnsFill = document.getElementById('o2ToxCnsFill');
                                        var cnsValue = document.getElementById('o2ToxCnsValue');
                                        var otuValue = document.getElementById('o2ToxOtuValue');
                                        var note = document.getElementById('o2ToxSurfaceNote');

                                        if (!o2Exposure) {
                                            cnsFill.style.width = '0%';
                                            cnsFill.classList.remove('is-warn', 'is-danger');
                                            cnsValue.textContent = '-';
                                            otuValue.textContent = '-';
                                            note.hidden = true;
                                            return;
                                        }

                                        var cns = o2Exposure.cnsPercent;
                                        var otu = o2Exposure.otu;

                                        // Cap the bar's width at 100% - cnsPercent can exceed it
                                        // (that's the whole point, it's a warning), but the track
                                        // itself has nowhere to go past full.
                                        cnsFill.style.width = Math.min(cns, 100) + '%';
                                        cnsFill.classList.remove('is-warn', 'is-danger');
                                        if (cns > 100) {
                                            cnsFill.classList.add('is-danger');
                                        } else if (cns >= 80) {
                                            cnsFill.classList.add('is-warn');
                                        }
                                        cnsValue.textContent = Math.round(cns) + '%';
                                        otuValue.textContent = Math.round(otu);

                                        // Only present when the surfaceTime input is > 0.
                                        var afterSI = o2Exposure.afterSurfaceInterval;
                                        if (afterSI) {
                                            document.getElementById('o2ToxSurfaceMinutes').textContent = afterSI.surfaceTimeMinutes;
                                            document.getElementById('o2ToxResidualCns').textContent = Math.round(afterSI.residualCnsPercent) + '%';
                                            document.getElementById('o2ToxCarriedOtu').textContent = Math.round(afterSI.otuCarriedForward);
                                            note.hidden = false;
                                        } else {
                                            note.hidden = true;
                                        }
                                    }
                                </script>

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

    

    <script>
    

    window.addEventListener("load", () => {
        setTimeout(() => {
            const lang = new URLSearchParams(window.location.search).get("lang");
            const label = document.getElementById("labelMaxDepthPPO2Description");
            const label1 = document.getElementById("switchDepthLabel1");
            const label2 = document.getElementById("switchDepthLabel2");
            const label3 = document.getElementById("switchDepthLabel3");
            const label4 = document.getElementById("switchDepthLabel4");
            const labelBO = document.getElementById("switchDepthLabelBO");

            if (label && lang === "es-ES") {
                label.textContent = "PPO₂ a máxima profundidad";
            }

            if (label1 && lang === "es-ES") {
                label1.textContent = "Profundidad cambio de gas";
            }

            if (labelBO && lang === "es-ES") {
                labelBO.textContent = "Profundidad cambio de gas";
            }

            if (label2 && lang === "es-ES") {
                label2.textContent = "Profundidad cambio de gas";
            }

            if (label3 && lang === "es-ES") {
                label3.textContent = "Profundidad cambio de gas";
            }

            if (label4 && lang === "es-ES") {
                label4.textContent = "Profundidad cambio de gas";
            }
        }, 1500); // Delay lets Elfsight finish first
    });

</script>


    <script>
        @if($deco_unit)
            let modeImpOrMetric = "met";
        @else
            let modeImpOrMetric = "imp";
        @endif

        <?php
            if($deco_unit)
                $unit = "m";
            else
                $unit = "ft";
        ?>
        const FT2M = (modeImpOrMetric === "imp" ? 1 :0.3048);
        const FT2MLabel = (modeImpOrMetric === "imp" ? "ft" : "m");

        if(modeImpOrMetric === "imp") {
            document.getElementById("maxDepthSliderTitle").innerText = "Max Depth (ft)";
            //document.getElementById("maxDepthInputLabel").innerText = "ft";
            //document.getElementById("ascRateValueLabel").innerText = "ft/min";
            //document.getElementById("descRateValueLabel").innerText = "ft/min";
            document.getElementById("labelBottomGasENDUnit").innerText = "ft";
            document.getElementById("labelDecoGas1SwitchUNIT").innerText = "ft";
            document.getElementById("labelDecoGas2SwitchUNIT").innerText = "ft";
            document.getElementById("labelDecoGas3SwitchUNIT").innerText = "ft";
            document.getElementById("labelDecoGas4SwitchUNIT").innerText = "ft";
            document.getElementById("labelBailoutSwitchUNIT").innerText = "ft";

            document.getElementById("ascRateContainerMet").style.display = "none";
            document.getElementById("desRateContainerMet").style.display = "none";
            document.getElementById("maxDepthContainerMet").style.setProperty("display", "none", "important");
            for (var dhU = 1; dhU <= 4; dhU++) {
                document.getElementById("depthLevel" + dhU + "Title").innerText = "Depth (ft)";
                document.getElementById("maxDepthContainerMetLevel" + dhU).style.setProperty("display", "none", "important");
                document.getElementById("maxDepthContainerImpLevel" + dhU).style.setProperty("display", "flex", "important");
            }

            document.getElementById("ascRateContainerImp").style.display = "flex";
            document.getElementById("desRateContainerImp").style.display = "flex";
            document.getElementById("maxDepthContainerImp").style.setProperty("display", "flex", "important");

        } else {
            document.getElementById("maxDepthSliderTitle").innerText = "Max Depth (m)";
            //document.getElementById("maxDepthInputLabel").innerText = "m";
            //document.getElementById("ascRateValueLabel").innerText = "m/min";
            //document.getElementById("desRateValueLabel").innerText = "m/min";
            document.getElementById("labelBottomGasENDUnit").innerText = "m";
            document.getElementById("labelDecoGas1SwitchUNIT").innerText = "m";
            document.getElementById("labelDecoGas2SwitchUNIT").innerText = "m";
            document.getElementById("labelDecoGas3SwitchUNIT").innerText = "m";
            document.getElementById("labelDecoGas4SwitchUNIT").innerText = "m";
            document.getElementById("labelBailoutSwitchUNIT").innerText = "m";

            document.getElementById("ascRateContainerMet").style.display = "flex";
            document.getElementById("desRateContainerMet").style.display = "flex";
            document.getElementById("maxDepthContainerMet").style.setProperty("display", "flex", "important");
            for (var dhU = 1; dhU <= 4; dhU++) {
                document.getElementById("depthLevel" + dhU + "Title").innerText = "Depth (m)";
                document.getElementById("maxDepthContainerMetLevel" + dhU).style.setProperty("display", "flex", "important");
                document.getElementById("maxDepthContainerImpLevel" + dhU).style.setProperty("display", "none", "important");
            }

            document.getElementById("ascRateContainerImp").style.display = "none";
            document.getElementById("desRateContainerImp").style.display = "none";
            document.getElementById("maxDepthContainerImp").style.setProperty("display", "none", "important");

        }
        
    </script>

    {{-- Script to communicate with server -> deco profile calculation --}}
    <script>
        let baselineRTDT = [];
        let filter1RTDT = [];
        let filter2RTDT = [];
        let filter3RTDT = [];
        let filter4RTDT = [];
        let filter5RTDT = [];
        let filter6RTDT = [];
        let filter7RTDT = [];
        let conveyor = [];

        let globalResponse = null;

        // Deco Table API v2 (Pablo, 2026-09-25 -
        // https://claude.ai/code/artifact/40cb7622-7fd7-4c44-ab0d-5a8a223f25e2):
        // the backend is moving to send a pre-consolidated `table` array (and
        // a `gasSwitches` list, and `totals`) directly on every scenario,
        // across all 3 endpoints (single-level, multi-level, and whichever
        // what-if variant), so the frontend stops re-deriving table rows and
        // gas-switch markers from the raw step array itself.
        //
        // dhNormalizeScenario() is the ONE place that shape difference is
        // absorbed: a scenario value is either still the legacy flat step
        // array, or the new {table, gasSwitches, profile, totals, ...}
        // object - either way this returns {profile, table, gasSwitches,
        // totals}, so every existing consumer that just wants the flat step
        // array (renderProfileChart, calculateGasConsumption, the conveyor)
        // keeps reading `.profile` unchanged, while generateDecoTable()/
        // calculateDecoTime()/buildGasSwitchAnnotations() can look for
        // `.table`/`.gasSwitches`/`.totals` to skip their legacy heuristics
        // entirely once the backend actually sends them.
        //
        // Remove this whole indirection once every endpoint ships v2 and the
        // legacy branches it's gating are deleted for good.
        function dhNormalizeScenario(x) {
            if (Array.isArray(x)) {
                return { profile: x, table: null, gasSwitches: null, totals: null, conveyor: null, criticalPoint: null, o2Exposure: null, requiresDeco: null };
            }
            if (x && Array.isArray(x.table)) {
                return {
                    profile: Array.isArray(x.profile) ? x.profile : [],
                    table: x.table,
                    gasSwitches: Array.isArray(x.gasSwitches) ? x.gasSwitches : null,
                    totals: x.totals || null,
                    conveyor: Array.isArray(x.conveyor) ? x.conveyor : null,
                    criticalPoint: x.criticalPoint || null,
                    o2Exposure: x.o2Exposure || null,
                    requiresDeco: (typeof x.requiresDeco === 'boolean') ? x.requiresDeco : null,
                };
            }
            return {
                profile: (x && Array.isArray(x.profile)) ? x.profile : [],
                table: null, gasSwitches: null, totals: null,
                conveyor: (x && Array.isArray(x.conveyor)) ? x.conveyor : null,
                criticalPoint: (x && x.criticalPoint) || null,
                o2Exposure: (x && x.o2Exposure) || null,
                requiresDeco: (x && typeof x.requiresDeco === 'boolean') ? x.requiresDeco : null,
            };
        }

        // Normalized {profile, table, gasSwitches, totals} per scenario name
        // ('baseline', 'bailout', 'lostDecoGas', 'minDeco', and single-
        // level's extra 'add5min'/'add10ft'/'short5min'/'short10ft'),
        // populated fresh by both success handlers on every Calculate.
        window.dhScenarios = {};

        // Set up the CSRF token for AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Attach click event to the "Calculate NDL" button
        $('#calculateDecoProfile').on('click', function () {
            // reset calculation area
            resetCalculationArea()

            // The deco planning API call below is a real server round trip,
            // a few seconds long - show the splash immediately so the app
            // doesn't read as frozen while it waits (Pablo, 2026-09-19).
            var dhCalcSplash = document.getElementById('dhCalcSplash');
            if (dhCalcSplash) {
                // The Inputs card is a Bootstrap .card with its own
                // "position:relative; z-index:2" (a real stacking context) -
                // same trap the What-if modal hit - which caps this
                // fixed-position overlay's z-index below the topbar/nav no
                // matter how high it's set. Re-parenting onto <body>
                // escapes it into the root stacking context. appendChild on
                // an element already there is a harmless no-op re-append,
                // so this is safe to run on every click.
                document.body.appendChild(dhCalcSplash);
                dhCalcSplash.classList.add('is-visible');
            }

            // Get values from the input fields
            const maxDepth = parseInt(labelDepth.value);
            const bottomTime = parseInt(labelBottomTime.value);
            const bottomGas = {
                O2: parseInt(labelBottomGasO2.value),
                He: parseInt(labelBottomGasHe.value)
            };
            const GFs = {
                low: parseInt(labelGFL.value),
                high: parseInt(labelGFH.value)
            };
            const rate = {
                descent: parseInt(labelDes.value),
                ascent: parseInt(labelAsc.value)
            };
            const surfTime = parseFloat(labelSurfaceTime.value);
            const setpoint = parseFloat(labelSetpoint.value);

            var decoGases = [];

            function addDecoGas(tabBtnId, o2LabelId, heLabelId, switchLabelId) {
                if (!document.getElementById(tabBtnId).hidden) {
                    decoGases.push({
                        O2: parseInt(document.getElementById(o2LabelId).value),
                        He: parseInt(document.getElementById(heLabelId).value),
                        switchDepth: Math.floor(parseInt(document.getElementById(switchLabelId).textContent) * {{ $deco_unit ? 3.28084 : 1 }})
                    });
                }
            }

            // Add gases dynamically if they've been added (their tab exists)

            if (modeOCOrCC == "CC")
                addDecoGas("gasAccordionItemDeco1", "labelDecoGas1O2", "labelDecoGas1He", "labelBailoutSwitch");
            else
                addDecoGas("gasAccordionItemDeco1", "labelDecoGas1O2", "labelDecoGas1He", "labelDecoGas1Switch");

            addDecoGas("gasAccordionItemDeco2", "labelDecoGas2O2", "labelDecoGas2He", "labelDecoGas2Switch");
            addDecoGas("gasAccordionItemDeco3", "labelDecoGas3O2", "labelDecoGas3He", "labelDecoGas3Switch");
            addDecoGas("gasAccordionItemDeco4", "labelDecoGas4O2", "labelDecoGas4He", "labelDecoGas4Switch");

            // Create final JSON structure
            const diveProfile = {
                mode: modeOCOrCC,
                maxDepth: maxDepth,
                bottomTime: bottomTime,
                bottomGas: bottomGas,
                gradientFactors: GFs,
                rate: rate,
                surfaceTime: surfTime,
                decoGases: decoGases,
                setpoint: setpoint
            };

            // Convert to JSON string
            const diveJSON = JSON.stringify(diveProfile, null, 4);
            console.log(diveJSON);

            <?php
                // Deco Table API v2 (Pablo, 2026-09-25 -
                // https://claude.ai/artifact/CQMT3MahLtqEgA68z7Fgta): all 3
                // endpoints (DecoPlanner, MultiLevelDivePlanner,
                // MultiDivePlanner) now live on the DecoPlanningApi function
                // app - one host key covers all of them, so a route added
                // later never needs a new key wired in here.
                $DivePlanningApiV2HostKey = base64_decode(env('DIVE_PLANNING_API_V2_HOST_KEY'));
            ?>

            // Multi-level: a different endpoint (MultiLevelDivePlanner)
            // and request/response shape (Pablo, 2026-09-25: "wire the
            // multilevel based on the artifact" -
            // https://claude.ai/artifact/6VxHGjo4WVgXxc2g1QZecM). Diverges
            // here entirely and returns - the single-level $.ajax below is
            // untouched.
            if (modeLevelSingleOrMulti === 'multi') {
                var dhLevels = dhCollectLevels();
                var dhLevelsError = dhValidateLevels(dhLevels, rate);
                if (dhLevelsError) {
                    if (dhCalcSplash) dhCalcSplash.classList.remove('is-visible');
                    alert(dhLevelsError);
                    return;
                }

                diveProfile.levels = dhLevels;
                diveProfile.levelsMode = 'multi';
                // Level 1's depth/bottom time stand in as the closest
                // single-value equivalent for any single-level-shaped
                // consumer (PDF inputs table) not yet multi-level-aware.
                diveProfile.maxDepth = dhLevels[0].depth;
                diveProfile.bottomTime = dhLevels[0].bottomTime;
                window.lastDiveProfile = diveProfile;

                dhCallMultiLevelDivePlanner(diveProfile, dhLevels, dhCalcSplash);
                return;
            }
            diveProfile.levelsMode = 'single';

            // Cached for "Export PDF" (the inputs table) and "Save Plan" -
            // this object already has every input the diver entered,
            // gases included, so there's no need to re-scrape the DOM for
            // either feature (Pablo, 2026-09-18).
            window.lastDiveProfile = diveProfile;

            // Make the AJAX POST request
            $.ajax({
                LOCALurl: ` http://localhost:7071/api/DecoPlanner`,
                url: `https://decoplanningapi.azurewebsites.net/api/DecoPlanner?code=<?php echo $DivePlanningApiV2HostKey; ?>`,
                method: 'POST',
                contentType: 'application/json',  // Ensures JSON format
                data: JSON.stringify({inputs: diveProfile}), // Converts data to JSON
                crossDomain: true,  // Explicitly allow CORS
                complete: function () {
                    // Fires on both success and error - the splash should
                    // never outlive the request either way.
                    if (dhCalcSplash) dhCalcSplash.classList.remove('is-visible');
                },
                success: function (response) {
                    console.log('Success:', response);

                    // Deco Table API v2 wraps every scenario under
                    // `scenarios` (Pablo, 2026-09-25 -
                    // https://claude.ai/artifact/CQMT3MahLtqEgA68z7Fgta) -
                    // `|| response` is a fallback to v1's bare top-level keys,
                    // only in case an older response ever comes back.
                    var scenarios = response.scenarios || response;

                    // Normalize every scenario key in place: response[key]
                    // keeps being the flat step array
                    // (renderProfileChart/calculateGasConsumption below are
                    // unchanged either way), while window.dhScenarios holds
                    // each scenario's table/gasSwitches/totals/conveyor/
                    // o2Exposure/criticalPoint for the functions that know
                    // how to use them.
                    ['baseline', 'add5min', 'add10ft', 'lostDecoGas', 'short5min', 'short10ft', 'minDeco', 'bailout'].forEach(function (key) {
                        if (!(key in scenarios)) return;
                        window.dhScenarios[key] = dhNormalizeScenario(scenarios[key]);
                        response[key] = window.dhScenarios[key].profile;
                    });

                    // make a copy of the basline response to have to calculate gas consumption
                    //baselineResponse = response['baseline'];
                    //bailoutResponse = response['bailout'];
                    globalResponse = response;

                    renderProfileChart(response);
                    baselineRTDT = generateDecoTable(window.dhScenarios.baseline);

                    // generate BO table
                    if (modeOCOrCC == "CC") {
                        // Captured for the PDF's red "+Xm" badges on the
                        // Dive times pills - how much longer a real bailout
                        // to OC would run vs the planned CC profile (Pablo,
                        // 2026-09-19: "add the red badges with the +xm over
                        // the Run Time and Deco time").
                        window.lastBailoutTotals = generateDecoTable(window.dhScenarios.bailout, 1);
                    }

                    if(baselineRTDT[1] == 0){  //No deco, we hide the table and adjust the size of the chart to col-12
                        document.getElementById("decoTableContainer").style.display = "none";
                        dhSetChartColClass("col-lg-12 col-12");
                        dhSetDecoTableCollapseBtnVisible(false);
                        // Nothing to export - a pure NDL dive has no
                        // decompression plan (Pablo, 2026-09-19: "if deco
                        // time is 0 for the case, just don't show the
                        // export to pdf, there is no deco to print").
                        document.getElementById("exportDecoPlanPdfBtn").hidden = true;

                    } else {
                        document.getElementById("decoTableContainer").style.display ="block";
                        dhSetChartColClass("col-lg-6 col-12");
                        dhSetDecoTableCollapseBtnVisible(true);
                        document.getElementById("exportDecoPlanPdfBtn").hidden = false;
                    }
                    filter1RTDT = calculateDecoTime(window.dhScenarios.add5min);
                    filter2RTDT = calculateDecoTime(window.dhScenarios.add10ft);
                    filter3RTDT = calculateDecoTime(window.dhScenarios.lostDecoGas);
                    filter4RTDT = calculateDecoTime(window.dhScenarios.short5min);
                    filter5RTDT = calculateDecoTime(window.dhScenarios.short10ft);
                    filter6RTDT = calculateDecoTime(window.dhScenarios.minDeco);
                    filter7RTDT = calculateDecoTime(window.dhScenarios.bailout);

                    // Reveal the What-if floating bubble - it only makes sense once a
                    // plan actually exists (Pablo, 2026-09-19: "once decompression was
                    // calculated...show an icon bubble"). Not for multi-level yet
                    // (Pablo, 2026-09-25: "expect no What if? in multi level for now").
                    if (document.getElementById('dhWhatIfFab') && modeLevelSingleOrMulti !== 'multi') document.getElementById('dhWhatIfFab').hidden = false;

                    // update timeLapse tissue data - conveyor/o2Exposure now
                    // live per-scenario (baseline's) under v2, not at the
                    // response's own top level.
                    conveyor = window.dhScenarios.baseline.conveyor || response['conveyor'];
                    console.log(conveyor.length);

                    renderO2Toxicity(window.dhScenarios.baseline.o2Exposure || response['o2Exposure']);
                    dhRenderSafetyWarnings(window.dhScenarios.baseline);
                    timeLapseSlider.noUiSlider.updateOptions({
                        range: {
                            'min': 0,
                            'max': conveyor.length-1 // Set max dynamically
                        }
                    });
                    // Every fresh calculation starts the tissue animation
                    // over from the surface, rather than leaving it
                    // wherever the diver last scrubbed to on a previous
                    // plan (Pablo, 2026-09-25: "everytime we run a
                    // decompression plan, reset the tissue animation to
                    // start from the beginning").
                    timeLapseSlider.noUiSlider.set(0);

                    // calculate Gas consumption
                    // Clear any per-gas SAC overrides - they belonged to
                    // whatever profile was just replaced by this fresh
                    // Calculate (Pablo, 2026-09-24). Each gas re-falls-back
                    // to its type default (0.8/0.5) until tuned again.
                    window.dhGasSacRates = {};
                    if (modeOCOrCC == "OC") {
                        document.getElementById("gasConsumptionRow").style.display="block";
                        gasConsumption = calculateGasConsumption(window.dhScenarios.baseline);
                        renderGasConsumptionTable(gasConsumption, "bottom", window.dhScenarios.baseline);
                        renderGasConsumptionTable(gasConsumption, "deco", window.dhScenarios.baseline);
                        document.getElementById("gasConsumptionHeader").innerText = "Gas consumption (Baseline OC)";
                        document.getElementById("gasConsumptionBottomCol").style.display = "block";
                        document.getElementById("gasConsumptionDecoCol").style.display = "block";
                        document.getElementById("gasConsumptionBottomCol").className = "col-lg-6 col-12";
                        document.getElementById("gasConsumptionDecoCol").className = "col-lg-6 col-12";
                        document.getElementById("gasConsumptionDecoOrBOHeader").innerText = "Decompression Gases";

                        
                    } else {
                        document.getElementById("gasConsumptionRow").style.display="none";
                        document.getElementById("gasConsumptionHeader").innerText = "Gas consumption (Bailout to OC)";
                        document.getElementById("gasConsumptionBottomCol").style.display = "none";
                        document.getElementById("gasConsumptionDecoCol").style.display = "block";
                        //document.getElementById("gasConsumptionBottomCol").className = "col-lg-6 col-12";
                        document.getElementById("gasConsumptionDecoCol").className = "col-12";
                        document.getElementById("gasConsumptionDecoOrBOHeader").innerText = "Bailout Gases";
                    }


                    // reset all checkboxes
                    document.querySelectorAll(".form-check-input").forEach(cb => {
                            cb.checked = false; // Uncheck all other checkboxes
                    });
                    if (typeof window.dhUpdateWhatIfVisibility === 'function') window.dhUpdateWhatIfVisibility();

                    // Restore the 4 chips multi-level hides (Pablo,
                    // 2026-09-25) - this is single-level's own success
                    // handler, so all 7 scenarios are available again.
                    document.getElementById('filter1Container').style.display = "block";
                    document.getElementById('filter2Container').style.display = "block";
                    document.getElementById('filter4Container').style.display = "block";
                    document.getElementById('filter5Container').style.display = "block";

                    console.log("modeOCCC = " + modeOCOrCC);
                    console.log("Length=" + (diveProfile['decoGases'].length));
                    if((modeOCOrCC === "OC" && diveProfile['decoGases'].length === 0) || (modeOCOrCC === "CC" && diveProfile['decoGases'].length < 2)) {
                        document.getElementById('filter3Container').style.display = "none";
                    } else {
                        document.getElementById('filter3Container').style.display = "block";
                    }

                    if(modeOCOrCC === "OC") {
                        document.getElementById('filter7Container').style.display = "none";
                    } else {
                        document.getElementById('filter7Container').style.display = "block";
                    }

                },
                error: function (xhr, status, error) {
                    console.error('Error:', error);
                    console.error('Response:', xhr.responseText); // Debugging response
                }
            });

            
        });

        

    </script>

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
    <script>
        // Remembers the chart column's "natural" width (col-lg-6 normally,
        // col-lg-12 for a pure-NDL no-deco dive) separately from whatever the
        // manual collapse-the-table toggle below has done to it, so toggling
        // back to "not collapsed" restores the right one either way.
        function dhSetChartColClass(cls) {
            var el = document.getElementById('profileChartContainer');
            if (!el) return;
            el.dataset.naturalClass = cls;
            if (!el.dataset.tableCollapsed || el.dataset.tableCollapsed === 'false') {
                el.className = cls;
            }
        }

        // Undoes a manual collapse - shared by the toggle button's own
        // "show" click and by dhSetDecoTableCollapseBtnVisible(false) below.
        // Without the latter, a table collapsed on a wide viewport (button
        // visible) could get stranded hidden with no way back once the
        // button itself disappears - e.g. the button is lg+-only, so
        // rotating an iPad from landscape to portrait mid-session hides the
        // button while the table stays collapsed (Pablo, 2026-09-26: "we
        // risk not being able to see the table if the user collapsed and
        // then change the viewport").
        function dhForceShowDecoTable() {
            var tableCol = document.getElementById('decoTableCol');
            var chartCol = document.getElementById('profileChartContainer');
            var icon = document.getElementById('dhToggleDecoTableIcon');
            var btn = document.getElementById('dhToggleDecoTableBtn');
            if (!tableCol || !chartCol) return;
            chartCol.dataset.tableCollapsed = 'false';
            tableCol.style.display = '';
            chartCol.className = chartCol.dataset.naturalClass || 'col-lg-6 col-12';
            if (icon) icon.textContent = 'left_panel_close';
            if (btn) {
                btn.title = 'Collapse the decompression table';
                btn.setAttribute('aria-label', btn.title);
            }
            if (profileChartInstance) profileChartInstance.resize();
        }

        // A pure-NDL dive has no table to collapse in the first place - the
        // chart is already col-12 via dhSetChartColClass above (Pablo,
        // 2026-09-25: "if there's no deco on the dive, we just show the
        // graph chart in col-12 and hide the collapse button"). Toggling
        // the d-lg-inline-flex class (not just [hidden]) because Bootstrap's
        // responsive utility is !important and would otherwise keep
        // overriding a plain [hidden] at lg+ widths.
        function dhSetDecoTableCollapseBtnVisible(visible) {
            var btn = document.getElementById('dhToggleDecoTableBtn');
            if (!btn) return;
            btn.classList.toggle('d-lg-inline-flex', visible);
            if (!visible) {
                btn.hidden = true;
                dhForceShowDecoTable();
            } else {
                btn.hidden = false;
            }
        }

        (function () {
            var btn = document.getElementById('dhToggleDecoTableBtn');
            var icon = document.getElementById('dhToggleDecoTableIcon');
            var tableCol = document.getElementById('decoTableCol');
            var chartCol = document.getElementById('profileChartContainer');
            if (!btn || !tableCol || !chartCol) return;
            btn.addEventListener('click', function () {
                var collapsed = chartCol.dataset.tableCollapsed === 'true';
                collapsed = !collapsed;
                if (!collapsed) { dhForceShowDecoTable(); return; }
                chartCol.dataset.tableCollapsed = 'true';
                tableCol.style.display = 'none';
                chartCol.className = 'col-lg-12 col-12';
                icon.textContent = 'left_panel_open';
                btn.title = 'Show the decompression table';
                btn.setAttribute('aria-label', btn.title);
                // The chart canvas is sized by its wrapper's width - it
                // won't pick up the new, wider column on its own.
                if (profileChartInstance) profileChartInstance.resize();
            });
        })();

        // The toggle button's own markup is "d-none d-lg-inline-flex" - CSS
        // hides it below Bootstrap's lg breakpoint (992px) no matter what
        // dhSetDecoTableCollapseBtnVisible() last set, with no JS involved
        // at all. dhForceShowDecoTable() covers the JS-driven case (a
        // no-deco dive hiding the button) but not this one - a manually
        // collapsed table on a wide screen would stay collapsed with no
        // visible way to bring it back the moment the viewport narrows
        // (Pablo, 2026-09-26: "when the deco table is collapsed...and the
        // user changes to a view that makes the collapse icon be hidden, we
        // need to show the deco table" - reported as still broken after the
        // JS-only fix, which is why this separate viewport listener exists).
        (function () {
            var mq = window.matchMedia('(min-width: 992px)');
            function onChange(e) {
                if (!e.matches) dhForceShowDecoTable();
            }
            if (mq.addEventListener) mq.addEventListener('change', onChange);
            else if (mq.addListener) mq.addListener(onChange); // Safari <14
        })();

        function dhCollectLevels() {
            var levels = [];
            for (var i = 1; i <= 4; i++) {
                var panel = document.getElementById('levelPanel' + i);
                if (!panel || panel.hidden) continue;
                levels.push({
                    depth: parseInt(document.getElementById('labelDepthLevel' + i).value),
                    bottomTime: parseInt(document.getElementById('labelBottomTimeLevel' + i).value)
                });
            }
            return levels;
        }

        // Client-side half of the same check the API makes (per the
        // artifact's error table: "Level 2 bottom time (5min) is shorter
        // than the 6.7min needed to reach 90ft from the previous level") -
        // bottomTime is elapsed-since-leaving-the-previous-level,
        // transit included, so it can never be shorter than the transit
        // itself. Descent rate applies going deeper, ascent rate going
        // shallower, matching the UI's own labeled rates.
        function dhValidateLevels(levels, rate) {
            if (!levels.length) return 'Add at least one level.';
            for (var i = 1; i < levels.length; i++) {
                var deltaDepth = levels[i].depth - levels[i - 1].depth;
                var transitRate = deltaDepth > 0 ? rate.descent : rate.ascent;
                var transitMinutes = Math.abs(deltaDepth) / transitRate;
                if (levels[i].bottomTime < transitMinutes) {
                    return 'Level ' + (i + 1) + ' bottom time (' + levels[i].bottomTime + 'min) is shorter than the '
                        + transitMinutes.toFixed(1) + 'min needed to reach ' + levels[i].depth + 'ft from the previous level.';
                }
            }
            return null;
        }

        // A simple text summary rather than a chart overlay for now - the
        // dot-on-the-graph treatment the artifact suggests is a follow-up,
        // this surfaces the same safety-relevant numbers immediately.
        function dhRenderCriticalPoint(criticalPoint) {
            var el = document.getElementById('dhCriticalPointSummary');
            if (!el) return;
            if (!criticalPoint) { el.hidden = true; return; }
            el.hidden = false;
            el.innerHTML = 'Critical point: Level ' + criticalPoint.level + ' (' + criticalPoint.depth + 'ft) at t='
                + criticalPoint.time.toFixed(1) + 'min &mdash; ' + criticalPoint.timeToSurface.toFixed(1)
                + 'min to surface if bailed there'
                + (criticalPoint.requiresDeco ? ' (' + criticalPoint.totalStopTime.toFixed(1) + 'min of stops)' : ' (NDL)');
            // Pablo, 2026-09-25: "the critical pill at the top, make it
            // col-12" - full width instead of the compact pill's usual
            // auto-sized, inline footprint.
            el.className = 'col-12 ' + (criticalPoint.requiresDeco ? 'dh-gas-result-pill is-compact is-danger' : 'dh-gas-result-pill is-compact');
        }

        function dhCallMultiLevelDivePlanner(diveProfile, levels, dhCalcSplash) {
            var payload = {
                // Deco Table API v2's own MultiLevelDivePlanner example
                // includes a top-level "diveType" field (also present on
                // MultiDivePlanner, absent from DecoPlanner's) that v1 never
                // sent - the doc doesn't say what values it accepts or
                // whether it's required, so this is a best guess ("technical"
                // matches this app's own multi-level/CC-heavy use case) worth
                // confirming with the backend rather than trusting blindly.
                diveType: 'technical',
                mode: diveProfile.mode,
                gradientFactors: diveProfile.gradientFactors,
                rate: diveProfile.rate,
                bottomGas: diveProfile.bottomGas,
                decoGases: diveProfile.decoGases,
                levels: levels
                // surfaceTime deliberately omitted (optional per the API) -
                // its slider is hidden in multi-level mode (Pablo, 2026-09-24:
                // "the surface time slider is not needed either"), so the
                // hidden input still holds whatever stale value it last had
                // and shouldn't be sent as if the diver chose it (Pablo,
                // 2026-09-25: "the O2 Toxicity does not make sense" - this
                // was surfacing a residual-CNS note for a surface interval
                // nobody actually set for this dive).
            };
            if (diveProfile.mode === 'CC') payload.setpoint = diveProfile.setpoint;

            $.ajax({
                // Deco Table API v2 (Pablo, 2026-09-25 -
                // https://claude.ai/artifact/CQMT3MahLtqEgA68z7Fgta) moved
                // MultiLevelDivePlanner off decoplanning.azurewebsites.net
                // (v1) onto decoplanningapi.azurewebsites.net, alongside
                // DecoPlanner - same host key for both now.
                url: `https://decoplanningapi.azurewebsites.net/api/MultiLevelDivePlanner?code=<?php echo $DivePlanningApiV2HostKey; ?>`,
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(payload),
                crossDomain: true,
                complete: function () {
                    if (dhCalcSplash) dhCalcSplash.classList.remove('is-visible');
                },
                success: function (response) {
                    console.log('MultiLevelDivePlanner success:', response);

                    // Deco Table API v2 wraps everything - baseline included -
                    // under `scenarios` now (Pablo, 2026-09-25 -
                    // https://claude.ai/artifact/CQMT3MahLtqEgA68z7Fgta);
                    // `|| response` falls back to v1's shape, where baseline's
                    // profile/criticalPoint/o2Exposure lived at the response's
                    // own top level and only the what-if scenarios nested
                    // under their own {requiresDeco, profile} key.
                    var scenarios = response.scenarios || {
                        baseline: { profile: response.profile, criticalPoint: response.criticalPoint, o2Exposure: response.o2Exposure },
                        lostDecoGas: response.lostDecoGas,
                        minDeco: response.minDeco,
                        bailout: response.bailout,
                    };

                    window.dhScenarios.baseline = dhNormalizeScenario(scenarios.baseline);
                    window.dhScenarios.lostDecoGas = dhNormalizeScenario(scenarios.lostDecoGas);
                    window.dhScenarios.minDeco = dhNormalizeScenario(scenarios.minDeco);
                    window.dhScenarios.bailout = dhNormalizeScenario(scenarios.bailout);

                    // Reshaped to the same flat-array convention every
                    // downstream reader (filter3/6/7's change handlers, the
                    // PDF export) already expects from single-level's own
                    // globalResponse.
                    globalResponse = {
                        baseline: window.dhScenarios.baseline.profile,
                        lostDecoGas: window.dhScenarios.lostDecoGas.profile,
                        minDeco: window.dhScenarios.minDeco.profile,
                        bailout: window.dhScenarios.bailout.profile,
                    };

                    // Set before renderProfileChart() below so it can draw the
                    // marker in the same pass, rather than needing a second redraw.
                    window.lastCriticalPoint = window.dhScenarios.baseline.criticalPoint;

                    // add5min/add10ft/short5min/short10ft have no multilevel
                    // equivalent from this endpoint - their chips stay
                    // hidden below, so aliasing them to the baseline profile
                    // is just a harmless placeholder for renderProfileChart's
                    // own unconditional field access.
                    var adapted = {
                        baseline: globalResponse.baseline,
                        add5min: globalResponse.baseline, add10ft: globalResponse.baseline,
                        short5min: globalResponse.baseline, short10ft: globalResponse.baseline,
                        lostDecoGas: globalResponse.lostDecoGas,
                        minDeco: globalResponse.minDeco,
                        bailout: globalResponse.bailout
                    };

                    renderProfileChart(adapted);
                    baselineRTDT = generateDecoTable(window.dhScenarios.baseline);

                    // Bailout-to-OC table, same as single-level (Pablo,
                    // 2026-09-25: "for multilevel, for CC, please include
                    // the bailout table in the pdf, same we did for single
                    // level") - populates #BOTableContainer (shown by
                    // filter7's own toggle above) and window.lastBailoutTotals
                    // (read by the PDF's dive-times section).
                    if (modeOCOrCC == "CC") {
                        window.lastBailoutTotals = generateDecoTable(window.dhScenarios.bailout, 1);
                    }

                    if (baselineRTDT[1] == 0) {
                        document.getElementById("decoTableContainer").style.display = "none";
                        dhSetChartColClass("col-lg-12 col-12");
                        dhSetDecoTableCollapseBtnVisible(false);
                        document.getElementById("exportDecoPlanPdfBtn").hidden = true;
                    } else {
                        document.getElementById("decoTableContainer").style.display = "block";
                        dhSetChartColClass("col-lg-6 col-12");
                        dhSetDecoTableCollapseBtnVisible(true);
                        document.getElementById("exportDecoPlanPdfBtn").hidden = false;
                    }

                    // A trimmed-down What if...? for multi-level (Pablo,
                    // 2026-09-25: "bringing back a few What if...? scenarios.
                    // Same as in single level, but less scenarios") - only
                    // the 3 this endpoint actually returns (bailout,
                    // lostDecoGas, minDeco); the other 4 chips (extend/
                    // shorten bottom time, +/-10ft) have no equivalent here
                    // and stay hidden.
                    filter3RTDT = calculateDecoTime(window.dhScenarios.lostDecoGas);
                    filter6RTDT = calculateDecoTime(window.dhScenarios.minDeco);
                    filter7RTDT = calculateDecoTime(window.dhScenarios.bailout);
                    var fab = document.getElementById('dhWhatIfFab');
                    if (fab) fab.hidden = false;
                    document.getElementById('filter1Container').style.display = "none";
                    document.getElementById('filter2Container').style.display = "none";
                    document.getElementById('filter4Container').style.display = "none";
                    document.getElementById('filter5Container').style.display = "none";
                    // Same threshold single-level uses: "lost deco gases"
                    // is only meaningful if there's a real staged gas to
                    // lose - for CC, slot 1 is always the mandatory bailout
                    // gas, so it takes 2+ entries before there's an actual
                    // extra deco gas on top of that.
                    document.getElementById('filter3Container').style.display =
                        ((modeOCOrCC === "OC" && diveProfile.decoGases.length === 0) ||
                         (modeOCOrCC === "CC" && diveProfile.decoGases.length < 2)) ? "none" : "block";
                    document.getElementById('filter7Container').style.display =
                        (modeOCOrCC === "OC") ? "none" : "block";

                    conveyor = window.dhScenarios.baseline.conveyor || window.dhScenarios.baseline.profile;
                    renderO2Toxicity(window.dhScenarios.baseline.o2Exposure);
                    dhRenderSafetyWarnings(window.dhScenarios.baseline);
                    timeLapseSlider.noUiSlider.updateOptions({
                        range: { 'min': 0, 'max': conveyor.length - 1 }
                    });
                    // Reset to the surface on every fresh calculation
                    // (Pablo, 2026-09-25).
                    timeLapseSlider.noUiSlider.set(0);

                    window.dhGasSacRates = {};
                    if (modeOCOrCC == "OC") {
                        document.getElementById("gasConsumptionRow").style.display = "block";
                        gasConsumption = calculateGasConsumption(window.dhScenarios.baseline);
                        renderGasConsumptionTable(gasConsumption, "bottom", window.dhScenarios.baseline);
                        renderGasConsumptionTable(gasConsumption, "deco", window.dhScenarios.baseline);
                        document.getElementById("gasConsumptionHeader").innerText = "Gas consumption (Baseline OC)";
                        document.getElementById("gasConsumptionBottomCol").style.display = "block";
                        document.getElementById("gasConsumptionDecoCol").style.display = "block";
                        document.getElementById("gasConsumptionBottomCol").className = "col-lg-6 col-12";
                        document.getElementById("gasConsumptionDecoCol").className = "col-lg-6 col-12";
                        document.getElementById("gasConsumptionDecoOrBOHeader").innerText = "Decompression Gases";
                    } else {
                        document.getElementById("gasConsumptionRow").style.display = "none";
                        document.getElementById("gasConsumptionHeader").innerText = "Gas consumption (Bailout to OC)";
                        document.getElementById("gasConsumptionBottomCol").style.display = "none";
                        document.getElementById("gasConsumptionDecoCol").style.display = "block";
                        document.getElementById("gasConsumptionDecoCol").className = "col-12";
                        document.getElementById("gasConsumptionDecoOrBOHeader").innerText = "Bailout Gases";
                    }

                    document.querySelectorAll(".form-check-input").forEach(function (cb) { cb.checked = false; });
                    if (typeof window.dhUpdateWhatIfVisibility === 'function') window.dhUpdateWhatIfVisibility();

                    dhRenderCriticalPoint(window.dhScenarios.baseline.criticalPoint);
                },
                error: function (xhr, status, error) {
                    console.error('MultiLevelDivePlanner error:', error);
                    console.error('Response:', xhr.responseText);
                    alert('Could not calculate the multi-level dive plan. ' + (xhr.responseText || error));
                }
            });
        }
    </script>

    {{-- Show modal warning --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let modal = new bootstrap.Modal(document.getElementById("modalWarning"));
            modal.show();
        });

        
    </script>

    <script>
        let currentSite = @json($currentSite);
        console.log("Current Site:", currentSite);

        let allSites = @json($allSites);
        console.log(allSites);

        let setPointOCOrCC = 1.4;
        let modeOCOrCC = "OC";

        // Advanced settings, registered users only (Pablo, 2026-09-25) -
        // always default to both guardrails ON on a fresh page load, never
        // persisted, so a diver can't accidentally carry a relaxed
        // configuration into a new session without noticing. Declared this
        // early since sliders that read them (GF, setpoint, depth) fire
        // their own 'update' synchronously during page load, before most
        // other script blocks have run.
        let dhRecommendBestGases = true;
        let dhRemoveSafetyGuards = false;

        //var depth = parseInt(document.getElementById("labelDepth").textContent);
        @if( !is_null($currentSite))
            let depth = currentSite.maxDepth;
        @else
            let depth = 100;    // default depth
        @endif
    </script>

    {{-- Gas density and END--}}
    <script>
        function calculateLoopGasDensity(depth, setpoint, diluentO2, diluentHe, waterVapor) {
            // Constants
            const R = 0.0821; // Gas constant in L·atm·K^−1·mol^−1
            //const T = 293;//310.15; // Body temperature in Kelvin (37°C)
            const M_H2O = 18.015; // Molar mass of water vapor (g/mol)
            const M_O2 = 32.00; // Molar mass of oxygen (g/mol)
            const M_N2 = 28.01; // Molar mass of nitrogen (g/mol)
            const M_He = 4.002; // Molar mass of helium (g/mol)
            T = 310;
            if(waterVapor)
                baseVaporPressureH2O = 0.062; // Water vapor pressure at sea level (37°C, ATA)
            else
                baseVaporPressureH2O = 0;

            // Step 1: Calculate ambient pressure at depth (in ATA)
            const ambientPressure = depth / 33 + 1;

            // Step 2: Scale water vapor pressure by ambient pressure
            const ppH2O = baseVaporPressureH2O * (ambientPressure / 1.0); // Scaled water vapor pressure

            // Step 3: Calculate PPO2 contribution from diluent
            const ppDiluentO2 = diluentO2 * (ambientPressure - setpoint);

            // Step 4: Calculate pure O2 PPO2 (setpoint minus diluent PPO2 contribution)
            const ppPureO2 = setpoint - ppDiluentO2;

            // Step 5: Calculate diluent partial pressure
            const ppDiluent = ambientPressure - ppPureO2;

            // Step 6: Calculate diluent nitrogen fraction
            const diluentN2 = 1 - diluentO2 - diluentHe;

            // Step 7: Calculate partial pressures of He and N2 in the loop
            const ppHeLoop = diluentHe * ppDiluent;
            const ppN2Loop = diluentN2 * ppDiluent;

            // Step 8: Calculate loop gas fractions
            const fH2O = ppH2O / ambientPressure;
            const fO2 = setpoint / ambientPressure; // Setpoint directly determines O2 fraction
            const fHe = ppHeLoop / ambientPressure;
            const fN2 = ppN2Loop / ambientPressure;

            // Step 9: Calculate effective molar mass of the loop gas mixture
            const molarMass = (fH2O * M_H2O) + (fO2 * M_O2) + (fHe * M_He) + (fN2 * M_N2);

            // Step 10: Calculate loop gas density
            const density = (molarMass * ambientPressure) / (R * T);

            // Return the result
            return density; // Density in g/L
        };

        function calculateENDCCR(depth, setpoint, diluentO2, diluentHe, isOxygenNarcotic) {
            // Step 1: Calculate ambient pressure in ATA (depth in feet)
            const ambientPressure = depth / 33 + 1;

            // Step 2: Calculate the combined partial pressure of He and N2
            const ppHeN2 = ambientPressure - setpoint;

            // Step 3: Calculate the loop oxygen fraction
            const loopO2 = setpoint / ambientPressure;

            // Step 4: Calculate the combined fraction of He and N2
            const remainingFraction = 1 - loopO2;

            // Step 5: Calculate the diluent nitrogen fraction
            const diluentN2 = 1 - diluentO2 - diluentHe;

            // Step 6: Proportionally divide He and N2 in the loop
            const loopHe = (diluentHe / (diluentHe + diluentN2)) * remainingFraction;
            const loopN2 = (diluentN2 / (diluentHe + diluentN2)) * remainingFraction;

            // Step 7: Calculate the narcotic fraction based on whether oxygen is considered narcotic
            let narcoticFraction;
            if (isOxygenNarcotic) {
                narcoticFraction = loopO2 + loopN2;
            } else {
                narcoticFraction = loopN2;
            }

            // Step 8: Calculate the Equivalent Narcotic Depth (END)
            const END = (depth + 33) * narcoticFraction - 33;

            // Step 9: Return the result
            return Math.max(END,0);
        }

        function updateGasDensity(O2, He, depth, label) {
            let gasDensity = 0;
            // check if we are calculating loop denisty or gas density OC
            if(modeOCOrCC == "OC") {
                // Constants for molecular weights (g/mol)
                const molecularWeights = {
                    O2: 32,
                    N2: 28,
                    He: 4
                };

                var fractionO2 = O2 / 100;
                var fractionHe = He / 100;
                var fractionN2 = 1 - fractionO2 - fractionHe;
                var ambientPressure = depth / 33 + 1;

                // Calculate gas density
                gasDensity = (
                    (fractionO2 * molecularWeights.O2 +
                    fractionN2 * molecularWeights.N2 +
                    fractionHe * molecularWeights.He) *
                    ambientPressure
                ) / 22.4; // Use 22.4 L/mol at standard temperature and pressure
            } else {
                var currentSetPoint = parseFloat(labelSetpoint.value);
                gasDensity = calculateLoopGasDensity(depth, currentSetPoint, O2 / 100, He / 100, 1);
            }
            // Round the result to 2 decimal places
            const densityRounded = gasDensity.toFixed(2);

            // Update the element in HTML
            const gasDensityLabel = label;
            if (gasDensityLabel) {
                gasDensityLabel.textContent = densityRounded;
            }

            if (densityRounded > 6.2) {
                gasDensityLabel.classList.remove("is-warn");
                gasDensityLabel.classList.add("is-danger");
            } else if (densityRounded > 5.2) {
                gasDensityLabel.classList.remove("is-danger");
                gasDensityLabel.classList.add("is-warn");
            } else {
                gasDensityLabel.classList.remove("is-warn", "is-danger");
                gasDensityLabel.classList.add("is-safe");
            }

            
        }
    
        function updateEND(O2, He, depth) {
            //console.log("Updating END");
            let bottomGasEND = 0;
            labelBottomGasEND = document.getElementById("labelBottomGasEND");

            if(modeOCOrCC == "OC") {
                var ambientPressure = depth / 33 +1;
                var bottomGasPPHe = ambientPressure * He / 100;
                var bottomGasENDPressure = ambientPressure - bottomGasPPHe;
                bottomGasEND = (bottomGasENDPressure - 1 ) * 33;
                

                   
            } else {
                bottomGasEND = calculateENDCCR(depth, parseFloat(labelSetpoint.value), O2 / 100, He / 100, 1); 
            }

            // adjust the unit
            if(modeImpOrMetric === "imp")
                labelBottomGasEND.textContent = Math.max(0,(bottomGasEND).toFixed(0));
            else
                labelBottomGasEND.textContent = (Math.max(0,(bottomGasEND)) * 0.3048).toFixed(0);

            if (bottomGasEND > 130) {
                labelBottomGasEND.classList.remove("is-warn");
                labelBottomGasEND.classList.add("is-danger");
            } else if (bottomGasEND > 100) {
                labelBottomGasEND.classList.remove("is-danger");
                labelBottomGasEND.classList.add("is-warn");
            } else {
                labelBottomGasEND.classList.remove("is-warn", "is-danger");
                labelBottomGasEND.classList.add("is-safe");
            } 
        }

    </script>
    {{-- Bottom gas chart --}}
    <script>
        
        // Get the canvas element
        const bottomGasStackedBarChartElement = document.getElementById('bottomGasStackedBar').getContext('2d');
        let labelHorizontalOffset = -28;

        // Create the chart with a custom plugin for labels
        const bottomGasstackedBarChart = new Chart(bottomGasStackedBarChartElement, {
            type: 'bar', // Bar chart type
            data: {
                labels: [''], // X-axis labels
                datasets: [
                    {
                        label: 'Oxygen',
                        data: [18], // Data points for this dataset
                        backgroundColor: '#2e7d4f', // Bar color
                        borderRadius: 0, // Rounded corners
                        barPercentage: 1 // Adjust bar width (smaller bars)
                    },
                    {
                        label: 'Helium',
                        data: [45], // Data points for this dataset
                        backgroundColor: '#0e7c9e', // Bar color
                        borderRadius: 0, // Rounded corners
                        barPercentage: 1 // Adjust bar width (smaller bars)
                    },
                    {
                        label: 'Nitrogen',
                        data: [37], // Data points for this dataset
                        backgroundColor: '#5a6b78', // Bar color
                        borderRadius: 0, // Rounded corners
                        barPercentage: 1 // Adjust bar width (smaller bars)
                    }
                ]
            },
            options: {
                responsive: true, // Makes the chart responsive
                // Without this, Chart.js ignores the canvas's own CSS height
                // and instead derives it from the container's width divided
                // by a default aspect ratio (~2:1) - on this ~210px-wide
                // tank column that works out to roughly half the height we
                // actually set, which is why the gas-color bar never reached
                // the top of the tank's transparent cutout (Pablo,
                // 2026-09-17: "make the bar chart taller to cover all the
                // tank mask").
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false, // Hide legend
                    }
                },
                scales: {
                    x: {
                        stacked: true, // Enable stacking for the x-axis
                        display: false // Hides the x-axis scale
                    },
                    y: {
                        stacked: true, // Enable stacking for the y-axis
                        display: false // Hides the y-axis scale
                    }
                },
                layout: {
                    // Measured directly from the mask PNG's alpha channel:
                    // the transparent "window" cut into the tank body starts
                    // at y=58/300 (double), 57/300 (single) of the source
                    // image. Scaled to this 156px-tall canvas (matching the
                    // now-fixed 156px parent, see maintainAspectRatio note
                    // above), that's ~29px/28px down from the canvas top -
                    // top: 28 sits just inside that so the gas-color fill
                    // reaches the mask's northmost transparent point without
                    // gap (Pablo, 2026-09-17: "find the northest point in
                    // each mask that is transparent - that's the limit to
                    // where we want the bar chart to render up to").
                    padding: {
                        left: 14,
                        right: 14,
                        top: 28,
                        bottom: 3
                    }
                }
            },
            plugins: [
                {
                    id: 'valueLabels', // Custom plugin ID
                    afterDatasetsDraw: function (chart) {
                        const ctx = chart.ctx;
                        chart.data.datasets.forEach((dataset, i) => {
                            const meta = chart.getDatasetMeta(i);
                            meta.data.forEach((bar, index) => {
                                const data = dataset.data[index];
                                if (data != 100 && data != 0) {
                                    ctx.font = '8px Roboto';
                                    ctx.fillStyle = '#FFF'; // Label color
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'middle'; // Centers text vertically
                                    ctx.fillText(data + '%', bar.x + labelHorizontalOffset, bar.y + 7); // Position label slightly above the bar
                                    
                                } else if (data == 100) {
                                    ctx.font = '8px Roboto';
                                    ctx.fillStyle = '#FFF'; // Label color
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'middle'; // Centers text vertically
                                    ctx.fillText(data + '%', bar.x +labelHorizontalOffset , bar.y + 49); // Position label slightly above the bar
                                }
                            });
                        });
                    }
                }
            ]
        });

        function updateBottomGasChart(oxygen, helium) {

            // Ensure the passed values are integers
            oxygen = parseInt(oxygen, 10);
            helium = parseInt(helium, 10);

            // Calculate Nitrogen
            const nitrogen = 100 - (oxygen + helium);

            // Update the chart data
            bottomGasstackedBarChart.data.datasets = [
                {
                    label: 'Oxygen',
                    data: [oxygen],
                    backgroundColor: '#2e7d4f'
                },
                {
                    label: 'Helium',
                    data: [helium],
                    backgroundColor: '#0e7c9e'
                },
                {
                    label: 'Nitrogen',
                    data: [nitrogen],
                    backgroundColor: '#5a6b78'
                }
            ];

            // Refresh the chart
            bottomGasstackedBarChart.update();

        }
    </script>

    {{-- Script to reset calculation area --}}
    <script>
        function resetCalculationArea() {

            document.getElementById("profileChartAndTable").style.display = "none";
            document.getElementById("decoTableContainer").style.display = "block";
            document.getElementById("BOTableContainer").style.display = "none";
            document.getElementById("decoTableTitle").innerText = "Decompression Table";
            document.getElementById("labelWhatIfRunTime").innerText="-";
            document.getElementById("labelWhatIfRunTimeDiff").innerText="-";
            document.getElementById("labelWhatIfDecoTime").innerText="-";
            document.getElementById("labelWhatIfDecoTimeDiff").innerText="-";
            // Multi-level's critical point summary/chart marker - stale
            // after any input change until the next Calculate re-populates
            // it (also guards single-level from ever drawing a leftover
            // marker from a previous multi-level run).
            var dhCpEl = document.getElementById('dhCriticalPointSummary');
            if (dhCpEl) dhCpEl.hidden = true;
            window.lastCriticalPoint = null;
            // Any input change invalidates whatever's on screen (already hidden
            // above) - bring the inputs panel back if Calculate had collapsed it.
            if (typeof window.dhDecoInputsSetOpen === 'function') window.dhDecoInputsSetOpen(true);
        }
    </script>

    {{-- Deco gas 1 chart --}}
    <script>
        
        // Get the canvas element
        const decoGas1StackedBarChartElement = document.getElementById('decoGas1StackedBar').getContext('2d');

        // Create the chart with a custom plugin for labels
        const decoGas1stackedBarChart = new Chart(decoGas1StackedBarChartElement, {
            type: 'bar', // Bar chart type
            data: {
                labels: [''], // X-axis labels
                datasets: [
                    {
                        label: 'Oxygen',
                        data: [18], // Data points for this dataset
                        backgroundColor: '#2e7d4f', // Bar color
                        borderRadius: 0, // Rounded corners
                        barPercentage: 1 // Adjust bar width (smaller bars)
                    },
                    {
                        label: 'Helium',
                        data: [45], // Data points for this dataset
                        backgroundColor: '#0e7c9e', // Bar color
                        borderRadius: 0, // Rounded corners
                        barPercentage: 1 // Adjust bar width (smaller bars)
                    },
                    {
                        label: 'Nitrogen',
                        data: [37], // Data points for this dataset
                        backgroundColor: '#5a6b78', // Bar color
                        borderRadius: 0, // Rounded corners
                        barPercentage: 1 // Adjust bar width (smaller bars)
                    }
                ]
            },
            options: {
                responsive: true, // Makes the chart responsive
                // Without this, Chart.js ignores the canvas's own CSS height
                // and instead derives it from the container's width divided
                // by a default aspect ratio (~2:1) - on this ~210px-wide
                // tank column that works out to roughly half the height we
                // actually set, which is why the gas-color bar never reached
                // the top of the tank's transparent cutout (Pablo,
                // 2026-09-17: "make the bar chart taller to cover all the
                // tank mask").
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false, // Hide legend
                    }
                },
                scales: {
                    x: {
                        stacked: true, // Enable stacking for the x-axis
                        display: false // Hides the x-axis scale
                    },
                    y: {
                        stacked: true, // Enable stacking for the y-axis
                        display: false // Hides the y-axis scale
                    }
                },
                layout: {
                    // Measured directly from the mask PNG's alpha channel:
                    // the transparent "window" cut into the tank body starts
                    // at y=58/300 (double), 57/300 (single) of the source
                    // image. Scaled to this 156px-tall canvas (matching the
                    // now-fixed 156px parent, see maintainAspectRatio note
                    // above), that's ~29px/28px down from the canvas top -
                    // top: 28 sits just inside that so the gas-color fill
                    // reaches the mask's northmost transparent point without
                    // gap (Pablo, 2026-09-17: "find the northest point in
                    // each mask that is transparent - that's the limit to
                    // where we want the bar chart to render up to").
                    padding: {
                        left: 14,
                        right: 14,
                        top: 28,
                        bottom: 3
                    }
                }
            },
            plugins: [
                {
                    id: 'valueLabels', // Custom plugin ID
                    afterDatasetsDraw: function (chart) {
                        const ctx = chart.ctx;
                        chart.data.datasets.forEach((dataset, i) => {
                            const meta = chart.getDatasetMeta(i);
                            meta.data.forEach((bar, index) => {
                                const data = dataset.data[index];
                                if (data != 100 && data != 0) {
                                    ctx.font = '8px Roboto';
                                    ctx.fillStyle = '#FFF'; // Label color
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'middle'; // Centers text vertically
                                    ctx.fillText(data + '%', bar.x, bar.y + 7); // Position label slightly above the bar
                                    
                                } else if (data == 100) {
                                    ctx.font = '8px Roboto';
                                    ctx.fillStyle = '#FFF'; // Label color
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'middle'; // Centers text vertically
                                    ctx.fillText(data + '%', bar.x , bar.y + 49); // Position label slightly above the bar
                                }
                            });
                        });
                    }
                }
            ]
        });

        function updateDecoGas1Chart(oxygen, helium) {

            // Ensure the passed values are integers
            oxygen = parseInt(oxygen, 10);
            helium = parseInt(helium, 10);

            // Calculate Nitrogen
            const nitrogen = 100 - (oxygen + helium);

            // Update the chart data
            decoGas1stackedBarChart.data.datasets = [
                {
                    label: 'Oxygen',
                    data: [oxygen],
                    backgroundColor: '#2e7d4f'
                },
                {
                    label: 'Helium',
                    data: [helium],
                    backgroundColor: '#0e7c9e'
                },
                {
                    label: 'Nitrogen',
                    data: [nitrogen],
                    backgroundColor: '#5a6b78'
                }
            ];

            // Refresh the chart
            decoGas1stackedBarChart.update();

        }
    </script>

    {{-- Deco gas 2 chart --}}
     <script>
        
        // Get the canvas element
        const decoGas2StackedBarChartElement = document.getElementById('decoGas2StackedBar').getContext('2d');

        // Create the chart with a custom plugin for labels
        const decoGas2stackedBarChart = new Chart(decoGas2StackedBarChartElement, {
            type: 'bar', // Bar chart type
            data: {
                labels: [''], // X-axis labels
                datasets: [
                    {
                        label: 'Oxygen',
                        data: [18], // Data points for this dataset
                        backgroundColor: '#2e7d4f', // Bar color
                        borderRadius: 0, // Rounded corners
                        barPercentage: 1 // Adjust bar width (smaller bars)
                    },
                    {
                        label: 'Helium',
                        data: [45], // Data points for this dataset
                        backgroundColor: '#0e7c9e', // Bar color
                        borderRadius: 0, // Rounded corners
                        barPercentage: 1 // Adjust bar width (smaller bars)
                    },
                    {
                        label: 'Nitrogen',
                        data: [37], // Data points for this dataset
                        backgroundColor: '#5a6b78', // Bar color
                        borderRadius: 0, // Rounded corners
                        barPercentage: 1 // Adjust bar width (smaller bars)
                    }
                ]
            },
            options: {
                responsive: true, // Makes the chart responsive
                // Without this, Chart.js ignores the canvas's own CSS height
                // and instead derives it from the container's width divided
                // by a default aspect ratio (~2:1) - on this ~210px-wide
                // tank column that works out to roughly half the height we
                // actually set, which is why the gas-color bar never reached
                // the top of the tank's transparent cutout (Pablo,
                // 2026-09-17: "make the bar chart taller to cover all the
                // tank mask").
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false, // Hide legend
                    }
                },
                scales: {
                    x: {
                        stacked: true, // Enable stacking for the x-axis
                        display: false // Hides the x-axis scale
                    },
                    y: {
                        stacked: true, // Enable stacking for the y-axis
                        display: false // Hides the y-axis scale
                    }
                },
                layout: {
                    // Measured directly from the mask PNG's alpha channel:
                    // the transparent "window" cut into the tank body starts
                    // at y=58/300 (double), 57/300 (single) of the source
                    // image. Scaled to this 156px-tall canvas (matching the
                    // now-fixed 156px parent, see maintainAspectRatio note
                    // above), that's ~29px/28px down from the canvas top -
                    // top: 28 sits just inside that so the gas-color fill
                    // reaches the mask's northmost transparent point without
                    // gap (Pablo, 2026-09-17: "find the northest point in
                    // each mask that is transparent - that's the limit to
                    // where we want the bar chart to render up to").
                    padding: {
                        left: 14,
                        right: 14,
                        top: 28,
                        bottom: 3
                    }
                }
            },
            plugins: [
                {
                    id: 'valueLabels', // Custom plugin ID
                    afterDatasetsDraw: function (chart) {
                        const ctx = chart.ctx;
                        chart.data.datasets.forEach((dataset, i) => {
                            const meta = chart.getDatasetMeta(i);
                            meta.data.forEach((bar, index) => {
                                const data = dataset.data[index];
                                if (data != 100 && data != 0) {
                                    ctx.font = '8px Roboto';
                                    ctx.fillStyle = '#FFF'; // Label color
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'middle'; // Centers text vertically
                                    ctx.fillText(data + '%', bar.x, bar.y + 7); // Position label slightly above the bar
                                    
                                } else if (data == 100) {
                                    ctx.font = '8px Roboto';
                                    ctx.fillStyle = '#FFF'; // Label color
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'middle'; // Centers text vertically
                                    ctx.fillText(data + '%', bar.x , bar.y + 49); // Position label slightly above the bar
                                }
                            });
                        });
                    }
                }
            ]
        });

        function updateDecoGas2Chart(oxygen, helium) {

            // Ensure the passed values are integers
            oxygen = parseInt(oxygen, 10);
            helium = parseInt(helium, 10);

            // Calculate Nitrogen
            const nitrogen = 100 - (oxygen + helium);

            // Update the chart data
            decoGas2stackedBarChart.data.datasets = [
                {
                    label: 'Oxygen',
                    data: [oxygen],
                    backgroundColor: '#2e7d4f'
                },
                {
                    label: 'Helium',
                    data: [helium],
                    backgroundColor: '#0e7c9e'
                },
                {
                    label: 'Nitrogen',
                    data: [nitrogen],
                    backgroundColor: '#5a6b78'
                }
            ];

            // Refresh the chart
            decoGas2stackedBarChart.update();

        }
    </script>

    {{-- Deco gas 3 chart --}}
    <script>
        
        // Get the canvas element
        const decoGas3StackedBarChartElement = document.getElementById('decoGas3StackedBar').getContext('2d');

        // Create the chart with a custom plugin for labels
        const decoGas3stackedBarChart = new Chart(decoGas3StackedBarChartElement, {
            type: 'bar', // Bar chart type
            data: {
                labels: [''], // X-axis labels
                datasets: [
                    {
                        label: 'Oxygen',
                        data: [18], // Data points for this dataset
                        backgroundColor: '#2e7d4f', // Bar color
                        borderRadius: 0, // Rounded corners
                        barPercentage: 1 // Adjust bar width (smaller bars)
                    },
                    {
                        label: 'Helium',
                        data: [45], // Data points for this dataset
                        backgroundColor: '#0e7c9e', // Bar color
                        borderRadius: 0, // Rounded corners
                        barPercentage: 1 // Adjust bar width (smaller bars)
                    },
                    {
                        label: 'Nitrogen',
                        data: [37], // Data points for this dataset
                        backgroundColor: '#5a6b78', // Bar color
                        borderRadius: 0, // Rounded corners
                        barPercentage: 1 // Adjust bar width (smaller bars)
                    }
                ]
            },
            options: {
                responsive: true, // Makes the chart responsive
                // Without this, Chart.js ignores the canvas's own CSS height
                // and instead derives it from the container's width divided
                // by a default aspect ratio (~2:1) - on this ~210px-wide
                // tank column that works out to roughly half the height we
                // actually set, which is why the gas-color bar never reached
                // the top of the tank's transparent cutout (Pablo,
                // 2026-09-17: "make the bar chart taller to cover all the
                // tank mask").
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false, // Hide legend
                    }
                },
                scales: {
                    x: {
                        stacked: true, // Enable stacking for the x-axis
                        display: false // Hides the x-axis scale
                    },
                    y: {
                        stacked: true, // Enable stacking for the y-axis
                        display: false // Hides the y-axis scale
                    }
                },
                layout: {
                    // Measured directly from the mask PNG's alpha channel:
                    // the transparent "window" cut into the tank body starts
                    // at y=58/300 (double), 57/300 (single) of the source
                    // image. Scaled to this 156px-tall canvas (matching the
                    // now-fixed 156px parent, see maintainAspectRatio note
                    // above), that's ~29px/28px down from the canvas top -
                    // top: 28 sits just inside that so the gas-color fill
                    // reaches the mask's northmost transparent point without
                    // gap (Pablo, 2026-09-17: "find the northest point in
                    // each mask that is transparent - that's the limit to
                    // where we want the bar chart to render up to").
                    padding: {
                        left: 14,
                        right: 14,
                        top: 28,
                        bottom: 3
                    }
                }
            },
            plugins: [
                {
                    id: 'valueLabels', // Custom plugin ID
                    afterDatasetsDraw: function (chart) {
                        const ctx = chart.ctx;
                        chart.data.datasets.forEach((dataset, i) => {
                            const meta = chart.getDatasetMeta(i);
                            meta.data.forEach((bar, index) => {
                                const data = dataset.data[index];
                                if (data != 100 && data != 0) {
                                    ctx.font = '8px Roboto';
                                    ctx.fillStyle = '#FFF'; // Label color
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'middle'; // Centers text vertically
                                    ctx.fillText(data + '%', bar.x, bar.y + 7); // Position label slightly above the bar
                                    
                                } else if (data == 100) {
                                    ctx.font = '8px Roboto';
                                    ctx.fillStyle = '#FFF'; // Label color
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'middle'; // Centers text vertically
                                    ctx.fillText(data + '%', bar.x , bar.y + 49); // Position label slightly above the bar
                                }
                            });
                        });
                    }
                }
            ]
        });

        function updateDecoGas3Chart(oxygen, helium) {

            // Ensure the passed values are integers
            oxygen = parseInt(oxygen, 10);
            helium = parseInt(helium, 10);

            // Calculate Nitrogen
            const nitrogen = 100 - (oxygen + helium);

            // Update the chart data
            decoGas3stackedBarChart.data.datasets = [
                {
                    label: 'Oxygen',
                    data: [oxygen],
                    backgroundColor: '#2e7d4f'
                },
                {
                    label: 'Helium',
                    data: [helium],
                    backgroundColor: '#0e7c9e'
                },
                {
                    label: 'Nitrogen',
                    data: [nitrogen],
                    backgroundColor: '#5a6b78'
                }
            ];

            // Refresh the chart
            decoGas3stackedBarChart.update();

        }
    </script>

    {{-- Deco gas 4 chart --}}
    <script>
        
        // Get the canvas element
        const decoGas4StackedBarChartElement = document.getElementById('decoGas4StackedBar').getContext('2d');

        // Create the chart with a custom plugin for labels
        const decoGas4stackedBarChart = new Chart(decoGas4StackedBarChartElement, {
            type: 'bar', // Bar chart type
            data: {
                labels: [''], // X-axis labels
                datasets: [
                    {
                        label: 'Oxygen',
                        data: [18], // Data points for this dataset
                        backgroundColor: '#2e7d4f', // Bar color
                        borderRadius: 0, // Rounded corners
                        barPercentage: 1 // Adjust bar width (smaller bars)
                    },
                    {
                        label: 'Helium',
                        data: [45], // Data points for this dataset
                        backgroundColor: '#0e7c9e', // Bar color
                        borderRadius: 0, // Rounded corners
                        barPercentage: 1 // Adjust bar width (smaller bars)
                    },
                    {
                        label: 'Nitrogen',
                        data: [37], // Data points for this dataset
                        backgroundColor: '#5a6b78', // Bar color
                        borderRadius: 0, // Rounded corners
                        barPercentage: 1 // Adjust bar width (smaller bars)
                    }
                ]
            },
            options: {
                responsive: true, // Makes the chart responsive
                // Without this, Chart.js ignores the canvas's own CSS height
                // and instead derives it from the container's width divided
                // by a default aspect ratio (~2:1) - on this ~210px-wide
                // tank column that works out to roughly half the height we
                // actually set, which is why the gas-color bar never reached
                // the top of the tank's transparent cutout (Pablo,
                // 2026-09-17: "make the bar chart taller to cover all the
                // tank mask").
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false, // Hide legend
                    }
                },
                scales: {
                    x: {
                        stacked: true, // Enable stacking for the x-axis
                        display: false // Hides the x-axis scale
                    },
                    y: {
                        stacked: true, // Enable stacking for the y-axis
                        display: false // Hides the y-axis scale
                    }
                },
                layout: {
                    // Measured directly from the mask PNG's alpha channel:
                    // the transparent "window" cut into the tank body starts
                    // at y=58/300 (double), 57/300 (single) of the source
                    // image. Scaled to this 156px-tall canvas (matching the
                    // now-fixed 156px parent, see maintainAspectRatio note
                    // above), that's ~29px/28px down from the canvas top -
                    // top: 28 sits just inside that so the gas-color fill
                    // reaches the mask's northmost transparent point without
                    // gap (Pablo, 2026-09-17: "find the northest point in
                    // each mask that is transparent - that's the limit to
                    // where we want the bar chart to render up to").
                    padding: {
                        left: 14,
                        right: 14,
                        top: 28,
                        bottom: 3
                    }
                }
            },
            plugins: [
                {
                    id: 'valueLabels', // Custom plugin ID
                    afterDatasetsDraw: function (chart) {
                        const ctx = chart.ctx;
                        chart.data.datasets.forEach((dataset, i) => {
                            const meta = chart.getDatasetMeta(i);
                            meta.data.forEach((bar, index) => {
                                const data = dataset.data[index];
                                if (data != 100 && data != 0) {
                                    ctx.font = '8px Roboto';
                                    ctx.fillStyle = '#FFF'; // Label color
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'middle'; // Centers text vertically
                                    ctx.fillText(data + '%', bar.x, bar.y + 7); // Position label slightly above the bar
                                    
                                } else if (data == 100) {
                                    ctx.font = '8px Roboto';
                                    ctx.fillStyle = '#FFF'; // Label color
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'middle'; // Centers text vertically
                                    ctx.fillText(data + '%', bar.x , bar.y + 49); // Position label slightly above the bar
                                }
                            });
                        });
                    }
                }
            ]
        });

        function updateDecoGas4Chart(oxygen, helium) {

            // Ensure the passed values are integers
            oxygen = parseInt(oxygen, 10);
            helium = parseInt(helium, 10);

            // Calculate Nitrogen
            const nitrogen = 100 - (oxygen + helium);

            // Update the chart data
            decoGas4stackedBarChart.data.datasets = [
                {
                    label: 'Oxygen',
                    data: [oxygen],
                    backgroundColor: '#2e7d4f'
                },
                {
                    label: 'Helium',
                    data: [helium],
                    backgroundColor: '#0e7c9e'
                },
                {
                    label: 'Nitrogen',
                    data: [nitrogen],
                    backgroundColor: '#5a6b78'
                }
            ];

            // Refresh the chart
            decoGas4stackedBarChart.update();

        }
    </script>

    {{-- Scripts slider surface time --}}
    <script>
        var surfaceTimeSlider = document.getElementById('surfaceTimeSlider');
        var labelSurfaceTime = document.getElementById('labelSurfaceTime');


        noUiSlider.create(surfaceTimeSlider, {
            start: 48,
            connect: [true, false],
            range: {
                'min': 0.5,
                'max': 48
            },
            step: 0.5,
            

        });

        // Hide the tick mark labels
        var surfaceTimeSliderTicks = surfaceTimeSlider.querySelectorAll('.noUi-value-sub');
        surfaceTimeSliderTicks.forEach(function (surfaceTimeSlider) {
            surfaceTimeSlider.style.display = 'none';
        });

        surfaceTimeSlider.noUiSlider.on('update', function (values, handle) {
            var surfaceTimeSliderValue = values[handle];
            labelSurfaceTime.value = parseFloat(surfaceTimeSliderValue).toFixed(1);

            // reset calculation area
            resetCalculationArea()

        });

        surfaceTimeSlider.setAttribute('disabled', true);
        // The slider was already disabled but the paired text box was
        // still typeable, letting a user change the value through the
        // one door left open (Pablo, 2026-09-25: "also disable the
        // textbox so users cannot change the value").
        labelSurfaceTime.setAttribute('disabled', true);

        labelSurfaceTime.addEventListener('change', function () {
            var typedSurface = parseFloat(labelSurfaceTime.value);
            if (isNaN(typedSurface)) { labelSurfaceTime.value = parseFloat(surfaceTimeSlider.noUiSlider.get()).toFixed(1); return; }
            surfaceTimeSlider.noUiSlider.set(typedSurface);
        });
    </script>

    {{-- Scripts slider GFs --}}
    <script>
        var GFLSlider = document.getElementById('GFLSlider');
        var labelGFL = document.getElementById('labelGFL');


        noUiSlider.create(GFLSlider, {
            start: {{ $decoPrefs['gfLow'] ?? 40 }}, // diver's saved GF Low, if any (Pablo, 2026-09-18)
            connect: [true, false],
            range: {
                'min': 10,
                'max': 100
            },
            step: 1,
            

        });

        // Hide the tick mark labels
        var GFLSliderTicks = GFLSlider.querySelectorAll('.noUi-value-sub');
        GFLSliderTicks.forEach(function (GFLSlider) {
            GFLSlider.style.display = 'none';
        });

        GFLSlider.noUiSlider.on('update', function (values, handle) {
            var GFLSliderValue = values[handle];
            labelGFL.value = parseInt(GFLSliderValue);

            // reset calculation area
            resetCalculationArea()
        });

        labelGFL.addEventListener('change', function () {
            var typedGFL = parseInt(labelGFL.value);
            if (isNaN(typedGFL)) { labelGFL.value = Math.round(GFLSlider.noUiSlider.get()); return; }
            GFLSlider.noUiSlider.set(typedGFL);
        });

        var GFHSlider = document.getElementById('GFHSlider');
        var labelGFH = document.getElementById('labelGFH');


        noUiSlider.create(GFHSlider, {
            start: {{ $decoPrefs['gfHigh'] ?? 75 }}, // diver's saved GF High, if any (Pablo, 2026-09-18)
            connect: [true, false],
            range: {
                'min': 10,
                'max': 100
            },
            step: 1,
            

        });

        // Hide the tick mark labels
        var GFHSliderTicks = GFHSlider.querySelectorAll('.noUi-value-sub');
        GFHSliderTicks.forEach(function (GFHSlider) {
            GFHSlider.style.display = 'none';
        });

        GFHSlider.noUiSlider.on('update', function (values, handle) {
            var GFHSliderValue = parseInt(values[handle]);
            labelGFH.value = GFHSliderValue;

            // GF Low is normally kept at or below GF High (raising the
            // ascent ceiling above the first-stop conservatism makes no
            // sense) - "Remove safety guards" lets a registered user break
            // that link entirely and pick any GFL/GFH combination (Pablo,
            // 2026-09-25: "allow for GFH and GFL to be completely
            // independent").
            if (typeof dhRemoveSafetyGuards === 'undefined' || !dhRemoveSafetyGuards) {
                // Update GFL max range dynamically
                GFLSlider.noUiSlider.updateOptions({
                    range: {
                        'min': 10,
                        'max': GFHSliderValue // Set max dynamically
                    }
                });

                // Ensure GFL value stays within range
                var GFLSliderValue = parseInt(GFLSlider.noUiSlider.get());
                if (GFLSliderValue > GFHSliderValue) {
                    GFLSlider.noUiSlider.set(GFHSliderValue);
                }
            } else {
                GFLSlider.noUiSlider.updateOptions({ range: { 'min': 10, 'max': 100 } });
            }

            // reset calculation area
            resetCalculationArea()
        });

        labelGFH.addEventListener('change', function () {
            var typedGFH = parseInt(labelGFH.value);
            if (isNaN(typedGFH)) { labelGFH.value = Math.round(GFHSlider.noUiSlider.get()); return; }
            GFHSlider.noUiSlider.set(typedGFH);
        });
    </script>

    {{-- Scripts slider Asc/Des rates --}}
    <script>
        var desSlider = document.getElementById('desSlider');
        var labelDes = document.getElementById('labelDes');
        var labelDesMET = document.getElementById('labelDesMET');


        
        noUiSlider.create(desSlider, {
            start: 60 *FT2M,
            connect: [true, false],
            range: {
                'min': 10 * FT2M,
                'max': 120 * FT2M
            },
            step: 10 * FT2M,
        });
        

        // Hide the tick mark labels
        var desSliderTicks = desSlider.querySelectorAll('.noUi-value-sub');
        desSliderTicks.forEach(function (desSlider) {
            desSlider.style.display = 'none';
        });

        desSlider.noUiSlider.on('update', function (values, handle) {
            var desSliderValue = values[handle];
            if(modeImpOrMetric === "imp") {
                labelDes.value = parseInt(desSliderValue);
                labelDesMET.value = parseInt(desSliderValue * 0.3948);
            } else {
                labelDes.value = parseInt(desSliderValue * 3.281);
                labelDesMET.value = parseInt(desSliderValue);
                desSliderValue = parseInt(desSliderValue * 3.281);
            }

            // reset calculation area
            resetCalculationArea()
        });

        labelDes.addEventListener('change', function () {
            var typedDes = parseInt(labelDes.value);
            if (isNaN(typedDes)) { labelDes.value = Math.round(desSlider.noUiSlider.get()); return; }
            desSlider.noUiSlider.set(modeImpOrMetric === "imp" ? typedDes : typedDes / 3.281);
        });
        labelDesMET.addEventListener('change', function () {
            var typedDesMET = parseInt(labelDesMET.value);
            if (isNaN(typedDesMET)) { labelDesMET.value = Math.round(desSlider.noUiSlider.get()); return; }
            desSlider.noUiSlider.set(modeImpOrMetric === "imp" ? typedDesMET / 0.3948 : typedDesMET);
        });

        var ascSlider = document.getElementById('ascSlider');
        var labelAsc = document.getElementById('labelAsc');

        noUiSlider.create(ascSlider, {
            start: 30 *FT2M,
            connect: [true, false],
            range: {
                'min': 10 * FT2M,
                'max': 60 * FT2M
            },
            step: 10 * FT2M,
        });

        // Hide the tick mark labels
        var ascSliderTicks = ascSlider.querySelectorAll('.noUi-value-sub');
        ascSliderTicks.forEach(function (ascSlider) {
            ascSlider.style.display = 'none';
        });

        ascSlider.noUiSlider.on('update', function (values, handle) {
            var ascSliderValue = values[handle];
            if(modeImpOrMetric === "imp") {
                labelAsc.value = parseInt(ascSliderValue);
                labelAscMET.value = parseInt(ascSliderValue * 0.3948);
            } else {
                labelAsc.value = parseInt(ascSliderValue * 3.281);
                labelAscMET.value = parseInt(ascSliderValue);
                ascSliderValue = parseInt(ascSliderValue * 3.281);
            }

            // reset calculation area
            resetCalculationArea()
        });

        labelAsc.addEventListener('change', function () {
            var typedAsc = parseInt(labelAsc.value);
            if (isNaN(typedAsc)) { labelAsc.value = Math.round(ascSlider.noUiSlider.get()); return; }
            ascSlider.noUiSlider.set(modeImpOrMetric === "imp" ? typedAsc : typedAsc / 3.281);
        });
        labelAscMET.addEventListener('change', function () {
            var typedAscMET = parseInt(labelAscMET.value);
            if (isNaN(typedAscMET)) { labelAscMET.value = Math.round(ascSlider.noUiSlider.get()); return; }
            ascSlider.noUiSlider.set(modeImpOrMetric === "imp" ? typedAscMET / 0.3948 : typedAscMET);
        });
    </script>



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
    <script>
        var activeGasTab = null;
        var isSettingDepthFromSite = false;

        // "Search dive sites" and its result now share one button (Pablo,
        // 2026-09-19: "replace the pill Search a dive with the result...add
        // a magnifier glass...save space in the screen") - dhSelectedSiteName
        // is the one source of truth the PDF header also reads, instead of
        // scraping a separate result pill's text/hidden state.
        var dhSelectedSiteName = null;

        function dhResetSiteSearchButton() {
            dhSelectedSiteName = null;
            document.getElementById('dropdownMenuButton').classList.remove('dh-site-selected');
            document.getElementById('dropdownMenuButtonContent').innerHTML =
                '<span class="material-icons-round" aria-hidden="true" style="font-size: 15px; vertical-align: -3px;">search</span> Search dive sites';
        }

        // Built with real DOM nodes, not an innerHTML string, so a site
        // name with HTML-special characters in it can never be interpreted
        // as markup. The level icon comes from Divers Hub's own dive-level
        // set (OW/AOW/...) - those PNGs aren't drawn white, so it's forced
        // with a filter rather than needing a second, white-only export of
        // every icon (Pablo, 2026-09-16: "all white in order to keep the
        // contrast with the pill bg").
        function dhSetSiteSearchButton(siteName, levelIcon, levelName) {
            dhSelectedSiteName = siteName;
            document.getElementById('dropdownMenuButton').classList.add('dh-site-selected');
            var content = document.getElementById('dropdownMenuButtonContent');
            content.innerHTML = '';
            if (levelIcon) {
                var levelImg = document.createElement('img');
                levelImg.src = levelIcon;
                levelImg.alt = levelName || '';
                levelImg.title = levelName || '';
                levelImg.style.cssText = 'height:14px;vertical-align:-2px;margin-right:4px;filter:brightness(0) invert(1);';
                content.appendChild(levelImg);
            }
            content.appendChild(document.createTextNode(siteName));
            // The magnifier glass is what tells the diver this pill is
            // still clickable to search again, not just a static label.
            var searchIcon = document.createElement('span');
            searchIcon.className = 'material-icons-round';
            searchIcon.setAttribute('aria-hidden', 'true');
            searchIcon.style.cssText = 'font-size: 15px; vertical-align: -3px; margin-left: 6px;';
            searchIcon.textContent = 'search';
            content.appendChild(searchIcon);
        }

        function dhNextGasSlot() {
            for (var i = 1; i <= 4; i++) {
                if (document.getElementById('gasAccordionItemDeco' + i).hidden) return i;
            }
            return null;
        }

        function dhUpdateAddGasButtonVisibility() {
            document.getElementById('gasTabBtnAdd').hidden = (dhNextGasSlot() === null);
        }

        // Switching Open/Closed Circuit is a different dive plan, not a
        // toggle on top of the same one - any deco/bailout gases the diver
        // had added under the old mode shouldn't silently carry over into
        // the new one (Pablo, 2026-09-16: "reset all the form including
        // gases that he may have had there"). Slot 1 is re-added right after
        // this by showOpenCircuit/showClosedCircuit themselves (OC: hidden
        // again; CC: forced back on as the mandatory Bailout).
        function dhResetGasSlots() {
            for (var i = 1; i <= 4; i++) {
                document.getElementById('gasAccordionItemDeco' + i).hidden = true;
                window['decoGas' + i + 'O2Slider'].noUiSlider.reset();
                window['decoGas' + i + 'HeSlider'].noUiSlider.reset();
                window['decoGas' + i + 'SwitchSlider'].noUiSlider.reset();
            }
            // Every gas card starts collapsed, including Bottom gas/Diluent
            // (Pablo, 2026-09-16: "by default...show all the gases cards
            // collapsed").
            dhShowGasTab(null);
            dhUpdateAddGasButtonVisibility();
        }

        function dhShowGasTab(tab) {
            activeGasTab = tab;
            document.querySelectorAll('#gas-accordion .dh-gas-accordion-head[data-gas-tab]').forEach(function (btn) {
                btn.classList.toggle('is-active', btn.getAttribute('data-gas-tab') === tab);
            });
            document.getElementById('gasPanelBottom').hidden = (tab !== 'bottom');
            for (var i = 1; i <= 4; i++) {
                document.getElementById('deco' + i).hidden = (tab !== ('deco' + i));
            }

            // Chart.js measures its canvas at creation time - a chart created
            // while its tab panel was still hidden gets a 0x0 layout and never
            // fixes itself on its own. Resizing right after the panel becomes
            // visible (now that the container has a real size) is what makes
            // the tank-content bar chart actually draw (Pablo, 2026-09-16:
            // "the bottom gas graph is showing fine, but all others are not").
            var chartsByTab = { deco1: decoGas1stackedBarChart, deco2: decoGas2stackedBarChart, deco3: decoGas3stackedBarChart, deco4: decoGas4stackedBarChart };
            var chart = chartsByTab[tab];
            if (chart) {
                requestAnimationFrame(function () {
                    chart.resize();
                    chart.update();
                });
            }
        }

        // Updates the live O2/He mix shown in a gas's tab on every slider
        // tick (including mid-drag). The "Deco N"/"Bailout" prefix is NOT
        // recomputed here - it's read from data-gas-prefix, last written by
        // dhReorderGasAccordion() - so dragging one slider can't make its
        // own or any other card's prefix flicker between its raw slot
        // number and its sorted display position (Pablo, 2026-09-17: "wait
        // until the user releases the slider...avoid too much movement").
        // Falls back to the slot number only before any reorder has ever
        // run for this slot (e.g. the immediate 'update' fire at page load).
        function dhUpdateGasTabLabel(n, o2, he) {
            var btn = document.getElementById('gasTabBtnDeco' + n);
            var title = btn.querySelector('.dh-gas-accordion-head-title');
            var prefix = btn.dataset.gasPrefix || ((n === 1 && modeOCOrCC === 'CC') ? 'Bailout' : ('Deco ' + n));
            var mix = he > 0 ? (o2 + '% O₂ / ' + he + '% He') : (o2 + '% O₂');
            title.textContent = prefix + ' (' + mix + ')';
        }

        // Orders the visible deco/bailout cards by O2 content, low to high
        // (Pablo, 2026-09-17: "order and name the deco gases in order from
        // more less O2 content to more O2 content"). Bottom gas/Diluent is
        // never touched - its item never moves. In CC, slot 1 (Bailout) is
        // mandatory and pinned right after Diluent - it's never part of the
        // ascending-O2 group ("Diluent shows first in CC and then bailout.
        // Then all others with the sort"). Everything else visible gets
        // physically reordered in the DOM and relabeled "Deco 1/2/3..." by
        // its DISPLAY position, not its internal slot number (1-4), which
        // stays fixed so the sliders/inputs it's wired to don't change.
        //
        // Only called once a slider value is actually committed (its 'set'
        // event - see the listeners registered right after the deco gas 4
        // script block), never on every drag tick, so cards don't jump
        // around while the diver is still moving a handle.
        function dhReorderGasAccordion() {
            var container = document.getElementById('gas-accordion');
            var addBtn = document.getElementById('gasTabBtnAdd');
            if (!container || !addBtn) return;

            var pinnedBailout = (modeOCOrCC === 'CC') ? 1 : null;
            if (pinnedBailout) {
                var bO2 = parseInt(document.getElementById('labelDecoGas1O2').value, 10);
                var bHe = parseInt(document.getElementById('labelDecoGas1He').value, 10);
                var bBtn = document.getElementById('gasTabBtnDeco1');
                var bMix = bHe > 0 ? (bO2 + '% O₂ / ' + bHe + '% He') : (bO2 + '% O₂');
                bBtn.dataset.gasPrefix = 'Bailout';
                bBtn.querySelector('.dh-gas-accordion-head-title').textContent = 'Bailout (' + bMix + ')';
            }

            var sortable = [];
            for (var i = 1; i <= 4; i++) {
                if (i === pinnedBailout) continue;
                var item = document.getElementById('gasAccordionItemDeco' + i);
                if (!item.hidden) {
                    sortable.push({ n: i, o2: parseInt(document.getElementById('labelDecoGas' + i + 'O2').value, 10), item: item });
                }
            }
            sortable.sort(function (a, b) { return a.o2 - b.o2; });

            var bottomItem = document.getElementById('gasPanelBottom').closest('.dh-gas-accordion-item');
            var afterNode = pinnedBailout ? document.getElementById('gasAccordionItemDeco' + pinnedBailout) : bottomItem;
            sortable.forEach(function (entry) {
                afterNode.insertAdjacentElement('afterend', entry.item);
                afterNode = entry.item;
            });
            container.appendChild(addBtn); // "+ Add gas" always stays last

            sortable.forEach(function (entry, idx) {
                var o2 = parseInt(document.getElementById('labelDecoGas' + entry.n + 'O2').value, 10);
                var he = parseInt(document.getElementById('labelDecoGas' + entry.n + 'He').value, 10);
                var mix = he > 0 ? (o2 + '% O₂ / ' + he + '% He') : (o2 + '% O₂');
                var entryBtn = document.getElementById('gasTabBtnDeco' + entry.n);
                var prefix = 'Deco ' + (idx + 1);
                entryBtn.dataset.gasPrefix = prefix;
                entryBtn.querySelector('.dh-gas-accordion-head-title').textContent = prefix + ' (' + mix + ')';
            });
        }

        function dhUpdateBottomGasTabLabel() {
            var btn = document.getElementById('gasTabBtnBottom');
            var title = btn.querySelector('.dh-gas-accordion-head-title');
            var o2 = parseInt(labelBottomGasO2.value);
            var he = parseInt(labelBottomGasHe.value);
            var prefix = modeOCOrCC === 'CC' ? 'Diluent' : 'Bottom gas';
            var mix = he > 0 ? (o2 + '% O₂ / ' + he + '% He') : (o2 + '% O₂');
            title.textContent = prefix + ' (' + mix + ')';
        }

        // Nitrox mixes (He = 0) show one plain, fully-rounded O2 pill instead
        // of a split pill with an empty/zero He half (Pablo, 2026-09-16).
        function dhUpdateSplitPillSolo(hePillId) {
            var hePill = document.getElementById(hePillId);
            var solo = parseInt(hePill.textContent, 10) === 0;
            hePill.hidden = solo;
            hePill.parentElement.classList.toggle('is-solo', solo);
        }

        document.getElementById('gasTabBtnAdd').addEventListener('click', function () {
            var n = dhNextGasSlot();
            if (n === null) return;
            window['showDecoGas' + n]();
        });

        // Standard gas presets for each deco/bailout/diluent card (Pablo,
        // 2026-09-16, refined 2026-09-17). Picking one drives both sliders
        // at once; the reverse direction - highlighting a preset when the
        // current O2/He happens to match it, and clearing all of them the
        // moment it doesn't - runs from each gas's own O2/He slider update
        // handler via dhUpdateGasPreset.
        //
        // Slot 1 has TWO preset rows sharing one entry here - #gasPreset1
        // (Deco 1, shown in OC) and #gasPresetBailout (shown in CC) both
        // drive decoGas1's own sliders, since Bailout IS slot 1's gas, just
        // a different menu for a different context (Pablo, 2026-09-17: "for
        // decos: 32%, 50%, 80%...for bailout: Air, 32%, 21/35, 18/45").
        // "bottom" is the Diluent-only row on the Bottom gas/Diluent card,
        // which uses bottomGasO2Slider/HeSlider instead of the decoGasN
        // naming the other slots use.
        var dhGasPresetSlots = {
            bottom: { o2Id: 'labelBottomGasO2', heId: 'labelBottomGasHe', o2SliderVar: 'bottomGasO2Slider', heSliderVar: 'bottomGasHeSlider', rowIds: ['gasPresetDiluent'] },
            1: { o2Id: 'labelDecoGas1O2', heId: 'labelDecoGas1He', o2SliderVar: 'decoGas1O2Slider', heSliderVar: 'decoGas1HeSlider', rowIds: ['gasPreset1', 'gasPresetBailout'] },
            2: { o2Id: 'labelDecoGas2O2', heId: 'labelDecoGas2He', o2SliderVar: 'decoGas2O2Slider', heSliderVar: 'decoGas2HeSlider', rowIds: ['gasPreset2'] },
            3: { o2Id: 'labelDecoGas3O2', heId: 'labelDecoGas3He', o2SliderVar: 'decoGas3O2Slider', heSliderVar: 'decoGas3HeSlider', rowIds: ['gasPreset3'] },
            4: { o2Id: 'labelDecoGas4O2', heId: 'labelDecoGas4He', o2SliderVar: 'decoGas4O2Slider', heSliderVar: 'decoGas4HeSlider', rowIds: ['gasPreset4'] },
        };

        document.querySelectorAll('.dh-gas-preset-row').forEach(function (row) {
            var cfg = dhGasPresetSlots[row.dataset.slot];
            if (!cfg) return;
            row.querySelectorAll('.dh-channel-chip').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var o2 = parseInt(btn.getAttribute('data-o2'), 10);
                    var he = parseInt(btn.getAttribute('data-he'), 10);
                    window[cfg.o2SliderVar].noUiSlider.set(o2);
                    window[cfg.heSliderVar].noUiSlider.set(he);
                });
            });
        });

        function dhUpdateGasPreset(slot) {
            var cfg = dhGasPresetSlots[slot];
            if (!cfg) return;
            var o2 = parseInt(document.getElementById(cfg.o2Id).value, 10);
            var he = parseInt(document.getElementById(cfg.heId).value, 10);
            cfg.rowIds.forEach(function (rowId) {
                var row = document.getElementById(rowId);
                if (!row) return;
                row.querySelectorAll('.dh-channel-chip').forEach(function (btn) {
                    var matches = parseInt(btn.getAttribute('data-o2'), 10) === o2 && parseInt(btn.getAttribute('data-he'), 10) === he;
                    btn.classList.toggle('is-active', matches);
                });
            });
        }

        // "My Gases" - up to 10 custom gas mixes a registered diver can
        // save from any gas card and reuse on any other (Pablo, 2026-09-18:
        // "add in all gas cards another pill at the top...My Gases" /
        // "add a small save icon where a modal will show up and confirm").
        // savedGasesData starts from what the controller already loaded for
        // this diver; every save/delete keeps it (and the modal list) in
        // sync without a full page reload.
        var savedGasesData = @json($savedGases ?? []);
        var dhMyGasesTargetSlot = null;
        var dhSaveGasTargetSlot = null;
        var modalMyGasesEl = document.getElementById('modalMyGases');
        var modalSaveGasEl = document.getElementById('modalSaveGas');
        var modalMyGases = modalMyGasesEl ? new bootstrap.Modal(modalMyGasesEl) : null;
        var modalSaveGas = modalSaveGasEl ? new bootstrap.Modal(modalSaveGasEl) : null;

        function dhFormatGasMix(o2, he) {
            return he > 0 ? (o2 + '% O₂ / ' + he + '% He') : (o2 + '% O₂');
        }

        // Flashes an icon button green/red for ~1.5s after a save attempt,
        // then puts its original glyph back - a lightweight inline
        // confirmation instead of pulling in a toast library for this one
        // page (Pablo: no toast helper already exists here to reuse).
        function dhFlashIconButton(btn, ok) {
            var icon = btn.querySelector('.material-icons-round');
            var originalGlyph = icon.textContent;
            btn.classList.add(ok ? 'is-success' : 'is-error');
            icon.textContent = ok ? 'check' : 'error_outline';
            setTimeout(function () {
                btn.classList.remove('is-success', 'is-error');
                icon.textContent = originalGlyph;
            }, 1500);
        }

        function dhRenderMyGasesList() {
            var list = document.getElementById('modalMyGasesList');
            var empty = document.getElementById('modalMyGasesEmpty');
            if (!list) return;
            list.innerHTML = '';
            empty.hidden = savedGasesData.length > 0;
            savedGasesData.forEach(function (gas) {
                var row = document.createElement('div');
                row.className = 'dh-mygases-row';

                var pick = document.createElement('button');
                pick.type = 'button';
                pick.className = 'dh-mygases-pick';
                // Same green O2/blue He split pill as every gas card's own
                // result pills, fully rounded when there's no He (Pablo,
                // 2026-09-18: "use the same pill convention...green for O2,
                // blue for He...if the gas only has O2, use full rounded
                // pill") - not the plain blue compact pill this used before.
                var isSolo = gas.he === 0;
                var splitPill = document.createElement('span');
                splitPill.className = 'dh-gas-split-pill' + (isSolo ? ' is-solo' : '');
                var o2Pill = document.createElement('label');
                o2Pill.className = 'dh-gas-result-pill is-o2';
                o2Pill.textContent = gas.o2;
                splitPill.appendChild(o2Pill);
                if (!isSolo) {
                    var hePill = document.createElement('label');
                    hePill.className = 'dh-gas-result-pill is-he';
                    hePill.textContent = gas.he;
                    splitPill.appendChild(hePill);
                }
                pick.appendChild(splitPill);
                pick.addEventListener('click', function () {
                    var cfg = dhGasPresetSlots[dhMyGasesTargetSlot];
                    if (cfg) {
                        window[cfg.o2SliderVar].noUiSlider.set(gas.o2);
                        window[cfg.heSliderVar].noUiSlider.set(gas.he);
                    }
                    modalMyGases.hide();
                });

                var del = document.createElement('button');
                del.type = 'button';
                del.className = 'dh-mygases-delete';
                del.title = 'Delete this saved gas';
                var delIcon = document.createElement('span');
                delIcon.className = 'material-icons-round';
                delIcon.setAttribute('aria-hidden', 'true');
                delIcon.textContent = 'delete_outline';
                del.appendChild(delIcon);
                del.addEventListener('click', function () {
                    if (!confirm('Delete this saved gas (' + dhFormatGasMix(gas.o2, gas.he) + ')?')) return;
                    $.ajax({
                        url: '{{ route("DecoPlanner.deleteGas", ["id" => "__ID__"]) }}'.replace('__ID__', gas.id),
                        method: 'DELETE',
                        success: function () {
                            savedGasesData = savedGasesData.filter(function (g) { return g.id !== gas.id; });
                            dhRenderMyGasesList();
                        },
                        error: function () {
                            alert('Could not delete that gas - please try again.');
                        },
                    });
                });

                row.appendChild(pick);
                row.appendChild(del);
                list.appendChild(row);
            });
        }
        dhRenderMyGasesList();

        document.querySelectorAll('.dh-gas-mygases-chip').forEach(function (btn) {
            btn.addEventListener('click', function () {
                dhMyGasesTargetSlot = btn.getAttribute('data-mygases-slot');
                if (modalMyGases) modalMyGases.show();
            });
        });

        document.querySelectorAll('[data-save-slot]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                dhSaveGasTargetSlot = btn.getAttribute('data-save-slot');
                var cfg = dhGasPresetSlots[dhSaveGasTargetSlot];
                if (!cfg || !modalSaveGas) return;
                var o2 = parseInt(document.getElementById(cfg.o2Id).value, 10);
                var he = parseInt(document.getElementById(cfg.heId).value, 10);
                document.getElementById('modalSaveGasMix').textContent = dhFormatGasMix(o2, he);
                var errorEl = document.getElementById('modalSaveGasError');
                errorEl.hidden = true;
                errorEl.textContent = '';
                modalSaveGas.show();
            });
        });

        document.getElementById('modalSaveGasConfirm').addEventListener('click', function () {
            var cfg = dhGasPresetSlots[dhSaveGasTargetSlot];
            if (!cfg) return;
            var o2 = parseInt(document.getElementById(cfg.o2Id).value, 10);
            var he = parseInt(document.getElementById(cfg.heId).value, 10);
            var errorEl = document.getElementById('modalSaveGasError');
            errorEl.hidden = true;

            $.ajax({
                url: '{{ route("DecoPlanner.saveGas") }}',
                method: 'POST',
                data: { o2: o2, he: he },
                success: function (gas) {
                    savedGasesData.push(gas);
                    dhRenderMyGasesList();
                    modalSaveGas.hide();
                },
                error: function (xhr) {
                    errorEl.textContent = (xhr.responseJSON && xhr.responseJSON.message) || 'Could not save that gas - please try again.';
                    errorEl.hidden = false;
                },
            });
        });

        var saveDecoPrefsPill = document.getElementById('saveDecoPrefsPill');
        if (saveDecoPrefsPill) {
            saveDecoPrefsPill.addEventListener('click', function () {
                $.ajax({
                    url: '{{ route("DecoPlanner.savePreferences") }}',
                    method: 'POST',
                    data: {
                        gfLow: parseInt(labelGFL.value, 10),
                        gfHigh: parseInt(labelGFH.value, 10),
                        setpoint: parseFloat(labelSetpoint.value),
                    },
                    success: function () { dhFlashIconButton(saveDecoPrefsPill, true); },
                    error: function (xhr) {
                        dhFlashIconButton(saveDecoPrefsPill, false);
                        if (xhr.status === 403) {
                            // Guest (the shared account) - explain why instead of
                            // leaving a bare red flash with no context.
                            alert((xhr.responseJSON && xhr.responseJSON.message) || 'Create an account to save your preferences.');
                        }
                    },
                });
            });
        }

        document.querySelectorAll('#gas-accordion .dh-gas-accordion-head[data-gas-tab]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var tab = this.getAttribute('data-gas-tab');
                // Clicking the already-open gas collapses it - it does NOT
                // fall back to opening Bottom gas/Diluent (Pablo, 2026-09-16:
                // "if I collapse bailout it opens diluent in CC" - each gas
                // needs to be independently collapsible, including down to
                // none open at all).
                dhShowGasTab(activeGasTab === tab ? null : tab);
            });
        });
    </script>

    {{-- Scripts slider Bottom gas O2 and He --}}
    <script>
        

        // In multi-level mode the single "Max Depth" field is hidden and
        // stale - the bottom gas card's PPO2/END/density warnings need the
        // deepest of the currently visible levels instead (Pablo,
        // 2026-09-25: "consider the max depth to be the deepest of all the
        // levels"). Falls back to the single-level field otherwise, or on
        // the very first noUiSlider 'update' firing during page load,
        // before the level sliders (defined later in the file) exist yet.
        function dhEffectiveMaxDepth() {
            if (typeof modeLevelSingleOrMulti !== 'undefined' && modeLevelSingleOrMulti === 'multi'
                && typeof dhLevelDepthSliders !== 'undefined' && dhLevelDepthSliders[1]) {
                var deepest = 0;
                for (var n = 1; n <= 4; n++) {
                    var panel = document.getElementById('levelPanel' + n);
                    if (panel && !panel.hidden && dhLevelDepthSliders[n]) {
                        deepest = Math.max(deepest, parseFloat(dhLevelDepthSliders[n].noUiSlider.get()));
                    }
                }
                if (deepest > 0) return deepest;
            }
            return parseInt(labelDepth.value);
        }

        // CC bailout's switch depth/PPO2/END aren't user-adjustable via a
        // slider like the OC deco gases (see containerDeco1CCInfo - no
        // slider there) - bailout always kicks in at the bottom, so the
        // switch depth is pinned to whatever depth is "in force" (single-
        // level's own field, or the deepest level in multi mode), while
        // PPO2/END are readouts of the CURRENT bailout gas at that depth
        // (Pablo, 2026-09-25: "switch depth...should show the max depth of
        // the dive", "no matter what gas I choose, the switch depth does
        // not update" - the depth wasn't tracking multi-level at all, and
        // PPO2/END were using the stale single-level `depth` global).
        function dhSyncBailoutSwitchInfo() {
            var switchEl = document.getElementById('labelBailoutSwitch');
            if (!switchEl) return;
            var d = dhEffectiveMaxDepth();
            switchEl.textContent = parseInt(d);

            var o2El = document.getElementById('labelDecoGas1O2');
            var ppo2El = document.getElementById('labelBailoutSwitchPPO2');
            if (o2El && ppo2El) {
                ppo2El.textContent = ((d / 33 + 1) * parseInt(o2El.value) / 100).toFixed(2);
            }

            var heEl = document.getElementById('labelDecoGas1He');
            var endEl = document.getElementById('labelBailoutEND');
            if (heEl && endEl) {
                var ambientPressure = (d / 33 + 1);
                var bailoutPPHe = ambientPressure * parseInt(heEl.value) / 100;
                var bailoutEND = ((ambientPressure - bailoutPPHe) - 1) * 33;
                endEl.textContent = Math.max(0, (bailoutEND / {{ $deco_unit ? 3.28084 : 1 }}).toFixed(0));
                if (endEl.textContent > 130) {
                    endEl.classList.remove("is-warn");
                    endEl.classList.add("is-danger");
                } else if (endEl.textContent > 100) {
                    endEl.classList.remove("is-danger");
                    endEl.classList.add("is-warn");
                } else {
                    endEl.classList.remove("is-warn", "is-danger");
                    endEl.classList.add("is-safe");
                }
            }
        }

        var bottomGasO2Slider = document.getElementById('bottomGasO2Slider');
        var labelBottomGasO2 = document.getElementById('labelBottomGasO2');
        var bestO2 =  Math.round((1.4 / (depth / 33 + 1) * 100));

        var bottomGasHeSlider = document.getElementById('bottomGasHeSlider');
        var labelBottomGasHe = document.getElementById('labelBottomGasHe');
        var bestHe = ((1 - ((80 / 33) +1) / (depth / 33 + 1)) * 100).toFixed(0);
        if (depth < 131)
            bestHe = 0;

        var labelBottomGasPPO2 = document.getElementById('labelBottomGasPPO2');
        var labelBottomGasEND = document.getElementById('labelBottomGasEND');
        var labelBottomGasDensity = document.getElementById('labelBottomGasDensity');

        // Drops the ideal bottom gas/diluent (same PPO2/END targets the
        // single-level depth slider already used) into the bottom gas
        // card for whatever depth is currently in force - single-level's
        // own field, or the deepest visible level in multi mode (Pablo,
        // 2026-09-25: "put in the bottom gas or diluent the ideal gas as
        // we move the depth sliders"). CC's Diluent shares these same O2/
        // He fields (just relabeled), so this covers both modes.
        function dhSyncIdealBottomGas() {
            // Advanced setting, registered users only (Pablo, 2026-09-25:
            // "Recommend best gases (on and off)...won't change the gases
            // for bottom OC, diluent and bailout CC while the depth
            // gauges are being moved").
            if (typeof dhRecommendBestGases !== 'undefined' && !dhRecommendBestGases) return;
            var d = dhEffectiveMaxDepth();
            bottomGasO2Slider.noUiSlider.set(Math.round((setPointOCOrCC / (d / 33 + 1) * 100)));
            // Below 131ft/40m the formula still nudges out a small (~15%
            // at 100ft) He suggestion for a marginally shorter END, but
            // convention is to leave trimix out entirely at recreational-
            // ish depths - matches the page-load bestHe default's own
            // clamp just below.
            var idealHe = (d < 131) ? 0 : ((1 - ((80 / 33) + 1) / (d / 33 + 1)) * 100).toFixed(0);
            bottomGasHeSlider.noUiSlider.set(idealHe);
        }


        noUiSlider.create(bottomGasO2Slider, {
            start: bestO2,
            connect: [true, false],
            range: {
                'min': 5,
                'max': 95
            },
            step: 1,
        });

        noUiSlider.create(bottomGasHeSlider, {
            start: bestHe,
            connect: [true, false],
            range: {
                'min': 0,
                'max': 95-bestO2
            },
            step: 1,
        });

        // Hide the tick mark labels
        var bottomGasO2SliderTicks = bottomGasO2Slider.querySelectorAll('.noUi-value-sub');
        bottomGasO2SliderTicks.forEach(function (bottomGasO2Slider) {
            bottomGasO2Slider.style.display = 'none';
        });

        bottomGasO2Slider.noUiSlider.on('update', function (values, handle) {
            var bottomGasO2SliderValue = values[handle];
            labelBottomGasO2.value = parseInt(bottomGasO2SliderValue);
            document.getElementById('bottomGasSplitO2').textContent = labelBottomGasO2.value;

            depth = dhEffectiveMaxDepth();

            var bottomGasPPO2 = parseFloat((depth / 33 +1) * labelBottomGasO2.value / 100);
            labelBottomGasPPO2.textContent = ((depth / 33 +1) * labelBottomGasO2.value / 100).toFixed(1);

            if (bottomGasPPO2 > 1.6 || bottomGasPPO2 < 0.16) {
                labelBottomGasPPO2.classList.remove("is-warn");
                labelBottomGasPPO2.classList.add("is-danger");
            } else if (bottomGasPPO2 > 1.41 || bottomGasPPO2 < 0.2) {
                labelBottomGasPPO2.classList.remove("is-danger");
                labelBottomGasPPO2.classList.add("is-warn");
            } else {
                labelBottomGasPPO2.classList.remove("is-warn", "is-danger");
                labelBottomGasPPO2.classList.add("is-safe");
            }
        
            updateEND(parseInt(labelBottomGasO2.value), parseInt(labelBottomGasHe.value), depth);

            updateGasDensity(parseInt(labelBottomGasO2.value), parseInt(labelBottomGasHe.value), depth, labelBottomGasDensity);

            // Update MAX on He slider
            bottomGasHeSlider.noUiSlider.updateOptions({
                range: {
                    'min': 0,    // Keep the minimum value as is
                    'max': 95-bottomGasO2SliderValue   // Update the maximum value to 120
                }
            });

            var oxygen = parseInt(labelBottomGasO2.value);
            var helium = parseInt(labelBottomGasHe.value);
            updateBottomGasChart(oxygen, helium);
            dhUpdateBottomGasTabLabel();
            dhUpdateGasPreset('bottom');

            // reset calculation area
            resetCalculationArea()

        });

        labelBottomGasO2.addEventListener('change', function () {
            var typedO2 = parseInt(labelBottomGasO2.value);
            if (isNaN(typedO2)) { labelBottomGasO2.value = Math.round(bottomGasO2Slider.noUiSlider.get()); return; }
            bottomGasO2Slider.noUiSlider.set(typedO2);
        });


        

        // Hide the tick mark labels
        var bottomGasHeSliderTicks = bottomGasHeSlider.querySelectorAll('.noUi-value-sub');
        bottomGasHeSliderTicks.forEach(function (bottomGasHeSlider) {
            bottomGasHeSlider.style.display = 'none';
        });

        bottomGasHeSlider.noUiSlider.on('update', function (values, handle) {
            var bottomGasHeSliderValue = values[handle];
            depth = dhEffectiveMaxDepth();
            labelBottomGasHe.value = parseInt(bottomGasHeSliderValue);
            document.getElementById('bottomGasSplitHe').textContent = labelBottomGasHe.value;
            dhUpdateSplitPillSolo('bottomGasSplitHe');

            var oxygen = parseInt(labelBottomGasO2.value);
            var helium = parseInt(labelBottomGasHe.value);
            updateBottomGasChart(oxygen, helium);

            updateEND(parseInt(labelBottomGasO2.value), parseInt(labelBottomGasHe.value), depth);

            updateGasDensity(parseInt(labelBottomGasO2.value), parseInt(labelBottomGasHe.value), depth, labelBottomGasDensity);
            dhUpdateBottomGasTabLabel();
            dhUpdateGasPreset('bottom');

            // reset calculation area
            resetCalculationArea()
        });

        labelBottomGasHe.addEventListener('change', function () {
            var typedHe = parseInt(labelBottomGasHe.value);
            if (isNaN(typedHe)) { labelBottomGasHe.value = Math.round(bottomGasHeSlider.noUiSlider.get()); return; }
            bottomGasHeSlider.noUiSlider.set(typedHe);
        });

    </script>

    {{-- Scripts slider setPoint --}}
    <script>
        var setpointSlider = document.getElementById('setpointSlider');
        var labelSetpoint = document.getElementById('labelSetpoint');


        noUiSlider.create(setpointSlider, {
            start: {{ $decoPrefs['setpoint'] ?? 1.3 }}, // diver's saved setpoint, if any (Pablo, 2026-09-18)
            connect: [true, false],
            range: {
                'min': 0.5,
                'max': 1.6
            },
            step: 0.05,
            

        });

        // Hide the tick mark labels
        var setpointSliderTicks = setpointSlider.querySelectorAll('.noUi-value-sub');
        setpointSliderTicks.forEach(function (setpointSlider) {
            setpointSlider.style.display = 'none';
        });

        setpointSlider.noUiSlider.on('update', function (values, handle) {
            var setpointSliderValue = values[handle];
            labelSetpoint.value = parseFloat(setpointSliderValue).toFixed(2);

            if (setpointSliderValue > 1.5) {
                labelSetpoint.classList.remove("is-safe", "is-warn");
                labelSetpoint.classList.add("is-danger");
            } else if (setpointSliderValue > 1.4) {
                labelSetpoint.classList.remove("is-safe", "is-danger");
                labelSetpoint.classList.add("is-warn");
            } else {
                labelSetpoint.classList.remove("is-warn", "is-danger");
                labelSetpoint.classList.add("is-safe");
            }

            // update dil PPO2 slider - normally capped so the diluent
            // alone (i.e. if O2 injection fails and the loop is breathing
            // pure diluent) can't exceed 1.2 ATA PPO2, a standard CCR
            // safety convention. "Remove safety guards" raises that ceiling
            // to 2.0 (Pablo, 2026-09-25: "allow users to set the diluent
            // PPO2 beyond 1.2, up to 2.0").
            var dhGuardsOff = (typeof dhRemoveSafetyGuards !== 'undefined' && dhRemoveSafetyGuards);
            var dilPpo2Cap = dhGuardsOff ? 2.0 : 1.2;
            var O2At12 = dilPpo2Cap / (depth / 33 +1) * 100;
            // O2AtSetpoint normally also caps diluent O2 at "no more than
            // needed to reach setpoint via O2 injection" - a sensible bound
            // under normal operation, but it also silently overrode the
            // 1.2->2.0 relaxation above for anyone near a typical ~1.3
            // setpoint. With guards off the diver is deliberately exploring
            // beyond normal operation, so this cap is skipped too.
            var O2AtSetpoint = (parseFloat(setpointSliderValue) - 0.1) / (depth / 33 +1) * 100;
            var O2SliderMaxValue = dhGuardsOff ? O2At12 : Math.min(O2At12, O2AtSetpoint)
            bottomGasO2Slider.noUiSlider.updateOptions({
                range: {
                    'min': 5,    // Keep the minimum value as is
                    'max': O2SliderMaxValue   // Update the maximum value to 120
                }
            });

        });

        labelSetpoint.addEventListener('change', function () {
            var typedSetpoint = parseFloat(labelSetpoint.value);
            if (isNaN(typedSetpoint)) { labelSetpoint.value = parseFloat(setpointSlider.noUiSlider.get()).toFixed(2); return; }
            setpointSlider.noUiSlider.set(typedSetpoint);
        });
    </script>



     {{-- Scripts slider time --}}
     <script>
        var bottomTimeSlider = document.getElementById('bottomTimeSlider');
        var labelBottomTime = document.getElementById('labelBottomTime');


        noUiSlider.create(bottomTimeSlider, {
            start: 25,
            connect: [true, false],
            range: {
                'min': 5,
                'max': 120
            },
            step: 1,
            

        });

        // Hide the tick mark labels
        var bottomTimeSliderTicks = bottomTimeSlider.querySelectorAll('.noUi-value-sub');
        bottomTimeSliderTicks.forEach(function (bottomTimeSlider) {
            bottomTimeSlider.style.display = 'none';
        });

        bottomTimeSlider.noUiSlider.on('update', function (values, handle) {
            var bottomTimeSliderValue = values[handle];
            labelBottomTime.value = parseInt(bottomTimeSliderValue);

            // reset calculation area
            resetCalculationArea()


        });

        labelBottomTime.addEventListener('change', function () {
            var typedBottomTime = parseInt(labelBottomTime.value);
            if (isNaN(typedBottomTime)) { labelBottomTime.value = Math.round(bottomTimeSlider.noUiSlider.get()); return; }
            bottomTimeSlider.noUiSlider.set(typedBottomTime);
        });
    </script>

    {{-- Scripts slider Deco gas 1 O2, He and switch depth --}}
    <script>
        

        var decoGas1O2Slider = document.getElementById('decoGas1O2Slider');
        var labelDecoGas1O2 = document.getElementById('labelDecoGas1O2');
        var bestO2D1 =  50;

        var decoGas1HeSlider = document.getElementById('decoGas1HeSlider');
        var labelDecoGas1He = document.getElementById('labelDecoGas1He');
        var bestHeD1 = 0;

        var decoGas1SwitchSlider = document.getElementById('decoGas1SwitchSlider');
        var labelDecoGas1Switch = document.getElementById('labelDecoGas1Switch');
        var labelDecoGas1SwitchPPO2 = document.getElementById('labelDecoGas1SwitchPPO2');


        noUiSlider.create(decoGas1O2Slider, {
            start: bestO2D1,
            connect: [true, false],
            range: {
                'min': 5,
                'max': 100 //Math.min(100, Math.floor(1.6 / (depth /33 +1) * 100))
            },
            step: 1,
        });

        noUiSlider.create(decoGas1HeSlider, {
            start: bestHeD1,
            connect: [true, false],
            range: {
                'min': 0,
                'max': 100-bestO2D1
            },
            step: 1,
        });

        
        noUiSlider.create(decoGas1SwitchSlider, {
            start: 1.6,
            connect: [true, false],
            range: {
                'min': 0.50,
                'max': 1.6
            },
            step: 0.05,
        });

        // Hide the tick mark labels
        var decoGas1O2SliderTicks = decoGas1O2Slider.querySelectorAll('.noUi-value-sub');
        decoGas1O2SliderTicks.forEach(function (decoGas1O2Slider) {
            decoGas1O2Slider.style.display = 'none';
        });

        decoGas1O2Slider.noUiSlider.on('update', function (values, handle) {
            var decoGas1O2SliderValue = values[handle];
            labelDecoGas1O2.value = parseInt(decoGas1O2SliderValue);
            document.getElementById('decoGas1SplitO2').textContent = labelDecoGas1O2.value;

            // Update MAX on He slider
            decoGas1HeSlider.noUiSlider.updateOptions({
                range: {
                    'min': 0,    // Keep the minimum value as is
                    'max': Math.max(0, 95-decoGas1O2SliderValue)   // Update the maximum value to 120
                }
            });




            var oxygen = parseInt(labelDecoGas1O2.value);
            var helium = parseInt(labelDecoGas1He.value);
            updateDecoGas1Chart(oxygen, helium);
            dhUpdateGasTabLabel(1, oxygen, helium);
            dhUpdateGasPreset(1);

            // Update MAX and Min on Switch depth slider
            decoGas1SwitchSlider.noUiSlider.updateOptions({
                range: {
                    'min': parseFloat(Math.max(0.2, (10 / 33 + 1) * oxygen / 100).toFixed(1)),    // Keep the minimum value as is
                    'max': 1.6 //Math.min(1.6, Math.floor(decoGas1O2SliderValue / 100 * (depth /33 +1) * 20) / 20)   // Update the maximum value to 120
                }
            });

            // reset calculation area
            resetCalculationArea();

            // update the CC portion on the bailout (switch depth/PPO2/END,
            // uses dhEffectiveMaxDepth() so multi-level dives track the
            // deepest level rather than the stale single-level `depth`
            // global - Pablo, 2026-09-25)
            dhSyncBailoutSwitchInfo();

        });

        labelDecoGas1O2.addEventListener('change', function () {
            var typedO2 = parseInt(labelDecoGas1O2.value);
            if (isNaN(typedO2)) { labelDecoGas1O2.value = Math.round(decoGas1O2Slider.noUiSlider.get()); return; }
            decoGas1O2Slider.noUiSlider.set(typedO2);
        });

        // Hide the tick mark labels
        var decoGas1HeSliderTicks = decoGas1HeSlider.querySelectorAll('.noUi-value-sub');
        decoGas1HeSliderTicks.forEach(function (decoGas1HeSlider) {
            bottomGasHeSlider.style.display = 'none';
        });

        decoGas1HeSlider.noUiSlider.on('update', function (values, handle) {
            var decoGas1HeSliderValue = values[handle];
            labelDecoGas1He.value = parseInt(decoGas1HeSliderValue);
            document.getElementById('decoGas1SplitHe').textContent = labelDecoGas1He.value;
            dhUpdateSplitPillSolo('decoGas1SplitHe');

            var oxygen = parseInt(labelDecoGas1O2.value);
            var helium = parseInt(labelDecoGas1He.value);
            updateDecoGas1Chart(oxygen, helium);
            dhUpdateGasTabLabel(1, oxygen, helium);
            dhUpdateGasPreset(1);

            // reset calculation area
            resetCalculationArea()

            // update the CC portion on the bailout (switch depth/PPO2/END)
            dhSyncBailoutSwitchInfo();

        });

        labelDecoGas1He.addEventListener('change', function () {
            var typedHe = parseInt(labelDecoGas1He.value);
            if (isNaN(typedHe)) { labelDecoGas1He.value = Math.round(decoGas1HeSlider.noUiSlider.get()); return; }
            decoGas1HeSlider.noUiSlider.set(typedHe);
        });

        // Hide the tick mark labels
        var decoGas1SwitchSliderTicks = decoGas1SwitchSlider.querySelectorAll('.noUi-value-sub');
        decoGas1SwitchSliderTicks.forEach(function (decoGas1SwitchSlider) {
            decoGas1SwitchSlider.style.display = 'none';
        });

        decoGas1SwitchSlider.noUiSlider.on('update', function (values, handle) {
            var decoGas1SwitchSliderValue = values[handle];
            labelDecoGas1SwitchPPO2.value = parseFloat(decoGas1SwitchSliderValue).toFixed(2);

            var O2Content = labelDecoGas1O2.value / 100;

            // adjust the unit
            if(modeImpOrMetric === "imp")
                labelDecoGas1Switch.textContent = (((decoGas1SwitchSliderValue / O2Content) - 1 ) * 33).toFixed(0);
            else
                labelDecoGas1Switch.textContent = (((decoGas1SwitchSliderValue / O2Content) - 1 ) * 33 * 0.3048).toFixed(0);



            // reset calculation area
            resetCalculationArea()
        });

        labelDecoGas1SwitchPPO2.addEventListener('change', function () {
            var typedSwitchPPO2 = parseFloat(labelDecoGas1SwitchPPO2.value);
            if (isNaN(typedSwitchPPO2)) { labelDecoGas1SwitchPPO2.value = parseFloat(decoGas1SwitchSlider.noUiSlider.get()).toFixed(2); return; }
            decoGas1SwitchSlider.noUiSlider.set(typedSwitchPPO2);
        });

        function showDecoGas1() {
            document.getElementById("gasAccordionItemDeco1").hidden = false;
            dhShowGasTab('deco1');
            dhUpdateAddGasButtonVisibility();
            dhReorderGasAccordion();
            // reset calculation area
            resetCalculationArea()
        }

        function hideDecoGas1() {
            document.getElementById("gasAccordionItemDeco1").hidden = true;
            dhShowGasTab('bottom');
            dhUpdateAddGasButtonVisibility();
            dhReorderGasAccordion();
            // reset calculation area
            resetCalculationArea()
        }
    </script>

     {{-- Slider depth --}}
     <script>
        var depthSlider = document.getElementById('depthSlider');
        var labelDepth = document.getElementById('labelDepth');
        var labelDepthMET = document.getElementById('labelDepthMET');
        var labelBailoutSwitch = document.getElementById("labelBailoutSwitch");
        

        //console.log("Current Site Max Depth:", currentSite.maxDepth);
        let startDepth = (currentSite ? currentSite.maxDepth : 100 ) * FT2M;
        //console.log("Slider Start Depth:", startDepth);

        noUiSlider.create(depthSlider, {
            start: startDepth,
            connect: [true, false],
            range: {
                'min': 10 * FT2M,
                'max': 450 * FT2M
            },
            step: 1,
            

        });

        // Hide the tick mark labels
        var depthSliderTicks = depthSlider.querySelectorAll('.noUi-value-sub');
        depthSliderTicks.forEach(function (depthSlider) {
            depthSlider.style.display = 'none';
        });

        depthSlider.noUiSlider.on('update', function (values, handle) {
            var depthSliderValue = values[handle];
            if (!isSettingDepthFromSite) {
                dhResetSiteSearchButton();
            }
            if(modeImpOrMetric == "imp") {
                labelDepth.value = parseInt(depthSliderValue);
                labelDepthMET.value = parseInt(depthSliderValue * 0.3948);
                depthSliderValue = parseInt(depthSliderValue);
            } else {
                labelDepth.value = parseInt(depthSliderValue * 3.281);
                labelDepthMET.value = parseInt(depthSliderValue);
                depthSliderValue = parseInt(depthSliderValue * 3.281);
            }

            dhSyncIdealBottomGas();
            dhSyncBailoutSwitchInfo();    // update bailout switching depth/PPO2/END

            // The range (max PPO2-driven O2% ceiling) is a depth-derived
            // slider bound, not a recommendation - always kept current.
            // The actual VALUE it jumps to (bailout O2/He) is the "Recommend
            // best gases" behavior and is skipped when that's off (Pablo,
            // 2026-09-25: "won't change the gases for...bailout CC while
            // the depth gauges are being moved").
            if(modeOCOrCC == "CC") {
                decoGas1O2Slider.noUiSlider.updateOptions({
                    range: {
                        'min': 5, //parseFloat(Math.max(5, (10 / 33 + 1) * Math.floor(decoGas1O2SliderValue / 100).toFixed(1))),    // Keep the minimum value as is
                        'max': Math.min(100, 1.6 / (depth /33 +1) * 100),
                    }
                });
                if (dhRecommendBestGases) {
                    decoGas1O2Slider.noUiSlider.set(Math.min(100, 1.4 / (depth /33 +1) * 100));
                    decoGas1HeSlider.noUiSlider.set(((1 - ((80 / 33) +1) / (depthSliderValue / 33 + 1)) * 100).toFixed(0));
                }
            } else {
                decoGas1O2Slider.noUiSlider.updateOptions({
                    range: {
                        'min': 5, //parseFloat(Math.max(5, (10 / 33 + 1) * Math.floor(decoGas1O2SliderValue / 100).toFixed(1))),    // Keep the minimum value as is
                        'max': 100, //Math.min(100, 1.6 / (depth /33 +1) * 100),
                    }
                });
                if (dhRecommendBestGases) {
                    decoGas1O2Slider.noUiSlider.set(50);
                    decoGas1HeSlider.noUiSlider.set(0);
                }
            }

            // reset calculation area
            resetCalculationArea()
        });

        function useSiteDepth() {

            depthSlider.noUiSlider.set(startDepth);
        }

        labelDepth.addEventListener('change', function () {
            var typedDepth = parseInt(labelDepth.value);
            if (isNaN(typedDepth)) { labelDepth.value = Math.round(depthSlider.noUiSlider.get()); return; }
            depthSlider.noUiSlider.set(modeImpOrMetric === "imp" ? typedDepth : typedDepth / 3.281);
        });
        labelDepthMET.addEventListener('change', function () {
            var typedDepthMET = parseInt(labelDepthMET.value);
            if (isNaN(typedDepthMET)) { labelDepthMET.value = Math.round(depthSlider.noUiSlider.get()); return; }
            depthSlider.noUiSlider.set(modeImpOrMetric === "imp" ? typedDepthMET / 0.3948 : typedDepthMET);
        });
    </script>

    {{-- Multi-level: Depth + Bottom Time sliders for each of the 4 levels.
         Same slider+input sync pattern as depthSlider/bottomTimeSlider
         above, deliberately WITHOUT depthSlider's gas-composition side
         effects (bottomGasO2/He, decoGas1 range updates) - those are
         single-level-only for now, until this is wired to
         MultiLevelDivePlanner (Pablo, 2026-09-25: front-end/layout only,
         no backend call yet). Level 1 defaults to the selected site's max
         depth exactly like the single-level slider does; levels 2-4 get
         arbitrary shallower defaults since they don't exist until added. --}}
    <script>
        var dhLevelDepthSliders = {};
        var dhLevelTimeSliders = {};
        var dhLevelDefaultDepth = { 1: (currentSite ? currentSite.maxDepth : 100), 2: 80, 3: 60, 4: 40 };

        for (var dhLevelN = 1; dhLevelN <= 4; dhLevelN++) {
            (function (n) {
                var depthEl = document.getElementById('depthSliderLevel' + n);
                var labelImp = document.getElementById('labelDepthLevel' + n);
                var labelMet = document.getElementById('labelDepthLevel' + n + 'MET');
                if (!depthEl || !labelImp || !labelMet) return;

                noUiSlider.create(depthEl, {
                    start: dhLevelDefaultDepth[n] * FT2M,
                    connect: [true, false],
                    range: { 'min': 10 * FT2M, 'max': 450 * FT2M },
                    step: 1,
                });
                depthEl.querySelectorAll('.noUi-value-sub').forEach(function (el) { el.style.display = 'none'; });

                depthEl.noUiSlider.on('update', function (values, handle) {
                    var v = values[handle];
                    if (modeImpOrMetric === "imp") {
                        labelImp.value = parseInt(v);
                        labelMet.value = parseInt(v * 0.3948);
                    } else {
                        labelImp.value = parseInt(v * 3.281);
                        labelMet.value = parseInt(v);
                    }
                    // A level's depth just changed, which can change which
                    // level is the deepest - drop in the ideal bottom gas/
                    // diluent for the new deepest depth (Pablo, 2026-09-25:
                    // "put in the bottom gas or diluent the ideal gas as we
                    // move the depth sliders"). Guarded to multi mode only -
                    // in single mode this slider isn't the one driving depth
                    // and may still be mid-setup (dhLevelDepthSliders[n] not
                    // assigned yet) when it first fires.
                    if (typeof modeLevelSingleOrMulti !== 'undefined' && modeLevelSingleOrMulti === 'multi'
                        && typeof bottomGasO2Slider !== 'undefined' && bottomGasO2Slider.noUiSlider) {
                        dhSyncIdealBottomGas();
                        // Bailout's switch depth is pinned to the deepest
                        // level too - it never tracked multi-level depth
                        // changes at all before this (Pablo, 2026-09-25).
                        if (typeof dhSyncBailoutSwitchInfo === 'function') dhSyncBailoutSwitchInfo();
                    }
                });
                labelImp.addEventListener('change', function () {
                    var typed = parseInt(labelImp.value);
                    if (isNaN(typed)) { labelImp.value = Math.round(depthEl.noUiSlider.get()); return; }
                    depthEl.noUiSlider.set(modeImpOrMetric === "imp" ? typed : typed / 3.281);
                });
                labelMet.addEventListener('change', function () {
                    var typed = parseInt(labelMet.value);
                    if (isNaN(typed)) { labelMet.value = Math.round(depthEl.noUiSlider.get()); return; }
                    depthEl.noUiSlider.set(modeImpOrMetric === "imp" ? typed / 0.3948 : typed);
                });
                dhLevelDepthSliders[n] = depthEl;

                var timeEl = document.getElementById('bottomTimeSliderLevel' + n);
                var timeLabel = document.getElementById('labelBottomTimeLevel' + n);
                if (!timeEl || !timeLabel) return;

                noUiSlider.create(timeEl, {
                    start: parseInt(timeLabel.value) || 15,
                    connect: [true, false],
                    range: { 'min': 5, 'max': 120 },
                    step: 1,
                });
                timeEl.querySelectorAll('.noUi-value-sub').forEach(function (el) { el.style.display = 'none'; });
                timeEl.noUiSlider.on('update', function (values, handle) {
                    timeLabel.value = parseInt(values[handle]);
                });
                timeLabel.addEventListener('change', function () {
                    var typed = parseInt(timeLabel.value);
                    if (isNaN(typed)) { timeLabel.value = Math.round(timeEl.noUiSlider.get()); return; }
                    timeEl.noUiSlider.set(typed);
                });
                dhLevelTimeSliders[n] = timeEl;
            })(dhLevelN);
        }
    </script>

    {{-- Scripts slider Deco gas 2 O2, He and switch depth --}}
    <script>
        

        var decoGas2O2Slider = document.getElementById('decoGas2O2Slider');
        var labelDecoGas2O2 = document.getElementById('labelDecoGas2O2');
        var bestO2D2 =  80;

        var decoGas2HeSlider = document.getElementById('decoGas2HeSlider');
        var labelDecoGas2He = document.getElementById('labelDecoGas2He');
        var bestHeD2 = 0;

        var decoGas2SwitchSlider = document.getElementById('decoGas2SwitchSlider');
        var labelDecoGas2Switch = document.getElementById('labelDecoGas2Switch');
        var labelDecoGas2SwitchPPO2 = document.getElementById('labelDecoGas2SwitchPPO2');


        noUiSlider.create(decoGas2O2Slider, {
            start: bestO2D2,
            connect: [true, false],
            range: {
                'min': 5,
                'max': 100
            },
            step: 1,
        });

        noUiSlider.create(decoGas2HeSlider, {
            start: bestHeD2,
            connect: [true, false],
            range: {
                'min': 0,
                'max': 100-bestO2D2
            },
            step: 1,
        });

        noUiSlider.create(decoGas2SwitchSlider, {
            start: 1.6,
            connect: [true, false],
            range: {
                'min': 0.5,
                'max': 1.6
            },
            step: 0.1,
        });

        // Hide the tick mark labels
        var decoGas2O2SliderTicks = decoGas2O2Slider.querySelectorAll('.noUi-value-sub');
        decoGas2O2SliderTicks.forEach(function (decoGas2O2Slider) {
            decoGas2O2Slider.style.display = 'none';
        });

        decoGas2O2Slider.noUiSlider.on('update', function (values, handle) {
            var decoGas2O2SliderValue = values[handle];
            labelDecoGas2O2.value = parseInt(decoGas2O2SliderValue);
            document.getElementById('decoGas2SplitO2').textContent = labelDecoGas2O2.value;

            // Update MAX on He slider
            decoGas2HeSlider.noUiSlider.updateOptions({
                range: {
                    'min': 0,    // Keep the minimum value as is
                    'max': Math.max(0, 95-decoGas2O2SliderValue)   // Update the maximum value to 120
                }
            });



            var oxygen = parseInt(labelDecoGas2O2.value);
            var helium = parseInt(labelDecoGas2He.value);
            updateDecoGas2Chart(oxygen, helium);
            dhUpdateGasTabLabel(2, oxygen, helium);
            dhUpdateGasPreset(2);

            // Update MAX and Min on Switch depth slider
            decoGas2SwitchSlider.noUiSlider.updateOptions({
                range: {
                    'min': parseFloat(Math.max(0.2, (10 / 33 + 1) * oxygen / 100).toFixed(1)),    // Keep the minimum value as is
                    'max': 1.6   // Update the maximum value to 120
                }
            });

            // reset calculation area
            resetCalculationArea()
        });

        labelDecoGas2O2.addEventListener('change', function () {
            var typedO2 = parseInt(labelDecoGas2O2.value);
            if (isNaN(typedO2)) { labelDecoGas2O2.value = Math.round(decoGas2O2Slider.noUiSlider.get()); return; }
            decoGas2O2Slider.noUiSlider.set(typedO2);
        });

        // Hide the tick mark labels
        var decoGas2HeSliderTicks = decoGas2HeSlider.querySelectorAll('.noUi-value-sub');
        decoGas2HeSliderTicks.forEach(function (decoGas2HeSlider) {
            bottomGasHe2Slider.style.display = 'none';
        });

        decoGas2HeSlider.noUiSlider.on('update', function (values, handle) {
            var decoGas2HeSliderValue = values[handle];
            labelDecoGas2He.value = parseInt(decoGas2HeSliderValue);
            document.getElementById('decoGas2SplitHe').textContent = labelDecoGas2He.value;
            dhUpdateSplitPillSolo('decoGas2SplitHe');

            var oxygen = parseInt(labelDecoGas2O2.value);
            var helium = parseInt(labelDecoGas2He.value);
            updateDecoGas2Chart(oxygen, helium);
            dhUpdateGasTabLabel(2, oxygen, helium);
            dhUpdateGasPreset(2);

            // reset calculation area
            resetCalculationArea()
        });

        labelDecoGas2He.addEventListener('change', function () {
            var typedHe = parseInt(labelDecoGas2He.value);
            if (isNaN(typedHe)) { labelDecoGas2He.value = Math.round(decoGas2HeSlider.noUiSlider.get()); return; }
            decoGas2HeSlider.noUiSlider.set(typedHe);
        });

        // Hide the tick mark labels
        var decoGas2SwitchSliderTicks = decoGas2SwitchSlider.querySelectorAll('.noUi-value-sub');
        decoGas2SwitchSliderTicks.forEach(function (decoGas2SwitchSlider) {
            decoGas2SwitchSlider.style.display = 'none';
        });

        decoGas2SwitchSlider.noUiSlider.on('update', function (values, handle) {
            var decoGas2SwitchSliderValue = values[handle];
            labelDecoGas2SwitchPPO2.value = parseFloat(decoGas2SwitchSliderValue).toFixed(1);

            var O2Content = labelDecoGas2O2.value / 100;



            // adjust the unit
            if(modeImpOrMetric === "imp")
                labelDecoGas2Switch.textContent = (((decoGas2SwitchSliderValue / O2Content) - 1 ) * 33).toFixed(0);
            else
                labelDecoGas2Switch.textContent = (((decoGas2SwitchSliderValue / O2Content) - 1 ) * 33 * 0.3048).toFixed(0);

            // reset calculation area
            resetCalculationArea()
        });

        labelDecoGas2SwitchPPO2.addEventListener('change', function () {
            var typedSwitchPPO2 = parseFloat(labelDecoGas2SwitchPPO2.value);
            if (isNaN(typedSwitchPPO2)) { labelDecoGas2SwitchPPO2.value = parseFloat(decoGas2SwitchSlider.noUiSlider.get()).toFixed(1); return; }
            decoGas2SwitchSlider.noUiSlider.set(typedSwitchPPO2);
        });

        function showDecoGas2() {
            document.getElementById("gasAccordionItemDeco2").hidden = false;
            dhShowGasTab('deco2');
            dhUpdateAddGasButtonVisibility();
            dhReorderGasAccordion();
            // reset calculation area
            resetCalculationArea()
        }

        function hideDecoGas2() {
            document.getElementById("gasAccordionItemDeco2").hidden = true;
            dhShowGasTab('bottom');
            dhUpdateAddGasButtonVisibility();
            dhReorderGasAccordion();
            // reset calculation area
            resetCalculationArea()
        }
    </script>

    {{-- Scripts slider Deco gas 3 O2, He and switch depth --}}
    <script>
        

        var decoGas3O2Slider = document.getElementById('decoGas3O2Slider');
        var labelDecoGas3O2 = document.getElementById('labelDecoGas3O2');
        var bestO2D3 =  21;

        var decoGas3HeSlider = document.getElementById('decoGas3HeSlider');
        var labelDecoGas3He = document.getElementById('labelDecoGas3He');
        var bestHeD3 = 35;

        var decoGas3SwitchSlider = document.getElementById('decoGas3SwitchSlider');
        var labelDecoGas3Switch = document.getElementById('labelDecoGas3Switch');
        var labelDecoGas3SwitchPPO2 = document.getElementById('labelDecoGas3SwitchPPO2');


        noUiSlider.create(decoGas3O2Slider, {
            start: bestO2D3,
            connect: [true, false],
            range: {
                'min': 5,
                'max': 100
            },
            step: 1,
        });

        noUiSlider.create(decoGas3HeSlider, {
            start: bestHeD3,
            connect: [true, false],
            range: {
                'min': 0,
                'max': 100-bestO2D3
            },
            step: 1,
        });

        noUiSlider.create(decoGas3SwitchSlider, {
            start: 1.4,
            connect: [true, false],
            range: {
                'min': 0.5,
                'max': 1.6
            },
            step: 0.1,
        });

        // Hide the tick mark labels
        var decoGas3O2SliderTicks = decoGas3O2Slider.querySelectorAll('.noUi-value-sub');
        decoGas3O2SliderTicks.forEach(function (decoGas3O2Slider) {
            decoGas3O2Slider.style.display = 'none';
        });

        decoGas3O2Slider.noUiSlider.on('update', function (values, handle) {
            var decoGas3O2SliderValue = values[handle];
            labelDecoGas3O2.value = parseInt(decoGas3O2SliderValue);
            document.getElementById('decoGas3SplitO2').textContent = labelDecoGas3O2.value;

            // Update MAX on He slider
            decoGas3HeSlider.noUiSlider.updateOptions({
                range: {
                    'min': 0,    // Keep the minimum value as is
                    'max': Math.max(0, 95-decoGas3O2SliderValue)   // Update the maximum value to 120
                }
            });



            var oxygen = parseInt(labelDecoGas3O2.value);
            var helium = parseInt(labelDecoGas3He.value);
            updateDecoGas3Chart(oxygen, helium);
            dhUpdateGasTabLabel(3, oxygen, helium);
            dhUpdateGasPreset(3);

            // Update MAX and Min on Switch depth slider
            decoGas3SwitchSlider.noUiSlider.updateOptions({
                range: {
                    'min': parseFloat(Math.max(0.2, (10 / 33 + 1) * oxygen / 100).toFixed(1)),    // Keep the minimum value as is
                    'max': 1.6   // Update the maximum value to 120
                }
            });

            // reset calculation area
            resetCalculationArea()
        });

        labelDecoGas3O2.addEventListener('change', function () {
            var typedO2 = parseInt(labelDecoGas3O2.value);
            if (isNaN(typedO2)) { labelDecoGas3O2.value = Math.round(decoGas3O2Slider.noUiSlider.get()); return; }
            decoGas3O2Slider.noUiSlider.set(typedO2);
        });

        // Hide the tick mark labels
        var decoGas3HeSliderTicks = decoGas3HeSlider.querySelectorAll('.noUi-value-sub');
        decoGas3HeSliderTicks.forEach(function (decoGas3HeSlider) {
            bottomGasHe3Slider.style.display = 'none';
        });

        decoGas3HeSlider.noUiSlider.on('update', function (values, handle) {
            var decoGas3HeSliderValue = values[handle];
            labelDecoGas3He.value = parseInt(decoGas3HeSliderValue);
            document.getElementById('decoGas3SplitHe').textContent = labelDecoGas3He.value;
            dhUpdateSplitPillSolo('decoGas3SplitHe');

            var oxygen = parseInt(labelDecoGas3O2.value);
            var helium = parseInt(labelDecoGas3He.value);
            updateDecoGas3Chart(oxygen, helium);
            dhUpdateGasTabLabel(3, oxygen, helium);
            dhUpdateGasPreset(3);

            // reset calculation area
            resetCalculationArea()
        });

        labelDecoGas3He.addEventListener('change', function () {
            var typedHe = parseInt(labelDecoGas3He.value);
            if (isNaN(typedHe)) { labelDecoGas3He.value = Math.round(decoGas3HeSlider.noUiSlider.get()); return; }
            decoGas3HeSlider.noUiSlider.set(typedHe);
        });

        // Hide the tick mark labels
        var decoGas3SwitchSliderTicks = decoGas3SwitchSlider.querySelectorAll('.noUi-value-sub');
        decoGas3SwitchSliderTicks.forEach(function (decoGas3SwitchSlider) {
            decoGas3SwitchSlider.style.display = 'none';
        });

        decoGas3SwitchSlider.noUiSlider.on('update', function (values, handle) {
            var decoGas3SwitchSliderValue = values[handle];
            labelDecoGas3SwitchPPO2.value = parseFloat(decoGas3SwitchSliderValue).toFixed(1);

            var O2Content = labelDecoGas3O2.value / 100;


            // adjust the unit
            if(modeImpOrMetric === "imp")
                labelDecoGas3Switch.textContent = (((decoGas3SwitchSliderValue / O2Content) - 1 ) * 33).toFixed(0);
            else
                labelDecoGas3Switch.textContent = (((decoGas3SwitchSliderValue / O2Content) - 1 ) * 33 * 0.3048).toFixed(0);

            // reset calculation area
            resetCalculationArea()
        });

        labelDecoGas3SwitchPPO2.addEventListener('change', function () {
            var typedSwitchPPO2 = parseFloat(labelDecoGas3SwitchPPO2.value);
            if (isNaN(typedSwitchPPO2)) { labelDecoGas3SwitchPPO2.value = parseFloat(decoGas3SwitchSlider.noUiSlider.get()).toFixed(1); return; }
            decoGas3SwitchSlider.noUiSlider.set(typedSwitchPPO2);
        });
        
        function showDecoGas3() {
            document.getElementById("gasAccordionItemDeco3").hidden = false;
            dhShowGasTab('deco3');
            dhUpdateAddGasButtonVisibility();
            dhReorderGasAccordion();
            // reset calculation area
            resetCalculationArea()
        }

        function hideDecoGas3() {
            document.getElementById("gasAccordionItemDeco3").hidden = true;
            dhShowGasTab('bottom');
            dhUpdateAddGasButtonVisibility();
            dhReorderGasAccordion();
            // reset calculation area
            resetCalculationArea()
        }
    </script>

    {{-- Scripts slider Deco gas 4 O2, He and switch depth --}}
    <script>
        

        var decoGas4O2Slider = document.getElementById('decoGas4O2Slider');
        var labelDecoGas4O2 = document.getElementById('labelDecoGas4O2');
        var bestO2D4 =  18;

        var decoGas4HeSlider = document.getElementById('decoGas4HeSlider');
        var labelDecoGas4He = document.getElementById('labelDecoGas4He');
        var bestHeD4 = 45;

        var decoGas4SwitchSlider = document.getElementById('decoGas4SwitchSlider');
        var labelDecoGas4Switch = document.getElementById('labelDecoGas4Switch');
        var labelDecoGas4SwitchPPO2 = document.getElementById('labelDecoGas4SwitchPPO2');


        noUiSlider.create(decoGas4O2Slider, {
            start: bestO2D4,
            connect: [true, false],
            range: {
                'min': 5,
                'max': 100
            },
            step: 1,
        });

        noUiSlider.create(decoGas4HeSlider, {
            start: bestHeD4,
            connect: [true, false],
            range: {
                'min': 0,
                'max': 100-bestO2D4
            },
            step: 1,
        });

        noUiSlider.create(decoGas4SwitchSlider, {
            start: 1.4,
            connect: [true, false],
            range: {
                'min': 0.5,
                'max': 1.6
            },
            step: 0.1,
        });

        // Hide the tick mark labels
        var decoGas4O2SliderTicks = decoGas4O2Slider.querySelectorAll('.noUi-value-sub');
        decoGas4O2SliderTicks.forEach(function (decoGas4O2Slider) {
            decoGas4O2Slider.style.display = 'none';
        });

        decoGas4O2Slider.noUiSlider.on('update', function (values, handle) {
            var decoGas4O2SliderValue = values[handle];
            labelDecoGas4O2.value = parseInt(decoGas4O2SliderValue);
            document.getElementById('decoGas4SplitO2').textContent = labelDecoGas4O2.value;

            // Update MAX on He slider
            decoGas4HeSlider.noUiSlider.updateOptions({
                range: {
                    'min': 0,    // Keep the minimum value as is
                    'max': Math.max(0, 95-decoGas4O2SliderValue)   // Update the maximum value to 120
                }
            });



            var oxygen = parseInt(labelDecoGas4O2.value);
            var helium = parseInt(labelDecoGas4He.value);
            updateDecoGas4Chart(oxygen, helium);
            dhUpdateGasTabLabel(4, oxygen, helium);
            dhUpdateGasPreset(4);

            // Update MAX and Min on Switch depth slider
            decoGas4SwitchSlider.noUiSlider.updateOptions({
                range: {
                    'min': parseFloat(Math.max(0.2, (10 / 33 + 1) * oxygen / 100).toFixed(1)),    // Keep the minimum value as is
                    'max': 1.6   // Update the maximum value to 120
                }
            });

            // reset calculation area
            resetCalculationArea()
        });

        labelDecoGas4O2.addEventListener('change', function () {
            var typedO2 = parseInt(labelDecoGas4O2.value);
            if (isNaN(typedO2)) { labelDecoGas4O2.value = Math.round(decoGas4O2Slider.noUiSlider.get()); return; }
            decoGas4O2Slider.noUiSlider.set(typedO2);
        });

        // Hide the tick mark labels
        var decoGas4HeSliderTicks = decoGas4HeSlider.querySelectorAll('.noUi-value-sub');
        decoGas4HeSliderTicks.forEach(function (decoGas4HeSlider) {
            bottomGasHe4Slider.style.display = 'none';
        });

        decoGas4HeSlider.noUiSlider.on('update', function (values, handle) {
            var decoGas4HeSliderValue = values[handle];
            labelDecoGas4He.value = parseInt(decoGas4HeSliderValue);
            document.getElementById('decoGas4SplitHe').textContent = labelDecoGas4He.value;
            dhUpdateSplitPillSolo('decoGas4SplitHe');

            var oxygen = parseInt(labelDecoGas4O2.value);
            var helium = parseInt(labelDecoGas4He.value);
            updateDecoGas4Chart(oxygen, helium);
            dhUpdateGasTabLabel(4, oxygen, helium);
            dhUpdateGasPreset(4);

            // reset calculation area
            resetCalculationArea()
        });

        labelDecoGas4He.addEventListener('change', function () {
            var typedHe = parseInt(labelDecoGas4He.value);
            if (isNaN(typedHe)) { labelDecoGas4He.value = Math.round(decoGas4HeSlider.noUiSlider.get()); return; }
            decoGas4HeSlider.noUiSlider.set(typedHe);
        });

        // Hide the tick mark labels
        var decoGas4SwitchSliderTicks = decoGas4SwitchSlider.querySelectorAll('.noUi-value-sub');
        decoGas4SwitchSliderTicks.forEach(function (decoGas4SwitchSlider) {
            decoGas4SwitchSlider.style.display = 'none';
        });

        decoGas4SwitchSlider.noUiSlider.on('update', function (values, handle) {
            var decoGas4SwitchSliderValue = values[handle];
            labelDecoGas4SwitchPPO2.value = parseFloat(decoGas4SwitchSliderValue).toFixed(1);

            var O2Content = labelDecoGas4O2.value / 100;

            // adjust the unit
            if(modeImpOrMetric === "imp")
                labelDecoGas4Switch.textContent = (((decoGas4SwitchSliderValue / O2Content) - 1 ) * 33).toFixed(0);
            else
                labelDecoGas4Switch.textContent = (((decoGas4SwitchSliderValue / O2Content) - 1 ) * 33 * 0.3048).toFixed(0);

            // reset calculation area
            resetCalculationArea()
        });

        labelDecoGas4SwitchPPO2.addEventListener('change', function () {
            var typedSwitchPPO2 = parseFloat(labelDecoGas4SwitchPPO2.value);
            if (isNaN(typedSwitchPPO2)) { labelDecoGas4SwitchPPO2.value = parseFloat(decoGas4SwitchSlider.noUiSlider.get()).toFixed(1); return; }
            decoGas4SwitchSlider.noUiSlider.set(typedSwitchPPO2);
        });
        
        function showDecoGas4() {
            document.getElementById("gasAccordionItemDeco4").hidden = false;
            dhShowGasTab('deco4');
            dhUpdateAddGasButtonVisibility();
            dhReorderGasAccordion();
            // reset calculation area
            resetCalculationArea()
        }

        function hideDecoGas4() {
            document.getElementById("gasAccordionItemDeco4").hidden = true;
            dhShowGasTab('bottom');
            dhUpdateAddGasButtonVisibility();
            dhReorderGasAccordion();
            // reset calculation area
            resetCalculationArea()
        }
    </script>

    {{-- Re-sort the gas accordion only once a value is actually committed,
         not on every tick while a slider is being dragged (Pablo,
         2026-09-17: "wait until the user releases the slider...avoid too
         much movement while the user is playing with the O2 handle"). Per
         nouislider.js: dragging only fires 'update'/'slide' on every frame;
         'set' fires exactly once - on drag release, on a keyboard step, and
         on a programmatic .set() (typed input, preset pill, mode switch) -
         so it's the right event to hang the reorder on instead of 'update'. --}}
    <script>
        [1, 2, 3, 4].forEach(function (n) {
            window['decoGas' + n + 'O2Slider'].noUiSlider.on('set', dhReorderGasAccordion);
            window['decoGas' + n + 'HeSlider'].noUiSlider.on('set', dhReorderGasAccordion);
        });
    </script>

    {{-- Script to show profile chart --}}
    <script>
        let formattedData = [];
        let formattedData1 = [];
        let formattedData2 = [];
        let formattedData3 = [];
        let formattedData4 = [];
        let formattedData5 = [];
        let formattedData6 = [];
        let formattedData7 = [];
        // Convert absolute pressure to depth in feet
        function absPressureToDepth(abs_p) {
            if (modeImpOrMetric == "imp")
                return Math.round((abs_p - 1) * 33);
            else
                return Math.round((abs_p - 1) * 10);
        }

        function absPressureToDepthDeco(abs_p) {
            if (modeImpOrMetric == "imp")
                return Math.round(((abs_p - 1) * 33) / 10) * 10;
            else    
                return Math.round(((abs_p - 1) * 10) / 3) * 3;
        }

        let profileChartInstance = null; // Global variable to store chart instance

        // Badge every gas switch directly on the profile (Pablo, 2026-09-16:
        // "mark the gas changes...add a badge icon on the chart at that
        // time").
        //
        // The API's own 'gas_switch' phase only ever flags the FIRST switch
        // (bottom/travel gas -> first deco gas) - it's really there for the
        // table to look up the bailout gas, not as a complete list of every
        // switch. Later switches between deco gas 1 -> 2 -> 3 -> 4 show up
        // only as the 'gas' field changing between consecutive rows, with
        // no phase tag of their own (Pablo, 2026-09-17: "There are two gas
        // switches at RT 29m and RT 39m...you are only showing one"). So
        // instead of trusting that phase tag, walk the same row sequence
        // the decompression table builds (descent -> bottom -> last ascent
        // before deco -> every deco stop -> final ascent) and drop a marker
        // anywhere the O2/He mix differs from the previous row - that
        // matches exactly what the table itself shows as a gas change.
        //
        // Pulled out to top-level (not nested in renderProfileChart) so the
        // "Bailout to OC" what-if handler can reuse it too (Pablo,
        // 2026-09-17: "in CC when the what if case is 'Bail out to OC' you
        // need to show the switching markers as in OC in the chart" - once
        // bailout kicks in the diver IS effectively running an OC profile).
        function formatGasLabel(gasArray) {
            return gasArray[3] == 0 ? (gasArray[1] + '%') : (gasArray[1] + '/' + gasArray[3]);
        }
        // `firstSwitchAtMaxDepth`: bailing out to OC happens the instant the
        // diver decides to bail, at the bottom - there's no swimming up to
        // a configured switch depth first like a planned OC deco gas change
        // (Pablo, 2026-09-18: "the first switch to the bailout happens at
        // max depth exactly when the diver starts to come up"). The API
        // bundles the whole ascent-before-first-deco-stop into one entry,
        // so without this flag the marker lands wherever that ascent ends
        // (correct for a regular OC dive's planned switch depth - already
        // confirmed working - but wrong for an immediate bailout). When
        // true, that specific transition is marked at the previous entry's
        // position (end of bottom time = max depth) instead of its own.
        // `timeMode` picks which entry's time/depth the marker uses when a
        // switch is detected:
        //   - 'entry' (default): the entry's own time+depth - correct for
        //     the bottom gas -> first deco gas switch, since the API's
        //     ascent-before-deco entry's time already IS the moment the
        //     diver arrives at that first stop.
        //   - 'prevEntry': the previous entry's time AND depth - the
        //     CC-bailout-at-max-depth case, where the switch is a discrete
        //     event that happens at the bottom, not on the walked list at all.
        //   - 'prevTime': previous entry's TIME but this entry's DEPTH -
        //     every deco-stop-to-deco-stop switch (and the final ascent to
        //     surface). A deco_stop's own .time is when the diver LEAVES
        //     that stop, but the gas switch itself happens the moment they
        //     ARRIVE at it, i.e. the previous stop's departure time, at the
        //     new (this entry's) depth (Pablo, 2026-09-19: "the switch
        //     happens at the beginning of the depth stop...take the RT from
        //     the previous stop...the current depth shown is correct").
        function computeOCGasSwitchPoints(baseline, firstSwitchAtMaxDepth, onlyRealSwitches) {
            var points = [];
            var prevMix = null;
            var prevEntry = null;
            function consider(entry, timeMode) {
                if (!entry || !Array.isArray(entry.gas)) return;
                var mix = entry.gas[1] + '/' + entry.gas[3];
                if (prevMix !== null && mix !== prevMix) {
                    var timeVal = timeMode === 'prevEntry' || timeMode === 'prevTime' ? prevEntry.time : entry.time;
                    var depthVal = timeMode === 'prevEntry' ? prevEntry.abs_p : entry.abs_p;
                    points.push({ time: timeVal, abs_p: depthVal, gasLabel: formatGasLabel(entry.gas) });
                }
                prevMix = mix;
                prevEntry = entry;
            }
            consider(baseline.find(function (r) { return r.phase === 'descent'; }));
            consider(baseline.find(function (r) { return r.phase === 'const'; }));

            var firstDecoStopIndex = baseline.findIndex(function (r) { return r.phase === 'deco_stop'; });
            // A bailout's "ascent to the first stop" can bundle MORE THAN
            // ONE real switch (diluent -> bailout gas, then bailout -> the
            // next OC deco gas, both before reaching the first stop) -
            // confirmed against the raw API response, which showed two
            // separate 'gas_switch'-tagged rows here. Collapsing this whole
            // span into a single point (as before, using only the LAST
            // ascent row) silently dropped every switch but the last one
            // (Pablo, 2026-09-19: "you are missing a gas switch when
            // Bailout... check you are not skipping any gas switch"). Walk
            // every row in order instead, exactly like the deco-stop loop
            // below already does, so each real mix change gets its own
            // point. The very first tagged switch still plots at max depth
            // (prevEntry - the bottom/const phase) to match the existing
            // "you bail right at the bottom" convention; any further
            // switch plots at its own real time/depth, since those are
            // genuine depth-triggered switches during the ascent.
            var ascentEnd = firstDecoStopIndex === -1 ? baseline.length : firstDecoStopIndex;
            var sawFirstTaggedSwitch = false;
            for (var i = 0; i < ascentEnd; i++) {
                var row = baseline[i];
                // A CC bailout's ascent legs still carry the loop's own
                // continuously-recalculated diluent-equivalent mix (holding
                // setpoint, not a real breathing gas change) - only a real
                // 'gas_switch'-tagged row is a genuine switch there, so
                // plain 'ascent' rows are excluded from consideration
                // entirely (Pablo, 2026-09-25: "every other gas in there is
                // related to the artificial gases the calculator uses to
                // keep the setpoint...before the switch to OC we only
                // should have CC" - without this, the CC loop's mix drift
                // between multi-level's levels was plotting a bogus switch
                // marker at the level boundary).
                if (onlyRealSwitches) {
                    if (row.phase !== 'gas_switch') continue;
                } else if (row.phase !== 'ascent' && row.phase !== 'gas_switch') continue;
                // Skip pass-through ascent waypoints whose gas hasn't
                // actually changed yet - calling consider() on them would
                // still advance prevEntry to that waypoint, which would
                // wreck the "at max depth" positioning below (prevEntry
                // needs to stay the bottom/const row until the FIRST real
                // transition, whichever row that turns out to be).
                if (!Array.isArray(row.gas) || row.gas[1] + '/' + row.gas[3] === prevMix) continue;
                var isFirstTaggedSwitch = firstSwitchAtMaxDepth && row.phase === 'gas_switch' && !sawFirstTaggedSwitch;
                if (row.phase === 'gas_switch') sawFirstTaggedSwitch = true;
                consider(row, isFirstTaggedSwitch ? 'prevEntry' : 'entry');
            }

            if (firstDecoStopIndex !== -1) {
                for (var j = firstDecoStopIndex; j < baseline.length; j++) {
                    if (baseline[j].phase === 'deco_stop') consider(baseline[j], 'prevTime');
                }
                consider(baseline[baseline.length - 1], 'prevTime'); // final ascent to surface
            }
            return points;
        }

        // A point annotation's own `label` sub-option only exists on
        // line/box annotations in chartjs-plugin-annotation v3 - point
        // annotations silently ignore it, which is why the gas text never
        // showed up. The documented way to caption a point is a second,
        // separate `type: 'label'` annotation anchored to the same
        // xValue/yValue.
        // `scenarioKey` ('baseline'/'lostDecoGas'/'bailout'/...) - when that
        // scenario's normalized object (window.dhScenarios[scenarioKey], see
        // dhNormalizeScenario) already carries a `gasSwitches` list from the
        // Deco Table API v2 backend, use it directly and skip
        // computeOCGasSwitchPoints()'s heuristics entirely - the engine
        // already knows exactly where the real switches are, feet-based
        // depth same as every other chart coordinate here.
        function buildGasSwitchAnnotations(baseline, unitConversion, firstSwitchAtMaxDepth, onlyRealSwitches, scenarioKey) {
            var annotations = {};
            var v2 = scenarioKey && window.dhScenarios && window.dhScenarios[scenarioKey];
            var points = (v2 && v2.gasSwitches)
                ? v2.gasSwitches.map(function (s) {
                    return { time: s.time, abs_p: 1 + s.depth / 33, gasLabel: formatGasLabel([null, s.gas.o2, null, s.gas.he]) };
                })
                : computeOCGasSwitchPoints(baseline, firstSwitchAtMaxDepth, onlyRealSwitches);
            points.forEach(function (point, idx) {
                annotations['gasSwitch' + idx] = {
                    type: 'point',
                    xValue: point.time,
                    yValue: -(point.abs_p - 1) * unitConversion,
                    backgroundColor: '#c2660f',
                    borderColor: '#ffffff',
                    borderWidth: 2,
                    radius: 7,
                    z: 10,
                };
                annotations['gasSwitchLabel' + idx] = {
                    type: 'label',
                    xValue: point.time,
                    yValue: -(point.abs_p - 1) * unitConversion,
                    yAdjust: -18,
                    content: point.gasLabel,
                    backgroundColor: '#c2660f',
                    color: '#ffffff',
                    font: { size: 11, weight: 'bold' },
                    padding: 4,
                    borderRadius: 4,
                    z: 10,
                };
            });
            return annotations;
        }

        // Multi-level's critical point (Pablo, 2026-09-25: "mark the critical
        // point as a red circle in the chart (time and depth)") - the moment
        // of greatest decompression obligation, per
        // https://claude.ai/artifact/6VxHGjo4WVgXxc2g1QZecM. depth/time come
        // back in ft always, same as every other chart coordinate here.
        function buildCriticalPointAnnotation(criticalPoint, unitConversion) {
            if (!criticalPoint) return {};
            var depthInChartUnits = modeImpOrMetric === 'met' ? criticalPoint.depth * (unitConversion / 33) : criticalPoint.depth;
            return {
                criticalPoint: {
                    type: 'point',
                    xValue: criticalPoint.time,
                    yValue: -depthInChartUnits,
                    backgroundColor: '#ff3b30',
                    borderColor: '#ffffff',
                    borderWidth: 2,
                    radius: 8,
                    z: 11,
                },
                criticalPointLabel: {
                    type: 'label',
                    xValue: criticalPoint.time,
                    yValue: -depthInChartUnits,
                    yAdjust: -20,
                    content: 'Critical point',
                    backgroundColor: '#ff3b30',
                    color: '#ffffff',
                    font: { size: 11, weight: 'bold' },
                    padding: 4,
                    borderRadius: 4,
                    z: 11,
                },
            };
        }

        function renderProfileChart(response) {

            let unitConversion = 33;
            if (modeImpOrMetric == "met")
                unitConversion = 10;
            // Convert data into correct format for a scatter plot
            formattedData = response['baseline'].map(item => ({ x: item.time, y: -(item.abs_p - 1) * unitConversion }));

            var gasSwitchAnnotations = modeOCOrCC === "OC" ? buildGasSwitchAnnotations(response['baseline'], unitConversion, undefined, undefined, 'baseline') : {};
            Object.assign(gasSwitchAnnotations, buildCriticalPointAnnotation(window.lastCriticalPoint, unitConversion));
            formattedData1 = response['add5min'].map(item => ({ x: item.time, y: -(item.abs_p - 1) * unitConversion }));
            formattedData2 = response['add10ft'].map(item => ({ x: item.time, y: -(item.abs_p - 1) * unitConversion }));
            formattedData3 = response['lostDecoGas'].map(item => ({ x: item.time, y: -(item.abs_p - 1) * unitConversion }));
            formattedData4 = response['short5min'].map(item => ({ x: item.time, y: -(item.abs_p - 1) * unitConversion }));
            formattedData5 = response['short10ft'].map(item => ({ x: item.time, y: -(item.abs_p - 1) * unitConversion }));
            formattedData6 = response['minDeco'].map(item => ({ x: item.time, y: -(item.abs_p - 1) * unitConversion }));
            formattedData7 = response['bailout'].map(item => ({ x: item.time, y: -(item.abs_p - 1) * unitConversion }));

            // Get the chart canvas
            var ctx = document.getElementById('profileChart').getContext('2d');
            var chartRow = document.getElementById("profileChartAndTable");
            chartRow.style.display = "block";
            // Real results are up - collapse the inputs panel out of the way.
            if (typeof window.dhDecoInputsSetOpen === 'function') window.dhDecoInputsSetOpen(false);

            // Destroy previous chart instance if it exists
            if (profileChartInstance) {
                profileChartInstance.destroy();
            }

            // A fresh calculation means a fresh chart with no overlay on it
            // yet - drop any UDDF overlay from a previous run along with its
            // "Overlaying: ..." status line, so it doesn't keep describing a
            // dataset that's no longer on screen (Pablo, 2026-09-22).
            uddfOverlayDataset = null;
            var uddfStatusEl = document.getElementById('uddfStatus');
            if (uddfStatusEl) uddfStatusEl.textContent = '';

            // Create Chart.js Scatter Plot (correctly scaled x-axis)
            profileChartInstance = new Chart(ctx, {
                type: 'scatter', // Scatter ensures proportional spacing of points
                plugins: [uddfOverlayFrontPlugin],
                data: {
                    datasets: [{
                            label: 'Deco profile',
                            data: formattedData,
                            borderColor: '#ffffff',
                            backgroundColor: 'rgba(255, 255, 255, 0.5)', // White shading
                            borderWidth: 2,
                            showLine: true, // Draws a connecting line
                            fill: true, // Enables shading under the line
                            pointRadius: 0,
                            pointHoverRadius: 6 // Shows markers on hover
                        },
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: {
                                color: '#FFFFFF' // Sets label font color to white
                            }
                        },
                        annotation: {
                            annotations: gasSwitchAnnotations,
                        },
                        tooltip: {
                            enabled: true, // Enables tooltips
                            mode: 'nearest', // Shows the closest point's tooltip
                            intersect: false, // Allows hovering anywhere near the line
                            callbacks: {
                                label: function (context) {
                                    // Convert time (decimal minutes) to mm:ss
                                    let totalSeconds = Math.round(context.raw.x * 60);
                                    let minutes = Math.floor(totalSeconds / 60);
                                    let seconds = totalSeconds % 60;
                                    let formattedTime = `${minutes}:${seconds.toString().padStart(2, '0')}`; // Ensures two-digit seconds

                                    // Round depth to nearest integer
                                    let roundedDepth = -Math.round(context.raw.y);

                                    //Unit
                                    let unit = "ft"
                                    if(modeImpOrMetric == "met")
                                        unit = "m";

                                    return `RT: ${formattedTime}, Depth: ${roundedDepth} ${unit}`;
                                }
                            }
                        },
                    },
                    scales: {
                        x: {
                            type: 'linear',
                            title: { display: true, text: 'Run Time (minutes)', color: 'white' }, // Label color white
                            ticks: { color: 'white' }, // Tick labels white
                            grid: { color: 'rgba(255, 255, 255, 0.5)' } // Grid lines semi-white
                        },
                        y: {
                            title: { display: true, text: 'Depth ({{ $unit }})', color: 'white' }, // Label color white
                            ticks: {
                                color: 'white',
                                callback: function(value) {
                                    return Math.abs(value); // Convert negative values to positive
                                }
                            },
                            grid: { color: 'rgba(255, 255, 255, 0.5)' } // Grid lines semi-white
                        }
                    }
                }
            });
        }
    </script>

    {{-- Overlay a real dive computer log (UDDF export) on the calculated
         profile chart above (Pablo, 2026-09-22). UDDF is a standard XML
         format several dive computer platforms export (Shearwater Cloud,
         Subsurface, MacDive...), not proprietary to one brand. Parsed
         entirely client-side - the file never reaches the server - and
         merged into the SAME profileChartInstance renderProfileChart()
         already built, using the identical {x: minutes, y: -depth} point
         shape, so it draws on the same axes and reuses that chart's
         existing tooltip/legend formatting for free. --}}
    <script>
        // Last successfully parsed overlay, kept at top level (not inside
        // the IIFE below) so the "What If?" checkbox handlers further down
        // the page - which each rebuild profileChartInstance.data.datasets
        // wholesale - can call reapplyUddfOverlay() to put it back rather
        // than silently losing the diver's uploaded log on every toggle.
        var uddfOverlayDataset = null;

        // Chart.js draws datasets in array order, so a What If? scenario
        // added AFTER the overlay (its own fill area painted on top) hides
        // the actual-dive line behind it, regardless of where in the array
        // reapplyUddfOverlay() re-inserts it (Pablo, 2026-09-22: "always
        // show the actual dive overlay at the front...the overlay gets
        // hidden in the back layer"). Redrawing just that one dataset in
        // afterDatasetsDraw - which fires once every other dataset has
        // already painted - guarantees it's always the last thing on the
        // canvas, independent of array order or dataset "order" values.
        var uddfOverlayFrontPlugin = {
            id: 'uddfOverlayFront',
            afterDatasetsDraw: function (chart) {
                var idx = chart.data.datasets.findIndex(function (d) {
                    return d.isActualDiveOverlay;
                });
                if (idx === -1) return;
                var meta = chart.getDatasetMeta(idx);
                if (meta && meta.controller) meta.controller.draw();
            }
        };

        function reapplyUddfOverlay() {
            if (!uddfOverlayDataset || !profileChartInstance) return;
            var alreadyThere = profileChartInstance.data.datasets.some(function (d) {
                return d.isActualDiveOverlay;
            });
            if (!alreadyThere) {
                profileChartInstance.data.datasets.push(uddfOverlayDataset);
            }
        }

        (function () {
            var input = document.getElementById('uddfFileInput');
            var btn = document.getElementById('uddfUploadBtn');
            var status = document.getElementById('uddfStatus');
            if (!btn || !input) return;

            btn.addEventListener('click', function () {
                if (!profileChartInstance) {
                    status.style.color = 'var(--dh-danger)';
                    status.textContent = 'Calculate a plan first, then overlay your log.';
                    return;
                }
                input.click();
            });

            input.addEventListener('change', function () {
                var file = input.files && input.files[0];
                if (!file) return;

                var reader = new FileReader();
                reader.onload = function () {
                    try {
                        var parsed = parseUddf(reader.result);
                        overlayRealDive(parsed);
                        status.style.color = 'var(--dh-good)';
                        status.textContent = 'Overlaying: ' + (parsed.label || file.name) + ' (' + parsed.points.length + ' points)';
                    } catch (e) {
                        console.error(e);
                        status.style.color = 'var(--dh-danger)';
                        status.textContent = 'Could not read that file - is it a UDDF dive log export?';
                    }
                };
                reader.onerror = function () {
                    status.style.color = 'var(--dh-danger)';
                    status.textContent = 'Could not read that file.';
                };
                reader.readAsText(file);
                input.value = ''; // allow re-selecting the same file again later
            });

            // UDDF's default xmlns makes plain getElementsByTagName miss every
            // element in most browsers unless the lookup is namespace-aware -
            // stripping the xmlns declaration before parsing is the standard,
            // simple workaround (element names in a UDDF export are never
            // namespace-prefixed, so nothing else needs to change).
            function parseUddf(xmlText) {
                var cleaned = xmlText.replace(/\sxmlns="[^"]*"/, '');
                var doc = new DOMParser().parseFromString(cleaned, 'application/xml');
                if (doc.querySelector('parsererror')) {
                    throw new Error('XML parse error');
                }

                var waypoints = doc.getElementsByTagName('waypoint');
                if (!waypoints.length) {
                    throw new Error('No waypoints found - not a UDDF dive log?');
                }

                var points = [];
                for (var i = 0; i < waypoints.length; i++) {
                    var wp = waypoints[i];
                    var depthEl = wp.getElementsByTagName('depth')[0];
                    var timeEl = wp.getElementsByTagName('divetime')[0];
                    if (!depthEl || !timeEl) continue;

                    var depthMeters = parseFloat(depthEl.textContent);
                    var timeSeconds = parseFloat(timeEl.textContent);
                    if (isNaN(depthMeters) || isNaN(timeSeconds)) continue;

                    // The calculated line's y is already in the planner's
                    // current unit (ft or m, see unitConversion in
                    // renderProfileChart above) - UDDF depth is always
                    // meters, so convert to match when in imperial mode.
                    var depthInUnit = modeImpOrMetric === 'imp' ? depthMeters * 3.28084 : depthMeters;
                    points.push({ x: timeSeconds / 60, y: -depthInUnit });
                }

                var siteName = doc.getElementsByTagName('name')[0];
                return { points: points, label: siteName ? siteName.textContent : null };
            }

            function overlayRealDive(parsed) {
                // Drop a previous overlay before adding the new one, so
                // re-uploading (a different dive, or a correction) doesn't
                // stack duplicate lines on the chart.
                profileChartInstance.data.datasets = profileChartInstance.data.datasets.filter(function (d) {
                    return !d.isActualDiveOverlay;
                });

                uddfOverlayDataset = {
                    label: 'Actual dive' + (parsed.label ? ' (' + parsed.label + ')' : ''),
                    data: parsed.points,
                    borderColor: '#ffd600', // Yellow line, fill stays transparent (Pablo, 2026-09-22)
                    backgroundColor: 'rgba(255, 214, 0, 0.15)',
                    borderWidth: 2,
                    showLine: true,
                    fill: false,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    isActualDiveOverlay: true,
                };
                profileChartInstance.data.datasets.push(uddfOverlayDataset);
                profileChartInstance.update();
            }
        })();
    </script>

    {{-- Script to show decompression table --}}
    <script>

        function getPhaseIcon(phase) {
            const icons = {
                // Legacy raw-step phase names (still used by generateDecoTable's
                // fallback Pass 1/2, until every endpoint is confirmed on v2).
                "descent": "south", // Down arrow for descent
                "const": "swap_horiz", // Stopwatch for bottom time
                "ascent": "north", // Up arrow for ascent
                "deco_stop": "pause_circle", // Pause for deco stops
                // Deco Table API v2's own table row phase names (Pablo,
                // 2026-09-25 - https://claude.ai/artifact/CQMT3MahLtqEgA68z7Fgta).
                "descend": "south",
                "level": "swap_horiz",
                "ascend": "north",
                "decoStop": "pause_circle",
                "surfaceAscent": "north",
            };
            return `<span class="material-icons-round">${icons[phase] || "help"}</span>`; // Default to "help" if missing
        }

        // `scenario` is the normalized {profile, table, gasSwitches, totals}
        // shape from dhNormalizeScenario() - every call site now passes
        // window.dhScenarios.<name> instead of a bare step array.
        function generateDecoTable(scenario, BO=0) {
            // Deco Table API v2 - once the backend sends a pre-consolidated
            // `table` directly, skip every heuristic below entirely (run
            // consolidation, CC-drift tolerance, crossedToOC, deco-stop
            // folding - see https://claude.ai/code/artifact/40cb7622-7fd7-4c44-ab0d-5a8a223f25e2)
            // and just render it.
            if (scenario && Array.isArray(scenario.table)) {
                return dhRenderDecoTableV2(scenario, BO);
            }

            let response = scenario ? scenario.profile : [];
            let tableData = []; // Store table rows

            // Gas column as the real green-O2/blue-He split pill instead of
            // plain "18/45" text (Pablo, 2026-09-19: "replace the Gas column
            // in the decompression tables with the gas pills format" /
            // "I don't like [the stacked pills]...just need to make sure
            // the width of the col is wide enough to fit the split pill") -
            // the Gas <th> now gets a fixed, generous % width instead
            // (table-layout:fixed above), so the normal joined split pill
            // fits without overlapping PPO2 next to it. .is-compact keeps a
            // dense multi-row table from getting too tall. row.gas only
            // ever feeds straight into a <td> below, so returning markup
            // here (instead of a plain string) is safe.
            function formatGas(gasArray) {
                return dhBuildGasSplitPillHtml(gasArray[1], gasArray[3], true);
            }
            function formatTime(minutes) {
                let totalSeconds = Math.round(minutes * 60);
                let mins = Math.floor(totalSeconds / 60);
                let secs = totalSeconds % 60;
                return `${mins}:${secs.toString().padStart(2, '0')}`; // Ensures two-digit seconds
            }

            function formatTimeMin(minutes) {
                return Math.ceil(minutes);
            }

            // Step 0: Check if we have deco or noUi-tick
            console.log(response);
            console.log(response.some(entry => entry.phase === "deco_stop"));
            if(response.some(entry => entry.phase === "deco_stop") === false) {
                GFL = document.getElementById("labelGFL").value
                GFH = document.getElementById("labelGFH").value
                model = "ZH-L16C-GF";
                let totalRuntime = Math.round(response[response.length - 1].time);
                document.getElementById("labelTotalDecoTime").innerHTML = "<b>" +"0 (No deco)" + "</b>";
                document.getElementById("labelTotalRunTime").innerHTML = "<b>" + Math.round(totalRuntime) + " m</b>";
                document.getElementById("labelModel").innerHTML = "<b>" + model + "</b>";
                document.getElementById("labelGFs").innerHTML = "<b>" + GFL + "/" + GFH +"</b>";
                return [totalRuntime, 0];
            }


            // Pass 1: consolidate consecutive steps into "runs". Single-level's
            // DecoPlanner coalesces each phase leg into one summary entry
            // already (one row falls out naturally), but MultiLevelDivePlanner
            // samples every ~10s throughout, including within a single level's
            // hold and within a single deco stop - grouping by (levelIndex,
            // phase, rounded depth) collapses those back into one row each
            // (Pablo, 2026-09-25: "the decompression table for multilevel
            // needs to be fixed...descend, level 1, ascend to level 2, level
            // 2, ascend to level 3...then all the deco stops" - this is what
            // was producing dozens of near-duplicate one-minute rows instead).
            // Depth only breaks a run for STATIONARY phases (const, deco_stop) -
            // it guards against silently merging two genuinely different stop
            // depths that happen to share a phase name. A descent/ascent leg's
            // depth changes every step by definition, so requiring it to match
            // there just re-splits the whole transit back into dozens of rows
            // (one per ~10ft) - the opposite of what's wanted.
            function dhIsStationaryPhase(phase) { return phase === 'const' || phase === 'deco_stop'; }

            let runs = [];
            {
                let i = (response.length && response[0].phase === 'start') ? 1 : 0;
                while (i < response.length) {
                    let step = response[i];
                    let keyLevel = (step.levelIndex === undefined || step.levelIndex === null) ? null : step.levelIndex;
                    let keyPhase = step.phase;
                    let keyDepth = Math.round((step.abs_p - 1) * 33);
                    let keyGas = Array.isArray(step.gas) ? step.gas[1] + '/' + step.gas[3] : null;
                    let j = i;
                    while (j + 1 < response.length) {
                        // A gas_switch marker mid-transit doesn't necessarily
                        // mean a real gas change - CC's own diluent/O2 ratio
                        // recalculates continuously as ambient pressure
                        // changes, so the API emits one of these at points
                        // during a single continuous ascent even on a
                        // single-level dive with no real bailout/deco gas
                        // (Pablo, 2026-09-25: "we are seeing entries when
                        // coming up for CC because the gas are artificially
                        // changing...collapse all the ascent to the first
                        // deco stop in one entry"). Peek past any run of
                        // gas_switch markers to see if the SAME transit
                        // continues after them, rather than letting them end
                        // the run. Scoped to CC only - on OC a gas_switch mid-
                        // ascent is always a real cylinder change (a travel/
                        // deco gas) that should keep showing as its own row.
                        // Stationary phases don't need this either way, since
                        // ambient pressure (and so the CC artifact) is
                        // constant while holding depth. Also excluded for a
                        // CC dive's own bailout table (BO=1) - once bailed,
                        // the diver is breathing OC-style staged gases, and
                        // THOSE switches (e.g. diluent -> the first deco gas
                        // at its own configured switch depth) are real,
                        // meaningful events, not the loop's continuous O2%
                        // recalculation (Pablo, 2026-09-25: "the table is
                        // incorrect too" - a real bailout gas switch was
                        // getting folded away by this same-page-mode check).
                        let lookahead = j + 1;
                        if (!dhIsStationaryPhase(keyPhase) && modeOCOrCC === 'CC' && !BO) {
                            while (response[lookahead] && response[lookahead].phase === 'gas_switch') lookahead++;
                        }
                        if (lookahead >= response.length) break;
                        let n = response[lookahead];
                        let nLevel = (n.levelIndex === undefined || n.levelIndex === null) ? null : n.levelIndex;
                        let nDepth = Math.round((n.abs_p - 1) * 33);
                        let nGas = Array.isArray(n.gas) ? n.gas[1] + '/' + n.gas[3] : null;
                        // A transit run also breaks on a real gas change -
                        // without this, a genuine mid-ascent switch (e.g. a
                        // CC bailout reaching a staged deco gas's own switch
                        // depth) that happens to still be tagged with the
                        // same levelIndex/phase as the leg before it got
                        // silently merged into one row at the WRONG depth
                        // (Pablo, 2026-09-25: "the table is incorrect too").
                        // Skipped specifically in the CC-artifact-tolerant
                        // case (modeOCOrCC==='CC' && !BO) - that's exactly
                        // when gas SHOULD be ignored (the loop's own
                        // continuous O2% recalculation, not a real switch).
                        let dhToleratesGasDrift = !dhIsStationaryPhase(keyPhase) && modeOCOrCC === 'CC' && !BO;
                        let sameRun = nLevel === keyLevel && n.phase === keyPhase
                            && (!dhIsStationaryPhase(keyPhase) || nDepth === keyDepth)
                            && (dhIsStationaryPhase(keyPhase) || dhToleratesGasDrift || nGas === keyGas);
                        if (sameRun) { j = lookahead; } else break;
                    }
                    runs.push({ phase: keyPhase, endStep: response[j] });
                    i = j + 1;
                }
            }

            let firstDecoRunIndex = runs.findIndex(r => r.phase === 'deco_stop');

            // Pass 2: turn runs into table rows. crossedToOC tracks whether
            // we've passed a REAL gas_switch marker yet - for a CC bailout
            // table (BO=1), everything before that is still the CC loop
            // (gas just drifting to hold setpoint, not a real breathing gas
            // change), and everything from that marker on is genuinely OC,
            // however many real switches the bailout stages through before
            // the first deco stop (Pablo, 2026-09-25: "you need to be
            // checking on when the first OC happens...before the switch to
            // OC we only should have CC" - the old isLastBeforeDeco/BOGas
            // approach only ever handled a single switch, mislabeling every
            // genuinely-OC leg before that as CC and showing "-" for its gas).
            let crossedToOC = false;
            let prevTime = 0;
            let totalDecoTime = 0;
            let totalRuntime = 0;

            for (let r = 0; r < runs.length; r++) {
                let phase = runs[r].phase;
                if (phase === 'gas_switch') { crossedToOC = true; continue; } // marker only, not a row

                let endStep = runs[r].endStep;
                let isDecoStop = phase === 'deco_stop';
                let isLastBeforeDeco = firstDecoRunIndex !== -1 && r === firstDecoRunIndex - 1;
                let isFinalAscentToSurface = firstDecoRunIndex !== -1 && r === runs.length - 1 && phase === 'ascent';
                let useDecoRounding = firstDecoRunIndex !== -1 && r >= firstDecoRunIndex - 1;

                // Every ascent strictly between the first deco stop and the
                // final ascent to the surface is just the diver leaving one
                // stop for the next, shallower one - fold its time into the
                // stop row it's leaving instead of showing it as its own
                // "10ft ascend, 1 min" row (Pablo, 2026-09-25: "the only
                // deco stop ascend I want to show is the last one to
                // surface").
                let isFoldableAscent = phase === 'ascent' && firstDecoRunIndex !== -1
                    && r > firstDecoRunIndex && !isFinalAscentToSurface;
                if (isFoldableAscent) {
                    let lastRow = tableData[tableData.length - 1];
                    if (lastRow) {
                        lastRow.time += (endStep.time - prevTime);
                        lastRow.runtime = endStep.time;
                    }
                    prevTime = endStep.time;
                    totalRuntime = endStep.time;
                    continue;
                }

                let gas = endStep.gas;
                let rowIsOC = modeOCOrCC === 'OC' || crossedToOC;

                let showGas = rowIsOC || isDecoStop || isLastBeforeDeco || isFinalAscentToSurface;
                let depth = useDecoRounding ? absPressureToDepthDeco(endStep.abs_p) : absPressureToDepth(endStep.abs_p);
                if (isFinalAscentToSurface) depth = 0;
                let ppo2AbsP = isFinalAscentToSurface ? 1 : endStep.abs_p;

                tableData.push({
                    phase: phase,
                    depth: depth,
                    time: endStep.time - prevTime,
                    runtime: endStep.time,
                    gas: showGas ? formatGas(gas) : "-",
                    gf: endStep.gf,
                    mode: rowIsOC ? 'OC' : 'CC',
                    ppo2: parseFloat((gas[1]) / 100 * ppo2AbsP).toFixed(2)
                });

                if (isDecoStop) totalDecoTime += (endStep.time - prevTime);
                prevTime = endStep.time;
                totalRuntime = endStep.time;
            }

            let tableHTML = "";
            if(modeOCOrCC == "OC") {
                // table-layout:fixed + explicit % widths (Pablo, 2026-09-19:
                // "you fixed the table with the gas pills, but not with the
                // split pills we have been using...make sure the width of
                // the col is wide enough...take space from other columns
                // that do not show content that wide") - auto layout just
                // squeezed every column to an equal share and let the split
                // pill (O2+He joined, ~130px wide) overflow into PPO2;
                // fixed sizing is the only way to hand Gas real room while
                // shrinking the phase-icon/Time/RT columns that don't need it.
                tableHTML =
                `<div class="table-responsive">
                    <table class="table table-striped table-sm" style="table-layout: fixed; width: 100%;">
                        <thead>
                            <tr>
                                <th class="phase-column" style="width: 7%;"></th>
                                <th class="depth-column text-sm" style="width: 12%; padding-left: 0px; padding-right:0px;">Depth</th>
                                <th class="text-sm" style="width: 9%; padding-left: 0px; padding-right:0px;">Time</th>
                                <th class="text-sm" style="width: 9%; padding-left: 0px; padding-right:0px;">RT</th>
                                <th class="text-sm" style="width: 32%; padding-left: 0px; padding-right:0px; text-align:center;">Gas</th>
                                <th class="text-sm" style="width: 17%; padding-left: 0px; padding-right:0px;">PPO&#8322;</th>
                                <th class="text-sm" style="width: 14%; padding-left: 0px; padding-right:0px;">GF</th>
                            </tr>
                        </thead>
                        <tbody>`;

                tableData.forEach(row => {
                    tableHTML += `<tr>
                        <td class="text-info">${getPhaseIcon(row.phase)}</td> <!-- Display icon instead of text -->
                        <td>${row.depth}</td>
                        <td class="text-sm text-left">${formatTimeMin(row.time)}</td>
                        <td class="text-sm">${formatTimeMin(row.runtime)}</td>
                        <td class="text-sm">${row.gas}</td>
                        <td class="text-sm">${row.ppo2}</td>
                        <td class="text-sm">${(row.gf * 100).toFixed(0)}%</td>
                    </tr>`;
                });
            } else if (modeOCOrCC == "CC" && !BO){
                tableHTML = 
                `<div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th class="phase-column"></th>
                                <th class="depth-column text-sm" style="padding-left: 0px; padding-right:0px;">Depth</th>
                                <th class="text-sm" style="padding-left: 0px; padding-right:0px;">Time</th>
                                <th class="text-sm" style="padding-left: 0px; padding-right:0px;">RT</th>
                                <th class="text-sm" style="padding-left: 0px; padding-right:0px;">PPO&#8322;</th>
                                <th class="text-sm" style="padding-left: 0px; padding-right:0px;">GF</th>
                            </tr>
                        </thead>
                        <tbody>`;

                tableData.forEach(row => {
                    tableHTML += `<tr>
                        <td class="text-info">${getPhaseIcon(row.phase)}</td> <!-- Display icon instead of text -->
                        <td>${row.depth}</td>
                        <td class="text-sm text-left">${formatTimeMin(row.time)}</td>
                        <td class="text-sm">${formatTimeMin(row.runtime)}</td>
                        <td class="text-sm">${labelSetpoint.value}</td>
                        <td class="text-sm">${(row.gf * 100).toFixed(0)}%</td>
                    </tr>`;
                });
            } else {
                tableHTML = 
                `<div style="overflow: auto;">
                    <table class="table table-striped table-sm" style="min-width:300px; width: 100%; table-layout: fixed;">
                        <thead>
                            <tr>
                                <th class="phase-column" style="width: 6%;"></th>
                                <th class="text-xs" style="width: 7%; padding-left: 0px; text-align:center;">Mode</th>
                                <th class="depth-column text-sm" style="width: 11%; padding-left: 0px; padding-right:0px;">Depth</th>
                                <th class="text-sm" style="width: 8%; padding-left: 0px; padding-right:0px;">Time</th>
                                <th class="text-sm" style="width: 8%; padding-left: 0px; padding-right:0px;">RT</th>
                                <th class="text-sm" style="width: 30%; padding-left: 0px; padding-right:0px; text-align:center;">Gas</th>
                                <th class="text-sm hide-on-mobile" style="width: 16%; padding-left: 0px; padding-right:0px;">PPO&#8322;</th>
                                <th class="text-sm hide-on-mobile" style="width: 14%; padding-left: 0px; padding-right:0px;">GF</th>
                            </tr>
                        </thead>
                        <tbody>`;

                tableData.forEach(row => {
                    tableHTML += `<tr>
                        <td class="text-info">${getPhaseIcon(row.phase)}</td> <!-- Display icon instead of text -->
                        <td class=text-xs style="padding-left: 0px;">${row.mode}</td>
                        <td>${row.depth}</td>
                        <td class="text-sm text-left">${formatTimeMin(row.time)}</td>
                        <td class="text-sm">${formatTimeMin(row.runtime)}</td>
                        <td class="text-xs">${row.gas}</td>
                        <td class="text-sm fw-bold hide-on-mobile">${row.ppo2}</td>
                        <td class="text-sm hide-on-mobile">${(row.gf * 100).toFixed(0)}%</td>
                    </tr>`;
                }); 
            }

            tableHTML += `</tbody></table></div>`;
            
            if(BO)
                document.getElementById("BOTableContainer").innerHTML = tableHTML;
            else {
                document.getElementById("decoTableContainer").innerHTML = tableHTML;

                GFL = document.getElementById("labelGFL").value
                GFH = document.getElementById("labelGFH").value
                model = "ZH-L16C-GF";
                updateDecoSummary(totalDecoTime, totalRuntime, model, GFH, GFL);
            }

            
            

            return [totalRuntime, totalDecoTime];
        }

        // Deco Table API v2 row renderer - see
        // https://claude.ai/code/artifact/40cb7622-7fd7-4c44-ab0d-5a8a223f25e2.
        // `scenario.table` is already fully consolidated and already knows
        // each row's own mode (CC/OC), so this is pure rendering: no run-
        // merging, no gas-drift tolerance, no crossedToOC tracking. One row
        // template, keyed off each row's own `mode` - a pure OC or pure CC
        // table just never varies it, and a CC bailout table's real mid-
        // table switch just falls out of the data.
        //
        // Row phase values reuse the SAME strings the raw step API already
        // emits ("descent"/"const"/"ascent"/"deco_stop" - see
        // getPhaseIcon() above), not new enum names - the backend doesn't
        // need to invent a vocabulary the frontend doesn't already read.
        function dhRenderDecoTableV2(scenario, BO) {
            var rows = scenario.table;
            var totals = scenario.totals;

            function fmtDepth(depthFt) {
                // Backend already picked the right rounding convention per
                // leg (e.g. stops to the nearest 10ft) - this is a straight
                // unit conversion for display, nothing else. Matches the
                // existing 33ft-per-atm/10m-per-atm convention used
                // everywhere else in this file (absPressureToDepth).
                return modeImpOrMetric === 'imp' ? Math.round(depthFt) : Math.round(depthFt / 33 * 10);
            }
            function fmtTimeMin(minutes) { return Math.ceil(minutes); }
            // PPO2 comes back from the API at whatever precision the engine
            // computed it at (e.g. 1.3218) - always round to 2 decimals for
            // display (Pablo, 2026-09-26: "all PPO2 should have only two
            // decimals - you are showing 4 in the tables").
            function fmtPpo2(ppo2) { return parseFloat(ppo2).toFixed(2); }
            // A PPO2 over 1.6 ATA gets its own red pill right in the table,
            // not just plain bold text (Pablo, 2026-09-26: "if any PPO2 is
            // larger than 1.6, show it in the table as a red pill with
            // white fonts").
            function fmtPpo2Cell(ppo2) {
                var val = fmtPpo2(ppo2);
                if (parseFloat(val) <= 1.6) return val;
                return '<span class="dh-gas-result-pill is-compact is-danger">'
                    + '<span class="material-icons-round" aria-hidden="true" style="font-size: 13px;">warning</span> ' + val + '</span>';
            }
            function fmtGasCell(row) {
                // A CC row's `gas` is null (there's no fixed cylinder mix on
                // the loop) - show the diluent actually being breathed, not
                // the setpoint (a PPO2 target, not a gas) (Pablo, 2026-09-26:
                // "in the bailout table, in the CC stages, you are put the
                // setpoint as gas. You need to put the diluent").
                if (row.mode === 'CC' || !row.gas) {
                    var dilO2 = document.getElementById('labelBottomGasO2');
                    var dilHe = document.getElementById('labelBottomGasHe');
                    return (dilO2 && dilHe) ? dhBuildGasSplitPillHtml(dilO2.value, dilHe.value, true) : '-';
                }
                return dhBuildGasSplitPillHtml(row.gas.o2, row.gas.he, true);
            }

            var hasMixedMode = rows.length > 0 && rows.some(function (r) { return r.mode !== rows[0].mode; });
            var isPureCC = rows.length > 0 && rows.every(function (r) { return r.mode === 'CC'; });
            // A pure OC or pure CC table doesn't need a Mode column at all
            // (every row already says so via its gas cell) - only a real
            // bailout table, where mode genuinely changes mid-table, needs it.
            var showModeColumn = hasMixedMode;
            var showGasColumn = !isPureCC;

            // Column widths, narrowest for Time/RT/GF so Gas/PPO2 (the
            // columns that actually carry the interesting content) get more
            // room (Pablo, 2026-09-26: "narrow the cols time, RT, and GF to
            // give more room to gas [and] PPO2") - one width set per column
            // combination so the numbers always add up to 100%.
            var w = showModeColumn
                ? { phase: 6, mode: 7, depth: 10, time: 7, rt: 7, gas: 34, ppo2: 18, gf: 11 }
                : showGasColumn
                    ? { phase: 6, depth: 11, time: 8, rt: 8, gas: 36, ppo2: 19, gf: 12 }
                    : { phase: 8, depth: 15, time: 10, rt: 10, ppo2: 33, gf: 24 };

            // Every header shares the same font/size and is centered (Pablo,
            // 2026-09-26: "center all the titles...make sure they all have
            // the same font and size" - Mode used to be text-xs while every
            // other header was text-sm).
            function th(label, pct, extraClass) {
                return '<th class="text-sm text-center' + (extraClass ? ' ' + extraClass : '') + '" style="width: ' + pct + '%; padding-left: 0px; padding-right: 0px;">' + label + '</th>';
            }

            var theadCells = '<th class="phase-column" style="width: ' + w.phase + '%;"></th>';
            if (showModeColumn) theadCells += th('Mode', w.mode);
            theadCells += th('Depth', w.depth, 'depth-column');
            theadCells += th('Time', w.time);
            theadCells += th('RT', w.rt);
            if (showGasColumn) theadCells += th('Gas', w.gas);
            theadCells += th('PPO&#8322;', w.ppo2, 'hide-on-mobile');
            theadCells += th('GF', w.gf, 'hide-on-mobile');

            var tableHTML = '<div style="overflow: auto;"><table class="table table-striped table-sm" style="min-width:300px; width: 100%; table-layout: fixed;"><thead><tr>'
                + theadCells + '</tr></thead><tbody>';

            rows.forEach(function (row) {
                tableHTML += '<tr><td class="text-info">' + getPhaseIcon(row.phase) + '</td>';
                if (showModeColumn) tableHTML += '<td class="text-sm" style="padding-left: 0px;">' + row.mode + '</td>';
                tableHTML += '<td>' + fmtDepth(row.depth) + '</td>';
                tableHTML += '<td class="text-sm text-left">' + fmtTimeMin(row.time) + '</td>';
                tableHTML += '<td class="text-sm">' + fmtTimeMin(row.runtime) + '</td>';
                if (showGasColumn) tableHTML += '<td class="text-sm">' + fmtGasCell(row) + '</td>';
                tableHTML += '<td class="text-sm fw-bold hide-on-mobile">' + fmtPpo2Cell(row.ppo2) + '</td>';
                tableHTML += '<td class="text-sm hide-on-mobile">' + (row.gf * 100).toFixed(0) + '%</td></tr>';
            });
            tableHTML += '</tbody></table></div>';

            var lastRow = rows[rows.length - 1];
            var totalRuntime = totals ? totals.runtime : (lastRow ? lastRow.runtime : 0);
            var totalDecoTime = totals ? totals.decoTime
                : rows.filter(function (r) { return r.phase === 'decoStop'; }).reduce(function (sum, r) { return sum + r.time; }, 0);

            if (BO) {
                document.getElementById("BOTableContainer").innerHTML = tableHTML;
            } else {
                document.getElementById("decoTableContainer").innerHTML = tableHTML;
                var gf = totals && totals.gf ? totals.gf : {
                    low: document.getElementById("labelGFL").value,
                    high: document.getElementById("labelGFH").value,
                };
                updateDecoSummary(totalDecoTime, totalRuntime, (totals && totals.model) || "ZH-L16C-GF", gf.high, gf.low);
            }

            return [totalRuntime, totalDecoTime];
        }

        function calculateDecoTime(scenario) {
            // Deco Table API v2 - totals are already computed backend-side.
            if (scenario && scenario.totals) {
                return [scenario.totals.runtime, scenario.totals.decoTime];
            }
            let response = scenario ? scenario.profile : [];
            let tableData = []; // Store table rows
            let prevTime = 0; // Track cumulative runtime

            // Function to format gas mixture (O2/He)
            function formatGas(gasArray) {
                if (gasArray[3] == 0)
                    return `${gasArray[1]}%`; // O2/He format
                else
                    return `${gasArray[1]}/${gasArray[3]}`; // O2/He format
            }
            function formatTime(minutes) {
                let totalSeconds = Math.round(minutes * 60);
                let mins = Math.floor(totalSeconds / 60);
                let secs = totalSeconds % 60;
                return `${mins}:${secs.toString().padStart(2, '0')}`; // Ensures two-digit seconds
            }

            function formatTimeMin(minutes) {
                return Math.ceil(minutes);
            }

            // Step 0: Check if we have deco or noUi-tick
            
            if(!response.some(entry => entry.phase === "deco_stop")) {
                let totalRuntime = Math.round(response[response.length - 1].time);
                return [totalRuntime, 0];
            }
            // Step 1: Add descent row
            let descent = response.find(r => r.phase === "descent");
            if (descent) {
                tableData.push({
                    phase: "descent",
                    depth: absPressureToDepth(descent.abs_p),
                    time: descent.time, 
                    runtime: prevTime + descent.time,
                    gas: formatGas(descent.gas),
                    gf: descent.gf
                });
                prevTime += descent.time;
            }

            // Step 2: Add bottom time row (const)
            let bottomTime = response.find(r => r.phase === "const");
            if (bottomTime) {
                tableData.push({
                    phase: "const",
                    depth: absPressureToDepth(bottomTime.abs_p),
                    time: bottomTime.time - prevTime,
                    runtime: bottomTime.time,
                    gas: formatGas(bottomTime.gas),
                    gf: bottomTime.gf
                });
                //prevTime += bottomTime.time;
                prevTime = bottomTime.time;
            }

            // Find the last "ascent" that occurs before any "deco_stop"
            let lastAscentBeforeDeco = null;

            for (let i = 0; i < response.length; i++) {
                if (response[i].phase === "ascent") {
                    lastAscentBeforeDeco = response[i]; // Keep updating with the last ascent found
                }
                if (response[i].phase === "deco_stop") {
                    response = response.slice(i); // Keeps all phases from index i to the end
                    break; // Stop when we hit the first deco stop
                }
            }

            if (lastAscentBeforeDeco) {
                tableData.push({
                    phase: "ascent",
                    depth: absPressureToDepthDeco(lastAscentBeforeDeco.abs_p),
                    time: lastAscentBeforeDeco.time - prevTime, // Time taken for the last ascent before deco stop
                    runtime: lastAscentBeforeDeco.time, // Run time should be the time of the last ascent
                    gas: formatGas(lastAscentBeforeDeco.gas),
                    gf: lastAscentBeforeDeco.gf
                });
                prevTime = lastAscentBeforeDeco.time;
            }

            let firstDecoStopIndex = response.findIndex(r => r.phase === "deco_stop");
            //console.log(response);
            let totalDecoTime = 0;
            let totalRuntime = 0;

            if (firstDecoStopIndex !== -1) {
                //let prevTime = response[firstDecoStopIndex].time; // Start tracking from the first deco stop
                
                for (let i = firstDecoStopIndex; i < response.length; i++) {
                    let entry = response[i];

                    // Process deco stops
                    if (entry.phase === "deco_stop") {
                        //let nextDecoStop = response.find(r => r.phase === "deco_stop" && r.time > entry.time);
                        //let nextAscent = response.find(r => r.phase === "ascent" && r.abs_p < entry.abs_p);

                        //let timeSpent = nextDecoStop ? nextDecoStop.time - entry.time : nextAscent.time - entry.time;
                        let timeSpent = entry.time - prevTime;

                        tableData.push({
                            phase: `deco_stop`,
                            depth: absPressureToDepthDeco(entry.abs_p),
                            time: timeSpent, // Corrected calculation: difference between consecutive stops
                            runtime: entry.time,
                            gas: formatGas(entry.gas),
                            gf: entry.gf
                        });

                        prevTime = entry.time;
                        totalDecoTime += timeSpent;
                    }
                }

                // Handle final ascent to surface
                //let lastAscent = response.find(r => r.phase === "ascent" && r.abs_p <= 1.01325);
                let lastAscent = response[response.length - 1];

                if (lastAscent) {
                    tableData.push({
                        phase: "ascent",
                        depth: 0,
                        time: lastAscent.time - prevTime,
                        runtime: lastAscent.time,
                        gas: formatGas(lastAscent.gas),
                        gf: lastAscent.gf
                    });

                    totalRuntime = lastAscent.time;
                }

                // Remove processed phases from `response`
                response = response.slice(firstDecoStopIndex);
            }
            return [totalRuntime, totalDecoTime];
        }
    </script>

    {{-- Script to update summary table --}}
    <script>
        function updateDecoSummary(decoTime, runTime, model="ZH-L16C-GF", GFH="75", GFL="40") {
            document.getElementById("labelTotalDecoTime").innerHTML = "<b>" + Math.round(decoTime) + " m</b>";
            document.getElementById("labelTotalRunTime").innerHTML = "<b>" + Math.round(runTime) + " m</b>";

            document.getElementById("labelModel").innerHTML = "<b>" + model + "</b>";
            document.getElementById("labelGFs").innerHTML = "<b>" + GFL + "/" + GFH +"</b>";

        }

        // Highest PPO2 reached anywhere in the dive - walks the fine-grained
        // profile (not the coarse table) since a momentary peak mid-leg can
        // exceed 1.6 without ever being a row's own start/end value (Pablo,
        // 2026-09-26: "if at any point of the dive any PPO2 is higher than
        // 1.6").
        function dhComputeMaxPpo2(profile) {
            var max = 0;
            if (!Array.isArray(profile)) return max;
            profile.forEach(function (step) {
                if (!Array.isArray(step.gas)) return;
                var ppo2 = step.gas[1] / 100 * step.abs_p;
                if (ppo2 > max) max = ppo2;
            });
            return max;
        }

        // Highest END reached anywhere in the dive. Deliberately NOT
        // computed here - the backend is adding this per-point (Pablo,
        // 2026-09-26: "probably its a good idea that the backend calculates
        // the END for all points of the dive so we don't calculate ENDs on
        // the front end"). Returns null (no END-based warning shown) until
        // wired to whatever field/shape they ship - see the message to
        // dh-back-end for the exact ask.
        function dhComputeMaxEnd(scenario) {
            return null;
        }

        // Red/yellow safety pills below the Run time/Deco time pills -
        // PPO2 > 1.6 ATA, CNS% >= 100, END > 200ft all red; END > 150ft
        // (and <= 200ft) yellow (Pablo, 2026-09-26: "I want to flag when a
        // dive plan is unsafe"). Call with the scenario currently driving
        // the main summary (today: baseline).
        function dhRenderSafetyWarnings(scenario) {
            var row = document.getElementById('dhSafetyWarningsRow');
            var container = document.getElementById('dhSafetyWarningsContainer');
            if (!row || !container) return;
            container.innerHTML = '';
            if (!scenario) { row.hidden = true; return; }

            var warnings = [];

            var maxPpo2 = dhComputeMaxPpo2(scenario.profile);
            if (maxPpo2 > 1.6) {
                warnings.push({ level: 'danger', text: 'PPO₂ above 1.6 (' + maxPpo2.toFixed(2) + ')' });
            }

            var cns = scenario.o2Exposure ? scenario.o2Exposure.cnsPercent : null;
            if (cns !== null && cns !== undefined && cns >= 100) {
                warnings.push({ level: 'danger', text: 'CNS ' + Math.round(cns) + '% (100%+)' });
            }

            var maxEnd = dhComputeMaxEnd(scenario);
            if (maxEnd !== null && maxEnd !== undefined) {
                var endUnit = modeImpOrMetric === 'imp' ? 'ft' : 'm';
                var endDisplay = modeImpOrMetric === 'imp' ? Math.round(maxEnd) : Math.round(maxEnd * 0.3048);
                var endDangerThreshold = modeImpOrMetric === 'imp' ? 200 : Math.round(200 * 0.3048);
                var endWarnThreshold = modeImpOrMetric === 'imp' ? 150 : Math.round(150 * 0.3048);
                if (endDisplay > endDangerThreshold) {
                    warnings.push({ level: 'danger', text: 'END above ' + endDangerThreshold + endUnit + ' (' + endDisplay + endUnit + ')' });
                } else if (endDisplay > endWarnThreshold) {
                    warnings.push({ level: 'warn', text: 'END above ' + endWarnThreshold + endUnit + ' (' + endDisplay + endUnit + ')' });
                }
            }

            warnings.forEach(function (w) {
                var pill = document.createElement('span');
                pill.className = 'dh-gas-result-pill dh-deco-summary-pill is-' + w.level;
                pill.innerHTML = '<span class="material-icons-round" aria-hidden="true" style="font-size: 16px; vertical-align: -3px;">warning</span> ' + w.text;
                container.appendChild(pill);
            });

            row.hidden = warnings.length === 0;
        }
    </script>

    {{-- Scripts to manage WhatIf? checksboxes --}}
    <script>
        document.getElementById("filter1").addEventListener("change", function() {
            
            // we only show the decompression table if the baseline has deco
            if(baselineRTDT[1] > 0) {
                document.getElementById("decoTableContainer").style.display = "block";
                document.getElementById("BOTableContainer").style.display = "none";
                document.getElementById("decoTableTitle").innerText = "Decompression Table";
            }

            if (!this.checked) {
                profileChartInstance.data.datasets = profileChartInstance.data.datasets.filter(dataset => dataset.label === "Deco profile");
                document.getElementById("labelWhatIfRunTime").innerText="-";
                document.getElementById("labelWhatIfRunTimeDiff").innerText="-";
                document.getElementById("labelWhatIfDecoTime").innerText="-";
                document.getElementById("labelWhatIfDecoTimeDiff").innerText="-";
            } else {
                profileChartInstance.data.datasets = [
                    {
                        label: 'Deco profile',
                        data: formattedData,
                        borderColor: '#ffffff',
                        backgroundColor: 'rgba(255, 255, 255, 0.5)',
                        borderWidth: 2,
                        showLine: true,
                        fill: true,
                        pointRadius: 0,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Extend bottom time 5 min',
                        data: formattedData1,
                        borderColor: '#8c1c13', // Darker red than the fill, so the line reads against it (Pablo, 2026-09-22)
                        backgroundColor: '#f44335',
                        borderWidth: 2,
                        showLine: true,
                        fill: true,
                        pointRadius: 0,
                        pointHoverRadius: 6
                    }
                ];

                // update new decoT and newRT
            
                let labelRT = document.getElementById("labelWhatIfRunTime");
                let labelRTDiff = document.getElementById("labelWhatIfRunTimeDiff");
                
                labelRT.innerText = `${Math.round(filter1RTDT[0])} min`;
                var difference = Math.round(filter1RTDT[0] - baselineRTDT[0]);
                var sign = difference >= 0 ? "+" : "-";

                // Update text with the correct sign
                labelRTDiff.textContent = difference === 0 ? "-" : `${sign}${Math.abs(difference)}`;

                // Change color based on sign
                labelRTDiff.classList.toggle("is-danger", difference > 0);
                labelRTDiff.classList.toggle("is-ideal", difference < 0);

                let labelDT = document.getElementById("labelWhatIfDecoTime");
                let labelDTDiff = document.getElementById("labelWhatIfDecoTimeDiff");
                
            
                labelDT.innerText = `${Math.round(filter1RTDT[1])} min`;
                difference = Math.round(filter1RTDT[1] - baselineRTDT[1]);
                sign = difference >= 0 ? "+" : "-";

                // Update text with the correct sign
                labelDTDiff.textContent = difference === 0 ? "-" : `${sign}${Math.abs(difference)}`;

                // Change color based on sign
                labelDTDiff.classList.toggle("is-danger", difference > 0);
                labelDTDiff.classList.toggle("is-ideal", difference < 0);
            }

            reapplyUddfOverlay(); // keep an uploaded real-dive log visible across What If? toggles
            profileChartInstance.update(); // Refresh chart

            document.querySelectorAll(".form-check-input").forEach(cb => {
                if (cb !== this) {
                    cb.checked = false; // Uncheck all other checkboxes
                }
            });

            // hide gas consumption if CC
            if(modeOCOrCC === "CC")
                document.getElementById("gasConsumptionRow").style.display="none";

        });

        document.getElementById("filter2").addEventListener("change", function() {

            // we only show the decompression table if the baseline has deco
            if(baselineRTDT[1] > 0) {
                document.getElementById("decoTableContainer").style.display = "block";
                document.getElementById("BOTableContainer").style.display = "none";
                document.getElementById("decoTableTitle").innerText = "Decompression Table";
            }

            if (!this.checked) {
                profileChartInstance.data.datasets = profileChartInstance.data.datasets.filter(dataset => dataset.label === "Deco profile");
                let labelRT = document.getElementById("labelWhatIfRunTime").innerText="-";
                let labelRTDiff = document.getElementById("labelWhatIfRunTimeDiff").innerText="-";
                let labelDT = document.getElementById("labelWhatIfDecoTime").innerText="-";
                let labelDTDiff = document.getElementById("labelWhatIfDecoTimeDiff").innerText="-";
            } else {
                profileChartInstance.data.datasets = [
                    {
                        label: 'Deco profile',
                        data: formattedData,
                        borderColor: '#ffffff',
                        backgroundColor: 'rgba(255, 255, 255, 0.5)',
                        borderWidth: 2,
                        showLine: true,
                        fill: true,
                        pointRadius: 0,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Increase max depth 10ft/3m',
                        data: formattedData2,
                        borderColor: '#8c1c13', // Darker red than the fill, so the line reads against it (Pablo, 2026-09-22)
                        backgroundColor: '#f44335',
                        borderWidth: 2,
                        showLine: true,
                        fill: true,
                        pointRadius: 0,
                        pointHoverRadius: 6
                    }
                ];

                // update new decoT and newRT
            
                let labelRT = document.getElementById("labelWhatIfRunTime");
                let labelRTDiff = document.getElementById("labelWhatIfRunTimeDiff");
                
                labelRT.innerText = `${Math.round(filter2RTDT[0])} min`;
                var difference = Math.round(filter2RTDT[0] - baselineRTDT[0]);
                var sign = difference >= 0 ? "+" : "-";

                // Update text with the correct sign
                labelRTDiff.textContent = difference === 0 ? "-" : `${sign}${Math.abs(difference)}`;

                // Change color based on sign
                labelRTDiff.classList.toggle("is-danger", difference > 0);
                labelRTDiff.classList.toggle("is-ideal", difference < 0);

                let labelDT = document.getElementById("labelWhatIfDecoTime");
                let labelDTDiff = document.getElementById("labelWhatIfDecoTimeDiff");
                
            
                labelDT.innerText = `${Math.round(filter2RTDT[1])} min`;
                difference = Math.round(filter2RTDT[1] - baselineRTDT[1]);
                sign = difference >= 0 ? "+" : "-";

                // Update text with the correct sign
                labelDTDiff.textContent = difference === 0 ? "-" : `${sign}${Math.abs(difference)}`;

                // Change color based on sign
                labelDTDiff.classList.toggle("is-danger", difference > 0);
                labelDTDiff.classList.toggle("is-ideal", difference < 0);
            }

            reapplyUddfOverlay(); // keep an uploaded real-dive log visible across What If? toggles
            profileChartInstance.update(); // Refresh chart

            document.querySelectorAll(".form-check-input").forEach(cb => {
                    if (cb !== this) {
                        cb.checked = false; // Uncheck all other checkboxes
                    }
            });

            // hide gas consumption if CC
            if(modeOCOrCC === "CC")
                document.getElementById("gasConsumptionRow").style.display="none";
            
        });

        document.getElementById("filter3").addEventListener("change", function() {
            
            // we only show the decompression table if the baseline has deco
            if(baselineRTDT[1] > 0) {
                document.getElementById("decoTableContainer").style.display = "block";
                document.getElementById("BOTableContainer").style.display = "none";
                document.getElementById("decoTableTitle").innerText = "Decompression Table";
            }

            if (!this.checked) {
                profileChartInstance.data.datasets = profileChartInstance.data.datasets.filter(dataset => dataset.label === "Deco profile");
                // Keep the critical point marker on screen when toggling
                // this off, same as every other annotations reset below
                // (Pablo, 2026-09-25: "once you click on what if and
                // another overlay is shown, the critical point marker
                // disappears...make it part of the baseline group").
                profileChartInstance.options.plugins.annotation.annotations = buildCriticalPointAnnotation(window.lastCriticalPoint, modeImpOrMetric == "met" ? 10 : 33);
                let labelRT = document.getElementById("labelWhatIfRunTime").innerText="-";
                let labelRTDiff = document.getElementById("labelWhatIfRunTimeDiff").innerText="-";
                let labelDT = document.getElementById("labelWhatIfDecoTime").innerText="-";
                let labelDTDiff = document.getElementById("labelWhatIfDecoTimeDiff").innerText="-";
            } else {
                profileChartInstance.data.datasets = [
                    {
                        label: 'Deco profile',
                        data: formattedData,
                        borderColor: '#ffffff',
                        backgroundColor: 'rgba(255, 255, 255, 0.5)',
                        borderWidth: 2,
                        showLine: true,
                        fill: true,
                        pointRadius: 0,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Lost deco gases',
                        data: formattedData3,
                        borderColor: '#8c1c13', // Darker red than the fill, so the line reads against it (Pablo, 2026-09-22)
                        backgroundColor: '#f44335',
                        borderWidth: 2,
                        showLine: true,
                        fill: true,
                        pointRadius: 0,
                        pointHoverRadius: 6
                    }
                ];
                // Losing every extra deco gas still leaves one real switch -
                // diluent/backgas to the (mandatory, in CC) bailout gas,
                // right at the bottom (Pablo, 2026-09-18: "there is only one
                // switch to the bailout and that's it"). In OC this
                // naturally computes to zero switches (losing deco gases
                // just means staying on bottom gas the whole way up).
                var unitConversion3 = modeImpOrMetric == "met" ? 10 : 33;
                // Same multi-level exception as the bailout overlay below -
                // no single "the bottom" to force the marker back to.
                profileChartInstance.options.plugins.annotation.annotations = buildGasSwitchAnnotations(globalResponse['lostDecoGas'], unitConversion3, modeLevelSingleOrMulti !== 'multi', undefined, 'lostDecoGas');
                Object.assign(profileChartInstance.options.plugins.annotation.annotations, buildCriticalPointAnnotation(window.lastCriticalPoint, unitConversion3));

                // update new decoT and newRT
            
                let labelRT = document.getElementById("labelWhatIfRunTime");
                let labelRTDiff = document.getElementById("labelWhatIfRunTimeDiff");
                
                labelRT.innerText = `${Math.round(filter3RTDT[0])} min`;
                var difference = Math.round(filter3RTDT[0] - baselineRTDT[0]);
                var sign = difference >= 0 ? "+" : "-";

                // Update text with the correct sign
                labelRTDiff.textContent = difference === 0 ? "-" : `${sign}${Math.abs(difference)}`;

                // Change color based on sign
                labelRTDiff.classList.toggle("is-danger", difference > 0);
                labelRTDiff.classList.toggle("is-ideal", difference < 0);

                let labelDT = document.getElementById("labelWhatIfDecoTime");
                let labelDTDiff = document.getElementById("labelWhatIfDecoTimeDiff");
                
            
                labelDT.innerText = `${Math.round(filter3RTDT[1])} min`;
                difference = Math.round(filter3RTDT[1] - baselineRTDT[1]);
                sign = difference >= 0 ? "+" : "-";

                // Update text with the correct sign
                labelDTDiff.textContent = difference === 0 ? "-" : `${sign}${Math.abs(difference)}`;

                // Change color based on sign
                labelDTDiff.classList.toggle("is-danger", difference > 0);
                labelDTDiff.classList.toggle("is-ideal", difference < 0);
            }

            reapplyUddfOverlay(); // keep an uploaded real-dive log visible across What If? toggles
            profileChartInstance.update(); // Refresh chart

            document.querySelectorAll(".form-check-input").forEach(cb => {
                    if (cb !== this) {
                        cb.checked = false; // Uncheck all other checkboxes
                    }
            });

            // hide gas consumption if CC
            if(modeOCOrCC === "CC")
                document.getElementById("gasConsumptionRow").style.display="none";
            
        }); 

        document.getElementById("filter4").addEventListener("change", function() {
            // we only show the decompression table if the baseline has deco
            if(baselineRTDT[1] > 0) {
                document.getElementById("decoTableContainer").style.display = "block";
                document.getElementById("BOTableContainer").style.display = "none";
                document.getElementById("decoTableTitle").innerText = "Decompression Table";
            }

            if (!this.checked) {
                profileChartInstance.data.datasets = profileChartInstance.data.datasets.filter(dataset => dataset.label === "Deco profile");
                let labelRT = document.getElementById("labelWhatIfRunTime").innerText="-";
                let labelRTDiff = document.getElementById("labelWhatIfRunTimeDiff").innerText="-";
                let labelDT = document.getElementById("labelWhatIfDecoTime").innerText="-";
                let labelDTDiff = document.getElementById("labelWhatIfDecoTimeDiff").innerText="-";
            } else {
                profileChartInstance.data.datasets = [
                    {
                        label: 'Shorten bottom time 5 min',
                        data: formattedData4,
                        borderColor: '#1b5e20', // Darker green than the fill, so the line reads against it (Pablo, 2026-09-22)
                        backgroundColor: '#4caf50',
                        borderWidth: 2,
                        showLine: true,
                        fill: true,
                        pointRadius: 0,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Deco profile',
                        data: formattedData,
                        borderColor: '#ffffff',
                        backgroundColor: 'rgba(255, 255, 255, 0.5)',
                        borderWidth: 2,
                        showLine: true,
                        fill: true,
                        pointRadius: 0,
                        pointHoverRadius: 6
                    },
                    
                ];

                // update new decoT and newRT
            
                let labelRT = document.getElementById("labelWhatIfRunTime");
                let labelRTDiff = document.getElementById("labelWhatIfRunTimeDiff");
                
                labelRT.innerText = `${Math.round(filter4RTDT[0])} min`;
                var difference = Math.round(filter4RTDT[0] - baselineRTDT[0]);
                var sign = difference >= 0 ? "+" : "-";

                // Update text with the correct sign
                labelRTDiff.textContent = difference === 0 ? "-" : `${sign}${Math.abs(difference)}`;

                // Change color based on sign
                labelRTDiff.classList.toggle("is-danger", difference > 0);
                labelRTDiff.classList.toggle("is-ideal", difference < 0);

                let labelDT = document.getElementById("labelWhatIfDecoTime");
                let labelDTDiff = document.getElementById("labelWhatIfDecoTimeDiff");
                
            
                labelDT.innerText = `${Math.round(filter4RTDT[1])} min`;
                difference = Math.round(filter4RTDT[1] - baselineRTDT[1]);
                sign = difference >= 0 ? "+" : "-";

                // Update text with the correct sign
                labelDTDiff.textContent = difference === 0 ? "-" : `${sign}${Math.abs(difference)}`;

                // Change color based on sign
                labelDTDiff.classList.toggle("is-danger", difference > 0);
                labelDTDiff.classList.toggle("is-ideal", difference < 0);
            }

            reapplyUddfOverlay(); // keep an uploaded real-dive log visible across What If? toggles
            profileChartInstance.update(); // Refresh chart

            document.querySelectorAll(".form-check-input").forEach(cb => {
                    if (cb !== this) {
                        cb.checked = false; // Uncheck all other checkboxes
                    }
            });

            // hide gas consumption if CC
            if(modeOCOrCC === "CC")
                document.getElementById("gasConsumptionRow").style.display="none";
            
        }); 

        document.getElementById("filter5").addEventListener("change", function() {
            
            // we only show the decompression table if the baseline has deco
            if(baselineRTDT[1] > 0) {
                document.getElementById("decoTableContainer").style.display = "block";
                document.getElementById("BOTableContainer").style.display = "none";
                document.getElementById("decoTableTitle").innerText = "Decompression Table";
            }

            if (!this.checked) {
                profileChartInstance.data.datasets = profileChartInstance.data.datasets.filter(dataset => dataset.label === "Deco profile");
                let labelRT = document.getElementById("labelWhatIfRunTime").innerText="-";
                let labelRTDiff = document.getElementById("labelWhatIfRunTimeDiff").innerText="-";
                let labelDT = document.getElementById("labelWhatIfDecoTime").innerText="-";
                let labelDTDiff = document.getElementById("labelWhatIfDecoTimeDiff").innerText="-";
                
            } else {
                profileChartInstance.data.datasets = [
                    {
                        label: 'Reduce max depth 10ft/3m',
                        data: formattedData5,
                        borderColor: '#1b5e20', // Darker green than the fill, so the line reads against it (Pablo, 2026-09-22)
                        backgroundColor: '#4caf50',
                        borderWidth: 2,
                        showLine: true,
                        fill: true,
                        pointRadius: 0,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Deco profile',
                        data: formattedData,
                        borderColor: '#ffffff',
                        backgroundColor: 'rgba(255, 255, 255, 0.5)',
                        borderWidth: 2,
                        showLine: true,
                        fill: true,
                        pointRadius: 0,
                        pointHoverRadius: 6
                    },
                    
                ];

                // update new decoT and newRT
            
                let labelRT = document.getElementById("labelWhatIfRunTime");
                let labelRTDiff = document.getElementById("labelWhatIfRunTimeDiff");
                
                labelRT.innerText = `${Math.round(filter5RTDT[0])} min`;
                var difference = Math.round(filter5RTDT[0] - baselineRTDT[0]);
                var sign = difference >= 0 ? "+" : "-";

                // Update text with the correct sign
                labelRTDiff.textContent = difference === 0 ? "-" : `${sign}${Math.abs(difference)}`;

                // Change color based on sign
                labelRTDiff.classList.toggle("is-danger", difference > 0);
                labelRTDiff.classList.toggle("is-ideal", difference < 0);

                let labelDT = document.getElementById("labelWhatIfDecoTime");
                let labelDTDiff = document.getElementById("labelWhatIfDecoTimeDiff");
                
            
                labelDT.innerText = `${Math.round(filter5RTDT[1])} min`;
                difference = Math.round(filter5RTDT[1] - baselineRTDT[1]);
                sign = difference >= 0 ? "+" : "-";

                // Update text with the correct sign
                labelDTDiff.textContent = difference === 0 ? "-" : `${sign}${Math.abs(difference)}`;

                // Change color based on sign
                labelDTDiff.classList.toggle("is-danger", difference > 0);
                labelDTDiff.classList.toggle("is-ideal", difference < 0);
            }

            reapplyUddfOverlay(); // keep an uploaded real-dive log visible across What If? toggles
            profileChartInstance.update(); // Refresh chart

            document.querySelectorAll(".form-check-input").forEach(cb => {
                    if (cb !== this) {
                        cb.checked = false; // Uncheck all other checkboxes
                    }
            });

            // hide gas consumption if CC
            if(modeOCOrCC === "CC")
                document.getElementById("gasConsumptionRow").style.display="none";

        }); 
    
        document.getElementById("filter6").addEventListener("change", function() {
            // we only show the decompression table if the baseline has deco
            if(baselineRTDT[1] > 0) {
                document.getElementById("decoTableContainer").style.display = "block";
                document.getElementById("BOTableContainer").style.display = "none";
                document.getElementById("decoTableTitle").innerText = "Decompression Table";
            }
            
            if (!this.checked) {
                profileChartInstance.data.datasets = profileChartInstance.data.datasets.filter(dataset => dataset.label === "Deco profile");
                let labelRT = document.getElementById("labelWhatIfRunTime").innerText="-";
                let labelRTDiff = document.getElementById("labelWhatIfRunTimeDiff").innerText="-";
                let labelDT = document.getElementById("labelWhatIfDecoTime").innerText="-";
                let labelDTDiff = document.getElementById("labelWhatIfDecoTimeDiff").innerText="-";

                
                
            } else {
                profileChartInstance.data.datasets = [
                    {
                        label: 'Minimum deco (GFs=100%)',
                        data: formattedData6,
                        borderColor: '#1b5e20', // Darker green than the fill, so the line reads against it (Pablo, 2026-09-22)
                        backgroundColor: '#4caf50',
                        borderWidth: 2,
                        showLine: true,
                        fill: true,
                        pointRadius: 0,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Deco profile',
                        data: formattedData,
                        borderColor: '#ffffff',
                        backgroundColor: 'rgba(255, 255, 255, 0.5)',
                        borderWidth: 2,
                        showLine: true,
                        fill: true,
                        pointRadius: 0,
                        pointHoverRadius: 6
                    },
                    
                ];

                // update new decoT and newRT
            
                let labelRT = document.getElementById("labelWhatIfRunTime");
                let labelRTDiff = document.getElementById("labelWhatIfRunTimeDiff");
                
                labelRT.innerText = `${Math.round(filter6RTDT[0])} min`;
                var difference = Math.round(filter6RTDT[0] - baselineRTDT[0]);
                var sign = difference >= 0 ? "+" : "-";

                // Update text with the correct sign
                labelRTDiff.textContent = difference === 0 ? "-" : `${sign}${Math.abs(difference)}`;

                // Change color based on sign
                labelRTDiff.classList.toggle("is-danger", difference > 0);
                labelRTDiff.classList.toggle("is-ideal", difference < 0);

                let labelDT = document.getElementById("labelWhatIfDecoTime");
                let labelDTDiff = document.getElementById("labelWhatIfDecoTimeDiff");
                
            
                labelDT.innerText = `${Math.round(filter6RTDT[1])} min`;
                difference = Math.round(filter6RTDT[1] - baselineRTDT[1]);
                sign = difference >= 0 ? "+" : "-";

                // Update text with the correct sign
                labelDTDiff.textContent = difference === 0 ? "-" : `${sign}${Math.abs(difference)}`;

                // Change color based on sign
                labelDTDiff.classList.toggle("is-danger", difference > 0);
                labelDTDiff.classList.toggle("is-ideal", difference < 0);
            }

            reapplyUddfOverlay(); // keep an uploaded real-dive log visible across What If? toggles
            profileChartInstance.update(); // Refresh chart

            document.querySelectorAll(".form-check-input").forEach(cb => {
                    if (cb !== this) {
                        cb.checked = false; // Uncheck all other checkboxes
                    }
            });

            // hide gas consumption if CC
            if(modeOCOrCC === "CC")
                document.getElementById("gasConsumptionRow").style.display="none";
            
        });

        document.getElementById("filter7").addEventListener("change", function() {
            let decoTableContainer = document.getElementById("decoTableContainer");
            let BOTableContainer = document.getElementById("BOTableContainer");
            let decoTableTitle = document.getElementById("decoTableTitle");

            if (!this.checked) {
                profileChartInstance.data.datasets = profileChartInstance.data.datasets.filter(dataset => dataset.label === "Deco profile");
                // Back to CC's baseline - no gas-switch markers (the CC
                // diluent itself never changes mid-dive), but keep the
                // critical point marker (Pablo, 2026-09-25: "make it part
                // of the baseline group").
                profileChartInstance.options.plugins.annotation.annotations = buildCriticalPointAnnotation(window.lastCriticalPoint, modeImpOrMetric == "met" ? 10 : 33);
                let labelRT = document.getElementById("labelWhatIfRunTime").innerText="-";
                let labelRTDiff = document.getElementById("labelWhatIfRunTimeDiff").innerText="-";
                let labelDT = document.getElementById("labelWhatIfDecoTime").innerText="-";
                let labelDTDiff = document.getElementById("labelWhatIfDecoTimeDiff").innerText="-";

                // hide BO table and show regular table
                decoTableContainer.style.display = "block";
                BOTableContainer.style.display = "none";
                decoTableTitle.innerText = "Decompression Table";

                // hide gas Consumption// hide gas consumption if CC
                document.getElementById("gasConsumptionRow").style.display="none";


            } else {
                profileChartInstance.data.datasets = [
                    {
                        label: 'Deco profile',
                        data: formattedData,
                        borderColor: '#ffffff',
                        backgroundColor: 'rgba(255, 255, 255, 0.5)',
                        borderWidth: 2,
                        showLine: true,
                        fill: true,
                        pointRadius: 0,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Bailout to OC',
                        data: formattedData7,
                        borderColor: '#8c1c13', // Darker red than the fill, so the line reads against it (Pablo, 2026-09-22)
                        backgroundColor: '#f44335',
                        borderWidth: 2,
                        showLine: true,
                        fill: true,
                        pointRadius: 0,
                        pointHoverRadius: 6
                    }
                ];
                // Once bailout kicks in the diver is effectively running an
                // OC profile - badge its real gas switches the same way an
                // OC dive's chart would (Pablo, 2026-09-17: "in CC when the
                // what if case is 'Bail out to OC' you need to show the
                // switching markers as in OC in the chart").
                var unitConversion = modeImpOrMetric == "met" ? 10 : 33;
                // "Force the first switch to max depth" is single-level's
                // own "you bail right at the bottom" convention - there's
                // one well-defined bottom to snap to. Multi-level has no
                // single bottom (several levels, possibly at different
                // depths), and forcing it there was landing the marker at
                // Level 1's position instead of the real switch point,
                // right after leaving the LAST level (Pablo, 2026-09-25:
                // "the first gas switch is incorrectly marked...it should
                // happen at the end of the last level") - the row's own
                // real time/depth already IS that point for multi-level,
                // so skip the forcing there.
                var dhForceFirstSwitchToBottom = modeLevelSingleOrMulti !== 'multi';
                // CC bailout only - restrict switch markers to real
                // 'gas_switch'-tagged rows, ignoring the loop's own
                // continuous setpoint-driven mix drift during plain ascent/
                // const legs (Pablo, 2026-09-25: "before the switch to OC
                // we only should have CC").
                profileChartInstance.options.plugins.annotation.annotations = buildGasSwitchAnnotations(globalResponse['bailout'], unitConversion, dhForceFirstSwitchToBottom, true, 'bailout');
                // Multi-level's critical point usually sits right on top of
                // the bailout's own first gas-switch marker (both mark the
                // same "moment of maximum decompression obligation" this
                // dive bails from) - showing both stacks two markers and
                // hides the switch marker itself (Pablo, 2026-09-26: "hide
                // the critical point marker...it's usually at a gas switch
                // to OC and its preventing seeing on the chart the switch").
                // Single-level's own bailout view keeps it, per "show it
                // back in all other cases".
                if (modeLevelSingleOrMulti !== 'multi') {
                    Object.assign(profileChartInstance.options.plugins.annotation.annotations, buildCriticalPointAnnotation(window.lastCriticalPoint, unitConversion));
                }

                // show BO table and change table title
                decoTableContainer.style.display = "none";
                BOTableContainer.style.display = "block";
                decoTableTitle.innerText = "Bailout to OC";

                // update new decoT and newRT
            
                let labelRT = document.getElementById("labelWhatIfRunTime");
                let labelRTDiff = document.getElementById("labelWhatIfRunTimeDiff");
                
                labelRT.innerText = `${Math.round(filter7RTDT[0])} min`;
                var difference = Math.round(filter7RTDT[0] - baselineRTDT[0]);
                var sign = difference >= 0 ? "+" : "-";

                // Update text with the correct sign
                labelRTDiff.textContent = difference === 0 ? "-" : `${sign}${Math.abs(difference)}`;

                // Change color based on sign
                labelRTDiff.classList.toggle("is-danger", difference > 0);
                labelRTDiff.classList.toggle("is-ideal", difference < 0);

                let labelDT = document.getElementById("labelWhatIfDecoTime");
                let labelDTDiff = document.getElementById("labelWhatIfDecoTimeDiff");
                
            
                labelDT.innerText = `${Math.round(filter7RTDT[1])} min`;
                difference = Math.round(filter7RTDT[1] - baselineRTDT[1]);
                sign = difference >= 0 ? "+" : "-";

                // Update text with the correct sign
                labelDTDiff.textContent = difference === 0 ? "-" : `${sign}${Math.abs(difference)}`;

                // Change color based on sign
                labelDTDiff.classList.toggle("is-danger", difference > 0);
                labelDTDiff.classList.toggle("is-ideal", difference < 0);
            }

            reapplyUddfOverlay(); // keep an uploaded real-dive log visible across What If? toggles
            profileChartInstance.update(); // Refresh chart

            // update gas consumption
            document.getElementById("gasConsumptionRow").style.display="block";
            gasConsumption = calculateGasConsumption(window.dhScenarios.bailout);
            // no need to render bottom gas (it's on CC)
            renderGasConsumptionTable(gasConsumption, "deco", window.dhScenarios.bailout);
            


            document.querySelectorAll(".form-check-input").forEach(cb => {
                    if (cb !== this) {
                        cb.checked = false; // Uncheck all other checkboxes
                    }
            });

            
        });
    </script>

    {{-- Site selector dropdown scripts --}}
    <script>
        document.getElementById("dropdownSearch").addEventListener("keyup", function() {
            filterDropdown();
        });
        function filterDropdown() {
            console.log("I'm here!");
            let input = document.getElementById("dropdownSearch").value.toLowerCase();
            let items = document.querySelectorAll(".dropdown-item");

            items.forEach(item => {
                if (item.textContent.toLowerCase().includes(input)) {
                    item.style.display = "block";
                } else {
                    item.style.display = "none";
                }
            });
        }

        document.querySelectorAll(".dropdown-item").forEach(item => {
            item.addEventListener("click", function() {
                let selectedDepth = this.getAttribute("data-depth");
                let siteName = this.getAttribute("data-site-name");
                let levelIcon = this.getAttribute("data-level-icon");
                let levelName = this.getAttribute("data-level-name");
                let depthSlider = document.getElementById("depthSlider");

                if (depthSlider && selectedDepth) {
                    // Picking a site sets the depth FOR the diver, so the
                    // button keeps showing which site that came from; typing
                    // a depth by hand means "not from this site anymore"
                    // (Pablo, 2026-09-16). isSettingDepthFromSite tells the
                    // depth slider's own update handler not to reset the
                    // button for the very set() call this click just
                    // triggered.
                    isSettingDepthFromSite = true;
                    dhSetSiteSearchButton(siteName, levelIcon, levelName);
                    depthSlider.noUiSlider.set(selectedDepth); // Updates slider value
                    // Multi-level: Level 1 is the dive's starting depth, so a
                    // site pick sets it the same way it sets the single-level
                    // slider (Pablo, 2026-09-25: "we will set the depth for
                    // the first level at the max depth for the site chosen").
                    if (dhLevelDepthSliders && dhLevelDepthSliders[1]) {
                        dhLevelDepthSliders[1].noUiSlider.set(selectedDepth);
                    }
                    isSettingDepthFromSite = false;
                }
            });
        });

    </script>



    {{-- Script to switch from OC to CC --}}
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            openCircuitTab = document.querySelector('#nav-tabs [data-tag="OC"]');
            closedCircuitTab = document.querySelector('#nav-tabs [data-tag="CC"]');
            const tankDouble = document.getElementById("tank_double");
            const tankCCR = document.getElementById("tank_ccr");
            const labelBottomGasOrDiluent = document.getElementById("labelBottomGasOrDiluent");
            const containerSetPoint = document.getElementById("containerSetPoint");

            const labelMaxDepthPPO2Description = document.getElementById("labelMaxDepthPPO2Description");
            const labelENDDescription = document.getElementById("labelENDDescription");
            const labelGasDensityDescription = document.getElementById("labelGasDensityDescription");

            const containerDeco1OCInfo = document.getElementById("containerDeco1OCInfo");
            const containerDeco1CCInfo = document.getElementById("containerDeco1CCInfo");

                
            

            function showOpenCircuit() {
                tankDouble.style.display = "block";
                tankCCR.style.display = "none";
                labelBottomGasOrDiluent.innerText = "bottom gas";
                containerSetPoint.style.display="none";

                // set model to mode OC
                modeOCOrCC = "OC";
                // OC has no CCR setpoint, so the save pill only mentions GFs.
                // Guests don't get this pill at all (Pablo, 2026-09-25), hence the null check.
                if (document.getElementById('saveDecoPrefsPillLabel')) document.getElementById('saveDecoPrefsPillLabel').textContent = 'Save GFs';
                dhResetGasSlots();
                // tank_double.png's transparent window starts at y=58/300 of
                // the source image - scaled to this 156px canvas that's ~28px
                // down from the top (Pablo, 2026-09-17: mark the mask's own
                // northmost transparent point as the fill limit).
                bottomGasstackedBarChart.options.layout.padding.bottom = 3;
                bottomGasstackedBarChart.options.layout.padding.top = 28;
                labelHorizontalOffset = -28;
                // Refresh chart to apply changes
                setPointOCOrCC = 1.4;
                bottomGasO2Slider.noUiSlider.set(setPointOCOrCC * 100 / (depth / 33 + 1));
                bottomGasstackedBarChart.update();

                //update label descriptors
                labelMaxDepthPPO2Description.textContent = "Max depth PPO₂";
                labelENDDescription.textContent = "Equivalent Narcotic Depth";
                labelGasDensityDescription.textContent = "Gas density";

                bottomGasO2Slider.noUiSlider.updateOptions({
                    range: {
                        'min': 5,    // Keep the minimum value as is
                        'max': 95
                    }
                });

                //update deco gases title
                document.getElementById("titleDecoGases").innerText="decompression gases";

                // hide CC Bailout and show elements from OC
                containerDeco1OCInfo.style.display = "block";
                containerDeco1CCInfo.style.display = "none";
                // Diluent/Bailout presets don't apply to OC - swap slot 1
                // back to the Deco 1 preset menu (Pablo, 2026-09-17); Bottom
                // gas's own My-Gases-only row (guests never get one of
                // these, so it may not exist) comes back too.
                document.getElementById("gasPresetDiluent").hidden = true;
                document.getElementById("gasPreset1").hidden = false;
                document.getElementById("gasPresetBailout").hidden = true;
                var gasPresetBottomOC = document.getElementById("gasPresetBottomOC");
                if (gasPresetBottomOC) gasPresetBottomOC.hidden = false;
                hideDecoGas1();
                depthSlider.noUiSlider.set(depthSlider.noUiSlider.get()); //refresh slider to have the deco sliders updated


            }

            function showClosedCircuit() {
                tankDouble.style.display = "none";
                tankCCR.style.display = "block";
                labelBottomGasOrDiluent.innerText = "diluent";
                containerSetPoint.style.display="block";

                // set model to mode OC
                modeOCOrCC = "CC";
                // CC has a setpoint, so the save pill covers both. Guests don't
                // get this pill at all (Pablo, 2026-09-25), hence the null check.
                if (document.getElementById('saveDecoPrefsPillLabel')) document.getElementById('saveDecoPrefsPillLabel').textContent = 'Save GFs & Setpoint';
                dhResetGasSlots();
                // tank_ccr.png's window starts higher in the tank body than
                // tank_double's - y=34/300 of the source image, ~16px down
                // from the top of this 156px canvas - so CC needs its own,
                // smaller top padding to reach that mask's northmost
                // transparent point (Pablo, 2026-09-17).
                bottomGasstackedBarChart.options.layout.padding.bottom = 21;
                bottomGasstackedBarChart.options.layout.padding.top = 16;
                labelHorizontalOffset = -2;
                // Refresh chart to apply changes
                setPointOCOrCC = 1.2;
                bottomGasO2Slider.noUiSlider.set(setPointOCOrCC * 100 / (depth / 33 + 1));
                bottomGasstackedBarChart.update();

                //update label descriptors
                labelMaxDepthPPO2Description.textContent = "Dil Max depth PPO₂";
                labelENDDescription.textContent = "Loop END";
                labelGasDensityDescription.textContent = "Loop Gas density";

                //refresh slider
                setpointSlider.noUiSlider.set(setpointSlider.noUiSlider.get());

                //update deco gases title
                document.getElementById("titleDecoGases").innerText="Bailout and decompression gases";

                // hide OC Deco1 and elements from CC
                containerDeco1OCInfo.style.display = "none";
                containerDeco1CCInfo.style.display = "block";
                // Diluent gets its own preset row, and slot 1 swaps to the
                // Bailout preset menu instead of Deco 1's (Pablo, 2026-09-17).
                document.getElementById("gasPresetDiluent").hidden = false;
                document.getElementById("gasPreset1").hidden = true;
                document.getElementById("gasPresetBailout").hidden = false;
                var gasPresetBottomOCEl = document.getElementById("gasPresetBottomOC");
                if (gasPresetBottomOCEl) gasPresetBottomOCEl.hidden = true;
                // Reveal Bailout's card (it's mandatory in CC) without forcing
                // it open - every gas card starts collapsed (Pablo, 2026-09-16).
                document.getElementById("gasAccordionItemDeco1").hidden = false;
                dhUpdateAddGasButtonVisibility();
                dhReorderGasAccordion();
                resetCalculationArea();
                depthSlider.noUiSlider.set(depthSlider.noUiSlider.get()); //refresh slider to have the deco sliders updated
            }

            openCircuitTab.addEventListener("click", function () {
                showOpenCircuit();
            });

            closedCircuitTab.addEventListener("click", function () {
                showClosedCircuit();
            });

            // Initialize with Open Circuit visible
            showOpenCircuit();
        });
    </script>

    {{-- Single Level / Multi Level toggle, independent of OC/CC above.
         Same is-active pill pattern; showSingleLevel()/showMultiLevel()
         just swap which depth-input block is visible - front-end only,
         Calculate still always runs the single-level flow for now (Pablo,
         2026-09-25). --}}
    <script>
        let modeLevelSingleOrMulti = "single";

        function showSingleLevel() {
            modeLevelSingleOrMulti = "single";
            document.getElementById('singleLevelDepthRow').style.display = '';
            document.getElementById('multiLevelRow').style.display = 'none';
            document.getElementById('singleLevelTab').classList.add('is-active');
            document.getElementById('multiLevelTab').classList.remove('is-active');
            // Whichever depth is now "in force" just changed (was the
            // deepest level, now the single-level field) - resync the
            // ideal bottom gas/diluent to match (Pablo, 2026-09-25).
            dhSyncIdealBottomGas();
            if (typeof dhSyncBailoutSwitchInfo === 'function') dhSyncBailoutSwitchInfo();
            // The overall Bottom time/Surface time column comes back, and
            // GF/rate return to their original col-lg-4 width (Pablo,
            // 2026-09-25).
            document.getElementById('singleLevelTimingCol').style.display = '';
            document.getElementById('gfCol').classList.remove('col-lg-6');
            document.getElementById('gfCol').classList.add('col-lg-4');
            document.getElementById('rateCol').classList.remove('col-lg-6');
            document.getElementById('rateCol').classList.add('col-lg-4');
        }

        function showMultiLevel() {
            modeLevelSingleOrMulti = "multi";
            document.getElementById('singleLevelDepthRow').style.display = 'none';
            document.getElementById('multiLevelRow').style.display = '';
            document.getElementById('multiLevelTab').classList.add('is-active');
            document.getElementById('singleLevelTab').classList.remove('is-active');
            dhSyncIdealBottomGas();
            if (typeof dhSyncBailoutSwitchInfo === 'function') dhSyncBailoutSwitchInfo();
            // No What if? in multi-level yet (Pablo, 2026-09-25).
            var fab = document.getElementById('dhWhatIfFab');
            if (fab) fab.hidden = true;
            // The overall Bottom time/Surface time column doesn't apply
            // once every level has its own bottom time (Pablo, 2026-09-25:
            // "the bottom time slider at the top does not make sense
            // because each level will have its own bottom time. The
            // surface time slider is not needed either") - hide it and
            // let GF/rate split the freed width evenly.
            document.getElementById('singleLevelTimingCol').style.display = 'none';
            document.getElementById('gfCol').classList.remove('col-lg-4');
            document.getElementById('gfCol').classList.add('col-lg-6');
            document.getElementById('rateCol').classList.remove('col-lg-4');
            document.getElementById('rateCol').classList.add('col-lg-6');
        }

        document.getElementById('singleLevelTab').addEventListener('click', showSingleLevel);
        document.getElementById('multiLevelTab').addEventListener('click', function (e) {
            @if(auth()->user()->isGuest())
                e.preventDefault();
                if (typeof showModalGuest === 'function') showModalGuest();
                return;
            @endif
            showMultiLevel();
        });

        // On-demand level panels 2-4, same show/hide-a-pre-rendered-slot
        // pattern as the gas accordion's Add gas (dhNextGasSlot/
        // showDecoGasN/dhUpdateAddGasButtonVisibility) - Level 1 is
        // always present and has no delete button.
        function dhNextLevelSlot() {
            for (var i = 2; i <= 4; i++) {
                var panel = document.getElementById('levelPanel' + i);
                if (panel && panel.hidden) return i;
            }
            return null;
        }

        function dhUpdateAddLevelButtonVisibility() {
            var btn = document.getElementById('levelBtnAdd');
            if (btn) btn.style.display = (dhNextLevelSlot() === null) ? 'none' : '';
        }

        function showLevel2() { document.getElementById('levelPanel2').hidden = false; dhUpdateAddLevelButtonVisibility(); }
        function showLevel3() { document.getElementById('levelPanel3').hidden = false; dhUpdateAddLevelButtonVisibility(); }
        function showLevel4() { document.getElementById('levelPanel4').hidden = false; dhUpdateAddLevelButtonVisibility(); }

        /**
         * Deleting level N shifts every level AFTER it up by one slot,
         * then drops the now-duplicate last slot - so deleting level 2 of
         * a 3-level dive leaves 1, 2 (old level 3's numbers), not 1, [gap], 3
         * (Pablo, 2026-09-25: "if I have 3 levels and delete #2, then the
         * third now becomes #2"). Slot POSITIONS (and their headers,
         * "Level 2" etc.) are fixed; only the depth/bottom-time VALUES
         * move between slots via the sliders' own get/set, which also
         * refreshes each slot's ft/m text inputs through its existing
         * 'update' handler - no separate label-syncing needed here.
         */
        function dhDeleteLevel(n) {
            var visibleCount = 1;
            for (var i = 2; i <= 4; i++) {
                if (!document.getElementById('levelPanel' + i).hidden) visibleCount = i;
            }
            for (var i = n; i < visibleCount; i++) {
                dhLevelDepthSliders[i].noUiSlider.set(dhLevelDepthSliders[i + 1].noUiSlider.get());
                dhLevelTimeSliders[i].noUiSlider.set(dhLevelTimeSliders[i + 1].noUiSlider.get());
            }
            document.getElementById('levelPanel' + visibleCount).hidden = true;
            dhUpdateAddLevelButtonVisibility();
        }
        function hideLevel2() { dhDeleteLevel(2); }
        function hideLevel3() { dhDeleteLevel(3); }
        function hideLevel4() { dhDeleteLevel(4); }

        document.getElementById('levelBtnAdd').addEventListener('click', function () {
            var n = dhNextLevelSlot();
            if (n === null) return;
            window['showLevel' + n]();
        });
    </script>

    {{--  Script to change the color of the sidemenu to theme --}}
    <script>

    </script>

    {{-- Scripts to create Tissue chart --}}
    <script>
       
        const ctx = document.getElementById("tissueChart").getContext("2d");
        let tissueChartInstance = null;

        const data = {
            labels: ["Tissue 1", "Tissue 2", "Tissue 3", "Tissue 4", "Tissue 5", "Tissue 6", "Tissue 7", "Tissue 8",
                    "Tissue 9", "Tissue 10", "Tissue 11", "Tissue 12", "Tissue 13", "Tissue 14", "Tissue 15", "Tissue 16"
            ],
            datasets: [{
                label: "Values",
                data: [10, 25, 40, 30, 10, 25, 40, 30, 10, 25, 40, 30, 10, 25, 40, 30],
                backgroundColor: "black",
                barThickness: 3
            }]
        };

        const config = {
            type: "bar",
            data: data,
            options: {
                indexAxis: "y",
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { display: false, min: 0, max:300 },
                    y: { grid: { display: false }, ticks: { color: "black" , display: false} }
                },
                plugins: {
                    legend: { display: false },
                    annotation: {
                        // Matches the reference tissue bar graph (Peregrine
                        // dive computer manual, Pablo, 2026-09-25): each
                        // bar's own scale is already normalized so 100 is
                        // always "at ambient pressure" and 295 is always
                        // "at this compartment's own M-value ceiling". The
                        // Ambient boundary itself is drawn by the chart's
                        // own green/yellow CSS background split (see
                        // #tissueChart above, aligned to the exact same
                        // 100/300 position) rather than a separate dashed
                        // line annotation on top of it (Pablo, 2026-09-25:
                        // "extend the green/yellow boundary to that exact
                        // position and remove the dotted line"). The
                        // Inspired Inert Gas line sits to its left by the
                        // gas's own inert-gas fraction and is depth-
                        // independent BY DESIGN for a fixed gas (ambient
                        // pressure cancels out of the ratio) - it only
                        // moves at a real gas switch, same as the
                        // reference device.
                        annotations: {
                            inspiredPressureInertGas: {
                                type: "line",
                                xMin: 79,
                                xMax: 79,
                                borderColor: "black",
                                borderWidth: 2,
                                label: {
                                    enabled: true,
                                    content: "Inspired Inert Gas 79%",
                                    position: "top",
                                    font: { size: 10 }
                                }
                            }
                        }
                    }
                }
            }
        };

        tissueChartInstance = new Chart(ctx, config);
    </script>

    {{-- Script to manage Tissue chart --}}
    <script>

        function formatTime(minutes) {
            let totalSeconds = Math.round(minutes * 60);
            let mins = Math.floor(totalSeconds / 60);
            let secs = totalSeconds % 60;
            return `${mins}:${secs.toString().padStart(2, '0')}`; // Ensures two-digit seconds
        }
        // Exact ZH-L16C-GF coefficients transcribed from the DecoPlanning
        // backend's own engine (decotengu==0.14.1, decotengu/model.py) -
        // Pablo, 2026-09-25, via https://claude.ai/artifact/WPWQLpPhiKPySYdWAmHhgL.
        // Two earlier attempts here (a made-up dM table, then a "textbook"
        // ZHL-16C table) both let a compartment's tissue tension read as
        // exceeding the yellow/red boundary on a dive (Cayman Wall) whose
        // actual decompression the backend computed as safe - this is the
        // real table, not a re-derivation, and includes helium (He A/B)
        // for trimix/CC dives, not just nitrogen.
        const MValues = [
            { a_n2: 1.2599, b_n2: 0.5050, a_he: 1.7424, b_he: 0.4245 },
            { a_n2: 1.0000, b_n2: 0.6514, a_he: 1.3830, b_he: 0.5747 },
            { a_n2: 0.8618, b_n2: 0.7222, a_he: 1.1919, b_he: 0.6527 },
            { a_n2: 0.7562, b_n2: 0.7825, a_he: 1.0458, b_he: 0.7223 },
            { a_n2: 0.6200, b_n2: 0.8126, a_he: 0.9220, b_he: 0.7582 },
            { a_n2: 0.5043, b_n2: 0.8434, a_he: 0.8205, b_he: 0.7957 },
            { a_n2: 0.4410, b_n2: 0.8693, a_he: 0.7305, b_he: 0.8279 },
            { a_n2: 0.4000, b_n2: 0.8910, a_he: 0.6502, b_he: 0.8553 },
            { a_n2: 0.3750, b_n2: 0.9092, a_he: 0.5950, b_he: 0.8757 },
            { a_n2: 0.3500, b_n2: 0.9222, a_he: 0.5545, b_he: 0.8903 },
            { a_n2: 0.3295, b_n2: 0.9319, a_he: 0.5333, b_he: 0.8997 },
            { a_n2: 0.3065, b_n2: 0.9403, a_he: 0.5189, b_he: 0.9073 },
            { a_n2: 0.2835, b_n2: 0.9477, a_he: 0.5181, b_he: 0.9122 },
            { a_n2: 0.2610, b_n2: 0.9544, a_he: 0.5176, b_he: 0.9171 },
            { a_n2: 0.2480, b_n2: 0.9602, a_he: 0.5172, b_he: 0.9217 },
            { a_n2: 0.2327, b_n2: 0.9653, a_he: 0.5119, b_he: 0.9267 }
        ];

        /**
         * Calculate the maximum tolerable inert gas pressure for a given
         * compartment, weighting the N2/He coefficients by this
         * compartment's own current N2/He tissue split - matching the
         * backend's own ceiling formula (a = (a_n2*p_n2 + a_he*p_he)/p,
         * same for b), which reduces to plain N2 coefficients when p_he=0.
         * NOTE: this engine's M-value line is M(P) = a + P/b (divide by b),
         * not the a + b*P form printed in most textbook Buhlmann summaries.
         * @param {number} compartment - Compartment number (1-16)
         * @param {number} ambientPressure - Absolute ambient pressure (ATA)
         * @param {number} pN2 - This compartment's current N2 tissue tension (ATA)
         * @param {number} pHe - This compartment's current He tissue tension (ATA)
         * @returns {number} - Maximum allowable inert gas partial pressure (ATA)
         */
        function calculateMValue(compartment, ambientPressure, pN2, pHe) {
            if (compartment < 1 || compartment > 16) {
                throw new Error("Invalid compartment number. Must be between 1 and 16.");
            }

            const c = MValues[compartment - 1];
            const pTotal = pN2 + pHe;
            const a = pTotal > 0 ? (c.a_n2 * pN2 + c.a_he * pHe) / pTotal : c.a_n2;
            const b = pTotal > 0 ? (c.b_n2 * pN2 + c.b_he * pHe) / pTotal : c.b_n2;
            return a + (ambientPressure / b);
        }


        var timeLapseSlider = document.getElementById('timeLapseSlider');
        

        noUiSlider.create(timeLapseSlider, {
            start: 0,
            connect: [true, false],
            range: {
                'min': 0,
                'max': 100
            },
            step: 1,
            

        });

        // Hide the tick mark labels
        var timeLapseSliderTicks = timeLapseSlider.querySelectorAll('.noUi-value-sub');
        timeLapseSliderTicks.forEach(function (timeLapseSlider) {
            timeLapseSlider.style.display = 'none';
        });

        
    
        var animationActive = false;
        var interval; // Store interval reference
        var currentValue; // Store slider position

        function toggleSliderAnimation() {
            const button = document.getElementById("playTissueAnimation");
            const min = timeLapseSlider.noUiSlider.options.range.min;
            const max = timeLapseSlider.noUiSlider.options.range.max;
            const duration = 20000; // 20 seconds
            const steps = duration / 100; // Move every 100ms
            const stepValue = (max - min) / steps;

            if (animationActive) {
                clearInterval(interval); // Stop animation
                animationActive = false;
                button.textContent = "Start"; // Update button text
            } else {
                if (currentValue === undefined || currentValue >= max) {
                    currentValue = min; // Reset to min if animation ended
                }

                interval = setInterval(() => {
                    currentValue += stepValue;
                    timeLapseSlider.noUiSlider.set(currentValue);

                    if (currentValue >= max) {
                        clearInterval(interval); // Stop at max value
                        animationActive = false;
                        button.textContent = "Start";
                    }
                }, 100);

                animationActive = true;
                button.textContent = "Stop";
            }
        }

        timeLapseSlider.noUiSlider.on('update', function (values, handle) {
            let index = parseInt(values[handle]);

            let tissueChart = document.getElementById("tissueChart");
            //console.log("Conveyor");
            //console.log(conveyor[index].tissue_p);
            //console.log("index = " +(index) + " Abs_P = " + (conveyor[index].abs_p) + " time = " + conveyor[index].time + " N2 = " + conveyor[index].gas[2]);

            // The API's own tissue_p is alveolar (inspired), not ambient -
            // it already subtracts the standard respiratory water vapor
            // pressure (~47mmHg/0.0627 bar at body temperature) before
            // multiplying by gas fraction, the same way every Buhlmann-
            // style computer does. Confirmed empirically: at t=0 (surface,
            // fully air-equilibrated) tissue_p reads ~0.751, not 0.79*1.01325
            // (~0.80) - it matches 0.79*(1.01325-0.0627) almost exactly
            // (Pablo, 2026-09-25: "at t=0 the PPN2 should be 0.79 matching
            // right on the bar"). The green/yellow (undersaturated) branch
            // needs the same correction in its own denominator so "100" on
            // the bar lines up with the Inspired N2 line's own 79% at the
            // surface, instead of reading low. The M-value (yellow/red)
            // branch is untouched - Buhlmann M-values are defined against
            // raw ambient pressure, not alveolar pressure.
            const WATER_VAPOR_PRESSURE_BAR = 0.0627;
            let effAmbient = conveyor[index].abs_p - WATER_VAPOR_PRESSURE_BAR;
            let tissueValues = [];
            for(i=0; i<16; i++) {
                // tissue_p[i] is [N2 tension, He tension] (ATA) - the
                // backend's own ceiling formula controls on their SUM, not
                // N2 alone, weighting the M-value coefficients by this
                // compartment's N2/He split (Pablo, 2026-09-25, per
                // https://claude.ai/artifact/WPWQLpPhiKPySYdWAmHhgL's own
                // "quick sanity checks": "Is tissue_p...read as the summed
                // (p_n2+p_he)...matching what the ceiling formula's p
                // represents?"). Using N2 alone made a trimix/CC compartment
                // read as under its limit when it wasn't, or vice versa.
                let pN2 = conveyor[index].tissue_p[i][0];
                let pHe = conveyor[index].tissue_p[i][1];
                let pTotal = pN2 + pHe;
                if(pTotal <= effAmbient) {
                    tissueValues[i] = pTotal / effAmbient * 100;
                } else {
                    // Position within the yellow zone as "% of the way from
                    // ambient to this compartment's own M-value" rather than
                    // a raw tissue_p/M_value ratio - continuous with the
                    // green formula above (both give exactly 100 right at
                    // the ambient crossover, so a bar's length never jumps
                    // as it crosses from green into yellow), and still
                    // reaches exactly 295 at the M-value/red boundary
                    // (Pablo, 2026-09-25: smooth-crossover option).
                    let mval = calculateMValue(i + 1, conveyor[index].abs_p, pN2, pHe);
                    tissueValues[i] = 100 + (pTotal - effAmbient) / (mval - effAmbient) * 195;
                }
            }
            //console.log(tissueValues); // Check the extracted values

            tissueChartInstance.data.datasets[0].data = tissueValues;

            // This marks where a tissue exactly in equilibrium with the
            // CURRENTLY INSPIRED gas would sit on the bars' own %-of-
            // current-ambient scale - bars to its right are on-gassing
            // relative to what's being breathed right now, bars to its
            // left are off-gassing. That threshold is the gas's own N2
            // fraction and is depth-independent for a fixed gas (ambient
            // pressure cancels out of both sides of the comparison) - it
            // only moves at a real gas switch (Pablo, 2026-09-25: "In OC
            // it's easy because there is no change in the PPN2 unless
            // there is a gas switch...at the start of the dive PPN2 is
            // 0.79...at 79% of the green area").
            //
            // CC is different: PPO2 is held at the setpoint rather than a
            // fixed gas fraction, so the loop's O2% (gas[1]) constantly
            // shifts with depth to maintain it - using gas[1] directly (as
            // OC does) would fold that shifting O2% into the N2 estimate
            // and drift the line for no physiological reason. Pablo:
            // "the PPO2 is fixed (at the setpoint), so we need to
            // calculate the PPN2 (basically Ambient pressure - PPO2 -
            // PPHe)". PPHe still comes from the loop's own He fraction
            // (gas[3]) - diluent He isn't actively regulated, so its
            // absolute partial pressure stays constant through a normal
            // ascent and its reported fraction already reflects that.
            // Bars now track TOTAL inert gas (N2+He, see above) rather
            // than nitrogen alone, so this line needs to match: whatever
            // isn't O2 is inert gas, so it's just "100 - O2%" - no need to
            // separately subtract He% anymore, it's already included.
            var inspiredInertPct;
            if (index === 0 || index === conveyor.length - 1) {
                // Every dive starts AND ends on the surface breathing
                // ordinary air - the gas mix in the tank/loop is only
                // relevant while actually submerged and breathing it
                // (Pablo, 2026-09-25: "at the start of the dive, make sure
                // the black vertical line is exactly at 79%...", then
                // later: "at the VERY end of the dive, the black vertical
                // line needs to come back to 0.79 (the diver is back on
                // the surface)").
                inspiredInertPct = 79;
            } else if (modeOCOrCC === 'CC') {
                var ccSetpoint = (window.lastDiveProfile && window.lastDiveProfile.setpoint) || parseFloat(labelSetpoint.value);
                inspiredInertPct = 100 - (ccSetpoint / conveyor[index].abs_p * 100);
                // At/near the surface the loop physically can't hit the
                // setpoint (would need >100% O2) and the very first/last
                // samples are really pre-/post-dive placeholders, not the
                // loop actually running - clamp to the chart's own [0,100]
                // green band rather than let those instants push the line
                // to a nonsensical negative position.
                inspiredInertPct = Math.max(0, Math.min(100, inspiredInertPct));
            } else {
                inspiredInertPct = 100 - conveyor[index].gas[1];
            }
            tissueChartInstance.options.plugins.annotation.annotations = {
                inspiredPressureInertGas: {
                    type: "line",
                    xMin: inspiredInertPct,
                    xMax: inspiredInertPct,
                    borderColor: "black",
                    borderWidth: 2,
                    label: {
                        enabled: true,
                        content: "Inspired Inert Gas " + Math.round(inspiredInertPct) + "%",
                        position: "top",
                        font: { size: 10 }
                    }
                }
            };

            tissueChartInstance.update();
            
            let unitConversion = 33;
            if(modeImpOrMetric == "met")
                unitConversion = 10;

            document.getElementById("labelTissueChartTime").textContent = formatTime(conveyor[index].time);
            document.getElementById("labelTissueChartDepth").textContent = ((conveyor[index].abs_p -1 ) * unitConversion).toFixed(0);
            
            // update global var for the animation
            currentValue = parseFloat(values[handle]); // Update current position

        });
    </script>

    {{--  Completly useless scripts to avoid the labels in the bottom gas to change the size of the frame :) --}}
    <script>
        function updateLabel() {
            const label = document.getElementById("labelENDDescription");
            let originalText = "Loop END";
            if(modeOCOrCC == "OC")
                originalText = "Equivalent Narcotic Depth";
            
                
            
            label.textContent = originalText; // Set original text first

            // Create a temporary measurement span inside the label
            const tempSpan = document.createElement("span");
            tempSpan.style.visibility = "hidden";
            tempSpan.style.whiteSpace = "nowrap"; // Prevent wrapping
            tempSpan.style.position = "absolute";
            tempSpan.textContent = originalText;
            label.appendChild(tempSpan);

            // Measure the actual rendered text width
            const textWidth = tempSpan.offsetWidth;
            const containerWidth = label.offsetWidth;

            label.removeChild(tempSpan); // Remove temporary span

            // Replace text only if wrapping is required
            if (textWidth > containerWidth) {
                if(modeOCOrCC == "OC")
                    label.textContent = "END";
            }
        }

        // Run when the page loads & when resizing
        window.addEventListener("load", updateLabel);
        window.addEventListener("resize", updateLabel);

        function updateLabel1() {
            const label = document.getElementById("labelMaxDepthPPO2Description");
            let originalText = "Dil Max Depth PPO₂";
            if(modeOCOrCC == "OC")
                originalText = "Max Depth PPO₂";
            
            label.textContent = originalText; // Set original text first

            // Create a temporary measurement span inside the label
            const tempSpan = document.createElement("span");
            tempSpan.style.visibility = "hidden";
            tempSpan.style.whiteSpace = "nowrap"; // Prevent wrapping
            tempSpan.style.position = "absolute";
            tempSpan.textContent = originalText;
            label.appendChild(tempSpan);

            // Measure the actual rendered text width
            const textWidth = tempSpan.offsetWidth;
            const containerWidth = label.offsetWidth;

            label.removeChild(tempSpan); // Remove temporary span

            // Replace text only if wrapping is required
            if (textWidth > containerWidth) {
                if(modeOCOrCC == "OC")
                    label.textContent = "Max PPO₂";
                else
                    label.textContent = "Dil Max PPO₂";
            }
        }

        // Run when the page loads & when resizing
        window.addEventListener("load", updateLabel1);
        window.addEventListener("resize", updateLabel1);

        function updateLabel2() {
            const label = document.getElementById("labelGasDensityDescription");
            let originalText = "Loop Gas Density";
            if(modeOCOrCC == "OC")
                originalText = "Gas Density";
            
            label.textContent = originalText; // Set original text first

            // Create a temporary measurement span inside the label
            const tempSpan = document.createElement("span");
            tempSpan.style.visibility = "hidden";
            tempSpan.style.whiteSpace = "nowrap"; // Prevent wrapping
            tempSpan.style.position = "absolute";
            tempSpan.textContent = originalText;
            label.appendChild(tempSpan);

            // Measure the actual rendered text width
            const textWidth = tempSpan.offsetWidth;
            const containerWidth = label.offsetWidth;

            label.removeChild(tempSpan); // Remove temporary span

            // Replace text only if wrapping is required
            if (textWidth > containerWidth) {
                if(modeOCOrCC == "CC")
                    label.textContent = "Loop Density";
                
            }
        }

        // Run when the page loads & when resizing
        window.addEventListener("load", updateLabel2);
        window.addEventListener("resize", updateLabel2);
    </script>

    {{--  Script to avoid the pill selector OC CC to go back to default OC --}}
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const navLinks = document.querySelectorAll("#nav-tabs .dh-channel-chip");

            navLinks.forEach(link => {
                link.addEventListener("click", function () {
                    // Remove 'active' class from all tabs
                    navLinks.forEach(nav => nav.classList.remove("is-active"));

                    // Add 'active' class to the clicked tab
                    this.classList.add("is-active");

                    // Save selected tab in local storage (optional)
                    localStorage.setItem("activeTab", this.getAttribute("data-tag"));
                });
            });

            // Restore active tab after page reload or resize
            const savedTab = localStorage.getItem("activeTab");
            if (savedTab) {
                navLinks.forEach(link => {
                    if (link.getAttribute("data-tag") === savedTab) {
                        link.classList.add("is-active");
                    } else {
                        link.classList.remove("is-active");
                    }
                });
                if (savedTab === "CC" && typeof closedCircuitTab !== 'undefined') {
                    closedCircuitTab.click();
                } else if (savedTab === "OC" && typeof openCircuitTab !== 'undefined') {
                    openCircuitTab.click();
                }
            }
        });
    </script>

    {{-- Scripts to manage gas consumption --}}
    <script>
        // Per-gas SAC rate overrides, keyed by the same gas-mix string
        // calculateGasConsumption already groups by ("32%", "21/35", ...) -
        // persists across recalculations for the rest of the page session
        // (Pablo, 2026-09-24: "typically you consume more gas depending on
        // the mix"). A gas with no override yet falls back to whichever of
        // the two bulk Bottom/Deco gas sliders in the Inputs card matches
        // its type - those still work exactly as before for any gas that
        // hasn't been individually tuned.
        window.dhGasSacRates = window.dhGasSacRates || {};

        function dhGetSacRateForGas(gasMix, type) {
            if (window.dhGasSacRates.hasOwnProperty(gasMix)) {
                return window.dhGasSacRates[gasMix];
            }
            var fallbackEl = document.getElementById(type === 'deco' ? 'labelSACDecoGas' : 'labelSACBottomGas');
            var fallback = fallbackEl ? parseFloat(fallbackEl.value) : NaN;
            return isNaN(fallback) ? (type === 'deco' ? 0.5 : 0.8) : fallback;
        }

        // `scenario` is a normalized {profile, table, ...} object (see
        // dhNormalizeScenario) - a bare array is also accepted for whatever
        // legacy call site hasn't been updated yet, treated as `.profile`.
        function calculateGasConsumption(scenario) {
            var hasV2Table = scenario && !Array.isArray(scenario) && Array.isArray(scenario.table);
            if (hasV2Table) return dhCalculateGasConsumptionV2(scenario.table);
            var diveData = Array.isArray(scenario) ? scenario : (scenario ? scenario.profile : []);
            return dhCalculateGasConsumptionLegacy(diveData);
        }

        // Deco Table API v2 (Pablo, 2026-09-25 -
        // https://claude.ai/artifact/CQMT3MahLtqEgA68z7Fgta): reads the
        // backend's own consolidated `table` instead of the fine-grained
        // `profile`. A CC leg's `gas` is already null there - there's no
        // cylinder to report on for it at all - whereas the fine-grained
        // profile's CC legs each carry their own continuously-recalculated
        // loop-equivalent mix (holding setpoint, not a real gas), which is
        // exactly what was leaking into this table as spurious entries like
        // "23/46" (Pablo: "you will ONLY show gases that were either OC
        // gases, bailout or deco...never something the user did not
        // define"). Every row's own `time` is already that leg's own
        // duration too - no more prevTime bookkeeping needed.
        function dhCalculateGasConsumptionV2(rows) {
            const gasVolume = {};
            let bottomGasMix;

            rows.forEach(function (row) {
                if (!row.gas) return; // CC leg - no cylinder gas to report

                const gasMix = row.gas.he === 0 ? `${row.gas.o2}%` : `${row.gas.o2}/${row.gas.he}`;
                if (row.phase === "descend" || row.phase === "level") {
                    bottomGasMix = gasMix;
                }

                const isDecoGas = row.phase !== "descend" && row.phase !== "level" && gasMix !== bottomGasMix;
                const gasType = isDecoGas ? "deco" : "bottom";
                const sacRate = dhGetSacRateForGas(gasMix, gasType);
                const absPressure = 1 + row.depth / 33;
                const volumeNeeded = sacRate * absPressure * row.time;

                if (!gasVolume[gasMix]) gasVolume[gasMix] = { gas: gasMix, volume: 0, type: gasType };
                gasVolume[gasMix].volume += volumeNeeded;
            });

            return Object.values(gasVolume).map(entry => ({
                gas: entry.gas,
                volume: entry.volume.toFixed(2),
                type: entry.type,
            }));
        }

        function dhCalculateGasConsumptionLegacy(diveData) {
            const gasVolume = {};
            let bottomGasMix; // Store bottom gas mix from descent/constant phases

            diveData.forEach((segment, index) => {
                if (index === 0) return; // Skip first element (no previous time to compare)

                const prevTime = diveData[index - 1].time; // Previous cumulative time
                const currentTime = segment.time; // Current cumulative time
                const phaseTime = currentTime - prevTime; // Correct phase duration
                const absPressure = segment.abs_p; // Absolute pressure in ATA

                const o2 = segment.gas[1]; // O2 percentage
                const he = segment.gas[3]; // He percentage
                const gasMix = he === 0 ? `${o2}%` : `${o2}/${he}`;

                // Store bottom gas from descent/constant phases
                if (segment.phase === "descent" || segment.phase === "const") {
                    bottomGasMix = gasMix;
                }

                // Determine correct SAC rate & gas type
                const isDecoGas = segment.phase !== "descent" && segment.phase !== "const" && gasMix !== bottomGasMix;
                const gasType = isDecoGas ? "deco" : "bottom";
                const sacRate = dhGetSacRateForGas(gasMix, gasType);

                // Calculate gas volume needed for this phase
                const volumeNeeded = sacRate * absPressure * phaseTime;

                // Accumulate total volume for each gas mix
                if (!gasVolume[gasMix]) {
                    gasVolume[gasMix] = { gas: gasMix, volume: 0, type: gasType };
                }
                gasVolume[gasMix].volume += volumeNeeded;
            });

            // Convert object to array format
            return Object.values(gasVolume).map(entry => ({
                gas: entry.gas,
                volume: entry.volume.toFixed(2),
                type: entry.type
            }));
        }

        // Re-derives gas consumption from the same diveData a table was
        // last built from and re-renders both tables - used after a bulk
        // Bottom/Deco SAC slider changes (per-gas sliders update their own
        // row in place instead, see renderGasConsumptionTable below).
        function dhRecalculateGasConsumption(diveData) {
            if (!diveData) return;
            var gasConsumption = calculateGasConsumption(diveData);
            renderGasConsumptionTable(gasConsumption, "bottom", diveData);
            renderGasConsumptionTable(gasConsumption, "deco", diveData);
        }

        function renderGasConsumptionTable(gasConsumption, filterType, diveData) {
            const containerId = filterType === "deco" ? "decoGasConsumptionTableContainer" : "bottomGasConsumptionTableContainer";
            const container = document.getElementById(containerId);
            container.innerHTML = ""; // Clear previous content

            // Create table container
            const tableWrapper = document.createElement("div");
            tableWrapper.classList.add("table-responsive");

            // Create table
            const table = document.createElement("table");
            table.classList.add("table", "table-striped", "table-sm");

            // Create table header with cuft unit
            const thead = document.createElement("thead");
            thead.innerHTML = `
                <tr>
                    <th class="text-sm" style="padding-left: 0px; padding-right:0px;">Gas</th>
                    <th class="text-sm text-center" style="padding-left: 0px; padding-right:0px;">SAC rate</th>
                    <th class="text-sm" style="padding-left: 0px; padding-right:0px;">Volume {{ $deco_unit ? "(liters)" : "(cuft)" }}</th>
                </tr>
            `;
            table.appendChild(thead);

            // Create table body with filtering logic
            const tbody = document.createElement("tbody");
            var isMetric = (typeof modeImpOrMetric !== 'undefined' && modeImpOrMetric === 'met');
            var unitLabel = isMetric ? 'L/min' : 'cuft/min';
            gasConsumption.forEach(entry => {
                if (filterType === "bottom" && entry.type !== "bottom") return;
                if (filterType === "deco" && entry.type !== "deco") return;

                // Real green-O2/blue-He split pill instead of plain
                // "18/45" text - same convention as the Gas column
                // elsewhere (Pablo, 2026-09-19: "we can use the gas split
                // pills in the gas consumption...same criteria we do for
                // everywhere else").
                var mix = dhParseGasMixString(entry.gas);
                var sacRate = dhGetSacRateForGas(entry.gas, entry.type);
                var displayRate = isMetric ? (sacRate * 28.3168).toFixed(0) : sacRate.toFixed(1);

                const row = document.createElement("tr");
                row.innerHTML = `
                    <td>${dhBuildGasSplitPillHtml(mix.o2, mix.he, true)}</td>
                    <td>
                        <div class="dh-gasconsumption-sac">
                            <div class="dh-gas-editable">
                                <input type="text" inputmode="decimal" class="dh-gas-input dh-gasconsumption-sac-input" value="${displayRate}" aria-label="SAC rate for ${entry.gas}">
                                <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                            </div>
                            <span class="dh-gasconsumption-sac-unit">${unitLabel}</span>
                            <input type="range" class="dh-gasconsumption-sac-slider" min="0.3" max="4" step="0.1" value="${sacRate}" aria-label="SAC rate slider for ${entry.gas}">
                        </div>
                    </td>
                    <td class="dh-gasconsumption-volume">${(entry.volume * ({{ $deco_unit ? 28.3168 : 1 }})).toFixed(2)}</td>
                `;
                tbody.appendChild(row);

                // Dragging this gas's own slider (or typing in its number
                // field - Pablo, 2026-09-24: "user can type the numbers for
                // the SACs, specially useful on mobile...moving a slider is
                // tricky") only ever changes this gas's own volume (each
                // gas accumulates independently in calculateGasConsumption)
                // - update these cells in place instead of rebuilding the
                // table, which would tear down the slider/input mid-drag or
                // mid-edit.
                var numberInput = row.querySelector('.dh-gasconsumption-sac-input');
                var slider = row.querySelector('.dh-gasconsumption-sac-slider');
                var volumeCell = row.querySelector('.dh-gasconsumption-volume');

                function applyRate(canonicalRate) {
                    canonicalRate = Math.min(4, Math.max(0.3, canonicalRate));
                    window.dhGasSacRates[entry.gas] = canonicalRate;
                    slider.value = canonicalRate;
                    numberInput.value = isMetric ? (canonicalRate * 28.3168).toFixed(0) : canonicalRate.toFixed(1);

                    if (diveData) {
                        var updated = calculateGasConsumption(diveData).find(function (u) { return u.gas === entry.gas; });
                        if (updated) {
                            volumeCell.textContent = (updated.volume * ({{ $deco_unit ? 28.3168 : 1 }})).toFixed(2);
                        }
                    }
                }

                slider.addEventListener('input', function () {
                    applyRate(parseFloat(slider.value));
                });

                numberInput.addEventListener('change', function () {
                    var typed = parseFloat(numberInput.value);
                    if (isNaN(typed)) {
                        // Revert to the last valid rate, not this row's
                        // initial one - the slider always tracks the
                        // current value even though this closure's own
                        // `sacRate` doesn't.
                        var current = parseFloat(slider.value);
                        numberInput.value = isMetric ? (current * 28.3168).toFixed(0) : current.toFixed(1);
                        return;
                    }
                    applyRate(isMetric ? (typed / 28.3168) : typed);
                });
            });
            table.appendChild(tbody);

            // Append table to wrapper
            tableWrapper.appendChild(table);
            container.appendChild(tableWrapper);
        }
    </script>

    {{-- "Export to PDF" and "Save Plan" on the Decompression plan card
         (Pablo, 2026-09-18). Both read window.lastDiveProfile - the same
         object the calculate button already builds and sends to the
         calculation API - rather than re-scraping every input from the
         DOM a second time. --}}
    <script>
        function dhFormatDepthUnit() { return modeImpOrMetric === 'met' ? 'm' : 'ft'; }
        function dhFormatRateUnit() { return modeImpOrMetric === 'met' ? 'm/min' : 'ft/min'; }

        // Landscape US Letter at 2x (792x612pt -> 1584x1224px) for a crisp
        // rasterized page (Pablo, 2026-09-19: matched pixel-for-pixel
        // against the reference templates provided).
        var DH_PDF_PAGE_W = 1584;
        var DH_PDF_PAGE_H = 1224;

        // Same green O2 / blue He split pill used everywhere else in the
        // app (Pablo, 2026-09-19: "Use the gas pills with the O2 and He
        // colors we use in the app") - reuses the real .dh-gas-split-pill/
        // .dh-gas-result-pill classes so colors and shape are exact, not
        // reimplemented by hand for the PDF.
        function dhBuildPdfSplitPill(o2, he, compact) {
            var sizeClass = compact ? ' is-compact' : '';
            var pillHeight = compact ? 24 : 32;
            // Three earlier attempts at centering this (the wrapper's own
            // height, a table cell around the wrapper, line-height on each
            // pill) all still read as "higher" - because the wrapper itself
            // is .dh-gas-split-pill, an inline-flex row with NO align-items
            // set, so its default is "stretch": html2canvas stretches each
            // pill to the flex line's height rather than respecting the
            // pill's own explicit height, undoing line-height centering
            // from the inside. A real <table> has no such default -
            // dropping the flex wrapper entirely and laying the two pills
            // out as table cells (Pablo, 2026-09-19: "still showing
            // higher...add more padding to the top").
            //
            // .dh-gas-split-pill's joined-corner/box-shadow-divider CSS and
            // its "%" suffix are both selectors scoped to a DIRECT
            // .dh-gas-result-pill child of that class - once these pills
            // are inside <td>s instead, neither would match, so both are
            // replicated by hand below instead.
            var wrap = document.createElement('table');
            wrap.style.cssText = 'display:inline-table; border-collapse:collapse; vertical-align:middle;';
            var tr = document.createElement('tr');

            // Belt and suspenders on the text itself too: .dh-gas-result-pill
            // is ALSO an inline-flex box internally (for centering its own
            // number), the exact mechanism already shown unreliable here -
            // overriding it to plain block + line-height is real text
            // layout, not flex, regardless of what container it now sits in.
            function killInternalFlexCentering(el) {
                el.style.display = 'block';
                el.style.lineHeight = pillHeight + 'px';
                el.style.textAlign = 'center';
            }

            var o2Pill = document.createElement('label');
            o2Pill.className = 'dh-gas-result-pill is-o2' + sizeClass;
            o2Pill.textContent = o2 + '%';
            killInternalFlexCentering(o2Pill);
            var tdO2 = document.createElement('td');
            tdO2.style.cssText = 'vertical-align:middle; padding:0;';
            tdO2.appendChild(o2Pill);
            tr.appendChild(tdO2);

            if (he !== 0) {
                o2Pill.style.borderRadius = pillHeight + 'px 0 0 ' + pillHeight + 'px';
                var hePill = document.createElement('label');
                hePill.className = 'dh-gas-result-pill is-he' + sizeClass;
                hePill.textContent = he + '%';
                hePill.style.borderRadius = '0 ' + pillHeight + 'px ' + pillHeight + 'px 0';
                killInternalFlexCentering(hePill);
                var tdHe = document.createElement('td');
                tdHe.style.cssText = 'vertical-align:middle; padding:0;';
                tdHe.appendChild(hePill);
                tr.appendChild(tdHe);
            }

            wrap.appendChild(tr);
            return wrap;
        }

        // "18/45" -> {o2:18, he:45}; "50%" -> {o2:50, he:0} - the same
        // string shapes formatGas()/calculateGasConsumption() already
        // produce for the on-screen tables' Gas column/cells.
        function dhParseGasMixString(str) {
            str = (str || '').trim();
            if (str.indexOf('/') !== -1) {
                var parts = str.split('/');
                return { o2: parseInt(parts[0], 10) || 0, he: parseInt(parts[1], 10) || 0 };
            }
            return { o2: parseInt(str, 10) || 0, he: 0 };
        }

        // The one green-O2/blue-He split pill markup, as an HTML string for
        // the places that build a table row via innerHTML rather than the
        // DOM API (Pablo, 2026-09-19: "in the app view, we can use the gas
        // split pills in the gas consumption...same criteria we do
        // everywhere else"). Shared by generateDecoTable's Gas column and
        // the Gas Consumption tables below, so both stay in sync.
        function dhBuildGasSplitPillHtml(o2, he, compact) {
            var sizeClass = compact ? ' is-compact' : '';
            if (he == 0) {
                return '<span class="dh-gas-split-pill is-solo"><label class="dh-gas-result-pill is-o2' + sizeClass + '">' + o2 + '</label></span>';
            }
            return '<span class="dh-gas-split-pill"><label class="dh-gas-result-pill is-o2' + sizeClass + '">' + o2 + '</label><label class="dh-gas-result-pill is-he' + sizeClass + '">' + he + '</label></span>';
        }

        function dhBuildPdfHeader(profile, isCC) {
            var header = document.createElement('div');
            header.style.cssText = 'position:relative; background:#0b2a3a; color:#fff; display:flex; align-items:center; padding:26px 48px; gap:28px; flex:0 0 auto;';

            var logo = document.createElement('img');
            logo.src = '{{ asset("assets") }}/img/logos/logo_circle.png';
            logo.style.cssText = 'width:80px; height:80px; border-radius:50%; flex:0 0 auto; object-fit:cover;';
            header.appendChild(logo);

            var titleBlock = document.createElement('div');
            titleBlock.style.cssText = 'flex:1 1 auto; min-width:0;';
            var model = document.getElementById('labelModel').textContent || 'ZH-L16C-GF';
            titleBlock.innerHTML =
                '<div style="font-size:32px; font-weight:800; line-height:1.2;">Decompression Plan</div>' +
                '<div style="font-size:15px; opacity:.8; margin-top:6px;">created on ' + new Date().toLocaleString() + ' by {{ addslashes(auth()->user()->name ?? "") }}</div>' +
                '<div style="font-size:15px; opacity:.8;">Model: ' + model + '</div>';
            header.appendChild(titleBlock);

            // Absolutely centered on the page rather than a flex sibling -
            // titleBlock/infoBlock content widths differ, so splitting the
            // remaining flex space between them doesn't actually land the
            // badge in the middle of the page (Pablo, 2026-09-19: "the
            // circle OC and CC at the top need to be in the center of the
            // page").
            var badge = document.createElement('div');
            badge.style.cssText = 'position:absolute; left:50%; top:50%; transform:translate(-50%,-50%); width:100px; height:100px; border:4px solid #fff; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:28px; font-weight:800;';
            badge.textContent = isCC ? 'CC' : 'OC';
            header.appendChild(badge);

            var infoBlock = document.createElement('div');
            infoBlock.style.cssText = 'flex:1 1 auto; text-align:right; font-size:15px; line-height:1.8; min-width:0;';
            var siteName = dhSelectedSiteName || 'Custom dive';
            var gfsText = document.getElementById('labelGFs').textContent;
            var lines = [
                '<b>Site/Depth:</b> ' + siteName + ' (' + profile.maxDepth + dhFormatDepthUnit() + ')',
                '<b>Bottom time:</b> ' + profile.bottomTime + 'm &nbsp;&nbsp; <b>GFs:</b> ' + gfsText,
                '<b>Dsc/Asc rates:</b> ' + profile.rate.descent + '/' + profile.rate.ascent + ' ' + dhFormatRateUnit(),
            ];
            if (isCC) lines.push('<b>Set Point:</b> ' + profile.setpoint);
            infoBlock.innerHTML = lines.map(function (l) { return '<div>' + l + '</div>'; }).join('');
            header.appendChild(infoBlock);

            return header;
        }

        function dhBuildPdfDisclaimer() {
            var bar = document.createElement('div');
            bar.style.cssText = 'background:#fbeceb; border-left:8px solid #b0322b; display:flex; align-items:center; gap:20px; padding:14px 48px; flex:0 0 auto;';
            var icon = document.createElement('span');
            icon.className = 'material-icons-round';
            icon.textContent = 'warning';
            icon.style.cssText = 'color:#b0322b; font-size:32px; flex:0 0 auto;';
            bar.appendChild(icon);
            var text = document.createElement('div');
            text.style.cssText = 'color:#b0322b; font-size:13px; line-height:1.5;';
            text.textContent = 'Diving, especially decompression diving, is a risky activity. These calculations are based on mathematical models and do not guarantee prevention of decompression sickness or other diving hazards. Always use a reliable dive computer, follow established safety guidelines, dive within your training and experience level, and plan for contingencies. This plan is an informational aid, not a replacement for professional dive planning and real-time monitoring.';
            bar.appendChild(text);
            return bar;
        }

        function dhBuildPdfGases(profile, isCC) {
            var section = document.createElement('div');
            section.style.cssText = 'padding:18px 48px 4px; flex:0 0 auto;';

            var heading = document.createElement('div');
            heading.style.cssText = 'font-weight:800; font-size:16px; color:#0b2a3a; border-bottom:2px solid #0b2a3a; padding-bottom:8px; margin-bottom:16px;';
            heading.textContent = 'Gases';
            section.appendChild(heading);

            var row = document.createElement('div');
            row.style.cssText = 'display:flex; gap:16px; flex-wrap:wrap;';

            // Real fix for the label text vs. pill misalignment (Pablo,
            // 2026-09-19: "I never wanted to move the text and pill
            // together...referring to moving the split pill down in
            // relation to the text...right now they are not [aligned]"):
            // the previous version put the split pill in its own <td> as a
            // NESTED <table> (dhBuildPdfSplitPill's own return value) -
            // vertical-align:middle on a <td> whose only child is another
            // <table> is exactly the kind of nested-table layout
            // html2canvas doesn't compute reliably, matching what he's
            // seeing. Flattened here into ONE row - label, O2 pill, He
            // pill all as direct sibling <td>s - so there's no nested
            // table for html2canvas to get wrong; every visible piece
            // aligns off the same single row's vertical-align:middle.
            function addChip(label, o2, he) {
                var chip = document.createElement('div');
                // Tightened per Pablo, 2026-09-19: "once we fix that, then
                // I want the outer chip to be tighter wrapping the text
                // and split pill" - small, deliberate padding now that
                // alignment itself isn't riding on this value.
                chip.style.cssText = 'display:inline-block; border:1.5px solid #0b2a3a; border-radius:999px; padding:5px 12px 5px 14px; flex:0 0 auto;';
                var table = document.createElement('table');
                table.style.cssText = 'border-collapse:collapse;';
                var tr = document.createElement('tr');

                var tdLabel = document.createElement('td');
                tdLabel.style.cssText = 'vertical-align:middle; padding:0 6px 0 0; font-weight:700; font-size:12px; color:#0b2a3a; white-space:nowrap;';
                tdLabel.textContent = label + ':';
                tr.appendChild(tdLabel);

                // Alignment itself comes from the flat row's shared
                // vertical-align:middle above - this is a deliberate few-px
                // nudge on top of that, purely visual (doesn't touch layout/
                // alignment math at all), per Pablo, 2026-09-19: "now that
                // you have the pill and text aligned...push the pill even
                // further down compared to the text".
                var o2Pill = document.createElement('label');
                o2Pill.className = 'dh-gas-result-pill is-o2 is-compact';
                o2Pill.textContent = o2 + '%';
                o2Pill.style.cssText = 'position:relative; top:4px;';
                var tdO2 = document.createElement('td');
                tdO2.style.cssText = 'vertical-align:middle; padding:0;';
                tdO2.appendChild(o2Pill);
                tr.appendChild(tdO2);

                if (he !== 0) {
                    o2Pill.style.borderRadius = '24px 0 0 24px';
                    var hePill = document.createElement('label');
                    hePill.className = 'dh-gas-result-pill is-he is-compact';
                    hePill.textContent = he + '%';
                    hePill.style.cssText = 'position:relative; top:4px; border-radius:0 24px 24px 0;';
                    var tdHe = document.createElement('td');
                    tdHe.style.cssText = 'vertical-align:middle; padding:0;';
                    tdHe.appendChild(hePill);
                    tr.appendChild(tdHe);
                }

                table.appendChild(tr);
                chip.appendChild(table);
                row.appendChild(chip);
            }

            addChip(isCC ? 'Diluent' : 'Bottom gas', profile.bottomGas.O2, profile.bottomGas.He);
            profile.decoGases.forEach(function (gas, idx) {
                var label = (isCC && idx === 0) ? 'Bailout' : ('Deco ' + (idx + 1));
                addChip(label, gas.O2, gas.He);
            });

            section.appendChild(row);
            return section;
        }

        // `underlineColor` defaults to navy (the same color as the text) -
        // the Gas switches table asks for a distinct blue line instead
        // (Pablo, 2026-09-19: "use the same font and color that
        // Decompression table or Decompression chart...add the line below
        // in blue").
        function dhBuildPdfColumnHeader(text, underlineColor) {
            var h = document.createElement('div');
            h.style.cssText = 'font-weight:800; font-size:15px; color:#0b2a3a; border-bottom:2px solid ' + (underlineColor || '#0b2a3a') + '; padding-bottom:8px; margin-bottom:14px;';
            h.textContent = text;
            return h;
        }

        // Clones an already-rendered table container verbatim - same
        // classes (table-striped, the phase icons via Material Icons
        // ligatures), so html2canvas rasterizes it exactly as it already
        // looks on screen instead of needing every icon/color reimplemented
        // by hand for the PDF.
        function dhBuildPdfTableClone(sourceId) {
            var source = document.getElementById(sourceId);
            var wrap = document.createElement('div');
            wrap.style.fontSize = '12px';
            if (source && source.innerHTML.trim()) {
                wrap.innerHTML = source.innerHTML;
                var responsive = wrap.querySelector('.table-responsive');
                if (responsive) responsive.style.overflow = 'visible';
                var table = wrap.querySelector('table');
                if (table) {
                    table.style.width = '100%';
                    table.style.fontSize = '12px';
                }
            }
            return wrap;
        }

        // Rebuilt from the on-screen table's own cells (rather than cloning
        // its innerHTML like dhBuildPdfTableClone) so the plain "18/45"/
        // "50%" gas text can become a real split pill and the striped rows
        // can go away (Pablo, 2026-09-19: "we can use gas pills too...I
        // don't like the grey and white alternating row shade...align the
        // pill to the left...volume to the right").
        function dhBuildPdfGasConsumptionTable(sourceId) {
            var wrap = document.createElement('div');
            var source = document.getElementById(sourceId);
            var sourceRows = source ? source.querySelectorAll('table tbody tr') : [];
            if (!sourceRows.length) return wrap;

            var volumeHeaderEl = source.querySelector('table thead th:last-child');
            var volumeHeaderText = volumeHeaderEl ? volumeHeaderEl.textContent.trim() : 'Volume';

            var table = document.createElement('table');
            table.style.cssText = 'width:100%; border-collapse:collapse; font-size:12px;';
            var thead = document.createElement('thead');
            thead.innerHTML =
                '<tr>' +
                '<th style="text-align:left; padding:4px 4px 8px; font-weight:700; color:#5a6b78; font-size:10px; text-transform:uppercase; letter-spacing:.03em;">Gas</th>' +
                '<th style="text-align:right; padding:4px 4px 8px; font-weight:700; color:#5a6b78; font-size:10px; text-transform:uppercase; letter-spacing:.03em;">SAC rate</th>' +
                '<th style="text-align:right; padding:4px 4px 8px; font-weight:700; color:#5a6b78; font-size:10px; text-transform:uppercase; letter-spacing:.03em;">' + volumeHeaderText + '</th>' +
                '</tr>';
            table.appendChild(thead);

            var tbody = document.createElement('tbody');
            sourceRows.forEach(function (sourceRow) {
                var gasCell = sourceRow.querySelector('td');
                // Read the Volume/SAC cells by class, not position - each
                // gas gets its own editable SAC rate now (Pablo,
                // 2026-09-24), so the old "2nd <td> is the volume"
                // assumption no longer holds.
                var volumeCell = sourceRow.querySelector('.dh-gasconsumption-volume');
                var sacInput = sourceRow.querySelector('.dh-gasconsumption-sac-input');
                var sacUnit = sourceRow.querySelector('.dh-gasconsumption-sac-unit');
                if (!gasCell || !volumeCell) return;
                // The source cell holds a real split pill now (Pablo,
                // 2026-09-19: "in the app view, we can use the gas split
                // pills in the gas consumption"), not the plain "18/55"
                // text this used to scrape - reading its .textContent would
                // concatenate the two pill labels with no separator ("1855"
                // instead of "18/55", found from Pablo: "the gas was 18/55
                // and I see in the pdf 1855% in a green pill"). Read the O2/
                // He labels directly instead.
                var o2Label = gasCell.querySelector('.is-o2');
                var heLabel = gasCell.querySelector('.is-he');
                var mix = {
                    o2: o2Label ? parseInt(o2Label.textContent, 10) || 0 : 0,
                    he: heLabel ? parseInt(heLabel.textContent, 10) || 0 : 0,
                };
                var tr = document.createElement('tr');
                var tdGas = document.createElement('td');
                tdGas.style.cssText = 'padding:6px 4px; text-align:left; background:#fff;';
                tdGas.appendChild(dhBuildPdfSplitPill(mix.o2, mix.he, true));
                var tdSac = document.createElement('td');
                tdSac.style.cssText = 'padding:6px 4px; text-align:right; background:#fff; color:#5a6b78; white-space:nowrap;';
                tdSac.textContent = (sacInput ? sacInput.value : '?') + ' ' + (sacUnit ? sacUnit.textContent : '');
                var tdVolume = document.createElement('td');
                tdVolume.style.cssText = 'padding:6px 4px; text-align:right; background:#fff; font-weight:700; color:#0b2a3a;';
                tdVolume.textContent = volumeCell.textContent.trim();
                tr.appendChild(tdGas);
                tr.appendChild(tdSac);
                tr.appendChild(tdVolume);
                tbody.appendChild(tr);
            });
            table.appendChild(tbody);
            wrap.appendChild(table);
            return wrap;
        }

        // OC only (Pablo, 2026-09-19: "we could add a small table under the
        // chart with the gas switches...this applies only for OC") - the
        // exact same switch points already driving the chart's annotation
        // markers, so this table and the graph can never disagree.
        // `firstSwitchAtMaxDepth` matches the CC bailout profile's own
        // marker rule (buildGasSwitchAnnotations): bailing out happens the
        // instant the diver decides to, at the bottom, not wherever the
        // API's combined ascent-to-first-stop entry happens to end.
        function dhBuildPdfGasSwitchTable(profileResponse, firstSwitchAtMaxDepth) {
            var wrap = document.createElement('div');
            var points = computeOCGasSwitchPoints(profileResponse, !!firstSwitchAtMaxDepth);
            if (!points.length) return wrap;

            // Same font/color AND underline as every other section header -
            // the blue underline from the previous round didn't match after
            // all (Pablo, 2026-09-19: "the line below gas switches needs to
            // be the same color as the other lines").
            wrap.appendChild(dhBuildPdfColumnHeader('Gas switches'));

            var table = document.createElement('table');
            table.style.cssText = 'width:100%; border-collapse:collapse; font-size:12px;';
            var thead = document.createElement('thead');
            thead.innerHTML =
                '<tr>' +
                '<th style="text-align:left; padding:4px; font-weight:700; color:#5a6b78; font-size:10px; text-transform:uppercase; letter-spacing:.03em;">RT</th>' +
                '<th style="text-align:left; padding:4px; font-weight:700; color:#5a6b78; font-size:10px; text-transform:uppercase; letter-spacing:.03em;">Depth</th>' +
                '<th style="text-align:left; padding:4px; font-weight:700; color:#5a6b78; font-size:10px; text-transform:uppercase; letter-spacing:.03em;">Gas</th>' +
                '</tr>';
            table.appendChild(thead);

            var tbody = document.createElement('tbody');
            points.forEach(function (point) {
                var mix = dhParseGasMixString(point.gasLabel);
                var tr = document.createElement('tr');
                // Same rounding the Decompression Table itself uses for
                // these two columns (formatTimeMin/absPressureToDepthDeco) -
                // anything else and this table's numbers won't quite match
                // the RT/Depth already printed next to them.
                var tdRT = document.createElement('td');
                tdRT.style.cssText = 'padding:5px 4px; text-align:left; background:#fff; font-weight:600; color:#0b2a3a;';
                tdRT.textContent = Math.ceil(point.time) + ' min';
                var tdDepth = document.createElement('td');
                tdDepth.style.cssText = 'padding:5px 4px; text-align:left; background:#fff; font-weight:600; color:#0b2a3a;';
                tdDepth.textContent = absPressureToDepthDeco(point.abs_p) + ' ' + dhFormatDepthUnit();
                var tdGas = document.createElement('td');
                tdGas.style.cssText = 'padding:5px 4px; text-align:left; background:#fff;';
                tdGas.appendChild(dhBuildPdfSplitPill(mix.o2, mix.he, true));
                tr.appendChild(tdRT);
                tr.appendChild(tdDepth);
                tr.appendChild(tdGas);
                tbody.appendChild(tr);
            });
            table.appendChild(tbody);
            wrap.appendChild(table);
            return wrap;
        }

        // Its own section (title + line separator) above the chart, instead
        // of pills crammed onto the Decompression Table/Bailout Table
        // header lines - that made both headers misalign against each
        // other (Pablo, 2026-09-19: "the run time and deco time pills are
        // creating misalignment. Let's create a row above the Decompression
        // Chart...called 'Dive times'...slightly larger...single line").
        // Full-size (not .is-compact) pills, a step up from every other gas
        // pill in the PDF, since this is the one summary section rather
        // than a per-row/per-gas label.
        // `bailoutTotals` is the [runTime, decoTime] generateDecoTable(...,1)
        // already returned for the bailout profile (window.lastBailoutTotals)
        // - only meaningful, and only passed, for CC.
        function dhBuildPdfDiveTimesSection(isCC, bailoutTotals) {
            var wrap = document.createElement('div');
            wrap.appendChild(dhBuildPdfColumnHeader('Dive times'));

            var row = document.createElement('div');
            row.style.cssText = 'display:flex; align-items:center; gap:20px; margin-bottom:6px;';

            // Table + td vertical-align:middle, not a flex row - same fix
            // and same reason as dhBuildPdfGases's addChip below.
            function addTimePill(label, value, deltaMinutes) {
                var table = document.createElement('table');
                table.style.cssText = 'display:inline-table; border-collapse:collapse; margin-right:20px;';
                var tr = document.createElement('tr');
                var tdLabel = document.createElement('td');
                tdLabel.style.cssText = 'vertical-align:middle; padding:0 8px 0 0; font-weight:700; font-size:13px; color:#0b2a3a; white-space:nowrap;';
                tdLabel.textContent = label;
                var tdPill = document.createElement('td');
                tdPill.style.cssText = 'vertical-align:middle; padding:0;';
                // Wrapped in a relatively-positioned span so the delta badge
                // (CC only) can sit absolutely on the pill's own corner,
                // same convention as the on-screen What-if summary's
                // .dh-gas-pill-badge.
                var pillWrap = document.createElement('span');
                pillWrap.style.cssText = 'position:relative; display:inline-block;';
                var pill = document.createElement('label');
                pill.className = 'dh-gas-result-pill';
                pill.textContent = value;
                pillWrap.appendChild(pill);
                if (deltaMinutes > 0) {
                    var badge = document.createElement('span');
                    badge.style.cssText = 'position:absolute; top:-8px; right:-10px; min-width:20px; height:20px; padding:0 5px; border-radius:999px; background:#b0322b; color:#fff; font-size:11px; font-weight:800; display:flex; align-items:center; justify-content:center; border:2px solid #fff; line-height:1;';
                    badge.textContent = '+' + deltaMinutes;
                    pillWrap.appendChild(badge);
                }
                tdPill.appendChild(pillWrap);
                tr.appendChild(tdLabel);
                tr.appendChild(tdPill);
                table.appendChild(tr);
                row.appendChild(table);
            }

            // Deltas match the on-screen What-if summary's own convention
            // (round the difference of the raw totals, not the difference
            // of two already-rounded display strings).
            var runDelta = 0, decoDelta = 0;
            if (isCC && bailoutTotals) {
                runDelta = Math.round(bailoutTotals[0] - baselineRTDT[0]);
                decoDelta = Math.round(bailoutTotals[1] - baselineRTDT[1]);
            }

            addTimePill('Run time', document.getElementById('labelTotalRunTime').textContent.trim(), runDelta);
            addTimePill('Deco time', document.getElementById('labelTotalDecoTime').textContent.trim(), decoDelta);

            wrap.appendChild(row);

            // Legend only makes sense once at least one badge could show
            // (Pablo, 2026-09-19: "add a small red legend just below to say
            // in red added time if bailout to OC").
            if (isCC && (runDelta > 0 || decoDelta > 0)) {
                var legend = document.createElement('div');
                legend.style.cssText = 'color:#b0322b; font-size:10px; font-weight:600; margin-bottom:12px;';
                legend.textContent = 'in red: added time if bailout to OC';
                wrap.appendChild(legend);
            }

            return wrap;
        }

        function dhBuildPdfColumns(profile, isCC, mainChartUrl, baseline, bailout) {
            var row = document.createElement('div');
            row.id = 'dhPdfColumnsRow';
            row.style.cssText = 'display:flex; gap:30px; padding:12px 48px 34px; flex:1 1 auto; min-height:0;';

            var col1 = document.createElement('div');
            col1.className = 'dh-pdf-fit-col';
            col1.style.cssText = 'flex:1 1 0; min-width:0;';
            col1.appendChild(dhBuildPdfColumnHeader('Decompression Table'));
            col1.appendChild(dhBuildPdfTableClone('decoTableContainer'));
            row.appendChild(col1);

            var col2 = document.createElement('div');
            col2.className = 'dh-pdf-fit-col';
            col2.style.cssText = 'flex:1 1 0; min-width:0;';
            col2.appendChild(dhBuildPdfDiveTimesSection(isCC, bailout ? window.lastBailoutTotals : null));
            col2.appendChild(dhBuildPdfColumnHeader('Decompression Chart'));
            var chartCard = document.createElement('div');
            chartCard.style.cssText = 'background:#0b2a3a; border-radius:14px; padding:16px;';
            var chartImg = document.createElement('img');
            chartImg.src = mainChartUrl;
            chartImg.style.cssText = 'width:100%; display:block;';
            chartCard.appendChild(chartImg);
            col2.appendChild(chartCard);
            if (!isCC && baseline) col2.appendChild(dhBuildPdfGasSwitchTable(baseline, false));
            row.appendChild(col2);

            var col3 = document.createElement('div');
            col3.className = 'dh-pdf-fit-col';
            col3.style.cssText = 'flex:1 1 0; min-width:0;';
            if (isCC) {
                col3.appendChild(dhBuildPdfColumnHeader('Bailout Table'));
                col3.appendChild(dhBuildPdfTableClone('BOTableContainer'));
                // Gas consumption for the bailout profile, not the gas-switch
                // table this used to show (Pablo, 2026-09-24: "replace the
                // gas switches by the gas consumption table for bailout -
                // ONLY on CC"). Populated fresh from the bailout profile
                // right here rather than depending on the "Bailout to OC"
                // what-if checkbox having been ticked first - the on-screen
                // containers this reuses (dhBuildPdfGasConsumptionTable) are
                // shared with OC's own table, so they need bailout data
                // written into them before being read.
                if (bailout) {
                    // Reads window.dhScenarios directly (not the `bailout`
                    // parameter, still the flat profile array) so this gets
                    // the v2 `table`-based calculation - real bailout/deco
                    // gases only, never the diluent or a CC loop artifact
                    // (Pablo, 2026-09-25: "you will ONLY show gases that
                    // were either OC gases, bailout or deco...never
                    // something the user did not define").
                    var bailoutGasConsumption = calculateGasConsumption(window.dhScenarios.bailout);
                    renderGasConsumptionTable(bailoutGasConsumption, "deco");

                    if (bailoutGasConsumption.some(function (g) { return g.type === 'deco'; })) {
                        var boSub2 = document.createElement('div');
                        boSub2.style.cssText = 'font-weight:700; font-size:12px; color:#5a6b78; text-transform:uppercase; letter-spacing:.03em; margin:12px 0 6px;';
                        boSub2.textContent = 'Bailout deco gases';
                        col3.appendChild(boSub2);
                        col3.appendChild(dhBuildPdfGasConsumptionTable('decoGasConsumptionTableContainer'));
                    }
                }
            } else {
                col3.appendChild(dhBuildPdfColumnHeader('Gas Consumption'));
                var sub1 = document.createElement('div');
                sub1.style.cssText = 'font-weight:700; font-size:12px; color:#5a6b78; text-transform:uppercase; letter-spacing:.03em; margin:4px 0 6px;';
                sub1.textContent = 'Bottom gas';
                col3.appendChild(sub1);
                col3.appendChild(dhBuildPdfGasConsumptionTable('bottomGasConsumptionTableContainer'));
                // No deco gases added to this plan - there's nothing for a
                // "Decompression gases" table to ever show, so skip the
                // label entirely instead of a heading over an empty table
                // (Pablo, 2026-09-19: "if there is no deco gases in the
                // plan for OC, just remove the word decompression gases").
                if (profile.decoGases && profile.decoGases.length) {
                    var sub2 = document.createElement('div');
                    sub2.style.cssText = 'font-weight:700; font-size:12px; color:#5a6b78; text-transform:uppercase; letter-spacing:.03em; margin:12px 0 6px;';
                    sub2.textContent = 'Decompression gases';
                    col3.appendChild(sub2);
                    col3.appendChild(dhBuildPdfGasConsumptionTable('decoGasConsumptionTableContainer'));
                }
            }
            row.appendChild(col3);

            return row;
        }

        function dhBuildPdfPage(profile, isCC, mainChartUrl, baseline, bailout) {
            var page = document.createElement('div');
            page.style.cssText = 'position:fixed; left:-99999px; top:0; width:' + DH_PDF_PAGE_W + 'px; height:' + DH_PDF_PAGE_H + 'px; background:#ffffff; font-family: "Roboto", Arial, sans-serif; display:flex; flex-direction:column; overflow:hidden;';
            page.appendChild(dhBuildPdfHeader(profile, isCC));
            page.appendChild(dhBuildPdfDisclaimer());
            page.appendChild(dhBuildPdfGases(profile, isCC));
            page.appendChild(dhBuildPdfColumns(profile, isCC, mainChartUrl, baseline, bailout));
            document.body.appendChild(page);
            return page;
        }

        // A long enough plan (many deco stops, or a bailout table with its
        // own gas-switch table underneath) can be taller than the fixed
        // page - since the page clips with overflow:hidden, that content
        // was just gone from the export (Pablo, 2026-09-19: "content on the
        // pdf will overflow beyond the page...part of the deco table is
        // missing or the graph chart also cuts...scale the content in the
        // three columns"). Must run AFTER `page` is in the real DOM (layout/
        // scrollHeight only exist then) and BEFORE the html2canvas snapshot.
        //
        // Each column that overflows gets uniformly scaled down to fit,
        // independently of the other two - a wide chart card shouldn't
        // shrink just because the table next to it happens to be long.
        // transform:scale() alone would also shrink the column's WIDTH,
        // leaving a gap where its neighbor doesn't fill in - widening the
        // column by 1/scale first, then scaling by that same factor,
        // cancels that out and the column still fills its original slot.
        function dhFitPdfColumnsToPage(page) {
            var row = page.querySelector('#dhPdfColumnsRow');
            if (!row) return;
            var pageBottom = page.getBoundingClientRect().bottom;
            var rowTop = row.getBoundingClientRect().top;
            var rowBottomPadding = 34; // matches dhBuildPdfColumns's own row padding
            var availableHeight = pageBottom - rowTop - rowBottomPadding;
            row.querySelectorAll('.dh-pdf-fit-col').forEach(function (col) {
                var actualHeight = col.scrollHeight;
                if (actualHeight <= availableHeight || actualHeight <= 0) return;
                var scale = availableHeight / actualHeight;
                var originalWidth = col.getBoundingClientRect().width;
                // flex:1 1 0 sets flex-basis:0, which overrides `width`
                // entirely for a flex item - flex:0 0 auto is what makes
                // the explicit width (and so the compensation above) apply.
                col.style.flex = '0 0 auto';
                col.style.width = (originalWidth / scale) + 'px';
                col.style.transform = 'scale(' + scale + ')';
                col.style.transformOrigin = 'top left';
            });
        }

        async function dhExportDecoPlanToPDF() {
            if (!globalResponse || !window.lastDiveProfile) {
                alert('Calculate a decompression plan first.');
                return;
            }

            var btn = document.getElementById('exportDecoPlanPdfBtn');
            var originalHtml = btn.innerHTML;
            btn.innerHTML = '<span class="material-icons-round" aria-hidden="true">hourglass_top</span> Exporting…';
            btn.disabled = true;

            var page = null;
            try {
                var profile = window.lastDiveProfile;
                var isCC = profile.mode === 'CC';
                var mainChartUrl = document.getElementById('profileChart').toDataURL('image/png');

                page = dhBuildPdfPage(profile, isCC, mainChartUrl, globalResponse['baseline'], globalResponse['bailout']);
                dhFitPdfColumnsToPage(page);

                // html2canvas mis-renders the split pills' inset box-shadow
                // divider (real Chrome shows a crisp 1px highlight; canvas
                // output shows a diagonal gradient across the whole pill) -
                // it's a purely decorative divider, so just drop it for the
                // snapshot rather than fight html2canvas's box-shadow
                // support (Pablo, 2026-09-19: "the He rendering weird...
                // need to be in the same color as the theme").
                page.querySelectorAll('.dh-gas-result-pill').forEach(function (el) { el.style.boxShadow = 'none'; });

                var canvas = await html2canvas(page, { scale: 1, backgroundColor: '#ffffff', useCORS: true });
                var imgData = canvas.toDataURL('image/png');

                var { jsPDF } = window.jspdf;
                var doc = new jsPDF({ orientation: 'landscape', unit: 'pt', format: 'letter' });
                var pageWidth = doc.internal.pageSize.getWidth();
                var pageHeight = doc.internal.pageSize.getHeight();
                doc.addImage(imgData, 'PNG', 0, 0, pageWidth, pageHeight);
                doc.save('decompression-plan-' + new Date().toISOString().slice(0, 10) + '.pdf');
            } catch (e) {
                console.error(e);
                alert('Could not export this plan to PDF - please try again.');
            } finally {
                if (page && page.parentNode) page.parentNode.removeChild(page);
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        }

        document.getElementById('exportDecoPlanPdfBtn').addEventListener('click', function (e) {
            @if(auth()->user()->isGuest())
                e.preventDefault();
                if (typeof showModalGuest === 'function') showModalGuest();
                return;
            @endif
            dhExportDecoPlanToPDF();
        });

        // "Save a dive" now opens a naming modal instead of a native
        // prompt() (Pablo, 2026-09-19: "open a modal to give the dive a
        // name and save it with a date time stamp") - the timestamp is just
        // the row's created_at, already set server-side on every insert.
        var saveDecoPlanBtn = document.getElementById('saveDecoPlanBtn');
        var modalSaveDecoPlanEl = document.getElementById('modalSaveDecoPlan');
        var modalSaveDecoPlan = modalSaveDecoPlanEl ? new bootstrap.Modal(modalSaveDecoPlanEl) : null;
        if (saveDecoPlanBtn && modalSaveDecoPlan) {
            saveDecoPlanBtn.addEventListener('click', function () {
                if (!window.lastDiveProfile) {
                    alert('Calculate a decompression plan first.');
                    return;
                }
                document.getElementById('modalSaveDecoPlanLabel').value = '';
                document.getElementById('modalSaveDecoPlanError').hidden = true;
                modalSaveDecoPlan.show();
            });

            document.getElementById('modalSaveDecoPlanConfirm').addEventListener('click', function () {
                var errorEl = document.getElementById('modalSaveDecoPlanError');
                errorEl.hidden = true;
                $.ajax({
                    url: '{{ route("DecoPlanner.savePlan") }}',
                    method: 'POST',
                    data: {
                        mode: window.lastDiveProfile.mode,
                        inputs: window.lastDiveProfile,
                        label: document.getElementById('modalSaveDecoPlanLabel').value || null,
                    },
                    success: function () {
                        modalSaveDecoPlan.hide();
                        dhFlashIconButton(saveDecoPlanBtn, true);
                        dhSavedDecoPlansCache = null; // stale - refetch next time "Open a dive" opens
                    },
                    error: function (xhr) {
                        errorEl.textContent = (xhr.responseJSON && xhr.responseJSON.message) || 'Could not save that dive - please try again.';
                        errorEl.hidden = false;
                    },
                });
            });
        }

        // Advanced settings (Pablo, 2026-09-25). Both toggles just flip the
        // global dhRecommendBestGases/dhRemoveSafetyGuards flags declared
        // near the top of the page - re-firing the sliders that actually
        // enforce them (GF High, setpoint) applies a "remove guards" change
        // immediately rather than only on the next depth/GF nudge.
        (function () {
            var btn = document.getElementById('dhAdvancedSettingsBtn');
            var modalEl = document.getElementById('modalAdvancedSettings');
            var modal = modalEl ? new bootstrap.Modal(modalEl) : null;
            if (!btn || !modal) return;

            btn.addEventListener('click', function () { modal.show(); });

            document.getElementById('dhToggleRecommendGases').addEventListener('change', function (e) {
                dhRecommendBestGases = e.target.checked;
            });

            document.getElementById('dhToggleRemoveGuards').addEventListener('change', function (e) {
                dhRemoveSafetyGuards = e.target.checked;
                GFHSlider.noUiSlider.set(GFHSlider.noUiSlider.get());
                setpointSlider.noUiSlider.set(setpointSlider.noUiSlider.get());
            });
        })();

        // "Open a dive" (Pablo, 2026-09-19): reloads a saved plan's exact
        // inputs and re-runs the calculation. Fetched fresh each time the
        // modal opens (except right after this same session already loaded
        // the list and nothing's changed) rather than kept in sync live -
        // this list only ever changes from this same modal/Save Plan, both
        // in this tab.
        var openDivePlanBtn = document.getElementById('openDivePlanBtn');
        var modalOpenDiveEl = document.getElementById('modalOpenDive');
        var modalOpenDive = modalOpenDiveEl ? new bootstrap.Modal(modalOpenDiveEl) : null;
        var dhSavedDecoPlansCache = null;

        function dhFormatSavedPlanWhen(isoString) {
            var d = new Date(isoString);
            return d.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' }) +
                ' ' + d.toLocaleTimeString(undefined, { hour: 'numeric', minute: '2-digit' });
        }

        function dhRenderOpenDiveList(plans) {
            var list = document.getElementById('modalOpenDiveList');
            var empty = document.getElementById('modalOpenDiveEmpty');
            if (!list) return;
            list.innerHTML = '';
            empty.hidden = plans.length > 0;
            plans.forEach(function (plan) {
                var row = document.createElement('div');
                row.className = 'dh-mygases-row';

                var pick = document.createElement('button');
                pick.type = 'button';
                pick.className = 'dh-mygases-pick';
                pick.style.cssText = 'flex-direction: column; align-items: flex-start; gap: 2px; padding: 8px 14px;';
                var titleLine = document.createElement('span');
                titleLine.style.cssText = 'font-weight: 700; font-size: .82rem;';
                titleLine.textContent = plan.label || (plan.mode === 'CC' ? 'Untitled CC dive' : 'Untitled OC dive');

                // Pills so a saved dive's shape is scannable at a glance
                // (Pablo, 2026-09-25: "use small pills to indicate: OC vs
                // CC - Single Level vs MultiLevel - the depth(s)"). Plans
                // saved before multi-level existed have no levelsMode at
                // all and are always treated as single-level.
                var isMultiPlan = plan.inputs.levelsMode === 'multi' && Array.isArray(plan.inputs.levels) && plan.inputs.levels.length > 0;
                var metaLine = document.createElement('span');
                metaLine.style.cssText = 'display: flex; flex-wrap: wrap; align-items: center; gap: 6px; font-size: .72rem;';

                var modePill = document.createElement('span');
                modePill.className = 'dh-pill ' + (plan.mode === 'CC' ? 'dh-pill-cc' : 'dh-pill-oc');
                modePill.textContent = plan.mode;

                var levelPill = document.createElement('span');
                levelPill.className = 'dh-pill ' + (isMultiPlan ? 'dh-pill-multi' : 'dh-pill-single');
                levelPill.textContent = isMultiPlan ? 'Multi Level' : 'Single Level';

                var depthText = document.createElement('span');
                depthText.style.opacity = '.85';
                depthText.textContent = isMultiPlan
                    ? plan.inputs.levels.map(function (l) { return l.depth; }).join('/') + dhFormatDepthUnit()
                    : (plan.inputs.maxDepth || '?') + dhFormatDepthUnit();

                var whenText = document.createElement('span');
                whenText.style.opacity = '.75';
                whenText.textContent = dhFormatSavedPlanWhen(plan.created_at);

                metaLine.appendChild(modePill);
                metaLine.appendChild(levelPill);
                metaLine.appendChild(depthText);
                metaLine.appendChild(whenText);

                pick.appendChild(titleLine);
                pick.appendChild(metaLine);
                pick.addEventListener('click', function () {
                    dhLoadSavedDecoPlan(plan);
                    modalOpenDive.hide();
                });

                var del = document.createElement('button');
                del.type = 'button';
                del.className = 'dh-corner-delete';
                del.title = 'Delete this saved dive';
                del.setAttribute('aria-label', 'Delete this saved dive');
                var delIcon = document.createElement('span');
                delIcon.className = 'material-icons-round';
                delIcon.setAttribute('aria-hidden', 'true');
                delIcon.textContent = 'delete';
                del.appendChild(delIcon);
                del.addEventListener('click', function () {
                    if (!confirm('Delete "' + titleLine.textContent + '"?')) return;
                    $.ajax({
                        url: '{{ route("DecoPlanner.deletePlan", ["id" => "__ID__"]) }}'.replace('__ID__', plan.id),
                        method: 'DELETE',
                        success: function () {
                            dhSavedDecoPlansCache = dhSavedDecoPlansCache.filter(function (p) { return p.id !== plan.id; });
                            dhRenderOpenDiveList(dhSavedDecoPlansCache);
                        },
                        error: function () {
                            alert('Could not delete that dive - please try again.');
                        },
                    });
                });

                row.appendChild(pick);
                row.appendChild(del);
                list.appendChild(row);
            });
        }

        // Sets one field's value and dispatches the same 'change' event a
        // real edit + blur would - every input on this page (sliders
        // included) already listens for exactly that to sync its slider/
        // recompute dependents, so this reproduces a manual edit rather
        // than needing a second, parallel way to drive each field.
        function dhSetFieldValue(id, value) {
            var el = document.getElementById(id);
            if (!el || value === undefined || value === null) return;
            el.value = value;
            el.dispatchEvent(new Event('change', { bubbles: true }));
        }

        // Restores every input exactly as the diver had it when they saved
        // this plan, then forces a recalculation so the results reproduce
        // (Pablo, 2026-09-19: "load all configs...exactly as the user
        // configured. Then force to run the decompression so we can
        // reproduce the same results").
        function dhLoadSavedDecoPlan(plan) {
            var inputs = plan.inputs;
            var isCC = plan.mode === 'CC';
            // Plans saved before multi-level existed have no levelsMode at
            // all - treat those as single-level (Pablo, 2026-09-25: "For
            // the dives that already exist in the db, assume they were all
            // single level").
            var isMulti = inputs.levelsMode === 'multi' && Array.isArray(inputs.levels) && inputs.levels.length > 0;

            // Always click the target tab, even if already active - its
            // handler (showOpenCircuit/showClosedCircuit) is what resets
            // every gas slot back to empty, which this load needs whether
            // or not the mode itself is actually changing.
            (isCC ? closedCircuitTab : openCircuitTab).click();
            (isMulti ? multiLevelTab : singleLevelTab).click();

            if (isMulti) {
                // Collapse to level 1 first, then open exactly the slots
                // this plan needs - a currently-open 4-level layout must
                // not leak into a plan saved with only 2 levels, and vice
                // versa.
                for (var lv = 4; lv >= 2; lv--) {
                    if (!document.getElementById('levelPanel' + lv).hidden) dhDeleteLevel(lv);
                }
                for (var lv2 = 2; lv2 <= inputs.levels.length; lv2++) {
                    window['showLevel' + lv2]();
                }
                inputs.levels.forEach(function (level, idx) {
                    var n = idx + 1;
                    dhLevelDepthSliders[n].noUiSlider.set(level.depth);
                    dhLevelTimeSliders[n].noUiSlider.set(level.bottomTime);
                });
            } else {
                dhSetFieldValue('labelDepth', inputs.maxDepth);
                dhSetFieldValue('labelBottomTime', inputs.bottomTime);
            }

            if (inputs.gradientFactors) {
                dhSetFieldValue('labelGFL', inputs.gradientFactors.low);
                dhSetFieldValue('labelGFH', inputs.gradientFactors.high);
            }
            dhSetFieldValue('labelSurfaceTime', inputs.surfaceTime);
            if (inputs.rate) {
                dhSetFieldValue('labelDes', inputs.rate.descent);
                dhSetFieldValue('labelAsc', inputs.rate.ascent);
            }
            if (isCC) dhSetFieldValue('labelSetpoint', inputs.setpoint);

            if (inputs.bottomGas) {
                dhSetFieldValue('labelBottomGasO2', inputs.bottomGas.O2);
                dhSetFieldValue('labelBottomGasHe', inputs.bottomGas.He);
            }

            var decoGases = inputs.decoGases || [];
            // CC's slot 1 (Bailout) is already revealed by showClosedCircuit
            // above - only slots beyond that need an "Add gas" click each,
            // same as OC needing one click per slot including the first.
            var slotsToAdd = isCC ? Math.max(0, decoGases.length - 1) : decoGases.length;
            for (var i = 0; i < slotsToAdd; i++) {
                document.getElementById('gasTabBtnAdd').click();
            }
            decoGases.forEach(function (gas, idx) {
                var n = idx + 1;
                dhSetFieldValue('labelDecoGas' + n + 'O2', gas.O2);
                dhSetFieldValue('labelDecoGas' + n + 'He', gas.He);
            });

            document.getElementById('calculateDecoProfile').click();
        }

        if (openDivePlanBtn && modalOpenDive) {
            openDivePlanBtn.addEventListener('click', function () {
                if (dhSavedDecoPlansCache) {
                    dhRenderOpenDiveList(dhSavedDecoPlansCache);
                    modalOpenDive.show();
                    return;
                }
                $.ajax({
                    url: '{{ route("DecoPlanner.listPlans") }}',
                    method: 'GET',
                    success: function (plans) {
                        dhSavedDecoPlansCache = plans;
                        dhRenderOpenDiveList(plans);
                        modalOpenDive.show();
                    },
                    error: function () {
                        alert('Could not load your saved dives - please try again.');
                    },
                });
            });
        }
    </script>

    
    @endpush
</x-page-template>

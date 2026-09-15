<x-page-template bodyClass='dh-shell bg-gray-200' :SEO="$SEO ?? []">
    <x-shell.nav active="me" />
    
    
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <!-- Navbar -->
        <x-shell.header title="Dive Planner" icon="timer" />
        <!-- End Navbar -->
        <div class="container-fluid py-0">

            <div class="d-none" data-color="info" id="sidebarColorDiv"></div>

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
                    background: linear-gradient(to right, 
                                                green 0% 33%, 
                                                yellow 33% 95%, 
                                                red 95% 100%);
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
                                    @endif
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
                                         embedded in the OC/CC pills. Always visible, even for guests, who
                                         get a clear message on click instead of the control just not
                                         being there. --}}
                                    <button type="button" class="dh-channel-chip dh-deco-searchrow-pill" id="saveDecoPrefsPill" style="flex: 0 0 auto; margin-left:auto;" title="Save GF Low/High and setpoint to your profile">
                                        <span class="material-icons-round" aria-hidden="true" style="font-size: 15px; vertical-align: -3px;">bookmark_border</span>
                                        {{-- Setpoint only applies to CC (OC has no CCR setpoint) - label
                                             matches whichever the diver is currently looking at (Pablo,
                                             2026-09-19: "the pill in OC needs to say Save GFs, the one in
                                             CC is correct as is"). showOpenCircuit/showClosedCircuit swap
                                             this text; default here matches the page's default OC mode. --}}
                                        <span id="saveDecoPrefsPillLabel">Save GFs</span>
                                    </button>
                                </div>
                            </div>
                            <div class="row mt-3 dh-deco-input-divider" style="padding-bottom: 10px;">
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
                                <div class="col-lg-4 col-12 dh-deco-input-divider">
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
                                                    <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                </div>
                                                <span class="dh-gas-unit">hrs</span>
                                            </div>
                                            <div class="slider-styled" id="surfaceTimeSlider" data-dh-num-mirror="1"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Col gradients -->                          
                                <div class="col-lg-4 col-12 dh-deco-input-divider">
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
                                <div class="col-lg-4 col-12 dh-deco-input-divider">
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
                                         36%, 21/31, 18/45 and 10/55") - visible to everyone, like every
                                         other card's presets; "My Gases" and the save icon stay behind
                                         isNotGuest() same as elsewhere, since only a registered diver has
                                         anywhere to save one. --}}
                                    <div class="dh-channel-picker dh-gas-picker dh-gas-preset-row" id="gasPresetBottomOC" data-slot="bottom">
                                        <button type="button" class="dh-channel-chip" data-o2="21" data-he="0">Air</button>
                                        <button type="button" class="dh-channel-chip" data-o2="32" data-he="0">32%</button>
                                        <button type="button" class="dh-channel-chip" data-o2="36" data-he="0">36%</button>
                                        <button type="button" class="dh-channel-chip" data-o2="21" data-he="31">21/31</button>
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

                                                    <div class="row mt-3" id="containerDeco1OCButton">
                                                        <div class="text-center" style="border: none;">
                                                            <a type="button" class="btn btn-info mt-0 w-100 mb-0" id="deco1DeleteButton" onclick="hideDecoGas1()">
                                                                Delete gas
                                                            </a>
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

                                                    <div class="row mt-3">
                                                        <div class="text-center" style="border: none;">
                                                            <a type="button" class="btn btn-info mt-0 w-100 mb-0" id="deco2DeleteButton" onclick="hideDecoGas2()">
                                                                Delete gas
                                                            </a>
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

                                                    <div class="row mt-3">
                                                        <div class="text-center" style="border: none;">
                                                            <a type="button" class="btn btn-info mt-0 w-100 mb-0" id="deco3DeleteButton" onclick="hideDecoGas3()">
                                                                Delete gas
                                                            </a>
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

                                                    <div class="row mt-3">
                                                        <div class="text-center" style="border: none;">
                                                            <a type="button" class="btn btn-info mt-0 w-100 mb-0" id="deco4DeleteButton" onclick="hideDecoGas4()">
                                                                Delete gas
                                                            </a>
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
                            <button type="button" class="dh-deco-header-btn dh-deco-searchrow-pill" id="exportDecoPlanPdfBtn" title="Export this decompression plan to PDF">
                                <span class="material-icons-round" aria-hidden="true">picture_as_pdf</span> Export PDF
                            </button>
                            @if(auth()->user()->isNotGuest())
                                <button type="button" class="dh-deco-header-btn dh-deco-searchrow-pill" id="saveDecoPlanBtn" title="Save this plan's inputs so you can regenerate it later">
                                    <span class="material-icons-round" aria-hidden="true">save</span> Save Plan
                                </button>
                            @endif
                        </div>

                        <div class="card-body">
                            <div>
                                <!-- Row for summary - plain "row" (not
                                     mx-0), same as the table/chart row right
                                     below it, so both line up at the same
                                     edges (Pablo, 2026-09-19). -->
                                <div class="row">
                                    <div class="col-12">
                                        <div class="dh-deco-summary">
                                            <span class="dh-gas-result-pill dh-deco-summary-pill">
                                                <span class="dh-deco-summary-pill-label">Run time</span>
                                                <span id="labelTotalRunTime">67</span>
                                            </span>
                                            <span class="dh-gas-result-pill dh-deco-summary-pill">
                                                <span class="dh-deco-summary-pill-label">Deco time</span>
                                                <span id="labelTotalDecoTime">28</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Row with deco table and profile chart -->
                                <div class="row mt-2">

                                    <div class="col-lg-6 col-12">
                                        <div class="label-container d-flex justify-content-center align-items-center" style="margin-top:20px;">
                                            <label class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center" id="decoTableTitle">Decompression Table</label>
                                        </div>
                                        <div id="decoTableContainer"></div>
                                        <div id="BOTableContainer" style="display: none;"></div>
                                    </div>

                                    <div class="col-lg-6 col-12" id="profileChartContainer">
                                        <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1"> 
                                            <canvas id="profileChart" class="chart-canvas border-radius-lg" height="500px"></canvas>
                                        </div>
                                        
                                    </div>
                                </div>
                                
                                <!-- What if row -->
                                <div class="row">
                                    <div class="col-lg-12 col-12 mt-2">
                                        <div class="dh-deco-section-head is-toggle is-collapsed" id="dh-whatif-toggle" role="button" tabindex="0" aria-expanded="false" aria-controls="dh-whatif-body">
                                            <span class="material-icons-round" aria-hidden="true">help_outline</span>
                                            <h4>What if...?</h4>
                                            <span class="material-icons-round dh-deco-section-head-chevron" id="dh-whatif-chevron" aria-hidden="true">expand_more</span>
                                        </div>
                                        <div class="dh-deco-section-body" id="dh-whatif-body" hidden>

                                            <div class="row mx-0 g-2">
                                                <div class="col-lg-4 col-6">
                                                    <div class="dh-whatif-chips">
                                                        <label class="dh-whatif-chip" for="filter1">
                                                            <input class="form-check-input" type="checkbox" id="filter1">
                                                            <span class="dh-whatif-chip-body" data-bs-toggle="tooltip" data-bs-placement="top" title="Extend the dive for 5 m, how's deco profile affected??">Extend bottom time 5 min</span>
                                                        </label>

                                                        <label class="dh-whatif-chip" for="filter2">
                                                            <input class="form-check-input" type="checkbox" id="filter2">
                                                            <span class="dh-whatif-chip-body" data-bs-toggle="tooltip" data-bs-placement="top" title="Dive 10 ft deeper, how is RT and DT changed?">Increase max depth by {{ $deco_unit ? "3 m" : "10 ft" }}</span>
                                                        </label>

                                                        <label class="dh-whatif-chip" for="filter7" id="filter7Container" style="display: none;">
                                                            <input class="form-check-input" type="checkbox" id="filter7">
                                                            <span class="dh-whatif-chip-body" data-bs-toggle="tooltip" data-bs-placement="top" title="calculate RT and deco time switching to BO">Bailout to OC</span>
                                                        </label>

                                                        <label class="dh-whatif-chip" for="filter3" id="filter3Container">
                                                            <input class="form-check-input" type="checkbox" id="filter3">
                                                            <span class="dh-whatif-chip-body" data-bs-toggle="tooltip" data-bs-placement="top" title="calculate RT and deco time using only backgas">Lost all deco gases</span>
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="col-lg-4 col-6">
                                                    <div class="dh-whatif-chips">
                                                        <label class="dh-whatif-chip" for="filter4">
                                                            <input class="form-check-input" type="checkbox" id="filter4">
                                                            <span class="dh-whatif-chip-body" data-bs-toggle="tooltip" data-bs-placement="top" title="How's the RT affected if the dive is shorter?">Shorten bottom time 5 min</span>
                                                        </label>

                                                        <label class="dh-whatif-chip" for="filter5">
                                                            <input class="form-check-input" type="checkbox" id="filter5">
                                                            <span class="dh-whatif-chip-body" data-bs-toggle="tooltip" data-bs-placement="top" title="What's the impact of diving 10 ft shallower than planned?">Reduce max depth by {{ $deco_unit ? "3 m" : "10 ft" }}</span>
                                                        </label>

                                                        <label class="dh-whatif-chip" for="filter6">
                                                            <input class="form-check-input" type="checkbox" id="filter6">
                                                            <span class="dh-whatif-chip-body" data-bs-toggle="tooltip" data-bs-placement="top" title="Minimum deco time">Minimum deco (GFs=100%)</span>
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="col-lg-3 col-12 mb-2 mt-2">
                                                    <div class="table-responsive" id="summaryWhatIfTable" hidden style="border: 1px solid var(--dh-line); border-radius: 10px;">
                                                        <table class="table align-items-center mb-0">
                                                            <tbody>
                                                                <tr class="align-top w-30"><td class="text-secondary text-end text-md font-weight-bolder opacity-7">New Run Time:</td>
                                                                <td class="align-middle text-wrap" style="text-align: left;">
                                                                    <span class="dh-gas-pill-wrap">
                                                                        <label class="dh-gas-result-pill is-compact" id="labelWhatIfRunTime">-</label>
                                                                        <span class="dh-gas-pill-badge" id="labelWhatIfRunTimeDiff">-</span>
                                                                    </span>
                                                                </td></tr>

                                                                <tr class="align-top w-30"><td class="text-secondary text-end text-md font-weight-bolder opacity-7">New Deco Time:</td>
                                                                <td class="align-middle text-wrap" style="text-align: left;">
                                                                    <span class="dh-gas-pill-wrap">
                                                                        <label class="dh-gas-result-pill is-compact" id="labelWhatIfDecoTime">-</label>
                                                                        <span class="dh-gas-pill-badge" id="labelWhatIfDecoTimeDiff">-</span>
                                                                    </span>
                                                                </td></tr>

                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                <script>
                                    // "What if...?" starts collapsed (Pablo, 2026-09-16), same
                                    // expand/collapse mechanics as the Inputs card. Placed here,
                                    // right after its own markup, rather than up with the Inputs
                                    // card's toggle script - that script tag runs before this
                                    // section of the DOM even exists, so getElementById returned
                                    // null and the click listener was silently never attached
                                    // (Pablo, 2026-09-16: "the what if collapsed is not expanding
                                    // when I click").
                                    (function () {
                                        var body = document.getElementById('dh-whatif-body');
                                        var toggle = document.getElementById('dh-whatif-toggle');
                                        var chevron = document.getElementById('dh-whatif-chevron');
                                        if (!body || !toggle) return;

                                        function setOpen(open) {
                                            body.hidden = !open;
                                            toggle.classList.toggle('is-collapsed', !open);
                                            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                                            if (chevron) chevron.textContent = open ? 'expand_less' : 'expand_more';
                                        }

                                        toggle.addEventListener('click', function () { setOpen(body.hidden); });
                                        toggle.addEventListener('keydown', function (e) {
                                            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); setOpen(body.hidden); }
                                        });
                                    })();

                                    // The "New Run Time / New Deco Time" frame only means anything
                                    // once a scenario pill is picked - hide it otherwise (Pablo,
                                    // 2026-09-16: "if there is no pill selected...hide the...frame").
                                    (function () {
                                        var summary = document.getElementById('summaryWhatIfTable');
                                        var checkboxes = document.querySelectorAll('.dh-whatif-chip input[type=checkbox]');
                                        if (!summary || !checkboxes.length) return;

                                        function update() {
                                            var anyChecked = Array.prototype.some.call(checkboxes, function (cb) { return cb.checked; });
                                            summary.hidden = !anyChecked;
                                        }

                                        checkboxes.forEach(function (cb) { cb.addEventListener('change', update); });
                                        window.dhUpdateWhatIfVisibility = update;
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

                                
                                <!-- Row for gas consumption -->
                                <div id="gasConsumptionRow" class="row mt-2">
                                    <div class="col-lg-12 col-12">
                                        <div class="dh-deco-section-head">
                                            <span class="material-icons-round" aria-hidden="true">local_gas_station</span>
                                            <h4 id="gasConsumptionHeader">Gas consumption</h4>
                                        </div>

                                        <div class="dh-deco-section-body">

                                            <div class="row mt-6" style="padding:10px;">
                                                <div id="gasConsumptionBottomCol" class="col-lg-6 col-12">
                                                    <table class="table align-items-center mb-0 mt-1">
                                                        <tr><td class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center" style="border: none;">Bottom gas</td> </tr>
                                                    </table>
                                                    <label class="dh-gas-label">SAC rate</label>
                                                    <div class="dh-gas-row">
                                                        <div id="labelSACBottomGasLitersWrap" class="dh-gas-input-wrap">
                                                            <div class="dh-gas-editable">
                                                                <input type="text" inputmode="decimal" class="dh-gas-input" id="labelSACBottomGasLiters" value="23">
                                                                <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                            </div>
                                                            <span class="dh-gas-unit">liters/min</span>
                                                        </div>
                                                        <div id="labelSACBottomGasWrap" class="dh-gas-input-wrap">
                                                            <div class="dh-gas-editable">
                                                                <input type="text" inputmode="decimal" class="dh-gas-input" id="labelSACBottomGas" value="0.8">
                                                                <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                            </div>
                                                            <span class="dh-gas-unit">cuft/min</span>
                                                        </div>
                                                        <div class="slider-styled" id="sliderSACBottomGas" data-dh-num-mirror="1"></div>
                                                    </div>

                                                    <div class="label-container d-flex justify-content-center align-items-center" style="margin-top:20px;">
                                                        <label class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center">Gas Consumption</label>
                                                    </div>
                                                    <div id="bottomGasConsumptionTableContainer"></div>
                                                </div>

                                                <div id="gasConsumptionDecoCol" class="col-lg-6 col-12">
                                                    <table class="table align-items-center mb-0 mt-1">
                                                        <tr><td id="gasConsumptionDecoOrBOHeader" class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center" style="border: none;">Decompression gases</td> </tr>
                                                    </table>
                                                    <label class="dh-gas-label">SAC rate</label>
                                                    <div class="dh-gas-row">
                                                        <div id="labelSACDecoGasLitersWrap" class="dh-gas-input-wrap">
                                                            <div class="dh-gas-editable">
                                                                <input type="text" inputmode="decimal" class="dh-gas-input" id="labelSACDecoGasLiters" value="14">
                                                                <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                            </div>
                                                            <span class="dh-gas-unit">liters/min</span>
                                                        </div>
                                                        <div id="labelSACDecoGasWrap" class="dh-gas-input-wrap">
                                                            <div class="dh-gas-editable">
                                                                <input type="text" inputmode="decimal" class="dh-gas-input" id="labelSACDecoGas" value="0.5">
                                                                <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                            </div>
                                                            <span class="dh-gas-unit">cuft/min</span>
                                                        </div>
                                                        <div class="slider-styled" id="sliderSACDecoGas" data-dh-num-mirror="1"></div>
                                                    </div>

                                                    <div class="label-container d-flex justify-content-center align-items-center" style="margin-top:20px;">
                                                        <label class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center" id="decoTableTitle">Gas Consumption</label>
                                                    </div>
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
            

            document.getElementById("ascRateContainerImp").style.display = "flex";
            document.getElementById("desRateContainerImp").style.display = "flex";
            document.getElementById("maxDepthContainerImp").style.setProperty("display", "flex", "important");

            document.getElementById("labelSACBottomGasLitersWrap").style.display = "none";
            document.getElementById("labelSACDecoGasLitersWrap").style.display = "none";
            document.getElementById("labelSACBottomGasWrap").style.display = "flex";
            document.getElementById("labelSACDecoGasWrap").style.display = "flex";

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

            document.getElementById("ascRateContainerImp").style.display = "none";
            document.getElementById("desRateContainerImp").style.display = "none";
            document.getElementById("maxDepthContainerImp").style.setProperty("display", "none", "important");

            document.getElementById("labelSACBottomGasLitersWrap").style.display = "flex";
            document.getElementById("labelSACDecoGasLitersWrap").style.display = "flex";
            document.getElementById("labelSACBottomGasWrap").style.display = "none";
            document.getElementById("labelSACDecoGasWrap").style.display = "none";

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

            // Cached for "Export PDF" (the inputs table) and "Save Plan" -
            // this object already has every input the diver entered,
            // gases included, so there's no need to re-scrape the DOM for
            // either feature (Pablo, 2026-09-18).
            window.lastDiveProfile = diveProfile;


            <?php
                $DivePlannerKey = base64_decode(env('DIVE_PLANNING_API_KEY'));
            ?>
            // Make the AJAX POST request
            $.ajax({
                LOCALurl: ` http://localhost:7071/api/DecoPlanner`,
                url: `https://decoplanningapi.azurewebsites.net/api/DecoPlanner?code=<?php echo $DivePlannerKey; ?>`,
                method: 'POST',
                contentType: 'application/json',  // Ensures JSON format
                data: JSON.stringify({inputs: diveProfile}), // Converts data to JSON
                crossDomain: true,  // Explicitly allow CORS
                success: function (response) {
                    console.log('Success:', response);
                    // make a copy of the basline response to have to calculate gas consumption
                    //baselineResponse = response['baseline'];
                    //bailoutResponse = response['bailout'];
                    globalResponse = response;

                    renderProfileChart(response);
                    baselineRTDT = generateDecoTable(response['baseline']);
                    
                    // generate BO table
                    if (modeOCOrCC == "CC")
                        generateDecoTable(response['bailout'], 1);

                    if(baselineRTDT[1] == 0){  //No deco, we hide the table and adjust the size of the chart to col-12
                        document.getElementById("decoTableContainer").style.display = "none";
                        document.getElementById("profileChartContainer").className = "col-lg-12 col-12";

                    } else {
                        document.getElementById("decoTableContainer").style.display ="block";
                        document.getElementById("profileChartContainer").className = "col-lg-6 col-12";
                    }
                    filter1RTDT = calculateDecoTime(response['add5min']);
                    filter2RTDT = calculateDecoTime(response['add10ft']);
                    filter3RTDT = calculateDecoTime(response['lostDecoGas']);
                    filter4RTDT = calculateDecoTime(response['short5min']);
                    filter5RTDT = calculateDecoTime(response['short10ft']);
                    filter6RTDT = calculateDecoTime(response['minDeco']);
                    filter7RTDT = calculateDecoTime(response['bailout']);
                    
                    // update timeLapse tissue data
                    conveyor = response['conveyor'];
                    console.log(conveyor.length);
                    timeLapseSlider.noUiSlider.updateOptions({
                        range: {
                            'min': 0,
                            'max': conveyor.length-1 // Set max dynamically
                        }
                    });

                    // calculate Gas consumption
                    sliderSACDecoGas.noUiSlider.set(0.5);
                    sliderSACBottomGas.noUiSlider.set(0.8);
                    if (modeOCOrCC == "OC") {
                        document.getElementById("gasConsumptionRow").style.display="block";
                        gasConsumption = calculateGasConsumption(response['baseline']);
                        renderGasConsumptionTable(gasConsumption, "bottom");
                        renderGasConsumptionTable(gasConsumption, "deco");
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

                    @if($deco_unit)
                        document.getElementById('labelSACBottomGasWrap').style.display="none";
                        document.getElementById('labelSACBottomGasLitersWrap').style.display="flex";
                        document.getElementById('labelSACDecoGasWrap').style.display="none";
                        document.getElementById('labelSACDecoGasLitersWrap').style.display="flex";
                    @else
                        document.getElementById('labelSACBottomGasWrap').style.display="flex";
                        document.getElementById('labelSACBottomGasLitersWrap').style.display="none";
                        document.getElementById('labelSACDecoGasWrap').style.display="flex";
                        document.getElementById('labelSACDecoGasLitersWrap').style.display="none";
                    @endif
                },
                error: function (xhr, status, error) {
                    console.error('Error:', error);
                    console.error('Response:', xhr.responseText); // Debugging response
                }
            });

            
        });

        

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

            depth = parseInt(labelDepth.value);

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
            depth = parseInt(labelDepth.value);
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

            // update dil PPO2 slider
            var O2At12 = 1.2 / (depth / 33 +1) * 100;
            var O2AtSetpoint = (parseFloat(setpointSliderValue) - 0.1) / (depth / 33 +1) * 100;
            var O2SliderMaxValue = Math.min(O2At12, O2AtSetpoint)
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

            // update the CC portion on the bailout
            labelBailoutSwitchPPO2.textContent = ((depth / 33 +1) * parseInt(labelDecoGas1O2.value) /100).toFixed(2);

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

            // change on CCvar ambientPressure = depth / 33 +1;
            var ambientPressure = (depth /33 +1);
            var bottomGasPPHe = ambientPressure * parseInt(labelDecoGas1He.value) / 100;
            var bottomGasENDPressure = ambientPressure - bottomGasPPHe;
            bottomGasEND = (bottomGasENDPressure - 1 ) * 33;

            labelBailoutEND = document.getElementById("labelBailoutEND");
            labelBailoutEND.textContent = Math.max(0,(bottomGasEND / {{ $deco_unit ? 3.28084 : 1}}).toFixed(0));


            if (labelBailoutEND.textContent > 130) {
                labelBailoutEND.classList.remove("is-warn");
                labelBailoutEND.classList.add("is-danger");
            } else if (labelBailoutEND.textContent > 100) {
                labelBailoutEND.classList.remove("is-danger");
                labelBailoutEND.classList.add("is-warn");
            } else {
                labelBailoutEND.classList.remove("is-warn", "is-danger");
                labelBailoutEND.classList.add("is-safe");
            }

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
                labelBailoutSwitch.textContent = parseInt(depthSliderValue);    // update bailout switching depth
                depthSliderValue = parseInt(depthSliderValue);
            } else {
                labelDepth.value = parseInt(depthSliderValue * 3.281);
                labelDepthMET.value = parseInt(depthSliderValue);
                labelBailoutSwitch.textContent = parseInt(depthSliderValue);    // update bailout switching depth
                depthSliderValue = parseInt(depthSliderValue * 3.281);
            }

            
            //bottomGasO2Slider.noUiSlider.set(bottomGasO2Slider.noUiSlider.get());
            //bottomGasHeSlider.noUiSlider.set(bottomGasHeSlider.noUiSlider.get());
            console.log("Setpoint: " + setPointOCOrCC);
            bottomGasO2Slider.noUiSlider.set(Math.round((setPointOCOrCC / (depthSliderValue / 33 + 1) * 100)));
            bottomGasHeSlider.noUiSlider.set(((1 - ((80 / 33) +1) / (depthSliderValue / 33 + 1)) * 100).toFixed(0));

            if(modeOCOrCC == "CC") {
                decoGas1O2Slider.noUiSlider.updateOptions({
                    start: Math.min(100, 1.4 / (depth /33 +1) * 100),
                    range: {
                        'min': 5, //parseFloat(Math.max(5, (10 / 33 + 1) * Math.floor(decoGas1O2SliderValue / 100).toFixed(1))),    // Keep the minimum value as is
                        'max': Math.min(100, 1.6 / (depth /33 +1) * 100),
                    }
                });
                decoGas1HeSlider.noUiSlider.updateOptions({
                    start: ((1 - ((80 / 33) +1) / (depthSliderValue / 33 + 1)) * 100).toFixed(0)
                });

            } else {
                decoGas1O2Slider.noUiSlider.updateOptions({
                    start: 50,
                    range: {
                        'min': 5, //parseFloat(Math.max(5, (10 / 33 + 1) * Math.floor(decoGas1O2SliderValue / 100).toFixed(1))),    // Keep the minimum value as is
                        'max': 100, //Math.min(100, 1.6 / (depth /33 +1) * 100),
                    }
                });    
                decoGas1HeSlider.noUiSlider.updateOptions({
                    start: 0
                });
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
        function computeOCGasSwitchPoints(baseline, firstSwitchAtMaxDepth) {
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
            var lastAscentBeforeDeco = null;
            for (var i = 0; i < baseline.length; i++) {
                if (baseline[i].phase === 'ascent') lastAscentBeforeDeco = baseline[i];
                if (baseline[i].phase === 'deco_stop') break;
            }
            consider(lastAscentBeforeDeco, firstSwitchAtMaxDepth ? 'prevEntry' : 'entry');

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
        function buildGasSwitchAnnotations(baseline, unitConversion, firstSwitchAtMaxDepth) {
            var annotations = {};
            computeOCGasSwitchPoints(baseline, firstSwitchAtMaxDepth).forEach(function (point, idx) {
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

        function renderProfileChart(response) {
            
            let unitConversion = 33;
            if (modeImpOrMetric == "met")
                unitConversion = 10;
            // Convert data into correct format for a scatter plot
            formattedData = response['baseline'].map(item => ({ x: item.time, y: -(item.abs_p - 1) * unitConversion }));

            var gasSwitchAnnotations = modeOCOrCC === "OC" ? buildGasSwitchAnnotations(response['baseline'], unitConversion) : {};
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

            // Create Chart.js Scatter Plot (correctly scaled x-axis)
            profileChartInstance = new Chart(ctx, {
                type: 'scatter', // Scatter ensures proportional spacing of points
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
    
    {{-- Script to show decompression table --}}
    <script>

        function getPhaseIcon(phase) {
            const icons = {
                "descent": "south", // Down arrow for descent
                "const": "swap_horiz", // Stopwatch for bottom time
                "ascent": "north", // Up arrow for ascent
                "deco_stop": "pause_circle", // Pause for deco stops
            };
            return `<span class="material-icons-round">${icons[phase] || "help"}</span>`; // Default to "help" if missing
        }

        function generateDecoTable(response, BO=0) {
            let tableData = []; // Store table rows
            let prevTime = 0; // Track cumulative runtime

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
                var o2 = gasArray[1], he = gasArray[3];
                if (he == 0) {
                    return '<span class="dh-gas-split-pill is-solo"><label class="dh-gas-result-pill is-o2 is-compact">' + o2 + '</label></span>';
                }
                return '<span class="dh-gas-split-pill"><label class="dh-gas-result-pill is-o2 is-compact">' + o2 + '</label><label class="dh-gas-result-pill is-he is-compact">' + he + '</label></span>';
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
            // Step 1: Add descent row
            let descent = response.find(r => r.phase === "descent");
            if (descent) {
                tableData.push({
                    phase: "descent",
                    depth: absPressureToDepth(descent.abs_p),
                    time: descent.time, 
                    runtime: prevTime + descent.time,
                    gas: modeOCOrCC == "OC" ? formatGas(descent.gas) : "-",
                    gf: descent.gf,
                    mode: modeOCOrCC,
                    ppo2: parseFloat((descent.gas[1])/100 * descent.abs_p).toFixed(2)
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
                    gas: modeOCOrCC == "OC" ? formatGas(bottomTime.gas) : "-",
                    gf: bottomTime.gf,
                    mode: modeOCOrCC,
                    ppo2: parseFloat((bottomTime.gas[1])/100 * bottomTime.abs_p).toFixed(2)
                });
                //prevTime += bottomTime.time;
                prevTime = bottomTime.time;
            }

            // Find the last "ascent" that occurs before any "deco_stop"
            let lastAscentBeforeDeco = null;
            let BOGas = null;

            for (let i = 0; i < response.length; i++) {
                if (response[i].phase === "ascent") {
                    lastAscentBeforeDeco = response[i]; // Keep updating with the last ascent found
                }
                // look for the BO gas
                if (response[i].phase === "gas_switch" && BOGas == null) {
                    BOGas = response[i].gas;
                }
                if (response[i].phase === "deco_stop") {
                    response = response.slice(i); // Keeps all phases from index i to the end
                    break; // Stop when we hit the first deco stop
                }
            }

            let stepMode = modeOCOrCC;
            if(BO && modeOCOrCC === "CC") {
                stepMode = "OC";
            }

            if (lastAscentBeforeDeco) {
                tableData.push({
                    phase: "ascent",
                    depth: absPressureToDepthDeco(lastAscentBeforeDeco.abs_p),
                    time: lastAscentBeforeDeco.time - prevTime, // Time taken for the last ascent before deco stop
                    runtime: lastAscentBeforeDeco.time, // Run time should be the time of the last ascent
                    gas: BO && modeOCOrCC === "CC" ? formatGas(BOGas) : formatGas(lastAscentBeforeDeco.gas),
                    gf: lastAscentBeforeDeco.gf,
                    mode: stepMode,
                    ppo2: parseFloat((lastAscentBeforeDeco.gas[1])/100 * lastAscentBeforeDeco.abs_p).toFixed(2)
                });
                prevTime = lastAscentBeforeDeco.time;
            }

            let firstDecoStopIndex = response.findIndex(r => r.phase === "deco_stop");
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
                            gf: entry.gf,
                            mode: stepMode,
                            ppo2: parseFloat((entry.gas[1])/100 * entry.abs_p).toFixed(2)
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
                        gf: lastAscent.gf,
                        mode: stepMode,
                        ppo2: parseFloat((lastAscent.gas[1])/100 * (0 /33 + 1)).toFixed(2)
                    });

                    totalRuntime = lastAscent.time;
                }

                // Remove processed phases from `response`
                response = response.slice(firstDecoStopIndex);
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
                                <th class="text-sm" style="width: 32%; padding-left: 0px; padding-right:0px;">Gas</th>
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
                                <th class="text-xs" style="width: 7%; padding-left: 0px;">M</th>
                                <th class="depth-column text-sm" style="width: 11%; padding-left: 0px; padding-right:0px;">Depth</th>
                                <th class="text-sm" style="width: 8%; padding-left: 0px; padding-right:0px;">Time</th>
                                <th class="text-sm" style="width: 8%; padding-left: 0px; padding-right:0px;">RT</th>
                                <th class="text-sm" style="width: 30%; padding-left: 0px; padding-right:0px;">Gas</th>
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

        function calculateDecoTime(response) {
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
                        borderColor: '#1A73E8',
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
                        borderColor: '#1A73E8',
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
                profileChartInstance.options.plugins.annotation.annotations = {};
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
                        borderColor: '#1A73E8',
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
                profileChartInstance.options.plugins.annotation.annotations = buildGasSwitchAnnotations(globalResponse['lostDecoGas'], unitConversion3, true);

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
                        borderColor: '#1A73E8',
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
                        borderColor: '#1A73E8',
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
                        borderColor: '#1A73E8',
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
                // diluent itself never changes mid-dive).
                profileChartInstance.options.plugins.annotation.annotations = {};
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
                        borderColor: '#1A73E8',
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
                profileChartInstance.options.plugins.annotation.annotations = buildGasSwitchAnnotations(globalResponse['bailout'], unitConversion, true);

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

            profileChartInstance.update(); // Refresh chart

            // update gas consumption
            document.getElementById("gasConsumptionRow").style.display="block";
            gasConsumption = calculateGasConsumption(globalResponse['bailout']);
            // no need to render bottom gas (it's on CC)
            renderGasConsumptionTable(gasConsumption, "deco");
            


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
            const containerDeco1OCButton = document.getElementById("containerDeco1OCButton");
            const containerDeco1CCInfo = document.getElementById("containerDeco1CCInfo");

                
            

            function showOpenCircuit() {
                tankDouble.style.display = "block";
                tankCCR.style.display = "none";
                labelBottomGasOrDiluent.innerText = "bottom gas";
                containerSetPoint.style.display="none";

                // set model to mode OC
                modeOCOrCC = "OC";
                // OC has no CCR setpoint, so the save pill only mentions GFs.
                document.getElementById('saveDecoPrefsPillLabel').textContent = 'Save GFs';
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
                containerDeco1OCButton.style.display = "block";
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
                // CC has a setpoint, so the save pill covers both.
                document.getElementById('saveDecoPrefsPillLabel').textContent = 'Save GFs & Setpoint';
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
                containerDeco1OCButton.style.display = "none";
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
                        annotations: {
                            inspiredPressureInertGas: {
                                type: "line",
                                xMin: 30,
                                xMax: 30,
                                borderColor: "black",
                                borderWidth: 2,
                                label: {
                                    enabled: true,
                                    content: "100%",
                                    position: "top"
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
        // Define M0 and dM values for ZHL-16C (16 compartments)
        const MValues = [
            { M0: 1.83, dM: 0.56 },
            { M0: 1.81, dM: 0.55 },
            { M0: 1.79, dM: 0.54 },
            { M0: 1.75, dM: 0.52 },
            { M0: 1.71, dM: 0.51 },
            { M0: 1.68, dM: 0.50 },
            { M0: 1.65, dM: 0.49 },
            { M0: 1.62, dM: 0.48 },
            { M0: 1.61, dM: 0.47 },
            { M0: 1.60, dM: 0.46 },
            { M0: 1.55, dM: 0.45 },
            { M0: 1.50, dM: 0.44 },
            { M0: 1.46, dM: 0.43 },
            { M0: 1.41, dM: 0.42 },
            { M0: 1.37, dM: 0.41 },
            { M0: 1.33, dM: 0.40 }
        ];

        /**
         * Calculate the maximum tolerable nitrogen pressure for a given compartment.
         * @param {number} compartment - Compartment number (1-16)
         * @param {number} ambientPressure - Absolute ambient pressure (ATA)
         * @returns {number} - Maximum allowable nitrogen partial pressure in ATA
         */
        function calculateMValueN2(compartment, ambientPressure) {
            if (compartment < 1 || compartment > 16) {
                throw new Error("Invalid compartment number. Must be between 1 and 16.");
            }

            const { M0, dM } = MValues[compartment - 1]; // Get M-values for the selected compartment
            return M0 + (dM * ambientPressure); // Bühlmann M-value formula
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

            let tissueValues = [];
            for(i=0; i<16; i++) {
                if(conveyor[index].tissue_p[i][0] <= conveyor[index].abs_p)
                    //const tissueValues = conveyor[index].tissue_p.map(tissue => (tissue[0]) / (conveyor[index].abs_p) * 100);
                    tissueValues[i] = conveyor[index].tissue_p[i][0] / conveyor[index].abs_p * 100;
                else
                    tissueValues[i] = 100 + conveyor[index].tissue_p[i][0] / calculateMValueN2(i+1, conveyor[index].abs_p) * 195;
            }
            //console.log(tissueValues); // Check the extracted values

            tissueChartInstance.data.datasets[0].data = tissueValues;

            tissueChartInstance.options.plugins.annotation.annotations.inspiredPressureInertGas.xMin =  (100 - conveyor[index].gas[1] - conveyor[index].gas[3]);
            tissueChartInstance.options.plugins.annotation.annotations.inspiredPressureInertGas.xMax =  (100 - conveyor[index].gas[1] - conveyor[index].gas[3]);

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
        function calculateGasConsumption(diveData) {
            const sacBottomGas = parseFloat(document.getElementById('labelSACBottomGas').value);
            const sacDecoGas = parseFloat(document.getElementById('labelSACDecoGas').value);

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
                const sacRate = isDecoGas ? sacDecoGas : sacBottomGas;
                const gasType = isDecoGas ? "deco" : "bottom";

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
        
        function renderGasConsumptionTable(gasConsumption, filterType) {
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
                    <th class="text-sm" style="padding-left: 0px; padding-right:0px;">Volume {{ $deco_unit ? "(liters)" : "(cuft)" }}</th>
                </tr>
            `;
            table.appendChild(thead);

            // Create table body with filtering logic
            const tbody = document.createElement("tbody");
            gasConsumption.forEach(entry => {
                if (filterType === "bottom" && entry.type !== "bottom") return;
                if (filterType === "deco" && entry.type !== "deco") return;

                const row = document.createElement("tr");
                row.innerHTML = `
                    <td>${entry.gas}</td>
                    <td>${(entry.volume * ({{ $deco_unit ? 28.3168 : 1 }})).toFixed(2)}</td>
                `;
                tbody.appendChild(row);
            });
            table.appendChild(tbody);

            // Append table to wrapper
            tableWrapper.appendChild(table);
            container.appendChild(tableWrapper);
        }
    </script>

    {{-- Scripts to manage SAC sliders --}}
    <script>
        var sliderSACBottomGas = document.getElementById('sliderSACBottomGas');
        var labelSACBottomGas = document.getElementById('labelSACBottomGas');
        var labelSACBottomGasLiters = document.getElementById('labelSACBottomGasLiters');
        


        noUiSlider.create(sliderSACBottomGas, {
            start: 0.8,
            connect: [true, false],
            range: {
                'min': 0.3,
                'max': 4
            },
            step: 0.1,
            

        });

        // Hide the tick mark labels
        var sliderSACBottomGasTicks = sliderSACBottomGas.querySelectorAll('.noUi-value-sub');
        sliderSACBottomGasTicks.forEach(function (sliderSACBottomGas) {
            sliderSACBottomGas.style.display = 'none';
        });

        sliderSACBottomGas.noUiSlider.on('update', function (values, handle) {
            var sliderSACBottomGasValue = values[handle];
            labelSACBottomGas.value = parseFloat(sliderSACBottomGasValue).toFixed(1);
            labelSACBottomGasLiters.value = parseFloat(sliderSACBottomGasValue * 28.3168).toFixed(0);

            // update gas consumption table
            if(globalResponse != null) {
                if(modeOCOrCC === "OC") {
                    gasConsumption = calculateGasConsumption(globalResponse['baseline']);
                    renderGasConsumptionTable(gasConsumption, "bottom");
                }
            }

        });

        labelSACBottomGas.addEventListener('change', function () {
            var typed = parseFloat(labelSACBottomGas.value);
            if (isNaN(typed)) { labelSACBottomGas.value = parseFloat(sliderSACBottomGas.noUiSlider.get()).toFixed(1); return; }
            sliderSACBottomGas.noUiSlider.set(typed);
        });
        labelSACBottomGasLiters.addEventListener('change', function () {
            var typed = parseFloat(labelSACBottomGasLiters.value);
            if (isNaN(typed)) { labelSACBottomGasLiters.value = parseFloat(sliderSACBottomGas.noUiSlider.get() * 28.3168).toFixed(0); return; }
            sliderSACBottomGas.noUiSlider.set(typed / 28.3168);
        });

        var sliderSACDecoGas = document.getElementById('sliderSACDecoGas');
        var labelSACDecoGas = document.getElementById('labelSACDecoGas');
        var labelSACDecoGasLiters = document.getElementById('labelSACDecoGasLiters');


        noUiSlider.create(sliderSACDecoGas, {
            start: 0.5,
            connect: [true, false],
            range: {
                'min': 0.3,
                'max': 4
            },
            step: 0.1,
            

        });

        // Hide the tick mark labels
        var sliderSACDecoGasTicks = sliderSACDecoGas.querySelectorAll('.noUi-value-sub');
        sliderSACDecoGasTicks.forEach(function (sliderSACDecoGas) {
            sliderSACDecoGas.style.display = 'none';
        });

        sliderSACDecoGas.noUiSlider.on('update', function (values, handle) {
            console.log("paso");
            var sliderSACDecoGasValue = values[handle];
            labelSACDecoGas.value = parseFloat(sliderSACDecoGasValue).toFixed(1);
            labelSACDecoGasLiters.value = parseFloat(sliderSACDecoGasValue * 28.3168).toFixed(0);

            // update gas consumption table
            if (globalResponse != null) {
                if(modeOCOrCC === "OC") {
                    gasConsumption = calculateGasConsumption(globalResponse['baseline']);
                    renderGasConsumptionTable(gasConsumption, "deco");
                } else {
                    gasConsumption = calculateGasConsumption(globalResponse['bailout']);
                    renderGasConsumptionTable(gasConsumption, "deco");
                }

            }


        });

        labelSACDecoGas.addEventListener('change', function () {
            var typed = parseFloat(labelSACDecoGas.value);
            if (isNaN(typed)) { labelSACDecoGas.value = parseFloat(sliderSACDecoGas.noUiSlider.get()).toFixed(1); return; }
            sliderSACDecoGas.noUiSlider.set(typed);
        });
        labelSACDecoGasLiters.addEventListener('change', function () {
            var typed = parseFloat(labelSACDecoGasLiters.value);
            if (isNaN(typed)) { labelSACDecoGasLiters.value = parseFloat(sliderSACDecoGas.noUiSlider.get() * 28.3168).toFixed(0); return; }
            sliderSACDecoGas.noUiSlider.set(typed / 28.3168);
        });
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
            var wrap = document.createElement('span');
            wrap.className = 'dh-gas-split-pill' + (he === 0 ? ' is-solo' : '');
            wrap.style.verticalAlign = 'middle';
            var o2Pill = document.createElement('label');
            o2Pill.className = 'dh-gas-result-pill is-o2' + sizeClass;
            o2Pill.textContent = o2;
            wrap.appendChild(o2Pill);
            if (he !== 0) {
                var hePill = document.createElement('label');
                hePill.className = 'dh-gas-result-pill is-he' + sizeClass;
                hePill.textContent = he;
                wrap.appendChild(hePill);
            }
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
                '<div style="font-size:15px; opacity:.8; margin-top:6px;">created on ' + new Date().toLocaleString() + '</div>' +
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
                '<b>Asc/Dsc rates:</b> ' + profile.rate.descent + '/' + profile.rate.ascent + ' ' + dhFormatRateUnit(),
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

            function addChip(label, o2, he) {
                // Compact pills (24px) - same size as every other gas pill
                // in the PDF (Gas column, Gas Consumption, Gas switches),
                // and a tighter wrapper around them, not the earlier
                // full-size pill in a generously-padded chip (Pablo,
                // 2026-09-19: "the pills with the gases...are two large.
                // Make sure that all gas split pills have the same size in
                // all the pdf. The wrapper needs to be more tight").
                var chip = document.createElement('div');
                chip.style.cssText = 'display:flex; align-items:center; gap:6px; border:1.5px solid #0b2a3a; border-radius:999px; padding:3px 10px 3px 12px; flex:0 0 auto;';
                var labelSpan = document.createElement('span');
                // Matches the compact split pill's own 24px height exactly,
                // instead of trusting the flex row's align-items:center -
                // html2canvas has a known habit of mis-centering flex
                // children of different heights (Pablo, 2026-09-19: "result
                // pills are showing slightly above the text before them").
                labelSpan.style.cssText = 'display:inline-flex; align-items:center; height:24px; font-weight:700; font-size:12px; color:#0b2a3a; white-space:nowrap;';
                labelSpan.textContent = label + ':';
                chip.appendChild(labelSpan);
                chip.appendChild(dhBuildPdfSplitPill(o2, he, true));
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
                '<th style="text-align:right; padding:4px 4px 8px; font-weight:700; color:#5a6b78; font-size:10px; text-transform:uppercase; letter-spacing:.03em;">' + volumeHeaderText + '</th>' +
                '</tr>';
            table.appendChild(thead);

            var tbody = document.createElement('tbody');
            sourceRows.forEach(function (sourceRow) {
                var cells = sourceRow.querySelectorAll('td');
                if (cells.length < 2) return;
                var mix = dhParseGasMixString(cells[0].textContent);
                var tr = document.createElement('tr');
                var tdGas = document.createElement('td');
                tdGas.style.cssText = 'padding:6px 4px; text-align:left; background:#fff;';
                tdGas.appendChild(dhBuildPdfSplitPill(mix.o2, mix.he, true));
                var tdVolume = document.createElement('td');
                tdVolume.style.cssText = 'padding:6px 4px; text-align:right; background:#fff; font-weight:700; color:#0b2a3a;';
                tdVolume.textContent = cells[1].textContent.trim();
                tr.appendChild(tdGas);
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
        function dhBuildPdfGasSwitchTable(baseline) {
            var wrap = document.createElement('div');
            var points = computeOCGasSwitchPoints(baseline, false);
            if (!points.length) return wrap;

            // Same font/color as the Decompression Table/Chart headers, but
            // with a blue (not navy) underline to set it apart as a
            // secondary section (Pablo, 2026-09-19: "use the same font and
            // color that Decompression table or Decompression chart...add
            // the line below in blue").
            wrap.appendChild(dhBuildPdfColumnHeader('Gas switches', '#0e7c9e'));

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
                tdRT.textContent = Math.ceil(point.time) + ' m';
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
        function dhBuildPdfDiveTimesSection() {
            var wrap = document.createElement('div');
            wrap.appendChild(dhBuildPdfColumnHeader('Dive times'));

            var row = document.createElement('div');
            row.style.cssText = 'display:flex; align-items:center; gap:20px; margin-bottom:18px;';

            function addTimePill(label, value) {
                var chip = document.createElement('span');
                chip.style.cssText = 'display:inline-flex; align-items:center; gap:8px; flex:0 0 auto;';
                var labelSpan = document.createElement('span');
                labelSpan.style.cssText = 'display:inline-flex; align-items:center; height:32px; font-weight:700; font-size:13px; color:#0b2a3a; white-space:nowrap;';
                labelSpan.textContent = label;
                chip.appendChild(labelSpan);
                var pill = document.createElement('label');
                pill.className = 'dh-gas-result-pill';
                pill.textContent = value;
                chip.appendChild(pill);
                row.appendChild(chip);
            }

            addTimePill('Run time', document.getElementById('labelTotalRunTime').textContent.trim());
            addTimePill('Deco time', document.getElementById('labelTotalDecoTime').textContent.trim());

            wrap.appendChild(row);
            return wrap;
        }

        function dhBuildPdfColumns(profile, isCC, mainChartUrl, baseline) {
            var row = document.createElement('div');
            row.style.cssText = 'display:flex; gap:30px; padding:12px 48px 34px; flex:1 1 auto; min-height:0;';

            var col1 = document.createElement('div');
            col1.style.cssText = 'flex:1 1 0; min-width:0;';
            col1.appendChild(dhBuildPdfColumnHeader('Decompression Table'));
            col1.appendChild(dhBuildPdfTableClone('decoTableContainer'));
            row.appendChild(col1);

            var col2 = document.createElement('div');
            col2.style.cssText = 'flex:1 1 0; min-width:0;';
            col2.appendChild(dhBuildPdfDiveTimesSection());
            col2.appendChild(dhBuildPdfColumnHeader('Decompression Chart'));
            var chartCard = document.createElement('div');
            chartCard.style.cssText = 'background:#0b2a3a; border-radius:14px; padding:16px;';
            var chartImg = document.createElement('img');
            chartImg.src = mainChartUrl;
            chartImg.style.cssText = 'width:100%; display:block;';
            chartCard.appendChild(chartImg);
            col2.appendChild(chartCard);
            if (!isCC && baseline) col2.appendChild(dhBuildPdfGasSwitchTable(baseline));
            row.appendChild(col2);

            var col3 = document.createElement('div');
            col3.style.cssText = 'flex:1 1 0; min-width:0;';
            if (isCC) {
                col3.appendChild(dhBuildPdfColumnHeader('Bailout Table'));
                col3.appendChild(dhBuildPdfTableClone('BOTableContainer'));
            } else {
                col3.appendChild(dhBuildPdfColumnHeader('Gas Consumption'));
                var sub1 = document.createElement('div');
                sub1.style.cssText = 'font-weight:700; font-size:12px; color:#5a6b78; text-transform:uppercase; letter-spacing:.03em; margin:4px 0 6px;';
                sub1.textContent = 'Bottom gas';
                col3.appendChild(sub1);
                col3.appendChild(dhBuildPdfGasConsumptionTable('bottomGasConsumptionTableContainer'));
                var sub2 = document.createElement('div');
                sub2.style.cssText = 'font-weight:700; font-size:12px; color:#5a6b78; text-transform:uppercase; letter-spacing:.03em; margin:12px 0 6px;';
                sub2.textContent = 'Decompression gases';
                col3.appendChild(sub2);
                col3.appendChild(dhBuildPdfGasConsumptionTable('decoGasConsumptionTableContainer'));
            }
            row.appendChild(col3);

            return row;
        }

        function dhBuildPdfPage(profile, isCC, mainChartUrl, baseline) {
            var page = document.createElement('div');
            page.style.cssText = 'position:fixed; left:-99999px; top:0; width:' + DH_PDF_PAGE_W + 'px; height:' + DH_PDF_PAGE_H + 'px; background:#ffffff; font-family: "Roboto", Arial, sans-serif; display:flex; flex-direction:column; overflow:hidden;';
            page.appendChild(dhBuildPdfHeader(profile, isCC));
            page.appendChild(dhBuildPdfDisclaimer());
            page.appendChild(dhBuildPdfGases(profile, isCC));
            page.appendChild(dhBuildPdfColumns(profile, isCC, mainChartUrl, baseline));
            document.body.appendChild(page);
            return page;
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

                page = dhBuildPdfPage(profile, isCC, mainChartUrl, globalResponse['baseline']);

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

        document.getElementById('exportDecoPlanPdfBtn').addEventListener('click', dhExportDecoPlanToPDF);

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
                var metaLine = document.createElement('span');
                metaLine.style.cssText = 'font-size: .72rem; opacity: .75;';
                metaLine.textContent = plan.mode + ' · ' + (plan.inputs.maxDepth || '?') + dhFormatDepthUnit() + ' · ' + dhFormatSavedPlanWhen(plan.created_at);
                pick.appendChild(titleLine);
                pick.appendChild(metaLine);
                pick.addEventListener('click', function () {
                    dhLoadSavedDecoPlan(plan);
                    modalOpenDive.hide();
                });

                var del = document.createElement('button');
                del.type = 'button';
                del.className = 'dh-mygases-delete';
                del.title = 'Delete this saved dive';
                var delIcon = document.createElement('span');
                delIcon.className = 'material-icons-round';
                delIcon.setAttribute('aria-hidden', 'true');
                delIcon.textContent = 'delete_outline';
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

            // Always click the target tab, even if already active - its
            // handler (showOpenCircuit/showClosedCircuit) is what resets
            // every gas slot back to empty, which this load needs whether
            // or not the mode itself is actually changing.
            (isCC ? closedCircuitTab : openCircuitTab).click();

            dhSetFieldValue('labelDepth', inputs.maxDepth);
            dhSetFieldValue('labelBottomTime', inputs.bottomTime);
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

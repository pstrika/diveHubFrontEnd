<x-page-template bodyClass='g-sidenav-show  bg-gray-200'>
    <x-auth.navbars.sidebar activePage="planningTools" activeItem="decoPlanner" activeSubitem=""></x-auth.navbars.sidebar>
    
    
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <!-- Navbar -->
        <x-auth.navbars.navs.auth pageTitle="Dive Planner"></x-auth.navbars.navs.auth>
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

            {{--modal guest--}}
            <div class="modal fade" id="modal_logged_as_guest" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="modal-notification" aria-hidden="true">
                <div class="modal-dialog modal-danger modal-dialog-centered modal-" role="document">
                    <div class="modal-content">
                        <div class="modal-header text-center">
                            <h6 class="modal-title font-weight-normal" id="modal-title-notification">Logged as a guest</h6>
                            
                        </div>
                        <div class="modal-body">
                            <div class="py-3 text-center">
                            <i class="material-icons h1 text-primary">
                                lock
                            </i>
                            <h4 class="text-gradient text-info text-md mt-4">Create an account to access all features. It's free - no credit cards, no payment methods EVER required.</h4>
                            <a class="nav-link text-white " href="{{ route('logout') }} "
                                    onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                                    <span class="badge badge-lg badge-info"> Create an account</span>
                                </a>
                            <p>Press anywhere outside this dialog to continue</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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



            <div class="page-header min-height-200 max-height-300 border-radius-xl mt-4 mx-n2" style="background-image: url('/assets/img/illustrations/decompressionPlanner.webp');">
                <span class="mask  bg-gradient-info  opacity-4"></span>
            </div>

            <div class="card p-0 position-relative mt-n5 mx-1 z-index-2 mb-4">
                
                <div class="p-0 mt-0 mx-2 border-radius-lg py-3 pe-1 dive-planner-header">
                    <h1 class="card-title text-info mx-3 mt-0 text-xl">Decompression Dive Planner</h1>
                    
                    <!--
                    <div class="UnitDropdown">
                        <button class="btn bg-gradient-info dropdown-toggle w-100" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                            Select units...
                        </button>
                        <ul class="dropdown-menu" id="UnitDropdownMenu">
                            <li><a class="dropdown-item" href="#">Imperial</a></li>
                            <li><a class="dropdown-item" href="#">Metric</a></li>
                        </ul>
                    </div> -->

                    <div class="text-center" style="border: none;"> <!-- Added text-center here -->
                        @if(!is_null($currentSite) && $deco_unit)
                            <a type="button" class="btn btn-info mt-0" id="switchUnits" href="{{ route('DecoPlannerImperial') }}/{{ $currentSite->id }}">Switch to IMPERIAL</a>
                        @elseif(is_null($currentSite) && $deco_unit)
                            <a type="button" class="btn btn-info mt-0" id="switchUnits" href="{{ route('DecoPlannerImperial') }}">Switch to IMPERIAL</a>
                        @elseif(!is_null($currentSite) && !$deco_unit)
                            <a type="button" class="btn btn-info mt-0" id="switchUnits" href="{{ route('DecoPlannerMetric') }}/{{ $currentSite->id }}">Switch to Metric</a>
                        @else
                            <a type="button" class="btn btn-info mt-0" id="switchUnits" href="{{ route('DecoPlannerMetric') }}">Switch to Metric</a>
                        @endif
                        
                    </div>
                </div>

            </div>

           

            <div class="row">
                <div class="col-md-12 m-auto">             
                    <div class="card p-0 position-relative mt-3 mx-n2 z-index-2 mb-4">
                        <!--<div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h3 class="card-title text-white mx-4">Plan your dive here...</h3>
                            </div>
                        </div> -->

                        <div class="card-body">
                            <div row>
                                <div class="nav-wrapper position-relative end-0">
                                    <ul class="nav nav-pills nav-fill p-1" role="tablist" id="nav-tabs">
                                        <li class="nav-item">
                                            <a class="nav-link mb-0 px-0 py-1 active" href="#">Open Circuit</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link mb-0 px-0 py-1" href="#">Closed Circuit</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <table class="table align-items-center mb-0 mt-n2"> 
                                    <tr><td class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center" style="border: none;">Set dive max Depth</td> </tr>
                                </table>
                                @if( !is_null($currentSite))
                                <div class="col-lg-4 col-12">
                                    <div class="text-center" style="border: none;">
                                        <a type="button" class="btn btn-info mt-0 w-100" id="useCurrentSiteDepthButton" onclick="useSiteDepth()">
                                            Use {{ $currentSite->name }} depth
                                        </a>
                                    </div>    
                                </div>

                                @endif
                                @if( is_null($currentSite))
                                    <div class="col-lg-6 col-12">
                                @else
                                    <div class="col-lg-4 col-12">
                                @endif
                                    <div class="text-center" style="border: none;"> <!-- Added text-center here -->
                                        <!--<a type="button" class="btn btn-info mt-0 w-100" id="searchSitesButton">
                                            Search sites
                                        </a>-->

                                        <div class="dropdown">
                                            <button class="btn bg-gradient-info dropdown-toggle w-100" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                                Search sites...
                                            </button>
                                            <ul class="dropdown-menu" id="dropdownMenu">
                                                <li>
                                                    <input type="text" class="form-control mb-2" id="dropdownSearch" placeholder="Search..." style="display: block;">
                                                </li>
                                                <?php foreach ($allSites as $site): ?>
                                                    <li><a class="dropdown-item" href="#" data-depth="<?php echo htmlspecialchars($site->maxDepth / ($deco_unit ? 3.28 : 1) ); ?>">
                                                        <?php echo htmlspecialchars($site->name) . " (" . htmlspecialchars($site->type) . ")"; ?>
                                                    </a></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>

                                    </div>    
                                </div>
                                @if( is_null($currentSite))
                                    <div class="col-lg-6 col-12">
                                @else
                                    <div class="col-lg-4 col-12">
                                @endif
                                    <input type="hidden" id="depthSlider-value" name="depthSlider-value">
                                    <div class="slider-styled" id="depthSlider"></div>
                                    <div id="maxDepthSliderTitle" class="text-secondary text-xs font-weight-bolder opacity-7 text-center mt-2" style="border: none;">Max Depth (ft)</div> 
                                </div>

                            </div>
                            <div class="row mt-2" style="border-bottom: 1px solid #D3D3D3;">
                                <div id="maxDepthContainerImp" class="col-12 d-flex justify-content-center align-items-center">
                                    <div>
                                        <label class="text-info right-label-normal custom-label text-lg" id="labelDepth">Bottom PPO2</label>
                                        <label id="maxDepthInputLabel" class="text-info">ft</label>
                                    </div>
                                </div>

                                <div id="maxDepthContainerMet" class="col-12 d-flex justify-content-center align-items-center">
                                    <div>
                                        <label class="text-info right-label-normal custom-label text-lg" id="labelDepthMET">Bottom PPO2</label>
                                        <label class="text-info">m</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row" id="containerSetPoint" style="display: none;">
                                <div class="col-lg-12 col-12" style="border-bottom: 1px solid #D3D3D3;">
                                    <table class="table align-items-center mb-0 mt-1"> 
                                        <tr><td class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center" style="border: none;">CCR Setpoint</td> </tr>
                                    </table>

                                    <div class="mt-0">
                                        <input type="hidden" id="setpointSlider-value" name="setPointSlider-value">
                                        
                                        
                                        
                                        <div class="slider-styled" id="setpointSlider"></div>
                                        <div class="text-secondary text-xs font-weight-bolder opacity-7 text-center mt-2" style="border: none;">Setpoint</div>

                                        <div class="label-container d-flex justify-content-center align-items-center">
                                            
                                                <label class="text-info right-label-normal custom-label text-lg"id="labelSetpoint">2222</label>
                                            
                                            
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <!-- Row times, gradients and asc/des rates -->
                            <div class="row">
                                <div class="col-lg-4 col-12" style="border-bottom: 1px solid #D3D3D3;">
                                    <table class="table align-items-center mb-0 mt-1"> 
                                        <tr><td class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center" style="border: none;">Set Bottom time</td> </tr>
                                    </table>
                                    <div class="mt-0">
                                        <input type="hidden" id="bottomTimeSlider-value" name="bottomTimeSlider-value">
                                        
                                        <div class="label-container">
                                            
                                            <label class="text-info right-label-normal custom-label text-lg" id="labelBottomTime">2222</label>
                                            <label class="text-info">min</label>
                                        </div>
                                        
                                        <div class="slider-styled" id="bottomTimeSlider"></div>
                                        <div class="text-secondary text-xs font-weight-bolder opacity-7 text-center mt-2" style="border: none;">Bottom time</div>
                                    </div>

                                    <div class="mt-n2 mb-2">
                                        <input type="hidden" id="surfaceTimeSlider-value" name="surfaceTimeSlider-value">
                                        
                                        <div class="label-container">
                                            
                                            <label class="text-info right-label-normal custom-label text-sm" id="labelSurfaceTime">2222</label>
                                            <label class="text-info">hrs</label>
                                        </div>
                                        
                                        <div class="slider-styled" id="surfaceTimeSlider"></div>
                                        <div class="text-secondary text-xs font-weight-bolder opacity-7 text-center mt-2" style="border: none;">Surface time</div>
                                    </div>
                                </div>

                                <!-- Col gradients -->                          
                                <div class="col-lg-4 col-12" style="border-bottom: 1px solid #D3D3D3;">
                                    <table class="table align-items-center mb-0 mt-1"> 
                                        <tr><td class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center" style="border: none;">Set Gradient Factors</td> </tr>
                                    </table>
                                    <div class="mt-0">
                                        <input type="hidden" id="GFLTimeSlider-value" name="GFLSlider-value">
                                        
                                        <div class="label-container">
                                            
                                            <label class="text-info right-label-normal custom-label text-sm" id="labelGFL">50</label>
                                            
                                        </div>
                                        
                                        <div class="slider-styled" id="GFLSlider"></div>
                                        <div class="text-secondary text-xs font-weight-bolder opacity-7 text-center mt-2" style="border: none;">GF Low</div>
                                    </div>

                                    <div class="mt-n2 mb-2">
                                        <input type="hidden" id="GFHSlider-value" name="GFHSlider-value">
                                        
                                        <div class="label-container">
                                            
                                            <label class="text-info right-label-normal custom-label text-sm" id="labelGFH">75</label>
                                            
                                        </div>
                                        
                                        <div class="slider-styled" id="GFHSlider"></div>
                                        <div class="text-secondary text-xs font-weight-bolder opacity-7 text-center mt-2" style="border: none;">GF High</div>
                                    </div>
                                </div>

                                <!-- Col ascent descent -->                          
                                <div class="col-lg-4 col-12" style="border-bottom: 1px solid #D3D3D3;">
                                    <table class="table align-items-center mb-0 mt-1"> 
                                        <tr><td class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center" style="border: none;">Set des/asc rates</td> </tr>
                                    </table>
                                    <div class="mt-0">
                                        <input type="hidden" id="desSlider-value" name="desSlider-value">
                                        
                                        <div id="desRateContainerImp" class="label-container">
                                            <label class="text-info right-label-normal custom-label text-sm" id="labelDes">100</label>
                                            <label class="text-info">ft/min</label>
                                        </div>

                                        <div id="desRateContainerMet" class="label-container">
                                            <label class="text-info right-label-normal custom-label text-sm" id="labelDesMET">100</label>
                                            <label class="text-info">m/min</label>
                                        </div>
                                        
                                        <div class="slider-styled" id="desSlider"></div>
                                        <div class="text-secondary text-xs font-weight-bolder opacity-7 text-center mt-2" style="border: none;">Descend Rate</div>
                                    </div>

                                    <div class="mt-n2 mb-2">
                                        <input type="hidden" id="ascSlider-value" name="ascSlider-value">
                                        
                                        <div id="ascRateContainerImp" class="label-container">
                                            <label class="text-info right-label-normal custom-label text-sm" id="labelAsc">30</label>
                                            <label class="text-info">ft/min</label>
                                        </div>

                                        <div id="ascRateContainerMet" class="label-container">
                                            <label class="text-info right-label-normal custom-label text-sm" id="labelAscMET">30</label>
                                            <label class="text-info">m/min</label>
                                        </div>
                                        
                                        <div class="slider-styled" id="ascSlider"></div>
                                        <div class="text-secondary text-xs font-weight-bolder opacity-7 text-center mt-2" style="border: none;">Ascend Rate</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Row gases -->
                            <div class="row">
                                <div class="col-lg-3 col-12 align-items-center" style="border-bottom: 1px solid #D3D3D3;">
                                    <div class="row">
                                        <table class="table align-items-center mb-0 mt-1"> 
                                            <tr><td class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center" id="labelBottomGasOrDiluent" style="border: none;">Bottom gas</td> </tr>
                                        </table>
                                    </div>

                                    <div class="row">
                                        <div class="col-12 position-relative">
                                            <div style="
                                                        position: absolute; /* Place it on top */
                                                        top: 0;
                                                        left: 1%;
                                                        width: 98%;
                                                        height: 101%;
                                                        padding: 10px;
                                                        border: 2px solid #1A73E8;
                                                        border-radius: 4px;
                                                        box-sizing: border-box; /* Ensure padding/border don't affect width */
                                                        z-index: 100; /* Ensure it's above other elements */
                                                        pointer-events: none;
                                                    ">
                                                </div>
                                            <div style="padding: 5px; padding-top:10px;  border: 0px solid #1A73E8; border-radius: 4px; margin-top: 5px;">
                                                <div class="row" style="display: flex; justify-content: center;">
                                                    <div class="row" style="display: flex; justify-content: center;">
                                                        <div class="mt-n6" style="position: relative; width: 150px; height: 300px;">
                                                            <!-- Overlaying image -->
                                                                <img id="tank_double" src="{{ asset("assets") }}/img/tank_double.png"   alt="Overlay Image" 
                                                                style="position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 150%; height: 75%; z-index: 10;">

                                                                <img id="tank_ccr" src="{{ asset("assets") }}/img/tank_ccr.png"   alt="Overlay Image" display="none" 
                                                                style="position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 150%; height: 75%; z-index: 10;">

                                                                <img id="unblendable_sign" src="{{ asset("assets") }}/img/unblendable_sign.png" hidden alt="Overlay Image" 
                                                                style="position: absolute; top: 70%; left: 50%; transform: translate(-50%, -50%); z-index: 10;">

                                                            
                                                            <!-- Fixed-size chart canvas -->
                                                            <div style="width: 300px; heigth:300px; position: absolute; bottom: 0; left: 50%; transform: translateX(-50%);">
                                                                <canvas id="bottomGasStackedBar" 
                                                                        style="width: 100%; height: 202px; position: absolute; bottom: 0; left: 0; transform: none; z-index: 1;"></canvas>
                                                            </div>

                                                            <!-- <canvas id="stackedBarChart" 
                                                                    style="position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 100px; height: 161px; z-index: 1;"></canvas> -->
                                                        </div>
                                                    </div>

                                                    <div class="row mt-3">
                                                        <div class="col-12 d-flex justify-content-center align-items-center">
                                                            <div style="border: 2px solid #49a3f1; padding: 5px; font-weight: bold; border-radius: 4px;">
                                                                <label class="text-success text-lg mb-0" style="font-weight: bold;" id="labelBottomGasO2">21</label>
                                                                <label class="text-info text-lg mb-0" style="font-weight: bold;">/</label>
                                                                <label class="text-info text-lg mb-0" style="font-weight: bold;" id="labelBottomGasHe">35</label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row mt-4">
                                                        <div>
                                                            <input type="hidden" id="bottomGasO2Slider-value" name="bottomGasO2Slider-value">
                                                            <div class="slider-styled" id="bottomGasO2Slider"></div>
                                                            <div class="text-secondary text-xs font-weight-bolder opacity-7 text-center mt-2" style="border: none;">O2 %</div>
                                                        </div>
                                                    </div>

                                                    <div class="row mt-3">
                                                        <div>
                                                            <input type="hidden" id="bottomGasHeSlider-value" name="bottomGasHeSlider-value">
                                                            <div class="slider-styled" id="bottomGasHeSlider"></div>
                                                            <div class="do-not-translate text-secondary text-xs font-weight-bolder opacity-7 text-center mt-2" style="border: none;">He %</div>
                                                        </div>
                                                    </div>

                                                    <div class="row mt-3">
                                                        <div class="label-container">
                                                            <label class="do-not-translate" id="labelMaxDepthPPO2Description">Max depth PPO2</label>
                                                            <label class="text-info right-label-normal custom-label" id="labelBottomGasPPO2">1.4</label>
                                                            <label class="text-info">atm</label>
                                                        </div>

                                                        <div class="label-container">
                                                            <label id="labelENDDescription">Equivalent Narcotic Depth</label>
                                                            <label class="text-info right-label-normal custom-label" id="labelBottomGasEND">90</label>
                                                            <label id="labelBottomGasENDUnit" class="text-info">ft</label>
                                                        </div>

                                                        <div class="label-container">
                                                            <label id="labelGasDensityDescription">Gas density</label>
                                                            <label class="text-info right-label-normal custom-label" id="labelBottomGasDensity">90</label>
                                                            <label class="text-info">g/l</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <!-- Decompression gases -->
                                <div class="col-lg-9 col-12" style="border-bottom: 1px solid #D3D3D3; background-color: #ffffff; padding-bottom:0px;">
                                    <table class="table align-items-center mb-0 mt-1"> 
                                        <tr><td class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center" style="border: none;" id="titleDecoGases">Decompression gases</td> </tr>
                                    </table>

                                    <div class="row">
                                        <!-- Deco 1 -->
                                        <div class="col-lg-3 col-12 position-relative" id="deco1" style="margin-right: 0px; margin-left:0px; margin-bottom: 10px; padding: 5px; border: 0px solid #1A73E8; border-radius: 4px; background-color: #ffffff;">
                                            <div style="
                                                    position: absolute; /* Place it on top */
                                                    top: 0;
                                                    left: 1%;
                                                    width: 98%;
                                                    height: 100%;
                                                    padding: 10px;
                                                    border: 2px solid #1A73E8;
                                                    border-radius: 4px;
                                                    box-sizing: border-box; /* Ensure padding/border don't affect width */
                                                    z-index: 100; /* Ensure it's above other elements */
                                                    pointer-events: none;
                                                ">
                                            </div>
                                            <div style="padding: 10px; border: 0px solid #1A73E8; border-radius: 4px;">
                                                
                                                <div id="addGasIcon1" onclick="showDecoGas1()" 
                                                    style="position: absolute; top: 10px; left: 10px; bottom: 10px; right: 8px;  background-color: #ffffff; color: #1A73E8;
                                                        cursor: pointer; z-index: 20; display: flex; align-items: center; justify-content: center; flex-direction: column;">
                                                    
                                                    <span class="material-icons-round" style="font-size: 48px;">add_circle</span>
                                                    <label class="text-info text-lg">Add Gas</label>
                                                </div>
                                                
                                                
                                                <div class="row" style="display: flex; justify-content: center;">
                                                    <div class="mt-n6" style="position: relative; width: 150px; height: 300px;">
                                                        <!-- Overlaying image -->
                                                            <img id="tank_single_1" src="{{ asset("assets") }}/img/tank_single.png"   alt="Overlay Image" 
                                                            style="position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 150%; height: 75%; z-index: 10;">

                                                        <img id="unblendable_sign_1" src="{{ asset("assets") }}/img/unblendable_sign.png" hidden alt="Overlay Image" 
                                                            style="position: absolute; top: 70%; left: 50%; transform: translate(-50%, -50%); z-index: 10;">

                                                        
                                                        <!-- Fixed-size chart canvas -->
                                                        <div style="width: 300px; heigth:300px; position: absolute; bottom: 0; left: 50%; transform: translateX(-50%);">
                                                            <canvas id="decoGas1StackedBar" 
                                                                    style="width: 100%; height: 202px; position: absolute; bottom: 0; left: 0; transform: none; z-index: 1;"></canvas>
                                                        </div>

                                                        <!-- <canvas id="stackedBarChart" 
                                                                style="position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 100px; height: 161px; z-index: 1;"></canvas> -->
                                                    </div>
                                                </div>

                                                <div class="row mt-3">
                                                    <div class="col-12 d-flex justify-content-center align-items-center">
                                                        <div style="border: 2px solid #49a3f1; padding: 5px; font-weight: bold; border-radius: 4px;">
                                                            <label class="text-success text-lg mb-0" style="font-weight: bold;" id="labelDecoGas1O2">21</label>
                                                            <label class="text-info text-lg mb-0" style="font-weight: bold;">/</label>
                                                            <label class="text-info text-lg mb-0" style="font-weight: bold;" id="labelDecoGas1He">35</label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row mt-4">
                                                    <div>
                                                        <input type="hidden" id="decoGas1O2Slider-value" name="decoGas1O2Slider-value">
                                                        <div class="slider-styled" id="decoGas1O2Slider"></div>
                                                        <div class="text-secondary text-xs font-weight-bolder opacity-7 text-center mt-2" style="border: none;">O2 %</div>
                                                    </div>
                                                </div>

                                                <div class="row mt-3">
                                                    <div>
                                                        <input type="hidden" id="decoGas1HeSlider-value" name="decoGas1HeSlider-value">

                                                        <div class="slider-styled" id="decoGas1HeSlider"></div>
                                                        <div class="do-not-translate text-secondary text-xs font-weight-bolder opacity-7 text-center mt-2" style="border: none;">He %</div>
                                                    </div>
                                                </div>

                                                <div class="row mt-3" id="containerDeco1OCInfo">
                                                    <div>
                                                        <input type="hidden" id="decoGas1SwitchSlider-value" name="decoGas1SwitchSlider-value">

                                                        <div class="label-container">                                                
                                                            <label class="text-info">PPO2</label>
                                                            <label class="text-info left-label custom-label text-sm" id="labelDecoGas1SwitchPPO2">2222</label>
                                                            
                                                            <label class="text-info right-label-normal custom-label text-sm" id="labelDecoGas1Switch">2222</label>
                                                            <label id="labelDecoGas1SwitchUNIT" class="text-info">ft</label>
                                                        </div>
                                                        <div class="slider-styled" id="decoGas1SwitchSlider"></div>
                                                        <div class="do-not-translate text-secondary text-xs font-weight-bolder opacity-7 text-center mt-2" id="switchDepthLabel1" style="border: none;">Switch depth</div>
                                                    </div>
                                                </div>

                                                <div class="row mt-3" id="containerDeco1CCInfo" style="display:none;">
                                                    <div>
                                                        <div class="label-container">                                                
                                                            <label class="text-info">PPO2</label>
                                                            <label class="text-info left-label custom-label text-sm" id="labelBailoutSwitchPPO2">2222</label>
                                                            
                                                            <label class="text-info text-right">END</label>
                                                            <label class="text-info right-label-normal custom-label text-sm mx-1" id="labelBailoutEND">2222</label>
                                                            <label class="text-info" id="labelBailoutENDUNIT">{{ $deco_unit ? "m" : "ft" }}</label>
                                                        </div>
                                                        <div class="label-container align-text-right justify-text-right">                                                
                                                            <label class="do-not-translate text-secondary align-text-right text-right" id="switchDepthLabelBO">Switch Depth</label>
                                                            <label class="text-secondary right-label-freeze custom-label text-sm" id="labelBailoutSwitch">2222</label>
                                                            <label class="text-secondary" id="labelBailoutSwitchUNIT">ft</label>
                                                        </div>
                                                        <div class="label-container d-flex justify-content-center align-items-center" style="margin-top:20px;">                                                                  
                                                            <label class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center">BAILOUT GAS</label>
                                                        </div>
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

                                        <!-- Deco 2 -->
                                        <div class="col-lg-3 col-12 position-relative" id="deco2" style="margin-right: 0px; margin-left:0px; margin-bottom: 10px; padding: 5px; border: 0px solid #1A73E8; border-radius: 4px; background-color: #ffffff;">
                                            <div style="
                                                    position: absolute; /* Place it on top */
                                                    top: 0;
                                                    left: 1%;
                                                    width: 98%;
                                                    height: 100%;
                                                    padding: 10px;
                                                    border: 2px solid #1A73E8;
                                                    border-radius: 4px;
                                                    box-sizing: border-box; /* Ensure padding/border don't affect width */
                                                    z-index: 100; /* Ensure it's above other elements */
                                                    pointer-events: none;
                                                ">
                                            </div>
                                        
                                            <div style="padding: 10px; border: 0px solid #1A73E8; border-radius: 4px;">
                                                
                                                <div id="addGasIcon2" onclick="showDecoGas2()" 
                                                    style="position: absolute; top: 10px; left: 10px; bottom: 10px; right: 8px;  background-color: #ffffff; color: #1A73E8;
                                                        cursor: pointer; z-index: 20; display: flex; align-items: center; justify-content: center; flex-direction: column;">
                                                    
                                                    <span class="material-icons-round" style="font-size: 48px;">add_circle</span>
                                                    <label class="text-info text-lg">Add Gas</label>
                                                </div>
                                                
                                                <div class="row" style="display: flex; justify-content: center;">
                                                    <div class="mt-n6" style="position: relative; width: 150px; height: 300px;">
                                                        <!-- Overlaying image -->
                                                            <img id="tank_single_2" src="{{ asset("assets") }}/img/tank_single.png"   alt="Overlay Image" 
                                                            style="position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 150%; height: 75%; z-index: 10;">

                                                        <img id="unblendable_sign_2" src="{{ asset("assets") }}/img/unblendable_sign.png" hidden alt="Overlay Image" 
                                                            style="position: absolute; top: 70%; left: 50%; transform: translate(-50%, -50%); z-index: 10;">

                                                        
                                                        <!-- Fixed-size chart canvas -->
                                                        <div style="width: 300px; heigth:300px; position: absolute; bottom: 0; left: 50%; transform: translateX(-50%);">
                                                            <canvas id="decoGas2StackedBar" 
                                                                    style="width: 100%; height: 202px; position: absolute; bottom: 0; left: 0; transform: none; z-index: 1;"></canvas>
                                                        </div>

                                                        <!-- <canvas id="stackedBarChart" 
                                                                style="position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 100px; height: 161px; z-index: 1;"></canvas> -->
                                                    </div>
                                                </div>

                                                <div class="row mt-3">
                                                    <div class="col-12 d-flex justify-content-center align-items-center">
                                                        <div style="border: 2px solid #49a3f1; padding: 5px; font-weight: bold; border-radius: 4px;">
                                                            <label class="text-success text-lg mb-0" style="font-weight: bold;" id="labelDecoGas2O2">21</label>
                                                            <label class="text-info text-lg mb-0" style="font-weight: bold;">/</label>
                                                            <label class="text-info text-lg mb-0" style="font-weight: bold;" id="labelDecoGas2He">35</label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row mt-4">
                                                    <div>
                                                        <input type="hidden" id="decoGas2O2Slider-value" name="decoGas2O2Slider-value">
                                                        <div class="slider-styled" id="decoGas2O2Slider"></div>
                                                        <div class="text-secondary text-xs font-weight-bolder opacity-7 text-center mt-2" style="border: none;">O2 %</div>
                                                    </div>
                                                </div>

                                                <div class="row mt-3">
                                                    <div>
                                                        <input type="hidden" id="decoGas2HeSlider-value" name="decoGas2HeSlider-value">

                                                        <div class="slider-styled" id="decoGas2HeSlider"></div>
                                                        <div class="do-not-translate text-secondary text-xs font-weight-bolder opacity-7 text-center mt-2" style="border: none;">He %</div>
                                                    </div>
                                                </div>

                                                <div class="row mt-3">
                                                    <div>
                                                        <input type="hidden" id="decoGas2SwitchSlider-value" name="decoGas2SwitchSlider-value">

                                                        <div class="label-container">                                                
                                                            <label class="text-info">PPO2</label>
                                                            <label class="text-info left-label custom-label text-sm" id="labelDecoGas2SwitchPPO2">2222</label>
                                                            
                                                            <label class="text-info right-label-normal custom-label text-sm" id="labelDecoGas2Switch">2222</label>
                                                            <label id="labelDecoGas2SwitchUNIT" class="text-info">ft</label>
                                                        </div>
                                                        <div class="slider-styled" id="decoGas2SwitchSlider"></div>
                                                        <div class="do-not-translate text-secondary text-xs font-weight-bolder opacity-7 text-center mt-2" id="switchDepthLabel2" style="border: none;">Switch depth</div>
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

                                        <!-- Deco 3 -->
                                        <div class="col-lg-3 col-12 position-relative" id="deco3" style="margin-right: 0px; margin-left:0px; margin-bottom: 10px; padding: 5px; border: 0px solid #1A73E8; border-radius: 4px; background-color: #ffffff;">
                                            <div style="
                                                    position: absolute; /* Place it on top */
                                                    top: 0;
                                                    left: 1%;
                                                    width: 98%;
                                                    height: 100%;
                                                    padding: 10px;
                                                    border: 2px solid #1A73E8;
                                                    border-radius: 4px;
                                                    box-sizing: border-box; /* Ensure padding/border don't affect width */
                                                    z-index: 100; /* Ensure it's above other elements */
                                                    pointer-events: none;
                                                ">
                                            </div>
                                        
                                            <div style="padding: 10px; border: 0px solid #1A73E8; border-radius: 4px;">
                                                
                                                <div id="addGasIcon3" onclick="showDecoGas3()" 
                                                    style="position: absolute; top: 10px; left: 10px; bottom: 10px; right: 8px;  background-color: #ffffff; color: #1A73E8;
                                                        cursor: pointer; z-index: 20; display: flex; align-items: center; justify-content: center; flex-direction: column;">
                                                    
                                                    <span class="material-icons-round" style="font-size: 48px;">add_circle</span>
                                                    <label class="text-info text-lg">Add Gas</label>
                                                </div>
                                            
                                                <div class="row" style="display: flex; justify-content: center;">
                                                    <div class="mt-n6" style="position: relative; width: 150px; height: 300px;">
                                                        <!-- Overlaying image -->
                                                            <img id="tank_single_3" src="{{ asset("assets") }}/img/tank_single.png"   alt="Overlay Image" 
                                                            style="position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 150%; height: 75%; z-index: 10;">

                                                        <img id="unblendable_sign_3" src="{{ asset("assets") }}/img/unblendable_sign.png" hidden alt="Overlay Image" 
                                                            style="position: absolute; top: 70%; left: 50%; transform: translate(-50%, -50%); z-index: 10;">

                                                        
                                                        <!-- Fixed-size chart canvas -->
                                                        <div style="width: 300px; heigth:300px; position: absolute; bottom: 0; left: 50%; transform: translateX(-50%);">
                                                            <canvas id="decoGas3StackedBar" 
                                                                    style="width: 100%; height: 202px; position: absolute; bottom: 0; left: 0; transform: none; z-index: 1;"></canvas>
                                                        </div>

                                                        <!-- <canvas id="stackedBarChart" 
                                                                style="position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 100px; height: 161px; z-index: 1;"></canvas> -->
                                                    </div>
                                                </div>

                                                <div class="row mt-3">
                                                    <div class="col-12 d-flex justify-content-center align-items-center">
                                                        <div style="border: 2px solid #49a3f1; padding: 5px; font-weight: bold; border-radius: 4px;">
                                                            <label class="text-success text-lg mb-0" style="font-weight: bold;" id="labelDecoGas3O2">21</label>
                                                            <label class="text-info text-lg mb-0" style="font-weight: bold;">/</label>
                                                            <label class="text-info text-lg mb-0" style="font-weight: bold;" id="labelDecoGas3He">35</label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row mt-4">
                                                    <div>
                                                        <input type="hidden" id="decoGas3O2Slider-value" name="decoGas3O2Slider-value">
                                                        <div class="slider-styled" id="decoGas3O2Slider"></div>
                                                        <div class="text-secondary text-xs font-weight-bolder opacity-7 text-center mt-2" style="border: none;">O2 %</div>
                                                    </div>
                                                </div>

                                                <div class="row mt-3">
                                                    <div>
                                                        <input type="hidden" id="decoGas3HeSlider-value" name="decoGas3HeSlider-value">

                                                        <div class="slider-styled" id="decoGas3HeSlider"></div>
                                                        <div class="do-not-translate text-secondary text-xs font-weight-bolder opacity-7 text-center mt-2" style="border: none;">He %</div>
                                                    </div>
                                                </div>

                                                <div class="row mt-3">
                                                    <div>
                                                        <input type="hidden" id="decoGas3SwitchSlider-value" name="decoGas3SwitchSlider-value">

                                                        <div class="label-container">                                                
                                                            <label class="text-info">PPO2</label>
                                                            <label class="text-info left-label custom-label text-sm" id="labelDecoGas3SwitchPPO2">2222</label>
                                                            
                                                            <label class="text-info right-label-normal custom-label text-sm" id="labelDecoGas3Switch">2222</label>
                                                            <label id="labelDecoGas3SwitchUNIT" class="text-info">ft</label>
                                                        </div>
                                                        <div class="slider-styled" id="decoGas3SwitchSlider"></div>
                                                        <div class="do-not-translate text-secondary text-xs font-weight-bolder opacity-7 text-center mt-2" id="switchDepthLabel3" style="border: none;">Switch depth</div>
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

                                        <!-- Deco 4 -->
                                        <div class="col-lg-3 col-12 position-relative" id="deco4" style="margin-right: 0px; margin-left:0px; margin-bottom: 10px; padding: 5px; border: 0px solid #1A73E8; border-radius: 4px; background-color: #ffffff;">
                                            <div style="
                                                    position: absolute; /* Place it on top */
                                                    top: 0;
                                                    left: 1%;
                                                    width: 98%;
                                                    height: 100%;
                                                    padding: 10px;
                                                    border: 2px solid #1A73E8;
                                                    border-radius: 4px;
                                                    box-sizing: border-box; /* Ensure padding/border don't affect width */
                                                    z-index: 100; /* Ensure it's above other elements */
                                                    pointer-events: none;
                                                ">
                                            </div>
                                        
                                            <div style="padding: 10px; border: 0px solid #1A73E8; border-radius: 4px;">
                                                
                                                <div id="addGasIcon4" onclick="showDecoGas4()" 
                                                    style="position: absolute; top: 10px; left: 10px; bottom: 10px; right: 8px;  background-color: #ffffff; color: #1A73E8;
                                                        cursor: pointer; z-index: 20; display: flex; align-items: center; justify-content: center; flex-direction: column;">
                                                    
                                                    <span class="material-icons-round" style="font-size: 48px;">add_circle</span>
                                                    <label class="text-info text-lg">Add Gas</label>
                                                </div>
                                            
                                                <div class="row" style="display: flex; justify-content: center;">
                                                    <div class="mt-n6" style="position: relative; width: 150px; height: 300px;">
                                                        <!-- Overlaying image -->
                                                            <img id="tank_single_4" src="{{ asset("assets") }}/img/tank_single.png"   alt="Overlay Image" 
                                                            style="position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 150%; height: 75%; z-index: 10;">

                                                        <img id="unblendable_sign_4" src="{{ asset("assets") }}/img/unblendable_sign.png" hidden alt="Overlay Image" 
                                                            style="position: absolute; top: 70%; left: 50%; transform: translate(-50%, -50%); z-index: 10;">

                                                        
                                                        <!-- Fixed-size chart canvas -->
                                                        <div style="width: 300px; heigth:300px; position: absolute; bottom: 0; left: 50%; transform: translateX(-50%);">
                                                            <canvas id="decoGas4StackedBar" 
                                                                    style="width: 100%; height: 202px; position: absolute; bottom: 0; left: 0; transform: none; z-index: 1;"></canvas>
                                                        </div>

                                                        <!-- <canvas id="stackedBarChart" 
                                                                style="position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 100px; height: 161px; z-index: 1;"></canvas> -->
                                                    </div>
                                                </div>

                                                <div class="row mt-3">
                                                    <div class="col-12 d-flex justify-content-center align-items-center">
                                                        <div style="border: 2px solid #49a3f1; padding: 5px; font-weight: bold; border-radius: 4px;">
                                                            <label class="text-success text-lg mb-0" style="font-weight: bold;" id="labelDecoGas4O2">21</label>
                                                            <label class="text-info text-lg mb-0" style="font-weight: bold;">/</label>
                                                            <label class="text-info text-lg mb-0" style="font-weight: bold;" id="labelDecoGas4He">35</label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row mt-4">
                                                    <div>
                                                        <input type="hidden" id="decoGas4O2Slider-value" name="decoGas4O2Slider-value">
                                                        <div class="slider-styled" id="decoGas4O2Slider"></div>
                                                        <div class="text-secondary text-xs font-weight-bolder opacity-7 text-center mt-2" style="border: none;">O2 %</div>
                                                    </div>
                                                </div>

                                                <div class="row mt-3">
                                                    <div>
                                                        <input type="hidden" id="decoGas4HeSlider-value" name="decoGas4HeSlider-value">

                                                        <div class="slider-styled" id="decoGas4HeSlider"></div>
                                                        <div class="do-not-translate text-secondary text-xs font-weight-bolder opacity-7 text-center mt-2" style="border: none;">He %</div>
                                                    </div>
                                                </div>

                                                <div class="row mt-3">
                                                    <div>
                                                        <input type="hidden" id="decoGas4SwitchSlider-value" name="decoGas4SwitchSlider-value">

                                                        <div class="label-container">                                                
                                                            <label class="text-info">PPO2</label>
                                                            <label class="text-info left-label custom-label text-sm" id="labelDecoGas4SwitchPPO2">2222</label>
                                                            
                                                            <label class="text-info right-label-normal custom-label text-sm" id="labelDecoGas4Switch">2222</label>
                                                            <label id="labelDecoGas4SwitchUNIT" class="text-info">ft</label>
                                                        </div>
                                                        <div class="slider-styled" id="decoGas4SwitchSlider"></div>
                                                        <div class="do-not-translate text-secondary text-xs font-weight-bolder opacity-7 text-center mt-2" id="switchDepthLabel4" style="border: none;">Switch depth</div>
                                                    </div>
                                                </div>

                                                <div class="row mt-3">
                                                    <div class="text-center" style="border: none;">
                                                        <a type="button" class="btn btn-info mt-0 w-100 mb-0" id="deco3DeleteButton" onclick="hideDecoGas4()">
                                                            Delete gas
                                                        </a>
                                                    </div>   
                                                </div>
                                            </div>
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
                        
                        </div>

                    </div>
                    

                        

                        
                    
                </div>
            </div>

            <div class="row" id="profileChartAndTable" style="display: none;">
                <div class="col-12">
                    <div class="card p-0 position-relative mt-3 mx-n2 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h3 class="card-title text-white mx-4">Decompression plan</h3>
                            </div>
                        </div>

                        <div class="card-body">
                            <div>
                                <!-- Row for summary -->
                                <div class="row mx-0">
                                    <!-- Summary row -->
                                    <div class="col-lg-12 col-12 mt-0" style="border: 2px solid #1A73E8; border-radius: 10px; padding-top: 10px;">
                                        <div class="row">
                                            <div class="col-lg-6 col-12">
                                                <div class="table-responsive">
                                                    <table class="table align-items-center mb-0"> 
                                                        <tbody>
                                                            <tr class="align-top"><td class="text-secondary text-end text-md font-weight-bolder opacity-7">Run time</td>
                                                            <td class="align-middle text-left text-wrap text-lg" id="labelTotalRunTime" style="text-align: left;">67</td></tr>

                                                            <tr class="align-top w-10"><td class="text-secondary text-end text-md font-weight-bolder opacity-7">Deco time</td>
                                                            <td class="align-middle text-left text-wrap text-lg" id="labelTotalDecoTime" style="text-align: left;">28</td></tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                            <div class="col-lg-6 col-12">
                                                <div class="table-responsive">
                                                    <table class="table align-items-center mb-0"> 
                                                        <tbody>
                                                            <tr class="align-top"><td class="text-secondary text-end text-md font-weight-bolder opacity-7">Model</td>
                                                            <td class="align-middle text-left text-wrap text-lg" id="labelModel" style="text-align: left;">ZL</td></tr>

                                                            <tr class="align-top w-10"><td class="text-secondary text-end text-md font-weight-bolder opacity-7">GFs</td>
                                                            <td class="align-middle text-left text-wrap text-lg" id="labelGFs" style="text-align: left;">40/70</td></tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- What if row -->
                                <div class="row">
                                    <div class="col-lg-12 col-12 mt-2">
                                        <div class="card-header p-0 mt-0 mx-3 position-relative" style="z-index: 100;">
                                            <div class="bg-gradient-secondary shadow-info border-radius-xl py-3 pe-1">
                                                <h4 class="card-title text-white mx-4">What if...?</h4>
                                            </div>
                                        </div>
                                        <div class="mt-n5" style="border: 2px solid #1A73E8; border-radius: 10px;">

                                            <div class="row mx-0 mt-5">
                                                <div class="col-lg-4 col-12 mx-2">
                                                    <ul class="list-group">
                                                        <li class="list-group-item border-0 px-0">
                                                            <div class="form-check form-switch ps-0">
                                                                <input class="form-check-input ms-auto" type="checkbox"
                                                                    id="filter1">
                                                                <label class="form-check-label text-sm ms-3 text-wrap w-80 mb-0"
                                                                    for="flexSwitchCheckDefault" data-bs-toggle="tooltip" data-bs-placement="top" title="Extend the dive for 5 m, how's deco profile affected??">Extend bottom time 5 min</label>
                                                            </div>
                                                        </li>
                                                    </ul>
                                                
                                                    <ul class="list-group">
                                                        <li class="list-group-item border-0 px-0">
                                                            <div class="form-check form-switch ps-0">
                                                                <input class="form-check-input ms-auto" type="checkbox"
                                                                    id="filter2">
                                                                <label class="form-check-label text-body ms-3 text-truncate w-80 mb-0"
                                                                    for="flexSwitchCheckDefault" data-bs-toggle="tooltip" data-bs-placement="top" title="Dive 10 ft deeper, how is RT and DT changed?">Increase max depth by {{ $deco_unit ? "3 m" : "10 ft" }}</label>
                                                            </div>
                                                        </li>
                                                    </ul>
                                                
                                                    <ul class="list-group" id="filter7Container" style="display: none;">
                                                        <li class="list-group-item border-0 px-0">
                                                            <div class="form-check form-switch ps-0">
                                                                <input class="form-check-input ms-auto" type="checkbox"
                                                                    id="filter7">
                                                                <label class="form-check-label text-body ms-3 text-truncate w-80 mb-0"
                                                                    for="flexSwitchCheckDefault" data-bs-toggle="tooltip" data-bs-placement="top" title="calculate RT and deco time switching to BO">Bailout to OC</label>
                                                            </div>
                                                        </li>
                                                    </ul>

                                                    <ul class="list-group" id="filter3Container">
                                                        <li class="list-group-item border-0 px-0">
                                                            <div class="form-check form-switch ps-0">
                                                                <input class="form-check-input ms-auto" type="checkbox"
                                                                    id="filter3">
                                                                <label class="form-check-label text-body ms-3 text-truncate w-80 mb-0"
                                                                    for="flexSwitchCheckDefault" data-bs-toggle="tooltip" data-bs-placement="top" title="calculate RT and deco time using only backgas">Lost all deco gases</label>
                                                            </div>
                                                        </li>
                                                    </ul>
                                                </div>

                                                <div class="col-lg-4 col-12 mx-2">
                                                    <ul class="list-group">
                                                        <li class="list-group-item border-0 px-0">
                                                            <div class="form-check form-switch ps-0">
                                                                <input class="form-check-input ms-auto" type="checkbox"
                                                                    id="filter4">
                                                                <label class="form-check-label text-body ms-3 text-truncate w-80 mb-0"
                                                                    for="flexSwitchCheckDefault" data-bs-toggle="tooltip" data-bs-placement="top" title="How's the RT affected if the dive is shorter?">Shorten bottom time 5 min</label>
                                                            </div>
                                                        </li>
                                                    </ul>
                                                
                                                    <ul class="list-group">
                                                        <li class="list-group-item border-0 px-0">
                                                            <div class="form-check form-switch ps-0">
                                                                <input class="form-check-input ms-auto" type="checkbox"
                                                                    id="filter5">
                                                                <label class="form-check-label text-body ms-3 text-truncate w-80 mb-0"
                                                                    for="flexSwitchCheckDefault" data-bs-toggle="tooltip" data-bs-placement="top" title="What's the impact of diving 10 ft shallower than planned?">Reduce max depth by {{ $deco_unit ? "3 m" : "10 ft" }}</label>
                                                            </div>
                                                        </li>
                                                    </ul>

                                                    <ul class="list-group">
                                                        <li class="list-group-item border-0 px-0">
                                                            <div class="form-check form-switch ps-0">
                                                                <input class="form-check-input ms-auto" type="checkbox"
                                                                    id="filter6">
                                                                <label class="form-check-label text-body ms-3 text-truncate w-80 mb-0"
                                                                    for="flexSwitchCheckDefault" data-bs-toggle="tooltip" data-bs-placement="top" title="Minimum deco time">Minimum deco (GFs=100%)</label>
                                                            </div>
                                                        </li>
                                                    </ul>
                                                
                                                    
                                                </div>

                                                <div class="col-lg-3 col-12 mb-2 mt-2">
                                                    <div class="table-responsive" id="summaryWhatIfTable" style="border: 2px solid #1A73E8; border-radius: 10px;">
                                                        <table class="table align-items-center mb-0"> 
                                                            <tbody>
                                                                <tr class="align-top w-30"><td class="text-secondary text-end text-md font-weight-bolder opacity-7">New Run Time:</td>
                                                                <td class="align-middle text-wrap text-lg" style="text-align: left;">
                                                                    <span class="text-lg fw-bold" id="labelWhatIfRunTime">-</span>
                                                                    <span class="text-md" id="labelWhatIfRunTimeDiff">()</span>
                                                                </td></tr>

                                                                <tr class="align-top w-30"><td class="text-secondary text-end text-md font-weight-bolder opacity-7">New Deco Time:</td>
                                                                <td class="align-middle text-wrap text-lg" style="text-align: left;">
                                                                    <span class="text-lg fw-bold" id="labelWhatIfDecoTime">-</span>
                                                                    <span class="text-md" id="labelWhatIfDecoTimeDiff">()</span>
                                                                </td></tr>
                                                                
                                                            </tbody>
                                                        </table>
                                                    </div>    
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                <!-- Row with deco table and profile chart -->
                                <div class="row mt-2">

                                    <div class="col-lg-3 col-12">
                                        <div class="label-container d-flex justify-content-center align-items-center" style="margin-top:20px;">                                                                  
                                            <label class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center" id="decoTableTitle">Decompression Table</label>
                                        </div>
                                        <div id="decoTableContainer"></div>
                                        <div id="BOTableContainer" style="display: none;"></div>
                                    </div>

                                    <div class="col-lg-9 col-12" id="profileChartContainer">
                                        <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1"> 
                                            <canvas id="profileChart" class="chart-canvas border-radius-lg" height="500px"></canvas>
                                        </div>
                                        
                                    </div>
                                </div>
                                
                                <!-- Nitrogen gas consumption -->
                                <div class="row mt-2">
                                    <div class="col-12">
                                        
                                        <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1 mt-2">
                                            <div class="label-container d-flex justify-content-center align-items-center" style="margin-top:0px;">                                                                  
                                                <label class="text-uppercase text-white text-xs font-weight-bolder text-center" id="decoTableTitle">Nitrogen Tissue Compartments</label>
                                            </div>
                                            <div style="padding-left: 20px; padding-right: 20px;">
                                                <canvas id="tissueChart" class="chart-canvas border-radius-lg"></canvas>
                                            </div>
                                            
                                            <div class="label-container mt-2" style="padding-left:20px; padding-right:20px;">
                                            
                                                <label class="text-white text-align-left">Depth ({{ $deco_unit ? "m" : "ft" }})</label>
                                                <label class="text-white left-label-white custom-label text-lg" id="labelTissueChartDepth">22</label>
                                                

                                                <label class="text-white right-label-normal-white custom-label text-lg" id="labelTissueChartTime">22</label>
                                                <label class="text-white">min</label>
                                            </div>
                                            <div style="padding-left: 20px; padding-right: 20px;">
                                                <div class="slider-styled mt-2" id="timeLapseSlider"></div>
                                            </div>
                                            <div style="padding-left: 20px; padding-right: 20px;" class="mt-3">
                                                <div class="text-center" style="border: none;"> <!-- Added text-center here -->
                                                    <a type="button" class="btn btn-white mt-0 text-info" id="playTissueAnimation" onclick="toggleSliderAnimation()">
                                                        Play
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                
                                <!-- Row for gas consumption -->
                                <div id="gasConsumptionRow" class="row">
                                    <div class="col-lg-12 col-12 mt-2">
                                        <div class="card-header p-0 mt-0 mx-3 position-relative" style="z-index: 100;">
                                            <div class="bg-gradient-secondary shadow-info border-radius-xl py-3 pe-1">
                                                <h4 id="gasConsumptionHeader" class="card-title text-white mx-4">Gas consumption</h4>
                                            </div>
                                        </div>
                                    
                                        <div class="mt-n5" style="border: 2px solid #1A73E8; border-radius: 10px;">

                                            <div class="row mt-6" style="padding:10px;">
                                                <div id="gasConsumptionBottomCol" class="col-lg-6 col-12">
                                                    <table class="table align-items-center mb-0 mt-1"> 
                                                        <tr><td class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center" style="border: none;">Bottom gas</td> </tr>
                                                    </table>
                                                    <div class="label-container">
                                                        <label class="text-info right-label-normal custom-label text-sm" id="labelSACBottomGasLiters">50</label>
                                                        <label class="text-info right-label-normal custom-label text-sm" id="labelSACBottomGas">50</label>
                                                        <label class="text-info">{{ $deco_unit ? "liters/min" : "cuft/min" }}</label>
                                                    </div>
                                                    <div class="slider-styled" id="sliderSACBottomGas"></div>
                                                    <div class="text-secondary text-xs font-weight-bolder opacity-7 text-center mt-2" style="border: none;">SAC</div>

                                                    <div class="label-container d-flex justify-content-center align-items-center" style="margin-top:20px;">                                                                  
                                                        <label class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center">Gas Consumption</label>
                                                    </div>
                                                    <div id="bottomGasConsumptionTableContainer"></div>
                                                </div>

                                                <div id="gasConsumptionDecoCol" class="col-lg-6 col-12">
                                                    <table class="table align-items-center mb-0 mt-1"> 
                                                        <tr><td id="gasConsumptionDecoOrBOHeader" class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center" style="border: none;">Decompression gases</td> </tr>
                                                    </table>
                                                    <div class="label-container">
                                                        <label class="text-info right-label-normal custom-label text-sm" id="labelSACDecoGasLiters">50</label>
                                                        <label class="text-info right-label-normal custom-label text-sm" id="labelSACDecoGas">50</label>
                                                        <label class="text-info">{{ $deco_unit ? "liters/min" : "cuft/min" }}</label>
                                                    </div>
                                                    <div class="slider-styled" id="sliderSACDecoGas"></div>
                                                    <div class="text-secondary text-xs font-weight-bolder opacity-7 text-center mt-2" style="border: none;">SAC</div>

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
                label.textContent = "PPO2 a máxima profundidad";
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
            const maxDepth = parseInt(labelDepth.textContent);
            const bottomTime = parseInt(labelBottomTime.textContent);
            const bottomGas = {
                O2: parseInt(labelBottomGasO2.textContent),
                He: parseInt(labelBottomGasHe.textContent)
            };
            const GFs = {
                low: parseInt(labelGFL.textContent),
                high: parseInt(labelGFH.textContent)
            };
            const rate = {
                descent: parseInt(labelDes.textContent),
                ascent: parseInt(labelAsc.textContent)
            };
            const surfTime = parseFloat(labelSurfaceTime.textContent);
            const setpoint = parseFloat(labelSetpoint.textContent);

            var decoGases = [];

            function addDecoGas(iconId, o2LabelId, heLabelId, switchLabelId) {
                if (document.getElementById(iconId).style.display === 'none') {
                    decoGases.push({
                        O2: parseInt(document.getElementById(o2LabelId).textContent),
                        He: parseInt(document.getElementById(heLabelId).textContent),
                        switchDepth: Math.floor(parseInt(document.getElementById(switchLabelId).textContent) * {{ $deco_unit ? 3.28084 : 1 }})
                    });
                }
            }

            // Add gases dynamically if the icons are hidden
            
            if (modeOCOrCC == "CC")
                addDecoGas("addGasIcon1", "labelDecoGas1O2", "labelDecoGas1He", "labelBailoutSwitch");
            else
                addDecoGas("addGasIcon1", "labelDecoGas1O2", "labelDecoGas1He", "labelDecoGas1Switch");

            addDecoGas("addGasIcon2", "labelDecoGas2O2", "labelDecoGas2He", "labelDecoGas2Switch");
            addDecoGas("addGasIcon3", "labelDecoGas3O2", "labelDecoGas3He", "labelDecoGas3Switch");
            addDecoGas("addGasIcon4", "labelDecoGas4O2", "labelDecoGas4He", "labelDecoGas4Switch");

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
                        document.getElementById("profileChartContainer").className = "col-lg-9 col-12";
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
                        document.getElementById('labelSACBottomGas').style.display="none";
                        document.getElementById('labelSACBottomGasLiters').style.display="block";
                        document.getElementById('labelSACDecoGas').style.display="none";
                        document.getElementById('labelSACDecoGasLiters').style.display="block";
                    @else
                        document.getElementById('labelSACBottomGas').style.display="block";
                        document.getElementById('labelSACBottomGasLiters').style.display="none";
                        document.getElementById('labelSACDecoGas').style.display="block";
                        document.getElementById('labelSACDecoGasLiters').style.display="none";
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
                var currentSetPoint = parseFloat(labelSetpoint.textContent);
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
                gasDensityLabel.classList.remove("text-info", "text-warning", "right-label-normal", "right-label-warning"); // Remove other classes
                gasDensityLabel.classList.add("text-danger", "right-label-danger"); // Add "text-danger" class
            } else if (densityRounded > 5.2) {
                gasDensityLabel.classList.remove("text-info", "text-danger", "right-label-normal", "right-label-danger"); // Remove other classes
                gasDensityLabel.classList.add("text-warning", "right-label-warning"); // Add "text-warning" class
            } else {
                gasDensityLabel.classList.remove("text-warning", "text-danger", "right-label-warning", "right-label-danger"); // Remove other classes
                gasDensityLabel.classList.add("text-info", "right-label-normal"); // Add "text-info" class
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
                bottomGasEND = calculateENDCCR(depth, parseFloat(labelSetpoint.textContent), O2 / 100, He / 100, 1); 
            }

            // adjust the unit
            if(modeImpOrMetric === "imp")
                labelBottomGasEND.textContent = Math.max(0,(bottomGasEND).toFixed(0));
            else
                labelBottomGasEND.textContent = (Math.max(0,(bottomGasEND)) * 0.3048).toFixed(0);

            if (bottomGasEND > 130) {
                labelBottomGasEND.classList.remove("text-info", "text-warning", "right-label-normal", "right-label-warning"); // Remove other classes
                labelBottomGasEND.classList.add("text-danger", "right-label-danger"); // Add "text-danger" class
            } else if (bottomGasEND > 100) {
                labelBottomGasEND.classList.remove("text-info", "text-danger", "right-label-normal", "right-label-danger"); // Remove other classes
                labelBottomGasEND.classList.add("text-warning", "right-label-warning"); // Add "text-warning" class
            } else {
                labelBottomGasEND.classList.remove("text-warning", "text-danger", "right-label-warning", "right-label-danger"); // Remove other classes
                labelBottomGasEND.classList.add("text-info", "right-label-normal"); // Add "text-info" class
            } 
        }

    </script>
    {{-- Bottom gas chart --}}
    <script>
        
        // Get the canvas element
        const bottomGasStackedBarChartElement = document.getElementById('bottomGasStackedBar').getContext('2d');
        let labelHorizontalOffset = -40;

        // Create the chart with a custom plugin for labels
        const bottomGasstackedBarChart = new Chart(bottomGasStackedBarChartElement, {
            type: 'bar', // Bar chart type
            data: {
                labels: [''], // X-axis labels
                datasets: [
                    {
                        label: 'Oxygen',
                        data: [18], // Data points for this dataset
                        backgroundColor: 'rgb(76, 175, 80, 1.0)', // Bar color
                        borderRadius: 0, // Rounded corners
                        barPercentage: 1 // Adjust bar width (smaller bars)
                    },
                    {
                        label: 'Helium',
                        data: [45], // Data points for this dataset
                        backgroundColor: 'rgb(26, 115, 232, 1.0)', // Bar color
                        borderRadius: 0, // Rounded corners
                        barPercentage: 1 // Adjust bar width (smaller bars)
                    },
                    {
                        label: 'Nitrogen',
                        data: [37], // Data points for this dataset
                        backgroundColor: '#7b809a', // Bar color
                        borderRadius: 0, // Rounded corners
                        barPercentage: 1 // Adjust bar width (smaller bars)
                    }
                ]
            },
            options: {
                responsive: true, // Makes the chart responsive
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
                    padding: {
                        left: 20,
                        right: 20,
                        top: 20,
                        bottom: 4
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
                                    ctx.font = '12px Roboto';
                                    ctx.fillStyle = '#FFF'; // Label color
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'middle'; // Centers text vertically
                                    ctx.fillText(data + '%', bar.x + labelHorizontalOffset, bar.y + 10); // Position label slightly above the bar
                                    
                                } else if (data == 100) {
                                    ctx.font = '12px Roboto';
                                    ctx.fillStyle = '#FFF'; // Label color
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'middle'; // Centers text vertically
                                    ctx.fillText(data + '%', bar.x +labelHorizontalOffset , bar.y + 70); // Position label slightly above the bar
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
                    backgroundColor: 'rgb(76, 175, 80, 1.0)'
                },
                {
                    label: 'Helium',
                    data: [helium],
                    backgroundColor: 'rgb(26, 115, 232, 1.0)'
                },
                {
                    label: 'Nitrogen',
                    data: [nitrogen],
                    backgroundColor: '#7b809a'
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
            document.getElementById("labelWhatIfRunTimeDiff").innerText="()";
            document.getElementById("labelWhatIfDecoTime").innerText="-";
            document.getElementById("labelWhatIfDecoTimeDiff").innerText="()";
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
                        backgroundColor: 'rgb(76, 175, 80, 1.0)', // Bar color
                        borderRadius: 0, // Rounded corners
                        barPercentage: 1 // Adjust bar width (smaller bars)
                    },
                    {
                        label: 'Helium',
                        data: [45], // Data points for this dataset
                        backgroundColor: 'rgb(26, 115, 232, 1.0)', // Bar color
                        borderRadius: 0, // Rounded corners
                        barPercentage: 1 // Adjust bar width (smaller bars)
                    },
                    {
                        label: 'Nitrogen',
                        data: [37], // Data points for this dataset
                        backgroundColor: '#7b809a', // Bar color
                        borderRadius: 0, // Rounded corners
                        barPercentage: 1 // Adjust bar width (smaller bars)
                    }
                ]
            },
            options: {
                responsive: true, // Makes the chart responsive
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
                    padding: {
                        left: 20,
                        right: 20,
                        top: 20,
                        bottom: 4
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
                                    ctx.font = '12px Roboto';
                                    ctx.fillStyle = '#FFF'; // Label color
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'middle'; // Centers text vertically
                                    ctx.fillText(data + '%', bar.x, bar.y + 10); // Position label slightly above the bar
                                    
                                } else if (data == 100) {
                                    ctx.font = '12px Roboto';
                                    ctx.fillStyle = '#FFF'; // Label color
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'middle'; // Centers text vertically
                                    ctx.fillText(data + '%', bar.x , bar.y + 70); // Position label slightly above the bar
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
                    backgroundColor: 'rgb(76, 175, 80, 1.0)'
                },
                {
                    label: 'Helium',
                    data: [helium],
                    backgroundColor: 'rgb(26, 115, 232, 1.0)'
                },
                {
                    label: 'Nitrogen',
                    data: [nitrogen],
                    backgroundColor: '#7b809a'
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
                        backgroundColor: 'rgb(76, 175, 80, 1.0)', // Bar color
                        borderRadius: 0, // Rounded corners
                        barPercentage: 1 // Adjust bar width (smaller bars)
                    },
                    {
                        label: 'Helium',
                        data: [45], // Data points for this dataset
                        backgroundColor: 'rgb(26, 115, 232, 1.0)', // Bar color
                        borderRadius: 0, // Rounded corners
                        barPercentage: 1 // Adjust bar width (smaller bars)
                    },
                    {
                        label: 'Nitrogen',
                        data: [37], // Data points for this dataset
                        backgroundColor: '#7b809a', // Bar color
                        borderRadius: 0, // Rounded corners
                        barPercentage: 1 // Adjust bar width (smaller bars)
                    }
                ]
            },
            options: {
                responsive: true, // Makes the chart responsive
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
                    padding: {
                        left: 20,
                        right: 20,
                        top: 20,
                        bottom: 4
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
                                    ctx.font = '12px Roboto';
                                    ctx.fillStyle = '#FFF'; // Label color
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'middle'; // Centers text vertically
                                    ctx.fillText(data + '%', bar.x, bar.y + 10); // Position label slightly above the bar
                                    
                                } else if (data == 100) {
                                    ctx.font = '12px Roboto';
                                    ctx.fillStyle = '#FFF'; // Label color
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'middle'; // Centers text vertically
                                    ctx.fillText(data + '%', bar.x , bar.y + 70); // Position label slightly above the bar
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
                    backgroundColor: 'rgb(76, 175, 80, 1.0)'
                },
                {
                    label: 'Helium',
                    data: [helium],
                    backgroundColor: 'rgb(26, 115, 232, 1.0)'
                },
                {
                    label: 'Nitrogen',
                    data: [nitrogen],
                    backgroundColor: '#7b809a'
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
                        backgroundColor: 'rgb(76, 175, 80, 1.0)', // Bar color
                        borderRadius: 0, // Rounded corners
                        barPercentage: 1 // Adjust bar width (smaller bars)
                    },
                    {
                        label: 'Helium',
                        data: [45], // Data points for this dataset
                        backgroundColor: 'rgb(26, 115, 232, 1.0)', // Bar color
                        borderRadius: 0, // Rounded corners
                        barPercentage: 1 // Adjust bar width (smaller bars)
                    },
                    {
                        label: 'Nitrogen',
                        data: [37], // Data points for this dataset
                        backgroundColor: '#7b809a', // Bar color
                        borderRadius: 0, // Rounded corners
                        barPercentage: 1 // Adjust bar width (smaller bars)
                    }
                ]
            },
            options: {
                responsive: true, // Makes the chart responsive
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
                    padding: {
                        left: 20,
                        right: 20,
                        top: 20,
                        bottom: 4
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
                                    ctx.font = '12px Roboto';
                                    ctx.fillStyle = '#FFF'; // Label color
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'middle'; // Centers text vertically
                                    ctx.fillText(data + '%', bar.x, bar.y + 10); // Position label slightly above the bar
                                    
                                } else if (data == 100) {
                                    ctx.font = '12px Roboto';
                                    ctx.fillStyle = '#FFF'; // Label color
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'middle'; // Centers text vertically
                                    ctx.fillText(data + '%', bar.x , bar.y + 70); // Position label slightly above the bar
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
                    backgroundColor: 'rgb(76, 175, 80, 1.0)'
                },
                {
                    label: 'Helium',
                    data: [helium],
                    backgroundColor: 'rgb(26, 115, 232, 1.0)'
                },
                {
                    label: 'Nitrogen',
                    data: [nitrogen],
                    backgroundColor: '#7b809a'
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
                        backgroundColor: 'rgb(76, 175, 80, 1.0)', // Bar color
                        borderRadius: 0, // Rounded corners
                        barPercentage: 1 // Adjust bar width (smaller bars)
                    },
                    {
                        label: 'Helium',
                        data: [45], // Data points for this dataset
                        backgroundColor: 'rgb(26, 115, 232, 1.0)', // Bar color
                        borderRadius: 0, // Rounded corners
                        barPercentage: 1 // Adjust bar width (smaller bars)
                    },
                    {
                        label: 'Nitrogen',
                        data: [37], // Data points for this dataset
                        backgroundColor: '#7b809a', // Bar color
                        borderRadius: 0, // Rounded corners
                        barPercentage: 1 // Adjust bar width (smaller bars)
                    }
                ]
            },
            options: {
                responsive: true, // Makes the chart responsive
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
                    padding: {
                        left: 20,
                        right: 20,
                        top: 20,
                        bottom: 4
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
                                    ctx.font = '12px Roboto';
                                    ctx.fillStyle = '#FFF'; // Label color
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'middle'; // Centers text vertically
                                    ctx.fillText(data + '%', bar.x, bar.y + 10); // Position label slightly above the bar
                                    
                                } else if (data == 100) {
                                    ctx.font = '12px Roboto';
                                    ctx.fillStyle = '#FFF'; // Label color
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'middle'; // Centers text vertically
                                    ctx.fillText(data + '%', bar.x , bar.y + 70); // Position label slightly above the bar
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
                    backgroundColor: 'rgb(76, 175, 80, 1.0)'
                },
                {
                    label: 'Helium',
                    data: [helium],
                    backgroundColor: 'rgb(26, 115, 232, 1.0)'
                },
                {
                    label: 'Nitrogen',
                    data: [nitrogen],
                    backgroundColor: '#7b809a'
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
            labelSurfaceTime.textContent = parseFloat(surfaceTimeSliderValue).toFixed(1);
            
            // reset calculation area
            resetCalculationArea()

        });

        surfaceTimeSlider.setAttribute('disabled', true);
    </script>

    {{-- Scripts slider GFs --}}
    <script>
        var GFLSlider = document.getElementById('GFLSlider');
        var labelGFL = document.getElementById('labelGFL');


        noUiSlider.create(GFLSlider, {
            start: 40,
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
            labelGFL.textContent = parseInt(GFLSliderValue);

            // reset calculation area
            resetCalculationArea()
        });

        var GFHSlider = document.getElementById('GFHSlider');
        var labelGFH = document.getElementById('labelGFH');


        noUiSlider.create(GFHSlider, {
            start: 75,
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
            labelGFH.textContent = GFHSliderValue;

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
                labelDes.textContent = parseInt(desSliderValue);
                labelDesMET.textContent = parseInt(desSliderValue * 0.3948);
            } else {
                labelDes.textContent = parseInt(desSliderValue * 3.281);
                labelDesMET.textContent = parseInt(desSliderValue);
                desSliderValue = parseInt(desSliderValue * 3.281);
            }

            // reset calculation area
            resetCalculationArea()
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
                labelAsc.textContent = parseInt(ascSliderValue);
                labelAscMET.textContent = parseInt(ascSliderValue * 0.3948);
            } else {
                labelAsc.textContent = parseInt(ascSliderValue * 3.281);
                labelAscMET.textContent = parseInt(ascSliderValue);
                ascSliderValue = parseInt(ascSliderValue * 3.281);
            }

            // reset calculation area
            resetCalculationArea()
        });
    </script>

    {{-- Scripts slider setPoint --}}
    <script>
        var setpointSlider = document.getElementById('setpointSlider');
        var labelSetpoint = document.getElementById('labelSetpoint');


        noUiSlider.create(setpointSlider, {
            start: 1.3,
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
            labelSetpoint.textContent = parseFloat(setpointSliderValue).toFixed(2);

            if (setpointSliderValue > 1.5) {
                labelSetpoint.classList.remove("text-info", "text-warning", "right-label-normal", "right-label-warning"); // Remove other classes
                labelSetpoint.classList.add("text-danger", "right-label-danger"); // Add "text-danger" class
            } else if (setpointSliderValue > 1.4) {
                labelSetpoint.classList.remove("text-info", "text-danger", "right-label-normal", "right-label-danger"); // Remove other classes
                labelSetpoint.classList.add("text-warning", "right-label-warning"); // Add "text-warning" class
            } else {
                labelSetpoint.classList.remove("text-warning", "text-danger", "right-label-warning", "right-label-danger"); // Remove other classes
                labelSetpoint.classList.add("text-info", "right-label-normal"); // Add "text-info" class
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
            labelBottomGasO2.textContent = parseInt(bottomGasO2SliderValue);

            depth = parseInt(labelDepth.textContent);

            var bottomGasPPO2 = parseFloat((depth / 33 +1) * labelBottomGasO2.textContent / 100);
            labelBottomGasPPO2.textContent = ((depth / 33 +1) * labelBottomGasO2.textContent / 100).toFixed(1);

            if (bottomGasPPO2 > 1.6 || bottomGasPPO2 < 0.16) {
                labelBottomGasPPO2.classList.remove("text-info", "text-warning", "right-label-normal", "right-label-warning"); // Remove other classes
                labelBottomGasPPO2.classList.add("text-danger", "right-label-danger"); // Add "text-danger" class
            } else if (bottomGasPPO2 > 1.41 || bottomGasPPO2 < 0.2) {
                labelBottomGasPPO2.classList.remove("text-info", "text-danger", "right-label-normal", "right-label-danger"); // Remove other classes
                labelBottomGasPPO2.classList.add("text-warning", "right-label-warning"); // Add "text-warning" class
            } else {
                labelBottomGasPPO2.classList.remove("text-warning", "text-danger", "right-label-warning", "right-label-danger"); // Remove other classes
                labelBottomGasPPO2.classList.add("text-info", "right-label-normal"); // Add "text-info" class
            }
        
            updateEND(parseInt(labelBottomGasO2.textContent), parseInt(labelBottomGasHe.textContent), depth);

            updateGasDensity(parseInt(labelBottomGasO2.textContent), parseInt(labelBottomGasHe.textContent), depth, labelBottomGasDensity);

            // Update MAX on He slider
            bottomGasHeSlider.noUiSlider.updateOptions({
                range: {
                    'min': 0,    // Keep the minimum value as is
                    'max': 95-bottomGasO2SliderValue   // Update the maximum value to 120
                }
            });

            var oxygen = parseInt(labelBottomGasO2.textContent);
            var helium = parseInt(labelBottomGasHe.textContent);
            updateBottomGasChart(oxygen, helium);

            // reset calculation area
            resetCalculationArea()

        });

        


        

        // Hide the tick mark labels
        var bottomGasHeSliderTicks = bottomGasHeSlider.querySelectorAll('.noUi-value-sub');
        bottomGasHeSliderTicks.forEach(function (bottomGasHeSlider) {
            bottomGasHeSlider.style.display = 'none';
        });

        bottomGasHeSlider.noUiSlider.on('update', function (values, handle) {
            var bottomGasHeSliderValue = values[handle];
            depth = parseInt(labelDepth.textContent);
            labelBottomGasHe.textContent = parseInt(bottomGasHeSliderValue);

            var oxygen = parseInt(labelBottomGasO2.textContent);
            var helium = parseInt(labelBottomGasHe.textContent);
            updateBottomGasChart(oxygen, helium);

            updateEND(parseInt(labelBottomGasO2.textContent), parseInt(labelBottomGasHe.textContent), depth);

            updateGasDensity(parseInt(labelBottomGasO2.textContent), parseInt(labelBottomGasHe.textContent), depth, labelBottomGasDensity);

            // reset calculation area
            resetCalculationArea()
        });

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
            if(modeImpOrMetric == "imp") {
                labelDepth.textContent = parseInt(depthSliderValue);
                labelDepthMET.textContent = parseInt(depthSliderValue * 0.3948);
                labelBailoutSwitch.textContent = parseInt(depthSliderValue);    // update bailout switching depth
                depthSliderValue = parseInt(depthSliderValue);
            } else {
                labelDepth.textContent = parseInt(depthSliderValue * 3.281);
                labelDepthMET.textContent = parseInt(depthSliderValue);
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
            labelBottomTime.textContent = parseInt(bottomTimeSliderValue);

            // reset calculation area
            resetCalculationArea()
            

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
            labelDecoGas1O2.textContent = parseInt(decoGas1O2SliderValue);

            // Update MAX on He slider
            decoGas1HeSlider.noUiSlider.updateOptions({
                range: {
                    'min': 0,    // Keep the minimum value as is
                    'max': Math.max(0, 95-decoGas1O2SliderValue)   // Update the maximum value to 120
                }
            });

            
            

            var oxygen = parseInt(labelDecoGas1O2.textContent);
            var helium = parseInt(labelDecoGas1He.textContent);
            updateDecoGas1Chart(oxygen, helium);

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
            labelBailoutSwitchPPO2.textContent = ((depth / 33 +1) * parseInt(labelDecoGas1O2.textContent) /100).toFixed(2);

        });

        // Hide the tick mark labels
        var decoGas1HeSliderTicks = decoGas1HeSlider.querySelectorAll('.noUi-value-sub');
        decoGas1HeSliderTicks.forEach(function (decoGas1HeSlider) {
            bottomGasHeSlider.style.display = 'none';
        });

        decoGas1HeSlider.noUiSlider.on('update', function (values, handle) {
            var decoGas1HeSliderValue = values[handle];
            labelDecoGas1He.textContent = parseInt(decoGas1HeSliderValue);

            var oxygen = parseInt(labelDecoGas1O2.textContent);
            var helium = parseInt(labelDecoGas1He.textContent);
            updateDecoGas1Chart(oxygen, helium);

            // reset calculation area
            resetCalculationArea()

            // change on CCvar ambientPressure = depth / 33 +1;
            var ambientPressure = (depth /33 +1);
            var bottomGasPPHe = ambientPressure * parseInt(labelDecoGas1He.textContent) / 100;
            var bottomGasENDPressure = ambientPressure - bottomGasPPHe;
            bottomGasEND = (bottomGasENDPressure - 1 ) * 33;

            labelBailoutEND = document.getElementById("labelBailoutEND");
            labelBailoutEND.textContent = Math.max(0,(bottomGasEND / {{ $deco_unit ? 3.28084 : 1}}).toFixed(0));


            if (labelBailoutEND.textContent > 130) {
                labelBailoutEND.classList.remove("text-info", "text-warning", "right-label-normal", "right-label-warning"); // Remove other classes
                labelBailoutEND.classList.add("text-danger", "right-label-danger"); // Add "text-danger" class
            } else if (labelBailoutEND.textContent > 100) {
                labelBailoutEND.classList.remove("text-info", "text-danger", "right-label-normal", "right-label-danger"); // Remove other classes
                labelBailoutEND.classList.add("text-warning", "right-label-warning"); // Add "text-warning" class
            } else {
                labelBailoutEND.classList.remove("text-warning", "text-danger", "right-label-warning", "right-label-danger"); // Remove other classes
                labelBailoutEND.classList.add("text-info", "right-label-normal"); // Add "text-info" class
            }

        });

        // Hide the tick mark labels
        var decoGas1SwitchSliderTicks = decoGas1SwitchSlider.querySelectorAll('.noUi-value-sub');
        decoGas1SwitchSliderTicks.forEach(function (decoGas1SwitchSlider) {
            decoGas1SwitchSlider.style.display = 'none';
        });

        decoGas1SwitchSlider.noUiSlider.on('update', function (values, handle) {
            var decoGas1SwitchSliderValue = values[handle];
            labelDecoGas1SwitchPPO2.textContent = parseFloat(decoGas1SwitchSliderValue).toFixed(2);

            var O2Content = labelDecoGas1O2.textContent / 100;
            
            labelDecoGas1Switch.textContent = (((decoGas1SwitchSliderValue / O2Content) - 1 ) * 33).toFixed(0);

            // adjust the unit
            if(modeImpOrMetric === "imp")
                labelDecoGas1Switch.textContent = (((decoGas1SwitchSliderValue / O2Content) - 1 ) * 33).toFixed(0);
            else
                labelDecoGas1Switch.textContent = (((decoGas1SwitchSliderValue / O2Content) - 1 ) * 33 * 0.3048).toFixed(0);

            

            // reset calculation area
            resetCalculationArea()
        });
        
        function showDecoGas1() {
            document.getElementById("addGasIcon1").style.display = "none"; // Hide add gas icon
            // reset calculation area
            resetCalculationArea()
        }

        function hideDecoGas1() {
            document.getElementById("addGasIcon1").style.display = "flex"; // Hide add gas icon
            // reset calculation area
            resetCalculationArea()
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
            labelDecoGas2O2.textContent = parseInt(decoGas2O2SliderValue);

            // Update MAX on He slider
            decoGas2HeSlider.noUiSlider.updateOptions({
                range: {
                    'min': 0,    // Keep the minimum value as is
                    'max': Math.max(0, 95-decoGas2O2SliderValue)   // Update the maximum value to 120
                }
            });

            

            var oxygen = parseInt(labelDecoGas2O2.textContent);
            var helium = parseInt(labelDecoGas2He.textContent);
            updateDecoGas2Chart(oxygen, helium);

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

        // Hide the tick mark labels
        var decoGas2HeSliderTicks = decoGas2HeSlider.querySelectorAll('.noUi-value-sub');
        decoGas2HeSliderTicks.forEach(function (decoGas2HeSlider) {
            bottomGasHe2Slider.style.display = 'none';
        });

        decoGas2HeSlider.noUiSlider.on('update', function (values, handle) {
            var decoGas2HeSliderValue = values[handle];
            labelDecoGas2He.textContent = parseInt(decoGas2HeSliderValue);

            var oxygen = parseInt(labelDecoGas2O2.textContent);
            var helium = parseInt(labelDecoGas2He.textContent);
            updateDecoGas2Chart(oxygen, helium);

            // reset calculation area
            resetCalculationArea()
        });

        // Hide the tick mark labels
        var decoGas2SwitchSliderTicks = decoGas2SwitchSlider.querySelectorAll('.noUi-value-sub');
        decoGas2SwitchSliderTicks.forEach(function (decoGas2SwitchSlider) {
            decoGas2SwitchSlider.style.display = 'none';
        });

        decoGas2SwitchSlider.noUiSlider.on('update', function (values, handle) {
            var decoGas2SwitchSliderValue = values[handle];
            labelDecoGas2SwitchPPO2.textContent = parseFloat(decoGas2SwitchSliderValue).toFixed(1);

            var O2Content = labelDecoGas2O2.textContent / 100;
            
            

            // adjust the unit
            if(modeImpOrMetric === "imp")
                labelDecoGas2Switch.textContent = (((decoGas2SwitchSliderValue / O2Content) - 1 ) * 33).toFixed(0);
            else
                labelDecoGas2Switch.textContent = (((decoGas2SwitchSliderValue / O2Content) - 1 ) * 33 * 0.3048).toFixed(0);

            // reset calculation area
            resetCalculationArea()
        });
        
        function showDecoGas2() {
            document.getElementById("addGasIcon2").style.display = "none"; // Hide add gas icon
            // reset calculation area
            resetCalculationArea()
        }

        function hideDecoGas2() {
            document.getElementById("addGasIcon2").style.display = "flex"; // Hide add gas icon
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
            labelDecoGas3O2.textContent = parseInt(decoGas3O2SliderValue);

            // Update MAX on He slider
            decoGas3HeSlider.noUiSlider.updateOptions({
                range: {
                    'min': 0,    // Keep the minimum value as is
                    'max': Math.max(0, 95-decoGas3O2SliderValue)   // Update the maximum value to 120
                }
            });

            

            var oxygen = parseInt(labelDecoGas3O2.textContent);
            var helium = parseInt(labelDecoGas3He.textContent);
            updateDecoGas3Chart(oxygen, helium);

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

        // Hide the tick mark labels
        var decoGas3HeSliderTicks = decoGas3HeSlider.querySelectorAll('.noUi-value-sub');
        decoGas3HeSliderTicks.forEach(function (decoGas3HeSlider) {
            bottomGasHe3Slider.style.display = 'none';
        });

        decoGas3HeSlider.noUiSlider.on('update', function (values, handle) {
            var decoGas3HeSliderValue = values[handle];
            labelDecoGas3He.textContent = parseInt(decoGas3HeSliderValue);

            var oxygen = parseInt(labelDecoGas3O2.textContent);
            var helium = parseInt(labelDecoGas3He.textContent);
            updateDecoGas3Chart(oxygen, helium);

            // reset calculation area
            resetCalculationArea()
        });

        // Hide the tick mark labels
        var decoGas3SwitchSliderTicks = decoGas3SwitchSlider.querySelectorAll('.noUi-value-sub');
        decoGas3SwitchSliderTicks.forEach(function (decoGas3SwitchSlider) {
            decoGas3SwitchSlider.style.display = 'none';
        });

        decoGas3SwitchSlider.noUiSlider.on('update', function (values, handle) {
            var decoGas3SwitchSliderValue = values[handle];
            labelDecoGas3SwitchPPO2.textContent = parseFloat(decoGas3SwitchSliderValue).toFixed(1);

            var O2Content = labelDecoGas3O2.textContent / 100;
            

            // adjust the unit
            if(modeImpOrMetric === "imp")
                labelDecoGas3Switch.textContent = (((decoGas3SwitchSliderValue / O2Content) - 1 ) * 33).toFixed(0);
            else
                labelDecoGas3Switch.textContent = (((decoGas3SwitchSliderValue / O2Content) - 1 ) * 33 * 0.3048).toFixed(0);

            // reset calculation area
            resetCalculationArea()
        });
        
        function showDecoGas3() {
            document.getElementById("addGasIcon3").style.display = "none"; // Hide add gas icon
            // reset calculation area
            resetCalculationArea()
        }

        function hideDecoGas3() {
            document.getElementById("addGasIcon3").style.display = "flex"; // Hide add gas icon
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
            labelDecoGas4O2.textContent = parseInt(decoGas4O2SliderValue);

            // Update MAX on He slider
            decoGas4HeSlider.noUiSlider.updateOptions({
                range: {
                    'min': 0,    // Keep the minimum value as is
                    'max': Math.max(0, 95-decoGas4O2SliderValue)   // Update the maximum value to 120
                }
            });

            

            var oxygen = parseInt(labelDecoGas4O2.textContent);
            var helium = parseInt(labelDecoGas4He.textContent);
            updateDecoGas4Chart(oxygen, helium);

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

        // Hide the tick mark labels
        var decoGas4HeSliderTicks = decoGas4HeSlider.querySelectorAll('.noUi-value-sub');
        decoGas4HeSliderTicks.forEach(function (decoGas4HeSlider) {
            bottomGasHe4Slider.style.display = 'none';
        });

        decoGas4HeSlider.noUiSlider.on('update', function (values, handle) {
            var decoGas4HeSliderValue = values[handle];
            labelDecoGas4He.textContent = parseInt(decoGas4HeSliderValue);

            var oxygen = parseInt(labelDecoGas4O2.textContent);
            var helium = parseInt(labelDecoGas4He.textContent);
            updateDecoGas4Chart(oxygen, helium);

            // reset calculation area
            resetCalculationArea()
        });

        // Hide the tick mark labels
        var decoGas4SwitchSliderTicks = decoGas4SwitchSlider.querySelectorAll('.noUi-value-sub');
        decoGas4SwitchSliderTicks.forEach(function (decoGas4SwitchSlider) {
            decoGas4SwitchSlider.style.display = 'none';
        });

        decoGas4SwitchSlider.noUiSlider.on('update', function (values, handle) {
            var decoGas4SwitchSliderValue = values[handle];
            labelDecoGas4SwitchPPO2.textContent = parseFloat(decoGas4SwitchSliderValue).toFixed(1);

            var O2Content = labelDecoGas4O2.textContent / 100;

            // adjust the unit
            if(modeImpOrMetric === "imp")
                labelDecoGas4Switch.textContent = (((decoGas4SwitchSliderValue / O2Content) - 1 ) * 33).toFixed(0);
            else
                labelDecoGas4Switch.textContent = (((decoGas4SwitchSliderValue / O2Content) - 1 ) * 33 * 0.3048).toFixed(0);

            // reset calculation area
            resetCalculationArea()
        });
        
        function showDecoGas4() {
            document.getElementById("addGasIcon4").style.display = "none"; // Hide add gas icon
            // reset calculation area
            resetCalculationArea()
        }

        function hideDecoGas4() {
            document.getElementById("addGasIcon4").style.display = "flex"; // Hide add gas icon
            // reset calculation area
            resetCalculationArea()
        }
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

        function renderProfileChart(response) {
            
            let unitConversion = 33;
            if (modeImpOrMetric == "met")
                unitConversion = 10;
            // Convert data into correct format for a scatter plot
            formattedData = response['baseline'].map(item => ({ x: item.time, y: -(item.abs_p - 1) * unitConversion }));
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
            console.log(response);
            console.log(response.some(entry => entry.phase === "deco_stop"));
            if(response.some(entry => entry.phase === "deco_stop") === false) {
                GFL = document.getElementById("labelGFL").textContent
                GFH = document.getElementById("labelGFH").textContent
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
                tableHTML = 
                `<div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th class="phase-column"></th>
                                <th class="depth-column text-sm" style="padding-left: 0px; padding-right:0px;">Depth</th>
                                <th class="text-sm" style="padding-left: 0px; padding-right:0px;">Time</th>
                                <th class="text-sm" style="padding-left: 0px; padding-right:0px;">RT</th>
                                <th class="text-sm" style="padding-left: 0px; padding-right:0px;">Gas</th>
                                <th class="text-sm" style="padding-left: 0px; padding-right:0px;">PPO2</th>
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
                                <th class="text-sm" style="padding-left: 0px; padding-right:0px;">PPO2</th>
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
                        <td class="text-sm">${labelSetpoint.textContent}</td>
                        <td class="text-sm">${(row.gf * 100).toFixed(0)}%</td>
                    </tr>`;
                });
            } else {
                tableHTML = 
                `<div style="overflow: auto;">
                    <table class="table table-striped table-sm" style="min-width:300px; width: 100%; table-layout: fixed;">
                        <thead>
                            <tr>
                                <th class="phase-column"></th>
                                <th class="text-xs" style="padding-left: 0px;">M</th>
                                <th class="depth-column text-sm" style="padding-left: 0px; padding-right:0px;">Depth</th>
                                <th class="text-sm" style="padding-left: 0px; padding-right:0px;">Time</th>
                                <th class="text-sm" style="padding-left: 0px; padding-right:0px;">RT</th>
                                <th class="text-sm" style="padding-left: 0px; padding-right:0px;">Gas</th>
                                <th class="text-sm hide-on-mobile" style="padding-left: 0px; padding-right:0px;">PPO2</th>
                                <th class="text-sm hide-on-mobile" style="padding-left: 0px; padding-right:0px;">GF</th>
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

                GFL = document.getElementById("labelGFL").textContent
                GFH = document.getElementById("labelGFH").textContent
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
                document.getElementById("labelWhatIfRunTimeDiff").innerText="()";
                document.getElementById("labelWhatIfDecoTime").innerText="-";
                document.getElementById("labelWhatIfDecoTimeDiff").innerText="()";
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
                labelRTDiff.innerText = `(${sign}${Math.abs(difference)})`;

                // Change color based on sign
                labelRTDiff.style.color = difference >= 0 ? "#f44335" : "#4caf50";

                let labelDT = document.getElementById("labelWhatIfDecoTime");
                let labelDTDiff = document.getElementById("labelWhatIfDecoTimeDiff");
                
            
                labelDT.innerText = `${Math.round(filter1RTDT[1])} min`;
                difference = Math.round(filter1RTDT[1] - baselineRTDT[1]);
                sign = difference >= 0 ? "+" : "-";

                // Update text with the correct sign
                labelDTDiff.innerText = `(${sign}${Math.abs(difference)})`;

                // Change color based on sign
                labelDTDiff.style.color = difference >= 0 ? "#f44335" : "#4caf50";
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
                let labelRTDiff = document.getElementById("labelWhatIfRunTimeDiff").innerText="()";
                let labelDT = document.getElementById("labelWhatIfDecoTime").innerText="-";
                let labelDTDiff = document.getElementById("labelWhatIfDecoTimeDiff").innerText="()";
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
                labelRTDiff.innerText = `(${sign}${Math.abs(difference)})`;

                // Change color based on sign
                labelRTDiff.style.color = difference >= 0 ? "#f44335" : "#4caf50";

                let labelDT = document.getElementById("labelWhatIfDecoTime");
                let labelDTDiff = document.getElementById("labelWhatIfDecoTimeDiff");
                
            
                labelDT.innerText = `${Math.round(filter2RTDT[1])} min`;
                difference = Math.round(filter2RTDT[1] - baselineRTDT[1]);
                sign = difference >= 0 ? "+" : "-";

                // Update text with the correct sign
                labelDTDiff.innerText = `(${sign}${Math.abs(difference)})`;

                // Change color based on sign
                labelDTDiff.style.color = difference >= 0 ? "#f44335" : "#4caf50";
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
                let labelRT = document.getElementById("labelWhatIfRunTime").innerText="-";
                let labelRTDiff = document.getElementById("labelWhatIfRunTimeDiff").innerText="()";
                let labelDT = document.getElementById("labelWhatIfDecoTime").innerText="-";
                let labelDTDiff = document.getElementById("labelWhatIfDecoTimeDiff").innerText="()";
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

                // update new decoT and newRT
            
                let labelRT = document.getElementById("labelWhatIfRunTime");
                let labelRTDiff = document.getElementById("labelWhatIfRunTimeDiff");
                
                labelRT.innerText = `${Math.round(filter3RTDT[0])} min`;
                var difference = Math.round(filter3RTDT[0] - baselineRTDT[0]);
                var sign = difference >= 0 ? "+" : "-";

                // Update text with the correct sign
                labelRTDiff.innerText = `(${sign}${Math.abs(difference)})`;

                // Change color based on sign
                labelRTDiff.style.color = difference >= 0 ? "#f44335" : "#4caf50";

                let labelDT = document.getElementById("labelWhatIfDecoTime");
                let labelDTDiff = document.getElementById("labelWhatIfDecoTimeDiff");
                
            
                labelDT.innerText = `${Math.round(filter3RTDT[1])} min`;
                difference = Math.round(filter3RTDT[1] - baselineRTDT[1]);
                sign = difference >= 0 ? "+" : "-";

                // Update text with the correct sign
                labelDTDiff.innerText = `(${sign}${Math.abs(difference)})`;

                // Change color based on sign
                labelDTDiff.style.color = difference >= 0 ? "#f44335" : "#4caf50";
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
                let labelRTDiff = document.getElementById("labelWhatIfRunTimeDiff").innerText="()";
                let labelDT = document.getElementById("labelWhatIfDecoTime").innerText="-";
                let labelDTDiff = document.getElementById("labelWhatIfDecoTimeDiff").innerText="()";
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
                labelRTDiff.innerText = `(${sign}${Math.abs(difference)})`;

                // Change color based on sign
                labelRTDiff.style.color = difference >= 0 ? "#f44335" : "#4caf50";

                let labelDT = document.getElementById("labelWhatIfDecoTime");
                let labelDTDiff = document.getElementById("labelWhatIfDecoTimeDiff");
                
            
                labelDT.innerText = `${Math.round(filter4RTDT[1])} min`;
                difference = Math.round(filter4RTDT[1] - baselineRTDT[1]);
                sign = difference >= 0 ? "+" : "-";

                // Update text with the correct sign
                labelDTDiff.innerText = `(${sign}${Math.abs(difference)})`;

                // Change color based on sign
                labelDTDiff.style.color = difference >= 0 ? "#f44335" : "#4caf50";
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
                let labelRTDiff = document.getElementById("labelWhatIfRunTimeDiff").innerText="()";
                let labelDT = document.getElementById("labelWhatIfDecoTime").innerText="-";
                let labelDTDiff = document.getElementById("labelWhatIfDecoTimeDiff").innerText="()";
                
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
                labelRTDiff.innerText = `(${sign}${Math.abs(difference)})`;

                // Change color based on sign
                labelRTDiff.style.color = difference >= 0 ? "#f44335" : "#4caf50";

                let labelDT = document.getElementById("labelWhatIfDecoTime");
                let labelDTDiff = document.getElementById("labelWhatIfDecoTimeDiff");
                
            
                labelDT.innerText = `${Math.round(filter5RTDT[1])} min`;
                difference = Math.round(filter5RTDT[1] - baselineRTDT[1]);
                sign = difference >= 0 ? "+" : "-";

                // Update text with the correct sign
                labelDTDiff.innerText = `(${sign}${Math.abs(difference)})`;

                // Change color based on sign
                labelDTDiff.style.color = difference >= 0 ? "#f44335" : "#4caf50";
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
                let labelRTDiff = document.getElementById("labelWhatIfRunTimeDiff").innerText="()";
                let labelDT = document.getElementById("labelWhatIfDecoTime").innerText="-";
                let labelDTDiff = document.getElementById("labelWhatIfDecoTimeDiff").innerText="()";

                
                
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
                labelRTDiff.innerText = `(${sign}${Math.abs(difference)})`;

                // Change color based on sign
                labelRTDiff.style.color = difference >= 0 ? "#f44335" : "#4caf50";

                let labelDT = document.getElementById("labelWhatIfDecoTime");
                let labelDTDiff = document.getElementById("labelWhatIfDecoTimeDiff");
                
            
                labelDT.innerText = `${Math.round(filter6RTDT[1])} min`;
                difference = Math.round(filter6RTDT[1] - baselineRTDT[1]);
                sign = difference >= 0 ? "+" : "-";

                // Update text with the correct sign
                labelDTDiff.innerText = `(${sign}${Math.abs(difference)})`;

                // Change color based on sign
                labelDTDiff.style.color = difference >= 0 ? "#f44335" : "#4caf50";
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
                let labelRT = document.getElementById("labelWhatIfRunTime").innerText="-";
                let labelRTDiff = document.getElementById("labelWhatIfRunTimeDiff").innerText="()";
                let labelDT = document.getElementById("labelWhatIfDecoTime").innerText="-";
                let labelDTDiff = document.getElementById("labelWhatIfDecoTimeDiff").innerText="()";

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
                labelRTDiff.innerText = `(${sign}${Math.abs(difference)})`;

                // Change color based on sign
                labelRTDiff.style.color = difference >= 0 ? "#f44335" : "#4caf50";

                let labelDT = document.getElementById("labelWhatIfDecoTime");
                let labelDTDiff = document.getElementById("labelWhatIfDecoTimeDiff");
                
            
                labelDT.innerText = `${Math.round(filter7RTDT[1])} min`;
                difference = Math.round(filter7RTDT[1] - baselineRTDT[1]);
                sign = difference >= 0 ? "+" : "-";

                // Update text with the correct sign
                labelDTDiff.innerText = `(${sign}${Math.abs(difference)})`;

                // Change color based on sign
                labelDTDiff.style.color = difference >= 0 ? "#f44335" : "#4caf50";
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
                let depthSlider = document.getElementById("depthSlider");

                if (depthSlider && selectedDepth) {
                    depthSlider.noUiSlider.set(selectedDepth); // Updates slider value
                }
            });
        });

    </script>

    {{-- Script to switch from OC to CC --}}
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const navLinks = document.querySelectorAll('#nav-tabs .nav-link');
            openCircuitTab = navLinks[0];
            closedCircuitTab = navLinks[1];
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
                // Update padding bottom dynamically
                bottomGasstackedBarChart.options.layout.padding.bottom = 4; // New padding bottom value
                bottomGasstackedBarChart.options.layout.padding.top = 20;
                labelHorizontalOffset = -40;
                // Refresh chart to apply changes
                setPointOCOrCC = 1.4;
                bottomGasO2Slider.noUiSlider.set(setPointOCOrCC * 100 / (depth / 33 + 1));
                bottomGasstackedBarChart.update();

                //update label descriptors
                labelMaxDepthPPO2Description.textContent = "Max depth PPO2";
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
                // Update padding bottom dynamically
                bottomGasstackedBarChart.options.layout.padding.bottom = 30; // New padding bottom value
                bottomGasstackedBarChart.options.layout.padding.top = 0;
                labelHorizontalOffset = -3;
                // Refresh chart to apply changes
                setPointOCOrCC = 1.2;
                bottomGasO2Slider.noUiSlider.set(setPointOCOrCC * 100 / (depth / 33 + 1));
                bottomGasstackedBarChart.update();

                //update label descriptors
                labelMaxDepthPPO2Description.textContent = "Dil Max depth PPO2";
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
                showDecoGas1();
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
        document.addEventListener("DOMContentLoaded", function() {
            sidebarColor(document.getElementById("sidebarColorDiv")); // Execute the sidebarColor function once the HTML is loaded
        });
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
            let originalText = "Dil Max Depth PPO2";
            if(modeOCOrCC == "OC")
                originalText = "Max Depth PPO2";
            
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
                    label.textContent = "Max PPO2";
                else
                    label.textContent = "Dil Max PPO2";
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
            const navLinks = document.querySelectorAll("#nav-tabs .nav-link");

            navLinks.forEach(link => {
                link.addEventListener("click", function () {
                    // Remove 'active' class from all tabs
                    navLinks.forEach(nav => nav.classList.remove("active"));
                    
                    // Add 'active' class to the clicked tab
                    this.classList.add("active");

                    // Save selected tab in local storage (optional)
                    localStorage.setItem("activeTab", this.innerText.trim());
                });
            });

            // Restore active tab after page reload or resize
            const savedTab = localStorage.getItem("activeTab");
            if (savedTab) {
                navLinks.forEach(link => {
                    if (link.innerText.trim() === savedTab) {
                        link.classList.add("active");
                    } else {
                        link.classList.remove("active");
                    }
                });
            }
        });
    </script>

    {{-- Scripts to manage gas consumption --}}
    <script>
        function calculateGasConsumption(diveData) {
            const sacBottomGas = parseFloat(document.getElementById('labelSACBottomGas').textContent);
            const sacDecoGas = parseFloat(document.getElementById('labelSACDecoGas').textContent);

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
            labelSACBottomGas.textContent = parseFloat(sliderSACBottomGasValue).toFixed(1);
            labelSACBottomGasLiters.textContent = parseFloat(sliderSACBottomGasValue * 28.3168).toFixed(0);
            
            // update gas consumption table
            if(globalResponse != null) {
                if(modeOCOrCC === "OC") {
                    gasConsumption = calculateGasConsumption(globalResponse['baseline']);
                    renderGasConsumptionTable(gasConsumption, "bottom");
                }
            }

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
            labelSACDecoGas.textContent = parseFloat(sliderSACDecoGasValue).toFixed(1);
            labelSACDecoGasLiters.textContent = parseFloat(sliderSACDecoGasValue * 28.3168).toFixed(0);
            
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

        
    </script>

    
    @endpush
</x-page-template>

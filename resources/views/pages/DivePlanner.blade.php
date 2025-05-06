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

                .right-label-normal {
                    text-align: right;      /* Align text on the right */
                    border: 2px solid #49a3f1; /* Add box for the label */
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
                
                <div class="p-0 mt-0 mx-2 border-radius-lg py-3 pe-1">
                    <div style="float: left;">
                        <h2 class="card-title text-info mx-3 mt-0">Decompression Dive Planner</h2>
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
                            <div class="row mt-n2">
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
                                                    <li><a class="dropdown-item" href="#" data-depth="<?php echo htmlspecialchars($site->maxDepth); ?>">
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
                                    <div class="text-secondary text-xs font-weight-bolder opacity-7 text-center mt-2" style="border: none;">Max Depth (ft)</div> 
                                </div>

                            </div>
                            <div class="row mt-2" style="border-bottom: 1px solid #D3D3D3;">
                                <div class="col-12 d-flex justify-content-center align-items-center">
                                    <div>
                                        <label class="text-info right-label-normal custom-label text-lg" id="labelDepth">Bottom PPO2</label>
                                        <label class="text-info">ft</label>
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
                                        
                                        <div class="label-container">
                                            
                                            <label class="text-info right-label-normal custom-label text-sm" id="labelDes">100</label>
                                            <label class="text-info">ft/min</label>
                                        </div>
                                        
                                        <div class="slider-styled" id="desSlider"></div>
                                        <div class="text-secondary text-xs font-weight-bolder opacity-7 text-center mt-2" style="border: none;">Descend Rate</div>
                                    </div>

                                    <div class="mt-n2 mb-2">
                                        <input type="hidden" id="ascSlider-value" name="ascSlider-value">
                                        
                                        <div class="label-container">
                                            
                                            <label class="text-info right-label-normal custom-label text-sm" id="labelAsc">30</label>
                                            <label class="text-info">ft/min</label>
                                        </div>
                                        
                                        <div class="slider-styled" id="ascSlider"></div>
                                        <div class="text-secondary text-xs font-weight-bolder opacity-7 text-center mt-2" style="border: none;">Ascend Rate</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Row gases -->
                            <div class="row">
                                <div class="col-lg-3 col-12 align-items-center" style="border-bottom: 1px solid #D3D3D3;">
                                    <table class="table align-items-center mb-0 mt-1"> 
                                        <tr><td class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center" style="border: none;">Bottom gas</td> </tr>
                                    </table>

                                    <div style="padding: 5px; border: 2px solid #1A73E8; border-radius: 4px; margin-top: 5px;">
                                        <div class="row" style="display: flex; justify-content: center;">
                                            <div class="row" style="display: flex; justify-content: center;">
                                                <div class="mt-n6" style="position: relative; width: 150px; height: 300px;">
                                                    <!-- Overlaying image -->
                                                        <img id="tank_double" src="{{ asset("assets") }}/img/tank_double.png"   alt="Overlay Image" 
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
                                                    <div class="text-secondary text-xs font-weight-bolder opacity-7 text-center mt-2" style="border: none;">He %</div>
                                                </div>
                                            </div>

                                            <div class="row mt-3">
                                                <div class="label-container">
                                                    <label>Max depth PPO2</label>
                                                    <label class="text-info right-label-normal custom-label" id="labelBottomGasPPO2">1.4</label>
                                                    <label class="text-info">atm</label>
                                                </div>

                                                <div class="label-container">
                                                    <label>Equivalent Narcotic Depth</label>
                                                    <label class="text-info right-label-normal custom-label" id="labelBottomGasEND">90</label>
                                                    <label class="text-info">ft</label>
                                                </div>

                                                <div class="label-container">
                                                    <label>Gas density</label>
                                                    <label class="text-info right-label-normal custom-label" id="labelBottomGasDensity">90</label>
                                                    <label class="text-info">g/l</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <!-- Decompression gases -->
                                <div class="col-lg-9 col-12" style="border-bottom: 1px solid #D3D3D3; background-color: #ffffff; padding-bottom:0px;">
                                    <table class="table align-items-center mb-0 mt-1"> 
                                        <tr><td class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center" style="border: none;">Decompression gases</td> </tr>
                                    </table>

                                    <div class="row">
                                        <!-- Deco 1 -->
                                        <div class="col-lg-3 col-12 position-relative" id="deco1" style="margin-right: 0px; margin-left:0px; margin-bottom: 10px; padding: 5px; border: 0px solid #1A73E8; border-radius: 4px; background-color: #ffffff;">
                                            <div style="padding: 10px; border: 2px solid #1A73E8; border-radius: 4px;">
                                                
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
                                                        <div class="text-secondary text-xs font-weight-bolder opacity-7 text-center mt-2" style="border: none;">He %</div>
                                                    </div>
                                                </div>

                                                <div class="row mt-3">
                                                    <div>
                                                        <input type="hidden" id="decoGas1SwitchSlider-value" name="decoGas1SwitchSlider-value">

                                                        <div class="label-container">                                                
                                                            <label class="text-info">PPO2</label>
                                                            <label class="text-info left-label custom-label text-sm" id="labelDecoGas1SwitchPPO2">2222</label>
                                                            
                                                            <label class="text-info right-label-normal custom-label text-sm" id="labelDecoGas1Switch">2222</label>
                                                            <label class="text-info">ft</label>
                                                        </div>
                                                        <div class="slider-styled" id="decoGas1SwitchSlider"></div>
                                                        <div class="text-secondary text-xs font-weight-bolder opacity-7 text-center mt-2" style="border: none;">Switch depth</div>
                                                    </div>
                                                </div>

                                                <div class="row mt-3">
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
                                            <div style="padding: 10px; border: 2px solid #1A73E8; border-radius: 4px;">
                                                
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
                                                        <div class="text-secondary text-xs font-weight-bolder opacity-7 text-center mt-2" style="border: none;">He %</div>
                                                    </div>
                                                </div>

                                                <div class="row mt-3">
                                                    <div>
                                                        <input type="hidden" id="decoGas2SwitchSlider-value" name="decoGas2SwitchSlider-value">

                                                        <div class="label-container">                                                
                                                            <label class="text-info">PPO2</label>
                                                            <label class="text-info left-label custom-label text-sm" id="labelDecoGas2SwitchPPO2">2222</label>
                                                            
                                                            <label class="text-info right-label-normal custom-label text-sm" id="labelDecoGas2Switch">2222</label>
                                                            <label class="text-info">ft</label>
                                                        </div>
                                                        <div class="slider-styled" id="decoGas2SwitchSlider"></div>
                                                        <div class="text-secondary text-xs font-weight-bolder opacity-7 text-center mt-2" style="border: none;">Switch depth</div>
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
                                            <div style="padding: 10px; border: 2px solid #1A73E8; border-radius: 4px;">
                                                
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
                                                        <div class="text-secondary text-xs font-weight-bolder opacity-7 text-center mt-2" style="border: none;">He %</div>
                                                    </div>
                                                </div>

                                                <div class="row mt-3">
                                                    <div>
                                                        <input type="hidden" id="decoGas3SwitchSlider-value" name="decoGas3SwitchSlider-value">

                                                        <div class="label-container">                                                
                                                            <label class="text-info">PPO2</label>
                                                            <label class="text-info left-label custom-label text-sm" id="labelDecoGas3SwitchPPO2">2222</label>
                                                            
                                                            <label class="text-info right-label-normal custom-label text-sm" id="labelDecoGas3Switch">2222</label>
                                                            <label class="text-info">ft</label>
                                                        </div>
                                                        <div class="slider-styled" id="decoGas3SwitchSlider"></div>
                                                        <div class="text-secondary text-xs font-weight-bolder opacity-7 text-center mt-2" style="border: none;">Switch depth</div>
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
                                            <div style="padding: 10px; border: 2px solid #1A73E8; border-radius: 4px;">
                                                
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
                                                        <div class="text-secondary text-xs font-weight-bolder opacity-7 text-center mt-2" style="border: none;">He %</div>
                                                    </div>
                                                </div>

                                                <div class="row mt-3">
                                                    <div>
                                                        <input type="hidden" id="decoGas4SwitchSlider-value" name="decoGas4SwitchSlider-value">

                                                        <div class="label-container">                                                
                                                            <label class="text-info">PPO2</label>
                                                            <label class="text-info left-label custom-label text-sm" id="labelDecoGas4SwitchPPO2">2222</label>
                                                            
                                                            <label class="text-info right-label-normal custom-label text-sm" id="labelDecoGas4Switch">2222</label>
                                                            <label class="text-info">ft</label>
                                                        </div>
                                                        <div class="slider-styled" id="decoGas4SwitchSlider"></div>
                                                        <div class="text-secondary text-xs font-weight-bolder opacity-7 text-center mt-2" style="border: none;">Switch depth</div>
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
                                            Calculate Deco Profile
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

                                <!-- Row with deco table and profile chart -->
                                <div class="row mt-2">

                                    <div class="col-lg-3 col-12">
                                        <div id="decoTableContainer"></div>
                                    </div>

                                    <div class="col-lg-9 col-12" id="profileChartContainer">
                                        <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1"> 
                                            <canvas id="profileChart" class="chart-canvas border-radius-lg" height="500px"></canvas>
                                        </div>
                                    </div>
                                </div>

                                <!-- What if row -->
                                <div class="row">
                                    <div class="col-lg-12 col-12 mt-2">
                                        <div class="card-header p-0 mt-0 mx-3 position-relative" style="z-index: 100;">
                                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                                <h3 class="card-title text-white mx-4">What if...?</h3>
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
                                                                    for="flexSwitchCheckDefault" data-bs-toggle="tooltip" data-bs-placement="top" title="Dive 10 ft deeper, how is RT and DT changed?">Increase max depth by 10 ft</label>
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
                                                                    for="flexSwitchCheckDefault" data-bs-toggle="tooltip" data-bs-placement="top" title="What's the impact of diving 10 ft shallower than planned?">Reduce max depth by 10 ft</label>
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
    <script src="../../assets/js/plugins/chartjs.min.js"></script>
    
    <script src="{{ asset('assets') }}/js/plugins/nouislider.js"></script>
    <link href="{{ asset('assets') }}/css/nouislider.css" rel="stylesheet">

    <script src="../../assets/js/plugins/chartjs.min.js"></script>

    {{-- Script to communicate with server -> deco profile calculation --}}
    <script>
        let baselineRTDT = [];
        let filter1RTDT = [];
        let filter2RTDT = [];
        let filter3RTDT = [];
        let filter4RTDT = [];
        let filter5RTDT = [];
        // Set up the CSRF token for AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Attach click event to the "Calculate NDL" button
        $('#calculateDecoProfile').on('click', function () {
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

            var decoGases = [];

            function addDecoGas(iconId, o2LabelId, heLabelId, switchLabelId) {
                if (document.getElementById(iconId).style.display === 'none') {
                    decoGases.push({
                        O2: parseInt(document.getElementById(o2LabelId).textContent),
                        He: parseInt(document.getElementById(heLabelId).textContent),
                        switchDepth: parseInt(document.getElementById(switchLabelId).textContent)
                    });
                }
            }

            // Add gases dynamically if the icons are hidden
            addDecoGas("addGasIcon1", "labelDecoGas1O2", "labelDecoGas1He", "labelDecoGas1Switch");
            addDecoGas("addGasIcon2", "labelDecoGas2O2", "labelDecoGas2He", "labelDecoGas2Switch");
            addDecoGas("addGasIcon3", "labelDecoGas3O2", "labelDecoGas3He", "labelDecoGas3Switch");
            addDecoGas("addGasIcon4", "labelDecoGas4O2", "labelDecoGas4He", "labelDecoGas4Switch");

            // Create final JSON structure
            const diveProfile = {
                maxDepth: maxDepth,
                bottomTime: bottomTime,
                bottomGas: bottomGas,
                gradientFactors: GFs,
                rate: rate,
                surfaceTime: surfTime,
                decoGases: decoGases
            };

            // Convert to JSON string
            const diveJSON = JSON.stringify(diveProfile, null, 4);
            console.log(diveJSON);


            <?php
                $DivePlannerKey = base64_decode(env('DIVE_PLANNING_API_KEY'));
            ?>
            // Make the AJAX POST request
            $.ajax({
                url: `https://decoplanningapi.azurewebsites.net/api/DecoPlanner?code=<?php echo $DivePlannerKey; ?>`,
                method: 'POST',
                contentType: 'application/json',  // Ensures JSON format
                data: JSON.stringify({inputs: diveProfile}), // Converts data to JSON
                crossDomain: true,  // Explicitly allow CORS
                success: function (response) {
                    console.log('Success:', response);
                    renderProfileChart(response);
                    baselineRTDT = generateDecoTable(response['baseline']);
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

                    // reset all checkboxes
                    document.querySelectorAll(".form-check-input").forEach(cb => {
                            cb.checked = false; // Uncheck all other checkboxes
                    });

                    if(diveProfile['decoGases'].length === 0) {
                        document.getElementById('filter3Container').style.display = "none";
                    } else {
                        document.getElementById('filter3Container').style.display = "block";
                    }
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
    </script>

    {{-- Gas density --}}
    <script>
        function updateGasDensity(O2, He, depth, label) {
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
            const gasDensity = (
                (fractionO2 * molecularWeights.O2 +
                fractionN2 * molecularWeights.N2 +
                fractionHe * molecularWeights.He) *
                ambientPressure
            ) / 22.4; // Use 22.4 L/mol at standard temperature and pressure

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
    </script>
    {{-- Bottom gas chart --}}
    <script>
        
        // Get the canvas element
        const bottomGasStackedBarChartElement = document.getElementById('bottomGasStackedBar').getContext('2d');

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
                                    ctx.fillText(data + '%', bar.x - 40, bar.y + 10); // Position label slightly above the bar
                                    
                                } else if (data == 100) {
                                    ctx.font = '12px Roboto';
                                    ctx.fillStyle = '#FFF'; // Label color
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'middle'; // Centers text vertically
                                    ctx.fillText(data + '%', bar.x -10 , bar.y + 70); // Position label slightly above the bar
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
                                    ctx.fillText(data + '%', bar.x -10 , bar.y + 70); // Position label slightly above the bar
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
                                    ctx.fillText(data + '%', bar.x -10 , bar.y + 70); // Position label slightly above the bar
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
                                    ctx.fillText(data + '%', bar.x -10 , bar.y + 70); // Position label slightly above the bar
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
                                    ctx.fillText(data + '%', bar.x -10 , bar.y + 70); // Position label slightly above the bar
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
            
            document.getElementById("profileChartAndTable").style.display = "none";

        });
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

            document.getElementById("profileChartAndTable").style.display = "none";
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

            document.getElementById("profileChartAndTable").style.display = "none";
        });
    </script>

    {{-- Scripts slider Asc/Des rates --}}
    <script>
        var desSlider = document.getElementById('desSlider');
        var labelDes = document.getElementById('labelDes');


        noUiSlider.create(desSlider, {
            start: 60,
            connect: [true, false],
            range: {
                'min': 10,
                'max': 120
            },
            step: 10,
            

        });

        // Hide the tick mark labels
        var desSliderTicks = desSlider.querySelectorAll('.noUi-value-sub');
        desSliderTicks.forEach(function (desSlider) {
            desSlider.style.display = 'none';
        });

        desSlider.noUiSlider.on('update', function (values, handle) {
            var desSliderValue = values[handle];
            labelDes.textContent = parseInt(desSliderValue);

            document.getElementById("profileChartAndTable").style.display = "none";
        });

        var ascSlider = document.getElementById('ascSlider');
        var labelAsc = document.getElementById('labelAsc');


        noUiSlider.create(ascSlider, {
            start: 30,
            connect: [true, false],
            range: {
                'min': 10,
                'max': 60
            },
            step: 10,
            

        });

        // Hide the tick mark labels
        var ascSliderTicks = ascSlider.querySelectorAll('.noUi-value-sub');
        ascSliderTicks.forEach(function (ascSlider) {
            ascSlider.style.display = 'none';
        });

        ascSlider.noUiSlider.on('update', function (values, handle) {
            var ascSliderValue = values[handle];
            labelAsc.textContent = parseInt(ascSliderValue);

            document.getElementById("profileChartAndTable").style.display = "none";
        });
    </script>

    {{-- Scripts slider Bottom gas O2 and He --}}
    <script>
        //var depth = parseInt(document.getElementById("labelDepth").textContent);
        @if( !is_null($currentSite))
            var depth = currentSite.maxDepth;
        @else
            var depth = 100;    // default depth
        @endif

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
        
            var ambientPressure = depth / 33 +1;
            var bottomGasPPHe = ambientPressure * parseInt(labelBottomGasHe.textContent) / 100;
            var bottomGasENDPressure = ambientPressure - bottomGasPPHe;
            var bottomGasEND = (bottomGasENDPressure - 1 ) * 33;
            labelBottomGasEND.textContent = Math.max(0,(bottomGasEND).toFixed(0));

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

            document.getElementById("profileChartAndTable").style.display = "none";

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

            var ambientPressure = depth / 33 +1;
            var bottomGasPPHe = ambientPressure * parseInt(labelBottomGasHe.textContent) / 100;
            var bottomGasENDPressure = ambientPressure - bottomGasPPHe;
            var bottomGasEND = (bottomGasENDPressure - 1 ) * 33;
            labelBottomGasEND.textContent = Math.max(0,(bottomGasEND).toFixed(0));

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

            updateGasDensity(parseInt(labelBottomGasO2.textContent), parseInt(labelBottomGasHe.textContent), depth, labelBottomGasDensity);

            document.getElementById("profileChartAndTable").style.display = "none";
        });

    </script>

     {{-- Slider depth --}}
     <script>
        var depthSlider = document.getElementById('depthSlider');
        var labelDepth = document.getElementById('labelDepth');

        //console.log("Current Site Max Depth:", currentSite.maxDepth);
        let startDepth = currentSite ? currentSite.maxDepth : 100;
        //console.log("Slider Start Depth:", startDepth);

        noUiSlider.create(depthSlider, {
            start: startDepth,
            connect: [true, false],
            range: {
                'min': 10,
                'max': 450
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
            labelDepth.textContent = parseInt(depthSliderValue);

            //bottomGasO2Slider.noUiSlider.set(bottomGasO2Slider.noUiSlider.get());
            //bottomGasHeSlider.noUiSlider.set(bottomGasHeSlider.noUiSlider.get());

            bottomGasO2Slider.noUiSlider.set(Math.round((1.4 / (depthSliderValue / 33 + 1) * 100)));
            bottomGasHeSlider.noUiSlider.set(((1 - ((80 / 33) +1) / (depthSliderValue / 33 + 1)) * 100).toFixed(0));


            document.getElementById("profileChartAndTable").style.display = "none";
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

            document.getElementById("profileChartAndTable").style.display = "none";
            

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
                'max': 100
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
                'min': 0.5,
                'max': 1.6
            },
            step: 0.1,
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
                    'max': 1.6   // Update the maximum value to 120
                }
            });

            document.getElementById("profileChartAndTable").style.display = "none";

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

            document.getElementById("profileChartAndTable").style.display = "none";

        });

        // Hide the tick mark labels
        var decoGas1SwitchSliderTicks = decoGas1SwitchSlider.querySelectorAll('.noUi-value-sub');
        decoGas1SwitchSliderTicks.forEach(function (decoGas1SwitchSlider) {
            decoGas1SwitchSlider.style.display = 'none';
        });

        decoGas1SwitchSlider.noUiSlider.on('update', function (values, handle) {
            var decoGas1SwitchSliderValue = values[handle];
            labelDecoGas1SwitchPPO2.textContent = parseFloat(decoGas1SwitchSliderValue).toFixed(1);

            var O2Content = labelDecoGas1O2.textContent / 100;
            
            labelDecoGas1Switch.textContent = (((decoGas1SwitchSliderValue / O2Content) - 1 ) * 33).toFixed(0);

            document.getElementById("profileChartAndTable").style.display = "none";
        });
        
        function showDecoGas1() {
            document.getElementById("addGasIcon1").style.display = "none"; // Hide add gas icon
            document.getElementById("profileChartAndTable").style.display = "none";
        }

        function hideDecoGas1() {
            document.getElementById("addGasIcon1").style.display = "flex"; // Hide add gas icon
            document.getElementById("profileChartAndTable").style.display = "none";
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

            document.getElementById("profileChartAndTable").style.display = "none";
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

            document.getElementById("profileChartAndTable").style.display = "none";
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
            
            labelDecoGas2Switch.textContent = (((decoGas2SwitchSliderValue / O2Content) - 1 ) * 33).toFixed(0);

            document.getElementById("profileChartAndTable").style.display = "none";
        });
        
        function showDecoGas2() {
            document.getElementById("addGasIcon2").style.display = "none"; // Hide add gas icon
            document.getElementById("profileChartAndTable").style.display = "none";
        }

        function hideDecoGas2() {
            document.getElementById("addGasIcon2").style.display = "flex"; // Hide add gas icon
            document.getElementById("profileChartAndTable").style.display = "none";
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

            document.getElementById("profileChartAndTable").style.display = "none";
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

            document.getElementById("profileChartAndTable").style.display = "none";
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
            
            labelDecoGas3Switch.textContent = (((decoGas3SwitchSliderValue / O2Content) - 1 ) * 33).toFixed(0);
            document.getElementById("profileChartAndTable").style.display = "none";
        });
        
        function showDecoGas3() {
            document.getElementById("addGasIcon3").style.display = "none"; // Hide add gas icon
            document.getElementById("profileChartAndTable").style.display = "none";
        }

        function hideDecoGas3() {
            document.getElementById("addGasIcon3").style.display = "flex"; // Hide add gas icon
            document.getElementById("profileChartAndTable").style.display = "none";
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

            document.getElementById("profileChartAndTable").style.display = "none";
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

            document.getElementById("profileChartAndTable").style.display = "none";
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
            
            labelDecoGas4Switch.textContent = (((decoGas4SwitchSliderValue / O2Content) - 1 ) * 33).toFixed(0);

            document.getElementById("profileChartAndTable").style.display = "none";
        });
        
        function showDecoGas4() {
            document.getElementById("addGasIcon4").style.display = "none"; // Hide add gas icon
            document.getElementById("profileChartAndTable").style.display = "none";
        }

        function hideDecoGas4() {
            document.getElementById("addGasIcon4").style.display = "flex"; // Hide add gas icon
            document.getElementById("profileChartAndTable").style.display = "none";
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
        // Convert absolute pressure to depth in feet
        function absPressureToDepth(abs_p) {
            return Math.round((abs_p - 1) * 33);
        }

        function absPressureToDepthDeco(abs_p) {
            return Math.round(((abs_p - 1) * 33) / 10) * 10;
        }

        let profileChartInstance = null; // Global variable to store chart instance

        function renderProfileChart(response) {
            // Convert data into correct format for a scatter plot
            formattedData = response['baseline'].map(item => ({ x: item.time, y: -(item.abs_p - 1) * 33 }));
            formattedData1 = response['add5min'].map(item => ({ x: item.time, y: -(item.abs_p - 1) * 33 }));
            formattedData2 = response['add10ft'].map(item => ({ x: item.time, y: -(item.abs_p - 1) * 33 }));
            formattedData3 = response['lostDecoGas'].map(item => ({ x: item.time, y: -(item.abs_p - 1) * 33 }));
            formattedData4 = response['short5min'].map(item => ({ x: item.time, y: -(item.abs_p - 1) * 33 }));
            formattedData5 = response['short10ft'].map(item => ({ x: item.time, y: -(item.abs_p - 1) * 33 }));

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
                            borderColor: '#1A73E8',
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

                                    return `RT: ${formattedTime}, Depth: ${roundedDepth} ft`;
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
                            title: { display: true, text: 'Depth (ft)', color: 'white' }, // Label color white
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

        function generateDecoTable(response) {
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


            let tableHTML = 
            `<div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th class="phase-column"></th>
                            <th class="depth-column text-sm" style="padding-left: 0px; padding-right:0px;">Depth</th>
                            <th class="text-sm" style="padding-left: 0px; padding-right:0px;">Time</th>
                            <th class="text-sm" style="padding-left: 0px; padding-right:0px;">RT</th>
                            <th class="text-sm" style="padding-left: 0px; padding-right:0px;">Gas</th>
                            <th class="text-sm" style="padding-left: 0px; padding-right:0px;">GF</th>
                        </tr>
                    </thead>
                    <tbody>`;

            tableData.forEach(row => {
                tableHTML += `<tr>
                    <td class="text-info">${getPhaseIcon(row.phase)}</td> <!-- Display icon instead of text -->
                    <td>${row.depth}</td>
                    <td class="text-sm text-left">${formatTime(row.time)}</td>
                    <td class="text-sm">${formatTime(row.runtime)}</td>
                    <td class="text-sm">${row.gas}</td>
                    <td class="text-sm">${(row.gf * 100).toFixed(0)}%</td>
                </tr>`;
            });

            tableHTML += `</tbody></table></div>`;
            document.getElementById("decoTableContainer").innerHTML = tableHTML;

            GFL = document.getElementById("labelGFL").textContent
            GFH = document.getElementById("labelGFH").textContent
            model = "ZH-L16C-GF";
            updateDecoSummary(totalDecoTime, totalRuntime, model, GFH, GFL);

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
                        borderColor: '#1A73E8',
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

            

        });

        document.getElementById("filter2").addEventListener("change", function() {
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
                        borderColor: '#1A73E8',
                        backgroundColor: 'rgba(255, 255, 255, 0.5)',
                        borderWidth: 2,
                        showLine: true,
                        fill: true,
                        pointRadius: 0,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Increase max depth 10ft',
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

            
        });

        document.getElementById("filter3").addEventListener("change", function() {
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
                        borderColor: '#1A73E8',
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

            
        }); 

        document.getElementById("filter4").addEventListener("change", function() {
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
                        borderColor: '#1A73E8',
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

            
        }); 

        document.getElementById("filter5").addEventListener("change", function() {
            if (!this.checked) {
                profileChartInstance.data.datasets = profileChartInstance.data.datasets.filter(dataset => dataset.label === "Deco profile");
                let labelRT = document.getElementById("labelWhatIfRunTime").innerText="-";
                let labelRTDiff = document.getElementById("labelWhatIfRunTimeDiff").innerText="()";
                let labelDT = document.getElementById("labelWhatIfDecoTime").innerText="-";
                let labelDTDiff = document.getElementById("labelWhatIfDecoTimeDiff").innerText="()";
                
            } else {
                profileChartInstance.data.datasets = [
                    {
                        label: 'Reduce max depth 10ft',
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
                        borderColor: '#1A73E8',
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

            
        }); 
    </script>


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

    {{--  Script to change the color of the sidemenu to theme --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            sidebarColor(document.getElementById("sidebarColorDiv")); // Execute the sidebarColor function once the HTML is loaded
        });
    </script>
    @endpush
</x-page-template>

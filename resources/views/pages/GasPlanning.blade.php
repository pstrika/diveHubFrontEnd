<x-page-template bodyClass='g-sidenav-show  bg-gray-200' :SEO="$SEO">
    
    <x-auth.navbars.sidebar activePage="planningTools" activeItem="gasPlanning" activeSubitem=""></x-auth.navbars.sidebar>
    
    
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <style>
            /* ------ Default Style ---------- */
            .gauge-container {
            width: 150px;
            height: 70px;
            display: block;
            float: center;
            padding: 10px;
            /*background-color: #222;*/
            margin: 7px;
            border-radius: 3px;
            position: relative;
            }
            .gauge-container > .label {
            position: absolute;
            right: 0;
            top: 0;
            display: inline-block;
            background: rgba(0,0,0,0.5);
            font-family: monospace;
            font-size: 0.8em;
            padding: 5px 10px;
            }
            .gauge-container > .gauge .dial {
            stroke: #334455;
            stroke-width: 2;
            fill: rgba(0,0,0,0);
            }
            .gauge-container > .gauge .value {
            stroke: rgb(47, 227, 255);
            stroke-width: 2;
            fill: rgba(0,0,0,0);
            }
            .gauge-container > .gauge .value-text {
            fill: rgb(47, 227, 255);
            font-family: sans-serif;
            font-weight: bold;
            font-size: 0.8em;
            }
            /* ------- Alternate Style ------- */
            .wrapper {
            height: 100px;
            float: left;
            margin: 7px;
            overflow: hidden;
            }
            .wrapper > .gauge-container {
            margin: 0;
            }
            .gauge-container.two {
            }
            .gauge-container.two > .gauge .dial {
            stroke: #334455;
            stroke-width: 10;
            }
            .gauge-container.two > .gauge .value {
            stroke: orange;
            stroke-dasharray: none;
            stroke-width: 13;
            }
            .gauge-container.two > .gauge .value-text {
            fill: #ccc;
            font-weight: 100;
            font-size: 1em;
            }

            /* ----- Alternate Style ----- */
            .gauge-container.five > .gauge .dial {
            stroke: #D3D3D3;
            stroke-width: 15;
            }
            .gauge-container.five > .gauge .value {
            stroke: #F8774B;
            stroke-dasharray: 25 1;
            stroke-width: 15;
            }
            .gauge-container.five > .gauge .value-text {
            fill: transparent;
            font-size: 0.7em;
            }

        </style>

        <style>
            iframe {
                aspect-ratio: 16 / 9; /* Set the desired aspect ratio (16:9 for YouTube) */
                height: auto; /* Let the height adjust automatically */
                width: 100%; /* Fill the available width */
            }

            {{--Code to change the color of the input text--}}
            .input-group.input-group-dynamic .form-control, .input-group.input-group-dynamic .form-control:focus, .input-group.input-group-static .form-control, .input-group.input-group-static .form-control:focus {
                    background-image: linear-gradient(0deg, #2F88EC 2px, rgba(156, 39, 176, 0) 0), linear-gradient(0deg, #d2d2d2 1px, rgba(209, 209, 209, 0) 0);
                    border-radius: 0 !important;
                }

            .input-group.input-group-dynamic.is-focused label, .input-group.input-group-static.is-focused label {
                color: #2F88EC;
            }
        </style>

        <style>
            .label-container {
                display: flex;          /* Enable Flexbox layout */
                justify-content: space-between; /* Push labels to opposite ends */
                align-items: center;    /* Ensure vertical alignment */
            }

            .left-label {
                text-align: left;       /* Align text on the left */
                margin-right: auto;     /* Push it to the far left */
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
        </style>

        <!-- Navbar -->
        <x-auth.navbars.navs.auth pageTitle="Best Gases"></x-auth.navbars.navs.auth>
        <!-- End Navbar -->
        <div class="container-fluid py-0">

            <div class="d-none" data-color="info" id="sidebarColorDiv"></div>

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


            <!--modal success rating-->
            @if(session('msg'))
            <div class="modal fade" id="modal-notification" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="modal-notification" aria-hidden="true">
                <div class="modal-dialog modal-danger modal-dialog-centered modal-" role="document">
                    <div class="modal-content">
                        <div class="modal-header text-center">
                            <h6 class="modal-title font-weight-normal" id="modal-title-notification">Notification</h6>
                            
                        </div>
                        <div class="modal-body">
                            <div class="py-3 text-center">
                            <i class="material-icons h1 text-secondary">
                                task_alt
                            </i>
                            <h4 class="text-gradient text-info mt-4">{{ session('msg') }}</h4>
                            <p>Press anywhere outside this dialog to continue</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

   
            <div class="page-header min-height-200 max-height-300 border-radius-xl mt-4 mx-0" style="background-image: url('/assets/img/illustrations/best_gases.jpg');">
                <span class="mask  bg-gradient-secondary  opacity-4"></span>
            </div>

            <div class="card p-0 position-relative mt-n5 mx-3 z-index-2 mb-4" style="background-color: rgba(255, 255, 255, 1.0);">
                <div class="p-0 mt-0 mx-2  border-radius-lg py-3 pe-1">
                    <div style="float: left;">
                        <h1 class="card-category text-info mx-4 mt-3 text-xl">Best Gas Calculator</h1>
                    </div>
                </div>
            </div>

            {{-- Card Gases --}}        
            <div class="row mx-2">
                
                <div class="col-md-12">             
                    <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                        
                        <div class="card-body" id="gasesCardBody">
                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="label-container">
                                        <label class="left-label" id="label1">Set Max depth</label>
                                        <label class="text-info right-label-normal custom-label" id="labelDepth">Bottom PPO2</label>
                                        <label class="text-info">ft</label>
                                    </div>
                                    
                                    <div class="slider-styled" id="sliderDepth"></div> 
                                </div>   
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    
                                    <div class="nav-wrapper position-relative end-0">
                                        <ul class="nav nav-pills nav-fill p-1" role="tablist" id="nav-tabs">
                                            <li class="nav-item">
                                                <a class="nav-link mb-0 px-0 py-1 active" href="#" data-tag="OC">Open Circuit</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link mb-0 px-0 py-1" href="#" data-tag="CC">Closed Circuit</a>
                                            </li>
                                        </ul>
                                    </div>
                            
                                </div>
                            </div>
                            <div class="row" id="CC" hidden>

                                <div class="col-12 col-lg-4 col-sm-12 col-md-4" style="border-bottom: 1px solid #D3D3D3;">
                                    <div class="row" style="display: flex; justify-content: center;">
                                        <div class="mt-n6" style="position: relative; width: 150px; height: 300px;">
                                            <!-- Overlaying image -->
                                            <img id="tankCCR" src="{{ asset("assets") }}/img/ccr.png" alt="Overlay Image" 
                                                style="position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 150%; height: 75%; z-index: 10;">

                                            <img id="unblendable_sign_CCR" src="{{ asset("assets") }}/img/unblendable_sign.png" hidden alt="Overlay Image" 
                                                style="position: absolute; top: 70%; left: 50%; transform: translate(-50%, -50%); z-index: 10;">

                                            
                                            <!-- Fixed-size chart canvas -->
                                            <div style="width: 300px; heigth:300px; position: absolute; bottom: 0; left: 50%; transform: translateX(-50%);">
                                                <canvas id="stackedBarChartCCR" 
                                                        style="width: 90%; height: 177px; position: absolute; bottom: 10px; left: 0; transform: none; z-index: 1;"></canvas>
                                            </div>

                                            <!-- <canvas id="stackedBarChart" 
                                                    style="position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 100px; height: 161px; z-index: 1;"></canvas> -->
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-5">
                                            <table class="table align-items-center mb-0"> 
                                                <tr><td class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center" style="border: none;">Diluent mix</td> </tr>
                                            </table>
                                            <div class="label-container">
                                                <label class="left-label text-success" id="mainLabel">Oxygen</label>
                                                <label class="text-success right-label-success custom-label" id="labelMixO2CCR">21%</label>
                                            </div>
                                            <div class="label-container" id="label-container-mix-He-CCR">
                                                <label class="left-label text-info" id="mainLabel">Helium</label>
                                                <label class="text-info right-label-normal custom-label" id="labelMixHeCCR">35%</label>
                                            </div>
                                            <div class="label-container">
                                                <label class="left-label text-secondary" id="mainLabel">Nitrogen</label>
                                                <label class="text-secondary right-label-secondary custom-label" id="labelMixN2CCR">47%</label>
                                            </div>
                                        </div>
                                        <div class="col-7">
                                            <table class="table align-items-center mb-0"> 
                                                <tr><td class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center" style="border: none;">Gas prices</td> </tr>
                                            </table>
                                            <table class="table"> 
                                                <tr>
                                                    <td class="text-secondary text-xs opacity-10 text-left" style="border: none;">Diluent tank</td>
                                                    <td id="diluentPrice" class="text-secondary text-xs opacity-10 text-right" style="border: none; text-align: end;">$35.50</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-secondary text-xs opacity-10 text-left" style="border: none;">O2 tank</td>
                                                    <td id="O2Price" class="text-secondary text-xs opacity-10 text-right" style="border: none; text-align: end;">$10.00</td>
                                                </tr>
                                                
                                            </table>
                                            <table class="table mt-n2">
                                                <tr id="closeMixRowCCR" hidden style="border-top: 1px solid #D3D3D3;">
                                                    <td class="text-info text-xs opacity-10 text-left" style="border: none;">Closest standard mix</td>
                                                    <td id="closeMixCCR" class="text-info font-weight-bolder text-xs opacity-10 text-right" style="border: none; text-align: end;">-</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-8">
                                    <table class="table align-items-center mb-0"> 
                                        <tr><td class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center" style="border: none;">Diluent</td> </tr>
                                    </table>
                                    <div class="mt-n2">
                                        <input type="hidden" id="sliderPPO2CCR-value" name="txsliderPPO2CCR">
                                        
                                        <!-- Flex container for label alignment -->
                                        <div class="label-container">
                                            <label class="left-label" id="mainLabelTx">Diluent PPO2 at max depth</label>
                                            <label class="text-info right-label-normal custom-label" id="txlabelPPO2CCR">0.9</label>
                                            <label class="text-info">atm</label>
                                        </div>
                                        
                                        <div class="slider-styled" id="txsliderPPO2CCR"></div>
                                        <div class="text-secondary text-xs font-weight-bolder opacity-7 text-center mt-2" style="border: none;">O2 Content Diluent</div>
                                    </div>

                                    <div class="mt-0">
                                        <input type="hidden" id="sliderSetPoint-value" name="sliderSetPoint-input">
                                        
                                        <!-- Flex container for label alignment -->
                                        <div class="label-container">
                                            <label class="left-label" id="mainLabelTx">Set Point</label>
                                            <label class="text-info right-label-normal custom-label" id="labelSetPoint">1.2</label>
                                            <label class="text-info">atm</label>
                                        </div>
                                        
                                        <div class="slider-styled" id="sliderSetPoint"></div>
                                        <div class="text-secondary text-xs font-weight-bolder opacity-7 text-center mt-2" style="border: none;">Set Point</div>
                                    </div>

                                    <div class="mt-2">
                                        <input type="hidden" id="sliderHeCCR-value" name="sliderHeCCR">
                                        
                                        <!-- Flex container for label alignment -->
                                        <div class="label-container">
                                            <label class="left-label" id="ENDLabelMax">END at max depth</label>
                                            <label class="text-info right-label-normal custom-label" id="labelENDCCR"></label>
                                            <label class="text-info">ft</label>
                                        </div>
                                        
                                        
                                        <div class="slider-styled" id="sliderHeCCR"></div>
                                        <div class="text-secondary text-xs font-weight-bolder opacity-7 text-center mt-2" style="border: none;">He Content Diluent</div>
                                        <div class="form-container" style="display: flex; justify-content: space-between; align-items: center;">
                                            <div class="form-check form-switch ps-0">
                                                <input name="O2narcoticCCR" class="form-check-input ms-auto" type="checkbox"
                                                    id="O2NarcoticCCR" checked value="1">
                                                <label class="form-check-label text-body ms-3"
                                                    for="O2NarcoticCCR">O2 narcotic?</label>
                                            </div>
                                            
                                        </div>
                                    </div>

                                    <div class="mt-n2">
                                        <input type="hidden" id="sliderTempCCR-value" name="sliderTempCCR">
                                        
                                        <!-- Flex container for label alignment -->
                                        <div class="label-container">
                                            <label class="left-label">Loop temperature at depth</label>
                                            <label class="text-info right-label-normal custom-label" id="labelTempCCR"></label>
                                            <label class="text-info">°F/°C</label>
                                        </div>
                                        
                                        
                                        <div class="slider-styled" id="sliderTempCCR"></div>
                                        <div class="text-secondary text-xs font-weight-bolder opacity-7 text-center mt-2" style="border: none;">Loop temperature</div>
                                        <div class="form-container" style="display: flex; justify-content: space-between; align-items: center;">
                                            <div class="form-check form-switch ps-0">
                                                <input name="waterVapor" class="form-check-input ms-auto" type="checkbox"
                                                    id="waterVapor" checked value="1">
                                                <label class="form-check-label text-body ms-3"
                                                    for="waterVapor">Consider H2O vapor?</label>
                                            </div>
                                            <div class="label-container" style="text-align: right;">
                                                <label class="left-label" id="denisityCCR" style="padding-right: 10px;">Gas density</label>
                                                <label class="text-info right-label-normal custom-label" id="gasDensityCCR"></label>
                                                <label class="text-info">g/L</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-2">
                                        <table class="table mb-0" style="border: top; width: 100%;"> 
                                            <tbody>
                                                <tr class="mt-n4">
                                                    <table style="width: 100%;">
                                                        <tr>
                                                            
                                                            <td class="mt-n4" style="border: none; text-align: right; width: 49%;">
                                                                <label class="text-info text-lg font-weight-bolder" id="txBestO2CCR">32</label>
                                                            </td>
                                                            <td class="mt-n4" style="border: none; width: 2%; text-align: center;">
                                                                <label class="text-info text-lg font-weight-bolder">/</label>
                                                            </td>
                                                            <td class="mt-n4" style="border: none; text-align: left; width: 49%;">
                                                                <label class="text-info text-lg font-weight-bolder" id="txBestHeCCR">45</label>
                                                            </td>
                                                        
                                                        </tr>
                                                        
                                                    </table>
                                                </tr>
                                                <tr>
                                                    <td style="border: none;">
                                                    <div class="text-center align-items-center mt-n3 mb-n2" id="txhypoxicCCR" style="display: flex; justify-content: center; align-items: center;">
                                                        <label class="text-danger text-sm font-weight-bolder" >Hypoxic at surface</label>
                                                    </div>

                                                    </td>
                                                </tr>
                                                <tr class="text-center align-items-center">
                                                    <td class="text-center align-items-center" style="border: none;"> <!-- Added text-center here -->
                                                        <div class="text-center align-items-center mt-0">
                                                            <a type="button" class="btn btn-info mt-0" id="buttonBestDiluent">
                                                                Calculate Best Diluent
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                                
                                            </tbody> 
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="row" id="OC">
                                <div class="col-12 col-lg-4 col-sm-12 col-md-4" style="border-bottom: 1px solid #D3D3D3;">
                                    <div class="row" style="display: flex; justify-content: center;">
                                        <div class="mt-n6" style="position: relative; width: 150px; height: 300px;">
                                            <!-- Overlaying image -->
                                            <img id="tank_single" src="{{ asset("assets") }}/img/tank_single.png"  hidden alt="Overlay Image" 
                                                style="position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 150%; height: 75%; z-index: 10;">

                                            <img id="tank_double" src="{{ asset("assets") }}/img/tank_double.png"   alt="Overlay Image" 
                                                style="position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 150%; height: 75%; z-index: 10;">

                                            <img id="unblendable_sign" src="{{ asset("assets") }}/img/unblendable_sign.png" hidden alt="Overlay Image" 
                                                style="position: absolute; top: 70%; left: 50%; transform: translate(-50%, -50%); z-index: 10;">

                                            
                                            <!-- Fixed-size chart canvas -->
                                            <div style="width: 300px; heigth:300px; position: absolute; bottom: 0; left: 50%; transform: translateX(-50%);">
                                                <canvas id="stackedBarChart" 
                                                        style="width: 100%; height: 202px; position: absolute; bottom: 0; left: 0; transform: none; z-index: 1;"></canvas>
                                            </div>

                                            <!-- <canvas id="stackedBarChart" 
                                                    style="position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 100px; height: 161px; z-index: 1;"></canvas> -->
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-5">
                                            <table class="table align-items-center mb-0"> 
                                                <tr><td class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center" style="border: none;">Gas mix</td> </tr>
                                            </table>
                                            <div class="label-container">
                                                <label class="left-label text-success" id="mainLabel">Oxygen</label>
                                                <label class="text-success right-label-success custom-label" id="labelMixO2">Bottom PPO2</label>
                                            </div>
                                            <div class="label-container" id="label-container-mix-He">
                                                <label class="left-label text-info" id="mainLabel">Helium</label>
                                                <label class="text-info right-label-normal custom-label" id="labelMixHe">Bottom PPO2</label>
                                            </div>
                                            <div class="label-container">
                                                <label class="left-label text-secondary" id="mainLabel">Nitrogen</label>
                                                <label class="text-secondary right-label-secondary custom-label" id="labelMixN2">Bottom PPO2</label>
                                            </div>
                                        </div>
                                        <div class="col-7">
                                            <table class="table align-items-center mb-0"> 
                                                <tr><td id="tankConf" class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center" style="border: none;">Gas prices</td> </tr>
                                            </table>
                                            <table class="table"> 
                                                <tr>
                                                    <td class="text-secondary text-xs opacity-10 text-left" style="border: none;">Aluminum 80</td>
                                                    <td id="tank80" class="text-secondary text-xs opacity-10 text-right" style="border: none; text-align: end;">$10</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-secondary text-xs opacity-10 text-left" style="border: none;">Steel HP 100</td>
                                                    <td id="tank100" class="text-secondary text-xs opacity-10 text-right" style="border: none; text-align: end;">$10</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-secondary text-xs opacity-10 text-left" style="border: none;">Steel LP 85</td>
                                                    <td id="tank85" class="text-secondary text-xs opacity-10 text-right" style="border: none; text-align: end;">$10</td>
                                                </tr>
                                                
                                            </table>
                                            <table class="table mt-n2">
                                                <tr id="closeMixRow" hidden style="border-top: 1px solid #D3D3D3;">
                                                    <td class="text-info text-xs opacity-10 text-left" style="border: none;">Closest standard mix</td>
                                                    <td id="closeMix" class="text-info font-weight-bolder text-xs opacity-10 text-right" style="border: none; text-align: end;">-</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            
                                                <div class="label-container" id="NDLContainer" style="display:none;">
                                                    <!-- Highlighted max depth -->
                                                    <label class="left-label">NDL at <span class="text-info" style="font-weight: bold;" id="NDLDepthLabel">xxx ft</span> - 24 hr min surface interval</label>

                                                    <!-- NDL result -->
                                                    <label class="text-info right-label-normal custom-label" id="ndlResult">-</label>
                                                    <label class="text-info">m</label>
                                                </div>
                                                <div class="text-center" style="border: none;"> <!-- Added text-center here -->
                                                    <a type="button" class="btn btn-info mt-0" id="calculateNDLButton">
                                                        Calculate NDL
                                                    </a>
                                                </div>
                                            
                                            <!-- Legend directly below the first label -->
                                            <div class="text-center mt-n2">
                                                <label class="text-center text-danger text-xs">Always use a dive computer</label>
                                            </div>
                                                    
                                        </div>
                                    </div>
                                </div>


                                
                                    <div class="col-md-4" style="border-bottom: 1px solid #D3D3D3;">
                                    
                                        <table class="table align-items-center mb-0"> 
                                            <tr><td class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center" style="border: none;">Nitrox</td> </tr>
                                        </table>
                                        <div class="mt-n2">
                                            <input type="hidden" id="sliderPPO2-value" name="sliderPPO2">
                                            
                                            <!-- Flex container for label alignment -->
                                            <div class="label-container">
                                                <label class="left-label" id="mainLabel">PPO2 at max depth</label>
                                                <label class="text-info right-label-normal custom-label" id="labelPPO2">Bottom PPO2</label>
                                                <label class="text-info">atm</label>
                                            </div>
                                            
                                            
                                            <div class="slider-styled" id="sliderPPO2"></div>
                                        </div>
                                        <div class="mt-0">
                                            <table class="table align-items-center mb-0" style="border: top;"> 
                                                <tr>
                                                    <td class="text-secondary text-xs font-weight-bolder opacity-7 text-center" style="border: none;">O2 Content</td>
                                                </tr>
                                                <tr class="mt-n4">
                                                    <td class="text-center mt-n4" style="border: none;">
                                                        <label class="text-info text-lg font-weight-bolder" id="bestNitrox">32%</label>
                                                    </td>
                                                </tr>
                                                <tr >
                                                    <td class="text-center" style="border: none;"> <!-- Added text-center here -->
                                                        <a type="button" class="btn btn-secondary mt-n4 w-100" id="buttonBestNitrox">
                                                            Calculate Best Nitrox
                                                        </a>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>

                                    </div>
                                

                                
                                        <div class="col-md-4">
                                    
                                            <table class="table align-items-center mb-0"> 
                                                <tr><td class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center" style="border: none;">Trimix</td> </tr>
                                            </table>
                                            <div class="mt-n2">
                                                <input type="hidden" id="sliderPPO2-value" name="txsliderPPO2">
                                                
                                                <!-- Flex container for label alignment -->
                                                <div class="label-container">
                                                    <label class="left-label" id="mainLabelTx">PPO2 at max depth</label>
                                                    <label class="text-info right-label-normal custom-label" id="txlabelPPO2">Bottom PPO2</label>
                                                    <label class="text-info">atm</label>
                                                </div>
                                                
                                                
                                                <div class="slider-styled" id="txsliderPPO2"></div>
                                                <div class="text-secondary text-xs font-weight-bolder opacity-7 text-center mt-2" style="border: none;">O2 Content</div>
                                            </div>
                                            <div class="mt-2">
                                                <input type="hidden" id="sliderPPO2-value" name="txsliderPPHe">
                                                
                                                <!-- Flex container for label alignment -->
                                                <div class="label-container">
                                                    <label class="left-label" id="ENDLabelMax">END at max depth</label>
                                                    <label class="text-info right-label-normal custom-label" id="txlabelEND"></label>
                                                    <label class="text-info">ft</label> 
                                                </div>
                                                
                                                
                                                <div class="slider-styled" id="txsliderHe"></div>
                                                <div class="text-secondary text-xs font-weight-bolder opacity-7 text-center mt-2" style="border: none;">He Content</div>
                                                <div class="form-container" style="display: flex; justify-content: space-between; align-items: center;">
                                                    <div class="form-check form-switch ps-0">
                                                        <input name="O2narcotic" class="form-check-input ms-auto" type="checkbox"
                                                            id="O2Narcotic" checked value="1">
                                                        <label class="form-check-label text-body ms-3"
                                                            for="O2Narcotic">O2 narcotic?</label>
                                                    </div>
                                                    <div class="label-container" style="text-align: right;">
                                                        <label class="left-label" id="denisity" style="padding-right: 10px;">Gas density</label>
                                                        <label class="text-info right-label-normal custom-label" id="gasDensity"></label>
                                                        <label class="text-info">g/L</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-2">
                                                <table class="table mb-0" style="border: top; width: 100%;"> 
                                                    <tbody>
                                                        <tr class="mt-n4">
                                                            <table style="width: 100%;">
                                                                <tr>
                                                                    
                                                                    <td class="mt-n4" style="border: none; text-align: right; width: 49%;">
                                                                        <label class="text-info text-lg font-weight-bolder" id="txbestNitrox">32%</label>
                                                                    </td>
                                                                    <td class="mt-n4" style="border: none; width: 2%; text-align: center;">
                                                                        <label class="text-info text-lg font-weight-bolder">/</label>
                                                                    </td>
                                                                    <td class="mt-n4" style="border: none; text-align: left; width: 49%;">
                                                                        <label class="text-info text-lg font-weight-bolder" id="txbestHe">32%</label>
                                                                    </td>
                                                                
                                                                </tr>
                                                                
                                                            </table>
                                                        </tr>
                                                        <tr>
                                                            <td style="border: none;">
                                                            <div class="text-center align-items-center mt-n3 mb-n2" id="txhypoxic" style="display: flex; justify-content: center; align-items: center;">
                                                                <label class="text-danger text-sm font-weight-bolder" >Hypoxic at surface</label>
                                                            </div>

                                                            </td>
                                                        </tr>
                                                        <tr class="text-center align-items-center">
                                                            <td class="text-center align-items-center" style="border: none;"> <!-- Added text-center here -->
                                                                <div class="text-center align-items-center mt-0">
                                                                    <a type="button" class="btn btn-info mt-0 w-100" id="txbuttonBestNitrox">
                                                                        Calculate Best Trimix
                                                                    </a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        
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
            
            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>
    
    
    {{--<x-plugins></x-plugins>--}}
    
    @push('js')
    
    <script src="{{ asset('assets') }}/js/plugins/flatpickr.min.js"></script>
    <script src="{{ asset('assets') }}/js/plugins/jquery-3.6.0.min.js" type="text/javascript"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/rateYo/2.3.2/jquery.rateyo.min.js"></script>
    <script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/3.5.0/model-viewer.min.js"></script>
   
    <script src="{{ asset('assets') }}/js/plugins/nouislider.js"></script>
    <link href="{{ asset('assets') }}/css/nouislider.css" rel="stylesheet">
    <script src="../../assets/js/plugins/chartjs.min.js"></script>

    <script>
        var depth =100;
        var ambientPressure = depth / 33 +1;
        var sliderDepth = document.getElementById('sliderDepth');
        var labelDepth = document.getElementById('labelDepth');
        
        var sliderValueInput = document.getElementById('slider-value');
        var labelBestNitrox = document.getElementById('bestNitrox');
        



        noUiSlider.create(sliderDepth, {
            start: 100,
            connect: [true, false],
            range: {
                'min': 10,
                'max': 450
            },
            step: 1,
            

        });

        // Hide the tick mark labels
        var tickLabels = sliderDepth.querySelectorAll('.noUi-value-sub');
        tickLabels.forEach(function (label) {
            label.style.display = 'none';
        });
            
        // Listen for the 'update' event
        
        sliderDepth.noUiSlider.on('update', function (values, handle) {
            depth = values[handle];
            ambientPressure = depth / 33 +1;
            labelDepth.textContent = Number(depth).toFixed(0);

            // force calculation of best Nitrox
            document.getElementById("buttonBestNitrox").click();
            document.getElementById("txbuttonBestNitrox").click();

            //force calculation on ccr
            updateCCRSliders();

            // if the depth slider is changed, we enable the Calculate NDL button
            document.getElementById("calculateNDLButton").classList.add("btn-info");
            document.getElementById("calculateNDLButton").classList.remove('btn-secondary');
            document.getElementById('NDLContainer').style.display = "none";

            if(depth > 130) {
                //if depth is beyond recreational we hide the NDL calculation 
                document.getElementById("calculateNDLButton").style.display="none";
                document.getElementById('NDLContainer').style.display = "none";
            } else {
                document.getElementById("calculateNDLButton").style.display="block";
                //document.getElementById('NDLContainer').style.display = "block";
            }

            
            
            


        });
    </script>
    
    <script>
        // Scipt to manage navigation on gases
        document.querySelectorAll('#nav-tabs a').forEach(link => {
            link.addEventListener('click', function(event) {
                event.preventDefault(); // Prevent the default behavior of scrolling to the top
                
                // Add any custom logic for handling clicks here
                const tag = this.getAttribute('data-tag'); // Get the data-tag value
                if (tag == "OC") {
                    document.getElementById("CC").setAttribute("hidden", "true");
                    document.getElementById("OC").removeAttribute("hidden"); // Show the row
                } else {
                    document.getElementById("OC").setAttribute("hidden", "true");
                    document.getElementById("CC").removeAttribute("hidden"); // Show the row
                }
                console.log('Clicked on:', tag); // Example action
            });
        });

    </script>

    <script>

        function blendGas(targetO2, targetHe, targetPressure) {
            // Fixed air composition
            const airO2 = 0.21; // Oxygen in air
            const airN2 = 0.79; // Nitrogen in air

            // Validate inputs
            if (targetO2 + targetHe > 1) {
                //throw new Error("Mix is unachievable: fractions exceed 1.");
                return 0;
            }
            if (targetO2 < 0 || targetHe < 0 || targetPressure <= 0) {
                //throw new Error("Invalid inputs: fractions and pressure must be positive.");
                return 0;
            }

            // Calculate target nitrogen fraction
            const targetN2 = 1 - targetO2 - targetHe;

            // Check if nitrogen fraction is achievable using air
            if (targetN2 < 0 || targetN2 > airN2) {
                //throw new Error("Mix is unachievable: nitrogen fraction outside bounds.");
                return 0;
            }

            // Solve for PSI contributions
            let psiAir = targetN2 / airN2 * targetPressure; // PSI of air required
            let psiO2 = (targetO2 - airO2 * psiAir / targetPressure) * targetPressure; // PSI of pure oxygen required
            let psiHe = targetHe * targetPressure; // PSI of pure helium required

            // Validate results
            if (psiO2 < 0 || psiHe < 0 || psiAir < 0) {
                //throw new Error("Mix is unachievable: negative PSI calculated.");
                return 0;
            }

            // Return the results
            return {
                oxygenPSI: psiO2,
                heliumPSI: psiHe,
                airPSI: psiAir
            };
        }


        // Script to update gas prices
        function updateGasPrices() {
            labelAl80 = document.getElementById("tank80");
            labelSt85 = document.getElementById("tank85");
            labelSt100 = document.getElementById("tank100");
            signUnblendable = document.getElementById("unblendable_sign");
            
            tankConf = document.getElementById("tankConf").textContent;

            // Get the text content of the label
            const labelTextHe = document.getElementById("labelMixHe").textContent;
            // Strip the '%' and convert the number to a fraction
            const he = parseFloat(labelTextHe.replace('%', '')) / 100;
            const labelTextO2 = document.getElementById("labelMixO2").textContent;
            // Strip the '%' and convert the number to a fraction
            const o2 = parseFloat(labelTextO2.replace('%', '')) / 100;
            const n2 = 1 - o2 - he;

            const O2Price = 0;
            const HePrice = 4; //cuft
            const AirPrice = 9;
            const NitroxPrice = 13;

            // Suggest closest standard mix
            //console.log("----------------------Hiding row----------------------------");
            document.getElementById("closeMixRow").setAttribute("hidden", "true");

            // Define the array of standard gas mixes
            const gasArray = [
                { mix: "21/35", O2: 0.21, He: 0.35 },
                { mix: "18/45", O2: 0.18, He: 0.45 },
                { mix: "10/50", O2: 0.10, He: 0.50 },
                { mix: "36%", O2: 0.36, He: 0 },
                { mix: "32%", O2: 0.32, He: 0 },
                { mix: "Air", O2: 0.21, He: 0 },
            ];

            // Iterate over each gas mix to find the closest match
            gasArray.forEach(element => {
                if ((o2 - element.O2) < 0.03 && (o2 - element.O2) >= -0.01) { // Check within 4% difference
                    //console.log("he=" + he + " element.He=" + element.He + " Diff=" + Math.abs(he - element.He));
                    if( Math.abs(he - element.He) <= 0.1) { 
                        //console.log("Unhidding closeMixLabel");
                        document.getElementById("closeMix").textContent = element.mix; // Use element.mix, not this->mix
                        document.getElementById("closeMixRow").removeAttribute("hidden"); // Show the row
                    }
                }
            });

            //check if the gas is blendable
            if (blendGas(o2, he, 3000) == 0) {
                labelAl80.textContent = "-";
                labelSt85.textContent = "-" ;
                labelSt100.textContent = "-";
                document.getElementById("unblendable_sign").removeAttribute("hidden"); // Removes 'hidden' attribute from the first image
                return;
                
            }
            else
                document.getElementById("unblendable_sign").setAttribute("hidden", "true"); // Adds 'hidden' attribute to the second image
            // case for air
            if(he == 0 && o2 == 0.21) {
                price80 = AirPrice;
                price85 = AirPrice;
                price100 = AirPrice;
            } else if (he == 0) {
                price80 = NitroxPrice;
                price85 = NitroxPrice;
                price100 = NitroxPrice;
            } else {
                price80 = he * 80 * HePrice;
                price85 = he * 85 * HePrice;
                price100 = he * 100 * HePrice;
            }

            // doubles or singles?
            if (tankConf.includes("Doubles")) {
                price80 = price80 * 2;
                price85 = price85 * 2;
                price100 = price100 * 2;
            }
            

            labelAl80.textContent = "$" + price80.toFixed(2);
            labelSt85.textContent = "$" + price85.toFixed(2);
            labelSt100.textContent = "$" + price100.toFixed(2);

            


            return;
        }
    </script>
    
    
    

    <script>

    let labelHorizontalOffset = -40; // Initial offset value

    // Get the canvas element
    const ctx = document.getElementById('stackedBarChart').getContext('2d');

    // Create the chart with a custom plugin for labels
    const stackedBarChart = new Chart(ctx, {
        type: 'bar', // Bar chart type
        data: {
            labels: [''], // X-axis labels
            datasets: [
                {
                    label: 'Oxygen',
                    data: [18], // Data points for this dataset
                    backgroundColor: 'rgba(255, 99, 132, 0.6)', // Bar color
                    borderRadius: 0, // Rounded corners
                    barPercentage: 1 // Adjust bar width (smaller bars)
                },
                {
                    label: 'Helium',
                    data: [45], // Data points for this dataset
                    backgroundColor: 'rgba(54, 162, 235, 0.6)', // Bar color
                    borderRadius: 0, // Rounded corners
                    barPercentage: 1 // Adjust bar width (smaller bars)
                },
                {
                    label: 'Nitrogen',
                    data: [37], // Data points for this dataset
                    backgroundColor: 'rgba(75, 192, 192, 0.6)', // Bar color
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
                    left: 0,
                    right: 0,
                    top: 20,
                    bottom: 0
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
                            if (data) {
                                ctx.font = '14px Roboto';
                                ctx.fillStyle = '#FFF'; // Label color
                                ctx.textAlign = 'center';
                                ctx.textBaseline = 'middle'; // Centers text vertically
                                ctx.fillText(data + '%', bar.x + labelHorizontalOffset, bar.y + 10); // Position label slightly above the bar
                                
                            }
                        });
                    });
                }
            }
        ]
    });

    function updateLabelHorizontalOffset(newOffset) {
        labelHorizontalOffset = newOffset; // Update the global variable
        stackedBarChart.update(); // Refresh the chart to apply changes
    }


    function updateGasMix(oxygen, helium) {
        // Ensure the passed values are integers
        oxygen = parseInt(oxygen, 10);
        helium = parseInt(helium, 10);

        // Calculate Nitrogen
        const nitrogen = 100 - (oxygen + helium);

        // Validate that the sum doesn't exceed 100
        if (nitrogen < 0) {
            console.error("Error: The sum of Oxygen and Helium exceeds 100!");
            return;
        }

        // Update the chart data
        stackedBarChart.data.datasets = [
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
        stackedBarChart.update();
        

        var labelMixO2 = document.getElementById('labelMixO2');
        var labelMixHe = document.getElementById('labelMixHe');
        var labelMixN2 = document.getElementById('labelMixN2');

        labelMixO2.textContent = oxygen + '%';
        labelMixHe.textContent = helium + '%';
        labelMixN2.textContent = nitrogen + '%';

        var labelContainerHe = document.getElementById("label-container-mix-He");
        if(helium == 0) {
            labelContainerHe.style.display = "none";
        } else {
            labelContainerHe.style.display = "flex";
        }
            
    }

    </script>
    {{-- Slider script Nitrox --}}
    <script>
        var slider = document.getElementById('sliderPPO2');
        var label = document.getElementById('labelPPO2');
        
        var sliderValueInput = document.getElementById('slider-value');
        var labelBestNitrox = document.getElementById('bestNitrox');
        


        var bestMix =  Math.round((1.4 / (depth / 33 + 1) * 100));
        console.log("The value of bestMix is:", bestMix);

        noUiSlider.create(slider, {
            start: bestMix,
            connect: [true, false],
            range: {
                'min': 21,
                'max': 40
            },
            step: 1,
            

        });

        // Hide the tick mark labels
        var tickLabels = slider.querySelectorAll('.noUi-value-sub');
        tickLabels.forEach(function (label) {
            label.style.display = 'none';
        });
            
        // Listen for the 'update' event
        
        slider.noUiSlider.on('update', function (values, handle) {
            labelBestNitrox.textContent = Math.round(values[handle]) + '%';

            //enable the Calculate Best Nitrox button
            document.getElementById("buttonBestNitrox").classList.remove("btn-secondary");
            document.getElementById("buttonBestNitrox").classList.add("btn-info");
            

            var sliderValue = parseFloat(values[handle]); // Ensure sliderValue is numeric
            console.log("Slider Value:", sliderValue);

            var maxDepth = parseFloat(depth); // Ensure maxDepth is rendered as a number
            console.log("Max Depth:", maxDepth);

        

            var ppo2 = (((maxDepth / 33 + 1) * sliderValue) / 100).toFixed(2); // Calculate PPO2 and format
            console.log("PPO2:", ppo2);


            label.textContent = ppo2;
            

            // Check PPO2 threshold and set the appropriate class
            if (parseFloat(ppo2) > 1.59 || parseFloat(ppo2) < 0.16) {
                console.log("High PPO2 - Danger");
                label.classList.remove("text-info", "text-warning", "right-label-normal", "right-label-warning"); // Remove other classes
                label.classList.add("text-danger", "right-label-danger"); // Add "text-danger" class
            } else if (parseFloat(ppo2) > 1.41 || parseFloat(ppo2) < 0.21) {
                console.log("Medium PPO2 - Warning");
                label.classList.remove("text-info", "text-danger", "right-label-normal", "right-label-danger"); // Remove other classes
                label.classList.add("text-warning", "right-label-warning"); // Add "text-warning" class
            } else {
                console.log("Low PPO2 - Info");
                label.classList.remove("text-warning", "text-danger", "right-label-warning", "right-label-danger"); // Remove other classes
                label.classList.add("text-info", "right-label-normal"); // Add "text-info" class
            }

            

            console.log("slider value=",sliderValue);
            console.log("bestMix=", bestMix);
            if(sliderValue == bestMix) {
                labelBestNitrox.classList.remove("text-info");
                labelBestNitrox.classList.add("text-success");
            } else {
                labelBestNitrox.classList.add("text-info");
                labelBestNitrox.classList.remove("text-success");
            }
            updateGasMix(labelBestNitrox.textContent, 0);
            $('#ndlResult').text("-");
            //calculateNDL(depth, labelMixO2.textContent.slice(0, -1)/100, labelMixN2.textContent.slice(0, -1)/100, labelMixHe.textContent.slice(0, -1)/100);

            updateLabelHorizontalOffset(0);
            // JavaScript code to toggle visibility of images
            document.getElementById("tank_single").removeAttribute("hidden"); // Removes 'hidden' attribute from the first image
            document.getElementById("tank_double").setAttribute("hidden", "true"); // Adds 'hidden' attribute to the second image

            //update Gas price label
            document.getElementById("tankConf").innerText = "Gas Price (Single)";
            updateGasPrices();

            // if the slider is changed, we enable the Calculate NDL button
            document.getElementById("calculateNDLButton").classList.add("btn-info");
            document.getElementById("calculateNDLButton").classList.remove('btn-secondary');
            document.getElementById('NDLContainer').style.display = "none";

 
            });

            // Add an event listener to the button
            document.getElementById("buttonBestNitrox").addEventListener("click", function () {
                // Reset the slider to its start value
                //slider.noUiSlider.set(slider.noUiSlider.options.start);

                
                

                slider.noUiSlider.set(Math.max(Math.round((1.4 / (depth / 33 + 1) * 100)),21));
                updateLabelHorizontalOffset(0);
                document.getElementById("tank_single").removeAttribute("hidden"); // Removes 'hidden' attribute from the first image
                document.getElementById("tank_double").setAttribute("hidden", "true"); // Adds 'hidden' attribute to the second image
                //update Gas price label
                document.getElementById("tankConf").innerText = "Gas Price (Single)";
                updateGasPrices();

                // once the button is clicked, we disable the button (meaning that the screen is already showing the Best Nitrox)
                document.getElementById("buttonBestNitrox").classList.remove("btn-info");
                document.getElementById("buttonBestNitrox").classList.add("btn-secondary");
            });

    </script>




    {{-- Slider script Trimix --}}
    <script>

        var txlabelBestHe = document.getElementById('txbestHe');

        var txslider = document.getElementById('txsliderPPO2');
        var txlabel = document.getElementById('txlabelPPO2');
        
        var txsliderValueInput = document.getElementById('txslider-value');
        var txlabelBestNitrox = document.getElementById('txbestNitrox');
        var txhypoxic = document.getElementById("txhypoxic");
        var txlabelEND = document.getElementById("txlabelEND");
        
        var O2Narcotic = document.getElementById("O2Narcotic");
        var bestHe = ((1 - ((80 / 33) +1) / (parseFloat(depth) / 33 + 1)) * 100).toFixed(0);
        
        

        function updateGasDensity() {
            // Constants for molecular weights (g/mol)
            const molecularWeights = {
                O2: 32,
                N2: 28,
                He: 4
            };

            var fractionO2 = txlabel.textContent / 100;
            var fractionHe = txlabelBestHe.textContent / 100;
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
            const gasDensityLabel = document.getElementById("gasDensity");
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

        var txbestMix =  Math.round((1.4 / (depth / 33 + 1) * 100));
        console.log("The value of bestMix is:", txbestMix);

        noUiSlider.create(txsliderHe, {
            start: bestHe,
            connect: [true, false],
            range: {
                'min': 0,
                'max': 95
            },
            step: 1,
            

        });

        // Hide the tick mark labels
        var txtickLabelsHe = txsliderHe.querySelectorAll('.noUi-value-sub');
        txtickLabelsHe.forEach(function (txlabelHe) {
            txlabelHe.style.display = 'none';
        });

        txsliderHe.noUiSlider.on('update', function (values, handle) {
            txlabelBestHe.textContent = Math.round(values[handle]);

            //txlabelBestNitrox.textContent

            var txmaxDepth = parseFloat(depth); // Ensure maxDepth is rendered as a number
            var O2Factor = 0;

            if(O2Narcotic.checked) {
                O2Factor = 0;
            } else {
                O2Factor = Math.round(txlabelBestNitrox.textContent) / 100;   // Get O2 %
            }

            var equivPMax =  (txmaxDepth / 33 + 1) * (1 - Math.round(values[handle]) / 100 - O2Factor);
            var ENDMax = ((equivPMax - 1) * 33).toFixed(0);
            txlabelEND.textContent = ENDMax;

            



            if (ENDMax > 130) {
                txlabelEND.classList.remove("text-info", "text-warning", "right-label-normal", "right-label-warning"); // Remove other classes
                txlabelEND.classList.add("text-danger", "right-label-danger"); // Add "text-danger" class
            } else if (ENDMax > 100) {
                txlabelEND.classList.remove("text-info", "text-danger", "right-label-normal", "right-label-danger"); // Remove other classes
                txlabelEND.classList.add("text-warning", "right-label-warning"); // Add "text-warning" class
            } else {
                txlabelEND.classList.remove("text-warning", "text-danger", "right-label-warning", "right-label-danger"); // Remove other classes
                txlabelEND.classList.add("text-info", "right-label-normal"); // Add "text-info" class
            }

            

            if(txlabelBestHe.textContent == bestHe) {
                txlabelBestHe.classList.remove("text-info");
                txlabelBestHe.classList.add("text-success");
            } else {
                txlabelBestHe.classList.add("text-info");
                txlabelBestHe.classList.remove("text-success");
            }

           updateGasDensity();
           updateGasMix(txlabelBestNitrox.textContent, txlabelBestHe.textContent);
           $('#ndlResult').text("-");
            //update NDL
            //const gasMix = {O2: txlabelBestNitrox.textContent / 100, N2: (100 - txlabelBestNitrox.textContent - txlabelBestHe.textContent)/100, He: txlabelBestHe.textContent/100};
            //const ndl = calculateNDL(depth, gasMix);
            //labelNDL = document.getElementById('labelNDL');
            //labelNDL.textContent = ndl;
            updateLabelHorizontalOffset(-40);
            // JavaScript code to toggle visibility of images
            document.getElementById("tank_double").removeAttribute("hidden"); // Removes 'hidden' attribute from the first image
            document.getElementById("tank_single").setAttribute("hidden", "true"); // Adds 'hidden' attribute to the second image
            //update Gas price label
            document.getElementById("tankConf").innerText = "Gas Price (Doubles)";
            updateGasPrices();

            //enable button
            document.getElementById("txbuttonBestNitrox").classList.add("btn-info");
            document.getElementById("txbuttonBestNitrox").classList.remove("btn-secondary");

            // if the slider is changed, we enable the Calculate NDL button
            document.getElementById("calculateNDLButton").classList.add("btn-info");
            document.getElementById("calculateNDLButton").classList.remove('btn-secondary');
            document.getElementById('NDLContainer').style.display = "none";
            
        });

        

        noUiSlider.create(txslider, {
            start: txbestMix,
            connect: [true, false],
            range: {
                'min': 5,
                'max': 40
            },
            step: 1,
            

        });

        // Hide the tick mark labels
        var txtickLabels = txslider.querySelectorAll('.noUi-value-sub');
        txtickLabels.forEach(function (txlabel) {
            txlabel.style.display = 'none';
        });
            
        // Listen for the 'update' event
        
        txslider.noUiSlider.on('update', function (values, handle) {
            txlabelBestNitrox.textContent = Math.round(values[handle]);

            var txsliderValue = parseFloat(values[handle]); // Ensure sliderValue is numeric
            console.log("TX Slider Value:", txsliderValue);

            var txmaxDepth = parseFloat(depth); // Ensure maxDepth is rendered as a number
            console.log("TX Max Depth:", txmaxDepth);

            

            var txppo2 = (((txmaxDepth / 33 + 1) * txsliderValue) / 100).toFixed(2); // Calculate PPO2 and format
            console.log("TX PPO2:", txppo2);

            

            txlabel.textContent = txppo2;
            

            // Update MAX on He slider
            txsliderHe.noUiSlider.updateOptions({
                range: {
                    'min': 0,    // Keep the minimum value as is
                    'max': 95-txsliderValue   // Update the maximum value to 120
                }
            });


            // Check PPO2 threshold and set the appropriate class
            if (parseFloat(txppo2) > 1.59 || parseFloat(txppo2) < 0.16) {
                console.log("TX High PPO2 - Danger");
                txlabel.classList.remove("text-info", "text-warning", "right-label-normal", "right-label-warning"); // Remove other classes
                txlabel.classList.add("text-danger", "right-label-danger"); // Add "text-danger" class
            } else if (parseFloat(txppo2) > 1.41 || parseFloat(txppo2) < 0.21) {
                console.log("TX Medium PPO2 - Warning");
                txlabel.classList.remove("text-info", "text-danger", "right-label-normal", "right-label-danger"); // Remove other classes
                txlabel.classList.add("text-warning", "right-label-warning"); // Add "text-warning" class
            } else {
                console.log("TX Low PPO2 - Info");
                txlabel.classList.remove("text-warning", "text-danger", "right-label-warning", "right-label-danger"); // Remove other classes
                txlabel.classList.add("text-info", "right-label-normal"); // Add "text-info" class
            }

        

            console.log("TX slider value=",txsliderValue);
            console.log("TX bestMix=", txbestMix);
            if(txsliderValue == txbestMix) {
                txlabelBestNitrox.classList.remove("text-info");
                txlabelBestNitrox.classList.add("text-success");
            } else {
                txlabelBestNitrox.classList.add("text-info");
                txlabelBestNitrox.classList.remove("text-success");
            }

            if(txsliderValue < 16) {
                txhypoxic.style.display = "flex";
            } else {
                txhypoxic.style.display = "none";
            }

            updateGasDensity();
            updateGasMix(txlabelBestNitrox.textContent, txlabelBestHe.textContent);
            $('#ndlResult').text("-");
            //update NDL
            //const gasMix = {O2: txlabelBestNitrox.textContent / 100, N2: (100 - txlabelBestNitrox.textContent - txlabelBestHe.textContent)/100, He: txlabelBestHe.textContent/100};
            //const ndl = calculateNDL(depth, gasMix);
            //labelNDL = document.getElementById('labelNDL');
            //labelNDL.textContent = ndl;
            updateLabelHorizontalOffset(-40);
            // JavaScript code to toggle visibility of images
            document.getElementById("tank_double").removeAttribute("hidden"); // Removes 'hidden' attribute from the first image
            document.getElementById("tank_single").setAttribute("hidden", "true"); // Adds 'hidden' attribute to the second image
            //update Gas price label
            document.getElementById("tankConf").innerText = "Gas Price (Doubles)";
            updateGasPrices();

            //enable button
            document.getElementById("txbuttonBestNitrox").classList.add("btn-info");
            document.getElementById("txbuttonBestNitrox").classList.remove("btn-secondary");

            // if the slider is changed, we enable the Calculate NDL button
            document.getElementById("calculateNDLButton").classList.add("btn-info");
            document.getElementById("calculateNDLButton").classList.remove('btn-secondary');
            document.getElementById('NDLContainer').style.display = "none";
        });

        // Add an event listener to the button
        document.getElementById("txbuttonBestNitrox").addEventListener("click", function () {
            // Reset the slider to its start value
            O2Narcotic.checked = true;
            txslider.noUiSlider.set(Math.floor((1.4 / (depth / 33 + 1) * 100)));
            txsliderHe.noUiSlider.set(Number(((1 - ((80 / 33) +1) / (parseFloat(depth) / 33 + 1)) * 100).toFixed(0)));
            updateGasDensity();
            updateGasMix(txlabelBestNitrox.textContent, txlabelBestHe.textContent);
            $('#ndlResult').text("-");

            //update NDL
            //const gasMix = {O2: txlabelBestNitrox.textContent / 100, N2: (100 - txlabelBestNitrox.textContent - txlabelBestHe.textContent)/100, He: txlabelBestHe.textContent/100};
            //const ndl = calculateNDL(depth, gasMix);
            //labelNDL = document.getElementById('labelNDL');
            //labelNDL.textContent = ndl;

            updateLabelHorizontalOffset(-40);
            // JavaScript code to toggle visibility of images
            document.getElementById("tank_double").removeAttribute("hidden"); // Removes 'hidden' attribute from the first image
            document.getElementById("tank_single").setAttribute("hidden", "true"); // Adds 'hidden' attribute to the second image
            //update Gas price label
            document.getElementById("tankConf").innerText = "Gas Price (Doubles)";
            updateGasPrices();

            //disable button (already showing best Trimix on screen)
            document.getElementById("txbuttonBestNitrox").classList.remove("btn-info");
            document.getElementById("txbuttonBestNitrox").classList.add("btn-secondary");
            
        });

        // Add an event listener for the 'change' event
        O2Narcotic.addEventListener("change", function () {
            // Check if the checkbox is checked or not
            console.log("Checkbox state changed. Checked:", O2Narcotic.checked);

            var tempLabelMax = document.getElementById("ENDLabelMax");
            
            if(O2Narcotic.checked) {
                tempLabelMax.textContent = "END at max depth (depth ft)";
            } else {
                tempLabelMax.textContent = "EAD at max depth (depth ft)";
            }

            // Trigger the noUiSlider's update event
            txsliderHe.noUiSlider.set(txsliderHe.noUiSlider.get()); // Force an update with the current value
        });


      

    </script>

    <script>
        // Set up the CSRF token for AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Attach click event to the "Calculate NDL" button
        $('#calculateNDLButton').on('click', function () {
            // Get values from the input fields
            //const depth = depth
            const oxygen = labelMixO2.textContent.slice(0, -1); // Removes the last character ('%')
            const nitrogen = labelMixN2.textContent.slice(0, -1); // Removes the last character ('%')
            const helium = labelMixHe.textContent.slice(0, -1); // Removes the last character ('%')

            // Create the gas mix object
            const gasMix = {
                O2: oxygen / 100, // Convert percentage to fraction
                N2: nitrogen / 100, // Convert percentage to fraction
                He: helium / 100 // Convert percentage to fraction
            };

            // Make the AJAX POST request
            $.ajax({
                url: '{{ route('Calculate-ndl') }}', // The route to the server-side function
                method: 'POST',
                data: { depth: depth, gasMix: gasMix },
                success: function (response) {
                    // Update the result in the HTML
                    
                    document.getElementById("NDLDepthLabel").textContent = Number(depth).toFixed(0) + " ft";
                    // Show NDL container and disable button
                    document.getElementById("NDLContainer").style.display="block";
                    document.getElementById("calculateNDLButton").classList.remove("btn-info");
                    document.getElementById("calculateNDLButton").classList.add('btn-secondary');
            
                    $('#ndlResult').text(response.ndl);
                },
                error: function (xhr, status, error) {
                    console.error('Error:', error);
                }
            });

            
        });

        function calculateNDL(depth, oxygen, nitrogen, helium) {
        // Convert percentages to fractions
        const gasMix = {
            O2: oxygen / 100, // Convert percentage to fraction
            N2: nitrogen / 100, // Convert percentage to fraction
            He: helium / 100 // Convert percentage to fraction
        };

        // Make the AJAX POST request
        $.ajax({
            url: '{{ route('Calculate-ndl') }}', // The route to the server-side function
            method: 'POST',
            data: { depth: depth, gasMix: gasMix },
            success: function (response) {
                // Update the result in the HTML
                $('#ndlResult').text(response.ndl);
            },
            error: function (xhr, status, error) {
                console.error('Error:', error);
            }
        });
}

    </script>
    
    <script>
        var tempK = 37 + 273.15;    //loop temp in Kelvin
        // Get the canvas element
        const ctxCCR = document.getElementById('stackedBarChartCCR').getContext('2d');

        // Create the chart with a custom plugin for labels
        const stackedBarChartCCR = new Chart(ctxCCR, {
            type: 'bar', // Bar chart type
            data: {
                labels: ['', 'Pure O2'], // X-axis labels
                datasets: [
                    {
                        label: 'Oxygen',
                        data: [18], // Data points for this dataset
                        backgroundColor: 'rgba(255, 99, 132, 0.6)', // Bar color
                        borderRadius: 0, // Rounded corners
                        barPercentage: 1 // Adjust bar width (smaller bars)
                    },
                    {
                        label: 'Helium',
                        data: [45], // Data points for this dataset
                        backgroundColor: 'rgba(54, 162, 235, 0.6)', // Bar color
                        borderRadius: 0, // Rounded corners
                        barPercentage: 1 // Adjust bar width (smaller bars)
                    },
                    {
                        label: 'Nitrogen',
                        data: [37], // Data points for this dataset
                        backgroundColor: 'rgba(75, 192, 192, 0.6)', // Bar color
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
                                    ctx.fillText(data + '%', bar.x + 12, bar.y + 10); // Position label slightly above the bar
                                    
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

    </script>
    
    <script>
    // Script for CC diluent calculations

        function updateGasPricesCCR() {
            if (txBestHeCCR.textContent == 0) {
                document.getElementById("diluentPrice").textContent = "$9.00";
            } else {
                document.getElementById("diluentPrice").textContent = "$37.50";
            }

            //check if the gas is blendable
            o2 = parseFloat(document.getElementById("txBestO2CCR").textContent)/100;
            he = parseFloat(document.getElementById("txBestHeCCR").textContent)/100;
            if (blendGas(o2, he, 3000) == 0) {
                document.getElementById("diluentPrice").textContent = "-";
                document.getElementById("unblendable_sign_CCR").removeAttribute("hidden"); // Removes 'hidden' attribute from the first image
                return;
                
            }
            else
                document.getElementById("unblendable_sign_CCR").setAttribute("hidden", "true"); // Adds 'hidden' attribute to the second image

            document.getElementById("closeMixRowCCR").setAttribute("hidden", "true");
            // Define the array of standard gas mixes
            const gasArray = [
                { mix: "21/35", O2: 0.21, He: 0.35 },
                { mix: "18/45", O2: 0.18, He: 0.45 },
                { mix: "10/55", O2: 0.10, He: 0.50 },
                { mix: "Air", O2: 0.21, He: 0 },
            ];

            // Iterate over each gas mix to find the closest match
            gasArray.forEach(element => {
                if ((o2 - element.O2) < 0.05 && (o2 - element.O2) >= -0.03) { // Check within 4% difference
                    //console.log("he=" + he + " element.He=" + element.He + " Diff=" + Math.abs(he - element.He));
                    if( Math.abs(he - element.He) <= 0.1) { 
                        //console.log("Unhidding closeMixLabel");
                        document.getElementById("closeMixCCR").textContent = element.mix; // Use element.mix, not this->mix
                        document.getElementById("closeMixRowCCR").removeAttribute("hidden"); // Show the row
                    }
                }
            });
        }

        function calculateLoopGasDensity(depth, setpoint, diluentO2, diluentHe, waterVapor) {
            // Constants
            const R = 0.0821; // Gas constant in L·atm·K^−1·mol^−1
            //const T = 293;//310.15; // Body temperature in Kelvin (37°C)
            const M_H2O = 18.015; // Molar mass of water vapor (g/mol)
            const M_O2 = 32.00; // Molar mass of oxygen (g/mol)
            const M_N2 = 28.01; // Molar mass of nitrogen (g/mol)
            const M_He = 4.002; // Molar mass of helium (g/mol)
            T = tempK;
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
        }

        function updateGasDensityCCR() {
            waterVapor = document.getElementById("waterVapor").checked;
            const density = calculateLoopGasDensity(depth, parseFloat(labelSetPoint.textContent), parseFloat(txBestO2CCR.textContent)/100, parseFloat(txBestHeCCR.textContent)/100, waterVapor);
            var gasDensityLabel = document.getElementById("gasDensityCCR");
            gasDensityLabel.textContent = density.toFixed(2);

            if(density > 5 && density <= 5.6) {
                gasDensityLabel.classList.remove("text-info", "text-danger", "right-label-normal", "right-label-danger"); // Remove other classes
                gasDensityLabel.classList.add("text-warning", "right-label-warning"); // Add "text-warning" class
            } else if (density > 5.6) {
                gasDensityLabel.classList.remove("text-info", "text-warning", "right-label-normal", "right-label-warning"); // Remove other classes
                gasDensityLabel.classList.add("text-danger", "right-label-danger"); // Add "text-danger" class
            } else {
                gasDensityLabel.classList.remove("text-warning", "text-danger", "right-label-warning", "right-label-danger"); // Remove other classes
                gasDensityLabel.classList.add("text-info", "right-label-normal"); // Add "text-info" class
            }
        }


        function updateGasMixCCR(oxygen, helium) {
            // Ensure the passed values are integers
            oxygen = parseInt(oxygen, 10);
            helium = parseInt(helium, 10);

            // Calculate Nitrogen
            const nitrogen = 100 - (oxygen + helium);

            // Validate that the sum doesn't exceed 100
            if (nitrogen < 0) {
                console.error("Error: The sum of Oxygen and Helium exceeds 100!");
                return;
            }

            // Update the chart data
            stackedBarChartCCR.data.datasets = [
                {
                    label: 'Oxygen',
                    data: [oxygen, 100],
                    backgroundColor: 'rgb(76, 175, 80, 1.0)'
                },
                {
                    label: 'Helium',
                    data: [helium, 0],
                    backgroundColor: 'rgb(26, 115, 232, 1.0)'
                },
                {
                    label: 'Nitrogen',
                    data: [nitrogen, 0],
                    backgroundColor: '#7b809a'
                }
            ];

            // Refresh the chart
            stackedBarChartCCR.update();
            

            var labelMixO2CCR = document.getElementById('labelMixO2CCR');
            var labelMixHeCCR = document.getElementById('labelMixHeCCR');
            var labelMixN2CCR = document.getElementById('labelMixN2CCR');

            labelMixO2CCR.textContent = oxygen + '%';
            labelMixHeCCR.textContent = helium + '%';
            labelMixN2CCR.textContent = nitrogen + '%';

            var labelContainerHeCCR = document.getElementById("label-container-mix-He-CCR");
            if(helium == 0) {
                labelContainerHeCCR.style.display = "none";
            } else {
                labelContainerHeCCR.style.display = "flex";
            }

            // show or hide Hypoix
            if(oxygen < 16) {
                txhypoxicCCR.style.display = "flex";
            } else {
                txhypoxicCCR.style.display = "none";
            }

            // Update PPO2 Diluent color
            if (parseFloat(txlabelPPO2CCR.textContent) < 0.18 || parseFloat(txlabelPPO2CCR.textContent) > 1.10) {
                console.log("High PPO2 - Danger");
                txlabelPPO2CCR.classList.remove("text-info", "text-warning", "right-label-normal", "right-label-warning"); // Remove other classes
                txlabelPPO2CCR.classList.add("text-danger", "right-label-danger"); // Add "text-danger" class
                
            } else if (parseFloat(txlabelPPO2CCR.textContent) < 0.5 ||  parseFloat(txlabelPPO2CCR.textContent) > 1) {
                console.log("TX Medium PPO2 - Warning");
                txlabelPPO2CCR.classList.remove("text-info", "text-danger", "right-label-normal", "right-label-danger"); // Remove other classes
                txlabelPPO2CCR.classList.add("text-warning", "right-label-warning"); // Add "text-warning" class
                
            } else {
                console.log("TX Low PPO2 - Info");
                txlabelPPO2CCR.classList.remove("text-warning", "text-danger", "right-label-warning", "right-label-danger"); // Remove other classes
                txlabelPPO2CCR.classList.add("text-info", "right-label-normal"); // Add "text-info" class
            }

            
                
        };

        //var ambientPressure = depth / 33 + 1;
        
        // slider for Diluent PPO2
        noUiSlider.create(txsliderPPO2CCR, {
            start: Math.min(Math.ceil(0.9 / ambientPressure * 100), 21),    // Set initial value to 0.9 PPO2 or 21 max
            connect: [true, false],
            range: {
                'min': Math.ceil(0.16 / ambientPressure * 100),
                'max': Math.min(Math.floor(1.2 / ambientPressure * 100),21)
            },
            step: 1,
        });



        // Hide the tick mark labels
        var txtickLabels = txsliderPPO2CCR.querySelectorAll('.noUi-value-sub');
        txtickLabels.forEach(function (txlabel) {
            txlabel.style.display = 'none';
        });

        

        // slider for SetPoint
        noUiSlider.create(sliderSetPoint, {
            start: Math.min(ambientPressure, 1.3),    // Set initial value to 0.9 PPO2 or 21 max
            connect: [true, false],
            range: {
                'min': parseFloat(txlabelPPO2CCR.textContent),
                'max': Math.min(ambientPressure, 1.5)
            },
            step: 0.05,
            

        });

        // Hide the tick mark labels
        var txtickLabels = sliderSetPoint.querySelectorAll('.noUi-value-sub');
        txtickLabels.forEach(function (txlabel) {
            txlabel.style.display = 'none';
        });
        
        function calculateENDCCR(depth, setpoint, diluentO2, diluentHe, isOxygenNarcotic) {
            // Step 1: Calculate ambient pressure in ATA (depth in feet)
            const ambientPressure = depth / 33 + 1;
            console.log("END Depth = " + Number(depth).toFixed(0) + " Ambient Pressure=" + Number(ambientPressure).toFixed(0) );
            console.log("SetPoint=" + Number(setpoint) + " dilO2=" + Number(diluentO2) + " dilHe=" + Number(diluentHe) + " O2Narc=" + Number(isOxygenNarcotic));

            // Step 2: Calculate the combined partial pressure of He and N2
            const ppHeN2 = ambientPressure - setpoint;
            console.log("ppHeN2=" + Number(ppHeN2));

            // Step 3: Calculate the loop oxygen fraction
            const loopO2 = setpoint / ambientPressure;
            console.log("loopeO2=" + Number(loopO2));

            // Step 4: Calculate the combined fraction of He and N2
            const remainingFraction = 1 - loopO2;
            console.log("remainingFrac=" + Number(remainingFraction));

            // Step 5: Calculate the diluent nitrogen fraction
            const diluentN2 = 1 - diluentO2 - diluentHe;
            console.log("diluentN2=" + Number(diluentN2));

            // Step 6: Proportionally divide He and N2 in the loop
            const loopHe = (diluentHe / (diluentHe + diluentN2)) * remainingFraction;
            const loopN2 = (diluentN2 / (diluentHe + diluentN2)) * remainingFraction;
            console.log("loopHe=" + Number(loopHe));
            console.log("loopeN2=" + Number(loopN2));

            // Step 7: Calculate the narcotic fraction based on whether oxygen is considered narcotic
            let narcoticFraction;
            if (isOxygenNarcotic) {
                narcoticFraction = loopO2 + loopN2;
            } else {
                narcoticFraction = loopN2;
            }

            // Step 8: Calculate the Equivalent Narcotic Depth (END)
            const END = (depth + 33) * narcoticFraction - 33;

            console.log("END=" + Number(END));

            // Step 9: Return the result
            return Math.max(END,0);
        }

        function calculateBestHeCCR(targetEND, depth, diluentO2, setpoint) {
            console.log("Target END=" + targetEND + " depth=" + depth + " diluentO2=" + diluentO2 + " setpoint=" + setpoint);
            // Step 1: Calculate ambient pressure at depth (in ATA)
            const ambientPressureDepth = depth / 33 + 1; //6.03

            // Step 2: Calculate ambient pressure at desired END (in ATA)
            const ambientPressureEND = targetEND / 33 + 1; // 3.42

            // Step (a): Calculate contribution to total pressure from diluent O2 fraction
            const ppDiluentO2 = diluentO2 * ambientPressureDepth; //0.90

            // Step (b): Calculate pure O2 PPO2 contribution (Setpoint - PPO2 from diluent O2 fraction)
            const ppPureO2 = setpoint - ppDiluentO2; // 0.4

            // Step (c): Calculate the required PPHe (ambientPressure - pressure at END)
            const ppRequiredHe = ambientPressureDepth - ambientPressureEND;  //2.61

            // Step (d): Subtract pure O2 PPO2 from ambient pressure to calculate total pressure from diluent
            const ppDiluent = ambientPressureDepth - ppPureO2; //5.63

            // Step (e): Calculate the fraction of helium in the diluent
            const fractionHe = ppRequiredHe / ppDiluent; //0.46

            // Step 6: Return helium fraction as an integer percentage (0–100)
            return Math.round(fractionHe * 100);
        }


        var bestHeCCR = calculateBestHeCCR(80, depth, Math.min(Math.ceil(0.9 / ambientPressure * 100), 21)/100, parseFloat(labelSetPoint.textContent));

        console.log("bestHe=" + bestHeCCR);

        noUiSlider.create(sliderHeCCR, {
            start: bestHeCCR,
            connect: [true, false],
            range: {
                'min': 0,
                'max': 95
            },
            step: 1,
            

        });

        // Hide the tick mark labels
        var txtickLabelsHe = sliderHeCCR.querySelectorAll('.noUi-value-sub');
        txtickLabelsHe.forEach(function (txlabelHe) {
            txlabelHe.style.display = 'none';
        });

        sliderHeCCR.noUiSlider.on('update', function (values, handle) {
            txBestHeCCR.textContent = Math.round(values[handle]);

            //txlabelBestNitrox.textContent

            var txmaxDepth = parseFloat(depth); // Ensure maxDepth is rendered as a number
            
            var O2FactorCCR = 0;

            if(O2NarcoticCCR.checked) {
                O2FactorCCR = 1;
            }

            var ENDMaxCCR = calculateENDCCR(txmaxDepth, parseFloat(labelSetPoint.textContent), parseFloat(txBestO2CCR.textContent)/100, parseFloat(txBestHeCCR.textContent)/100, O2FactorCCR);
            labelENDCCR.textContent = ENDMaxCCR.toFixed(0);




            if (ENDMaxCCR > 130) {
                labelENDCCR.classList.remove("text-info", "text-warning", "right-label-normal", "right-label-warning"); // Remove other classes
                labelENDCCR.classList.add("text-danger", "right-label-danger"); // Add "text-danger" class
            } else if (ENDMaxCCR > 100) {
                labelENDCCR.classList.remove("text-info", "text-danger", "right-label-normal", "right-label-danger"); // Remove other classes
                labelENDCCR.classList.add("text-warning", "right-label-warning"); // Add "text-warning" class
            } else {
                labelENDCCR.classList.remove("text-warning", "text-danger", "right-label-warning", "right-label-danger"); // Remove other classes
                labelENDCCR.classList.add("text-info", "right-label-normal"); // Add "text-info" class
            }

            

            if(txBestHeCCR.textContent == bestHeCCR) {
                txBestHeCCR.classList.remove("text-info");
                txBestHeCCR.classList.add("text-success");
            } else {
                txBestHeCCR.classList.add("text-info");
                txBestHeCCR.classList.remove("text-success");
            }

            updateGasMixCCR(txBestO2CCR.textContent, txBestHeCCR.textContent);
            updateGasDensityCCR();
            updateGasPricesCCR();

            // enable button
            document.getElementById("buttonBestDiluent").classList.add("btn-info");
            document.getElementById("buttonBestDiluent").classList.remove('btn-secondary');
            
        });

        

        sliderSetPoint.noUiSlider.on('update', function (values, handle) {
            labelSetPoint.textContent = values[handle];
            //txBestO2CCR.textContent = Math.round(values[handle]);

            //updateGasMixCCR(txBestO2CCR.textContent, txBestHeCCR.textContent);
            // Update SetPoint color
            if (parseFloat(labelSetPoint.textContent) < 0.5 || parseFloat(labelSetPoint.textContent) > 1.45) {
                console.log("High PPO2 - Danger");
                labelSetPoint.classList.remove("text-info", "text-warning", "right-label-normal", "right-label-warning"); // Remove other classes
                labelSetPoint.classList.add("text-danger", "right-label-danger"); // Add "text-danger" class
                
            } else if (parseFloat(labelSetPoint.textContent) < 0.7 ||  parseFloat(labelSetPoint.textContent) > 1.3) {
                console.log("TX Medium PPO2 - Warning");
                labelSetPoint.classList.remove("text-info", "text-danger", "right-label-normal", "right-label-danger"); // Remove other classes
                labelSetPoint.classList.add("text-warning", "right-label-warning"); // Add "text-warning" class
                
            } else {
                console.log("TX Low PPO2 - Info");
                labelSetPoint.classList.remove("text-warning", "text-danger", "right-label-warning", "right-label-danger"); // Remove other classes
                labelSetPoint.classList.add("text-info", "right-label-normal"); // Add "text-info" class
            }

            sliderHeCCR.noUiSlider.set(sliderHeCCR.noUiSlider.get());
            updateGasDensityCCR();

            
            
            
        })

        txsliderPPO2CCR.noUiSlider.on('update', function (values, handle) {
            txlabelPPO2CCR.textContent = (Math.round(values[handle]) * ambientPressure / 100).toFixed(2);
            txBestO2CCR.textContent = Math.round(values[handle]);

            // Update range for setPoint
            // Update MAX on He slider
            // Update MAX on He slider
            updateGasMixCCR(txBestO2CCR.textContent, txBestHeCCR.textContent);
            function roundToStep(value, step = 0.05) {
                return Math.ceil(value / step) * step;
            }

            sliderSetPoint.noUiSlider.updateOptions({
                range: {
                    'min': roundToStep(parseFloat(txlabelPPO2CCR.textContent), 0.05),
                    'max': roundToStep(Math.min(ambientPressure, 1.5), 0.05)
                }
            });

            // Update MAX on He slider
            sliderHeCCR.noUiSlider.updateOptions({
                range: {
                    'min': 0,    // Keep the minimum value as is
                    'max': 95 - Math.round(values[handle]) 
                }
            });

            if(txBestO2CCR.textContent == Math.min(Math.ceil(0.9 / ambientPressure * 100), 21)) {
                txBestO2CCR.classList.remove("text-info");
                txBestO2CCR.classList.add("text-success");
            } else {
                txBestO2CCR.classList.add("text-info");
                txBestO2CCR.classList.remove("text-success");
            }
            
            updateGasDensityCCR();
            updateGasPricesCCR();

            // enable button
            document.getElementById("buttonBestDiluent").classList.add("btn-info");
            document.getElementById("buttonBestDiluent").classList.remove('btn-secondary');
            
        })

        // Add an event listener for the 'change' event
        O2NarcoticCCR.addEventListener("change", function () {
            // Check if the checkbox is checked or not
            console.log("Checkbox state changed. Checked:", O2NarcoticCCR.checked);

            // Trigger the noUiSlider's update event
            sliderHeCCR.noUiSlider.set(sliderHeCCR.noUiSlider.get()); // Force an update with the current value
        });

        waterVapor = document.getElementById("waterVapor");
        waterVapor.addEventListener("change", function () {
            // Trigger the noUiSlider's update event
            updateGasDensityCCR();
        });

        

        labelTempCCR = document.getElementById("labelTempCCR");
        
        noUiSlider.create(sliderTempCCR, {
            start: 37,
            connect: [true, false],
            range: {
                'min': 0,
                'max': 42
            },
            step: 1,
            

        });

        // Hide the tick mark labels
        var txtickLabelsHe = sliderTempCCR.querySelectorAll('.noUi-value-sub');
        txtickLabelsHe.forEach(function (txlabelHe) {
            txlabelHe.style.display = 'none';
        });

        sliderTempCCR.noUiSlider.on('update', function (values, handle) {
            tempC = Math.round(values[handle]);
            tempF = (Math.round(values[handle]) * 9 /5 ) + 32;
            labelTempCCR.textContent =  tempF.toFixed(0) + "/" + tempC;
            tempK = Math.round(values[handle]) + 273.15;
            updateGasDensityCCR();
        });

        document.getElementById("buttonBestDiluent").addEventListener("click", function () {
            O2NarcoticCCR.checked = true;
            waterVapor.checked = true;
            sliderSetPoint.noUiSlider.set(sliderSetPoint.noUiSlider.options.start);
            // update ambient pressure
            ambientPressure = depth / 33 +1;
            bestHeCCR = calculateBestHeCCR(80, depth, Math.min(Math.ceil(0.9 / ambientPressure * 100), 21)/100, parseFloat(labelSetPoint.textContent));
            
            // Reset the slider to its start value
            
            sliderHeCCR.noUiSlider.set(bestHeCCR);
            txsliderPPO2CCR.noUiSlider.set(Math.min(Math.ceil(0.9 / ambientPressure * 100), 21));
            
            sliderTempCCR.noUiSlider.set(sliderTempCCR.noUiSlider.options.start);
            //updateGasDensity();
            //updateGasMix(txlabelBestNitrox.textContent, txlabelBestHe.textContent);
            
            // if button pressed, we disable button
            document.getElementById("buttonBestDiluent").classList.remove("btn-info");
            document.getElementById("buttonBestDiluent").classList.add('btn-secondary');
            
        });

        function updateCCRSliders() {
            ambientPressure = depth / 33 + 1;

            txsliderPPO2CCR.noUiSlider.updateOptions({
                range: {
                    'min': Math.ceil(0.16 / ambientPressure * 100),
                    'max': Math.min(Math.floor(1.2 / ambientPressure * 100),21)
                }
            });
            var bestO2CCRTemp = Math.min(Math.ceil(0.9 / ambientPressure * 100), 21);
            var bestHeCCRTemp = calculateBestHeCCR(80, depth, Math.min(Math.ceil(0.9 / ambientPressure * 100), 21)/100, parseFloat(labelSetPoint.textContent));
            sliderHeCCR.noUiSlider.set(bestHeCCRTemp);
            txsliderPPO2CCR.noUiSlider.set(bestO2CCRTemp);

            // disable button - best diluent already calculated
            document.getElementById("buttonBestDiluent").classList.remove("btn-info");
            document.getElementById("buttonBestDiluent").classList.add('btn-secondary');

        }

    </script>


 

    {{---Show modal----}}
    @if(session('msg'))
    <script>
        $(document).ready(function() {
            $('#modal-notification').modal('show'); // Show the modal
        });
    </script>
    @endif




   



    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const checkbox = document.getElementById("showGasDetails");
            const gasesCardBody = document.getElementById("gasesCardBody");

            // Initial state (optional): hide or show based on checkbox
            gasesCardBody.style.display = checkbox.checked ? "block" : "none";

            checkbox.addEventListener("change", function () {
            gasesCardBody.style.display = this.checked ? "block" : "none";
            });
        });
    </script>

     <script>
        document.addEventListener("DOMContentLoaded", function() {
            sidebarColor(document.getElementById("sidebarColorDiv")); // Execute the sidebarColor function once the HTML is loaded
        });
    </script>

    @endpush
</x-page-template>

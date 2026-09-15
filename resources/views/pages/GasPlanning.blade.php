<x-page-template bodyClass='dh-shell bg-gray-200' :SEO="$SEO">
    
    <x-shell.nav active="me" />
    
    
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
        <x-shell.header title="Best Gases" icon="science" />
        <!-- End Navbar -->
        <div class="container-fluid py-0">

            <div class="d-none" data-color="info" id="sidebarColorDiv"></div>


            {{--modal success rating--}}
            @if(session('msg'))
            <div class="modal fade" id="modal-notification" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title font-weight-normal">Notification</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center">
                            <span class="material-icons-round" style="font-size: 40px; color: var(--dh-good);" aria-hidden="true">task_alt</span>
                            <p class="mb-0 mt-2">{{ session('msg') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Card Gases --}}
            <div class="row">
                <div class="col-md-12">
                    <section class="dh-panel">
                        <h2 class="dh-panel-title">Best Gas Calculator</h2>
                        <div id="gasesCardBody">
                            <div class="row mb-3">
                                <div class="col-12">
                                    <label class="dh-gas-label" for="labelDepth">Set Max Depth</label>
                                    <div class="dh-gas-row">
                                        <div class="dh-gas-input-wrap">
                                            <div class="dh-gas-editable">
                                                <input type="text" inputmode="numeric" class="dh-gas-input" id="labelDepth" value="100">
                                                <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                            </div>
                                            <span class="dh-gas-unit">ft</span>
                                        </div>
                                        <div class="slider-styled" id="sliderDepth" data-dh-num-mirror="1"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    
                                    <div class="dh-channel-picker dh-gas-picker" id="nav-tabs">
                                        <button type="button" class="dh-channel-chip is-active" data-tag="OC">Open Circuit</button>
                                        <button type="button" class="dh-channel-chip" data-tag="CC">Close Circuit CCR</button>
                                    </div>
                            
                                </div>
                            </div>
                            <div class="row" id="CC" hidden>

                                <div class="col-md-6" id="cc-diluent-col">
                                    <div class="mt-n2">
                                        <input type="hidden" id="sliderPPO2CCR-value" name="txsliderPPO2CCR">

                                        <label class="dh-gas-label" for="txlabelPPO2CCR">Diluent PPO&#8322; at max depth</label>
                                        <div class="dh-gas-row">
                                            <div class="dh-gas-input-wrap">
                                                <div class="dh-gas-editable">
                                                    <input type="text" inputmode="decimal" class="dh-gas-input is-safe" id="txlabelPPO2CCR" value="0.9">
                                                    <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                </div>
                                                <span class="dh-gas-unit">atm</span>
                                            </div>
                                            <div class="slider-styled" id="txsliderPPO2CCR" data-dh-num-mirror="1"></div>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <input type="hidden" id="sliderSetPoint-value" name="sliderSetPoint-input">

                                        <label class="dh-gas-label" for="labelSetPoint">Set Point</label>
                                        <div class="dh-gas-row">
                                            <div class="dh-gas-input-wrap">
                                                <div class="dh-gas-editable">
                                                    <input type="text" inputmode="decimal" class="dh-gas-input is-safe" id="labelSetPoint" value="1.2">
                                                    <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                </div>
                                                <span class="dh-gas-unit">atm</span>
                                            </div>
                                            <div class="slider-styled" id="sliderSetPoint" data-dh-num-mirror="1"></div>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <input type="hidden" id="sliderHeCCR-value" name="sliderHeCCR">

                                        <div class="dh-gas-label-row">
                                            <label class="dh-gas-label mb-0" for="labelENDCCR">END at max depth</label>
                                            <label class="dh-gas-toggle" for="O2NarcoticCCR">
                                                <input name="O2narcoticCCR" type="checkbox" id="O2NarcoticCCR" checked value="1">
                                                <span class="dh-gas-toggle-track"><span class="dh-gas-toggle-thumb"></span></span>
                                                <span class="dh-gas-toggle-label">
                                                    Count O&#8322; as narcotic
                                                    <span class="dh-gas-info material-icons-round" aria-hidden="true" title="When on, oxygen counts toward narcotic effect in the END calculation above.">info</span>
                                                </span>
                                            </label>
                                        </div>
                                        <div class="dh-gas-row">
                                            <div class="dh-gas-input-wrap">
                                                <div class="dh-gas-editable">
                                                    <input type="text" inputmode="numeric" class="dh-gas-input is-safe" id="labelENDCCR" value="0">
                                                    <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                </div>
                                                <span class="dh-gas-unit">ft</span>
                                            </div>
                                            <div class="slider-styled" id="sliderHeCCR" data-dh-num-mirror="1"></div>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <div class="dh-gas-label-row">
                                            <label class="dh-gas-label mb-0" for="labelTempCCR">Loop temperature at depth</label>
                                            <label class="dh-gas-toggle" for="waterVapor">
                                                <input name="waterVapor" type="checkbox" id="waterVapor" checked value="1">
                                                <span class="dh-gas-toggle-track"><span class="dh-gas-toggle-thumb"></span></span>
                                                <span class="dh-gas-toggle-label">
                                                    Consider H&#8322;O vapor?
                                                </span>
                                            </label>
                                        </div>
                                        <div class="dh-gas-row">
                                            <div class="dh-gas-input-wrap">
                                                <div class="dh-gas-editable">
                                                    <input type="text" inputmode="numeric" class="dh-gas-input" id="labelTempCCR" value="37">
                                                    <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                </div>
                                                <span class="dh-gas-unit">&deg;C</span>
                                            </div>
                                            <div class="slider-styled" id="sliderTempCCR" data-dh-num-mirror="1"></div>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <div class="text-center mb-2">
                                            <div class="dh-gas-split-pill-row">
                                                <div class="dh-gas-split-pill">
                                                    <label class="dh-gas-result-pill is-o2" id="txBestO2CCR">32</label>
                                                    <label class="dh-gas-result-pill is-he" id="txBestHeCCR">45</label>
                                                </div>
                                                <div class="dh-gas-density-col">
                                                    <label class="dh-gas-result-pill is-compact is-density" id="gasDensityCCR">0.00</label>
                                                    <div class="dh-gas-density-caption" id="denisityCCR">Gas density</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-center align-items-center mb-2" id="txhypoxicCCR" style="display: flex; justify-content: center; align-items: center;">
                                            <label class="text-danger text-sm font-weight-bolder" >Hypoxic at surface</label>
                                        </div>
                                        <div class="text-center">
                                            <div class="dh-gas-btn-row">
                                                <button type="button" class="btn btn-secondary flex-fill" id="buttonBestDiluent">
                                                    Calculate Best Diluent
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-lg-6 col-sm-12 col-md-6" id="cc-tank-col">
                                    <div class="dh-gas-tank-layout">
                                        <div class="dh-gas-tank-graphic">
                                            <div class="dh-gas-tank-img-wrap" style="position: relative; width: 105px; height: 210px;">
                                                <!-- Overlaying image -->
                                                <img id="tankCCR" src="{{ asset("assets") }}/img/ccr.png" alt="Overlay Image"
                                                    style="position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 150%; height: 75%; z-index: 10;">

                                                <img id="unblendable_sign_CCR" src="{{ asset("assets") }}/img/unblendable_sign.png" hidden alt="Overlay Image"
                                                    style="position: absolute; top: 70%; left: 50%; transform: translate(-50%, -50%); z-index: 10;">

                                                <!-- Fixed-size chart canvas. Wrapper height matches the canvas's
                                                     own height exactly (Pablo, 2026-09-18: same fix as the Deco
                                                     Planner's tanks) - without maintainAspectRatio:false, Chart.js
                                                     sizes the canvas to its PARENT's box, so a taller parent (this
                                                     used to be 210px) silently overrode whatever height the canvas
                                                     itself declared. -->
                                                <div style="width: 210px; height: 124px; position: absolute; bottom: 0; left: 50%; transform: translateX(-50%);">
                                                    <canvas id="stackedBarChartCCR"
                                                            style="width: 90%; height: 124px; position: absolute; bottom: 0; left: 0; transform: none; z-index: 1;"></canvas>
                                                </div>
                                            </div>

                                            <div class="text-center mt-2">
                                                <div class="dh-gas-split-pill">
                                                    <label class="dh-gas-result-pill is-compact is-mix-o2" id="labelMixO2CCR">21%</label>
                                                    <label class="dh-gas-result-pill is-compact is-mix-he" id="labelMixHeCCR" hidden>35%</label>
                                                    <label class="dh-gas-result-pill is-compact is-mix-n2" id="labelMixN2CCR">47%</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="dh-gas-tank-side">
                                            <details class="dh-wx-more">
                                                <summary><span class="material-icons-round" aria-hidden="true">attach_money</span>Gas prices</summary>
                                                <div class="dh-wx-more-body">
                                                    <table class="table mb-0">
                                                        <tr>
                                                            <td class="text-secondary text-xs opacity-10 text-left" style="border: none;">Diluent tank</td>
                                                            <td id="diluentPrice" class="text-secondary text-xs opacity-10 text-right" style="border: none; text-align: end;">$35.50</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-secondary text-xs opacity-10 text-left" style="border: none;">O2 tank</td>
                                                            <td id="O2Price" class="text-secondary text-xs opacity-10 text-right" style="border: none; text-align: end;">$10.00</td>
                                                        </tr>
                                                    </table>
                                                    <div class="text-center dh-gas-closemix" id="closeMixRowCCR" hidden>
                                                        <label class="dh-gas-result-pill is-compact is-closemix" id="closeMixCCR">-</label>
                                                    </div>
                                                </div>
                                            </details>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row" id="OC">
                                <div class="col-12 mb-3">
                                    <div class="dh-channel-picker dh-gas-picker" id="oc-fuel-picker">
                                        <button type="button" class="dh-channel-chip is-active" data-fuel="nitrox">Nitrox</button>
                                        <button type="button" class="dh-channel-chip" data-fuel="trimix">Trimix</button>
                                    </div>
                                </div>
                                    <div class="col-md-6" id="oc-nitrox-col">

                                        <div class="mt-n2">
                                            <input type="hidden" id="sliderPPO2-value" name="sliderPPO2">

                                            <label class="dh-gas-label" for="labelPPO2">PPO&#8322; at max depth</label>
                                            <div class="dh-gas-row">
                                                <div class="dh-gas-input-wrap">
                                                    <div class="dh-gas-editable">
                                                        <input type="text" inputmode="decimal" class="dh-gas-input is-safe" id="labelPPO2" value="0.90">
                                                        <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                    </div>
                                                    <span class="dh-gas-unit">atm</span>
                                                </div>
                                                <div class="slider-styled" id="sliderPPO2" data-dh-num-mirror="1"></div>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <table class="table align-items-center mb-0" style="border: top;">
                                                <tr>
                                                    <td class="text-center" style="border: none;">
                                                        <label class="dh-gas-result-pill is-o2" id="bestNitrox">32%</label>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-center pt-3" style="border: none;"> <!-- Added text-center here -->
                                                        <div class="dh-gas-btn-row" id="oc-nitrox-btn-row">
                                                            <button type="button" class="btn btn-secondary flex-fill" id="buttonBestNitrox">
                                                                Calculate Best Nitrox
                                                            </button>
                                                            <button type="button" class="btn btn-info btn-sm flex-fill" id="calculateNDLButton">
                                                                Calculate NDL
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>

                                    </div>


                                
                                        <div class="col-md-6" id="oc-trimix-col" hidden>

                                            <div class="mt-n2">
                                                <input type="hidden" id="sliderPPO2-value" name="txsliderPPO2">

                                                <label class="dh-gas-label" for="txlabelPPO2">PPO&#8322; at max depth</label>
                                                <div class="dh-gas-row">
                                                    <div class="dh-gas-input-wrap">
                                                        <div class="dh-gas-editable">
                                                            <input type="text" inputmode="decimal" class="dh-gas-input is-safe" id="txlabelPPO2" value="0.90">
                                                            <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                        </div>
                                                        <span class="dh-gas-unit">atm</span>
                                                    </div>
                                                    <div class="slider-styled" id="txsliderPPO2" data-dh-num-mirror="1"></div>
                                                </div>
                                            </div>
                                            <div class="mt-3">
                                                <input type="hidden" id="sliderPPO2-value" name="txsliderPPHe">

                                                <div class="dh-gas-label-row">
                                                    <label class="dh-gas-label mb-0" for="txlabelEND">END at max depth</label>
                                                    <label class="dh-gas-toggle" for="O2Narcotic">
                                                        <input name="O2narcotic" type="checkbox" id="O2Narcotic" checked value="1">
                                                        <span class="dh-gas-toggle-track"><span class="dh-gas-toggle-thumb"></span></span>
                                                        <span class="dh-gas-toggle-label">
                                                            Count O&#8322; as narcotic
                                                            <span class="dh-gas-info material-icons-round" aria-hidden="true" title="When on, oxygen counts toward narcotic effect in the END calculation - the more conservative assumption most agencies teach.">info</span>
                                                        </span>
                                                    </label>
                                                </div>
                                                <div class="dh-gas-row">
                                                    <div class="dh-gas-input-wrap">
                                                        <div class="dh-gas-editable">
                                                            <input type="text" inputmode="numeric" class="dh-gas-input is-safe" id="txlabelEND" value="0">
                                                            <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                        </div>
                                                        <span class="dh-gas-unit">ft</span>
                                                    </div>
                                                    <div class="slider-styled" id="txsliderHe" data-dh-num-mirror="1"></div>
                                                </div>
                                            </div>
                                            <div class="mt-3">
                                                <div class="text-center align-items-center mb-2" id="txhypoxic" style="display: flex; justify-content: center; align-items: center;">
                                                    <label class="text-danger text-sm font-weight-bolder" >Hypoxic at surface</label>
                                                </div>
                                                <div class="text-center mb-3">
                                                    <div class="dh-gas-split-pill-row">
                                                        <div class="dh-gas-split-pill">
                                                            <label class="dh-gas-result-pill is-o2" id="txbestNitrox">32</label>
                                                            <label class="dh-gas-result-pill is-he" id="txbestHe">32</label>
                                                        </div>
                                                        <div class="dh-gas-density-col">
                                                            <label class="dh-gas-result-pill is-compact is-density" id="gasDensity">0.00</label>
                                                            <div class="dh-gas-density-caption" id="denisity">Gas density</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="text-center">
                                                    <div class="dh-gas-btn-row" id="oc-trimix-btn-row">
                                                        <button type="button" class="btn btn-info flex-fill" id="txbuttonBestNitrox">
                                                            Calculate Best Trimix
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>

                                    <div class="col-12 col-lg-6 col-sm-12 col-md-6" id="oc-tank-col">
                                        <div class="dh-gas-tank-layout">
                                            <div class="dh-gas-tank-graphic">
                                                <div class="dh-gas-tank-img-wrap" style="position: relative; width: 105px; height: 210px;">
                                                    <!-- Overlaying image -->
                                                    <img id="tank_single" src="{{ asset("assets") }}/img/tank_single.png"  hidden alt="Overlay Image"
                                                        style="position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 150%; height: 75%; z-index: 10;">

                                                    <img id="tank_double" src="{{ asset("assets") }}/img/tank_double.png"   alt="Overlay Image"
                                                        style="position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 150%; height: 75%; z-index: 10;">

                                                    <img id="unblendable_sign" src="{{ asset("assets") }}/img/unblendable_sign.png" hidden alt="Overlay Image"
                                                        style="position: absolute; top: 70%; left: 50%; transform: translate(-50%, -50%); z-index: 10;">

                                                    <!-- Fixed-size chart canvas -->
                                                    <div style="width: 210px; height: 210px; position: absolute; bottom: 0; left: 50%; transform: translateX(-50%);">
                                                        <canvas id="stackedBarChart"
                                                                style="width: 100%; height: 141px; position: absolute; bottom: 0; left: 0; transform: none; z-index: 1;"></canvas>
                                                    </div>
                                                </div>

                                                <div class="text-center mt-2">
                                                    <div class="dh-gas-split-pill">
                                                        <label class="dh-gas-result-pill is-compact is-mix-o2" id="labelMixO2">32%</label>
                                                        <label class="dh-gas-result-pill is-compact is-mix-he" id="labelMixHe" hidden>0%</label>
                                                        <label class="dh-gas-result-pill is-compact is-mix-n2" id="labelMixN2">68%</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="dh-gas-tank-side">
                                                <details class="dh-wx-more">
                                                    <summary><span class="material-icons-round" aria-hidden="true">attach_money</span><span id="tankConf">Gas prices</span></summary>
                                                    <div class="dh-wx-more-body">
                                                        <table class="table mb-0">
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
                                                        <div class="text-center dh-gas-closemix" id="closeMixRow" hidden>
                                                            <label class="dh-gas-result-pill is-compact is-closemix" id="closeMix">-</label>
                                                        </div>
                                                    </div>
                                                </details>

                                                <div class="dh-wx-more dh-gas-ndl-card mt-2" id="NDLContainer" style="display:none;">
                                                    <div class="dh-gas-ndl-body">
                                                        <div class="dh-gas-ndl-result">
                                                            NDL at <label class="dh-gas-result-pill is-compact" id="NDLDepthLabel">xxx ft</label> is <label class="dh-gas-result-pill is-compact is-ndl" id="ndlResult">-</label>
                                                        </div>
                                                        <div class="dh-gas-ndl-sub">24 hr min surface interval</div>
                                                        <div class="text-center mt-1">
                                                            <label class="text-center text-danger text-xs mb-0">Always use a dive computer</label>
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
                    </section>
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
            labelDepth.value = Number(depth).toFixed(0);

            // force calculation of best Nitrox - both tabs recalculate so
            // whichever one the diver switches to next is already correct
            // for the new depth, but only the currently VISIBLE one should
            // get the last word on the tank graphic (Pablo, 2026-09-15:
            // "the graph... is the real result" for whichever is selected)
            // - re-sync it last, once dhSelectGasFuel exists (it doesn't
            // yet on this handler's own very first, page-load firing).
            document.getElementById("buttonBestNitrox").click();
            document.getElementById("txbuttonBestNitrox").click();
            if (typeof dhSelectGasFuel === 'function') {
                var trimixCol = document.getElementById('oc-trimix-col');
                dhSelectGasFuel(trimixCol && !trimixCol.hidden ? 'trimix' : 'nitrox');
            }

            //force calculation on ccr
            // Guarded the same way dhSelectGasFuel already is just below -
            // noUiSlider fires 'update' immediately/synchronously during
            // .create() at page load, before updateCCRSliders' own <script>
            // block (further down the page) has run yet. Calling it
            // unguarded threw ReferenceError on that very first firing,
            // which aborted the REST of this script block before it ever
            // reached the labelDepth 'change' listener below - so typing a
            // depth silently did nothing at all (Pablo, 2026-09-19:
            // "manually typing the depth is not updating the calculations
            // or sliders").
            if (typeof updateCCRSliders === 'function') updateCCRSliders();

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

        // Typing a depth moves the slider - the slider's own 'update' handler
        // above then re-runs everything else (Nitrox, Trimix, CCR, gas mix).
        labelDepth.addEventListener('change', function () {
            var typed = parseFloat(labelDepth.value);
            if (isNaN(typed)) { labelDepth.value = Number(depth).toFixed(0); return; }
            sliderDepth.noUiSlider.set(typed);
        });
        // 'change' only fires on blur - pressing Enter while still focused
        // did nothing (Pablo, 2026-09-19: "if I type the number and then
        // hit enter, I'm expecting the update to happen"). Blurring fires
        // the real 'change' event above, reusing that logic instead of
        // duplicating it.
        labelDepth.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); labelDepth.blur(); }
        });
    </script>

    <script>
        // Scipt to manage navigation on gases
        document.querySelectorAll('#nav-tabs .dh-channel-chip').forEach(link => {
            link.addEventListener('click', function(event) {
                // Add any custom logic for handling clicks here
                const tag = this.getAttribute('data-tag'); // Get the data-tag value
                document.querySelectorAll('#nav-tabs .dh-channel-chip').forEach(chip => {
                    chip.classList.toggle('is-active', chip.getAttribute('data-tag') === tag);
                });
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

    let labelHorizontalOffset = -28; // Initial offset value - scaled with the smaller tank (2026-09-15)

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
                    backgroundColor: '#2e7d4f', // matches the O2 split pill (--dh-good)
                    borderRadius: 0, // Rounded corners
                    barPercentage: 1 // Adjust bar width (smaller bars)
                },
                {
                    label: 'Helium',
                    data: [45], // Data points for this dataset
                    backgroundColor: '#0e7c9e', // matches the He split pill (--dh-sea)
                    borderRadius: 0, // Rounded corners
                    barPercentage: 1 // Adjust bar width (smaller bars)
                },
                {
                    label: 'Nitrogen',
                    data: [37], // Data points for this dataset
                    backgroundColor: '#5a6b78', // matches the N2 split pill (--dh-muted)
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
                    top: 14, // scaled with the smaller tank (2026-09-15)
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
                                ctx.font = '10px Roboto'; // scaled with the smaller tank (2026-09-15)
                                ctx.fillStyle = '#FFF'; // Label color
                                ctx.textAlign = 'center';
                                ctx.textBaseline = 'middle'; // Centers text vertically
                                ctx.fillText(data + '%', bar.x + labelHorizontalOffset, bar.y + 7); // Position label slightly above the bar

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

        // Update the chart data - same colors as the O2/He/N2 split pill
        // below the tank (Pablo, 2026-09-15: "match the graph colors...
        // to the ones you chose for the split pill").
        stackedBarChart.data.datasets = [
            {
                label: 'Oxygen',
                data: [oxygen],
                backgroundColor: '#2e7d4f' // --dh-good
            },
            {
                label: 'Helium',
                data: [helium],
                backgroundColor: '#0e7c9e' // --dh-sea
            },
            {
                label: 'Nitrogen',
                data: [nitrogen],
                backgroundColor: '#5a6b78' // --dh-muted
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

        // 2-way split for Nitrox (O2/N2), 3-way for Trimix (O2/He/N2) -
        // same split pill either way, the He third just hides itself
        // (Pablo, 2026-09-15: "2 split for Nitrox O2 and N2; 3 splits for
        // Trimix O2, He and N2").
        labelMixHe.hidden = helium == 0;

    }

    </script>
    {{-- Slider script Nitrox --}}
    <script>
        var slider = document.getElementById('sliderPPO2');
        var label = document.getElementById('labelPPO2');
        
        var sliderValueInput = document.getElementById('slider-value');
        var labelBestNitrox = document.getElementById('bestNitrox');
        


        // A function, not a value computed once - Pablo, 2026-09-15: "it
        // depends on the depth", and depth can change after this script
        // runs (Set Max Depth, or Calculate Best Nitrox on another tab).
        // Recomputed fresh everywhere "the best mix right now" is needed.
        function currentBestNitroxMix() {
            return Math.max(Math.round((1.4 / (depth / 33 + 1) * 100)), 21);
        }

        noUiSlider.create(slider, {
            start: currentBestNitroxMix(),
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

            var sliderValue = parseFloat(values[handle]); // Ensure sliderValue is numeric
            console.log("Slider Value:", sliderValue);

            var maxDepth = parseFloat(depth); // Ensure maxDepth is rendered as a number
            console.log("Max Depth:", maxDepth);

        

            var ppo2 = (((maxDepth / 33 + 1) * sliderValue) / 100).toFixed(2); // Calculate PPO2 and format
            console.log("PPO2:", ppo2);


            label.value = ppo2;


            // Check PPO2 threshold and set the appropriate class - white
            // background always (dh-gas-input), only the border/text color
            // moves through the safe/warn/danger ladder (Pablo, 2026-09-15).
            if (parseFloat(ppo2) > 1.59 || parseFloat(ppo2) < 0.16) {
                console.log("High PPO2 - Danger");
                label.classList.remove("is-safe", "is-warn");
                label.classList.add("is-danger");
            } else if (parseFloat(ppo2) > 1.41 || parseFloat(ppo2) < 0.21) {
                console.log("Medium PPO2 - Warning");
                label.classList.remove("is-safe", "is-danger");
                label.classList.add("is-warn");
            } else {
                console.log("Low PPO2 - Info");
                label.classList.remove("is-warn", "is-danger");
                label.classList.add("is-safe");
            }



            console.log("slider value=",sliderValue);
            // O2 Content result pill: green fill when this IS the ideal mix
            // for the current depth, theme blue otherwise (Pablo,
            // 2026-09-15) - dh-gas-result-pill is the always-present base.
            // The Calculate button disables itself the same moment - "if
            // we are seeing best nitrox... disable the Calculate button."
            var isIdealMix = sliderValue == currentBestNitroxMix();
            labelBestNitrox.classList.toggle("is-ideal", isIdealMix);
            var bestNitroxBtn = document.getElementById("buttonBestNitrox");
            // A real block on user clicks (pointer-events), not just the
            // native `disabled` attribute - that would also stop the
            // depth slider's own .click() call below from recalculating
            // this tab when someone changes Set Max Depth while already
            // sitting on the ideal mix.
            bestNitroxBtn.classList.toggle("is-solved", isIdealMix);
            bestNitroxBtn.classList.toggle("btn-secondary", isIdealMix);
            bestNitroxBtn.classList.toggle("btn-info", !isIdealMix);
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

            // Add an event listener to the button - the slider's own
            // 'update' handler above (fired synchronously by .set() below)
            // is what actually disables/colors the button and marks the
            // pill ideal, so this only needs to move the slider.
            document.getElementById("buttonBestNitrox").addEventListener("click", function () {
                slider.noUiSlider.set(currentBestNitroxMix());
                updateLabelHorizontalOffset(0);
                document.getElementById("tank_single").removeAttribute("hidden"); // Removes 'hidden' attribute from the first image
                document.getElementById("tank_double").setAttribute("hidden", "true"); // Adds 'hidden' attribute to the second image
                //update Gas price label
                document.getElementById("tankConf").innerText = "Gas Price (Single)";
                updateGasPrices();
            });

            // Typing a PPO2 moves the O2% slider that actually drives it -
            // the diver's mental model is "I'm setting PPO2" (Pablo,
            // 2026-09-15), even though the slider itself is O2 content.
            // Re-derive the O2% this PPO2 implies at the current depth and
            // hand it to the slider; its own 'update' handler above then
            // recomputes everything (including rounding this back to
            // whatever PPO2 that exact O2% actually produces).
            label.addEventListener('change', function () {
                var typedPpo2 = parseFloat(label.value);
                if (isNaN(typedPpo2)) { label.value = ((depth / 33 + 1) * slider.noUiSlider.get() / 100).toFixed(2); return; }
                var targetO2Percent = Math.round((typedPpo2 / (depth / 33 + 1)) * 100);
                slider.noUiSlider.set(targetO2Percent);
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

        // Functions, not values computed once - Pablo, 2026-09-15: "it
        // depends on the depth" - depth can change after this script runs
        // (Set Max Depth, or switching tabs). Recomputed fresh everywhere
        // "the best value right now" is needed, for both O2 and He
        // independently (they can each be at their own ideal or not).
        function currentTxBestMix() {
            return Math.round((1.4 / (depth / 33 + 1) * 100));
        }
        function currentBestHe() {
            return Number(((1 - ((80 / 33) + 1) / (parseFloat(depth) / 33 + 1)) * 100).toFixed(0));
        }

        // The split pill above "Calculate Best Trimix" only goes solid
        // green when BOTH halves are independently ideal (Pablo: "If both
        // are best, then all pill should be green") - the button disables
        // itself the same moment, since there's nothing left to solve.
        function updateTrimixButtonState() {
            var bothIdeal = txlabelBestNitrox.classList.contains('is-ideal') && txlabelBestHe.classList.contains('is-ideal');
            var btn = document.getElementById("txbuttonBestNitrox");
            btn.classList.toggle("is-solved", bothIdeal);
            btn.classList.toggle("btn-secondary", bothIdeal);
            btn.classList.toggle("btn-info", !bothIdeal);
        }



        function updateGasDensity() {
            // Constants for molecular weights (g/mol)
            const molecularWeights = {
                O2: 32,
                N2: 28,
                He: 4
            };

            var fractionO2 = txlabel.value / 100;
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

            // Read-only calculation -> colored background pill (Pablo,
            // 2026-09-15: "Gas Density is a calculation, so it should go
            // with colored background") - default is the safe blue baked
            // into .dh-gas-result-pill itself, only warn/danger override it.
            if (densityRounded > 6.2) {
                gasDensityLabel.classList.remove("is-warn");
                gasDensityLabel.classList.add("is-danger");
            } else if (densityRounded > 5.2) {
                gasDensityLabel.classList.remove("is-danger");
                gasDensityLabel.classList.add("is-warn");
            } else {
                gasDensityLabel.classList.remove("is-warn", "is-danger");
            }
        }

        noUiSlider.create(txsliderHe, {
            start: currentBestHe(),
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
            txlabelEND.value = ENDMax;

            if (ENDMax > 130) {
                txlabelEND.classList.remove("is-safe", "is-warn");
                txlabelEND.classList.add("is-danger");
            } else if (ENDMax > 100) {
                txlabelEND.classList.remove("is-safe", "is-danger");
                txlabelEND.classList.add("is-warn");
            } else {
                txlabelEND.classList.remove("is-warn", "is-danger");
                txlabelEND.classList.add("is-safe");
            }

            // He half of the split pill: green fill when this IS the
            // ideal He% for the current depth, theme blue otherwise -
            // same rule as the Nitrox O2 Content pill, independent of
            // whatever the O2 half above is doing.
            txlabelBestHe.classList.toggle("is-ideal", txlabelBestHe.textContent == currentBestHe());
            updateTrimixButtonState();

           updateGasDensity();
           updateGasMix(txlabelBestNitrox.textContent, txlabelBestHe.textContent);
           $('#ndlResult').text("-");
            //update NDL
            //const gasMix = {O2: txlabelBestNitrox.textContent / 100, N2: (100 - txlabelBestNitrox.textContent - txlabelBestHe.textContent)/100, He: txlabelBestHe.textContent/100};
            //const ndl = calculateNDL(depth, gasMix);
            //labelNDL = document.getElementById('labelNDL');
            //labelNDL.textContent = ndl;
            updateLabelHorizontalOffset(-28); // scaled with the smaller tank
            // JavaScript code to toggle visibility of images
            document.getElementById("tank_double").removeAttribute("hidden"); // Removes 'hidden' attribute from the first image
            document.getElementById("tank_single").setAttribute("hidden", "true"); // Adds 'hidden' attribute to the second image
            //update Gas price label
            document.getElementById("tankConf").innerText = "Gas Price (Doubles)";
            updateGasPrices();

            // if the slider is changed, we enable the Calculate NDL button
            document.getElementById("calculateNDLButton").classList.add("btn-info");
            document.getElementById("calculateNDLButton").classList.remove('btn-secondary');
            document.getElementById('NDLContainer').style.display = "none";

        });

        // Typing an END moves the He% slider that actually drives it -
        // invert the same equivPMax/ENDMax formula the update handler
        // above uses, solving for He% instead of END.
        txlabelEND.addEventListener('change', function () {
            var typedEnd = parseFloat(txlabelEND.value);
            if (isNaN(typedEnd)) { txlabelEND.value = Math.round(txsliderHe.noUiSlider.get()); return; }
            var o2Factor = O2Narcotic.checked ? 0 : Math.round(txlabelBestNitrox.textContent) / 100;
            var equivPMax = typedEnd / 33 + 1;
            var targetHePercent = Math.round(100 * (1 - o2Factor - equivPMax / (depth / 33 + 1)));
            txsliderHe.noUiSlider.set(targetHePercent);
        });

        noUiSlider.create(txslider, {
            start: currentTxBestMix(),
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

            

            txlabel.value = txppo2;

            // Update MAX on He slider
            txsliderHe.noUiSlider.updateOptions({
                range: {
                    'min': 0,    // Keep the minimum value as is
                    'max': 95-txsliderValue   // Update the maximum value to 120
                }
            });

            // Check PPO2 threshold - white background always, only the
            // border/text color moves through the safe/warn/danger ladder.
            if (parseFloat(txppo2) > 1.59 || parseFloat(txppo2) < 0.16) {
                console.log("TX High PPO2 - Danger");
                txlabel.classList.remove("is-safe", "is-warn");
                txlabel.classList.add("is-danger");
            } else if (parseFloat(txppo2) > 1.41 || parseFloat(txppo2) < 0.21) {
                console.log("TX Medium PPO2 - Warning");
                txlabel.classList.remove("is-safe", "is-danger");
                txlabel.classList.add("is-warn");
            } else {
                console.log("TX Low PPO2 - Info");
                txlabel.classList.remove("is-warn", "is-danger");
                txlabel.classList.add("is-safe");
            }

            console.log("TX slider value=",txsliderValue);
            // O2 half of the split pill: green fill when this IS the
            // ideal mix for the current depth, theme blue otherwise -
            // independent of whatever the He half is doing.
            txlabelBestNitrox.classList.toggle("is-ideal", txsliderValue == currentTxBestMix());
            updateTrimixButtonState();

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
            updateLabelHorizontalOffset(-28); // scaled with the smaller tank
            // JavaScript code to toggle visibility of images
            document.getElementById("tank_double").removeAttribute("hidden"); // Removes 'hidden' attribute from the first image
            document.getElementById("tank_single").setAttribute("hidden", "true"); // Adds 'hidden' attribute to the second image
            //update Gas price label
            document.getElementById("tankConf").innerText = "Gas Price (Doubles)";
            updateGasPrices();

            // if the slider is changed, we enable the Calculate NDL button
            document.getElementById("calculateNDLButton").classList.add("btn-info");
            document.getElementById("calculateNDLButton").classList.remove('btn-secondary');
            document.getElementById('NDLContainer').style.display = "none";
        });

        // Add an event listener to the button - both sliders' own 'update'
        // handlers above (fired synchronously by .set() below) are what
        // actually mark each half ideal and update the button via
        // updateTrimixButtonState(), so this only needs to move them.
        document.getElementById("txbuttonBestNitrox").addEventListener("click", function () {
            O2Narcotic.checked = true;
            txslider.noUiSlider.set(currentTxBestMix());
            txsliderHe.noUiSlider.set(currentBestHe());
            updateGasDensity();
            updateGasMix(txlabelBestNitrox.textContent, txlabelBestHe.textContent);
            $('#ndlResult').text("-");

            updateLabelHorizontalOffset(-28); // scaled with the smaller tank
            // JavaScript code to toggle visibility of images
            document.getElementById("tank_double").removeAttribute("hidden"); // Removes 'hidden' attribute from the first image
            document.getElementById("tank_single").setAttribute("hidden", "true"); // Adds 'hidden' attribute to the second image
            //update Gas price label
            document.getElementById("tankConf").innerText = "Gas Price (Doubles)";
            updateGasPrices();
        });

        // Typing a PPO2 moves the O2% slider that actually drives it -
        // same idea as the Nitrox PPO2 field.
        txlabel.addEventListener('change', function () {
            var typedPpo2 = parseFloat(txlabel.value);
            if (isNaN(typedPpo2)) { txlabel.value = ((depth / 33 + 1) * txslider.noUiSlider.get() / 100).toFixed(2); return; }
            var targetO2Percent = Math.round((typedPpo2 / (depth / 33 + 1)) * 100);
            txslider.noUiSlider.set(targetO2Percent);
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
        // Nitrox/Trimix pill (Pablo, 2026-09-15): "the graph... is the
        // real result. But the result shown is either nitrox or trimix...
        // having both trimix and nitrox calculator on screen make no
        // sense." Only one calculator column shows at a time; the tank
        // graphic + gas mix + price column (always visible) already
        // re-renders off whichever calculator last fired an 'update' -
        // switching pills just re-fires that calculator's own update with
        // its CURRENT value (not a reset to "best"), so the tank catches
        // up to match without disturbing either calculator's own setting.
        function dhSelectGasFuel(fuel) {
            document.getElementById('oc-nitrox-col').hidden = fuel !== 'nitrox';
            document.getElementById('oc-trimix-col').hidden = fuel !== 'trimix';
            document.querySelectorAll('#oc-fuel-picker .dh-channel-chip').forEach(function (chip) {
                chip.classList.toggle('is-active', chip.getAttribute('data-fuel') === fuel);
            });

            // "Calculate NDL" is one single button (Pablo, 2026-09-15:
            // "make it share the same line as Calculate Best Trimix or
            // Calculate best nitrox") - physically moved into whichever
            // calculator's button row is now visible, rather than hidden
            // along with whichever column it was sitting in a moment ago.
            var ndlBtn = document.getElementById('calculateNDLButton');
            var targetRow = document.getElementById(fuel === 'nitrox' ? 'oc-nitrox-btn-row' : 'oc-trimix-btn-row');
            if (ndlBtn && targetRow && ndlBtn.parentElement !== targetRow) {
                targetRow.appendChild(ndlBtn);
            }

            if (fuel === 'nitrox') {
                slider.noUiSlider.set(slider.noUiSlider.get());
            } else {
                txslider.noUiSlider.set(txslider.noUiSlider.get());
            }
        }

        document.querySelectorAll('#oc-fuel-picker .dh-channel-chip').forEach(function (chip) {
            chip.addEventListener('click', function () { dhSelectGasFuel(chip.getAttribute('data-fuel')); });
        });

        // Both calculators' sliders fire an initial 'update' as soon as
        // they're created, so by the time this runs the tank graphic is
        // already showing whichever one's script happened to run last
        // (Trimix) - sync it to Nitrox, the default active pill, instead.
        dhSelectGasFuel('nitrox');
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

                    // Color-coded by how much no-deco time is actually left
                    // (Pablo, 2026-09-15): blue above 20 min, orange 10-20,
                    // red under 10 - same fill-not-border convention as
                    // every other calculation result pill on this page.
                    var ndlMinutes = parseFloat(response.ndl);
                    var ndlResultEl = document.getElementById("ndlResult");
                    if (ndlMinutes < 10) {
                        ndlResultEl.classList.remove("is-warn");
                        ndlResultEl.classList.add("is-danger");
                    } else if (ndlMinutes <= 20) {
                        ndlResultEl.classList.remove("is-danger");
                        ndlResultEl.classList.add("is-warn");
                    } else {
                        ndlResultEl.classList.remove("is-warn", "is-danger");
                    }
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
                // Without this, Chart.js derives the canvas height from its
                // parent's width and a default aspect ratio instead of the
                // canvas's own CSS height - see the wrapper div's comment
                // above (Pablo, 2026-09-18).
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
                    // ccr.png's transparent window starts at y=77/300 of the
                    // source image - scaled to this 124px canvas that's
                    // ~7px down from the top (Pablo, 2026-09-18: measured
                    // the same way as the Deco Planner's tank masks).
                    padding: {
                        left: 14,
                        right: 14,
                        top: 6,
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
                                    ctx.font = '8px Roboto'; // scaled with the smaller tank (2026-09-16)
                                    ctx.fillStyle = '#FFF'; // Label color
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'middle'; // Centers text vertically
                                    ctx.fillText(data + '%', bar.x + 8, bar.y + 7); // Position label slightly above the bar

                                } else if (data == 100) {
                                    ctx.font = '8px Roboto'; // scaled with the smaller tank (2026-09-16)
                                    ctx.fillStyle = '#FFF'; // Label color
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'middle'; // Centers text vertically
                                    ctx.fillText(data + '%', bar.x -7 , bar.y + 49); // Position label slightly above the bar - scaled with the smaller tank
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
            const density = calculateLoopGasDensity(depth, parseFloat(labelSetPoint.value), parseFloat(txBestO2CCR.textContent)/100, parseFloat(txBestHeCCR.textContent)/100, waterVapor);
            var gasDensityLabel = document.getElementById("gasDensityCCR");
            gasDensityLabel.textContent = density.toFixed(2);

            // Colored-background pill, same safe/warn/danger fill as Open
            // Circuit's Gas Density (2026-09-16) - default is-safe blue is
            // already baked into .dh-gas-result-pill itself.
            if(density > 5 && density <= 5.6) {
                gasDensityLabel.classList.remove("is-danger");
                gasDensityLabel.classList.add("is-warn");
            } else if (density > 5.6) {
                gasDensityLabel.classList.remove("is-warn");
                gasDensityLabel.classList.add("is-danger");
            } else {
                gasDensityLabel.classList.remove("is-warn", "is-danger");
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

            // Update the chart data - same colors as the O2/He/N2 split
            // pill below the tank (2026-09-16, matches Open Circuit).
            stackedBarChartCCR.data.datasets = [
                {
                    label: 'Oxygen',
                    data: [oxygen, 100],
                    backgroundColor: '#2e7d4f' // --dh-good
                },
                {
                    label: 'Helium',
                    data: [helium, 0],
                    backgroundColor: '#0e7c9e' // --dh-sea
                },
                {
                    label: 'Nitrogen',
                    data: [nitrogen, 0],
                    backgroundColor: '#5a6b78' // --dh-muted
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

            // 2-way (no Helium) or 3-way split pill, same as Open Circuit's
            // Gas Mix legend.
            labelMixHeCCR.hidden = helium == 0;

            // show or hide Hypoix
            if(oxygen < 16) {
                txhypoxicCCR.style.display = "flex";
            } else {
                txhypoxicCCR.style.display = "none";
            }

            // Update PPO2 Diluent color - white background always, only
            // the border/text color moves through the safe/warn/danger
            // ladder (2026-09-16, same convention as Open Circuit).
            if (parseFloat(txlabelPPO2CCR.value) < 0.18 || parseFloat(txlabelPPO2CCR.value) > 1.10) {
                console.log("High PPO2 - Danger");
                txlabelPPO2CCR.classList.remove("is-safe", "is-warn");
                txlabelPPO2CCR.classList.add("is-danger");
            } else if (parseFloat(txlabelPPO2CCR.value) < 0.5 ||  parseFloat(txlabelPPO2CCR.value) > 1) {
                console.log("TX Medium PPO2 - Warning");
                txlabelPPO2CCR.classList.remove("is-safe", "is-danger");
                txlabelPPO2CCR.classList.add("is-warn");
            } else {
                console.log("TX Low PPO2 - Info");
                txlabelPPO2CCR.classList.remove("is-warn", "is-danger");
                txlabelPPO2CCR.classList.add("is-safe");
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
                'min': parseFloat(txlabelPPO2CCR.value),
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


        // A function, not a value computed once - same staleness bug fix
        // as Open Circuit's Trimix (2026-09-16): depth/setpoint can both
        // change after this script runs, so "the best He% right now" is
        // recomputed fresh everywhere it's needed instead of read from a
        // var frozen at page-load time.
        function currentBestHeCCR() {
            return calculateBestHeCCR(80, depth, Math.min(Math.ceil(0.9 / ambientPressure * 100), 21) / 100, parseFloat(labelSetPoint.value));
        }

        noUiSlider.create(sliderHeCCR, {
            start: currentBestHeCCR(),
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

            var ENDMaxCCR = calculateENDCCR(txmaxDepth, parseFloat(labelSetPoint.value), parseFloat(txBestO2CCR.textContent)/100, parseFloat(txBestHeCCR.textContent)/100, O2FactorCCR);
            labelENDCCR.value = ENDMaxCCR.toFixed(0);

            if (ENDMaxCCR > 130) {
                labelENDCCR.classList.remove("is-safe", "is-warn");
                labelENDCCR.classList.add("is-danger");
            } else if (ENDMaxCCR > 100) {
                labelENDCCR.classList.remove("is-safe", "is-danger");
                labelENDCCR.classList.add("is-warn");
            } else {
                labelENDCCR.classList.remove("is-warn", "is-danger");
                labelENDCCR.classList.add("is-safe");
            }

            // He half of the split pill: green when this IS the ideal He%
            // for the current depth/setpoint, theme blue otherwise - same
            // rule as Open Circuit's Trimix.
            txBestHeCCR.classList.toggle("is-ideal", txBestHeCCR.textContent == currentBestHeCCR());
            updateDiluentButtonState();

            updateGasMixCCR(txBestO2CCR.textContent, txBestHeCCR.textContent);
            updateGasDensityCCR();
            updateGasPricesCCR();
        });

        sliderSetPoint.noUiSlider.on('update', function (values, handle) {
            labelSetPoint.value = values[handle];

            // Update SetPoint color - white background always, only the
            // border/text color moves through the safe/warn/danger ladder.
            if (parseFloat(labelSetPoint.value) < 0.5 || parseFloat(labelSetPoint.value) > 1.45) {
                console.log("High PPO2 - Danger");
                labelSetPoint.classList.remove("is-safe", "is-warn");
                labelSetPoint.classList.add("is-danger");
            } else if (parseFloat(labelSetPoint.value) < 0.7 ||  parseFloat(labelSetPoint.value) > 1.3) {
                console.log("TX Medium PPO2 - Warning");
                labelSetPoint.classList.remove("is-safe", "is-danger");
                labelSetPoint.classList.add("is-warn");
            } else {
                console.log("TX Low PPO2 - Info");
                labelSetPoint.classList.remove("is-warn", "is-danger");
                labelSetPoint.classList.add("is-safe");
            }

            sliderHeCCR.noUiSlider.set(sliderHeCCR.noUiSlider.get());
            updateGasDensityCCR();
        })

        txsliderPPO2CCR.noUiSlider.on('update', function (values, handle) {
            txlabelPPO2CCR.value = (Math.round(values[handle]) * ambientPressure / 100).toFixed(2);
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
                    'min': roundToStep(parseFloat(txlabelPPO2CCR.value), 0.05),
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

            // O2 half of the split pill: green when this IS the ideal O2%
            // for the current depth, theme blue otherwise.
            txBestO2CCR.classList.toggle("is-ideal", txBestO2CCR.textContent == Math.min(Math.ceil(0.9 / ambientPressure * 100), 21));
            updateDiluentButtonState();

            updateGasDensityCCR();
            updateGasPricesCCR();
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
            labelTempCCR.value = tempC; // pill shows the same Celsius value the slider itself uses
            tempK = Math.round(values[handle]) + 273.15;
            updateGasDensityCCR();
        });

        // Typing a temperature moves the slider directly - no derived
        // value here (matches Set Max Depth's "just a user entry" pill).
        labelTempCCR.addEventListener('change', function () {
            var typedTemp = parseFloat(labelTempCCR.value);
            if (isNaN(typedTemp)) { labelTempCCR.value = Math.round(sliderTempCCR.noUiSlider.get()); return; }
            sliderTempCCR.noUiSlider.set(typedTemp);
        });

        // Typing a Diluent PPO2 moves the O2% slider that actually drives
        // it, same idea as Open Circuit's PPO2 fields.
        txlabelPPO2CCR.addEventListener('change', function () {
            var typedPpo2 = parseFloat(txlabelPPO2CCR.value);
            if (isNaN(typedPpo2)) { txlabelPPO2CCR.value = (Math.round(txsliderPPO2CCR.noUiSlider.get()) * ambientPressure / 100).toFixed(2); return; }
            var targetO2Percent = Math.round((typedPpo2 / ambientPressure) * 100);
            txsliderPPO2CCR.noUiSlider.set(targetO2Percent);
        });

        // Set Point IS the slider's own unit (atm), so typing it just sets
        // the slider directly - no derivation needed.
        labelSetPoint.addEventListener('change', function () {
            var typedSetpoint = parseFloat(labelSetPoint.value);
            if (isNaN(typedSetpoint)) { labelSetPoint.value = sliderSetPoint.noUiSlider.get(); return; }
            sliderSetPoint.noUiSlider.set(typedSetpoint);
        });

        // Typing an END moves the He% slider that actually drives it -
        // invert calculateENDCCR's own formula, same idea as Open
        // Circuit's Trimix END field.
        labelENDCCR.addEventListener('change', function () {
            var typedEnd = parseFloat(labelENDCCR.value);
            if (isNaN(typedEnd)) { labelENDCCR.value = Math.round(sliderHeCCR.noUiSlider.get()); return; }
            var setpoint = parseFloat(labelSetPoint.value);
            var diluentO2 = parseFloat(txBestO2CCR.textContent) / 100;
            var isOxygenNarcotic = O2NarcoticCCR.checked ? 1 : 0;
            var ambientPressureDepth = depth / 33 + 1;
            var loopO2 = setpoint / ambientPressureDepth;
            var remainingFraction = 1 - loopO2;
            // calculateENDCCR solves forward from He% to END; walk the
            // He% slider until its own END lands on (or just past) what
            // was typed, since the underlying relationship isn't a clean
            // one-line inverse the way Nitrox/Trimix's PPO2<->O2% is.
            var targetHePercent = sliderHeCCR.noUiSlider.get();
            for (var he = 0; he <= 95; he++) {
                var end = calculateENDCCR(depth, setpoint, diluentO2, he / 100, isOxygenNarcotic);
                if (end <= typedEnd) { targetHePercent = he; break; }
            }
            sliderHeCCR.noUiSlider.set(targetHePercent);
        });

        // "Calculate Best Diluent" shares the same is-solved treatment as
        // Open Circuit's Nitrox/Trimix - solved once BOTH O2 and He are
        // independently at their ideal values.
        function updateDiluentButtonState() {
            var bothIdeal = txBestO2CCR.classList.contains('is-ideal') && txBestHeCCR.classList.contains('is-ideal');
            var btn = document.getElementById("buttonBestDiluent");
            btn.classList.toggle("is-solved", bothIdeal);
            btn.classList.toggle("btn-secondary", bothIdeal);
            btn.classList.toggle("btn-info", !bothIdeal);
        }

        // Both sliders' own 'update' handlers above (fired synchronously
        // by .set() below) are what actually mark each half ideal and
        // update the button via updateDiluentButtonState(), so this only
        // needs to move them.
        document.getElementById("buttonBestDiluent").addEventListener("click", function () {
            O2NarcoticCCR.checked = true;
            waterVapor.checked = true;
            sliderSetPoint.noUiSlider.set(sliderSetPoint.noUiSlider.options.start);
            // update ambient pressure
            ambientPressure = depth / 33 +1;

            sliderHeCCR.noUiSlider.set(currentBestHeCCR());
            txsliderPPO2CCR.noUiSlider.set(Math.min(Math.ceil(0.9 / ambientPressure * 100), 21));

            sliderTempCCR.noUiSlider.set(sliderTempCCR.noUiSlider.options.start);
        });

        function updateCCRSliders() {
            ambientPressure = depth / 33 + 1;

            txsliderPPO2CCR.noUiSlider.updateOptions({
                range: {
                    'min': Math.ceil(0.16 / ambientPressure * 100),
                    'max': Math.min(Math.floor(1.2 / ambientPressure * 100),21)
                }
            });
            sliderHeCCR.noUiSlider.set(currentBestHeCCR());
            txsliderPPO2CCR.noUiSlider.set(Math.min(Math.ceil(0.9 / ambientPressure * 100), 21));

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

    </script>

    @endpush
</x-page-template>

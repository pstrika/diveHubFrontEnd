<x-page-template bodyClass='g-sidenav-show  bg-gray-200' :SEO="$SEO">
    
    <x-auth.navbars.sidebar activePage="siteDetails" activeItem="siteDetails" activeSubitem=""></x-auth.navbars.sidebar>
    
    
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
        <x-auth.navbars.navs.auth pageTitle="Dive Sites"></x-auth.navbars.navs.auth>
        <!-- End Navbar -->
        <div class="container-fluid py-0">

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

            <!-- Modal rating -->
            <div class="modal fade" id="modalRating" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-weight-normal" id="exampleModalLabel">Rate site <b>{{ $site->name }}</b></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="myForm" class="multisteps-form__form m-auto" action="{{ route('RateSite') }}" method="POST" enctype="multipart/form-data">
                        @csrf <!-- Add CSRF token for security -->
                        <input type="hidden" name="siteId" value="{{ $site->id }}">
                        <div class="modal-body m-auto">
                            <input type="hidden" id="valueRate" name="rate">
                            <div id="rateSite"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn bg-gradient-secondary" data-bs-dismiss="modal">Close</button>
                            <button class="btn bg-gradient-info ms-auto" id="submit-all" title="Send" onclick="submitform()">Submit</button> {{---type="submit"----}}
                            
                        </div>
                    </form>
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

            <div class="page-header min-height-200 max-height-300 border-radius-xl mt-4 mx-0" style="background-image: url('/assets/img/illustrations/site_wreck.webp');">
                <span class="mask  bg-gradient-info  opacity-4"></span>
            </div>

            <div class="card p-0 position-relative mt-n5 mx-3 z-index-2 mb-4">
                
                    <div class="p-0 mt-0 mx-2 border-radius-lg py-3 pe-1">
                        
                        {{-- Div for site name and type--}}
                        <div style="float: left;" class="mt-n4">
                            <table> <tbody>
                                <tr>
                                    <td class="w-10"><img style="width: 70px; height: auto;" src="{{ asset('assets') }}/img/icons/{{ $site->type }}_icon.png" alt="{{ $site->type }}"></td>
                                    <td><h2 class="card-title text-info mx-3 mt-3">{{ $site->name }}</h2>
                                        <p class="align-middle text-left text-md text-info mx-3 mt-n3">{{ $site->type }} in {{ ucwords($location->location) }}</p>
                                    </td> 
                                </tr>
                            </tbody></table>
                            @if(auth()->user()->isNotGuest())
                            <table> <tbody>
                                <tr>
                                    <td>
                                        <div class="mt-1" style="float: right;" data-bs-toggle="tooltip" data-bs-placement="top" title="Add/Remove from wishlist">
                                            
                                            <button class="btn btn-icon btn-3 btn-info" type="button" onclick="window.location.href='{{ route('UpdateWished', ['siteId' => $site->id]) }}';">
                                                <span class="btn-inner--icon"><i class="material-icons">{{ !$wished ? "favorite" : "favorite_border"}}</i></span>
                                                <span class="btn-inner--text"> {{ !$wished ? "Add to wishlist" : "Remove from wishlist"}}</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody></table>
                            @endif
                        </div>
                        {{-- Div for star ratings--}}
                        <div class="m-auto" style="float: right;">
                            @php
                                $productRating = $site->rate; // Replace with your actual product rating
                            @endphp

                            {{--@foreach(range(1, 5) as $i)
                                <span class="fa-stack" style="width: 1em; font-size: 2em;">
                                    <i class="far fa-star fa-stack-1x"></i>
                                    @if($productRating > 0)
                                        @if($productRating > 0.5)
                                            <i class="fas fa-star fa-stack-1x" style="color: gold;"></i>
                                        @else
                                            <i class="fas fa-star-half fa-stack-1x" style="color: gold; "></i>
                                        @endif
                                    @endif
                                    @php $productRating--; @endphp
                                </span>
                            @endforeach--}}

                            <div class="d-flex justify-content-end"><div id="rateYoReadOnly"></div></div>
                            
                            <div class="mt-1">
                                <p class="align-middle text-end text-md text-info mt-n2"><b>{{ $site->votes }} ratings</b></p>
                            </div>

                            {{--Don't allow rating if guest--}}
                            @if(auth()->user()->isNotGuest())
                                @if(!$ratedAlready)
                                <div class="mt-n1">
                                    <p class="align-middle text-end text-xs text-decoration-underline text-info mt-0"><a href="#" data-bs-toggle="modal" data-bs-target="#modalRating"><b>rate this site</b></a></p>
                                </div>
                                @else
                                <div class="mt-n1">
                                    <p class="align-middle text-end text-xs text-info mt-0"><b>You already rated this site</b></p>
                                </div>
                                @endif
                                <div class="form-check form-switch ps-0">
                                    <form method="POST" action="{{ route('UpdateVisited') }}" id="updatedVisited-form">
                                        @csrf
                                        <input name="visited" type="text" hidden id="alreadyVisitedHiddenInput">
                                        <input name="site" type="text" hidden id="siteHiddenInput" value="{{ $site->id }}">
                                        <input class="form-check-input ms-auto" type="checkbox"
                                            id="alreadyVisited" {{ $visited ? "checked" : ""}}>

                                        <label class="form-check-label text-body ms-3 mt-0"
                                            for="flexSwitchCheckDefault" data-bs-toggle="tooltip" data-bs-placement="top" title="Click to update">Already dove this site?</label>
                                    </form>
                                </div>
                            @else
                                <div class="mt-n1">
                                <form method="POST" action="{{ route('logout') }}" class="d-none" id="logout-form">
                                    @csrf
                                    
                                </form>
                                <a class="nav-link text-white " href="{{ route('logout') }}"
                                onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                                    <p class="align-middle text-end text-xs text-info mt-0"><b>Register here to rate this site</b></p>
                                </a>
                                
                                    
                                </div>
                            @endif

                        </div>

                    </div>
                </div>
            </div>

            <div class="row mx-2">
                
                
                {{-- Card Details --}}
                <div class="col-md-12">             
                    <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h2 class="card-title text-white mx-4">Details</h4>
                                <div class="table-responsive"></div>
                            </div>
                        </div>
                        <div class="card-body">
                            
                            <div class="row">
                                <div class="col-md-3">
                                    <div id="gauge2" class="gauge-container justify-content-center mx-auto five"> </div>
                                    @php
                                        if($site->level == 0)
                                            $level="Open Water";
                                        elseif($site->level == 1)
                                            $level="Advanced Open Water";
                                        elseif($site->level == 2)
                                            $level="Technical Air";
                                        elseif($site->level == 3)
                                            $level="Technical Normoxic Trimix";
                                        elseif($site->level == 4)
                                            $level="Technical Hypoxic Trimix";
                                        
                                    @endphp
                                    <div class="align-middle text-center text-md"><b>{{ $level }}</b></div>
                                    <div class="align-middle text-center text-xxs">Minimum Recommended Certification</div>    

                                    <div class="table-responsive">                                   
                                        <table class="table align-items-center mb-0"> 
                                            <tbody>
                                                <div> <td>
                                                    <table class="table align-items-center mb-0">
                                                        @if($site->maxDepth)
                                                            <tr><td class="text-secondary text-end text-sm font-weight-bolder opacity-7 w-50">Max Depth</td>
                                                            <td class="align-middle text-left text-md w-50"><b>{{ $site->maxDepth}} ft</b></td> </tr>
                                                        @endif

                                                        @if($site->avgDepth)
                                                            <tr><td class="text-secondary text-end text-sm font-weight-bolder opacity-7">Average Depth</td>
                                                            <td class="align-middle text-left text-md"><b>{{ $site->avgDepth}} ft</a></b></td> </tr>
                                                        @endif
                                                    </table>
                                                    <div class="table">
                                                        @if($site->access)
                                                            <div class="text-secondary text-center text-sm font-weight-bolder opacity-7">Access</div>
                                                            <div class="align-middle text-center text-md text-wrap mt-n1">
                                                                <b>{{ $site->access}}</b>
                                                                <?php
                                                                    if($site->access == "Beach Access")
                                                                        echo "(" . $site->distance_from_shore . " ft from shore)";
                                                                ?>
                                                                
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="text-secondary text-center text-sm font-weight-bolder opacity-7">GPS coordinates</div>
                                                    <div class="align-middle text-center text-md text-wrap"><b>{{ $site->gpsLat}}<br>{{ $site->gpsLon}}</b></s> </div>
                                                </td></tr>
                                            </tbody>
                                        </table>
                                    </div>  
                                </div>

                                <div class="col-md-6">
                                    <div class="border-radius-xl">
                                        <div id="map" style="border: 2px solid #2F88EC; width: 100%; height: 350px; border-radius: 1rem; background-color: #f0f0f0; padding: 1rem;"></div>
                                    </div>
                                    
                                </div>

                                @if(!empty($operators))
                                <div class="col-md-3">
                                    <div class="table-responsive">    
                                        <table class="table align-items-center mb-0"> 
                                            <tr><td class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center" style="border: none;">Frequently visiting operators</td> </tr>
                                        </table>
                                        <table class="table align-items-center mb-0"> 
                                            <tbody>
                                                @foreach($operators as $operator)
                                                    <tr>
                                                        <td class="w-20"><img src="{{ asset('assets') }}{{ $operator->logoUrl}}" alt="img-blur-shadow" class="img-fluid"></td>
                                                        <td class="text-wrap"><a href="/OperatorDetails/{{ $operator->id }}"> {{ $operator->operatorName }}</a></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @endif

                            </div>  
                            
                        </div>
                    </div>
                </div>

            </div>

            {{-- Card Gases --}}
            
            <div class="row mx-2">
                
                <div class="col-md-12">             
                    <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h2 class="card-title text-white mx-4">Best Gases</h4>
                                <div class="table-responsive"></div>
                            </div>
                        </div>
                        <div class="card-body">
                        
                            <div class="row">
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
                                            @if( $site->maxDepth <= 140)
                                                <div class="label-container">
                                                    <!-- Highlighted max depth -->
                                                    <label class="left-label">NDL at <span class="text-info" style="font-weight: bold;">{{ $site->maxDepth }} ft</span> - 24 hr min surface interval</label>

                                                    <!-- NDL result -->
                                                    <label class="text-info right-label-normal custom-label" id="ndlResult">-</label>
                                                    <label class="text-info">m</label>
                                                </div>
                                                <div class="text-center" style="border: none;"> <!-- Added text-center here -->
                                                    <a type="button" class="btn btn-info mt-0" id="calculateNDLButton">
                                                        Calculate NDL
                                                    </a>
                                                </div>
                                            @endif
                                            <!-- Legend directly below the first label -->
                                            <div class="text-center mt-n2">
                                                <label class="text-center text-danger text-xs">Always use a dive computer</label>
                                            </div>
                                                    
                                        </div>
                                    </div>
                                </div>


                                @if( $site->maxDepth < 180)
                                    @if( $site->maxDepth > 150)
                                        <div class="col-md-4" style="border-bottom: 1px solid #D3D3D3;">
                                    @else
                                        <div class="col-md-8" style="border-bottom: 1px solid #D3D3D3;">
                                    @endif
                                
                                    
                                        <table class="table align-items-center mb-0"> 
                                            <tr><td class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center" style="border: none;">Nitrox</td> </tr>
                                        </table>
                                        <div class="mt-n2">
                                            <input type="hidden" id="sliderPPO2-value" name="sliderPPO2">
                                            
                                            <!-- Flex container for label alignment -->
                                            <div class="label-container">
                                                <label class="left-label" id="mainLabel">PPO2 at max depth ({{ $site->maxDepth }} ft)</label>
                                                <label class="text-info right-label-normal custom-label" id="labelPPO2">Bottom PPO2</label>
                                                <label class="text-info">atm</label>
                                            </div>
                                            <div class="label-container">
                                                <label class="left-label" id="mainLabel2">PPO2 at average depth ({{ $site->avgDepth }} ft)</label>
                                                <label class="text-info right-label-normal custom-label" id="labelPPO2Avg">Bottom PPO2</label>
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
                                                        <a type="button" class="btn btn-info mt-n4" id="buttonBestNitrox">
                                                            Calculate Best Nitrox
                                                        </a>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>

                                    </div>
                                @endif

                                @if( $site->maxDepth > 150)
                                    @if( $site->maxDepth < 180)
                                        <div class="col-md-4">
                                    @else
                                        <div class="col-md-8">
                                    @endif
                                        <table class="table align-items-center mb-0"> 
                                            <tr><td class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center" style="border: none;">Trimix</td> </tr>
                                        </table>
                                        <div class="mt-n2">
                                            <input type="hidden" id="sliderPPO2-value" name="txsliderPPO2">
                                            
                                            <!-- Flex container for label alignment -->
                                            <div class="label-container">
                                                <label class="left-label" id="mainLabelTx">PPO2 at max depth ({{ $site->maxDepth }} ft)</label>
                                                <label class="text-info right-label-normal custom-label" id="txlabelPPO2">Bottom PPO2</label>
                                                <label class="text-info">atm</label>
                                            </div>
                                            <div class="label-container">
                                                <label class="left-label" id="txmainLabel2">PPO2 at average depth ({{ $site->avgDepth }} ft)</label>
                                                <label class="text-info right-label-normal custom-label" id="txlabelPPO2Avg">Bottom PPO2</label>
                                                <label class="text-info">atm</label>
                                            </div>
                                            
                                            <div class="slider-styled" id="txsliderPPO2"></div>
                                            <div class="text-secondary text-xs font-weight-bolder opacity-7 text-center mt-2" style="border: none;">O2 Content</div>
                                        </div>
                                        <div class="mt-2">
                                            <input type="hidden" id="sliderPPO2-value" name="txsliderPPHe">
                                            
                                            <!-- Flex container for label alignment -->
                                            <div class="label-container">
                                                <label class="left-label" id="ENDLabelMax">END at max depth ({{ $site->maxDepth }} ft)</label>
                                                <label class="text-info right-label-normal custom-label" id="txlabelEND"></label>
                                                <label class="text-info">ft</label>
                                            </div>
                                            <div class="label-container">
                                                <label class="left-label" id="ENDLabelAvg">END at average depth ({{ $site->avgDepth }} ft)</label>
                                                <label class="text-info right-label-normal custom-label" id="txlabelENDAvg"></label>
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
                                                                <a type="button" class="btn btn-info mt-0" id="txbuttonBestNitrox">
                                                                    Calculate Best Trimix
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    
                                                </tbody> 
                                            </table>
                                        </div>

                                    </div>
                                @endif

                            </div>  
                            
                        </div>
                    </div>
                </div>

            </div>
            
            
            <div class="row mx-2">
                @php
                    $video = json_decode($site->videos);    
                @endphp

                @if(($video != null and $video[0]->link != null) or count($site->upcomingTrips))
                    <div class="col-md-6">
                        {{--Card for video---}}
                        @if($video != null)
                            @if($video[0]->link)
                                <div class="card p-0 position-relative mt-n2 mx-0 z-index-2 mb-4">
                                    <div class="card-body mt-0">
                                        <iframe id="youtubeVideo" class="img-fluid border-radius-lg" src="{{ $video[0]->link }}" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                                        @if($video[0]->credit)
                                            <p class="align-middle text-center text-sm"><b>🎥 {{ $video[0]->credit }}</b></p>
                                        @endif
                                        
                                    </div>
                                </div>
                            @endif
                        @endif

                        {{--Card for upcoming trips---}}
                        @if(count($site->upcomingTrips))
                            <div class="row mx-1 mt-3">
                                <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                                    <div class="card-header p-0 mt-n4 mx-3">
                                        <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                            <h2 class="card-title text-white mx-4">Upcoming trips ({{ count($site->upcomingTrips) }})</h2>
                                            <div class="table-responsive"></div>
                                        </div>
                                    </div>
                                    <div class="card-body mt-4">
                                        <div class="table-responsive">
                                            <table id="tableUpcomingTrips" style="display: block; max-height: 300px; overflow-y: scroll">
                                                <thead class="text-info">
                                                    <th class="align-top">Operator</th>
                                                    <th class="px-4 align-top">Date</th> 
                                                    <th class="px-4 align-top">Time</th>
                                                    <th class="py-0 align-top" data-bs-toggle="tooltip" data-bs-placement="top" title="Click on the numbers below to go to trip booking page" data-container="body" data-animation="true">Availability<p class="text-xs mt-0 px-1">click-to-book</p></th>
                                                    <th class="px-4 align-top" data-bs-toggle="tooltip" data-bs-placement="top" title="Click on the name of the trip to see full trip details" data-container="body" data-animation="true">Trip Name<p class="text-xs mt-0 px-1">click for details</p></th>
                                                </thead>
                                                <tbody> 
                                                    @foreach($site->upcomingTrips as $trip)
                                                        
                                                        <tr style="border-bottom: 1px solid #D3D3D3;" class="justify-content-center align-middle" data-tag="{{ $trip->tags }}">
                                                            
                                                            <td class="px-0 py-2 text-sm text-wrap align-middle justify-content-center">{{ $trip->operatorName }}</td>
                                                            <td class="px-4">{{ $trip->date }}</td>
                                                            <td class="px-4">{{ $trip->departureTime }}</td>
                                                            @if($trip->tripFreeSpots == 0)
                                                                <td class="text-center">-</td>
                                                            @else
                                                                <td class="text-center"> <a href="{{ $trip->linkToBook }}" target="_blank">{{ $trip->tripFreeSpots == 1000 ? "Y" : $trip->tripFreeSpots }}</a></td>
                                                            @endif
                                                            <td class="px-4 text-sm"><a href="{{ route('TripDetails', ['tripId' => $trip->id]) }}">{{ $trip->tripName }}<a></td>
                                                            
                                                        </tr>
                                                
                                                    @endforeach          
                                                </tbody>
                                            </table>
                                        </div>   
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    
                @endif
                {{-- Card pictures --}}
                @if($site->pics)
                    <div class="col-md-6">             
                        <div class="card p-0 position-relative mt-n2 mx-0 z-index-2 mb-4">
                            {{--<div class="card-header p-0 mt-n4 mx-3">
                                <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                    <h2 class="card-title text-white mx-4">Pictures</h2>
                                    <div class="table-responsive"></div>
                                </div>
                            </div>--}}
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table align-items-center mb-0"> 
                                        <tbody>
                                            <tr><td>
                                            <div id="carouselExampleControls" class="carousel slide" data-bs-ride="carousel">
                                                <div class="carousel-inner">
                                                    @php 
                                                        $first = true;
                                                    @endphp
                                                        
                                                    @foreach ($photos as $photo)    
                                                        <div class="carousel-item {{ $first ? "active" : "" }}">
                                                            @php
                                                                $first = false;
                                                            @endphp
                                                            <div class="page-header min-vh-50 border-radius-xl" style="background-image: url('{{ asset('assets') }}/img/sites/{{ $photo->file}}');">
                                                            
                                                                <div class="container">
                                                                    
                                                                </div>
                                                            </div>
                                                            {{--<h4 class="text-info mb-0 fadeIn1 fadeInBottom align-bottom text-center"> {{ $boat->name }}</h4>--}}

                                                            <table class="table align-items-center mb-0">
                                                        
                                                                @if($photo->desc)
                                                                    <tr class="align-top">
                                                                    <td class="align-middle text-center text-wrap text-md"><b>{{ $photo->desc }}</b></td> </tr>
                                                                @endif
                                                                
                                                                @if($photo->credit)
                                                                    <tr>
                                                                    <td class="align-middle text-center text-sm"><b>📸 {{ $photo->credit }}</b></td> </tr>
                                                                @endif
                                                                
                            

                                                            </table>


                                                        </div>
                                                    @endforeach

                                                    
                                                </div>

                                                <div class="position-absolute min-vh-25 w-100 top-10">
                                                    <a class="carousel-control-prev" href="#carouselExampleControls" role="button" data-bs-slide="prev">
                                                        <span class="carousel-control-prev-icon position-absolute bottom-50 text-info" aria-hidden="true"></span>
                                                        <span class="visually-hidden">Previous</span>
                                                    </a>
                                                    <a class="carousel-control-next" href="#carouselExampleControls" role="button" data-bs-slide="next">
                                                        <span class="carousel-control-next-icon position-absolute bottom-50" aria-hidden="true"></span>
                                                        <span class="visually-hidden">Next</span>
                                                    </a>
                                                </div>
                                                
                                            </div>

                                        </tbody>    
                                    </table>
                                </div>    
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="row mx-2">

                {{--Card 3D model--}}
                @if($site->dModel != null)
                <div class="col-md-12">             
                    <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h2 class="card-title text-white mx-4">3D-Model</h2>
                                <div class="table-responsive"></div>
                            </div>
                        </div>
                        <div class="card-body mt-4">
                            <div class="wp-block-tdvb-td-viewer  align" id="tdvb3DViewerBlock-38a8dc92-1">
                                <style> 
                                    #tdvb3DViewerBlock-38a8dc92-1 .tdvb3DViewerBlock {
                                        text-align: center;
                                    }
                                    #tdvb3DViewerBlock-38a8dc92-1 .tdvb3DViewerBlock model-viewer {
                                        width: 100%;
                                        height: 600px;
                                    }
                                    .progress-bar {
                                        display: flex;
                                        flex-direction: column;
                                        justify-content: center;
                                        overflow: hidden;
                                        color: #fff;
                                        text-align: center;
                                        white-space: nowrap;
                                        background-color: #ffffff;
                                        transition: width 0.6s ease;
                                    }
                                </style>
                                <div class="tdvb3DViewerBlock">
                                    <model-viewer camera-controls="" src="{{ $site->dModel}}" ar-modes="webxr scene-viewer quick-look" poster="https://www.bythecmedia.com/wp-content/uploads/2023/05/okinawa.jpg" shadow-intensity="1" camera-orbit="0deg 75deg 226.7m" field-of-view="30deg" exposure="2" shadow-softness="1" ar-status="not-presenting">
                                        {{--<div class="progress-bar hide" slot="progress-bar">
                                            <div class="update-bar"></div>
                                        </div>--}}
                                        
                                    </model-viewer>
                                    <p class="align-middle text-center text-sm"><b>📸 {{ $site->dModelCredit }}</b></p>
                                </div>
                            </div> 
                           
                        </div>
                    </div>
                </div>
                @endif

            </div>

            <div class="row mx-2">
                <div class="col-md-6">             
                    <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-secondary shadow-secondary border-radius-xl py-3 pe-1">
                                <h2 class="card-title text-white mx-4">Divers' Uploaded Pictures</h4>
                                <div class="table-responsive"></div>
                            </div>
                        </div>
                        <div class="card-body mt-4">
                            Coming soon!
                        </div>
                    </div>
                </div>

                <div class="col-md-6">             
                    <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h2 class="card-title text-white mx-4">Divers' Reviews</h4>
                                <div class="table-responsive"></div>
                            </div>
                        </div>
                        <div class="card-body mt-n3">
                            @if (auth()->user()->isNotGuest())
                                <div class="mt-0" data-bs-toggle="tooltip" data-bs-placement="top" title="Add a comment">                                      
                                    <button id="addReviewButton" class="btn btn-icon btn-3 btn-info" type="button" onclick="showReviewForm()">
                                        <span class="btn-inner--text"> Add review</span>
                                    </button>
                                </div>

                                <div id="addReviewForm" style="display:none;">
                                    <form action='{{ route('AddSiteReview', ['siteId' => $site->id]) }}' method="POST">
                                        @csrf
                                        <div class="input-group input-group-dynamic">
                                            <textarea id="review" class="multisteps-form__input form-control" name="review" rows="2" style="resize: vertical;" placeholder="write a review..."></textarea>
                                        </div>
                                        <div class="button-group mt-1">
                                            <button type="button" class="btn btn-secondary" onclick="cancelReview()">Cancel</button>
                                            <button type="submit" class="btn btn-info">Submit</button>
                                        </div>
                                    </form>
                                </div>
                            @else
                                <div class="mt-0" data-bs-toggle="tooltip" data-bs-placement="top" title="Add a comment">                                      
                                    <button id="addReviewButton" class="btn btn-icon btn-3 btn-primary" type="button" onclick="showModalGuest()">
                                        <span class="btn-inner--icon"><i class="material-icons">lock</i></span>
                                        <span class="btn-inner--text"> Add review</span>
                                    </button>
                                </div>
                            @endif
                            <div class="table-responsive">
                                <table id="reviews" style="display: block; max-height: 300px; overflow-y: scroll; width: 100%;">
                                    
                                    <tbody>
                                        @if(count($site->reviews))
                                            @foreach($site->reviews as $review)
                                                <tr style="border-bottom: 1px solid #D3D3D3;">
                                                    <td style="width: 10%;"> 
                                                        <table>
                                                            <tbody>
                                                                <tr class="align-items-center"><td class="align-items-center text-center">
                                                                    <div class="avatar avatar-sm">
                                                                        @if($review->user->picture)
                                                                            <img src="{{ asset('assets') }}/img/users/{{  $review->user->picture }}" alt="profile_image"
                                                                                class="w-100 rounded-circle shadow-sm">
                                                                        @else
                                                                            <img src="{{ asset('assets') }}/img/default-avatar.png" alt="profile_image"
                                                                                class="w-100 rounded-circle shadow-sm" style="background: black;">
                                                                        @endif
                                                                            
                                                                    </div>
                                                                </td></tr>
                                                                <tr class="text-center"> <td class="text-xs text-info">
                                                                    <div class="mt-n2">
                                                                        {{ $review->user->name }}
                                                                    </div>
                                                                </td></tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                    <td style="width: 90%;">
                                                        <table>
                                                            <tbody>
                                                                <tr>
                                                                    <td class="text-sm"> 
                                                                        <div class="text-sm mx-2 fst-italic">
                                                                            {{ $review->comment }}
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>
                                                                        <div class="fw-bold text-xs mx-2 mt-1">
                                                                            posted on {{ $review->created_at }}
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            Be the first to add a review
                                        @endif
                                    </tbody>
                                </table>
                            </div>  
                        </div>
                    </div>
                </div>

                
            </div>
            <div class="row mx-2">
                    {{-- Card Wreck  --}}
                    @if($site->type == "wreck")
                        <?php
                            
                            $wreck = json_decode($site->wreckData, true);
                            
                            
                        ?>
                        <div class="col-md-4">             
                            <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                                <div class="card-header p-0 mt-n4 mx-3">
                                    <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                        <h2 class="card-title text-white mx-4">Wreck Details</h2>
                                        <div class="table-responsive"></div>
                                    </div>
                                </div>
                                <div class="card-body mt-n4">
                                    <div class="table-responsive">
                                        <table class="table align-items-center mb-0"> 
                                            <tbody>
                                                <tr><td class="text-center" style="border: none;"><img src="{{ asset('assets') }}/img/icons/icons_{{ strtolower($wreck["type"]) }}.png" alt="img-blur-shadow" class="img-fluid"></td></tr> 
                                                <tr style="border-bottom: 1px solid #D3D3D3;"><td class="text-md text-center"> <b>{{ $wreck["type"]}}</b></td> </tr>
                                            </tbody>
                                        </table>

                                        <table class="table align-items-center mb-0"> 
                                            <tbody>
                                                <tr><td class="text-secondary text-end text-sm font-weight-bolder opacity-7 w-50">Sunk date</td>
                                                <td class="align-middle text-left text-md w-50"><b>{{ $wreck["sunkDate"] }}</b></td> </tr> 

                                                
                                                
                                            </tbody>
                                        </table>

                                        <table class="table align-items-center mb-0"> 
                                            <tbody>
                                                <tr><td class="text-center" style="border: none;"><img src="{{ asset('assets') }}/img/icons/icons_ship_length.png" alt="img-blur-shadow" class="img-fluid"></td>
                                                <td class="text-center" style="border: none;"><img src="{{ asset('assets') }}/img/icons/icons_ship_beam.png" alt="img-blur-shadow" class="img-fluid"></td></tr>  

                                                <tr>
                                                    <td class="text-md text-center" style="border: none;"><b>{{ $wreck["length"] }} ft</b></td>
                                                    <td class="text-md text-center" style="border: none;"> <b>{{ $wreck["beam"] }} ft</b></td>
                                                </tr>

                                                <tr class="mt-n2">
                                                    <td class="text-xxs text-center" style="border: none;">Length</td>
                                                    <td class="text-xxs text-center" style="border: none;">Beam</td>
                                                </tr>
                                                
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                    @endif
                    {{--- Card Site decription --}}
                    <div class="col-md-{{ $site->type == "wreck" ? 8 : 12 }}">             
                        <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                            <div class="card-header p-0 mt-n4 mx-3">
                                <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                    <h2 class="card-title text-white mx-4">Site Description</h4>
                                    <div class="table-responsive"></div>
                                </div>
                            </div>
                            <div class="card-body mt-4">
                                <div id="desc" style="max-height: 424px; overflow-y: auto;">
                                </div>
                            
                                
                            </div>
                        </div>
                    </div>
            </div>

            <div class="row mx-2">

                {{--- Card Route --}}
                <div class="col-md-6">             
                    <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h2 class="card-title text-white mx-4">Route</h4>
                                <div class="table-responsive"></div>
                            </div>
                        </div>
                        <div class="card-body mt-4">
                            <div id="route" style="max-height: 424px; overflow-y: auto;">
                            </div>
                        </div>
                    </div>
                </div>

                {{--- Card Typical Conditions --}}
                <div class="col-md-6">             
                    <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h2 class="card-title text-white mx-4">Typical Conditions</h4>
                                <div class="table-responsive"></div>
                            </div>
                        </div>
                        <div class="card-body mt-4">
                                <div id="typicalConditions" style="max-height: 424px; overflow-y: auto;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mx-2">
                {{--- Card Wreck History --}}
                @if($site->type == "wreck")
                
                <div class="col-md-12">             
                    <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h2 class="card-title text-white mx-4">Wreck History</h2>
                                <div class="table-responsive"></div>
                            </div>
                        </div>
                        <div class="card-body mt-4">
                            <div class="row">
                                @if(!empty($site->historicImg))
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center justify-content-center mt-3">
                                            <img src="{{ asset('assets') }}/img/sites/{{ $site->historicImg }}" class="img-fluid border-radius-xl shadow">
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                @else
                                    <div class="col-md-12">
                                @endif
                                    <div id="history" style="flex-grow: 1; max-height: 424px; overflow-y: auto;" class="mt-2"></div>
                                </div>                            
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            
                


                
            
            
            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>
    
    
    {{--<x-plugins></x-plugins>--}}
    
    @push('js')
    
    <script src="{{ asset('assets') }}/js/plugins/flatpickr.min.js"></script>
    <script src="{{ asset('assets') }}/js/plugins/gauge.js"></script>
    <script src="{{ asset('assets') }}/js/plugins/quill.min.js"></script>
    <script src="{{ asset('assets') }}/js/plugins/jquery-3.6.0.min.js" type="text/javascript"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/rateYo/2.3.2/jquery.rateyo.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/rateYo/2.3.2/jquery.rateyo.min.js"></script>
    <script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/3.5.0/model-viewer.min.js"></script>
    <script src="https://api.mapbox.com/mapbox-gl-js/v2.6.1/mapbox-gl.js"></script>
    <link href="https://api.mapbox.com/mapbox-gl-js/v2.6.1/mapbox-gl.css" rel="stylesheet" />
    <script src="{{ asset('assets') }}/js/plugins/nouislider.js"></script>
    <link href="{{ asset('assets') }}/css/nouislider.css" rel="stylesheet">
    <script src="../../assets/js/plugins/chartjs.min.js"></script>

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

            // Suggest closest standard mix
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
                    if( Math.abs((he - element.He) <= 0.1)) { 
                        document.getElementById("closeMix").textContent = element.mix; // Use element.mix, not this->mix
                        document.getElementById("closeMixRow").removeAttribute("hidden"); // Show the row
                    }
                }
            });


            return;
        }
    </script>
    
    
    <script>
        // Constants: Atmospheric and conversion settings
        const ATMOSPHERIC_PRESSURE = 1.0; // Pressure at sea level in bar
        const WATER_COLUMN_PRESSURE = 0.1; // Increase in pressure per 1m of seawater in bar

        // ZH-L16C Parameters for Nitrogen (N2) and Helium (He) compartments
        const nitrogenCompartments = [
            { halfTime: 5.0, a: 1.1696, b: 0.5578 },
            { halfTime: 8.0, a: 1.0, b: 0.6514 },
            { halfTime: 12.5, a: 0.8618, b: 0.7222 },
            { halfTime: 18.5, a: 0.7562, b: 0.7825 },
            { halfTime: 27.0, a: 0.62, b: 0.8126 },
            { halfTime: 38.3, a: 0.5043, b: 0.8434 },
            { halfTime: 54.3, a: 0.441, b: 0.8693 },
            { halfTime: 77.0, a: 0.4, b: 0.8910 },
            { halfTime: 109.0, a: 0.375, b: 0.9092 },
            { halfTime: 146.0, a: 0.35, b: 0.9222 },
            { halfTime: 187.0, a: 0.3295, b: 0.9319 },
            { halfTime: 239.0, a: 0.3065, b: 0.9403 },
            { halfTime: 305.0, a: 0.2835, b: 0.9477 },
            { halfTime: 390.0, a: 0.261, b: 0.9544 },
            { halfTime: 498.0, a: 0.248, b: 0.9602 },
            { halfTime: 635.0, a: 0.2327, b: 0.9653 }
        ];

        const heliumCompartments = [
            { halfTime: 1.88, a: 1.6189, b: 0.4770 },
            { halfTime: 3.02, a: 1.383, b: 0.5747 },
            { halfTime: 4.72, a: 1.1919, b: 0.6527 },
            { halfTime: 6.99, a: 1.0458, b: 0.7223 },
            { halfTime: 10.21, a: 0.922, b: 0.7582 },
            { halfTime: 14.48, a: 0.8205, b: 0.7957 },
            { halfTime: 20.53, a: 0.7305, b: 0.8279 },
            { halfTime: 29.11, a: 0.6502, b: 0.8553 },
            { halfTime: 41.20, a: 0.595, b: 0.8757 },
            { halfTime: 55.19, a: 0.5545, b: 0.8903 },
            { halfTime: 70.69, a: 0.5333, b: 0.8997 },
            { halfTime: 90.34, a: 0.5189, b: 0.9073 },
            { halfTime: 115.29, a: 0.5181, b: 0.9122 },
            { halfTime: 147.42, a: 0.5176, b: 0.9171 },
            { halfTime: 188.24, a: 0.5172, b: 0.9217 },
            { halfTime: 240.03, a: 0.5119, b: 0.9267 }
        ];

        // Function to calculate inert gas pressure in a tissue compartment
        function calculateInertGasPressure(initialPressure, ambientPressure, fraction, time, halfTime) {
            const k = Math.log(2) / halfTime; // Rate constant
            return initialPressure + (ambientPressure * fraction - initialPressure) * (1 - Math.exp(-k * time));
        }

        // Function to calculate M-value for a compartment
        function calculateMValue(ambientPressure, a, b) {
            return a + b * ambientPressure;
        }

        // Function to calculate NDL (supports Nitrox, Trimix, and Heliox)
        function calculateNDL(depth, gasMix) {
            const nitrogenFraction = gasMix.N2; // Fraction of nitrogen
            const heliumFraction = gasMix.He || 0; // Fraction of helium (default to 0)
            const ambientPressure = ATMOSPHERIC_PRESSURE + depth * WATER_COLUMN_PRESSURE; // Absolute ambient pressure in bar

            let ndl = Infinity; // Initialize NDL as a very large value

            // Loop through each tissue compartment
            for (let i = 0; i < nitrogenCompartments.length; i++) {
                const nitrogenCompartment = nitrogenCompartments[i];
                const heliumCompartment = heliumCompartments[i];

                const { halfTime: halfTimeN2, a: aN2, b: bN2 } = nitrogenCompartment;
                const { halfTime: halfTimeHe, a: aHe, b: bHe } = heliumCompartment;

                // M-values for nitrogen and helium at the current depth
                const mValueN2 = calculateMValue(ambientPressure, aN2, bN2);
                const mValueHe = calculateMValue(ambientPressure, aHe, bHe);

                // Solve for the time when nitrogen and helium pressures equal their respective M-values
                let t = 0;
                while (true) {
                    const nitrogenPressure = calculateInertGasPressure(0, ambientPressure, nitrogenFraction, t, halfTimeN2);
                    const heliumPressure = calculateInertGasPressure(0, ambientPressure, heliumFraction, t, halfTimeHe);

                    // Combined gas pressure in the compartment
                    const totalPressure = nitrogenPressure + heliumPressure;

                    // Stop iterating when total pressure exceeds the smaller of the two M-values
                    const limitingMValue = Math.min(mValueN2, mValueHe);
                    if (totalPressure >= limitingMValue) {
                        if (t < ndl) {
                            ndl = t; // Update the NDL if this compartment limits it
                        }
                        break;
                    }
                    t += 1; // Increment time in minutes
                }
            }

            return ndl; // Return the minimum NDL across all compartments
        }

        // Example usage
        //const depth = 30; // Depth in meters
        //const gasMix = { O2: 0.18, N2: 0.50, He: 0.32 }; // Example Trimix gas mix

        //const ndl = calculateNDL(depth, gasMix);
        //console.log(`NDL at ${depth} meters with gas mix: O2=${gasMix.O2 * 100}%, N2=${gasMix.N2 * 100}%, He=${gasMix.He * 100}% is: ${ndl} minutes`);

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
        var labelAvg = document.getElementById('labelPPO2Avg');
        var sliderValueInput = document.getElementById('slider-value');
        var labelBestNitrox = document.getElementById('bestNitrox');
        


        var bestMix =  Math.round((1.4 / ({{ $site->maxDepth }} / 33 + 1) * 100));
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

            var sliderValue = parseFloat(values[handle]); // Ensure sliderValue is numeric
            console.log("Slider Value:", sliderValue);

            var maxDepth = parseFloat({{ $site->maxDepth }}); // Ensure maxDepth is rendered as a number
            console.log("Max Depth:", maxDepth);

            var avgDepth = parseFloat({{ $site->avgDepth }}); // Ensure maxDepth is rendered as a number
            console.log("Avg Depth:", avgDepth);

            var ppo2 = (((maxDepth / 33 + 1) * sliderValue) / 100).toFixed(2); // Calculate PPO2 and format
            console.log("PPO2:", ppo2);

            var ppo2avg = (((avgDepth / 33 + 1) * sliderValue) / 100).toFixed(2); // Calculate PPO2 and format
            console.log("PPO2:", ppo2avg);

            label.textContent = ppo2;
            labelAvg.textContent = ppo2avg

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

            if (parseFloat(ppo2avg) > 1.59 || parseFloat(ppo2avg) < 0.16) {
                console.log("High PPO2 - Danger");
                labelAvg.classList.remove("text-info", "text-warning", "right-label-normal", "right-label-warning"); // Remove other classes
                labelAvg.classList.add("text-danger", "right-label-danger"); // Add "text-danger" class
            } else if (parseFloat(ppo2avg) > 1.41 || parseFloat(ppo2avg) < 0.21) {
                console.log("Medium PPO2 - Warning");
                labelAvg.classList.remove("text-info", "text-danger", "right-label-normal", "right-label-danger"); // Remove other classes
                labelAvg.classList.add("text-warning", "right-label-warning"); // Add "text-warning" class
            } else {
                console.log("Low PPO2 - Info");
                labelAvg.classList.remove("text-warning", "text-danger", "right-label-warning", "right-label-danger"); // Remove other classes
                labelAvg.classList.add("text-info", "right-label-normal"); // Add "text-info" class
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
            //calculateNDL({{ $site->maxDepth }}, labelMixO2.textContent.slice(0, -1)/100, labelMixN2.textContent.slice(0, -1)/100, labelMixHe.textContent.slice(0, -1)/100);

            updateLabelHorizontalOffset(0);
            // JavaScript code to toggle visibility of images
            document.getElementById("tank_single").removeAttribute("hidden"); // Removes 'hidden' attribute from the first image
            document.getElementById("tank_double").setAttribute("hidden", "true"); // Adds 'hidden' attribute to the second image

            //update Gas price label
            document.getElementById("tankConf").innerText = "Gas Price (Single)";
            updateGasPrices();

 
            });

            // Add an event listener to the button
            document.getElementById("buttonBestNitrox").addEventListener("click", function () {
                // Reset the slider to its start value
                slider.noUiSlider.set(slider.noUiSlider.options.start);
                updateLabelHorizontalOffset(0);
                document.getElementById("tank_single").removeAttribute("hidden"); // Removes 'hidden' attribute from the first image
                document.getElementById("tank_double").setAttribute("hidden", "true"); // Adds 'hidden' attribute to the second image
                //update Gas price label
                document.getElementById("tankConf").innerText = "Gas Price (Single)";
                updateGasPrices();
            });

    </script>




    {{-- Slider script Trimix --}}
    <script>

        var txlabelBestHe = document.getElementById('txbestHe');

        var txslider = document.getElementById('txsliderPPO2');
        var txlabel = document.getElementById('txlabelPPO2');
        var txlabelAvg = document.getElementById('txlabelPPO2Avg');
        var txsliderValueInput = document.getElementById('txslider-value');
        var txlabelBestNitrox = document.getElementById('txbestNitrox');
        var txhypoxic = document.getElementById("txhypoxic");
        var txlabelEND = document.getElementById("txlabelEND");
        var txlabelENDAvg = document.getElementById("txlabelENDAvg");
        var O2Narcotic = document.getElementById("O2Narcotic");
        var bestHe = ((1 - ((80 / 33) +1) / (parseFloat({{ $site->maxDepth }}) / 33 + 1)) * 100).toFixed(0);
        

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
            var ambientPressure = {{ $site->maxDepth }} / 33 + 1;

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

        var txbestMix =  Math.round((1.4 / ({{ $site->maxDepth }} / 33 + 1) * 100));
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

            var txmaxDepth = parseFloat({{ $site->maxDepth }}); // Ensure maxDepth is rendered as a number
            var txavgDepth = parseFloat({{ $site->avgDepth }}); // Ensure maxDepth is rendered as a number
            var O2Factor = 0;

            if(O2Narcotic.checked) {
                O2Factor = 0;
            } else {
                O2Factor = Math.round(txlabelBestNitrox.textContent) / 100;   // Get O2 %
            }

            var equivPMax =  (txmaxDepth / 33 + 1) * (1 - Math.round(values[handle]) / 100 - O2Factor);
            var ENDMax = ((equivPMax - 1) * 33).toFixed(0);
            txlabelEND.textContent = ENDMax;

            var equivPAvg =  (txavgDepth / 33 + 1) * (1 - Math.round(values[handle]) / 100 - O2Factor);
            var ENDAvg = ((equivPAvg - 1) * 33).toFixed(0);
            txlabelENDAvg.textContent = ENDAvg;



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

            if (ENDAvg > 130) {
                txlabelENDAvg.classList.remove("text-info", "text-warning", "right-label-normal", "right-label-warning"); // Remove other classes
                txlabelENDAvg.classList.add("text-danger", "right-label-danger"); // Add "text-danger" class
            } else if (ENDAvg > 100) {
                txlabelENDAvg.classList.remove("text-info", "text-danger", "right-label-normal", "right-label-danger"); // Remove other classes
                txlabelENDAvg.classList.add("text-warning", "right-label-warning"); // Add "text-warning" class
            } else {
                txlabelENDAvg.classList.remove("text-warning", "text-danger", "right-label-warning", "right-label-danger"); // Remove other classes
                txlabelENDAvg.classList.add("text-info", "right-label-normal"); // Add "text-info" class
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
            //const ndl = calculateNDL({{ $site->maxDepth }}, gasMix);
            //labelNDL = document.getElementById('labelNDL');
            //labelNDL.textContent = ndl;
            updateLabelHorizontalOffset(-40);
            // JavaScript code to toggle visibility of images
            document.getElementById("tank_double").removeAttribute("hidden"); // Removes 'hidden' attribute from the first image
            document.getElementById("tank_single").setAttribute("hidden", "true"); // Adds 'hidden' attribute to the second image
            //update Gas price label
            document.getElementById("tankConf").innerText = "Gas Price (Doubles)";
            updateGasPrices();
            
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

            var txmaxDepth = parseFloat({{ $site->maxDepth }}); // Ensure maxDepth is rendered as a number
            console.log("TX Max Depth:", txmaxDepth);

            var txavgDepth = parseFloat({{ $site->avgDepth }}); // Ensure maxDepth is rendered as a number
            console.log("TX Avg Depth:", txavgDepth);

            var txppo2 = (((txmaxDepth / 33 + 1) * txsliderValue) / 100).toFixed(2); // Calculate PPO2 and format
            console.log("TX PPO2:", txppo2);

            var txppo2avg = (((txavgDepth / 33 + 1) * txsliderValue) / 100).toFixed(2); // Calculate PPO2 and format
            console.log("TX PPO2:", txppo2avg);

            txlabel.textContent = txppo2;
            txlabelAvg.textContent = txppo2avg;

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

            if (parseFloat(txppo2avg) > 1.59 || parseFloat(txppo2avg) < 0.16) {
                console.log("High PPO2 - Danger");
                txlabelAvg.classList.remove("text-info", "text-warning", "right-label-normal", "right-label-warning"); // Remove other classes
                txlabelAvg.classList.add("text-danger", "right-label-danger"); // Add "text-danger" class
                
            } else if (parseFloat(txppo2avg) > 1.41 || parseFloat(txppo2avg) < 0.21) {
                console.log("TX Medium PPO2 - Warning");
                txlabelAvg.classList.remove("text-info", "text-danger", "right-label-normal", "right-label-danger"); // Remove other classes
                txlabelAvg.classList.add("text-warning", "right-label-warning"); // Add "text-warning" class
                
            } else {
                console.log("TX Low PPO2 - Info");
                txlabelAvg.classList.remove("text-warning", "text-danger", "right-label-warning", "right-label-danger"); // Remove other classes
                txlabelAvg.classList.add("text-info", "right-label-normal"); // Add "text-info" class
                
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
            //const ndl = calculateNDL({{ $site->maxDepth }}, gasMix);
            //labelNDL = document.getElementById('labelNDL');
            //labelNDL.textContent = ndl;
            updateLabelHorizontalOffset(-40);
            // JavaScript code to toggle visibility of images
            document.getElementById("tank_double").removeAttribute("hidden"); // Removes 'hidden' attribute from the first image
            document.getElementById("tank_single").setAttribute("hidden", "true"); // Adds 'hidden' attribute to the second image
            //update Gas price label
            document.getElementById("tankConf").innerText = "Gas Price (Doubles)";
            updateGasPrices();
        });

        // Add an event listener to the button
        document.getElementById("txbuttonBestNitrox").addEventListener("click", function () {
            // Reset the slider to its start value
            O2Narcotic.checked = true;
            txslider.noUiSlider.set(txslider.noUiSlider.options.start);
            txsliderHe.noUiSlider.set(txsliderHe.noUiSlider.options.start);
            updateGasDensity();
            updateGasMix(txlabelBestNitrox.textContent, txlabelBestHe.textContent);
            $('#ndlResult').text("-");

            //update NDL
            //const gasMix = {O2: txlabelBestNitrox.textContent / 100, N2: (100 - txlabelBestNitrox.textContent - txlabelBestHe.textContent)/100, He: txlabelBestHe.textContent/100};
            //const ndl = calculateNDL({{ $site->maxDepth }}, gasMix);
            //labelNDL = document.getElementById('labelNDL');
            //labelNDL.textContent = ndl;

            updateLabelHorizontalOffset(-40);
            // JavaScript code to toggle visibility of images
            document.getElementById("tank_double").removeAttribute("hidden"); // Removes 'hidden' attribute from the first image
            document.getElementById("tank_single").setAttribute("hidden", "true"); // Adds 'hidden' attribute to the second image
            //update Gas price label
            document.getElementById("tankConf").innerText = "Gas Price (Doubles)";
            updateGasPrices();
            
        });

        // Add an event listener for the 'change' event
        O2Narcotic.addEventListener("change", function () {
            // Check if the checkbox is checked or not
            console.log("Checkbox state changed. Checked:", O2Narcotic.checked);

            var tempLabelMax = document.getElementById("ENDLabelMax");
            var tempLabelAvg = document.getElementById("ENDLabelAvg");
            if(O2Narcotic.checked) {
                tempLabelMax.textContent = "END at max depth ({{ $site->maxDepth }} ft)";
                tempLabelAvg.textContent = "END at average depth ({{ $site->avgDepth }} ft)";
            } else {
                tempLabelMax.textContent = "EAD at max depth ({{ $site->maxDepth }} ft)";
                tempLabelAvg.textContent = "EAD at average depth ({{ $site->avgDepth }} ft)";
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
            const depth = {{ $site->maxDepth }}
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
        function cancelReview() {
            document.getElementById('addReviewButton').style.display = 'block';
            document.getElementById('addReviewForm').style.display = 'none';
        }

        function showReviewForm() {
            document.getElementById('addReviewButton').style.display = 'none';
      script.getElementById('addReviewForm').style.display = 'block';
        }
    </script>
    <script>
        /* Javascript */
        
        //Make sure that the dom is ready
       /* $(function () {
            $("#rateSite").rateYo({
            rating: 0
            });
        });*/

        $(function () {
            
            $("#rateSite").rateYo({
                precision : 0,
                onSet: function (rating, rateYoInstance) {
                    var rateInput = document.getElementById('valueRate');
                    rateInput.value = rating;
                    //alert("Rating is set to: " + rating);
                }
            });
        });

        $(function () {
 
            $("#rateYoReadOnly").rateYo({
                rating: {{ $site->rate != null ? $site->rate : 0 }},
                readOnly: true
            });
        });

    </script>

    {{---Show modal----}}
    @if(session('msg'))
    <script>
        $(document).ready(function() {
            $('#modal-notification').modal('show'); // Show the modal
        });
    </script>
    @endif

    {{---Script to show the description---}}
    <script>
        // Assuming you have the Quill delta saved as a JSON string
       
        <?php
            $decodedString = htmlspecialchars_decode($site->desc, ENT_QUOTES);
            $decodedString1 = htmlspecialchars_decode($site->history, ENT_QUOTES);
            $decodedString2 = htmlspecialchars_decode($site->route, ENT_QUOTES);
            $decodedString3 = htmlspecialchars_decode($site->typicalConditions, ENT_QUOTES);
        ?>
        const deltaString = <?php echo json_encode($decodedString); ?>;
        const deltaString1 = <?php echo json_encode($decodedString1); ?>;
        const deltaString2 = <?php echo json_encode($decodedString2); ?>;
        const deltaString3 = <?php echo json_encode($decodedString3); ?>;

        // Parse the JSON string into a JavaScript object
        const delta = JSON.parse(deltaString);
        const delta1 = JSON.parse(deltaString1);
        const delta2 = JSON.parse(deltaString2);
        const delta3 = JSON.parse(deltaString3);

        // Create a temporary Quill instance to convert the delta to HTML
        const tempQuill = new Quill(document.createElement('div'));
        const tempQuill1 = new Quill(document.createElement('div'));
        const tempQuill2 = new Quill(document.createElement('div'));
        const tempQuill3 = new Quill(document.createElement('div'));

        tempQuill.setContents(delta);
        tempQuill1.setContents(delta1);
        tempQuill2.setContents(delta2);
        tempQuill3.setContents(delta3);

        // Get the HTML representation
        const html = tempQuill.root.innerHTML;
        const html1 = tempQuill1.root.innerHTML;
        const html2 = tempQuill2.root.innerHTML;
        const html3 = tempQuill3.root.innerHTML;

        // Display the HTML wherever you need it
        document.getElementById('desc').innerHTML = html;
        <?php
            if( $site->type == "wreck")
                echo "document.getElementById('history').innerHTML = html1;";
        ?>
        document.getElementById('route').innerHTML = html2;
        document.getElementById('typicalConditions').innerHTML = html3;
    </script>

    <script>
        var gauge2 = Gauge(
            document.getElementById("gauge2"), {
                min: 0,
                max: 10,
                dialStartAngle: 180,
                dialEndAngle: 0,
                value: -1,
                color: function(value) {
                    if(value < 1) {
                    return "#ccdfe5";
                    }else if(value < 3) {
                    return "#aedced";
                    }else if(value < 5) {
                    return "#88d0ea";
                    }else if(value < 7) {
                    return "#43c3ef";
                    }else {
                    return "#03a9f4";
                    }
                }
            }
        );
        gauge2.setValueAnimated({{ $site->level * 2 + 2}}, 2);

    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('alreadyVisited').addEventListener('change', function() {
                document.getElementById('alreadyVisitedHiddenInput').value = document.getElementById('alreadyVisited').checked;  
                document.getElementById('updatedVisited-form').submit();
            });
        });
    </script>

    <script>
        <?php
            function dms_to_dd($degrees, $minutes, $direction) {
                $sign = ($direction === 'N' || $direction === 'E') ? 1 : -1;
                $dd = $degrees + ($minutes * 60)  / 3600;
                return $dd * $sign;
            }

            list($lat_deg, $lat_min, $lat_dir) = sscanf($site->gpsLat, "%d° %f' %c");
            list($lon_deg, $lon_min, $lon_dir) = sscanf($site->gpsLon, "%d° %f' %c");

            $latitude_dd = dms_to_dd($lat_deg, $lat_min, $lat_dir);
            $longitude_dd = dms_to_dd($lon_deg, $lon_min, $lon_dir);
        ?>
        mapboxgl.accessToken = 'pk.eyJ1IjoicHN0cmlrYSIsImEiOiJjbHZsc2p2bXcyY240MmtuMDcydHJzd2UxIn0.KBf79cvk47WseBc9rNu6gQ'; // Replace with your actual access token

        const map = new mapboxgl.Map({
            container: 'map',
            style: 'mapbox://styles/pstrika/clwqz4fds03gv01qo9d4w3g21', // Choose a map style
            //center: [-80.07488399442913, 26.137643513173536], // Set the initial center coordinates
            center: [ {{ $longitude_dd }}, {{ $latitude_dd }}],
            zoom: 12, // Set the initial zoom level
            projection: 'albers'
        });

        
        //add icons
        map.loadImage( '{{ asset('assets') }}/img/icons/marker_reef.png', (error, reef) => {
            if (error) throw error;
            // Continue to the next step...
            map.addImage('icon_reef', reef);
        });
        map.loadImage( '{{ asset('assets') }}/img/icons/marker_wreck.png', (error, wreck) => {
            if (error) throw error;
            // Continue to the next step...
            map.addImage('icon_wreck', wreck);
        });
        map.loadImage( '{{ asset('assets') }}/img/icons/marker_other.png', (error, other) => {
            if (error) throw error;
            // Continue to the next step...
            map.addImage('icon_other', other);
        });
        map.loadImage( '{{ asset('assets') }}/img/icons/marker_reef_this.png', (error, reef) => {
            if (error) throw error;
            // Continue to the next step...
            map.addImage('icon_reef_this', reef);
        });
        map.loadImage( '{{ asset('assets') }}/img/icons/marker_wreck_this.png', (error, wreck) => {
            if (error) throw error;
            // Continue to the next step...
            map.addImage('icon_wreck_this', wreck);
        });
        map.loadImage( '{{ asset('assets') }}/img/icons/marker_other_this.png', (error, other) => {
            if (error) throw error;
            // Continue to the next step...
            map.addImage('icon_other_this', other);
        });


        const sites = {
            'type': 'FeatureCollection',
            'features': [
                <?php
                    
                    $thisSiteId = $site->id;

                    foreach($sites as $site) {
                        list($lat_deg, $lat_min, $lat_dir) = sscanf($site->gpsLat, "%d° %f' %c");
                        list($lon_deg, $lon_min, $lon_dir) = sscanf($site->gpsLon, "%d° %f' %c");

                        $latitude_dd = dms_to_dd($lat_deg, $lat_min, $lat_dir);
                        $longitude_dd = dms_to_dd($lon_deg, $lon_min, $lon_dir);
                
                        $suffixIcon = "";
                        if($thisSiteId == $site->id)
                            $suffixIcon = "_this";

                        echo "{
                            'type': '" . $site->type . "'," .
                                "'properties': {" .
                                    "'name': \"" . $site->name . "\"," .
                                    "'icon': 'icon_" . $site->type . $suffixIcon . "'," .
                                    "'url': '" . $site->id . "'," .
                            "}," .
                            "'geometry': {" .
                                "'type': 'Point'," .
                                "'coordinates': [" . $longitude_dd . "," . $latitude_dd . "]" .
                            "}" .
                        "},";
                    }
                ?>
            ]
        };

        map.on('load', () => {
            // Add a GeoJSON source containing place coordinates and information.
            map.addSource('sites', {
                'type': 'geojson',
                'data': sites
            });

            map.addLayer({
                'id': 'poi-labels',
                'type': 'symbol',
                'source': 'sites',
                
                'layout': {
                    'text-field': ['get', 'name'],
                    'text-variable-anchor': ['top'],
                    'text-allow-overlap' : true,
                    'text-radial-offset': 0.1,
                    'text-justify': 'auto',
                    'text-size': 12,
                    'icon-image': ['get', 'icon'],
                    'icon-size': 0.3,
                    'icon-anchor': 'bottom',
                    'icon-allow-overlap' : true,
                },
                'paint': {
                    'text-color': 'white',
                },
            });

            
        });

        map.on('click', function (e) {
            var features = map.queryRenderedFeatures(e.point, { layers: ['poi-labels'] });

            if (!features.length) {
                return;
            }

            var feature = features[0];
            // Use Feature and put your code
            // Populate the popup and set its coordinates
            // based on the feature found.
            window.location.href = feature.properties.url;
            
        });

        map.on('mousemove', function (e) {
            var features = map.queryRenderedFeatures(e.point, { layers: ['poi-labels'] });
            map.getCanvas().style.cursor = (features.length) ? 'pointer' : '';

        });
    </script>
    @endpush
</x-page-template>

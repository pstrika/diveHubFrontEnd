<x-page-template bodyClass='g-sidenav-show  bg-gray-200' :SEO="$SEO">
    <x-auth.navbars.sidebar activePage="Weather" activeItem="WeatherAR" activeSubitem=""></x-auth.navbars.sidebar>
    
    
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
    <style>
        @keyframes fadeInOut {
            0%, 100% { opacity: 0; }
            50% { opacity: 1; }
        }

        .animate-icon {
            animation-name: fadeInOut;
            animation-duration: 2s;
            animation-iteration-count: infinite;
            animation-timing-function: ease-in-out;
        }

        .comparison-table {
            display: flex;
            flex-wrap: nowrap; /* Prevent columns from wrapping */
        }

        .comparison-table th,
        .comparison-table td {
            flex: 1; /* Distribute available space equally */
            padding: 10px;
            /*border: 1px solid grey;*/
            text-align: center;
            flex-wrap: nowrap;
}

    </style>

    
        <!-- Navbar -->
        <x-auth.navbars.navs.auth pageTitle="Dive Trips"></x-auth.navbars.navs.auth>
        <!-- End Navbar -->
        <div class="container-fluid py-4">

            <div class="d-none" data-color="info" id="sidebarColorDiv"></div>
        
        {{--modal code--}}
        <div class="modal fade" id="modal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="modal-notification" aria-hidden="true">
            <div class="modal-dialog modal-danger modal-dialog-centered modal-" role="document">
                <div class="modal-content">
                    <div class="modal-header text-center">
                        <h6 class="modal-title font-weight-normal" id="modal-title-notification">What is this?</h6>
                        
                    </div>
                    <div class="modal-body">
                        <div class="py-3 text-center">
                        <i class="material-icons h1 text-secondary">
                            help_outline
                        </i>
                        <h4 class="text-gradient text-info text-md mt-4">Diver's Hub uses weather data from waves (period, direction and height) and winds (direction and speed) to predict dive conditions.</h4>
                        <p>Press anywhere outside this dialog to continue</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-header min-height-250 max-height-300 border-radius-xl mt-0 mx-n2" style="background-image: url('/assets/img/illustrations/weather.webp');">
                <span class="mask  bg-gradient-info  opacity-4"></span>
            </div>

            <div class="card p-0 position-relative mt-n7 mx-2 z-index-2 mb-4">
                <div class="p-0 mt-n4 mx-2 ">
                    <div class="border-radius-lg py-3 pe-1" style="float: left;">
                        <h2 class="card-title text-info mx-3 mt-4">{{ ucwords($location) }}</h2>
                        <h4 class="card-category text-info mx-3">Weather Forecast</h4>
                    </div>
                    {{-----------------change location}} --}}
                    <div class="mt-5 mx-5" style="float: right;">
                            
                        <a  href="" >                         
                            <div class="dropdown">
                                <select class="btn bg-info dropdown-toggle w-100 text-white" type="button" id="filterLocation" data-bs-toggle="dropdown" aria-expanded="false">
                                <option value="" selected disabled>Select...</option>
                                    @foreach($allLocations as $thisLocation)
                                        <option value="{{ $thisLocation->location }}">{{ ucwords($thisLocation->location) }}</option>
                                    @endforeach
                                </select>   
                            </div>
                        </a>
                        <p class="text-xs font-weight-bold mb-0 mt-n3">Change location</p>
                    </div>

                    <div class="text-center mt-5 mx-5" style="border: none; float: right;"> <!-- Added text-center here -->
                        @if(!is_null($location) && $deco_unit)
                            <a type="button" class="btn btn-info mt-0" id="switchUnits" href="{{ route('WeatherARImperial') }}/{{ $location }}">Switch to IMPERIAL</a>
                        @elseif(is_null($location) && $deco_unit)
                            <a type="button" class="btn btn-info mt-0" id="switchUnits" href="{{ route('WeatherARImperial') }}">Switch to IMPERIAL</a>
                        @elseif(!is_null($location) && !$deco_unit)
                            <a type="button" class="btn btn-info mt-0" id="switchUnits" href="{{ route('WeatherARMetric') }}/{{ $location }}">Switch to Metric</a>
                        @else
                            <a type="button" class="btn btn-info mt-0" id="switchUnits" href="{{ route('WeatherARMetric') }}">Switch to Metric</a>
                        @endif
                        
                    </div>

                
                        <div style="clear: both;"></div>
                </div>

                
            </div>    
            
            <div class="row">

                {{-- Card location --}}
                <div class="col-md-3">
                        <div class="card p-0 position-relative mt-3 mx-n2 z-index-2 mb-4">
                            <div class="card-header p-0 mt-n4 mx-3">
                                <div class="bg-gradient-info min-height-100 shadow-info border-radius-xl py-3 pe-1"> 
                                    {{-- <img src="{{ asset('assets') }}/img/Florida1.png" height="200px" alt="img-blur-shadow" class=" border-radius-lg min-heigth-10 mt-n3 position-relative"> --}}
                                    <img src="{{ asset('assets') }}/img/Argentina1.png" height="200px" alt="img-blur-shadow" class=" border-radius-lg min-heigth-10 mt-n3 position-relative">
                                    {{--<div class="page-header min-height-250 max-height-250 border-radius-xl mt-0 mx-0" --}}
                                    <a href="/WeatherAR/ushuaia/" class="position-absolute text-sm material-icons text-black {{ ($location == "ushuaia" ? "animate-icon" : "") }}" style="top:160px; left:70px;">circle</a>
                                    <a href="/WeatherAR/puerto madryn/"class="position-absolute text-sm material-icons text-black {{ ($location == "puerto madryn" ? "animate-icon" : "") }}" style="top:37px; left:101px;">circle</a>
                                    <a href="/WeatherAR/las grutas/"class="position-absolute text-sm material-icons text-black {{ ($location == "las grutas" ? "animate-icon" : "") }}" style="top:16px; left:103px;">circle</a>
                                    <a href="/WeatherAR/mar del plat/"class="position-absolute text-sm material-icons text-black {{ ($location == "mar del plata" ? "animate-icon" : "") }}" style="top:-10px; left:150px;">circle</a>
                                    
                                        
                                    
                                </div>
                            </div>
                            <div class="card-body">
                                <h6 class="mb-0 "> Change location (click on map)</h6>
                            
                            </div>
                                    
                        </div>
                    </div>
                {{-----------------------------}}

                {{-- Marine Current NOW --}}
                @if( $currentLocation->buoy != null)
                <div class="col-md-3">             
                    <div class="card p-0 position-relative mt-3 mx-n2 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h2 class="card-title text-white mx-4" style="font-size: clamp(2rem, 3vw, 2rem);">Marine Current</h2>
                                <p class="text-white text-xs mt-n2 mx-4"><b>Live data</b></p>
                                
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive" style="overflow: hidden;">
                                <table class="table align-items-center mb-0">
                                    <tbody>
                                        <tr>
                                            <td class="text-center">
                                                <div class="image-container" style="position: relative;">
                                                    <img src="{{ asset('assets') }}/img/icons/icons_compass_bg.png" height="200px" alt="img-blur-shadow" class=" border-radius-lg min-heigth-10 mt-0" style="width: 100%; height: auto;">
                                                    <img src="{{ asset('assets') }}/img/icons/icons_compass_needle.png" height="200px" alt="img-blur-shadow" class=" border-radius-lg min-heigth-10 mt-0" style="position: absolute; top: 0; left: 0; width: 100%; height: auto; transform: rotate({{ $currentLocation->dir }}deg);">
                                                </div>
                                            </td>
                                            <td style="width: 20%;">
                                                <div class="table-responsive">
                                                    <table class="table align-items-center mb-0">
                                                        <tbody>
                                                            <tr>
                                                                <td><div class="col-xxs text-left">Direction: <b>{{ $currentLocation->dir}}º</b></div></td>
                                                            </tr>
                                                            <tr>
                                                                <td><div class="text-left">Speed: <b>{{ $currentLocation->speed}} knots</b></div></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </td>
                    
                                        </tr>
                                    </tbody>   
                                   
                                </table>
                                <table>
                                    <tr>
                                        <td>
                                            <div class="d-flex ">
                                                <i class="material-icons text-sm my-auto me-1">schedule</i>
                                                <p class="mb-0 text-sm">Last update: {{ $currentLocation->updatetime }} </p>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            
                        </div> 
                    </div>
                </div>
                @endif
                {{--------------------------}}

                {{-- Card Dive Conditions --}}
                <div class="col-md-{{ $currentLocation->buoy != null ? 6 : 9}}">             
                    <div class="card p-0 position-relative mt-3 mx-n2 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h2 class="card-title text-white mx-4">Ocean Conditions</h2>
                                <a href="#" onclick="showModal();"><p class="text-white text-xs mt-n2 mx-4 text-decoration-underline"><b>What is this?</b></p></a>
                                <div class="table-responsive"></div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table align-items-center mb-0">
                                    
                                    <tbody>
                                        <tr> {{--Day name--}}
                                            <td class="text-uppercase text-secondary text-md font-weight-bolder opacity-7 text-center" style="border: none;">  </td>
                                            @foreach($weathers as $weather)
                                                @php
                                                    $date = new DateTime($weather->date);
                                                    $dateDayName = $date->format('l-d');
                                                @endphp
                                                <td class="align-middle text-center text-md"><b>{{ $dateDayName }}</b></td>     
                                            @endforeach
                                        </tr>

                                        <tr>
                                            <td class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-left" style="border: none;">Morning</td>
                                            @foreach($weathers as $weather)
                                                @if($weather->conditionsAM_text == "Poor")
                                                    <td class="align-middle text-center text-sm"> <span class="badge badge-lg badge-warning">  Poor  </span> </td>
                                                @elseif($weather->conditionsAM_text == "No Dive")
                                                    <td class="align-middle text-center text-sm"> <span class="badge badge-lg badge-danger"> No dive </span> </td>
                                                @elseif($weather->conditionsAM_text == "Average")
                                                    <td class="align-middle text-center text-sm"> <span class="badge badge-lg badge-secondary"> Average </span> </td>
                                                @elseif($weather->conditionsAM_text == "Perfect")
                                                    <td class="align-middle text-center text-sm"> <span class="badge badge-lg badge-success"> Perfect </span> </td>
                                                @else
                                                    <td class="align-middle text-center text-sm"> <span class="badge badge-lg badge-info">  Good  </span> </td>
                                                @endif    
                                            @endforeach
                                        </tr>

                                        <tr>
                                            <td class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-left" style="border: none;">Afternoon</td>
                                            @foreach($weathers as $weather)
                                                @if($weather->conditionsPM_text == "Poor")
                                                    <td class="align-middle text-center text-sm"> <span class="badge badge-lg badge-warning">  Poor  </span> </td>
                                                @elseif($weather->conditionsPM_text == "No Dive")
                                                    <td class="align-middle text-center text-sm"> <span class="badge badge-lg badge-danger"> No dive </span> </td>
                                                @elseif($weather->conditionsPM_text == "Average")
                                                    <td class="align-middle text-center text-sm"> <span class="badge badge-lg badge-secondary"> Average </span> </td>
                                                @elseif($weather->conditionsPM_text == "Perfect")
                                                    <td class="align-middle text-center text-sm"> <span class="badge badge-lg badge-success"> Perfect </span> </td>
                                                @else
                                                    <td class="align-middle text-center text-sm"> <span class="badge badge-lg badge-info">  Good  </span> </td>
                                                @endif    
                                            @endforeach
                                        </tr>
                                            

                                    </tbody>
                                </table>
                            </div>
                            
                        </div> 
                    </div>
                </div>
                {{--------------------------}}

                

                {{-- Card live cam --}}
                @if($location == "fort lauderdale")                    
                    <div class="col-md-4">
                        <div class="card p-0 position-relative mt-3 mx-n2 z-index-2 mb-4">
                            <div class="card-header p-0 mt-n4 mx-3">
                                <iframe class="img-fluid border-radius-lg" allow-same-origin="" allow-scripts="" allowfullscreen="" alt="EarthCam Video Player Embed" autoplay="" frameborder="0" height="450" id="iframe" marginheight="0" marginwidth="0" scrolling="no" src="//www.earthcam.com/js/video/embed.php?type=h264&amp;vid=windjammerHD2.flv&amp;w=auto&amp;company=Windjammer&amp;timezone=America/New_York&amp;metar=KFLL&amp;ecn=1&amp;requested_version=current" style="top:0;left:0;width:10px;min-width:100%;*width:100%;height:200px;" width="800"></iframe>
                            </div>
                            <div class="card-body">
                                <h6 class="mb-1 mt-n3"> live web cam</h6>
                                <div class="d-flex ">
                                    <i class="material-icons text-sm my-auto me-1">schedule</i>
                                    <p class="mb-0 text-sm">Last update: now (source EarthCam) </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif($location == "pompano beach")
                    <div class="col-md-4">
                        <div class="card p-0 position-relative mt-3 mx-n2 z-index-2 mb-4">
                            <div class="card-header p-0 mt-n4 mx-3">
                                <iframe class="img-fluid shadow border-radius-lg" src="https://player.brownrice.com/embed/copbfl1" height="100%" width="100%" autoplay="" allowfullscreen mozallowfullscreen style="top:0;left:0;height:200px;" scrolling="no"></iframe>
                            </div>
                            <div class="card-body">
                                <h6 class="mb-0 "> live web cam</h6>  
                                <div class="d-flex ">
                                    <i class="material-icons text-sm my-auto me-1">schedule</i>
                                    <p class="mb-0 text-sm">Last update: now (source City of Pompano Beach) </p>
                                </div>
                            </div>
                        </div>
                    </div>              
                @elseif($location == "west palm beach")
                    <div class="col-md-4">
                        <div class="card p-0 position-relative mt-3 mx-n2 z-index-2 mb-4">
                            <div class="card-header p-0 mt-n4 mx-3">
                                <iframe class="img-fluid shadow border-radius-lg" id="main_content" name="main_content" src="https://video-monitoring.com/beachcams/palmbeachmarriott/stream.htm" width="960" height="540" allowfullscreen="" autoplay="true" style="top:0;left:0;width:10px;min-width:100%;*width:100%;height:200px;"></iframe>
                            </div>
                            <div class="card-body">
                                    <h6 class="mb-0 "> live web cam</h6>
                                    
                                    <div class="d-flex ">
                                        <i class="material-icons text-sm my-auto me-1">schedule</i>
                                        <p class="mb-0 text-sm">Last update: now (TBD) </p>
                                    </div>
                                </div>
                        </div>
                    </div>
                @elseif($location == "miami beach")
                    <div class="col-md-4">
                        <div class="card p-0 position-relative mt-3 mx-n2 z-index-2 mb-4">
                            <div class="card-header p-0 mt-n4 mx-3">
                            {{--<iframe loading="lazy" src="https://iframe.dacast.com/live/b5a8e966-0b7f-13a8-9ad4-5637cfb90a9f/8e2f7f51-4653-43e0-7e8e-7e213959bb2b" width="100%" height="100%" allowfullscreen="" style="position:absolute;top:0;left:0;"></iframe>--}}
                                <iframe class="img-fluid shadow border-radius-lg" src="https://iframe.dacast.com/live/b5a8e966-0b7f-13a8-9ad4-5637cfb90a9f/8e2f7f51-4653-43e0-7e8e-7e213959bb2b" height="100%" width="100%" autoplay="" allow="autoplay;" allowfullscreen mozallowfullscreen style="top:0;left:0;height:200px;" scrolling="no"></iframe>
                                
                                
                                    
                            </div>
                            <div class="card-body">
                                <h6 class="mb-0 "> live web cam</h6>  
                                <div class="d-flex ">
                                    <i class="material-icons text-sm my-auto me-1">schedule</i>
                                    <p class="mb-0 text-sm">Last update: now (source 76th Str, Miami Beach) </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @elseif($location == "key west")
                    <div class="col-md-4">
                        <div class="card p-0 position-relative mt-3 mx-n2 z-index-2 mb-4">
                            <div class="card-header p-0 mt-n4 mx-3">
                            
                                <iframe class="img-fluid shadow border-radius-lg" src="https://relay.ozolio.com/pub.api?cmd=embed&amp;oid=EMB_RKNO000004F8" height="100%" width="100%" autoplay="" allow="autoplay;" allowfullscreen mozallowfullscreen style="top:0;left:0;height:200px;" scrolling="no"></iframe>
                                    
                                
                                    
                            </div>
                            <div class="card-body">
                                <h6 class="mb-0 "> live web cam</h6>  
                                <div class="d-flex ">
                                    <i class="material-icons text-sm my-auto me-1">schedule</i>
                                    <p class="mb-0 text-sm">Last update: now (source 76th Str, Miami Beach) </p>
                                </div>
                            </div>
                        </div>
                    </div> 

                    
                @endif
                {{-------------------------------}}


                {{-- Card waves --}}
                @if($location == "fort lauderdale" or $location == "pompano beach" or $location == "west palm beach" or $location == "miami beach" or $location == "key west")
                    <div class="col-md-4">
                @else
                    <div class="col-md-6">
                @endif
                        <div class="card p-0 position-relative mt-3 mx-n2 z-index-2 mb-4">
                            <div class="card-header p-0 mt-n4 mx-3">
                                <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1"> 
                                    <canvas id="wavesChart" class="chart-canvas border-radius-lg" height="120px"></canvas>
                                </div>
                            </div>
                            <div class="card-body">
                                <h6 class="mb-0 "> waves {{ $deco_unit ? '(meters)' : '(ft)'}}</h6>
                                
                                <div class="d-flex ">
                                    <i class="material-icons text-sm my-auto me-1">schedule</i>
                                    <p class="mb-0 text-sm">Last update: {{ $weathers[0]->_dateAdded }} </p>
                                </div>
                            </div>
                                    
                        </div>
                    </div>
                {{-----------------------------}}

                

                {{-- Card winds--}}
                @if($location == "fort lauderdale" or $location == "pompano beach" or $location == "west palm beach" or $location == "miami beach" or $location == "key west")
                    <div class="col-md-4">
                @else
                    <div class="col-md-6">
                @endif
                        <div class="card p-0 position-relative mt-3 mx-n2 z-index-2 mb-4">
                            <div class="card-header p-0 mt-n4 mx-3">
                                <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1"> 
                                    <canvas id="windChart" class="chart-canvas" height="120px"></canvas>
                                </div>
                            </div>
                            <div class="card-body">
                                    <h6 class="mb-0 "> winds {{ $deco_unit ? '(km/h)' : '(mph)'}}</h6>
                                    <div class="d-flex ">
                                        <i class="material-icons text-sm my-auto me-1">schedule</i>
                                        <p class="mb-0 text-sm" >Last update: {{ $weathers[0]->_dateAdded }} </p>
                                    </div>
                            </div>   
                        </div>
                    </div>
                {{---------}}
                
                {{-- Card Extended forecast--}}
                <div class="col-md-12">             
                    <div class="card p-0 position-relative mt-3 mx-n2 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h2 class="card-title text-white mx-4">Extended Forecast</h4>
                                <div class="table-responsive"></div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="comparison-table align-items-center mb-0">    
                                    <tbody>

                                        
                                        <tr> {{--Day name--}}
                                            <td class="text-uppercase text-secondary text-md font-weight-bolder opacity-7 text-center" style="border: none;">  </td>
                                            @foreach($weathers as $weather)
                                                @php
                                                    $date = new DateTime($weather->date);
                                                    $dateDayName = $date->format('l-d');
                                                @endphp
                                                <td class="align-middle text-center text-md"><b>{{ $dateDayName }}</b></td>     
                                            @endforeach
                                        </tr>
                                        
                                        <tr><td colspan="100%" class="text-uppercase text-white text-sm font-weight-bolder opacity-7 text-center bg-gradient-info" style="border: none;">weather</td> </tr>
                                        
                                        <tr> {{--Conditions Icon--}}
                                            <td class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" style="border: none;">  </td>
                                            @foreach($weathers as $weather)
                                                <td class="align-middle text-center text-sm"> <img src="{{ $weather->conditions_icon }}" alt="{{ $weather->conditions_text }}"> </td>    
                                            @endforeach
                                        </tr>
                                        
                                        <tr> {{--Conditions text--}}
                                            <td class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" style="border: none;">  </td>
                                            @foreach($weathers as $weather)
                                                <td class="align-middle text-center text-sm text-wrap"> {{ $weather->conditions_text }} </td>
                                            @endforeach
                                        </tr>

                                        <tr> {{--Temp--}}
                                            <td class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-left" style="border: none;">TEMP {{ $deco_unit ? '(ºC)' : '(ºF)' }}</td>
                                            @foreach($weathers as $weather)
                                                <td class="align-middle text-center text-sm"><b>{{ $deco_unit ? round(($weather->mintemp_f-32) * 5 /9) : round($weather->mintemp_f) }}° - {{ $deco_unit ? round(($weather->maxtemp_f-32) * 5 /9) : round($weather->maxtemp_f) }}° </b></td> 
                                            @endforeach
                                        </tr>
                                        
                                        <tr> {{--Humidity--}}
                                            <td class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-left" style="border: none;">HUMIDITY (%)</td>
                                            @foreach($weathers as $weather)
                                                <td class="align-middle text-center text-sm"><b> {{ $weather->avghumidity }}%</b></td>
                                            @endforeach
                                        </tr>
                                        
                                        <tr><td colspan="100%" class="text-uppercase text-white text-sm font-weight-bolder opacity-7 text-center bg-gradient-info" style="border: none;">marine</td> </tr>

                                        <tr> {{--AM/PM--}}
                                            <td> </td>
                                            @foreach($weathers as $weather)
                                                <td><div class="container"><div class="row"><div class="col-xxs text-center"><span>AM</span></div><div class="col-sm text-center"><span>PM</span></div></div></div></td>
                                            @endforeach
                                            
                                        </tr>

                                        <tr> {{--waves--}}
                                            <td class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-left" style="border: none;">waves {{ $deco_unit ? '(metres)' : '(ft)' }}</td>
                                            @foreach($weathers as $weather)
                                                <td><div class="container"><div class="row"><div class="col-xxs text-center"><span>{{ $deco_unit ? round($weather->swell_height_AM * 0.3048,1) : round($weather->swell_height_AM) }}</span></div><div class="col-sm text-center"><span>{{ $deco_unit ? round($weather->swell_height_PM * 0.3048,1) : round($weather->swell_height_PM) }}</span></div></div></div></td>    
                                            @endforeach
                                        </tr>

                                        <tr> {{--Period--}}
                                            <td class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-left" style="border: none;">period (secs)</td>
                                            @foreach($weathers as $weather)
                                                <td><div class="container"><div class="row"><div class="col-xxs text-center"><span>{{ round($weather->swell_period_AM) }}</span></div><div class="col-sm text-center"><span>{{ round($weather->swell_period_PM) }}</span></div></div></div></td>    
                                            @endforeach
                                        </tr>
                                        
                                        <tr> {{--winds--}}
                                            <td class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-left" style="border: none;">wind {{ $deco_unit ? '(km/h)' : '(mph)'}}</td>
                                            @foreach($weathers as $weather)
                                                @php
                                                    $compassPoints = [
                                                        'N' => 0,
                                                        'NNE' => 22,
                                                        'NE' => 45,
                                                        'ENE' => 67,
                                                        'E' => 90,
                                                        'ESE' => 112,
                                                        'SE' => 135,
                                                        'SSE' => 157,
                                                        'S' => 180,
                                                        'SSW' => 202,
                                                        'SW' => 225,
                                                        'WSW' => 247,
                                                        'W' => 270,
                                                        'WNW' => 292,
                                                        'NW' => 315,
                                                        'NNW' => 337
                                                    ];
                                                    $rotationAM = $compassPoints[@$weather->wind_dir_AM];
                                                    $rotationPM = $compassPoints[@$weather->wind_dir_PM];

                                                @endphp
                                                {{--<td> <i class="material-icons rotate-icon" style="transform: rotate(45deg);">arrow_upward</i></td> --}}
                                                {{--<td><div class="container"><div class="row"><div class="col-sm text-center"><span>({{ $weather->wind_dir_AM }}) {{ $weather->wind_speed_AM }}</span></div><div class="col-sm text-center"><span>({{ $weather->wind_dir_PM }}) {{ $weather->wind_speed_PM }}</span></div></div></div></td>--}}
                                                <td><div class="container"><div class="row"><div class="col-xs text-center"><span>
                                                <i class="material-icons rotate-icon" style="transform: rotate({{ $rotationAM + 180 }}deg);">navigation</i>{{ $deco_unit ? round($weather->wind_speed_AM * 1.60934) : round($weather->wind_speed_AM) }}</span></div><div class="col-sm text-center"><span>
                                                <i class="material-icons rotate-icon" style="transform: rotate({{ $rotationPM +180  }}deg);">navigation</i>{{ $deco_unit ? round($weather->wind_speed_PM * 1.60934) : round($weather->wind_speed_PM) }}</span></div></div></div></td>    
                                            @endforeach
                                        </tr>

                                        <tr> {{--Water temp--}}
                                            <td class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-left" style="border: none;">water temp {{ $deco_unit ? '(ºC)' : '(ºF)' }} </td>
                                            @foreach($weathers as $weather)
                                                <td><div class="container"><div class="row"><div class="col-xxs text-center"><span>{{ $deco_unit ? round(($weather->water_temp_AM-32)*5/9) : round($weather->water_temp_AM) }}°</span></div><div class="col-sm text-center"><span>{{ $deco_unit ? round(($weather->water_temp_PM-32)*5/9): round($weather->water_temp_PM) }}°</span></div></div></div></td>    
                                            @endforeach
                                        </tr>
                                        
                                        <tr> {{--High tides--}}
                                            <td class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-left" style="border: none;">high tide</td>
                                            @php
                                                Log::debug('paso por aca');
                                                foreach($weathers as $weather) {     

                                                    Log::debug('paso por aca High Tides');
                                                    $jsonString = $weather->tides;
                                                    Log::debug('jsonString: ' . $jsonString . 'strLen is ' . str(strlen($jsonString)));
                                                    if (strlen($jsonString) < 5)  {
                                                        Log::debug("json string is empty");
                                                        $highTides['AM'] = "NA";
                                                        $highTides['PM'] = "NA";
                                                        $lowTides['AM'] = "NA";
                                                        $lowTides['PM'] = "NA";
                                                        
                                                    } else {
                                                    
                                                        $tides = json_decode($jsonString, true);

                                                        // Initialize arrays to hold the high and low tides for AM and PM
                                                        $highTides = ['AM' => [], 'PM' => []];
                                                        $lowTides = ['AM' => [], 'PM' => []];
                                                        // Init vars in case there's no tide
                                                        $highTides['AM'] = "none";
                                                        $highTides['PM'] = "none";
                                                        $lowTides['AM'] = "none";
                                                        $lowTides['PM'] = "none";

                                                        // Iterate through the array of tides
                                                        foreach ($tides as $tide) {
                                                            Log::debug($tide);
                                                            // Extract the time and AM/PM part
                                                            $time = strtotime($tide['tide_time']);
                                                            $suffix = date('A', $time);

                                                            
                                                            // Sort into high and low tides for AM and PM
                                                            if ($tide['tide_type'] == 'HIGH') {
                                                                $highTides[$suffix] = substr($tide['tide_time'], -5);
                                                            } elseif ($tide['tide_type'] == 'LOW') {
                                                                $lowTides[$suffix] = substr($tide['tide_time'], -5);
                                                            }
                                                        }
                                                    }
                                                    echo '<td><div class="container"><div class="row"><div class="col-xxs text-center"><span>' . $highTides['AM'] . '</span></div><div class="col-sm text-center"><span>' . $highTides['PM'] . '</span></div></div></div></td>';
                                                }
                                            @endphp
                                        </tr>

                                        <tr> {{--Low tides--}}
                                            <td class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-left" style="border: none;">low tide</td>
                                            @php
                                                foreach($weathers as $weather) {     
                                                    Log::debug('paso por aca Low Tides');
                                                    $jsonString = $weather->tides;
                                                    Log::debug('jsonString: ' . $jsonString . 'strLen is ' . str(strlen($jsonString)));
                                                    if (strlen($jsonString) < 5)  {
                                                        Log::debug("json string is empty");
                                                        $highTides['AM'] = "NA";
                                                        $highTides['PM'] = "NA";
                                                        $lowTides['AM'] = "NA";
                                                        $lowTides['PM'] = "NA";
                                                        
                                                    } else {
                                                    
                                                        $tides = json_decode($jsonString, true);

                                                        // Initialize arrays to hold the high and low tides for AM and PM
                                                        $highTides = ['AM' => [], 'PM' => []];
                                                        $lowTides = ['AM' => [], 'PM' => []];
                                                        // Init vars in case there's no tide
                                                        $highTides['AM'] = "none";
                                                        $highTides['PM'] = "none";
                                                        $lowTides['AM'] = "none";
                                                        $lowTides['PM'] = "none";

                                                        // Iterate through the array of tides
                                                        foreach ($tides as $tide) {
                                                            // Extract the time and AM/PM part
                                                            $time = strtotime($tide['tide_time']);
                                                            $suffix = date('A', $time);
                                                            
                                                            // Sort into high and low tides for AM and PM
                                                            if ($tide['tide_type'] == 'HIGH') {
                                                                $highTides[$suffix] = substr($tide['tide_time'], -5);
                                                            } elseif ($tide['tide_type'] == 'LOW') {
                                                                $lowTides[$suffix] = substr($tide['tide_time'], -5);
                                                            }
                                                        }
                                                    }
                                                    echo '<td><div class="container"><div class="row"><div class="col-xxs text-center"><span>' . $lowTides['AM'] . '</span></div><div class="col-sm text-center"><span>' . $lowTides['PM'] . '</span></div></div></div></td>';
                                                }
                                            @endphp
                                        </tr>
                                    
                                        <tr><td colspan="100%" class="text-uppercase text-white text-sm font-weight-bolder opacity-7 text-center bg-gradient-info" style="border: none;">astrological</td> </tr>
                                    
                                        <tr> {{--Sunrise--}}
                                            <td class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-left" style="border: none;">SUNRISE</td>
                                            <?php
                                            foreach($weathers as $weather) {
                                                $dateTime = DateTime::createFromFormat('h:i A', $weather->sunrise);
                                                $time24H = $dateTime->format('H:i');
                                                echo '<td class="align-middle text-center text-md">' . $time24H . '</td>';
                                            }
                                            ?>
                                        </tr>

                                        <tr> {{--Sunset--}}
                                            <td class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-left" style="border: none;">SUNSET</td>
                                            <?php
                                            foreach($weathers as $weather) {
                                                $dateTime = DateTime::createFromFormat('h:i A', $weather->sunset);
                                                $time24H = $dateTime->format('H:i');
                                                echo '<td class="align-middle text-center text-md">' . $time24H . '</td>';
                                            }
                                            ?>
                                        </tr>

                                        
                                    </tbody>
    
                                </table>
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

    <script src="../../assets/js/plugins/chartjs.min.js"></script>
    <script src="{{ asset('assets') }}/js/plugins/jquery-3.6.0.min.js" type="text/javascript"></script>
    
    <script>
        function showModal() {
            $('#modal').modal('show'); // Show the modal
        };
    </script>

    <script>
        var ctx = document.getElementById('wavesChart').getContext('2d');
        var wavesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [
                    @foreach($weathers as $weather)
                        @php
                            $date = new DateTime($weather->date);
                            $dateDayName = $date->format('D-d');
                        
                            echo "'" . $dateDayName . "',";
                        @endphp
                    @endforeach
                ],
                datasets: [{
                    label: 'waves',
                    data: [
                        @foreach($weathers as $weather)
                            @php
                                
                                if ( (int)$weather->swell_height_AM > (int)$weather->swell_height_PM)
                                    echo "'" . ($deco_unit ? ($weather->swell_height_AM * 0.3048) : $weather->swell_height_AM) . "',";
                                else
                                    echo "'" . ($deco_unit ? ($weather->swell_height_PM * 0.3048) : $weather->swell_height_PM) . "',";
                            @endphp
                        @endforeach    
                    ],
                    tension: 0,
                    borderWidth: 0,
                    pointRadius: 5,
                    pointBackgroundColor: "rgba(255, 255, 255, .8)",
                    pointBorderColor: "transparent",
                    borderColor: "rgba(255, 255, 255, .8)",
                    borderColor: "rgba(255, 255, 255, .8)",
                    borderWidth: 40,
                    backgroundColor: "transparent",
                    fill: true,
                    yAxisID: 'ywaves',
                    maxBarThickness: 20,
                    
                    },
                    
                ]

            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false,
                    }
                },
                scales: {
                    ywaves: {
                        beginAtZero: true,
                        type: 'linear',
                        display: 'true',
                        position: 'left',
                        title: { 
                            text: 'waves height (ft)',
                            display: false,
                        },
                        grid: {
                            drawBorder: false,
                            display: true,
                            drawOnChartArea: true,
                            drawTicks: false,
                            borderDash: [5, 5],
                            color: 'rgba(255, 255, 255, .2)',
                        },
                        ticks: {
                            display: true,
                            padding: 10,
                            color: '#f8f9fa',
                            font: {
                                size: 14,
                                weight: 300,
                                family: "Roboto",
                                style: 'normal',
                                lineHeight: 2
                            },
                        }

                    },

                    x: {
                        grid: {
                            drawBorder: false,
                            display: false,
                            drawOnChartArea: false,
                            drawTicks: false,
                            borderDash: [5, 5]
                        },
                        ticks: {
                            display: true,
                            color: '#f8f9fa',
                            padding: 10,
                            font: {
                                size: 14,
                                weight: 300,
                                family: "Roboto",
                                style: 'normal',
                                lineHeight: 2
                            },
                        }
                    },
                    

                }
            }
        });
    </script>

<script>
        var ctx = document.getElementById('windChart').getContext('2d');
        var windChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [
                    @foreach($weathers as $weather)
                        @php
                            $date = new DateTime($weather->date);
                            $dateDayName = $date->format('D-d');
                        
                            echo "'" . $dateDayName . "',";
                        @endphp
                    @endforeach
                ],
                datasets: [{
                    label: 'winds',
                    data: [
                        @foreach($weathers as $weather)
                            {{ $deco_unit ? $weather->maxwind_mph * 1.60934 : $weather->maxwind_mph }},
                        @endforeach
                    ],
                    tension: 0,
                    borderWidth: 0,
                    pointRadius: 5,
                    pointBackgroundColor: "rgba(255, 255, 255, .8)",
                    pointBorderColor: "transparent",
                    borderColor: "rgba(255, 255, 255, .8)",
                    borderColor: "rgba(255, 255, 255, .8)",
                    borderWidth: 40,
                    backgroundColor: "transparent",
                    fill: true,
                    yAxisID: 'ywaves',
                    maxBarThickness: 20,
                    
                    },
                    
                ]

            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false,
                    }
                },
                scales: {
                    ywaves: {
                        beginAtZero: true,
                        type: 'linear',
                        display: 'true',
                        position: 'left',
                        title: { 
                            text: 'waves height (ft)',
                            display: false,
                        },
                        grid: {
                            drawBorder: false,
                            display: true,
                            drawOnChartArea: true,
                            drawTicks: false,
                            borderDash: [5, 5],
                            color: 'rgba(255, 255, 255, .2)',
                        },
                        ticks: {
                            display: true,
                            padding: 10,
                            color: '#f8f9fa',
                            font: {
                                size: 14,
                                weight: 300,
                                family: "Roboto",
                                style: 'normal',
                                lineHeight: 2
                            },
                        }

                    },

                    x: {
                        grid: {
                            drawBorder: false,
                            display: false,
                            drawOnChartArea: false,
                            drawTicks: false,
                            borderDash: [5, 5]
                        },
                        ticks: {
                            display: true,
                            color: '#f8f9fa',
                            padding: 10,
                            font: {
                                size: 14,
                                weight: 300,
                                family: "Roboto",
                                style: 'normal',
                                lineHeight: 2
                            },
                        }
                    },
                    

                }
            }
        });
    </script>


    {{--Handler for location--}}
    <script>
    document.getElementById('filterLocation').addEventListener('change', function() {
        var location = this.value;
        var url = '/WeatherAR/' + location;
        window.location.href = url;
    });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            sidebarColor(document.getElementById("sidebarColorDiv")); // Execute the sidebarColor function once the HTML is loaded
        });
    </script>
    @endpush
</x-page-template>

<x-page-template bodyClass='g-sidenav-show  bg-gray-200'>
    <x-auth.navbars.sidebar activePage="weather" activeItem="weather" activeSubitem=""></x-auth.navbars.sidebar>
    
    
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <!-- Navbar -->
        <x-auth.navbars.navs.auth pageTitle="Dive Trips"></x-auth.navbars.navs.auth>
        <!-- End Navbar -->
        <div class="container-fluid py-4">
            
        <div class="page-header min-height-250 max-height-300 border-radius-xl mt-4 mx-0" style="background-image: url('/assets/img/illustrations/weather.jpeg');">
                <span class="mask  bg-gradient-info  opacity-4"></span>
            </div>

            <div class="card p-0 position-relative mt-n7 mx-3 z-index-2 mb-4">
                <div class="p-0 mt-n4 mx-2 ">
                    <div class="border-radius-lg py-3 pe-1" style="float: left;">
                        <h2 class="card-title text-info mx-3 mt-4">{{ $location }}</h2>
                        <h4 class="card-category text-info mx-3">Weather Forecast</h4>
                    </div>
                </div>
            </div>    
            
            <div class="row">
                {{-- Card Dive Conditions --}}
                <div class="col-md-12">             
                    <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h2 class="card-title text-white mx-4">Dive Conditions</h4>
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

                {{-- Card waves --}}
                @if($location == "fort lauderdale" or $location == "pompano beach" or $location == "west palm beach")
                    <div class="col-md-4">
                @else
                    <div class="col-md-6">
                @endif
                        <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                            <div class="card-header p-0 mt-n4 mx-3">
                                <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1"> 
                                    <canvas id="wavesChart" class="chart-canvas border-radius-lg" height="120px"></canvas>
                                </div>
                            </div>
                            <div class="card-body">
                                <h6 class="mb-0 "> waves (ft)</h6>
                                
                                <div class="d-flex ">
                                    <i class="material-icons text-sm my-auto me-1">schedule</i>
                                    <p class="mb-0 text-sm">Last update: {{ $weathers[0]->_dateAdded }} </p>
                                </div>
                            </div>
                                    
                        </div>
                    </div>
                {{-----------------------------}}

                {{-- Card live cam --}}
                @if($location == "fort lauderdale")                    
                    <div class="col-md-4">
                        <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
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
                        <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
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
                        <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
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
                @endif
                {{-------------------------------}}

                {{-- Card winds--}}
                @if($location == "fort lauderdale" or $location == "pompano beach" or $location == "west palm beach")
                    <div class="col-md-4">
                @else
                    <div class="col-md-6">
                @endif
                        <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                            <div class="card-header p-0 mt-n4 mx-3">
                                <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1"> 
                                    <canvas id="windChart" class="chart-canvas" height="120px"></canvas>
                                </div>
                            </div>
                            <div class="card-body">
                                    <h6 class="mb-0 "> winds (mph)</h6>
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
                    <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h2 class="card-title text-white mx-4">Extended Forecast</h4>
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
                                            <td class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-left" style="border: none;">TEMP (f)</td>
                                            @foreach($weathers as $weather)
                                                <td class="align-middle text-center text-sm"><b>{{ round($weather->mintemp_f) }}° - {{ round($weather->maxtemp_f) }}° </b></td> 
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
                                            <td><div class="container"><div class="row"><div class="col-sm text-center"><span>AM</span></div><div class="col-sm text-center"><span>PM</span></div></div></div></td>
                                            <td><div class="container"><div class="row"><div class="col-sm text-center"><span>AM</span></div><div class="col-sm text-center"><span>PM</span></div></div></div></td>
                                            <td><div class="container"><div class="row"><div class="col-sm text-center"><span>AM</span></div><div class="col-sm text-center"><span>PM</span></div></div></div></td>
                                            <td><div class="container"><div class="row"><div class="col-sm text-center"><span>AM</span></div><div class="col-sm text-center"><span>PM</span></div></div></div></td>
                                            <td><div class="container"><div class="row"><div class="col-sm text-center"><span>AM</span></div><div class="col-sm text-center"><span>PM</span></div></div></div></td>
                                        </tr>

                                        <tr> {{--waves--}}
                                            <td class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-left" style="border: none;">waves (ft)</td>
                                            @foreach($weathers as $weather)
                                                <td><div class="container"><div class="row"><div class="col-sm text-center"><span>{{ $weather->swell_height_AM }}</span></div><div class="col-sm text-center"><span>{{ $weather->swell_height_PM }}</span></div></div></div></td>    
                                            @endforeach
                                        </tr>

                                        <tr> {{--Period--}}
                                            <td class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-left" style="border: none;">period (secs)</td>
                                            @foreach($weathers as $weather)
                                                <td><div class="container"><div class="row"><div class="col-sm text-center"><span>{{ $weather->swell_period_AM }}</span></div><div class="col-sm text-center"><span>{{ $weather->swell_period_PM }}</span></div></div></div></td>    
                                            @endforeach
                                        </tr>
                                        
                                        <tr> {{--winds--}}
                                            <td class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-left" style="border: none;">wind (mph)</td>
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
                                                <td><div class="container"><div class="row"><div class="col-sm text-center"><span>
                                                <i class="material-icons rotate-icon" style="transform: rotate({{ $rotationAM + 180 }}deg);">navigation</i> {{ $weather->wind_speed_AM }}</span></div><div class="col-sm text-center"><span>
                                                <i class="material-icons rotate-icon" style="transform: rotate({{ $rotationPM +180  }}deg);">navigation</i> {{ $weather->wind_speed_PM }}</span></div></div></div></td>    
                                            @endforeach
                                        </tr>

                                        <tr> {{--Water temp--}}
                                            <td class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-left" style="border: none;">water temp (f)</td>
                                            @foreach($weathers as $weather)
                                                <td><div class="container"><div class="row"><div class="col-sm text-center"><span>{{ $weather->water_temp_AM }}°</span></div><div class="col-sm text-center"><span>{{ $weather->water_temp_PM }}°</span></div></div></div></td>    
                                            @endforeach
                                        </tr>
                                        
                                        <tr> {{--High tides--}}
                                            <td class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-left" style="border: none;">high tide</td>
                                            @php
                                                foreach($weathers as $weather) {     
                                                    $jsonString = $weather->tides;
                                                    
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
                                                    echo '<td><div class="container"><div class="row"><div class="col-sm text-center"><span>' . $highTides['AM'] . '</span></div><div class="col-sm text-center"><span>' . $highTides['PM'] . '</span></div></div></div></td>';
                                                }
                                            @endphp
                                        </tr>

                                        <tr> {{--Low tides--}}
                                            <td class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-left" style="border: none;">low tide</td>
                                            @php
                                                foreach($weathers as $weather) {     
                                                    $jsonString = $weather->tides;
                                                    
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
                                                    echo '<td><div class="container"><div class="row"><div class="col-sm text-center"><span>' . $lowTides['AM'] . '</span></div><div class="col-sm text-center"><span>' . $lowTides['PM'] . '</span></div></div></div></td>';
                                                }
                                            @endphp
                                        </tr>
                                    
                                        <tr><td colspan="100%" class="text-uppercase text-white text-sm font-weight-bolder opacity-7 text-center bg-gradient-info" style="border: none;">astrological</td> </tr>
                                    
                                        <tr> {{--Sunrise--}}
                                            <td class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-left" style="border: none;">SUNRISE</td>
                                            @foreach($weathers as $weather)
                                                <td class="align-middle text-center text-sm">{{ $weather->sunrise }}</td>
                                            @endforeach
                                        </tr>

                                        <tr> {{--Sunset--}}
                                            <td class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-left" style="border: none;">SUNSET)</td>
                                            @foreach($weathers as $weather)
                                                <td class="align-middle text-center text-sm">{{ $weather->sunset }}</td>
                                            @endforeach
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
    
    
    <x-plugins></x-plugins>
    
    @push('js')

    <script src="../../assets/js/plugins/chartjs.min.js"></script>
    
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
                                    echo "'" . $weather->swell_height_AM . "',";
                                else
                                    echo "'" . $weather->swell_height_PM . "',";
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
                            {{ $weather->maxwind_mph }},
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


    {{--Handler for tripAM table: filter by location--}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('filterLocAM').addEventListener('change', function() {
                var selectedOption = this.value;
                var rows = document.querySelectorAll('#tableTripsAM tr[data-tag]');
                
                rows.forEach(function(row) {
                var tags = row.getAttribute('data-tag');
                if (tags.includes(selectedOption) || selectedOption === 'all') {
                    row.style.display = ''; // Show the row
                } else {
                    row.style.display = 'none'; // Hide the row
                }
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('filterAvAM').addEventListener('change', function() {
                var selectedOption = this.value;
                var rows = document.querySelectorAll('#tableTripsAM tr[data-tag]');
                
                rows.forEach(function(row) {
                var tags = row.getAttribute('data-tag');
                if (tags.includes(selectedOption) || selectedOption === 'all') {
                    row.style.display = ''; // Show the row
                } else {
                    row.style.display = 'none'; // Hide the row
                }
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('filterTypeAM').addEventListener('change', function() {
                var selectedOption = this.value;
                var rows = document.querySelectorAll('#tableTripsAM tr[data-tag]');
                
                rows.forEach(function(row) {
                var tags = row.getAttribute('data-tag');
                if (tags.includes(selectedOption) || selectedOption === 'all') {
                    row.style.display = ''; // Show the row
                } else {
                    row.style.display = 'none'; // Hide the row
                }
                });
            });
        });

    {{--Handler for tripAM table: filter by location--}}

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('filterLocPM').addEventListener('change', function() {
            var selectedOption = this.value;
            var rows = document.querySelectorAll('#tableTripsPM tr[data-tag]');
            
            rows.forEach(function(row) {
            var tags = row.getAttribute('data-tag');
            if (tags.includes(selectedOption) || selectedOption === 'all') {
                row.style.display = ''; // Show the row
            } else {
                row.style.display = 'none'; // Hide the row
            }
            });
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('filterAvPM').addEventListener('change', function() {
            var selectedOption = this.value;
            var rows = document.querySelectorAll('#tableTripsPM tr[data-tag]');
            
            rows.forEach(function(row) {
            var tags = row.getAttribute('data-tag');
            if (tags.includes(selectedOption) || selectedOption === 'all') {
                row.style.display = ''; // Show the row
            } else {
                row.style.display = 'none'; // Hide the row
            }
            });
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('filterTypePM').addEventListener('change', function() {
            var selectedOption = this.value;
            var rows = document.querySelectorAll('#tableTripsPM tr[data-tag]');
            
            rows.forEach(function(row) {
            var tags = row.getAttribute('data-tag');
            if (tags.includes(selectedOption) || selectedOption === 'all') {
                row.style.display = ''; // Show the row
            } else {
                row.style.display = 'none'; // Hide the row
            }
            });
        });
    });
    </script>
    @endpush
</x-page-template>

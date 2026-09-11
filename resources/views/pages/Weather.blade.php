<x-page-template bodyClass='dh-shell bg-gray-200' :SEO="$SEO">
    <x-shell.nav active="weather" />
    
    
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
        <x-shell.header title="Marine forecast" />
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

        {{--
            The forecast, 2026-09-10. Weather is a bottom tab now, so this is the
            page a diver opens the night before a trip and it has one job: should
            I dive tomorrow, and where.

            It used to open on a stock photo of a breaking wave, the location name
            and a map, which is a screen and a half before any information about
            the ocean, and it ran to nine phone screens. The verdict Pablo's
            crawler already computes (conditionsAM_score / conditionsPM_score,
            bucketed Poor, Average, Good, Perfect) was four cells in a table
            halfway down, and swell_period and wind_dir were never shown at all
            even though they are what separates a nice roll from chop.

            Order now: the verdict for today with the four numbers behind it and
            the boats going out, then seven days, then the whole coast, then
            everything else behind a tap. Webcams, charts and the full table are
            all still here, they just do not load on arrival.
        --}}
        @php
            // Shared by the cards below. Conditions text drives the colour
            // everywhere on the site (see components/conditions-pill).
            $tone = function ($text) {
                $t = strtolower(trim((string) $text));
                if (in_array($t, ['perfect', 'good'])) return 'good';
                if ($t === 'average') return 'avg';
                if ($t === '') return 'none';
                return 'poor';
            };
            $ft = fn ($v) => $v === null || $v === '' ? null : round((float) $v, 1);
            // One honest sentence about why today reads the way it does. Period is
            // the difference between a roll and chop; direction is offshore or on.
            $why = null;
            if ($today) {
                $h = $ft($today->swell_height_AM); $pr = $ft($today->swell_period_AM);
                $w = $ft($today->wind_speed_AM); $d = $today->wind_dir_AM;
                $shape = $pr === null ? null : ($pr >= 7 ? 'Long period, so it rolls rather than chops.'
                        : ($pr >= 5 ? 'A medium period, some chop on the surface.'
                        : 'Short period, so it is chop rather than swell.'));
                $why = trim(($h !== null ? $h . ' ft of swell' : 'Swell') .
                       ($pr !== null ? ' on a ' . $pr . ' second period' : '') .
                       ($w !== null ? ', ' . round($w) . ' mph' . ($d ? ' out of the ' . $d : '') : '') . '. ' . $shape);
            }
        @endphp

        <div class="container-fluid py-0 dh-wx">

            {{-- 1. The verdict. --}}
            @if($today)
            <section class="dh-wx-verdict">
                <div class="dh-wx-head">
                    <div>
                        <h1 class="dh-wx-place">{{ ucwords($location) }}</h1>
                        <p class="dh-wx-date">{{ \Carbon\Carbon::parse($today->date)->format('l j F') }}</p>
                    </div>
                    <a class="dh-wx-swap" href="#dh-wx-coast">
                        <span class="material-icons-round" aria-hidden="true">place</span>Change
                    </a>
                </div>

                @php $dayTone = $tone($today->conditionsAM_text); @endphp
                <div class="dh-wx-callout is-{{ $dayTone }}">
                    <p class="dh-wx-word">{{ ucfirst(strtolower($today->conditionsAM_text ?: 'No forecast')) }} diving today</p>
                    @if($why)<p class="dh-wx-why">{{ $why }}</p>@endif
                </div>

                <div class="dh-wx-halves">
                    @foreach([['Morning', $today->conditionsAM_text, $today->swell_height_AM, $today->wind_speed_AM, $today->wind_dir_AM],
                              ['Afternoon', $today->conditionsPM_text, $today->swell_height_PM, $today->wind_speed_PM, $today->wind_dir_PM]] as $half)
                        <div class="dh-wx-half is-{{ $tone($half[1]) }}">
                            <span class="dh-wx-lab">{{ $half[0] }}</span>
                            <span class="dh-wx-val">{{ $half[1] ?: 'No forecast' }}</span>
                            <span class="dh-wx-sub">{{ $ft($half[2]) }} ft &middot; {{ round((float) $half[3]) }} mph {{ $half[4] }}</span>
                        </div>
                    @endforeach
                </div>

                {{-- The four numbers that actually move the decision. --}}
                <div class="dh-wx-facts">
                    @foreach([['waves', $ft($today->swell_height_AM), 'ft', 'Swell'],
                              ['timer', $ft($today->swell_period_AM), 's', 'Period'],
                              ['air', round((float) $today->wind_speed_AM), $today->wind_dir_AM, 'Wind'],
                              ['thermostat', round((float) $today->water_temp_AM) . '&deg;', '', 'Water']] as $f)
                        <div class="dh-wx-fact">
                            <span class="material-icons-round" aria-hidden="true">{{ $f[0] }}</span>
                            <span class="dh-wx-n">{!! $f[1] !!}<small>{{ $f[2] }}</small></span>
                            <span class="dh-wx-u">{{ $f[3] }}</span>
                        </div>
                    @endforeach
                </div>

                @php $boatsToday = $boatsByDate[$today->date] ?? 0; @endphp
                <a class="dh-wx-boats" href="{{ route('Trips') }}/{{ $today->date }}">
                    <span class="material-icons-round" aria-hidden="true">sailing</span>
                    @if($boatsToday > 0)
                        {{ $boatsToday }} {{ Str::plural('boat', $boatsToday) }} going out from {{ ucwords($location) }} today
                    @else
                        See every boat going out today
                    @endif
                    <span class="material-icons-round dh-wx-arrow" aria-hidden="true">chevron_right</span>
                </a>

                <p class="dh-wx-explain">
                    <a href="#" onclick="event.preventDefault();showModal();">How we score conditions</a>
                </p>
            </section>
            @endif

            {{-- 2. Seven days: the column a diver scans for "which day". --}}
            @if($days->isNotEmpty())
            <section class="dh-wx-card">
                <h2 class="dh-wx-ch">Next {{ $days->count() }} days <span>morning / afternoon</span></h2>
                @foreach($days as $d)
                    @php
                        $dc = \Carbon\Carbon::parse($d->date);
                        $isToday = $dc->isToday();
                        $boats = $boatsByDate[$d->date] ?? 0;
                    @endphp
                    <a class="dh-wx-day {{ $isToday ? 'is-today' : '' }}" href="{{ route('Trips') }}/{{ $d->date }}">
                        <span class="dh-wx-dday">
                            <b>{{ $isToday ? 'Today' : $dc->format('D') }}</b>
                            <span>{{ $dc->format('M j') }}</span>
                        </span>
                        <span class="dh-wx-bars">
                            <span class="dh-wx-bar is-{{ $tone($d->conditionsAM_text) }}">{{ $d->conditionsAM_text ?: '?' }}<small>AM</small></span>
                            <span class="dh-wx-bar is-{{ $tone($d->conditionsPM_text) }}">{{ $d->conditionsPM_text ?: '?' }}<small>PM</small></span>
                        </span>
                        <span class="dh-wx-dsea">
                            <b>{{ $ft($d->swell_height_AM) }} ft</b> {{ $ft($d->swell_period_AM) }}s<br>
                            {{ round((float) $d->wind_speed_AM) }} {{ $d->wind_dir_AM }}
                            @if($boats > 0)<br><span class="dh-wx-dboats">{{ $boats }} {{ Str::plural('boat', $boats) }}</span>@endif
                        </span>
                    </a>
                @endforeach
            </section>
            @endif

            {{-- 3. The whole coast today. This replaces the map as the picker and
                 answers the better question: not "show me Boynton" but "where is
                 it good". --}}
            @if($coastToday->isNotEmpty())
            <section class="dh-wx-card" id="dh-wx-coast">
                <h2 class="dh-wx-ch">Along the coast today <span>tap to switch</span></h2>
                @foreach($coastToday as $c)
                    <a class="dh-wx-crow {{ $c->location === $location ? 'is-here' : '' }}" href="{{ route('Weather') }}/{{ rawurlencode($c->location) }}">
                        <span class="dh-wx-cname">{{ ucwords($c->location) }}</span>
                        <span class="dh-pill dh-pill-{{ $tone($c->conditionsAM_text) }}">{{ $c->conditionsAM_text }}</span>
                        <span class="dh-wx-cnum"><b>{{ $ft($c->swell_height_AM) }}</b> ft {{ $ft($c->swell_period_AM) }}s</span>
                    </a>
                @endforeach
            </section>
            @endif

            {{-- 4. Everything else, one tap away. Argentina and any location not
                 on the coast list still reach their forecast from here. --}}
            <details class="dh-wx-more">
                <summary><span class="material-icons-round" aria-hidden="true">public</span>All locations<span class="dh-wx-hint">Florida and Argentina</span></summary>
                <div class="dh-wx-more-body">
                    <select class="form-select" onchange="if(this.value) window.location.href=this.value;" aria-label="Choose a location">
                        <option value="">Select...</option>
                        @foreach($allLocations as $thisLocation)
                            <option value="{{ route('Weather') }}/{{ rawurlencode($thisLocation->location) }}" {{ $thisLocation->location === $location ? 'selected' : '' }}>{{ ucwords($thisLocation->location) }}</option>
                        @endforeach
                    </select>
                </div>
            </details>

            @if($today && $today->tides)
            <details class="dh-wx-more">
                <summary><span class="material-icons-round" aria-hidden="true">waterfall_chart</span>Tides and daylight<span class="dh-wx-hint">{{ ucwords($location) }}</span></summary>
                <div class="dh-wx-more-body">
                    @php $tides = json_decode($today->tides, true) ?: []; @endphp
                    <ul class="dh-wx-tides">
                        @foreach($tides as $t)
                            <li><span>{{ ucfirst(strtolower($t['tide_type'] ?? '')) }}</span>
                                <b>{{ isset($t['tide_time']) ? \Carbon\Carbon::parse($t['tide_time'])->format('H:i') : '' }}</b></li>
                        @endforeach
                        <li><span>Sunrise and sunset</span><b>{{ $today->sunrise }} &middot; {{ $today->sunset }}</b></li>
                    </ul>
                </div>
            </details>
            @endif

            <details class="dh-wx-more" id="dh-wx-cams">
                <summary><span class="material-icons-round" aria-hidden="true">videocam</span>Live webcams<span class="dh-wx-hint">loads when you open it</span></summary>
                <div class="dh-wx-more-body">
                <div class="row mx-0">

                {{-- Card live cam --}}
                @if($location == "fort lauderdale")                    
                    <div class="col-md-4">
                        <div class="card p-0 position-relative mt-3 mx-n2 z-index-2 mb-4">
                            <div class="card-header p-0 mt-n4 mx-3">
                                <!-- <iframe loading="lazy" class="img-fluid border-radius-lg" allow-same-origin="" allow-scripts="" allowfullscreen="" alt="EarthCam Video Player Embed" autoplay="" frameborder="0" height="450" id="iframe" marginheight="0" marginwidth="0" scrolling="no" src="https://www.youtube.com/embed/pXx3YQVSUGg?si=rZOs45AdFVYTL4JQ" style="top:0;left:0;width:10px;min-width:100%;*width:100%;height:200px;" width="800"></iframe> -->
                                <iframe loading="lazy" width="100%" height="300"
                                    src="https://www.youtube.com/embed/pXx3YQVSUGg?autoplay=1&mute=1"
                                    title="YouTube video player"
                                    frameborder="0"
                                    allow="autoplay; accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                    referrerpolicy="strict-origin-when-cross-origin"
                                    allowfullscreen>
                                </iframe>

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
                                <!-- <iframe loading="lazy" class="img-fluid shadow border-radius-lg" src="https://player.brownrice.com/embed/copbfl1" height="100%" width="100%" autoplay="" allowfullscreen mozallowfullscreen style="top:0;left:0;height:200px;" scrolling="no"></iframe> -->
                                    <!-- <iframe loading="lazy" class="img-fluid shadow border-radius-lg"
                                        src="https://player.brownrice.com/embed/copbfl1?autoplay=1&muted=1"
                                        height="100%" width="100%"
                                        allow="autoplay; fullscreen"
                                        mozallowfullscreen
                                        style="top:0;left:0;height:200px;"
                                        scrolling="no">
                                    </iframe> -->
                                <!-- <iframe loading="lazy" width="560" height="315" src="https://www.youtube.com/embed/zclpD3QKEK4?si=nRFppTudYpqk4wq3" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe> -->
                                <iframe loading="lazy" width="100%" height="300"
                                    src="https://www.youtube.com/embed/zclpD3QKEK4?autoplay=1&mute=1"
                                    title="YouTube video player"
                                    frameborder="0"
                                    allow="autoplay; accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                    referrerpolicy="strict-origin-when-cross-origin"
                                    allowfullscreen>
                                </iframe>




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
                                <iframe loading="lazy" class="img-fluid shadow border-radius-lg" id="main_content" name="main_content" src="https://video-monitoring.com/beachcams/palmbeachmarriott/stream.htm" width="960" height="540" allowfullscreen="" autoplay="true" style="top:0;left:0;width:10px;min-width:100%;*width:100%;height:200px;"></iframe>
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
                @elseif($location == "jupiter")
                    <div class="col-md-4">
                        <div class="card p-0 position-relative mt-3 mx-n2 z-index-2 mb-4">
                            <div class="card-header p-0 mt-n4 mx-3">
                                <!-- <iframe loading="lazy" class="img-fluid shadow border-radius-lg" id="main_content" name="main_content" src="https://video-monitoring.com/beachcams/palmbeachmarriott/stream.htm" width="960" height="540" allowfullscreen="" autoplay="true" style="top:0;left:0;width:10px;min-width:100%;*width:100%;height:200px;"></iframe> -->
                                <!-- <video data-html5-video="" preload="metadata" src="blob:https://video-monitoring.com/33efa922-17ad-4d2e-bf95-7f0d172dec3c"><style class="clappr-style">[data-html5-video]{position:absolute;height:100%;width:100%;display:block}</style></video> -->
                            <iframe loading="lazy" width="100%" height="300"
                                src="https://www.youtube.com/embed/4y7kDbwBuh0?autoplay=1&mute=1"
                                title="YouTube video player"
                                frameborder="0"
                                allow="autoplay; accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                referrerpolicy="strict-origin-when-cross-origin"
                                allowfullscreen>
                            </iframe>

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
                                <!-- <iframe loading="lazy" class="img-fluid shadow border-radius-lg" src="https://relay.ozolio.com/pub.api?cmd=embed&oid=EMB_ZUKF00000B65" height="100%" width="100%" autoplay="" allow="autoplay;" allowfullscreen mozallowfullscreen style="top:0;left:0;height:200px;" scrolling="no"></iframe>  -->
                            <iframe loading="lazy" width="100%" height="300"
                                src="https://www.youtube.com/embed/bi7B4EmyHHs?autoplay=1&mute=1"
                                title="YouTube video player"
                                frameborder="0"
                                allow="autoplay; accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                referrerpolicy="strict-origin-when-cross-origin"
                                allowfullscreen>
                            </iframe>

                                
                                
                                
                                    
                            </div>
                            <div class="card-body">
                                <h6 class="mb-0 "> live web cam</h6>  
                                <div class="d-flex ">
                                    <i class="material-icons text-sm my-auto me-1">schedule</i>
                                    <p class="mb-0 text-sm">Last update: now (Sunny Isles Beach, Miami Beach) </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @elseif($location == "key west")
                    <div class="col-md-4">
                        <div class="card p-0 position-relative mt-3 mx-n2 z-index-2 mb-4">
                            <div class="card-header p-0 mt-n4 mx-3">
                            
                                <!-- <iframe loading="lazy" class="img-fluid shadow border-radius-lg" src="https://relay.ozolio.com/pub.api?cmd=embed&amp;oid=EMB_RKNO000004F8" height="100%" width="100%" autoplay="" allow="autoplay;" allowfullscreen mozallowfullscreen style="top:0;left:0;height:200px;" scrolling="no"></iframe> -->
                                <iframe loading="lazy" width="100%" height="300"
                                    src="https://www.youtube.com/embed/sDXwrYQPeOQ?autoplay=1&mute=1"
                                    title="YouTube video player"
                                    frameborder="0"
                                    allow="autoplay; accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                    referrerpolicy="strict-origin-when-cross-origin"
                                    allowfullscreen>
                                </iframe>

                                    
                                
                                    
                            </div>
                            <div class="card-body">
                                <h6 class="mb-0 "> live web cam</h6>  
                                <div class="d-flex ">
                                    <i class="material-icons text-sm my-auto me-1">schedule</i>
                                    <p class="mb-0 text-sm">Last update: now (source Fort Zachary Taylor Beach) </p>
                                </div>
                            </div>
                        </div>
                    </div> 
                @endif
                {{-------------------------------}}


                                </div>
                </div>
            </details>

            <details class="dh-wx-more">
                <summary><span class="material-icons-round" aria-hidden="true">show_chart</span>Charts and the full table<span class="dh-wx-hint">waves, wind, air, humidity, rain</span></summary>
                <div class="dh-wx-more-body">
                <div class="row mx-0">
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
                                <h6 class="mb-0 "> waves (ft)</h6>
                                
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
                                            @foreach($weathers as $weather)
                                                <td><div class="container"><div class="row"><div class="col-xxs text-center"><span>AM</span></div><div class="col-sm text-center"><span>PM</span></div></div></div></td>
                                            @endforeach
                                            
                                        </tr>

                                        <tr> {{--waves--}}
                                            <td class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-left" style="border: none;">waves (ft)</td>
                                            @foreach($weathers as $weather)
                                                <td><div class="container"><div class="row"><div class="col-xxs text-center"><span>{{ round($weather->swell_height_AM) }}</span></div><div class="col-sm text-center"><span>{{ round($weather->swell_height_PM) }}</span></div></div></div></td>    
                                            @endforeach
                                        </tr>

                                        <tr> {{--Period--}}
                                            <td class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-left" style="border: none;">period (secs)</td>
                                            @foreach($weathers as $weather)
                                                <td><div class="container"><div class="row"><div class="col-xxs text-center"><span>{{ round($weather->swell_period_AM) }}</span></div><div class="col-sm text-center"><span>{{ round($weather->swell_period_PM) }}</span></div></div></div></td>    
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
                                                <td><div class="container"><div class="row"><div class="col-xs text-center"><span>
                                                <i class="material-icons rotate-icon" style="transform: rotate({{ $rotationAM + 180 }}deg);">navigation</i>{{ round($weather->wind_speed_AM) }}</span></div><div class="col-sm text-center"><span>
                                                <i class="material-icons rotate-icon" style="transform: rotate({{ $rotationPM +180  }}deg);">navigation</i>{{ round($weather->wind_speed_PM) }}</span></div></div></div></td>    
                                            @endforeach
                                        </tr>

                                        <tr> {{--Water temp--}}
                                            <td class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-left" style="border: none;">water temp (f)</td>
                                            @foreach($weathers as $weather)
                                                <td><div class="container"><div class="row"><div class="col-xxs text-center"><span>{{ round($weather->water_temp_AM) }}°</span></div><div class="col-sm text-center"><span>{{ round($weather->water_temp_PM) }}°</span></div></div></div></td>    
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
                                    
                                        <tr><td colspan="100%" class="text-uppercase text-white text-sm font-weight-bolder opacity-7 text-center bg-gradient-info" style="border: none;">sunrise/sunset</td> </tr>
                                    
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

                            </div>
                </div>
            </details>

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


    {{--Handler for location--}}
    <script>
    document.getElementById('filterLocation').addEventListener('change', function() {
        var location = this.value;
        var url = '/Weather/' + location;
        window.location.href = url;
    });
    </script>

    <script>

    </script>
    @endpush
</x-page-template>

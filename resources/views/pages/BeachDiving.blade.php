<x-page-template bodyClass='g-sidenav-show  bg-gray-200' :SEO="$SEO">
    <x-auth.navbars.sidebar activePage="Special" activeItem="beachDiving" activeSubitem=""></x-auth.navbars.sidebar>
    
    
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <!-- Navbar -->
        <x-auth.navbars.navs.auth pageTitle="Beach Diving"></x-auth.navbars.navs.auth>
        <!-- End Navbar -->

        <style>
            iframe {
                aspect-ratio: 16 / 9; /* Set the desired aspect ratio (16:9 for YouTube) */
                height: auto; /* Let the height adjust automatically */
                width: 100%; /* Fill the available width */
            }
        </style>
        
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

            {{--modal Level--}}
            <div class="modal fade" id="modalLevel" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="modal-notification" aria-hidden="true">
                <div class="modal-dialog modal-danger modal-dialog-centered modal-" role="document">
                    <div class="modal-content">
                        <div class="modal-header text-center">
                            <h6 class="modal-title font-weight-normal" id="modal-title-notification">Site levels</h6>
                            
                        </div>
                        <div class="modal-body">
                            <div class="py-3 text-center">
                                <h4 class="text-gradient text-info text-md mt-4"></h4>
                                <div class="table-responsive">
                                    <table class="table align-items-left mb-0"> 
                                        <tbody>
                                            
                                            <tr><td class="w-20 text-secondary text-end text-lg font-weight-bolder opacity-7"> </td>
                                            <td class="w-60 align-middle text-start text-sm"><b>Level</b></td>
                                            <td class="w-20 align-middle text-start text-sm"><b>Max Depth (ft)</b></td> </tr>
                                            
                                            <tr><td class="text-secondary text-end text-lg font-weight-bolder"><img src="{{ asset('assets') }}/img/icons/icons_level_0.png" alt="OW" height="25"></td>
                                            <td class="align-middle text-info text-start text-sm"><b>Open Water</b></td> 
                                            <td class="align-middle text-info text-center text-sm"><b>60</b></td> </tr>

                                            <tr><td class="text-secondary text-end text-lg font-weight-bolder"><img src="{{ asset('assets') }}/img/icons/icons_level_1.png" alt="AOW" height="25"></td>
                                            <td class="align-middle text-info text-start text-sm"><b>Advanced Open Water</b></td>
                                            <td class="align-middle text-info text-center text-sm"><b>130</b></td> </tr>

                                            <tr><td class="text-secondary text-end text-lg font-weight-bolder opacity-7"><img src="{{ asset('assets') }}/img/icons/icons_level_2.png" alt="Ta" height="25"></td>
                                            <td class="align-middle text-info text-start text-sm"><b>Technical Air</b></td>
                                            <td class="align-middle text-info text-center text-sm"><b>150</b></td> </tr>

                                            <tr><td class="text-secondary text-end text-lg font-weight-bolder"><img src="{{ asset('assets') }}/img/icons/icons_level_3.png" alt="Tn" height="25"></td>
                                            <td class="align-middle text-info text-start text-sm"><b>Technical Normoxic Trimix</b></td>
                                            <td class="align-middle text-info text-center text-sm"><b>200</b></td> </tr>

                                            <tr><td class="text-secondary text-end text-lg font-weight-bolder"><img src="{{ asset('assets') }}/img/icons/icons_level_4.png" alt="Tn" height="25"></td>
                                            <td class="align-middle text-info text-start text-sm"><b>Technical Hypoxic Trimix</b></td>
                                            <td class="align-middle text-info text-center text-sm"><b>330+</b></td> </tr>

                                        </tbody>
                                    </table>
                                </div>   
                                <p>Press anywhere outside this dialog to continue</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>



            <div class="page-header min-height-200 max-height-300 border-radius-xl mt-4 mx-n2" style="background-image: url('/assets/img/illustrations/beach_diving.webp');">
                <span class="mask  bg-gradient-info  opacity-4"></span>
            </div>

            <div class="card p-0 position-relative mt-n5 mx-1 z-index-2 mb-4">
                
                    <div class="p-0 mt-0 mx-2 border-radius-lg py-3 pe-1">
                        <div style="float: left;">
                            <h1 class="card-title text-info mx-3 mt-0">Beach Diving</h1>
                        </div>

                    </div>
                </div>
            </div>

            <div class="row">
                
                
                {{-- Card Dive Conditions --}}
                <div class="col-md-7">             
                    <div class="card p-0 position-relative mt-3 mx-3 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h2 class="card-title text-white mx-4">Shore Dive Conditions</h2>
                                <a href="#" onclick="showModal();"><p class="text-white text-decoration-underline text-xs mt-n2 mx-4"><b>What is this?</b></p></a>
                                <div class="table-responsive"></div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table align-items-center mb-0">
                                    
                                    <tbody>
                                        <tr> {{--Day name--}}
                                            <td class="text-uppercase text-secondary text-md font-weight-bolder opacity-7 text-center" style="border: none;">  </td>
                                            @foreach($weathers[0] as $weather)
                                                @php
                                                    $date = new DateTime($weather->date);
                                                    $dateDayName = $date->format('l-d');
                                                @endphp
                                                <td class="align-middle text-center text-md"><b>{{ $dateDayName }}</b></td>     
                                            @endforeach
                                        </tr>

                                        @foreach($weathers as $locationWeather)
                                            <tr><td colspan="100%" class="text-uppercase text-white text-sm font-weight-bolder opacity-7 text-center bg-gradient-info" style="border: none;"><a class="text-white" href="/Weather/{{$locationWeather[0]->location}}/">{{$locationWeather[0]->location}}</a></td> </tr>
                                            <tr>
                                                <td class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-left" style="border: none;">Morning</td>
                                                @foreach($locationWeather as $weather)
                                                    @if($weather->conditionsAM_score >= 3.8)
                                                        <td class="align-middle text-center text-sm"> <span class="badge badge-lg badge-success">Go dive!</span> </td>
                                                    @else
                                                        <td class="align-middle text-center text-sm"> <span class="badge badge-lg badge-danger">No  dive</span> </td>
                                                    @endif    
                                                @endforeach
                                            </tr>

                                            <tr>
                                                <td class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-left" style="border: none;">Afternoon</td>
                                                @foreach($locationWeather as $weather)
                                                    @if($weather->conditionsPM_score >= 3.8)
                                                        <td class="align-middle text-center text-sm"> <span class="badge badge-lg badge-success">Go dive!</span> </td>
                                                    @else
                                                        <td class="align-middle text-center text-sm"> <span class="badge badge-lg badge-danger">No  dive</span> </td>
                                                    @endif    
                                                @endforeach
                                            </tr>
                                        @endforeach

                                    </tbody>
                                </table>
                            </div>
                            
                        </div> 
                    </div>
                </div>
                {{--------------------------}}

                {{-- Card Live UW cam --}}
                <div class="col-md-5">
                    <div class="card p-0 position-relative mt-3 mx-3 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h2 class="card-title text-white mx-4">Underwater camera</h2>
                                <p class="card-title text-white text-xs mt-n2 mx-4">Reference for current visibility</p>
                                <div class="table-responsive"></div>
                            </div>
                        </div>
                        <div class="card-body mt-0">
                            <iframe id="youtubeVideo" class="img-fluid border-radius-lg" src="https://www.youtube.com/embed/hFr5w9KLEA4?si=fUQD_s-39lclBoZb&amp;controls=0;autoplay=1" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                            <p class="align-middle text-center text-sm"><b>🎥 City of Pompano Beach</b></p>
                        </div>
                    </div>
                
                </div>
                {{--------------------------}}

                {{-- Card Locations --}}
                <?php
                    $i=0;
                    $displayedLocations = [];
                ?>
                @foreach($sites as $siteLocation)
                <div class="col-md-12 m-auto">
                    <div class="card p-0 position-relative mt-3 mx-3 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <?php
                                
                                foreach($locations as $location)
                                    if($location->short == $siteLocation[0]->location) {
                                        echo '<h3 class="card-title text-white mx-4">' . ucwords($location->location) . '</h3>';
                                        array_push($displayedLocations, $location);

                                    }
                                ?>
                                
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-5">
                                    <div id="map{{$i}}" style="width: 100%; height: 600px; border-radius: 1rem; background-color: #f0f0f0; padding: 1rem;"></div>
                                </div>
                                <div class="col-md-7">
                                    <div class="table-responsive">
                                        <table>
                                            <thead class="text-info">
                                                <th class="align-top">
                                                    Type
                                                </th>
                                                
                                                <th class="px-4 align-top text-start" data-bs-toggle="tooltip" data-bs-placement="top" title="Click on the name of the trip to see full trip details" data-container="body" data-animation="true">
                                                    Site Name<p class="text-xs text-start mt-0 px-1">click for details</p>
                                                </th>
                                                <th class="px-4 align-top text-center">
                                                    Level<a href="#" onclick="showModalLevel();"><p class="text-xs text-info text-center mt-0 px-1">(?)</p></a>
                                                </th>
                                                <th class="px-4 align-top text-center" data-bs-toggle="tooltip" data-bs-placement="top" title="swimming distance from shore" data-container="body" data-animation="true">
                                                    Distance from shore <p class="text-xs text-info text-center mt-0 px-1">(ft)</p>
                                                </th>
                                                <th class="px-4 align-top text-center" data-bs-toggle="tooltip" data-bs-placement="top" title="site max depth" data-container="body" data-animation="true">
                                                    Max Depth <p class="text-xs text-info text-center mt-0 px-1">(ft)</p>
                                                </th>
                                            </thead>
                                            <tbody>
                                                @foreach($siteLocation as $site)    
                                                    <tr style="border-bottom: 1px solid #D3D3D3;">
                                                        <td class="w-5 img-fluid"><img style="height:50px;" src="{{ asset('assets') }}/img/icons/{{ $site->type }}_icon.png" alt="{{ $site->type }}"></td>
                                                        <td class="w-60 align-middle text-left text-md"><b><a href="/SiteDetails/{{ $site->id }}"> {{ $site->name }}</a></b></td> 

                                                        <td class="w-5 text-center" style="border: none;"><img src="{{ asset('assets') }}/img/icons/icons_level_{{ $site->level }}.png" alt="levelIcon" height="25"></td>
                                                        <td class="w-20 align-middle text-center text-md"><b>{{ $site->distance_from_shore }}</b></td> 
                                                        <td class="w-10 align-middle text-center text-md"><b>{{ $site->maxDepth }}</b></td> 
                                                
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            
                                        </table>
                                    </div>    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                    $i++;
                ?>
                @endforeach
                {{--------------------------}}
                
                    
            </div>
            
                


                
            
            
            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>
    
    
    {{--<x-plugins></x-plugins>--}}
    
    @push('js')
    
    <script src="{{ asset('assets') }}/js/plugins/jquery-3.6.0.min.js" type="text/javascript"></script>

    <script>
        function showModal() {
            $('#modal').modal('show'); // Show the modal
        };

        function showModalLevel() {
            $('#modalLevel').modal('show'); // Show the modal
        };
    </script>
    
    <script src="https://api.mapbox.com/mapbox-gl-js/v2.6.1/mapbox-gl.js"></script>
    <link href="https://api.mapbox.com/mapbox-gl-js/v2.6.1/mapbox-gl.css" rel="stylesheet" />

    <script>
        mapboxgl.accessToken = 'pk.eyJ1IjoicHN0cmlrYSIsImEiOiJjbHZsc2p2bXcyY240MmtuMDcydHJzd2UxIn0.KBf79cvk47WseBc9rNu6gQ'; // Replace with your actual access token

        <?php
            function dms_to_dd($degrees, $minutes, $direction) {
                $sign = ($direction === 'N' || $direction === 'E') ? 1 : -1;
                $dd = $degrees + ($minutes * 60)  / 3600;
                return $dd * $sign;
            }
        ?>

        @for($j=0; $j < $i; $j++)
        const map{{$j}} = new mapboxgl.Map({
            container: 'map{{$j}}',
            style: 'mapbox://styles/pstrika/clwqz4fds03gv01qo9d4w3g21', // Choose a map style
            center: [ {{$displayedLocations[$j]->centerLon }}, {{$displayedLocations[$j]->centerLat }}], // Set the initial center coordinates
            zoom: 11, // Set the initial zoom level
            projection: 'albers'
        });

        //add icons
        map{{$j}}.loadImage( '{{ asset('assets') }}/img/icons/marker_reef.png', (error, reef) => {
            if (error) throw error;
            // Continue to the next step...
            map{{$j}}.addImage('icon_reef', reef);
        });
        map{{$j}}.loadImage( '{{ asset('assets') }}/img/icons/marker_wreck.png', (error, wreck) => {
            if (error) throw error;
            // Continue to the next step...
            map{{$j}}.addImage('icon_wreck', wreck);
        });
        map{{$j}}.loadImage( '{{ asset('assets') }}/img/icons/marker_other.png', (error, other) => {
            if (error) throw error;
            // Continue to the next step...
            map{{$j}}.addImage('icon_other', other);
        });

        const sites{{$j}} = {
            'type': 'FeatureCollection',
            'features': [
                <?php
                    $siteLocation = $sites[$j];
                    foreach($siteLocation as $site) {
                        list($lat_deg, $lat_min, $lat_dir) = sscanf($site->gpsLat, "%d° %f' %c");
                        list($lon_deg, $lon_min, $lon_dir) = sscanf($site->gpsLon, "%d° %f' %c");

                        $latitude_dd = dms_to_dd($lat_deg, $lat_min, $lat_dir);
                        $longitude_dd = dms_to_dd($lon_deg, $lon_min, $lon_dir);
                
                        echo "{
                            'type': '" . $site->type . "'," .
                                "'properties': {" .
                                    "'name': \"" . $site->name . "\"," .
                                    "'icon': 'icon_" . $site->type . "'," .
                                    "'url': 'SiteDetails/" . $site->id . "'," .
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

        map{{$j}}.on('load', () => {
            // Add a GeoJSON source containing place coordinates and information.
            map{{$j}}.addSource('sites', {
                'type': 'geojson',
                'data': sites{{$j}}
            });

            map{{$j}}.addLayer({
                'id': 'poi-labels',
                'type': 'symbol',
                'source': 'sites',
                'layout': {
                    'text-field': ['get', 'name'],
                    'text-variable-anchor': ['top'],
                    'text-radial-offset': 0.5,
                    'text-justify': 'auto',
                    'text-size': 12,
                    'icon-image': ['get', 'icon'],
                    'icon-size': 0.3,
                    'icon-anchor': 'bottom',
                },
                'paint': {
                    'text-color': 'white',
                },
            });

            
        });

        map{{$j}}.on('click', function (e) {
            var features = map{{$j}}.queryRenderedFeatures(e.point, { layers: ['poi-labels'] });

            if (!features.length) {
                return;
            }

            var feature = features[0];
            // Use Feature and put your code
            // Populate the popup and set its coordinates
            // based on the feature found.
            window.location.href = feature.properties.url;
            
        });

        map{{$j}}.on('mousemove', function (e) {
            var features = map{{$j}}.queryRenderedFeatures(e.point, { layers: ['poi-labels'] });
            map{{$j}}.getCanvas().style.cursor = (features.length) ? 'pointer' : '';
        });
        
        @endfor

    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            sidebarColor(document.getElementById("sidebarColorDiv")); // Execute the sidebarColor function once the HTML is loaded
        });
    </script>
    @endpush
</x-page-template>

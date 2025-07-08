<x-page-template bodyClass='g-sidenav-show  bg-gray-200' :SEO="$SEO">
    <x-auth.navbars.sidebar activePage="DiveSites" activeItem="wreckWiki" activeSubitem=""></x-auth.navbars.sidebar>
    
    
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <!-- Navbar -->
        <x-auth.navbars.navs.auth pageTitle="WreckWiki"></x-auth.navbars.navs.auth>
        <!-- End Navbar -->

        <style>
            .custom-marker {
                /* Customize your marker icon (e.g., use an image) */
                height: 50px;
                
                /*background-image: url('{{ asset('assets') }}/img/icons/marker_wreck.png');
                background-size: cover;*/
                /*background-color: #0074D9;*/
                
                /*display: flex;*/
                justify-content: center;
                align-items: center;
                color: #FFF;
                font-size: 10px;
            }

            .custom-icon {
                /* Customize your marker icon (e.g., use an image) */
                max-width: 100%;
                height: auto;
                display: flex;
                justify-content: center;
                align-items: center;
                color: #FFF;
                
            }

            .custom-label {
                font-size: 10px;
                color: white;
                text-align: center;
                
            }
            
        </style>

        <div class="container-fluid py-0">
            <div class="d-none" data-color="info" id="sidebarColorDiv"></div> {{--Set active element on sidenav bar color (goes together wih JS below--}}

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

                                            <tr><td class="text-secondary text-end text-lg font-weight-bolder"><img src="{{ asset('assets') }}/img/icons/icons_level_4.png" alt="Th" height="25"></td>
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


            <div class="page-header min-height-200 max-height-300 border-radius-xl mt-4 mx-n2" style="background-image: url('/assets/img/logos/wreckWikiLogo.png');">
                <span class="mask  bg-gradient-info  opacity-4"></span>
            </div>

            <div class="card p-0 position-relative mt-n5 mx-1 z-index-2 mb-4">
                
                    <div class="p-0 mt-0 mx-2 border-radius-lg py-3 pe-1">
                        <div style="float: left;">
                            <h1 class="card-title text-info mx-3 mt-0">Wreck Sites (Powered by wreckwiki.com)</h1>
                        </div>

                    </div>
                </div>
            </div>

            {{---Search input--}}
            <div class="row">
                
                <div id="searchBox" class="row">
                    {{-- Dive Operator location are cards --}}
                    <div class="col-md-6 m-auto">             
                        <div class="card p-0 position-relative mt-3 mx-3 z-index-2 mb-4">
                            <div class="card-body">
                                <form id="myForm" class="multisteps-form__form" action="{{ route('DiveSitesSearch') }}" method="POST" enctype="multipart/form-data">
                                        @csrf <!-- Add CSRF token for security -->

                                        {{--<div class="input-group input-group-dynamic">
                                            <label for="exampleFormControlInput1" class="form-label">what's in your mind?</label>
                                            <input id="searchString" class="multisteps-form__input form-control" type="text" name="searchString"/>
                                        </div>--}}
                                        <div class="ms-md-auto pe-md-3 d-flex align-items-center">
                                            <div class="input-group input-group-outline">
                                                <label class="form-label">What's in your mind?</label>
                                                <input type="text" class="form-control" name="searchString">
                                            </div>
                                        </div>

                                        <div class="button-row text-center mt-0 mt-md-4">
                                            <button class="btn bg-gradient-info ms-auto mb-0" id="submit-all" title="Send" onclick="submitform()">Search</button> {{---type="submit"----}}
                                            <a href="{{ route('DiveSitesAll') }}"><span class="btn bg-gradient-info ms-auto mb-0">Show me all sites</span></a> {{---type="submit"----}}
                                        </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
            {{-----------------------------}}

            <div class="row">
                
                {{--<div class="col-md-6">    
                    <div class="card p-0 position-relative mt-3 mx-3 z-index-2 mb-4">
                        <div class="border-radius-xl">
                            <div id="map" style="width: 100%; height: 400px; border-radius: 1rem; background-color: #f0f0f0; padding: 1rem;"></div>
                        </div>
                    </div>
                </div>--}}
                {{-- Dive Sites Wrecks card --}}
                <div class="col-md-12 m-auto">             
                    <div class="card p-0 position-relative mt-3 mx-3 z-index-2 mb-4">
                        

                        <div class="card-body">
                            <div class="table-responsive">
                                <table>
                                    <thead class="text-info">
                                        
                                        
                                        <th class="px-4 align-top text-start" data-bs-toggle="tooltip" data-bs-placement="top" title="Click on the name of the trip to see full trip details" data-container="body" data-animation="true">
                                            Site Name<p class="text-xs text-start mt-0 px-1">click for details</p>
                                        </th>

                                        <th class="px-4 align-top text-start" data-bs-toggle="tooltip" data-bs-placement="top" title="Click on the name of the trip to see full trip details" data-container="body" data-animation="true">
                                            Location
                                        </th>

                                        <th class="px-4 align-top text-start" data-bs-toggle="tooltip" data-bs-placement="top" title="Click on the name of the trip to see full trip details" data-container="body" data-animation="true">
                                            Rating
                                        </th>
                                        <th class="px-4 align-top text-center">
                                            Level<a href="#" onclick="showModalLevel();"><p class="text-xs text-info text-center mt-0 px-1">(?)</p></a>
                                        </th>
                                        <th class="px-4 align-top text-center" data-bs-toggle="tooltip" data-bs-placement="top" title="site max depth" data-container="body" data-animation="true">
                                            Max Depth
                                        </th>
                                    </thead>
                                    <tbody>
                                        @foreach($sitesWrecks as $site)    
                                            <tr style="border-bottom: 1px solid #D3D3D3;">
                                                
                                                <td class="w-40 align-middle text-left text-md"><b><a href="/SiteDetails/{{ $site->id }}"> {{ $site->name }}</a></b></td> 
                                                <?php
                                                    // Find and print the full location name
                                                    foreach ($locations as $loc) {
                                                        if ($loc['short'] === $site->location) {
                                                            $longLocation = ucwords($loc['location']);
                                                            break;
                                                        }
                                                    }
                                                ?>
                                                <td class="w-30 align-middle text-left text-md"><b>{{ $longLocation }}</b></td> 

                                                <td class="w-15 align-middle text-md"><div id="rateYoReadOnly_{{ $site->id }}"></div></td> 
                                                
                                                <td class="w-5 text-center" style="border: none;"><img src="{{ asset('assets') }}/img/icons/icons_level_{{ $site->level }}.png" height="25"></td>

                                                <td class="w-10 align-middle text-center text-md"><b>{{ $site->maxDepth }}</b></td> 
                                        
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    
                                </table>
                            </div>     
                        </div>
                    </div>
                </div>
                {{-----------------------------}}

               
                    
            </div>
            
                


                
            
            
            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>
    
    
    {{--<x-plugins></x-plugins>--}}
    
    @push('js')
    
    <script src="{{ asset('assets') }}/js/plugins/jquery-3.6.0.min.js" type="text/javascript"></script>
    <script src="https://api.mapbox.com/mapbox-gl-js/v2.6.1/mapbox-gl.js"></script>
    <link href="https://api.mapbox.com/mapbox-gl-js/v2.6.1/mapbox-gl.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/rateYo/2.3.2/jquery.rateyo.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/rateYo/2.3.2/jquery.rateyo.min.js"></script>

    <script>
        @foreach($sitesWrecks as $site)
        $(function () {   
            $("#rateYoReadOnly_{{ $site->id }}").rateYo({
                rating: {{ $site->rate != null ? $site->rate : 0 }},
                readOnly: true
            });
        });
        @endforeach

        

    </script>
    <script>
        mapboxgl.accessToken = 'pk.eyJ1IjoicHN0cmlrYSIsImEiOiJjbHZsc2p2bXcyY240MmtuMDcydHJzd2UxIn0.KBf79cvk47WseBc9rNu6gQ'; // Replace with your actual access token

        const map = new mapboxgl.Map({
            container: 'map',
            style: 'mapbox://styles/pstrika/clwqz4fds03gv01qo9d4w3g21', // Choose a map style
            center: [-80.07488399442913, 26.137643513173536], // Set the initial center coordinates
            zoom: 10, // Set the initial zoom level
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


        const sites = {
            'type': 'FeatureCollection',
            'features': [
                <?php
                    function dms_to_dd($degrees, $minutes, $direction) {
                        $sign = ($direction === 'N' || $direction === 'E') ? 1 : -1;
                        $dd = $degrees + ($minutes * 60)  / 3600;
                        return $dd * $sign;
                    }

                    foreach($sitesWrecks as $site) {
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

        /*coordinates.forEach(coord => {
            const marker = new mapboxgl.Marker()
                .setLngLat(coord.lngLat)
                .addTo(map);

            const popup = new mapboxgl.Popup().setText(coord.label); // Set your label text
            
            marker.setPopup(popup);
        });*/

        /*
        coordinates.forEach(coord => {
            // Create a custom marker
            const el = document.createElement('div');
            el.className = 'custom-marker';
            el.textContent = coord.label; // Set your label text

            // Add your marker at specific coordinates
            new mapboxgl.Marker(el)
                .setLngLat(coord.lngLat)
                .addTo(map);
        });*/

       /* coordinates.forEach(coord => {
            const markerElement = document.createElement('div');
            markerElement.className = 'custom-marker'; // Apply your custom CSS styling

            // Create an icon element (e.g., an image or SVG) for your marker
            const iconElement = document.createElement('div');
            iconElement.className = 'custom-icon'; // Apply additional styling for the icon
            // Set the icon content (you can use an image URL or an SVG here)
            
            iconElement.innerHTML = '<img src="{{ asset('assets') }}/img/icons/marker_wreck.png" height="50px">'; 

            // Create a label element for the location text
            const labelElement = document.createElement('div');
            labelElement.className = 'custom-label'; // Apply styling for the label
            labelElement.textContent = coord.label; // Display the location text

            // Append the icon and label elements to the marker element
            markerElement.appendChild(iconElement);
            markerElement.appendChild(labelElement);

            new mapboxgl.Marker(markerElement)
                .setLngLat(coord.lngLat)
                .addTo(map);
        }); */


    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            sidebarColor(document.getElementById("sidebarColorDiv")); // Execute the sidebarColor function once the HTML is loaded
        });
    </script>

    <script>
        function showModalLevel() {
            $('#modalLevel').modal('show'); // Show the modal
        };
    </script>
    @endpush
</x-page-template>

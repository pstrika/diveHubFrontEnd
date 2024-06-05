<x-page-template bodyClass='g-sidenav-show  bg-gray-200'>
    <x-auth.navbars.sidebar activePage="DiveSites" activeItem="DiveSitesTopRated" activeSubitem=""></x-auth.navbars.sidebar>
    
    
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <!-- Navbar -->
        <x-auth.navbars.navs.auth pageTitle="Dive Sites"></x-auth.navbars.navs.auth>
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

        



            <div class="page-header min-height-200 max-height-300 border-radius-xl mt-4 mx-n2" style="background-image: url('/assets/img/illustrations/dive_sites.jpg');">
                <span class="mask  bg-gradient-info  opacity-4"></span>
            </div>

            <div class="card p-0 position-relative mt-n5 mx-1 z-index-2 mb-4">
                
                    <div class="p-0 mt-0 mx-2 border-radius-lg py-3 pe-1">
                        <div style="float: left;">
                            <h2 class="card-title text-info mx-3 mt-0">Top Rated Dive Sites</h2>
                        </div>

                    </div>
                </div>
            </div>

            <div class="row">
                
                {{--<div class="col-md-6">    
                    <div class="card p-0 position-relative mt-3 mx-3 z-index-2 mb-4">
                        <div class="border-radius-xl">
                            <div id="map" style="width: 100%; height: 400px; border-radius: 1rem; background-color: #f0f0f0; padding: 1rem;"></div>
                        </div>
                    </div>
                </div>--}}
                {{-- Dive Operator location are cards --}}
                <div class="col-md-12 m-auto">             
                    <div class="card p-0 position-relative mt-3 mx-3 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h3 class="card-title text-white mx-4"> All Sites</h3>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table>
                                    <tbody>
                                        @foreach($sites as $site)    
                                            <tr style="border-bottom: 1px solid #D3D3D3;">
                                                <td class="w-5 img-fluid"><img style="height:50px;" src="{{ asset('assets') }}/img/icons/{{ $site->type }}_icon.png" alt="{{ $site->type }}"></td>
                                                <td class="w-40 align-middle text-left text-md"><b><a href="/SiteDetails/{{ $site->id }}"> {{ $site->name }}</a></b></td> 
                                                @foreach($locations as $location)
                                                    @if($location->short == $site->location)
                                                        <td class="w-20 align-middle text-left text-md"><b>{{ $location->location }}</b></td> 
                                                    @endif
                                                @endforeach
                                                

                                                <?php 
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
                                                ?>
                                                <td class="w-25 align-middle text-left text-md"><b>{{ $level}}</b></td> 
                                        
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
    
    <script src="https://api.mapbox.com/mapbox-gl-js/v2.6.1/mapbox-gl.js"></script>
    <link href="https://api.mapbox.com/mapbox-gl-js/v2.6.1/mapbox-gl.css" rel="stylesheet" />

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

                    foreach($sites as $site) {
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
    @endpush
</x-page-template>

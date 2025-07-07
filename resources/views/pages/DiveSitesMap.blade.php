<x-page-template bodyClass='g-sidenav-show  bg-gray-200'>
    <x-auth.navbars.sidebar activePage="DiveSites" activeItem="DiveSitesMap" activeSubitem=""></x-auth.navbars.sidebar>
    
    
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <!-- Navbar -->
        <x-auth.navbars.navs.auth pageTitle="Dive Sites Map"></x-auth.navbars.navs.auth>
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

        <div class="d-none" data-color="info" id="sidebarColorDiv"></div> {{--Set active element on sidenav bar color (goes together wih JS below--}}



            <div class="page-header min-height-200 max-height-300 border-radius-xl mt-4 mx-n2" style="background-image: url('/assets/img/illustrations/dive_sites.webp');">
                <span class="mask  bg-gradient-info  opacity-4"></span>
            </div>

            <div class="card p-0 position-relative mt-n5 mx-1 z-index-2 mb-4">
                
                    <div class="p-0 mt-0 mx-2 border-radius-lg py-3 pe-1">
                        <div style="float: left;">
                            <h1 class="card-title text-info mx-3 mt-0">Dive Sites Map</h1>
                        </div>

                    </div>
                </div>
            </div>

            <div class="row">
                
                <div class="col-md-12">    
                    <div class="card p-0 position-relative mt-3 mx-3 z-index-2 mb-4">
                        <div class="border-radius-xl">
                            <div id="map" style="width: 100%; height: 600px; border-radius: 1rem; background-color: #f0f0f0; padding: 1rem;"></div>
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
    <script src="https://api.mapbox.com/mapbox-gl-js/v2.6.1/mapbox-gl.js"></script>
    <link href="https://api.mapbox.com/mapbox-gl-js/v2.6.1/mapbox-gl.css" rel="stylesheet" />

    <script>
        mapboxgl.accessToken = 'pk.eyJ1IjoicHN0cmlrYSIsImEiOiJjbHZsc2p2bXcyY240MmtuMDcydHJzd2UxIn0.KBf79cvk47WseBc9rNu6gQ'; // Replace with your actual access token

        const map = new mapboxgl.Map({
            container: 'map',
            style: 'mapbox://styles/pstrika/clwqz4fds03gv01qo9d4w3g21', // Choose a map style
            //center: [-80.07488399442913, 26.137643513173536], // Set the initial center coordinates
            center: [ {{ $centerLon }}, {{ $centerLat }}],
            zoom: 11, // Set the initial zoom level
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

            {{--
            map.addLayer({
                id: 'bathymetry-layer',
                type: 'fill',
                source: {
                    type: 'vector',
                    url: 'mapbox://mapbox.mapbox-bathymetry-v2', // Use the tileset URL
                },
                'source-layer': 'depth', // Specify the layer within the tileset
                paint: {
                    'fill-color': '#0074D9', // Customize the fill color
                    'fill-opacity': 0.5, // Adjust opacity
                },
            });--}}
            
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
    @endpush
</x-page-template>

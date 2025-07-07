<x-page-template bodyClass='g-sidenav-show  bg-gray-200' :SEO="$SEO">
    <x-auth.navbars.sidebar activePage="diveOperators" activeItem="diveOperators" activeSubitem=""></x-auth.navbars.sidebar>
    
    
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <!-- Navbar -->
        <x-auth.navbars.navs.auth pageTitle="Dive Operators"></x-auth.navbars.navs.auth>
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
        



            <div class="page-header min-height-200 max-height-300 border-radius-xl mt-4 mx-n2" style="background-image: url('/assets/img/illustrations/operators.webp');">
                <span class="mask  bg-gradient-info  opacity-4"></span>
            </div>

            <div class="card p-0 position-relative mt-n5 mx-1 z-index-2 mb-4">
                
                    <div class="p-0 mt-0 mx-2 border-radius-lg py-3 pe-1">
                        <div style="float: left;">
                            <h1 class="card-title text-info mx-3 mt-0">Dive Operators</h1>
                        </div>

                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    
                    <div class="nav-wrapper position-relative end-0">
                        <ul class="nav nav-pills nav-fill p-1" role="tablist" id="nav-tabs">
                            <li class="nav-item">
                                <a class="nav-link mb-0 px-0 py-1 active" href="#" data-tag="all" data-lat="25.9379" data-lng="-80.9248" data-zoom="6.7">All</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link mb-0 px-0 py-1" href="#" data-tag="F.Lauderdale" data-lat="26.22231" data-lng="-80.14338" data-zoom="10">F.Lauderdale</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link mb-0 px-0 py-1" href="#" data-tag="WPB-Jupiter" data-lat="26.8000" data-lng="-80.0672" data-zoom="9">WPB-Jupiter</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link mb-0 px-0 py-1" href="#" data-tag="Miami" data-lat="25.793449" data-lng="-80.139198" data-zoom="10">Miami</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link mb-0 px-0 py-1" href="#" data-tag="Upper Keys" data-lat="25.05" data-lng="-80.54728" data-zoom="10">Upper Keys</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link mb-0 px-0 py-1" href="#" data-tag="Lower Keys" data-lat="24.65524" data-lng="-81.60163" data-zoom="10">Lower Keys</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link mb-0 px-0 py-1" href="#" data-tag="Middle Keys" data-lat="24.7263" data-lng="-81" data-zoom="11">Middle Keys</a>
                            </li>
                        </ul>
                    </div>
               
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card p-0 position-relative mt-3 mx-2 z-index-2 mb-4">
                        <div class="card-body">
                            <div class="text-center text-sm" style="background-color: #EBFBFF;" >
                                Operators offering ONLY private charters will show in light blue
                            </div>
                            <div class="table-responsive">
                                <table>
                                    <thead class="text-info">
                                        <th class="align-top">
                                            
                                        </th>
                                        <th class="align-middle text-left">
                                            Name
                                        </th>
                                        <th class="align-middle text-center text-xs">
                                            Trip Price (USD)
                                        </th>
                                        <th class="align-middle text-center text-xs">
                                            Location
                                        </th>
                                        <th class="align-middle text-center text-xs">
                                            Tec?
                                        </th>
                                        <th class="align-middle text-center text-xs">
                                            Phone
                                        </th>
                                        <th class="align-middle text-center text-xs">
                                            email
                                        </th>
                                        <th class="align-middle text-center text-xs">
                                            web
                                        </th>
                                    </thead>
                                    <tbody>
                                        @foreach($operators as $operator)
                                            <?php
                                                $data = json_decode($operator->tripPrice, true);        
                                                $price = "-";
                                                foreach ($data as $item) {
                                                    if ($item['type'] === "Recreational - 2 Tank") {
                                                        $price = $item['price'];
                                                        break;
                                                    }
                                                    elseif ($item['type'] === "Recreational - 3 Tank") {
                                                        $price = $item['price'];
                                                        break;
                                                    }
                                                    elseif ($item['type'] === "Recreational - 2 Tank Reef") {
                                                        $price = $item['price'];
                                                        break;
                                                    }
                                                    elseif ($item['type'] === "Private - Half Day") {
                                                        $price = $item['price'];
                                                        break;
                                                    }
                                                }
                                            ?>
                                            <tr style="border-bottom: 1px solid #D3D3D3;{{ $operator->private ? ' background-color: #EBFBFF;' :''}}" data-tag="{{ $operator->locationArea}}">
                                                <td class="w-10"><img src="{{ asset('assets') }}{{ $operator->logoUrl}}" alt="img-blur-shadow" class="img-fluid align-items-center border-radius-lg"></td>
                                                <td class="text-sm"><a href="OperatorDetails/{{ $operator->id }}"> {{ $operator->operatorName }}</a></td>
                                                <td class="w-10 text-center text-sm">${{ $price }}</td>
                                                <td class="w-10 text-center text-sm">{{ $operator->cityAddress }}</td>
                                                <td class="w-5 align-middle text-center text-sm"><i class="material-icons">{{ ($operator->tec ? "check" : "block") }}</i></td>
                                                <td class="w-20 text-center text-sm">{{ $operator->phone }}</td>
                                                <td class="align-middle text-center text-sm"><a href="mailto:{{ $operator->email}}"><i class="material-icons">mail</a></td>
                                                <td class="align-middle text-center text-sm"><a href="{{ $operator->webSite}}" target="_blank"><i class="material-icons">link</a></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>    
                
                <div class="col-md-6">
                    <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                        <div class="card-body">
                            <div class="border-radius-xl">
                                <div id="map" style="width: 100%; height: 500px; border-radius: 1rem; background-color: #f0f0f0; padding: 1rem;"></div>
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
    
    <script src="{{ asset('assets') }}/js/plugins/jquery-3.6.0.min.js" type="text/javascript"></script>
    
    <script src="https://api.mapbox.com/mapbox-gl-js/v2.6.1/mapbox-gl.js"></script>
    <link href="https://api.mapbox.com/mapbox-gl-js/v2.6.1/mapbox-gl.css" rel="stylesheet" />

    <script>
        mapboxgl.accessToken = 'pk.eyJ1IjoicHN0cmlrYSIsImEiOiJjbHZsc2p2bXcyY240MmtuMDcydHJzd2UxIn0.KBf79cvk47WseBc9rNu6gQ';

        const operators = [
            <?php
            foreach($operators as $operator)
                echo "{ streetAddress: '" . $operator->streetAddress . "', cityAddress: '" . $operator->cityAddress . "', stateAddress: '" . $operator->stateAddress . "', zipAddress: '" . $operator->zipAddress . "', operatorName: '" . addslashes($operator->operatorName) . "' },";
            
            ?>
            // Add more operators as needed
        ];

        const map = new mapboxgl.Map({
            container: 'map',
            style: 'mapbox://styles/pstrika/clx0wsurg01yj01qmhmvb9pd6',
            center: [-80.9248, 25.9379], // Center of the US
            zoom: 6.7, // Zoom level to show the entire US
            projection: 'albers'
        });


        operators.forEach(operator => {
            const address = `${operator.streetAddress}, ${operator.cityAddress}, ${operator.stateAddress} ${operator.zipAddress}`;

            fetch(`https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(address)}.json?access_token=${mapboxgl.accessToken}`)
                .then(response => response.json())
                .then(data => {
                    const [lng, lat] = data.features[0].center;
                    console.log(`Latitude: ${lat}, Longitude: ${lng}`);

                    const marker = new mapboxgl.Marker()
                        .setLngLat([lng, lat])
                        .addTo(map);

                    const popup = new mapboxgl.Popup().setText(operator.operatorName);
                    marker.setPopup(popup);
                })
                .catch(error => console.error('Error fetching geocoding data:', error));
        });
    </script>

<script>
        document.addEventListener('DOMContentLoaded', function() {
            const navLinks = document.querySelectorAll('#nav-tabs .nav-link');
            navLinks.forEach(link => {
                link.addEventListener('click', function(event) {
                    event.preventDefault();
                    const tag = this.getAttribute('data-tag');
                    const lat = parseFloat(this.getAttribute('data-lat'));
                    const lng = parseFloat(this.getAttribute('data-lng'));
                    const zoom = parseFloat(this.getAttribute('data-zoom'));
                    filterTable(tag);
                    map.setCenter([lng, lat]); // Update map center
                    map.setZoom(zoom); // Update map zoom
                });
            });
        });

        function filterTable(tag) {
            console.log('filterTable called with tag:', tag); // Debugging line
            const rows = document.querySelectorAll('table tbody tr');
            rows.forEach(row => {
                if (tag === 'all' || row.getAttribute('data-tag') === tag) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
  
    @endpush
</x-page-template>

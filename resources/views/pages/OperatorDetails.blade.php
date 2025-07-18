<x-page-template bodyClass='g-sidenav-show  bg-gray-200' :SEO="$SEO">
    <x-auth.navbars.sidebar activePage="operators" activeItem="operators" activeSubitem=""></x-auth.navbars.sidebar>
    
    
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
            
            <div class="page-header min-height-200 max-height-300 border-radius-xl mt-4 mx-0" style="background-image: url('/assets/img/illustrations/operators.webp');">
                <span class="mask  bg-gradient-info  opacity-4"></span>
            </div>

            <div class="card p-0 position-relative mt-n5 mx-3 z-index-2 mb-4">
                
                    <div class="p-0 mt-0 mx-2 border-radius-lg py-3 pe-1">
                        <div style="float: left;">
                            <h1 class="card-title text-info mx-3 mt-0">{{ $operator->operatorName }}</h1>
                            
                        </div>

                        @if(auth()->user()->isNotGuest())
                            <div style="float: right;">
                                <a href="{{ route('ToggleFav', ['id' => $operator->id]) }}"><i class="justify-content-bottom align-bottom material-icons text-info opacity-10" style="font-size: 50px;">{{ $fav ? "favorite" : "favorite_border"}}</i></a>
                            </div>
                        @endif

                    </div>
                </div>
            </div>

            <div class="row">
                
                
                {{-- Card Dive Center --}}
                <div class="col-md-4">             
                    <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h2 class="card-title text-white mx-4">Contact</h4>
                                <div class="table-responsive"></div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table align-items-center mb-0"> 
                                    <tbody>
                                        <tr><td class="text-center"><img src="{{ asset('assets') }}{{ $operator->logoUrl}}" alt="img-blur-shadow" class="img-fluid"></td></tr> 
                                        <tr><td class="text-uppercase text-secondary text-xl font-weight-bolder opacity-7 text-center" style="border: none;"> {{ $operator->operatorName}}</td> </tr>

                                        <tr> <td>
                                            <table class="table align-items-center mb-0">
                                            
                                                <tr class="align-top"><td class="text-secondary text-end text-lg font-weight-bolder opacity-7">Address</td>
                                                <td class="align-middle text-left text-wrap text-sm"><b>{{ $operator->streetAddress}}<br>{{ $operator->cityAddress}}, {{ $operator->stateAddress}} {{ $operator->zipAddress}} </b></td> </tr>
                                            </table>
                                            <table class="table align-items-center mb-0">
                                                <tr><td class="text-center">
                                                {{--<div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">--}}
                                                    <div class="border-radius-xl">
                                                        <div id="map" style="width: 100%; height: 250px; border-radius: 1rem; background-color: #f0f0f0; padding: 1rem;"></div>
                                                    </div>

                                                </td></tr>
                                            </table>
                                            <table class="table align-items-center mb-0">

                                                <tr><td class="text-secondary text-end text-lg font-weight-bolder opacity-7">Phone</td>
                                                <td class="align-middle text-left text-sm"><b>{{ $operator->phone}}</b></td> </tr>
                                                
                                                @if($operator->email)
                                                    <tr><td class="text-secondary text-end text-lg font-weight-bolder opacity-7">email</td>
                                                    <td class="align-middle text-left text-sm"><b><a href="mailto:{{ $operator->email}}">{{ $operator->email}}</a></b></td> </tr>
                                                @endif

                                                @if($operator->marinaAddress)
                                                    <tr><td class="text-secondary text-end text-lg font-weight-bolder opacity-7">Marina address</td>
                                                    <td class="align-middle text-wrap text-sm"><b>{{ $operator->marinaAddress}}</b></td> </tr>
                                                @endif
                                                
                                                @if($operator->webSite)
                                                    <tr><td class="text-secondary text-end text-lg font-weight-bolder opacity-7">Website</td>
                                                    <td class="align-middle text-left text-sm"><b><a href="{{ $operator->webSite}}">here</a></b></td> </tr>
                                                @endif

                                                @if($operator->waiverLink)
                                                    <tr><td class="text-secondary text-end text-lg font-weight-bolder opacity-7">Online waiver</td>
                                                    <td class="align-middle text-left text-sm"><b><a href="{{ $operator->waiverLink}}">here</a></b></td> </tr>
                                                @endif
                                            </table>

                                            <tr><td class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center" style="border: none;">Hours of Operation</td> </tr>
                                            <table class="table align-items-center mb-0">
                                                <tbody>
                                                    @php
                                                        $hoursOfOperation = json_decode($operator->hourOfOperation, true);
                                                    @endphp
                                                    <tr>
                                                        @foreach($hoursOfOperation as $hourOfOperation)
                                                            <td class="align-middle text-center text-sm">{{ $hourOfOperation['day'] }}</td>
                                                        @endforeach
                                                    </tr>
                                                    <tr>
                                                        @foreach($hoursOfOperation as $hourOfOperation)
                                                            <td class="align-middle text-center text-sm">{{ $hourOfOperation['hours'] }}</td>
                                                        @endforeach
                                                    </tr>

                                                    
                                                    
                                                </tbody>
                                            </table>
                                        </td></td>
                                        
                                        
                                                   
                                    </tbody>
                                </table>
                            </div>    
                        </div>
                    </div>
                </div>
                {{-----------------------------}}

                {{-- Boats --}}
                <div class="col-md-4">             
                    <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h2 class="card-title text-white mx-4">Boats</h2>
                                <div class="table-responsive"></div>
                            </div>
                        </div>
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
                                                        
                                                    @foreach ($boats as $boat)    
                                                        <div class="carousel-item {{ $first ? "active" : "" }}">
                                                            @php
                                                                $first = false;
                                                            @endphp
                                                            <div class="page-header min-vh-25 m-3 border-radius-xl" style="background-image: url('{{ asset('assets') }}{{ $boat->pic}}');">
                                                            
                                                                <div class="container">
                                                                    <div class="row">
                                                                        <div class="my-auto">
                                                                            <h3 class="text-white mt-10 fadeIn1 fadeInBottom ">{{ $boat->name }}</h3>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <table class="table align-items-center mb-0">
                                                                <tbody>
                                                                    @if($boat->type)
                                                                        <tr class="align-top"><td class="text-secondary text-end text-lg font-weight-bolder opacity-7">Type</td>
                                                                        <td class="align-middle text-left text-wrap text-sm"><b>{{ $boat->type }}</b></td> </tr>
                                                                    @endif
                                                                    
                                                                    @if($boat->capacity)
                                                                        <tr><td class="text-secondary text-end text-lg font-weight-bolder opacity-7">Capacity Rec</td>
                                                                        <td class="align-middle text-left text-sm"><b>{{ $boat->capacity }} divers</b></td> </tr>
                                                                    @endif

                                                                    @if($boat->tec_capacity)
                                                                        <tr><td class="text-secondary text-end text-lg font-weight-bolder opacity-7">Capacity Tec</td>
                                                                        <td class="align-middle text-left text-sm"><b>{{ $boat->tec_capacity }} divers</b></td> </tr>
                                                                    @endif
                                                                    
                                                                    @if($boat->manufacturer)
                                                                        <tr><td class="text-secondary text-end text-lg font-weight-bolder opacity-7">Manufacturer</td>
                                                                        <td class="align-middle text-left text-sm"><b>{{ $boat->manufacturer }}</b></td> </tr>
                                                                    @endif

                                                                    @if($boat->beam)
                                                                        <tr><td class="text-secondary text-end text-lg font-weight-bolder opacity-7">Beam</td>
                                                                        <td class="align-middle text-wrap text-sm"><b>{{ $boat->beam }} ft</b></td> </tr>
                                                                    @endif
                                                                    
                                                                    @if($boat->length)
                                                                        <tr><td class="text-secondary text-end text-lg font-weight-bolder opacity-7">Lentgh</td>
                                                                        <td class="align-middle text-wrap text-sm"><b>{{ $boat->length }} ft</b></td> </tr>
                                                                    @endif

                                                                    @if($boat->speed)
                                                                        <tr><td class="text-secondary text-end text-lg font-weight-bolder opacity-7">Speed</td>
                                                                        <td class="align-middle text-wrap text-sm"><b>{{ $boat->speed }} knots</b></td> </tr>
                                                                    @endif

                                                                    @if($boat->power)
                                                                        <tr><td class="text-secondary text-end text-lg font-weight-bolder opacity-7">Power</td>
                                                                        <td class="align-middle text-wrap text-sm"><b>{{ $boat->power }}</b></td> </tr>
                                                                    @endif
                                                                </tbody>
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
                                        </td></tr>
                                    </tbody>    
                                </table>
                            </div>    
                        </div>
                    </div>
                </div>
                {{-----------------------------}}
                
                
                
                {{-- Card Gas Fills--}}
                <div class="col-md-4">
                    <div class="col-md-12">             
                        <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                            <div class="card-header p-0 mt-n4 mx-3">
                                <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                    <h2 class="card-title text-white mx-4">Gas Fills Offered</h2>
                                    <div class="table-responsive"></div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table align-items-center mb-0"> 
                                        <tbody>

                                            <tr> <td>

                                                <table class="table align-items-center mb-0">
                                                    <tbody>
                                                        <tr>
                                                            <td class="align-middle text-center text-sm">Air</td>
                                                            <td class="align-middle text-center text-sm">Nitrox</td>
                                                            <td class="align-middle text-center text-sm">Trimix</td>
                                                            <td class="align-middle text-center text-sm">Oxygen</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="align-middle text-center text-sm"> <i class="material-icons">{{ ($operator->onSiteFillAir ? "check" : "block") }}</i></td>
                                                            <td class="align-middle text-center text-sm"> <i class="material-icons">{{ ($operator->onSiteFillNitrox ? "check" : "block") }}</i></td>
                                                            <td class="align-middle text-center text-sm"> <i class="material-icons">{{ ($operator->onSiteFillTrimix ? "check" : "block") }}</i></td>
                                                            <td class="align-middle text-center text-sm"> <i class="material-icons">{{ ($operator->onSiteFillO2 ? "check" : "block") }}</i></td>  
                                                        </tr>
                                                    </tbody>

                                                </table>
                                            </td></tr>

                                        </tbody>    
                                    </table>
                                </div>    
                            </div>
                        </div>
                    </div>
                
                    {{-----------------------------}}
                    {{-- Card Prices--}}
                    <div class="col-md-12">             
                        <div class="card p-0 position-relative mt-5 mx-0 z-index-2 mb-4">
                            <div class="card-header p-0 mt-n4 mx-3">
                                <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                    <h2 class="card-title text-white mx-4">Trip Prices</h2>
                                    <div class="table-responsive"></div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table align-items-center mb-0"> 
                                        <tbody>
                                            @php
                                                $tripPrices = json_decode($operator->tripPrice, true);
                                            @endphp

                                            <tr><td>
                                                <table class="table align-items-center mb-0">
                                                    <tr class="align-top">
                                                        <td class="text-info text-lg font-weight-bolder opacity-7">Type</td>
                                                        <td class="text-info align-middle text-left text-wrap text-lg">Price</td>
                                                    </tr>
                                                    @foreach($tripPrices as $tripPrice)
                                                    <tr class="align-top" style="border-bottom: 1px solid #D3D3D3;">
                                                        <td class="text-lg font-weight-bolder opacity-7">{{ $tripPrice['type'] }}</td>
                                                        <td class="align-middle text-left text-wrap text-lg">${{ $tripPrice['price'] }}</td>
                                                    </tr>
                                                    @endforeach
                                                </table>
                                            
                                            </td></td>       
                                        </tbody>
                                    </table>
                                </div>    
                            </div>
                        </div>
                    </div>
                    {{-----------------------------}}
                </div>

                @if($trips->isNotEmpty())
                <div class="col-md-12">             
                    <div class="card p-0 position-relative mt-5 mx-0 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h2 class="card-title text-white mx-4">Dive Calendar</h2>
                                <div class="table-responsive"></div>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <table>
                                <tr><td class="text-start text-sm w-1"> 
                                    <span class="badge badge-md bg-gradient-secondary text-white mx-2">Recreational</span>
                                    <span class="badge badge-md bg-gradient-success text-white">Technical</span>
                                </td></tr>
                                <tr><td><p class="text-xs font-weight-bold mb-0 mt-0 mx-2">reference</p></td></tr>
                            </table>

                            <div class="calendar" data-bs-toggle="calendar" id="calendar"></div>
                        </div>
                    </div>
                </div>
                @endif
                {{--card top sites--}}
                @if( $topSites != null and count($topSites))
                    <div class="col-md-6">
                        <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                            <div class="card-header p-0 mt-n4 mx-3">
                                <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                    <h2 class="card-title text-white mx-4">Most visited sites</h2>
                                    <div class="table-responsive"></div>
                                </div>
                            </div>
                            <div class="card-body mt-4">
                                <div class="table-responsive">
                                    <table style="display: block; height: 300px; overflow-y: scroll">
                                        <thead class="text-info">
                                            <th class="align-top text-center">Rank</th> 
                                            <th class="align-top">Type</th>
                                            <th class="px-4 align-top">Name</th> 
                                            <th class="px-4 align-top">Level</th>
                                        </thead>
                                        <tbody> 
                                            @foreach($topSites as $i => $site)
                                                
                                                <tr style="border-bottom: 1px solid #D3D3D3;" class="justify-content-center align-middle">
                                                    <td class="px-4 text-center">{{ $i+1 }}</td>
                                                    <td class="w-5 text-center align-middle"><img src="{{ asset('assets') }}/img/icons/{{ $site->type }}_icon.png" height="35"></td>
                                                    <td class="px-4"><a href="/SiteDetails/{{ $site->id }}">{{ $site->name }}</a></td>
                                                    <td class="w-5 text-center align-middle"><img src="{{ asset('assets') }}/img/icons/icons_level_{{ $site->level }}.png" height="25"></td>
                                                </tr>
                                        
                                            @endforeach          
                                        </tbody>
                                    </table>
                                </div>   
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                @else
                    <div class="col-md-12">
                @endif


                             
                        <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                            <div class="card-header p-0 mt-n4 mx-3">
                                <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                    <h2 class="card-title text-white mx-4">Description</h4>
                                    <div class="table-responsive"></div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table align-items-center mb-0"> 
                                        <tbody>
                                            <tr> <td>
                                                <p class="text-justify-left text-wrap">{{ $operator->desc }}</p>
                                            </td></tr>

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
    
    <script src="{{ asset('assets') }}/js/plugins/jquery-3.6.0.min.js" type="text/javascript"></script>
    <script src="{{ asset('assets') }}/js/plugins/flatpickr.min.js"></script>
    <script src="https://api.mapbox.com/mapbox-gl-js/v2.6.1/mapbox-gl.js"></script>
    <link href="https://api.mapbox.com/mapbox-gl-js/v2.6.1/mapbox-gl.css" rel="stylesheet" />
    <script src="/assets/js/plugins/fullcalendar.min.js"></script>
    <link href="{{ asset("assets") }}/css/calendar-buttons.css" rel="stylesheet" />

    <script>
        mapboxgl.accessToken = 'pk.eyJ1IjoicHN0cmlrYSIsImEiOiJjbHZsc2p2bXcyY240MmtuMDcydHJzd2UxIn0.KBf79cvk47WseBc9rNu6gQ';
        const address = '{{ $operator->streetAddress}}, {{ $operator->cityAddress}}, {{ $operator->stateAddress}} {{ $operator->zipAddress}}';
        

        fetch(`https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(address)}.json?access_token=${mapboxgl.accessToken}`)
        .then(response => response.json())
        .then(data => {
            const [lng, lat] = data.features[0].center;
            console.log(`Latitude: ${lat}, Longitude: ${lng}`);
            // Now you have the coordinates!
            const map = new mapboxgl.Map({
                container: 'map',
                style: 'mapbox://styles/pstrika/clx0wsurg01yj01qmhmvb9pd6',
                center: [lng, lat], // Set your desired center coordinates
                zoom: 12, // Set your desired zoom level
                projection: 'albers'
            });

            const marker1 = new mapboxgl.Marker()
                .setLngLat([lng, lat])
                .addTo(map);

            const popup = new mapboxgl.Popup().setText("{{ $operator->operatorName }}"); // Set your label text
            marker1.setPopup(popup);

        })
        .catch(error => console.error('Error fetching geocoding data:', error));

        
        
    </script>

    <script>
    flatpickr("#datePicker", {
        altInput: true,
        altFormat: "F j, Y",
        dateFormat: "Y-m-d",
        minDate: "today",
        
        maxDate: new Date().fp_incr(90),
        onChange: function(selectedDates, dateStr, instance) {
            window.location.href = `/Trips/${dateStr}`;
        }
    });
    </script>

    <script>
        function getResponsiveView() {
            const width = window.innerWidth;
            if (width >= 1200) return 'dayGridMonth';     // Large screens
            if (width >= 768) return 'dayGridWeek';       // Medium screens
            return 'dayGridThreeDay';                    // Small screens
        }

        const todayDate = new Date().toISOString().split('T')[0];
        var calendar = new FullCalendar.Calendar(document.getElementById("calendar"), {
        dateClick: function(info) {
            var link = '/Trips/' + info.dateStr;
            window.location.href = link;
        },
        
        initialView: getResponsiveView(),
        windowResize: function(view) {
            calendar.changeView(getResponsiveView());
        },
        firstDay: {{ auth()->user()->firstDayOfWeek }},
        contentHeight: 'auto',
        headerToolbar: {
            start: '', //'title', // will normally be on the left. if RTL, will be on the right
            center: 'title',
            end: 'prev,next today'//'today prev,next' // will normally be on the right. if RTL, will be on the left
        },
        selectable: true,
        editable: false,
        initialDate: todayDate,
        events: [
            @php
                foreach($trips as $trip) {
                    // fix the ' problem
                    $tripName = str_replace("'", "\\'", $trip->tripName);
                    echo "{";
                    echo "title: '" . (strstr($tripName, '(', true) ? strstr($tripName, '(', true) : $tripName) ."',";
                    echo "start: '" . $trip->date . " " . $trip->departureTime ."',";
                    echo "url: '/TripDetails/" . str($trip->id) . "',";
                    if($trip->tripType == "Technical")
                        echo "className: 'bg-gradient-success text-white tripType=1 isAvail=" . (($trip->tripFreeSpots > 0) ? "Y" : "N")  . "' },";
                    else
                        echo "className: 'bg-gradient-secondary text-white tripType=0 isAvail=" . (($trip->tripFreeSpots > 0) ? "Y" : "N")  . "' },";
                }
            @endphp
            

        ],
        views: {
            dayGridThreeDay: {
                type: 'dayGrid',
                duration: { days: 3 },
                buttonText: '3 day',
                titleFormat: {
                    month: "long",
                    year: "numeric",
                    day: "numeric"
                }
            },
            month: {
            titleFormat: {
                month: "long",
                year: "numeric"
            }
            },
            agendaWeek: {
            titleFormat: {
                month: "long",
                year: "numeric",
                day: "numeric"
            }
            },
            agendaDay: {
            titleFormat: {
                month: "short",
                year: "numeric",
                day: "numeric"
            }
            }
        },
        });

        calendar.render();


    </script>

    @endpush
</x-page-template>

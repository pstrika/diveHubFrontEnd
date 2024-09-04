<x-page-template bodyClass='g-sidenav-show  bg-gray-200'>
    <x-auth.navbars.sidebar activePage="operators" activeItem="operators" activeSubitem=""></x-auth.navbars.sidebar>
    
    
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <!-- Navbar -->
        <x-auth.navbars.navs.auth pageTitle="Dive Operators"></x-auth.navbars.navs.auth>
        <!-- End Navbar -->
        <div class="container-fluid py-0">

            <div class="page-header min-height-200 max-height-300 border-radius-xl mt-4 mx-0" style="background-image: url('/assets/img/illustrations/operators.webp');">
                <span class="mask  bg-gradient-info  opacity-4"></span>
            </div>

            <div class="card p-0 position-relative mt-n5 mx-3 z-index-2 mb-4">
                
                    <div class="p-0 mt-0 mx-2 border-radius-lg py-3 pe-1">
                        <div style="float: left;">
                            <h2 class="card-title text-info mx-3 mt-0">{{ $operator->operatorName }}</h2>
                        </div>

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
                                                    <div class="card-header p-0 mt-0 mx-3">
                                                        {{--<iframe class="img-fluid border-radius-lg" allow-same-origin="" allow-scripts="" allowfullscreen="" alt="EarthCam Video Player Embed" autoplay="" frameborder="0" height="450" id="iframe" marginheight="0" marginwidth="0" scrolling="no" src="//www.earthcam.com/js/video/embed.php?type=h264&amp;vid=windjammerHD2.flv&amp;w=auto&amp;company=Windjammer&amp;timezone=America/New_York&amp;metar=KFLL&amp;ecn=1&amp;requested_version=current" style="top:0;left:0;width:10px;min-width:100%;*width:100%;height:200px;" width="800"></iframe>--}}
                                                        <iframe style="top:0;left:0;width:10px;min-width:100%;*width:100%;height:200px;" src="{{ $operator->mapUrl }}" width="600" height="450" style="border:2;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                                                    </div>

                                                </td></td>
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
                                <h2 class="card-title text-white mx-4">Boats</h4>
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
                                                    <div class="carousel-item {{ ($first ? "active" : "") }}">
                                                        @php
                                                            $first = false;
                                                        @endphp
                                                        <div class="page-header min-vh-25 m-3 border-radius-xl" style="background-image: url('{{ asset('assets') }}{{ $boat->pic}}');">
                                                        
                                                        <div class="container">
                                                            <div class="row">
                                                            <div class="my-auto">
                                                                <h3 class="text-white mt-10 fadeIn1 fadeInBottom ">{{ $boat->name }}</h4>
                                                                
                                                                
                                                            </div>
                                                            </div>
                                                        </div>
                                                        </div>
                                                        {{--<h4 class="text-info mb-0 fadeIn1 fadeInBottom align-bottom text-center"> {{ $boat->name }}</h4>--}}

                                                        <table class="table align-items-center mb-0">
                                                    
                                                            @if($boat->type)
                                                                <tr class="align-top"><td class="text-secondary text-end text-lg font-weight-bolder opacity-7">Type</td>
                                                                <td class="align-middle text-left text-wrap text-sm"><b>{{ $boat->type }}</b></td> </tr>
                                                            @endif
                                                            
                                                            @if($boat->capacity)
                                                                <tr><td class="text-secondary text-end text-lg font-weight-bolder opacity-7">Capacity</td>
                                                                <td class="align-middle text-left text-sm"><b>{{ $boat->capacity }} divers</b></td> </tr>
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
                {{-----------------------------}}
                
                
                
                <div class="col-md-4">
                {{-- Card Gas Fills--}}
                    <div class="col-md-12">             
                        <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                            <div class="card-header p-0 mt-n4 mx-3">
                                <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                    <h2 class="card-title text-white mx-4">Gas Fills Offered</h4>
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
                                    <h2 class="card-title text-white mx-4">Trip Prices</h4>
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

                                            <tr> <td>
                                                <table class="table align-items-center mb-0">
                                                
                                                    <tr class="align-top"><td class="text-info text-lg font-weight-bolder opacity-7">Type</td>
                                                    <td class="text-info align-middle text-left text-wrap text-lg">Price</td> </tr>
                                                    @foreach($tripPrices as $tripPrice)
                                                    <tr class="align-top" style="border-bottom: 1px solid #D3D3D3;"><td class="text-lg font-weight-bolder opacity-7">{{ $tripPrice['type'] }}</td>
                                                    <td class="align-middle text-left text-wrap text-lg">${{ $tripPrice['price'] }}</td> </tr>
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

                <div class="col-md-12">             
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
    
    
    <x-plugins></x-plugins>
    
    @push('js')
    
    <script src="{{ asset('assets') }}/js/plugins/flatpickr.min.js"></script>

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

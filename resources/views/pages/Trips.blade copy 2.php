<x-page-template bodyClass='g-sidenav-show  bg-gray-200'>
    <x-auth.navbars.sidebar activePage="trips" activeItem="trips" activeSubitem=""></x-auth.navbars.sidebar>
    
    
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <!-- Navbar -->
        <x-auth.navbars.navs.auth pageTitle="Dive Trips"></x-auth.navbars.navs.auth>
        <!-- End Navbar -->
        <div class="container-fluid py-0">
            
            <div class="page-header min-height-300 border-radius-xl mt-4 mx-0" style="background-image: url('/assets/img/illustrations/divers_jumping.avif');">
                <span class="mask  bg-gradient-info  opacity-4"></span>
            </div>

            <div class="card p-0 position-relative mt-n7 mx-3 z-index-2 mb-4">
                
                    <div class="p-0 mt-n4 mx-2 border-radius-lg py-3 pe-1">
                    
                        @php
                            $date = new DateTime($trips[0]->date);
                            $dateDayName = $date->format('l');
                            $dateText = $date->format('F\-d, Y');
                        @endphp
                        
                        <div style="float: left;">
                            <h2 class="card-title text-info mx-3 mt-4">{{ $dateDayName }}</h2>
                            <h4 class="card-category text-info mx-3"> {{ $dateText }}</h4>
                        </div>

                        {{-----------------NAV to next day}} --}}
                        <div class="mt-5" style="float: right;">
                            <a type="button" href="/Trips/{{ $previousDay }}/" class="btn btn-info tex-end">
                                <span class="material-icons" style="font-size :30pt;">keyboard_arrow_left</span>
                            </a>
                            <a type="button" href="/Trips/" class="btn btn-info tex-end">
                                <span class="material-icons" style="font-size :30pt;">calendar_today</span>
                            </a>
                            <a type="button" href="/Trips/{{ $nextDay }}/" class="btn btn-info tex-end">
                                <span class="material-icons" style="font-size :30pt;">keyboard_arrow_right</span>
                            </a>
                        </div>
                        <div style="clear: both;"></div>
                    </div>
                </div>
            </div>

            <div class="row">
                {{-- Dive conditions card AM --}}
                <div class="col-md-6 ">             
                    <div class="card p-0 position-relative mt-3 mx-3 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h2 class="card-title text-white mx-4">Morning (AM)</h4>
                                
                                <div class="table-responsive">
                                    
                                </div>       
                                <p class="card-category text-white mx-4">Dive Conditions (beta) - Trips</p>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table align-items-center mb-0">
                                    {{--<thead>
                                            <th class="align-middle text-center text-sm">LOCATION</th>
                                            <th class="align-middle text-center text-sm">FORECAST</th>
                                            <th class="align-middle text-center text-sm">DIVE CONDITIONS</th>   
                                    </thead>--}}
                                    <tbody>
                                        <tr> <td>LOCATION</td>
                                            @foreach($weathers as $weather)
                                                @if($weather->location == "key largo" or $weather->location == "fort lauderdale" or $weather->location == "west palm beach")
                                                    <td class="align-middle text-center text-sm">{{ $weather->location }}</td>
                                                @endif
                                            @endforeach
                                        </tr>
                                        <tr><td>FORECAST</td>
                                            @foreach($weathers as $weather)
                                                @if($weather->location == "key largo" or $weather->location == "fort lauderdale" or $weather->location == "west palm beach")
                                                    <td class="align-middle text-center text-sm"> <img src="{{ $weather->conditions_icon }}" alt="{{ $weather->conditionsAM_text }}"> </td>
                                                @endif
                                            @endforeach
                                        </tr>
                                        <tr><td>DIVE CONDITIONS</td>
                                            @foreach($weathers as $weather)
                                                @if($weather->location == "key largo" or $weather->location == "fort lauderdale" or $weather->location == "west palm beach")
                                                    {{--<td class="align-middle text-center text-sm">{{ $weather->conditionsAM_text }}</td>--}}
                                                    @if($weather->conditionsAM_text == "Poor")
                                                        <td class="align-middle text-center text-sm"> <span class="badge badge-warning">  Poor  </span> </td>
                                                    @elseif($weather->conditionsAM_text == "No Dive")
                                                        <td class="align-middle text-center text-sm"> <span class="badge badge-danger"> No dive </span> </td>
                                                    @elseif($weather->conditionsAM_text == "Average")
                                                        <td class="align-middle text-center text-sm"> <span class="badge badge-secondary"> Average </span> </td>
                                                    @elseif($weather->conditionsAM_text == "Perfect")
                                                        <td class="align-middle text-center text-sm"> <span class="badge badge-success"> Perfect </span> </td>
                                                    @else
                                                        <td class="align-middle text-center text-sm"> <span class="badge badge-info">  Good  </span> </td>
                                                    @endif
                                                @endif
                                            @endforeach
                                        </tr>
                                        <tr> <td> </td></tr>
                                    </tbody>    
                                </table>

                                {{-- Table for filters--}}
                                <table class="table align-items-center mb-0">
                                    <tr>
                                        <td>
                                            <div class="dropdown">
                                                <select class="btn bg-info dropdown-toggle w-100 text-white" type="button" id="filterLocAM" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <option value="all">Show All</option>
                                                    <option value="MIA">miami beach</option>
                                                    <option value="FLL">fort lauderdale</option>
                                                    <option value="POM">pompano beach</option>
                                                    <option value="DEB">deerfield beach</option>
                                                    <option value="WPB">west pam beach</option>
                                                    <option value="KLA">key largo</option>
                                                </select>
                                                <p class="text-xs font-weight-bold mb-0 mt-n3">location</p>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <select class="btn bg-info dropdown-toggle w-100 text-white" type="button" id="filterAvAM" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <option value="all">Show All</option>
                                                    <option value="AVA">Available</option>
                                                    <option value="NAV">Sold-out</option>    
                                                </select>
                                                <p class="text-xs font-weight-bold mb-0 mt-n3">availability</p>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <select class="btn bg-info dropdown-toggle w-100 text-white" type="button" id="filterTypeAM" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <option value="all">Show All</option>
                                                    <option value="TEC">Technical</option>
                                                    <option value="R">Recreational</option>    
                                                </select>
                                                <p class="text-xs font-weight-bold mb-0 mt-n3">type</p>
                                            </div>
                                        </td>
                                    </tr> 
                                </table>
                                {{-------------------------}}


                                <div class="table-responsive">
                                    <table id="tableTripsAM">
                                        <thead class="text-info">
                                            <th class="align-top">
                                                Operator
                                            </th>
                                            <th class="px-4 align-top">
                                                Time
                                            </th>
                                                <th class="py-0">Availability<p class="text-xs mt-0 px-1">click-to-book</p>
                                            </th>
                                            <th class="px-4 align-top">
                                                Site / Trip Name<p class="text-xs mt-0 px-1">click for details</p>
                                            </th>
                                        </thead>
                                        <tbody >
                                            @foreach($trips as $trip)
                                                @php
                                                    $hour = (int)substr($trip->departureTime, 0, 2);
                                                @endphp
                                                @if($hour < 12)
                                                    <tr style="border-bottom: 1px solid #D3D3D3;" data-tag="{{ $trip->tags }}">
                                                        <td class="px-0 py-2 text-sm" style="min-width: 200px;">{{ $trip->operatorName }}</td>
                                                        <td class="px-4">{{ $trip->departureTime }}</td>
                                                        @if($trip->tripFreeSpots == 0)
                                                            <td class="text-center">-</td>
                                                        @else
                                                            <td class="text-center"> <a href="{{ $trip->linkToBook }}" target="_blank">{{ $trip->tripFreeSpots == 1000 ? "Y" : $trip->tripFreeSpots }}</a></td>
                                                        @endif
                                                        <td class="px-4 text-sm"><a href="{{ route('TripDetails', ['tripId' => $trip->id]) }}">{{ $trip->tripName }}<a></td>
                                                    </tr>
                                                @endif
                                            @endforeach          
                                        </tbody>
                                    </table>
                                </div>
                            </div>    
                        </div>
                    </div>
                </div>
                {{-----------------------------}}
                
                {{-- Dive conditions card PM --}}
                <div class="col-md-6">             
                    <div class="card p-0 position-relative mt-3 mx-3 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h2 class="card-title text-white mx-4">Afternoon/Evening (PM)</h2>
                                
                                <div class="table-responsive">
                                    
                                </div>       
                                <p class="card-category text-white mx-4">Dive Conditions (beta) - Trips</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                
                                <table class="table align-items-center mb-0">
                                    {{--<thead>
                                            <th class="align-middle text-center text-sm">LOCATION</th>
                                            <th class="align-middle text-center text-sm">FORECAST</th>
                                            <th class="align-middle text-center text-sm">DIVE CONDITIONS</th>   
                                    </thead>--}}
                                    <tbody>
                                        <tr> <td>LOCATION</td>
                                            @foreach($weathers as $weather)
                                                @if($weather->location == "key largo" or $weather->location == "fort lauderdale" or $weather->location == "west palm beach")
                                                    <td class="align-middle text-center text-sm">{{ $weather->location }}</td>
                                                @endif
                                            @endforeach
                                        </tr>
                                        <tr><td>FORECAST</td>
                                            @foreach($weathers as $weather)
                                                @if($weather->location == "key largo" or $weather->location == "fort lauderdale" or $weather->location == "west palm beach")
                                                    <td class="align-middle text-center text-sm"> <img src="{{ $weather->conditions_icon }}" alt="{{ $weather->conditionsPM_text }}"> </td>
                                                @endif
                                            @endforeach
                                        </tr>
                                        <tr><td>DIVE CONDITIONS</td>
                                            @foreach($weathers as $weather)
                                                @if($weather->location == "key largo" or $weather->location == "fort lauderdale" or $weather->location == "west palm beach")
                                                    {{--<td class="align-middle text-center text-sm">{{ $weather->conditionsAM_text }}</td>--}}
                                                    @if($weather->conditionsPM_text == "Poor")
                                                        <td class="align-middle text-center text-sm"> <span class="badge badge-warning">Poor</span> </td>
                                                    @elseif($weather->conditionsPM_text == "No Dive")
                                                        <td class="align-middle text-center text-sm"> <span class="badge badge-danger">No dive</span> </td>
                                                    @elseif($weather->conditionsPM_text == "Average")
                                                        <td class="align-middle text-center text-sm"> <span class="badge badge-secondary">Average</span> </td>
                                                    @elseif($weather->conditionsPM_text == "Perfect")
                                                        <td class="align-middle text-center text-sm"> <span class="badge badge-success">Perfect</span> </td>
                                                    @else
                                                        <td class="align-middle text-center text-sm"> <span class="badge badge-info">Good</span> </td>
                                                    @endif
                                                @endif
                                            @endforeach
                                        </tr>
                                        <tr> <td> </td></tr>

                                    </tbody>    
                                </table>
                                {{-- Table for filters--}}
                                <table class="table align-items-center mb-1">
                                    <tr>
                                        <td>
                                            <div class="dropdown">
                                                <select class="btn bg-info dropdown-toggle w-100 text-white" type="button" id="filterLocPM" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <option value="all">Show All</option>
                                                    <option value="MIA">miami beach</option>
                                                    <option value="FLL">fort lauderdale</option>
                                                    <option value="POM">pompano beach</option>
                                                    <option value="DEB">deerfield beach</option>
                                                    <option value="WPB">west pam beach</option>
                                                    <option value="KLA">key largo</option>
                                                </select>
                                                <p class="text-xs font-weight-bold mb-0 mt-n3">location</p>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <select class="btn bg-info dropdown-toggle w-100 text-white" type="button" id="filterAvPM" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <option value="all">Show All</option>
                                                    <option value="AVA">Available</option>
                                                    <option value="NAV">Sold-out</option>    
                                                </select>
                                                <p class="text-xs font-weight-bold mb-0 mt-n3">availability</p>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <select class="btn bg-info dropdown-toggle w-100 text-white" type="button" id="filterTypePM" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <option value="all">Show All</option>
                                                    <option value="TEC">Technical</option>
                                                    <option value="R">Recreational</option>    
                                                </select>
                                                <p class="text-xs font-weight-bold mb-0 mt-n3">type</p>
                                            </div>
                                        </td>
                                    </tr> 
                                </table>
                                {{-------------------------}}

                                <div class="table-responsive">
                                    <table id="tableTripsPM">
                                        <thead class="text-info">
                                            <th class="align-top">
                                                Operator
                                            </th>
                                            <th class="px-4 align-top">
                                                Time
                                            </th>
                                                <th class="py-0">Availability<p class="text-xs mt-0 px-1">click-to-book</p>
                                            </th>
                                            <th class="px-4 align-top">
                                                Site / Trip Name
                                            </th>
                                        </thead>
                                        <tbody>
                                            @foreach($trips as $trip)
                                                @php
                                                    $hour = (int)substr($trip->departureTime, 0, 2);
                                                @endphp
                                                @if($hour >= 12)
                                                    <tr style="border-bottom: 1px solid #D3D3D3;" data-tag="{{ $trip->tags }}">
                                                        <td class="d-flex px-0 py-2 text-sm" style="min-width: 200px;">{{ $trip->operatorName }}</td>
                                                        <td class="px-4">{{ $trip->departureTime }}</td>
                                                        @if($trip->tripFreeSpots == 0)
                                                            <td class="text-center">-</td>
                                                        @else
                                                            <td class="text-center"> <a href="{{ $trip->linkToBook }}" target="_blank">{{ $trip->tripFreeSpots == 1000 ? "Y" : $trip->tripFreeSpots }}</a></td>
                                                        @endif
                                                        <td class="px-4 text-sm"><a href="{{ route('TripDetails', ['tripId' => $trip->id]) }}">{{ $trip->tripName }}</a></td>
                                                    </tr>
                                                @endif
                                            @endforeach          
                                        </tbody>
                                    </table>
                                </div>


                            </div>    
                        </div>
                    </div>
                </div>
                {{-----------------------------}}
                    
                </div>
            <div class="card">
                <div class="card-header bg-gradient-info">
                
                    @php
                        $date = new DateTime($trips[0]->date);
                        $dateDayName = $date->format('l');
                        $dateText = $date->format('F\-d, Y');
                    @endphp
                    
                    <div style="float: left;">
                        <h2 class="card-title text-white">{{ $dateDayName }}</h2>
                        <h4 class="card-category text-white"> {{ $dateText }}</h4>
                    </div>

                    {{-----------------NAV to next day}} --}}
                    <div class="mt-4" style="float: right;">
                        <a type="button" href="/Trips/{{ $previousDay }}/" class="btn btn-info tex-end">
                            <span class="material-icons" style="font-size :30pt;">keyboard_arrow_left</span>
                        </a>
                        <a type="button" href="/Trips/" class="btn btn-info tex-end">
                            <span class="material-icons" style="font-size :30pt;">calendar_today</span>
                        </a>
                        <a type="button" href="/Trips/{{ $nextDay }}/" class="btn btn-info tex-end">
                            <span class="material-icons" style="font-size :30pt;">keyboard_arrow_right</span>
                        </a>
                    </div>
                    <div style="clear: both;"></div>
                </div>

                <div class="row">
                    {{-- Dive conditions card AM --}}
                    <div class="col-md-6 ">             
                        <div class="card p-0 position-relative mt-5 mx-3 z-index-2 mb-4">
                            <div class="card-header p-0 mt-n4 mx-2">
                                <div class="bg-gradient-info shadow-info border-radius-lg py-3 pe-1">
                                    <h2 class="card-title text-white mx-4">Morning (AM)</h4>
                                    
                                    <div class="table-responsive">
                                        
                                    </div>       
                                    <p class="card-category text-white mx-4">Dive Conditions (beta) - Trips</p>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table align-items-center mb-0">
                                        {{--<thead>
                                                <th class="align-middle text-center text-sm">LOCATION</th>
                                                <th class="align-middle text-center text-sm">FORECAST</th>
                                                <th class="align-middle text-center text-sm">DIVE CONDITIONS</th>   
                                        </thead>--}}
                                        <tbody>
                                            <tr> <td>LOCATION</td>
                                                @foreach($weathers as $weather)
                                                    @if($weather->location == "key largo" or $weather->location == "fort lauderdale" or $weather->location == "west palm beach")
                                                        <td class="align-middle text-center text-sm">{{ $weather->location }}</td>
                                                    @endif
                                                @endforeach
                                            </tr>
                                            <tr><td>FORECAST</td>
                                                @foreach($weathers as $weather)
                                                    @if($weather->location == "key largo" or $weather->location == "fort lauderdale" or $weather->location == "west palm beach")
                                                        <td class="align-middle text-center text-sm"> <img src="{{ $weather->conditions_icon }}" alt="{{ $weather->conditionsAM_text }}"> </td>
                                                    @endif
                                                @endforeach
                                            </tr>
                                            <tr><td>DIVE CONDITIONS</td>
                                                @foreach($weathers as $weather)
                                                    @if($weather->location == "key largo" or $weather->location == "fort lauderdale" or $weather->location == "west palm beach")
                                                        {{--<td class="align-middle text-center text-sm">{{ $weather->conditionsAM_text }}</td>--}}
                                                        @if($weather->conditionsAM_text == "Poor")
                                                            <td class="align-middle text-center text-sm"> <span class="badge badge-warning">  Poor  </span> </td>
                                                        @elseif($weather->conditionsAM_text == "No Dive")
                                                            <td class="align-middle text-center text-sm"> <span class="badge badge-danger"> No dive </span> </td>
                                                        @elseif($weather->conditionsAM_text == "Average")
                                                            <td class="align-middle text-center text-sm"> <span class="badge badge-secondary"> Average </span> </td>
                                                        @elseif($weather->conditionsAM_text == "Perfect")
                                                            <td class="align-middle text-center text-sm"> <span class="badge badge-success"> Perfect </span> </td>
                                                        @else
                                                            <td class="align-middle text-center text-sm"> <span class="badge badge-info">  Good  </span> </td>
                                                        @endif
                                                    @endif
                                                @endforeach
                                            </tr>
                                            <tr> <td> </td></tr>
                                        </tbody>    
                                    </table>

                                    {{-- Table for filters--}}
                                    <table class="table align-items-center mb-0">
                                        <tr>
                                            <td>
                                                <div class="dropdown">
                                                    <select class="btn bg-info dropdown-toggle w-100 text-white" type="button" id="filterLocAM" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <option value="all">Show All</option>
                                                        <option value="MIA">miami beach</option>
                                                        <option value="FLL">fort lauderdale</option>
                                                        <option value="POM">pompano beach</option>
                                                        <option value="DEB">deerfield beach</option>
                                                        <option value="WPB">west pam beach</option>
                                                        <option value="KLA">key largo</option>
                                                    </select>
                                                    <p class="text-xs font-weight-bold mb-0 mt-n3">location</p>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <select class="btn bg-info dropdown-toggle w-100 text-white" type="button" id="filterAvAM" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <option value="all">Show All</option>
                                                        <option value="AVA">Available</option>
                                                        <option value="NAV">Sold-out</option>    
                                                    </select>
                                                    <p class="text-xs font-weight-bold mb-0 mt-n3">availability</p>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <select class="btn bg-info dropdown-toggle w-100 text-white" type="button" id="filterTypeAM" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <option value="all">Show All</option>
                                                        <option value="TEC">Technical</option>
                                                        <option value="R">Recreational</option>    
                                                    </select>
                                                    <p class="text-xs font-weight-bold mb-0 mt-n3">type</p>
                                                </div>
                                            </td>
                                        </tr> 
                                    </table>
                                    {{-------------------------}}


                                    <div class="table-responsive">
                                        <table id="tableTripsAM">
                                            <thead class="text-info">
                                                <th class="align-top">
                                                    Operator
                                                </th>
                                                <th class="px-4 align-top">
                                                    Time
                                                </th>
                                                    <th class="py-0">Availability<p class="text-xs mt-0 px-1">click-to-book</p>
                                                </th>
                                                <th class="px-4 align-top">
                                                    Site / Trip Name
                                                </th>
                                            </thead>
                                            <tbody >
                                                @foreach($trips as $trip)
                                                    @php
                                                        $hour = (int)substr($trip->departureTime, 0, 2);
                                                    @endphp
                                                    @if($hour < 12)
                                                        <tr style="border-bottom: 1px solid #D3D3D3;" data-tag="{{ $trip->tags }}">
                                                            <td class="px-0 py-2 text-sm" style="min-width: 200px;">{{ $trip->operatorName }}</td>
                                                            <td class="px-4">{{ $trip->departureTime }}</td>
                                                            @if($trip->tripFreeSpots == 0)
                                                                <td class="text-center">-</td>
                                                            @else
                                                                <td class="text-center"> <a href="{{ $trip->linkToBook }}" target="_blank">{{ $trip->tripFreeSpots == 1000 ? "Y" : $trip->tripFreeSpots }}</a></td>
                                                            @endif
                                                            <td class="px-4 text-sm">{{ $trip->tripName }}</td>
                                                        </tr>
                                                    @endif
                                                @endforeach          
                                            </tbody>
                                        </table>
                                    </div>
                                </div>    
                            </div>
                        </div>
                    </div>
                    {{-----------------------------}}
                    
                    {{-- Dive conditions card PM --}}
                    <div class="col-md-6">             
                        <div class="card card p-0 position-relative mt-3 mx-3 z-index-2">
                            <div class="card-header bg-gradient-info">
                                    <h2 class="card-title text-white">Afternoon/Evening (PM)</h2>
                                    
                                    <div class="table-responsive">
                                        
                                    </div>       
                                    <p class="card-category text-white">Dive Conditions (beta) - Trips</p>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">
                                    
                                    <table class="table align-items-center mb-0">
                                        {{--<thead>
                                                <th class="align-middle text-center text-sm">LOCATION</th>
                                                <th class="align-middle text-center text-sm">FORECAST</th>
                                                <th class="align-middle text-center text-sm">DIVE CONDITIONS</th>   
                                        </thead>--}}
                                        <tbody>
                                            <tr> <td>LOCATION</td>
                                                @foreach($weathers as $weather)
                                                    @if($weather->location == "key largo" or $weather->location == "fort lauderdale" or $weather->location == "west palm beach")
                                                        <td class="align-middle text-center text-sm">{{ $weather->location }}</td>
                                                    @endif
                                                @endforeach
                                            </tr>
                                            <tr><td>FORECAST</td>
                                                @foreach($weathers as $weather)
                                                    @if($weather->location == "key largo" or $weather->location == "fort lauderdale" or $weather->location == "west palm beach")
                                                        <td class="align-middle text-center text-sm"> <img src="{{ $weather->conditions_icon }}" alt="{{ $weather->conditionsPM_text }}"> </td>
                                                    @endif
                                                @endforeach
                                            </tr>
                                            <tr><td>DIVE CONDITIONS</td>
                                                @foreach($weathers as $weather)
                                                    @if($weather->location == "key largo" or $weather->location == "fort lauderdale" or $weather->location == "west palm beach")
                                                        {{--<td class="align-middle text-center text-sm">{{ $weather->conditionsAM_text }}</td>--}}
                                                        @if($weather->conditionsPM_text == "Poor")
                                                            <td class="align-middle text-center text-sm"> <span class="badge badge-warning">Poor</span> </td>
                                                        @elseif($weather->conditionsPM_text == "No Dive")
                                                            <td class="align-middle text-center text-sm"> <span class="badge badge-danger">No dive</span> </td>
                                                        @elseif($weather->conditionsPM_text == "Average")
                                                            <td class="align-middle text-center text-sm"> <span class="badge badge-secondary">Average</span> </td>
                                                        @elseif($weather->conditionsPM_text == "Perfect")
                                                            <td class="align-middle text-center text-sm"> <span class="badge badge-success">Perfect</span> </td>
                                                        @else
                                                            <td class="align-middle text-center text-sm"> <span class="badge badge-info">Good</span> </td>
                                                        @endif
                                                    @endif
                                                @endforeach
                                            </tr>
                                            <tr> <td> </td></tr>

                                        </tbody>    
                                    </table>
                                    {{-- Table for filters--}}
                                    <table class="table align-items-center mb-1">
                                        <tr>
                                            <td>
                                                <div class="dropdown">
                                                    <select class="btn bg-info dropdown-toggle w-100 text-white" type="button" id="filterLocPM" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <option value="all">Show All</option>
                                                        <option value="MIA">miami beach</option>
                                                        <option value="FLL">fort lauderdale</option>
                                                        <option value="POM">pompano beach</option>
                                                        <option value="DEB">deerfield beach</option>
                                                        <option value="WPB">west pam beach</option>
                                                        <option value="KLA">key largo</option>
                                                    </select>
                                                    <p class="text-xs font-weight-bold mb-0 mt-n3">location</p>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <select class="btn bg-info dropdown-toggle w-100 text-white" type="button" id="filterAvPM" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <option value="all">Show All</option>
                                                        <option value="AVA">Available</option>
                                                        <option value="NAV">Sold-out</option>    
                                                    </select>
                                                    <p class="text-xs font-weight-bold mb-0 mt-n3">availability</p>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <select class="btn bg-info dropdown-toggle w-100 text-white" type="button" id="filterTypePM" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <option value="all">Show All</option>
                                                        <option value="TEC">Technical</option>
                                                        <option value="R">Recreational</option>    
                                                    </select>
                                                    <p class="text-xs font-weight-bold mb-0 mt-n3">type</p>
                                                </div>
                                            </td>
                                        </tr> 
                                    </table>
                                    {{-------------------------}}

                                    <div class="table-responsive">
                                        <table id="tableTripsPM">
                                            <thead class="text-info">
                                                <th class="align-top">
                                                    Operator
                                                </th>
                                                <th class="px-4 align-top">
                                                    Time
                                                </th>
                                                    <th class="py-0">Availability<p class="text-xs mt-0 px-1">click-to-book</p>
                                                </th>
                                                <th class="px-4 align-top">
                                                    Site / Trip Name
                                                </th>
                                            </thead>
                                            <tbody>
                                                @foreach($trips as $trip)
                                                    @php
                                                        $hour = (int)substr($trip->departureTime, 0, 2);
                                                    @endphp
                                                    @if($hour >= 12)
                                                        <tr style="border-bottom: 1px solid #D3D3D3;" data-tag="{{ $trip->tags }}">
                                                            <td class="d-flex px-0 py-2 text-sm" style="min-width: 200px;">{{ $trip->operatorName }}</td>
                                                            <td class="px-4">{{ $trip->departureTime }}</td>
                                                            @if($trip->tripFreeSpots == 0)
                                                                <td class="text-center">-</td>
                                                            @else
                                                                <td class="text-center"> <a href="{{ $trip->linkToBook }}" target="_blank">{{ $trip->tripFreeSpots == 1000 ? "Y" : $trip->tripFreeSpots }}</a></td>
                                                            @endif
                                                            <td class="px-4 text-sm">{{ $trip->tripName }}</td>
                                                        </tr>
                                                    @endif
                                                @endforeach          
                                            </tbody>
                                        </table>
                                    </div>


                                </div>    
                            </div>
                        </div>
                    </div>
                    {{-----------------------------}}
                    
                </div>
                
            </div>
                


                
            
            
            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>
    
    
    <x-plugins></x-plugins>
    
    @push('js')

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

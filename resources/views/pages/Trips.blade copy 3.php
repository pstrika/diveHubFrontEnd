<x-page-template bodyClass='g-sidenav-show  bg-gray-200'>
    <x-auth.navbars.sidebar activePage="trips" activeItem="trips" activeSubitem=""></x-auth.navbars.sidebar>
    <!--added a comments here -->
    
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <style>
            .flatpickr-day.selected {
                background: #03a9f4; /* Replace #yourColor with the hex code of the color you want */
                
            },
            .flatpickr-calendar {
                background: #03a9f4; /* Replace #yourColor with the hex code of the color you want */
            }
        </style>
        <!-- Navbar -->
        <x-auth.navbars.navs.auth pageTitle="Dive Trips"></x-auth.navbars.navs.auth>
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
                                        
                                        <tr><td class="text-secondary text-end text-lg font-weight-bolder"><img src="{{ asset('assets') }}/img/icons/icons_level_0.png" height="25"></td>
                                        <td class="align-middle text-info text-start text-sm"><b>Open Water</b></td> 
                                        <td class="align-middle text-info text-center text-sm"><b>60</b></td> </tr>

                                        <tr><td class="text-secondary text-end text-lg font-weight-bolder"><img src="{{ asset('assets') }}/img/icons/icons_level_1.png" height="25"></td>
                                        <td class="align-middle text-info text-start text-sm"><b>Advanced Open Water</b></td>
                                        <td class="align-middle text-info text-center text-sm"><b>130</b></td> </tr>

                                        <tr><td class="text-secondary text-end text-lg font-weight-bolder opacity-7"><img src="{{ asset('assets') }}/img/icons/icons_level_2.png" height="25"></td>
                                        <td class="align-middle text-info text-start text-sm"><b>Technical Air</b></td>
                                        <td class="align-middle text-info text-center text-sm"><b>150</b></td> </tr>

                                        <tr><td class="text-secondary text-end text-lg font-weight-bolder"><img src="{{ asset('assets') }}/img/icons/icons_level_3.png" height="25"></td>
                                        <td class="align-middle text-info text-start text-sm"><b>Technical Normoxic Trimix</b></td>
                                        <td class="align-middle text-info text-center text-sm"><b>200</b></td> </tr>

                                        <tr><td class="text-secondary text-end text-lg font-weight-bolder"><img src="{{ asset('assets') }}/img/icons/icons_level_4.png" height="25"></td>
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
        
        


            {{--
            <div class="page-header min-height-250 max-height-300 border-radius-xl mt-4 mx-n2" style="background-image: url('/assets/img/illustrations/divers_jumping.avif');">
                <span class="mask  bg-gradient-info  opacity-4"></span>
            </div>--}}

            <div class="page-header min-height-200 max-height-300 border-radius-xl mt-4 mx-n2 justify-content-end" style="background-image: url('/assets/img/illustrations/ads/240803_majestic.png');">
                <!--<span class="mask  bg-gradient-info  opacity-4"></span>-->
                <button class="btn btn-icon btn-3 btn-info mt-n6 mx-3" type="button" onclick="sendEmail();">
                    <span class="btn-inner--text">Learn More</span>
                </button>
            </div>

            
            <div class="card p-0 position-relative mt-n7 mx-2 z-index-2 mb-4">
                
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
                        <div id="spinner" style="float: center; display: none;" class="spinner-border text-info mt-4" role="status">
                                <span class="sr-only">Loading...</span>
                        </div>
                        
                        {{-----------------NAV to next day}} --}}
                        <div class="mt-5" style="float: right;">
                            <a type="button" class="btn btn-info tex-end" id="button1">
                                <span class="material-icons" style="font-size :24pt;" data-bs-toggle="tooltip" data-bs-placement="top" title="Go to previous day">keyboard_arrow_left</span>
                            </a>
                            <a type="button" class="btn btn-info tex-end" id="button2">
                                <span class="material-icons" style="font-size :24pt;" data-bs-toggle="tooltip" data-bs-placement="top" title="Go to today">calendar_today</span>
                            </a>

                            <div type="button" class="btn btn-info tex-end" id="button3">
                                <input id="datePicker" placeholder=" Select date..." class="text-info opacity-0 z-index-5 position-absolute" style="width: 120%;">              
                                <span class="material-icons z-index-1" style="font-size :24pt;" data-bs-toggle="tooltip" data-bs-placement="top" title="Choose specific day">calendar_month</span>
                            </div>
 
                            <a type="button" class="btn btn-info tex-end" >
                                <span class="material-icons" style="font-size :24pt;" id="button4" data-bs-toggle="tooltip" data-bs-placement="top" title="Go to next day">keyboard_arrow_right</span>
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
                                @if($weathers->isNotEmpty())   
                                    <p class="card-category text-white mx-4">Dive Conditions (<a class="text-white text-xs text-decoration-underline" href="#" onclick="showModal();"><b>What is this?</b></a>) - Trips</p>
                                @else
                                    <p class="card-category text-white mx-4">Trips</p>
                                @endif
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                {{-- Table for Weathers--}}
                                @if ($weathers->isNotEmpty())
                                <table class="table align-items-center mb-0">
                                    {{--<thead>
                                            <th class="align-middle text-center text-sm">LOCATION</th>
                                            <th class="align-middle text-center text-sm">FORECAST</th>
                                            <th class="align-middle text-center text-sm">DIVE CONDITIONS</th>   
                                    </thead>--}}
                                    <tbody>
                                        <tr> <td>LOCATION</td>
                                            @foreach($weathers as $weather)
                                                
                                                <td class="align-middle text-center text-sm">{{ ucwords($weather->location) }}</td>
                                                
                                            @endforeach
                                        </tr>
                                        <tr><td>FORECAST</td>
                                            @foreach($weathers as $weather)
                                                
                                                <td class="align-middle text-center text-sm"> <img src="{{ $weather->conditions_icon }}" alt="{{ $weather->conditionsAM_text }}"> </td>
                                                
                                            @endforeach
                                        </tr>
                                        <tr><td>OCEAN CONDITIONS</td>
                                            @foreach($weathers as $weather)
                                                
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
                                                
                                            @endforeach
                                        </tr>
                                        <tr> <td> </td></tr>
                                    </tbody>    
                                </table>
                                @endif

                                {{-- Table for filters--}}
                                <table class="table align-items-center mb-0">
                                    <tr>
                                        <td>
                                            <div class="dropdown">
                                                <select class="btn bg-info dropdown-toggle w-100 text-white" type="button" id="filterLocAM" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <option value="all">Show All</option>
                                                    <option value="MIA">Miami Beach</option>
                                                    <option value="FLL">Fort Lauderdale</option>
                                                    <option value="POM">Pompano Beach</option>
                                                    <option value="DEB">Deerfield Beach</option>
                                                    <option value="WPB">West Palm Beach</option>
                                                    <option value="KLA">Key Largo</option>
                                                </select>
                                                <p class="text-xs font-weight-bold mb-0 mt-n3">location</p>
                                            </div>
                                        </td>
                                        {{--<td>
                                            <div class="dropdown">
                                                <select class="btn bg-info dropdown-toggle w-100 text-white" type="button" id="filterAvAM" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <option value="all">Show All</option>
                                                    <option value="AVA">Available</option>
                                                    <option value="NAV">Sold-out</option>    
                                                </select>
                                                <p class="text-xs font-weight-bold mb-0 mt-n3">availability</p>
                                            </div>
                                        </td>--}}
                                        <td>
                                            <div class="dropdown">
                                                <select class="btn bg-info dropdown-toggle w-100 text-white" type="button" id="filterTypeAM" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <option value="all">Show All</option>
                                                    <option value="TEC">Technical</option>
                                                    <option value="R">Recreational (all)</option>
                                                    <option value=" OW">Recreational (OW)</option>    
                                                    <option value="AOW">Recreational (Advanced)</option>      
                                                </select>
                                                <p class="text-xs font-weight-bold mb-0 mt-n3">type</p>
                                            </div>
                                        </td>
                                    </tr> 
                                    <tr>
                                        @if($user->id != 5) {{---If user is "guest" aka id=5, we don't show favorite filter--}}
                                        <td>
                                            <ul class="list-group">
                                                <li class="list-group-item border-0 px-0">
                                                    <div class="form-check form-switch ps-0">
                                                        <input class="form-check-input ms-auto" type="checkbox"
                                                            id="filterFavAM">
                                                        <label class="form-check-label text-body ms-3 text-truncate w-80 mb-0"
                                                            for="flexSwitchCheckDefault" data-bs-toggle="tooltip" data-bs-placement="top" title="To set Favorites, go to your Profile">Show Favorites only</label>
                                                    </div>
                                                </li>
                                                
                                            </ul>
                                        </td>
                                        @endif
                                        <td>
                                            <ul class="list-group">
                                                <li class="list-group-item border-0 px-0">
                                                    <div class="form-check form-switch ps-0">
                                                        <input class="form-check-input ms-auto" type="checkbox"
                                                            id="filterAvAM">
                                                        <label class="form-check-label text-body ms-3 text-truncate w-80 mb-0"
                                                            for="flexSwitchCheckDefault">Show available only</label>
                                                    </div>
                                                </li>
                                                
                                            </ul>
                                        </td>
                                    </tr>
                                </table>
                                

                                {{-- Table for trips--}}
                                <div class="table-responsive">
                                    <table id="tableTripsAM">
                                        <thead class="text-info">
                                            <th class="align-top">
                                                
                                            </th>
                                            <th class="align-top">
                                                Operator
                                            </th>
                                            <th class="px-4 align-top">
                                                Time
                                            </th>
                                                <th class="py-0 align-top" data-bs-toggle="tooltip" data-bs-placement="top" title="Click on the numbers below to go to trip booking page" data-container="body" data-animation="true">Availability<p class="text-xs mt-0 px-1">click-to-book</p>
                                            </th>
                                            <th class="px-4 align-top" data-bs-toggle="tooltip" data-bs-placement="top" title="Click on the name of the trip to see full trip details" data-container="body" data-animation="true">
                                                Site / Trip Name<p class="text-xs mt-0 px-1">click for details</p>
                                            </th>
                                            <th class="px-4 align-top">
                                                Level<a href="#" onclick="showModalLevel();"><p class="text-xs text-info text-center mt-0 px-1">(?)</p></a>
                                            </th>
                                            <th class="px-4 align-top" data-bs-toggle="tooltip" data-bs-placement="top" title="site max depth" data-container="body" data-animation="true">
                                                Depth
                                            </th>
                                        </thead>
                                        <tbody >
                                            @foreach($trips as $trip)
                                                @php
                                                    $hour = (int)substr($trip->departureTime, 0, 2);
                                                @endphp
                                                @if($hour < 12)
                                                    <tr style="border-bottom: 1px solid #D3D3D3;" class="justify-content-center align-middle" data-tag="{{ $trip->tags }}">
                                                        @if($trip->fav == 1)
                                                            <td class="text-start justify-content-center"><i class="material-icons-round text-info opacity-10">favorite</i></td>
                                                        @else
                                                            <td class="text-center"> </td>
                                                        @endif
                                                        <td class="px-0 py-2 text-sm text-wrap align-middle justify-content-center">{{ $trip->operatorName }}</td>
                                                        <td class="px-4">{{ $trip->departureTime }}</td>
                                                        @if($trip->tripFreeSpots == 0)
                                                            <td class="text-center">-</td>
                                                        @else
                                                            <td class="text-center"> <a href="{{ $trip->linkToBook }}" target="_blank">{{ $trip->tripFreeSpots == 1000 ? "Y" : $trip->tripFreeSpots }}</a></td>
                                                        @endif
                                                        <td class="px-4 text-sm"><a href="{{ route('TripDetails', ['tripId' => $trip->id]) }}">{{ $trip->tripName }}<a></td>
                                                        @if(!empty($trip->site[0]))
                                                        {{--<td class="px-4 text-sm text-center">{{ $trip->site[0]->level }}</td>--}}
                                                            <td class="text-center" style="border: none;"><img src="{{ asset('assets') }}/img/icons/icons_level_{{ $trip->site[0]->level }}.png" height="25"></td>
                                                        @else
                                                            <td class="px-4 text-sm text-center"> </td>
                                                        @endif

                                                        @if(!empty($trip->site[0]))
                                                            <td class="px-4 text-sm text-center">{{ $trip->site[0]->maxDepth }}</td>
                                                        @else
                                                            <td class="px-4 text-sm text-center"> </td>
                                                        @endif
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
                                @if($weathers->isNotEmpty())   
                                    <p class="card-category text-white mx-4">Dive Conditions (<a class="text-white text-xs text-decoration-underline" href="#" onclick="showModal();"><b>What is this?</b></a>) - Trips</p>
                                @else
                                    <p class="card-category text-white mx-4">Trips</p>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                
                                {{-- Table for weathers--}}
                                @if($weathers->isNotEmpty())
                                <table class="table align-items-center mb-0">
                                    {{--<thead>
                                            <th class="align-middle text-center text-sm">LOCATION</th>
                                            <th class="align-middle text-center text-sm">FORECAST</th>
                                            <th class="align-middle text-center text-sm">DIVE CONDITIONS</th>   
                                    </thead>--}}
                                    <tbody>
                                        <tr> <td>LOCATION</td>
                                            @foreach($weathers as $weather)
                                                
                                                <td class="align-middle text-center text-sm">{{ ucwords($weather->location) }}</td>
                                                
                                            @endforeach
                                        </tr>
                                        <tr><td>FORECAST</td>
                                            @foreach($weathers as $weather)
                                                
                                                <td class="align-middle text-center text-sm"> <img src="{{ $weather->conditions_icon }}" alt="{{ $weather->conditionsPM_text }}"> </td>
                                            
                                            @endforeach
                                        </tr>
                                        <tr><td>OCEAN CONDITIONS</td>
                                            @foreach($weathers as $weather)
                                            
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
                                                
                                            @endforeach
                                        </tr>
                                        <tr> <td> </td></tr>

                                    </tbody>    
                                </table>
                                @endif
                                {{-- Table for filters--}}
                                <table class="table align-items-center mb-1">
                                    <tr>
                                        <td>
                                            <div class="dropdown">
                                                <select class="btn bg-info dropdown-toggle w-100 text-white" type="button" id="filterLocPM" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <option value="all">Show All</option>
                                                    <option value="MIA">Miami Beach</option>
                                                    <option value="FLL">Fort Lauderdale</option>
                                                    <option value="POM">Pompano Beach</option>
                                                    <option value="DEB">Deerfield Beach</option>
                                                    <option value="WPB">West Palm Beach</option>
                                                    <option value="KLA">Key Largo</option>
                                                </select>
                                                <p class="text-xs font-weight-bold mb-0 mt-n3">location</p>
                                            </div>
                                        </td>
                                        {{--<td>
                                            <div class="dropdown">
                                                <select class="btn bg-info dropdown-toggle w-100 text-white" type="button" id="filterAvPM" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <option value="all">Show All</option>
                                                    <option value="AVA">Available</option>
                                                    <option value="NAV">Sold-out</option>    
                                                </select>
                                                <p class="text-xs font-weight-bold mb-0 mt-n3">availability</p>
                                            </div>
                                        </td>--}}
                                        <td>
                                            <div class="dropdown">
                                                <select class="btn bg-info dropdown-toggle w-100 text-white" type="button" id="filterTypePM" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <option value="all">Show All</option>
                                                    <option value="TEC">Technical</option>
                                                    <option value="R">Recreational (all)</option>
                                                    <option value=" OW">Recreational (OW)</option>    
                                                    <option value="AOW">Recreational (Advanced)</option>    
                                                </select>
                                                <p class="text-xs font-weight-bold mb-0 mt-n3">type</p>
                                            </div>
                                        </td>
                                    </tr> 
                                    <tr>
                                        @if($user->id != 5) {{---If user is "guest" aka id=5, we don't show favorite filter--}}
                                        <td>
                                            <ul class="list-group">
                                                <li class="list-group-item border-0 px-0">
                                                    <div class="form-check form-switch ps-0">
                                                        <input class="form-check-input ms-auto" type="checkbox"
                                                            id="filterFavPM">
                                                        <label class="form-check-label text-body ms-3 text-truncate w-80 mb-0"
                                                            for="flexSwitchCheckDefault" data-bs-toggle="tooltip" data-bs-placement="top" title="To set Favorites, go to your Profile">Show Favorites only</label>
                                                    </div>
                                                </li>
                                                
                                            </ul>
                                        </td>
                                        @endif
                                        <td>
                                            <ul class="list-group">
                                                <li class="list-group-item border-0 px-0">
                                                    <div class="form-check form-switch ps-0">
                                                        <input class="form-check-input ms-auto" type="checkbox"
                                                            id="filterAvPM">
                                                        <label class="form-check-label text-body ms-3 text-truncate w-80 mb-0"
                                                            for="flexSwitchCheckDefault">Show available only</label>
                                                    </div>
                                                </li>
                                                
                                            </ul>
                                        </td>
                                    </tr>
                                </table>
                                
                                {{--Table for Trips-------}}
                                <div class="table-responsive">
                                    <table id="tableTripsPM">
                                        <thead class="text-info">
                                            <th class="align-top">
                                                
                                            </th>
                                            <th class="align-top">
                                                Operator
                                            </th>
                                            <th class="px-4 align-top">
                                                Time
                                            </th>
                                            <th class="py-0 align-top" data-bs-toggle="tooltip" data-bs-placement="top" title="Click on the numbers below to go to trip booking page" data-container="body" data-animation="true">Availability<p class="text-xs mt-0 px-1">click-to-book</p>
                                            </th>
                                            <th class="px-4 align-top" data-bs-toggle="tooltip" data-bs-placement="top" title="Click on the name of the trip to see full trip details" data-container="body" data-animation="true">
                                                Site / Trip Name<p class="text-xs mt-0 px-1">click for details</p>
                                            </th>
                                            <th class="px-4 align-top">
                                                Level<a href="#" onclick="showModalLevel();"><p class="text-xs text-info text-center mt-0 px-1">(?)</p></a>
                                            </th>
                                            <th class="px-4 align-top" data-bs-toggle="tooltip" data-bs-placement="top" title="site max depth" data-container="body" data-animation="true">
                                                Depth
                                            </th>
                                        </thead>
                                        <tbody>
                                            @foreach($trips as $trip)
                                                @php
                                                    $hour = (int)substr($trip->departureTime, 0, 2);
                                                @endphp
                                                @if($hour >= 12)
                                                    <tr style="border-bottom: 1px solid #D3D3D3;" class="justify-content-center align-middle" data-tag="{{ $trip->tags }}">
                                                        @if($trip->fav == 1)
                                                            <td class="text-start justify-content-center"><i class="material-icons-round text-info opacity-10">favorite</i></td>
                                                        @else
                                                            <td class="text-center"> </td>
                                                        @endif
                                                        <td class="px-0 py-2 text-sm text-wrap align-middle justify-content-center">{{ $trip->operatorName }}</td>
                                                        <td class="px-4">{{ $trip->departureTime }}</td>
                                                        @if($trip->tripFreeSpots == 0)
                                                            <td class="text-center">-</td>
                                                        @else
                                                            <td class="text-center"> <a href="{{ $trip->linkToBook }}" target="_blank">{{ $trip->tripFreeSpots == 1000 ? "Y" : $trip->tripFreeSpots }}</a></td>
                                                        @endif
                                                        <td class="px-4 text-sm"><a href="{{ route('TripDetails', ['tripId' => $trip->id]) }}">{{ $trip->tripName }}</a></td>
                                                        @if(!empty($trip->site[0]))
                                                        {{--<td class="px-4 text-sm text-center">{{ $trip->site[0]->level }}</td>--}}
                                                            <td class="text-center" style="border: none;"><img src="{{ asset('assets') }}/img/icons/icons_level_{{ $trip->site[0]->level }}.png" height="25"></td>
                                                        @else
                                                            <td class="px-4 text-sm text-center"> </td>
                                                        @endif

                                                        @if(!empty($trip->site[0]))
                                                            <td class="px-4 text-sm text-center">{{ $trip->site[0]->maxDepth }}</td>
                                                        @else
                                                            <td class="px-4 text-sm text-center"> </td>
                                                        @endif
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
            
                


                
            
            
            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>
    
    
    {{--<x-plugins></x-plugins>--}}
    
    @push('js')
    
    <script src="{{ asset('assets') }}/js/plugins/flatpickr.min.js"></script>
    <script src="{{ asset('assets') }}/js/plugins/jquery-3.6.0.min.js" type="text/javascript"></script>

    <script>
        function showSpinner() {
            document.getElementById("spinner").style.display = "inline-flex";
        }
    </script>

    <script>
        function showModal() {
            $('#modal').modal('show'); // Show the modal
        };

        function showModalLevel() {
            $('#modalLevel').modal('show'); // Show the modal
        };
    </script>

    <script>
      

    flatpickr("#datePicker", {
        altInput: true,
        altFormat: "F j, Y",
        dateFormat: "Y-m-d",
        disableMobile: "true",
        minDate: new Date(),
        
        maxDate: new Date().fp_incr(90),
        onChange: function(selectedDates, dateStr, instance) {
            showSpinner();
            window.location.href = `/Trips/${dateStr}`;
        }
    });
    </script>

    <script>
        

        

        const button1 = document.querySelector("#button1");
        button1.addEventListener("click", function() {
            // Redirect to the specified URL
            showSpinner();
            window.location.href = "/Trips/{{ $previousDay }}/";
        });

        const button2 = document.querySelector("#button2");
        button2.addEventListener("click", function() {
            // Redirect to the specified URL
            showSpinner();
            window.location.href = "/Trips/";
        });

        const button4 = document.querySelector("#button4");
        button4.addEventListener("click", function() {
            // Redirect to the specified URL
            showSpinner();
            window.location.href = "/Trips/{{ $nextDay }}/";
        });

    </script>

    <script>
        // To save filter state
        function saveFilterState(filterState) {
        localStorage.setItem('filters', JSON.stringify(filterState));
        console.log("Saved filters");
    }

    // To retrieve filter state
    function loadFilterState() {
        const savedFilters = localStorage.getItem('filters');
        return savedFilters ? JSON.parse(savedFilters) : null;
    }
    </script>
    {{--Handler for tripAM table: filter by location--}}
    <script>
        var filterAvPM =document.getElementById('filterAvPM');
        var filterFavPM =document.getElementById('filterFavPM');
        var filterLocPM =document.getElementById('filterLocPM');
        var filterTypePM =document.getElementById('filterTypePM');

        var filterAvAM =document.getElementById('filterAvAM');
        var filterFavAM =document.getElementById('filterFavAM');
        var filterLocAM =document.getElementById('filterLocAM');
        var filterTypeAM =document.getElementById('filterTypeAM');

        // Filter Location AM
        // Function to apply the filter
        function applyFilterLocAM(filterValue) {
            var rows = document.querySelectorAll('#tableTripsAM tr[data-tag]');
            rows.forEach(function(row) {
                var tags = row.getAttribute('data-tag');
                if (tags.includes(filterValue) || filterValue === 'all') {
                    row.style.display = ''; // Show the row
                } else {
                    row.style.display = 'none'; // Hide the row
                }
            });
        }

        // Function to load and apply the saved filter state
        function loadAndApplyFilterAM() {
            // Load the saved filter state
            var savedFilterState = loadFilterState();
            
            
            if(savedFilterState.filterAM == 'Loc') {
                var filterValue = savedFilterState.filterLocAM || 'all'; // Use saved value or default to 'all'
                // Apply the filter
                applyFilterLocAM(filterValue);
                
                // Update the filter UI
                document.getElementById('filterLocAM').value = filterValue;
                filterAvAM.checked = false;
                filterFavAM.checked = false;
                filterTypeAM.value = 'all';
            }
        }

        // Event listener for DOMContentLoaded to apply the filter on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Load and apply the filter when the page loads
            loadAndApplyFilterAM();
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Event listener for filter changes
            document.getElementById('filterLocAM').addEventListener('change', function() {
                applyFilterLocAM(this.value); // Apply the filter based on the selected option
                
                // Save the new filter state
                const filterState = {
                    filterLocAM: this.value,
                    filterAvAM: false,
                    filterFavAM: false,
                    filterTypeAM: 'all',
                    filterAM: 'Loc'
                    // ... other filter states
                };
                saveFilterState(filterState);
            });
        });

         // Filter AM Availability
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('filterAvAM').addEventListener('change', function() {
                var selectedOption = 'all';
                if (this.checked) {
                    selectedOption = 'AVA';
                }
                
                var rows = document.querySelectorAll('#tableTripsAM tr[data-tag]');
                
                rows.forEach(function(row) {
                var tags = row.getAttribute('data-tag');
                if (tags.includes(selectedOption) || selectedOption === 'all') {
                    row.style.display = ''; // Show the row
                } else {
                    row.style.display = 'none'; // Hide the row
                }
                });

                filterFavAM.checked = false;
                filterLocAM.value = 'all';
                filterTypeAM.value = 'all';
            });
        });

        // Filter AM Fav
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('filterFavAM').addEventListener('change', function() {
                var selectedOption = 'all';
                if (this.checked) {
                    selectedOption = 'FAV';
                }
                
                var rows = document.querySelectorAll('#tableTripsAM tr[data-tag]');
                
                rows.forEach(function(row) {
                var tags = row.getAttribute('data-tag');
                if (tags.includes(selectedOption) || selectedOption === 'all') {
                    row.style.display = ''; // Show the row
                } else {
                    row.style.display = 'none'; // Hide the row
                }
                });

                //reset all other filters
                filterAvAM.checked = false;
                filterLocAM.value = 'all';
                filterTypeAM.value = 'all';
            });
        });

        // Filter AM Type
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

                filterAvAM.checked = false;
                filterFavAM.checked = false;
                filterLocAM.value = 'all';
            });
        });

    {{--Handler for tripPM table: filter by location--}}

    // Filter PM Location
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

            filterAvPM.checked = false;
            filterFavPM.checked = false;
            filterTypePM.value = 'all';
        });
    });

    // Filter PM Availability
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('filterAvPM').addEventListener('change', function() {
            var selectedOption = 'all';
            if (this.checked) {
                selectedOption = 'AVA';
            }
            
            var rows = document.querySelectorAll('#tableTripsPM tr[data-tag]');
            
            rows.forEach(function(row) {
            var tags = row.getAttribute('data-tag');
            if (tags.includes(selectedOption) || selectedOption === 'all') {
                row.style.display = ''; // Show the row
            } else {
                row.style.display = 'none'; // Hide the row
            }
            });

            filterFavPM.checked = false;
            filterLocPM.value = 'all';
            filterTypePM.value = 'all';
        });
    });

    // Filter PM Fav
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('filterFavPM').addEventListener('change', function() {
            var selectedOption = 'all';
            if (this.checked) {
                selectedOption = 'FAV';
            }
            
            var rows = document.querySelectorAll('#tableTripsPM tr[data-tag]');
            
            rows.forEach(function(row) {
            var tags = row.getAttribute('data-tag');
            if (tags.includes(selectedOption) || selectedOption === 'all') {
                row.style.display = ''; // Show the row
            } else {
                row.style.display = 'none'; // Hide the row
            }
            });

            //reset all other filters
            filterAvPM.checked = false;
            filterLocPM.value = 'all';
            filterTypePM.value = 'all';
        });
    });

    // Filter PM Type
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

            filterAvPM.checked = false;
            filterFavPM.checked = false;
            filterLocPM.value = 'all';
        });
    });
    </script>

    {{-- Send email for ad--}}
    <script>
        function sendEmail() {
        var link = 'mailto:seatheskyadventures@gmail.com'
                + '?cc=info@divers-hub.com'
                + '&subject=' + encodeURIComponent("Inquiry: Majestic liveaboard on Oct-19th to Oct-26th 2024")
                + '&body=' + encodeURIComponent("I've read in divers-hub about a trip to Egypt. I want to know more!")
        ;

        window.location.href = link;
        }
    </script>
    @endpush
</x-page-template>

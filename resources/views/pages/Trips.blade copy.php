<x-page-template bodyClass='g-sidenav-show  bg-gray-200'>
    <!-- Navbar -->
    <x-auth.navbars.sidebar activePage="dashboard" activeItem="analytics" activeSubitem=""></x-auth.navbars.sidebar>
    <!-- End Navbar -->

    
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header card-header-info">
            
                @php
                    $date = new DateTime($trips[0]->date);
                    $dateDayName = $date->format('l');
                    $dateText = $date->format('F\-d, Y');
                @endphp
                
                <div style="float: left;">
                    <h2 class="card-title ">{{ $dateDayName }}</h4>
                    <h3 class="card-category"> {{ $dateText }}</p>
                </div>
                {{-----------------NAV to next day}} --}}
                
                <div style="float: right;">
                    <nav style="float: right;" aria-label="Page navigation example">
                        <ul class="pagination">
                            <li class="page-item {{ $controlNav }}">
                                <a class="page-link" href="/todayTrips/{{ $previousDay }}/" aria-label="Previous">
                                    <span class="material-icons" style="font-size :35pt;">
                                    keyboard_arrow_left
                                    </span> 
                                    <span class="sr-only">Previous</span>
                                </a>
                            </li>
                            
                            <li class="page-item">
                                <a class="page-link" href="/todayTrips/{{ $today }}/" aria-label="Next">
                                    <span class="material-icons" style="font-size :35pt;">
                                    calendar_today
                                    </span>
                                    <span class="sr-only">Next</span>
                                </a>
                            </li>

                            <li class="page-item">
                                <a class="page-link" href="/todayTrips/{{ $nextDay }}/" aria-label="Next">
                                    <span class="material-icons" style="font-size :35pt;">
                                    keyboard_arrow_right
                                    </span>
                                    <span class="sr-only">Next</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <div style="clear: both;"></div>
            </div>

            <div class="row">
                {{-- Dive conditions card AM --}}
                <div class="col-md-6">             
                    <div class="card">
                        <div class="card-header card-header-info">
                                <h2 class="card-title ">Morning (AM)</h4>
                                
                                <div class="table-responsive">
                                    
                                </div>       
                                <p class="card-category">Dive Conditions (beta) - Trips</p>
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
                                                @if($weather->location == "miami beach" or $weather->location == "fort lauderdale" or $weather->location == "pompano beach")
                                                    <td class="align-middle text-center text-sm">{{ $weather->location }}</td>
                                                @endif
                                            @endforeach
                                        </tr>
                                        <tr><td>FORECAST</td>
                                            @foreach($weathers as $weather)
                                                @if($weather->location == "miami beach" or $weather->location == "fort lauderdale" or $weather->location == "pompano beach")
                                                    <td class="align-middle text-center text-sm"> <img src="{{ $weather->conditions_icon }}" alt="{{ $weather->conditionsAM_text }}"> </td>
                                                @endif
                                            @endforeach
                                        </tr>
                                        <tr><td>DIVE CONDITIONS</td>
                                            @foreach($weathers as $weather)
                                                @if($weather->location == "miami beach" or $weather->location == "fort lauderdale" or $weather->location == "pompano beach")
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

                                    </tbody>    
                                </table>

                                {{-- Table for filters--}}
                                <table class="table align-items-center mb-0">
                                    <tr>
                                        <td>
                                            <div class="dropdown">
                                                <select class="btn bg-info dropdown-toggle w-100" type="button" id="filterLocAM" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <option value="all">Show All</option>
                                                    <option value="MIA">miami beach</option>
                                                    <option value="FLL">fort lauderdale</option>
                                                    <option value="POM">pompano beach</option>
                                                    <option value="DEB">deerfield beach</option>
                                                    <option value="WPB">west pam beach</option>
                                                </select>
                                                <p class="text-xs font-weight-bold mb-0">location</p>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <select class="btn bg-info dropdown-toggle w-100" type="button" id="filterAvAM" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <option value="all">Show All</option>
                                                    <option value="AVA">Available</option>
                                                    <option value="NAV">Sold-out</option>    
                                                </select>
                                                <p class="text-xs font-weight-bold mb-0">availability</p>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <select class="btn bg-info dropdown-toggle w-100" type="button" id="filterTypeAM" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <option value="all">Show All</option>
                                                    <option value="TEC">Technical</option>
                                                    <option value="R">Recreational</option>    
                                                </select>
                                                <p class="text-xs font-weight-bold mb-0">type</p>
                                            </div>
                                        </td>
                                    </tr> 
                                </table>
                                {{-------------------------}}


                                <table class="table align-items-center" id="tableTripsAM">
                                    <thead class=" text-info">
                                        <th>
                                            Operator
                                        </th>
                                        <th>
                                            Time
                                        </th>
                                        <th class="text-center">
                                            Availability
                                        </th>
                                        <th>
                                            Site / Trip Name
                                        </th>
                                    </thead>
                                    <tbody>
                                        @foreach($trips as $trip)
                                            @php
                                                $hour = (int)substr($trip->departureTime, 0, 2);
                                            @endphp
                                            @if($hour < 12)
                                                <tr data-tag="{{ $trip->tags }}">
                                                    <td>{{ $trip->operatorName }}</td>
                                                    <td>{{ $trip->departureTime }}</td>
                                                    <td class="text-center">{{ $trip->tripFreeSpots == 1000 ? "Y" : $trip->tripFreeSpots }}</td>
                                                    <td>{{ $trip->tripName }}</td>
                                                </tr>
                                            @endif
                                        @endforeach          
                                    </tbody>
                                </table>
                            </div>    
                        </div>
                    </div>
                </div>
                {{-----------------------------}}
                
                {{-- Dive conditions card PM --}}
                <div class="col-md-6">             
                    <div class="card">
                        <div class="card-header card-header-info">
                                <h2 class="card-title ">Afternoon/Evening (PM)</h4>
                                
                                <div class="table-responsive">
                                    
                                </div>       
                                <p class="card-category">Dive Conditions (beta) - Trips</p>
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
                                                @if($weather->location == "miami beach" or $weather->location == "fort lauderdale" or $weather->location == "pompano beach")
                                                    <td class="align-middle text-center text-sm">{{ $weather->location }}</td>
                                                @endif
                                            @endforeach
                                        </tr>
                                        <tr><td>FORECAST</td>
                                            @foreach($weathers as $weather)
                                                @if($weather->location == "miami beach" or $weather->location == "fort lauderdale" or $weather->location == "pompano beach")
                                                    <td class="align-middle text-center text-sm"> <img src="{{ $weather->conditions_icon }}" alt="{{ $weather->conditionsPM_text }}"> </td>
                                                @endif
                                            @endforeach
                                        </tr>
                                        <tr><td>DIVE CONDITIONS</td>
                                            @foreach($weathers as $weather)
                                                @if($weather->location == "miami beach" or $weather->location == "fort lauderdale" or $weather->location == "pompano beach")
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

                                    </tbody>    
                                </table>
                                {{-- Table for filters--}}
                                <table class="table align-items-center mb-0">
                                    <tr>
                                        <td class="w-20">
                                            <div class="dropdown">
                                                <select class="btn bg-info dropdown-toggle" type="button" id="filterLocPM" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <option value="all">Show All</option>
                                                    <option value="MIA">miami beach</option>
                                                    <option value="FLL">fort lauderdale</option>
                                                    <option value="POM">pompano beach</option>
                                                    <option value="DEB">deerfield beach</option>
                                                    <option value="WPB">west pam beach</option>
                                                </select>
                                                <p class="text-xs font-weight-bold mb-0">location</p>
                                            </div>
                                        </td>
                                        <td class="w-20">
                                            <div class="dropdown">
                                                <select class="btn bg-info dropdown-toggle" type="button" id="filterAvPM" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <option value="all">Show All</option>
                                                    <option value="AVA">Available</option>
                                                    <option value="NAV">Sold-out</option>    
                                                </select>
                                                <p class="text-xs font-weight-bold mb-0">availability</p>
                                            </div>
                                        </td>
                                        <td class="w-20">
                                            <div class="dropdown">
                                                <select class="btn bg-info dropdown-toggle" type="button" id="filterTypePM" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <option value="all">Show All</option>
                                                    <option value="TEC">Technical</option>
                                                    <option value="R">Recreational</option>    
                                                </select>
                                                <p class="text-xs font-weight-bold mb-0">type</p>
                                            </div>
                                        </td>
                                    </tr> 
                                </table>
                                {{-------------------------}}
                                <table class="table align-items-center" id="tableTripsPM">
                                    <thead class=" text-info">
                                        <th>
                                            Operator
                                        </th>
                                        <th>
                                            Time
                                        </th>
                                        <th class="text-center">
                                            Availability
                                        </th>
                                        <th>
                                            Site / Trip Name
                                        </th>
                                    </thead>
                                    <tbody>
                                        @foreach($trips as $trip)
                                            @php
                                                $hour = (int)substr($trip->departureTime, 0, 2);
                                            @endphp
                                            @if($hour >= 12)
                                                <tr data-tag="{{ $trip->tags }}">
                                                    <td>{{ $trip->operatorName }}</td>
                                                    <td>{{ $trip->departureTime }}</td>
                                                    <td class="text-center">{{ $trip->tripFreeSpots == 1000 ? "Y" : $trip->tripFreeSpots }}</td>
                                                    <td>{{ $trip->tripName }}</td>
                                                </tr>
                                            @endif
                                        @endforeach          
                                    </tbody>
                                </table>
                            </div>    
                        </div>
                    </div>
                </div>
                {{-----------------------------}}
                
            </div>
            
    </div>
    

    <!-- -------- START FOOTER 3 w/ COMPANY DESCRIPTION WITH LINKS & SOCIAL ICONS & COPYRIGHT ------- -->
    <x-auth.footers.guest.social-icons-footer></x-auth.footers.guest.social-icons-footer>
    <!-- -------- END FOOTER 3 w/ COMPANY DESCRIPTION WITH LINKS & SOCIAL ICONS & COPYRIGHT ------- -->
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




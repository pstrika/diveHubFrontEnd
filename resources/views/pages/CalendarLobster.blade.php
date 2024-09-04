<x-page-template bodyClass='g-sidenav-show  bg-gray-200'>
    <x-auth.navbars.sidebar activePage="Calendars" activeItem="lobsterDiving" activeSubitem=""></x-auth.navbars.sidebar>
    
    
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <!-- Navbar -->
        <x-auth.navbars.navs.auth pageTitle="Lobster Diving Calendar {{ $currentMonthS}}-{{ $year }}"></x-auth.navbars.navs.auth>
        <!-- End Navbar -->
            <div class="container-fluid py-4">
                <div class="page-header min-height-250 max-height-300 border-radius-xl mt-4 mx-n2" style="background-image: url('/assets/img/illustrations/calendar.webp');">
                    <span class="mask  bg-gradient-info  opacity-4"></span>
                </div>

                <div class="d-none" data-color="info" id="sidebarColorDiv"></div>

                {{--modal code--}}
                <div class="modal fade" id="modal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="modal-notification" aria-hidden="true">
                    <div class="modal-dialog modal-danger modal-dialog-centered modal-" role="document">
                        <div class="modal-content">
                            <div class="modal-header text-center">
                                <h6 class="modal-title font-weight-normal" id="modal-title-notification">Site levels</h6>
                                {{--<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">--}}
                                <span aria-hidden="true">×</span>
                                </button>
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

                <div class="card p-0 position-relative mt-n7 mx-2 z-index-2">
            
                    <div class="p-0 mt-n4 mx-2 border-radius-lg py-3 pe-1">
                        <div style="float: left;">
                            <h2 class="card-title text-info mx-3 mt-4">{{ $currentMonthS }}-{{ $year }}</h2>
                            <h4 class="card-category text-info mx-4">Lobster Diving Calendar</h4>
                        </div>

                        {{-----------------NAV to next day}} --}}
                        <div class="mt-5" style="float: right;">
                            <a type="button" href="/CalendarLobster/{{ $prevMonthS }}/" class="btn btn-info tex-end">
                                <span class="material-icons" style="font-size :24pt;">keyboard_arrow_left</span>
                            </a>
                            
                            <a type="button" href="/CalendarLobster/{{ $nextMonthS }}/" class="btn btn-info tex-end">
                                <span class="material-icons" style="font-size :24pt;">keyboard_arrow_right</span>
                            </a>
                        </div>
                        <div style="clear: both;"></div>
                        
                    </div>
                </div>
            
                <div class="row">
                    {{--- Shark dive intro --}}
                    <div class="col-md-12">             
                        <div class="card p-0 position-relative mt-5 mx-0 z-index-2 mb-4">
                            <div class="card-header p-0 mt-n4 mx-3">
                                <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                    <h2 class="card-title text-white mx-4">Lobster season in FL</h2>
                                    <div class="table-responsive"></div>
                                </div>
                            </div>
                            <div class="card-body mt-4">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center justify-content-center mt-n3">
                                            <img src="{{ asset('assets') }}/img/illustrations/lobster.webp" class="img-fluid border-radius-xl shadow">
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div id="history" style="flex-grow: 1; max-height: 424px; overflow-y: auto;" class="mt-0">
                                        Sport Season (Mini-Season): The recreational fishery for spiny lobsters begins with a two-day sport season. This season occurs on the last consecutive Wednesday and Thursday of July each year. During the sport season, divers and lobster enthusiasts can head out to catch these delicious crustaceans. Keep in mind that night diving is prohibited in Monroe County during this period.
                                        <br><br>Regular Season: Following the sport season, the regular spiny lobster season starts on August 6 and runs through March 31. During this time, lobster enthusiasts can continue their pursuit of these tasty creatures. The daily bag limit varies depending on the location: 6 per person for Monroe County and Biscayne National Park, and 12 per person for the rest of Florida. Remember to measure the carapace (shell) size, as it must be larger than 3 inches in the water. Also, possession and use of a measuring device are required at all times.
                                        </div>
                                    </div>                            
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="col-md-12">                        
                        <div class="card card-calendar p-0 position-relative mt-4 mx-0 z-index-2 mb-0">
                            {{-- Table for filters--}}
                            <table class="table align-items-left mb-0 mx-3">
                                <tr>
                                    <td>
                                        <div class="dropdown">
                                            <select class="btn bg-info dropdown-toggle text-white" type="button" id="filterCalendarOp" data-bs-toggle="dropdown" aria-expanded="false">
                                                <option value="all">Show All</option>
                                                <option value="opId=14">Emerald Charters</option>
                                                <option value="opId=1">South Florida Diving HQ</option>
                                                <option value="opId=11">Conch Republic</option>
                                                <option value="opId=15">Stuart Scuba</option>
                                                
                                            </select>
                                            <p class="text-xs font-weight-bold mb-0 mt-n3">dive operator</p>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="dropdown">
                                            <select class="btn bg-info dropdown-toggle text-white" type="button" id="filterCalendarAv" data-bs-toggle="dropdown" aria-expanded="false">
                                                <option value="all">Show All</option>
                                                <option value="isAvail=Y">Available</option>
                                                <option value="isAvail=N">Sold-out</option>    
                                            </select>
                                            <p class="text-xs font-weight-bold mb-0 mt-n3">availability</p>
                                        </div>
                                    </td>

                                </tr> 
                                
                                <tr><td class="text-center text-sm w-1"> 
                                    <span class="badge badge-md bg-gradient-success text-white">SFDH</span>
                                    <span class="badge badge-md bg-gradient-danger text-white">Emerald</span>
                                    <span class="badge badge-md bg-gradient-warning text-white">Conch R</span>
                                    <span class="badge badge-md bg-gradient-info text-white">Stuart</span>
                                    <span class="badge badge-md bg-gradient-primary text-white">OTHER</span> 
                                </td></tr>
                                <tr><td><p class="text-xs font-weight-bold mb-0 mt-n3">reference</p></td></tr>
                            </table>
                            {{-------------------------}}
                            
                            {{--Calendar--}}
                            <div class="card-body p-3">
                                <div class="calendar" data-bs-toggle="calendar" id="calendar"></div>
                            </div>
                            {{--------------------------}}
                        </div>
                    </div>

                    <div class="col-md-12">             
                        <div class="card p-0 position-relative mt-5 mx-0 z-index-2 mb-4">
                            <div class="card-header p-0 mt-n4 mx-3">
                                <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                    <h2 class="card-title text-white mx-4">Lobster Dives {{ $currentMonthS }}-{{ $year }}</h2>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    {{-- Table for filters--}}
                                    <table class="table align-items-center mb-0">
                                        <tr>
                                            <td class="w-20">
                                                <div class="dropdown">
                                                    <select class="btn bg-info dropdown-toggle text-white" type="button" id="filterLocAM" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <option value="all">Show All</option>
                                                        <option value="MIA">miami beach</option>
                                                        <option value="FLL">fort lauderdale</option>
                                                        <option value="POM">pompano beach</option>
                                                        <option value="DEB">deerfield beach</option>
                                                        <option value="WPB">west palm beach</option>
                                                        <option value="KLA">key largo</option>
                                                    </select>
                                                    <p class="text-xs font-weight-bold mb-0 mt-n3">location</p>
                                                </div>
                                            </td>
                                            <td class="w-20">
                                                <div class="dropdown">
                                                    <select class="btn bg-info dropdown-toggle text-white" type="button" id="filterAvAM" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <option value="all">Show All</option>
                                                        <option value="AVA">Available</option>
                                                        <option value="NAV">Sold-out</option>    
                                                    </select>
                                                    <p class="text-xs font-weight-bold mb-0 mt-n3">availability</p>
                                                </div>
                                            </td>
                                            

                                            <td>
                                                
                                            </td>
                                        </tr> 
                                    </table>
                                    {{-------------------------}}
                                    <div class="table-responsive">
                                        <table id="tableTripsAM">
                                            <thead class="text-info">
                                                <th class="px-4 align-top">
                                                    Date
                                                </th>
                                                <th class="align-top">
                                                    Operator
                                                </th>
                                                <th class="px-4 align-top">
                                                    Time
                                                </th>
                                                    <th class="py-0 align-top">Availability<p class="text-xs mt-0 px-1">click-to-book</p>
                                                </th>
                                                
                                                <th class="px-4 align-top">
                                                    Site / Trip Name
                                                </th>
                                                <th class="px-4 align-top">
                                                    Level<a href="#" onclick="showModal();"><p class="text-xs text-info text-center mt-0 px-1">(?)</p></a>
                                                </th>
                                                <th class="px-4 align-top" data-bs-toggle="tooltip" data-bs-placement="top" title="site max depth" data-container="body" data-animation="true">
                                                    Depth
                                                </th>
                                            </thead>
                                            <tbody >
                                                @foreach($trips as $trip)
                                                    
                                                    @php
                                                        // do this to avoid printing on the table trips that are not within the current month, but wanted to show them on calendar (check controller)
                                                        $tripDate = new DateTime($trip->date);
                                                        $tripMonth = $tripDate->format('F');
                                                    @endphp
                                                    @if( $tripMonth == $currentMonthS)
                                                    <tr style="border-bottom: 1px solid #D3D3D3;" data-tag="{{ $trip->tags }}">
                                                    <td class="px-4">{{ $trip->date }}</td>
                                                        <td class="px-0 py-2 text-sm text-wrap">{{ $trip->operatorName }}</td>
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
                </div>
            </div>      
            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>
    
    
    {{--<x-plugins></x-plugins>--}}
    
    @push('js')
    
    <script src="/assets/js/plugins/fullcalendar.min.js"></script>
    <script src="{{ asset('assets') }}/js/plugins/jquery-3.6.0.min.js" type="text/javascript"></script>


    <script>
        function showModal() {
            $('#modal').modal('show'); // Show the modal
        };
    </script>
    
    
    <script>
        var calendar = new FullCalendar.Calendar(document.getElementById("calendar"), {
        dateClick: function(info) {
            var link = '/Trips/' + info.dateStr;
            window.location.href = link;
        },
        initialView: "dayGridMonth",
        firstDay: {{ auth()->user()->firstDayOfWeek }},
        contentHeight: 'auto',
        headerToolbar: {
            start: '', //'title', // will normally be on the left. if RTL, will be on the right
            center: '',
            end: ''//'today prev,next' // will normally be on the right. if RTL, will be on the left
        },
        selectable: true,
        editable: false,
        initialDate: '{{ $currentDate }}',
        events: [
            @php
                foreach($trips as $trip) {
                    // fix the ' problem
                    $tripName = str_replace("'", "\\'", $trip->tripName);
                    echo "{";
                    echo "title: '" . (strstr($tripName, '(', true) ? strstr($tripName, '(', true) : $tripName) ."',";
                    echo "start: '" . $trip->date . " " . $trip->departureTime ."',";
                    echo "url: '/TripDetails/" . str($trip->id) . "',";
                    if($trip->operatorId == "1")
                        echo "className: 'bg-gradient-success text-white opId=1 isAvail=" . (($trip->tripFreeSpots > 0) ? "Y" : "N")  . "' },";
                    elseif($trip->operatorId == "14")
                        echo "className: 'bg-gradient-danger text-white opId=3 isAvail=" . (($trip->tripFreeSpots > 0) ? "Y" : "N")  . "' },";
                    elseif($trip->operatorId == "11")
                        echo "className: 'bg-gradient-warning text-white opId=8 isAvail=" . (($trip->tripFreeSpots > 0) ? "Y" : "N")  . "' },";
                    elseif($trip->operatorId == "15")
                        echo "className: 'bg-gradient-info text-white opId=9 isAvail=" . (($trip->tripFreeSpots > 0) ? "Y" : "N")  . "' },";
                    else
                        echo "className: 'bg-gradient-primary text-white isAvail=" . (($trip->tripFreeSpots > 0) ? "Y" : "N")  . "' },";    
                }
            @endphp
            

        ],
        views: {
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
            document.getElementById('filterCalendarOp').addEventListener('change', function() {
                var selectedOption = this.value;
                var rows = document.querySelectorAll('#calendar a[class]');
                
                rows.forEach(function(row) {
                var tags = row.getAttribute('class');
                if (tags.includes('opId=')) {
                    if (tags.includes(selectedOption) || selectedOption === 'all') {
                        row.style.display = ''; // Show the row
                    } else {
                        row.style.display = 'none'; // Hide the row
                    }
                }});
            });
        });

    
    
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            sidebarColor(document.getElementById("sidebarColorDiv")); // Execute the sidebarColor function once the HTML is loaded
        });
    </script>
    @endpush
</x-page-template>

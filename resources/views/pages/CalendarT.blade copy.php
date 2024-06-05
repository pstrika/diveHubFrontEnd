<x-page-template bodyClass='g-sidenav-show  bg-gray-200'>
    <x-auth.navbars.sidebar activePage="Calendars" activeItem="CalendarT" activeSubitem=""></x-auth.navbars.sidebar>
    
    
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <!-- Navbar -->
        <x-auth.navbars.navs.auth pageTitle="Technical Calendar {{ $currentMonthS}}-{{ $year }}"></x-auth.navbars.navs.auth>
        <!-- End Navbar -->
        <div class="container-fluid py-4">
            
            <div class="card">
                <div class="card-header bg-gradient-info">
                
                    
                    
                    <div style="float: left;">
                        <h2 class="card-title text-white">{{ $currentMonthS }}-{{ $year }}</h2>
                        <h4 class="card-category text-white">Technical Calendar</h4>
                    </div>

                    {{-----------------NAV to next day}} --}}
                    <div class="mt-4" style="float: right;">
                        <a type="button" href="/CalendarT/{{ $prevMonthS }}/" class="btn btn-info tex-end">
                            <span class="material-icons" style="font-size :30pt;">keyboard_arrow_left</span>
                        </a>
                        
                        <a type="button" href="/CalendarT/{{ $nextMonthS }}/" class="btn btn-info tex-end">
                            <span class="material-icons" style="font-size :30pt;">keyboard_arrow_right</span>
                        </a>
                    </div>
                    <div style="clear: both;"></div>
                    {{---------------------------------------}}
                </div>

                {{-- Calendar --}}
                <div class="row">
                    
                    <div class="col-md-12 ">             
                        <div class="card card-calendar">
                            {{-- Table for filters--}}
                            <table class="table align-items-center mb-0">
                                <tr>
                                    <td class="w-20">
                                        <div class="dropdown">
                                            <select class="btn bg-info dropdown-toggle w-100 text-white" type="button" id="filterCalendarOp" data-bs-toggle="dropdown" aria-expanded="false">
                                                <option value="all">Show All</option>
                                                <option value="opId=1">South Florida Diving HQ</option>
                                                <option value="opId=3">Pura Vida Divers</option>
                                                <option value="opId=8">Diver Paradise</option>
                                                <option value="opId=9">Horizon Divers</option>
                                                
                                            </select>
                                            <p class="text-xs font-weight-bold mb-0 mt-n3">dive operator</p>
                                        </div>
                                    </td>

                                    <td class="w-20">
                                        <div class="dropdown">
                                            <select class="btn bg-info dropdown-toggle w-100 text-white" type="button" id="filterCalendarAv" data-bs-toggle="dropdown" aria-expanded="false">
                                                <option value="all">Show All</option>
                                                <option value="isAvail=Y">Available</option>
                                                <option value="isAvail=N">Sold-out</option>    
                                            </select>
                                            <p class="text-xs font-weight-bold mb-0 mt-n3">availability</p>
                                        </div>
                                    </td>

                                    <td>
                                        
                                    </td>
                                </tr> 
                            </table>
                            {{-------------------------}}


                            <div class="card-body p-3">
                                <div class="calendar" data-bs-toggle="calendar" id="calendar"></div>
                            </div>
                        </div>
                    </div>
                </div>
                {{------------------------}}

                
                <div class="row">
                    {{-- Dive conditions card AM --}}
                    <div class="col-md-12">
                        <div class="card p-0 position-relative mt-3 mx-3 z-index-2">
                            <div class="card-header bg-gradient-info">
                                    <h2 class="card-title text-white">Technical dives</h4>
                                    
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">

                                    {{-- Table for filters--}}
                                    <table class="table align-items-center mb-0">
                                        <tr>
                                            <td class="w-20">
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
                                            <td class="w-20">
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
                                                    <th class="py-0">Availability<p class="text-xs mt-0 px-1">click-to-book</p>
                                                </th>
                                                <th class="px-4 align-top">
                                                    Site / Trip Name
                                                </th>
                                            </thead>
                                            <tbody >
                                                @foreach($trips as $trip)
                                                    <tr style="border-bottom: 1px solid #D3D3D3;" data-tag="{{ $trip->tags }}">
                                                    <td class="px-4">{{ $trip->date }}</td>
                                                        <td class="px-0 py-2 text-sm" style="min-width: 200px;">{{ $trip->operatorName }}</td>
                                                        <td class="px-4">{{ $trip->departureTime }}</td>
                                                        @if($trip->tripFreeSpots == 0)
                                                            <td class="text-center">-</td>
                                                        @else
                                                            <td class="text-center"> <a href="{{ $trip->linkToBook }}" target="_blank">{{ $trip->tripFreeSpots == 1000 ? "Y" : $trip->tripFreeSpots }}</a></td>
                                                        @endif
                                                        <td class="px-4 text-sm">{{ $trip->tripName }}</td>
                                                    </tr>
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
    
    
    {{--<x-plugins></x-plugins>--}}
    
    @push('js')
    
    <script src="../../assets/js/plugins/fullcalendar.min.js"></script>
    <script>
        var calendar = new FullCalendar.Calendar(document.getElementById("calendar"), {
        initialView: "dayGridMonth",
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
                    echo "{";
                    echo "title: '" . (strstr($trip->tripName, '(', true) ? strstr($trip->tripName, '(', true) : $trip->tripName) ."',";
                    echo "start: '" . $trip->date . " " . $trip->departureTime ."',";
                    echo "url: 'https://google.com/',";
                    if($trip->operatorId == "1")
                        echo "className: 'bg-gradient-success text-white opId=1 isAvail=" . (($trip->tripFreeSpots > 0) ? "Y" : "N")  . "' },";
                    elseif($trip->operatorId == "3")
                        echo "className: 'bg-gradient-danger text-white opId=3 isAvail=" . (($trip->tripFreeSpots > 0) ? "Y" : "N")  . "' },";
                    elseif($trip->operatorId == "8")
                        echo "className: 'bg-gradient-warning text-white opId=8 isAvail=" . (($trip->tripFreeSpots > 0) ? "Y" : "N")  . "' },";
                    elseif($trip->operatorId == "9")
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
    @endpush
</x-page-template>

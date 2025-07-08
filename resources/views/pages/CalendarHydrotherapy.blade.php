<x-page-template bodyClass='bg-gray-200'>
    
    <style>
        .past-day {
            background-color:rgb(218, 218, 218); /* Light grey color */
        },
    </style>
    <style>
        .recreational-event {
            background-color: #004652; /* Green color for recreational events */
            border-color: #004652;
        },
    </style>
    <style>
        .recreational-event:hover {
            background-color: #33a7a4; /* Green color for recreational events */
            border-color: #33a7a4;
        },
    </style>
    <style>
        .technical-event {
            background-color: #5a5a5a; /* Yellow color for technical events */
            border-color: #5a5a5a;
        },
    </style>
    <style>
        .technical-event:hover {
            background-color: #33a7a4; /* Yellow color for technical events */
            border-color: #33a7a4;
        },
    </style>
    <style>
        /* Styles for larger screens (desktops) */
        @media (min-width: 768px) {
        .fc-event-title {
            white-space: normal !important;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        }
    </style>

    <style>
        /* Styles for smaller screens (mobile devices) */
        @media (max-width: 767px) {
        .fc-event-title {
            white-space: nowrap !important;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        }
    </style>
    <style>
        .custom-hover-button {
            background-color: #004652; /* Initial color */
        }
    </style>
    <style>
        .custom-hover-button:hover {
            background-color: #33a7a4; /* Change to your desired hover color, e.g., tomato */
        }
    </style>

    <style>
        .fc-button-primary {
        background-color: #004652 !important; /* Desired color */
        color: white !important; /* Text color */
        border: none !important; /* Optional: Remove border */
        }
    </style>
    <style>
        .fc-button-primary:hover {
        background-color: #33a7a4 !important; /* Optional: Slightly darker color on hover */
        color: white !important; /* Text color on hover */
        }
    </style>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
            <div class="container-fluid py-4">
                {{-- <div class="page-header min-height-250 max-height-300 border-radius-xl mt-4 mx-n2" style="background-image: url('/assets/img/illustrations/calendar.webp');">
                    <span class="mask  bg-gradient-info  opacity-4"></span>
                </div> --}}

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
                                                
                                                <tr><td class="text-secondary text-end text-lg font-weight-bolder"><img src="{{ asset('assets') }}/img/icons/icons_level_0.png" alt="OW" height="25"></td>
                                                <td class="align-middle text-info text-start text-sm"><b>Open Water</b></td> 
                                                <td class="align-middle text-info text-center text-sm"><b>60</b></td> </tr>

                                                <tr><td class="text-secondary text-end text-lg font-weight-bolder"><img src="{{ asset('assets') }}/img/icons/icons_level_1.png" alt="AOW" height="25"></td>
                                                <td class="align-middle text-info text-start text-sm"><b>Advanced Open Water</b></td>
                                                <td class="align-middle text-info text-center text-sm"><b>130</b></td> </tr>

                                                <tr><td class="text-secondary text-end text-lg font-weight-bolder opacity-7"><img src="{{ asset('assets') }}/img/icons/icons_level_2.png" alt="Ta" height="25"></td>
                                                <td class="align-middle text-info text-start text-sm"><b>Technical Air</b></td>
                                                <td class="align-middle text-info text-center text-sm"><b>150</b></td> </tr>

                                                <tr><td class="text-secondary text-end text-lg font-weight-bolder"><img src="{{ asset('assets') }}/img/icons/icons_level_3.png" alt="Tn" height="25"></td>
                                                <td class="align-middle text-info text-start text-sm"><b>Technical Normoxic Trimix</b></td>
                                                <td class="align-middle text-info text-center text-sm"><b>200</b></td> </tr>

                                                <tr><td class="text-secondary text-end text-lg font-weight-bolder"><img src="{{ asset('assets') }}/img/icons/icons_level_4.png" alt="Th" height="25"></td>
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

                <div class="card p-0 position-relative mt-0 mx-2 z-index-2">
                {{-- <div class="card p-0 position-relative mt-n7 mx-2 z-index-2"> --}}
            
                    <div class="p-0 mt-n4 mx-2 border-radius-lg py-3 pe-1">
                        <div style="float: left;">
                            <h2 class="card-title mx-3 mt-4" id="calendar-title" style="color: #004652;">{{ $currentMonthS }}-{{ $year }}</h2>
                            <h4 class="card-category mx-3" style="color: #004652;">Click on calendar to book</h4>
                        </div>

                        {{-----------------NAV to next day}} --}}
                        <div class="mt-5" style="float: right;">
                            <a type="button" href="/CalendarHydrotherapy/{{ $prevMonthS }}/" class="btn btn-white tex-end custom-hover-button">
                                <span class="material-icons" style="font-size :24pt; color: white;">keyboard_arrow_left</span>
                            </a>
                            
                            <a type="button" href="/CalendarHydrotherapy/{{ $nextMonthS }}/" class="btn btn-info tex-end custom-hover-button">
                                <span class="material-icons" style="font-size :24pt; color: white;">keyboard_arrow_right</span>
                            </a>
                        </div>
                        <div style="clear: both;"></div>
                        
                    </div>
                </div>
            
                <div class="row">
                     <div class="col-md-12">                        
                        <div class="card card-calendar p-0 position-relative mt-4 mx-0 z-index-2 mb-0">
                            {{-- Table for filters--}}
                            <table class="table align-items-left mb-0 mx-3">
                                <tr>
                                    

                                    <td>
                                        <ul class="list-group">
                                            <li class="list-group-item border-0 px-0">
                                                <div class="form-check form-switch ps-0">
                                                    <input class="form-check-input ms-auto" type="checkbox"
                                                        id="filterAv">
                                                    <label class="form-check-label text-body ms-3 text-truncate w-80 mb-0"
                                                        for="flexSwitchCheckDefault">Show available only</label>
                                                </div>
                                            </li>
                                            
                                        </ul>
                                    </td>
                                    <td>
                                        
                                    </td>

                                </tr> 
                                
                                <tr><td class="text-center text-sm w-1"> 
                                    <span class="badge badge-md text-white" style="background-color: #004652;">Recreational</span>
                                    <span class="badge badge-md text-white" style="background-color: #5a5a5a;">Technical</span>
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

                
                </div>
            </div>      
            <x-auth.footers.auth.hydrotherapy></x-auth.footers.auth.hydrotherapy>
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
        initialView: "dayGridMonth",
        firstDay: {{ auth()->user()->firstDayOfWeek }},
        dateClick: function(info) {
            // Change to day view
            calendar.changeView('timeGridDay', info.dateStr);

            // Format and update the <h2> title
            var selectedDate = new Date(info.dateStr + 'T00:00:00'); 
            var options = { weekday: 'long', month: 'long', day: 'numeric' };
            var formattedDate = selectedDate.toLocaleDateString('en-US', options);
            document.getElementById('calendar-title').textContent = formattedDate;
        },
        
        contentHeight: 'auto',
        headerToolbar: {
            start: '', // Title position
            center: 'customButton',
            end: '' // Custom button position
        },
        customButtons: {
            customButton: {
                text: 'Back to Month View',
                click: function() {
                    calendar.changeView('dayGridMonth');

                    // Reset <h2> title back to current month and year
                    var currentMonthYear = new Date('{{ $currentDate }}').toLocaleDateString('en-US', {
                        month: 'long',
                        year: 'numeric'
                    });
                    document.getElementById('calendar-title').textContent = currentMonthYear;
                },
                className: 'back-to-month-view-button'
            }
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
                    echo "title: '" . (strstr($tripName, '(', true) ? strstr($tripName, '(', true) : $tripName) . " (" . str($trip->tripFreeSpots) . "/" . str($trip->boatCapacity) . ")" . "',";
                    echo "start: '" . $trip->date . " " . $trip->departureTime ."',";
                    echo "end: '" . $trip->date . " " . date('H:i', strtotime('+210 minutes', strtotime($trip->departureTime))) ."',"; 
                    echo "url: '" . $trip->linkToBook . "',";
                    if($trip->tripType == "Technical") {
                        echo "className: 'technical-event text-white isAvail=" . (($trip->tripFreeSpots > 0) ? "Y" : "N")  . "' },";
                    } else {
                        echo "className: 'recreational-event text-white isAvail=" . (($trip->tripFreeSpots > 0) ? "Y" : "N")  . "' },";
                    }
                }
            @endphp
        ],
        eventClick: function(info) {
            if (info.event.url) {
                window.open(info.event.url, '_parent');
                info.jsEvent.preventDefault();
            }
        },
        views: {
            month: {
                titleFormat: { month: "long", year: "numeric" }
            },
            agendaWeek: {
                titleFormat: { month: "long", year: "numeric", day: "numeric" }
            },
            agendaDay: {
                titleFormat: { month: "short", year: "numeric", day: "numeric" }
            }
        },
        viewDidMount: function(info) {
            if (info.view.type === 'timeGridDay') {
                document.querySelector('.fc-customButton-button').style.display = 'inline-block';
            } else {
                document.querySelector('.fc-customButton-button').style.display = 'none';
            }
        },
        viewWillUnmount: function(info) {
            document.querySelector('.fc-customButton-button').style.display = 'none';
        },
        dayCellDidMount: function(info) {
            var today = new Date();
            today.setHours(0, 0, 0, 0);
            var date = info.date;
            if (date < today) {
                info.el.classList.add('past-day');
            }
        }
    });

    calendar.render();

    // Apply CSS styles using JavaScript
    var style = document.createElement('style');
    style.innerHTML = `
    .fc-event-title {
        white-space: normal !important;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    @media (max-width: 767px) {
        .fc-event-title {
            white-space: nowrap !important;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    }`;
    document.head.appendChild(style);

    </script>

    <script>
        // Wait for the DOM to load
        document.addEventListener("DOMContentLoaded", function() {
            var links = document.querySelectorAll('a:not([type="button"])');

            links.forEach(function(link) {
                link.setAttribute('target', '_parent');
        });
        });
    </script>






    {{--Handler for tripAM table: filter by location--}}
    <script>


        // Filter AM Availability
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('filterAv').addEventListener('change', function() {
                var selectedOption = 'all';
                if (this.checked) {
                    selectedOption = 'AVA';
                }
                
                var rows = document.querySelectorAll('#tableTrips tr[data-tag]');
                
                rows.forEach(function(row) {
                var tags = row.getAttribute('data-tag');
                if (tags.includes(selectedOption) || selectedOption === 'all') {
                    row.style.display = ''; // Show the row
                } else {
                    row.style.display = 'none'; // Hide the row
                }
                });

                var rows = document.querySelectorAll('#calendar a[class]');
                rows.forEach(function(row) {
                    var tags = row.getAttribute('class');
                    if(selectedOption == 'all') {
                        row.style.display = '';    
                    } else {
                        if (tags.includes('isAvail=')) {
                            if (tags.includes('Y')) {
                                row.style.display = ''; // Show the row
                            } else {
                                row.style.display = 'none'; // Hide the row
                            }
                        }
                    }
                });

                filterOp.value = 'all';
               
            });
        });

    
    
    </script>

    @endpush
</x-page-template>

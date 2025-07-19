<x-page-template bodyClass='g-sidenav-show  bg-gray-200'>
    <x-auth.navbars.sidebar activePage="Dashboard" activeItem="Dashboard" activeSubitem=""></x-auth.navbars.sidebar>
    
    
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <!-- Navbar -->
        <x-auth.navbars.navs.auth pageTitle="My Dashboard"></x-auth.navbars.navs.auth>
        <!-- End Navbar -->
        <div class="container-fluid py-0">

        
        {{--modal edit calendar--}}
        <div class="modal fade" id="modal-calendar" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="modal-notification" aria-hidden="true">
            <div class="modal-dialog modal-danger modal-dialog-centered modal-" role="document">
                <div class="modal-content">
                    <span id="span-booked" class="badge badge-md bg-gradient-success text-white">Booked</span>
                    <span id="span-not-booked" class="badge badge-md bg-gradient-danger text-white">Not Booked</span>
                    <div class="modal-header text-center">
                        
                        <h6 class="modal-title font-weight-normal text-start" id="modal-title-notification-calendar">Edit calendar</h6>
                        {{--<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">--}}
                        {{--<span aria-hidden="true">×</span>--}}
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="py-3 text-center">
                            <h4 class="text-gradient text-info text-md mt-4"></h4>
                            <div class="table-responsive">
                                <table class="table align-items-left mb-0"> 
                                    <tbody>
                                        
                                        <tr>
                                            <td class="align-middle text-center text-sm">    
                                                <a id="button-go" href=""><button class="btn btn-icon btn-3 btn-info" type="button" data-bs-toggle="tooltip" data-bs-placement="top" title="See details for this trip">
                                                    <span class="btn-inner--icon"><i class="material-icons">visibility</i></span>
                                                    {{--<span class="btn-inner--text">Go to trip</span>--}}
                                                </button></a>
                                            </td>
                                        
                                            <td id="div-button-link" class="align-middle text-center text-sm">    
                                                <a id="button-link" href="" target="_blank"><button class="btn btn-icon btn-3 btn-info" type="button" data-bs-toggle="tooltip" data-bs-placement="top" title="Open booking page">
                                                    <span class="btn-inner--icon"><i class="material-icons">link</i></span>
                                                    {{--<span class="btn-inner--text">Click to book</span>--}}
                                                </button></a>
                                            </td>

                                            <td id="div-button-waiver" class="align-middle text-center text-sm">    
                                                <a id="button-waiver" href="" target="_blank"><button class="btn btn-icon btn-3 btn-info" type="button" data-bs-toggle="tooltip" data-bs-placement="top" title="Open online waiver">
                                                    <span class="btn-inner--icon"><i class="material-icons">description</i></span>
                                                    {{--<span class="btn-inner--text">Click to book</span>--}}
                                                </button></a>
                                            </td>
                                        
                                        
                                            <td id="div-button-book" class="align-middle text-center text-sm">    
                                                <a id="button-book" href=""><button class="btn btn-icon btn-3 btn-success" type="button" data-bs-toggle="tooltip" data-bs-placement="top" title="I'm already booked for this trip!">
                                                    <span class="btn-inner--icon"><i class="material-icons">check</i></span>
                                                    {{--<span class="btn-inner--text">I'm booked already!</span>--}}
                                                </button></a>
                                            </td>
                                        
                                        
                                        
                                            <td class="align-middle text-center text-sm">    
                                                <a id="button-remove" href=""><button class="btn btn-icon btn-3 btn-danger" type="button" data-bs-toggle="tooltip" data-bs-placement="top" title="Remove trip from My Calendar">
                                                    <span class="btn-inner--icon"><i class="material-icons">delete</i></span>
                                                    {{--<span class="btn-inner--text">Remove from calendar</span>--}}
                                                </button></a>
                                            </td>
                                        </tr>
                                        

                                    </tbody>
                                </table>
                            </div>   
                            <p>Press anywhere outside this dialog to continue</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>


            <div class="page-header min-height-250 max-height-300 border-radius-xl mt-4 mx-n2" style="background-image: url('/assets/img/illustrations/dashboard1.jpeg');">
                <span class="mask  bg-gradient-info  opacity-4"></span>
            </div>

            <div class="card p-0 position-relative mt-n5 mx-1 z-index-2 mb-4">
                
                    <div class="p-0 mt-0 mx-2 border-radius-lg py-3 pe-1">
                        <div style="float: left;">
                            <h1 class="card-title text-info mx-3 mt-0 text-xl">My Dashboard</h1>
                        </div>

                    </div>
                </div>
            </div>

            @if( !empty($favOperators) )
            <div class="row mx-1 mt-n1">
                <div class="col-md-12">             
                    <div class="card p-0 position-relative mt-5 mx-0 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h2 class="card-title text-white mx-4">My favorites dive calendars</h2>
                                <div class="table-responsive"></div>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <table>
                                <tr></td>
                                    <div class="d-flex align-items-center gap-3">
                                        <!-- Dropdown -->
                                        <div class="dropdown">
                                            <select class="btn bg-info dropdown-toggle text-white" id="filterOperators" data-bs-toggle="dropdown" aria-expanded="false">
                                                @foreach($favOperators as $favOperator)
                                                    <option value="{{ $favOperator->id }}">{{ $favOperator->operatorName }}</option>
                                                @endforeach
                                            </select>
                                            <p class="text-xs font-weight-bold mb-2 mt-n3">dive operator</p>
                                        </div>
                                        

                                        <!-- Logo -->
                                        <div class="text-left mx-n2 mt-n3">
                                            <img id="operatorLogo" src="/images/default-logo.png" height="45" alt="Operator Logo">
                                            
                                        </div>
                                    </div>
                                </td></tr>
                                <tr><td class="text-start text-sm w-1"> 
                                    <span class="badge badge-md bg-gradient-secondary text-white mx-0">Recreational</span>
                                    <span class="badge badge-md bg-gradient-success text-white">Technical</span>
                                </td></tr>
                                <tr><td><p class="text-xs font-weight-bold mb-0 mt-0 mx-0">reference</p></td></tr>
                            </table>

                            <div class="calendar" data-bs-toggle="calendar" id="calendar"></div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            
            <div class="row mx-1">
                {{---Card My Upcoming trips--}}
                <div class="col-md-4 mb-4">
                    <div class="card mt-3">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h2 class="card-title text-white mx-4">My upcoming dives</h2>
                                <div class="table-responsive"></div>
                            </div>
                        </div>
                        <div class="card-body p-3" style="display: block; max-height: 350px; overflow-y: scroll">
                            @if(count($trips) == 0)
                                <p>You have no upcoming dives in you calendar. Go to <a href=" {{ route('Trips')}} "><b class="text-info">"Upcoming Trips"</b></a> on the menu, select and add trips to your personal calendar.</p>
                            @else
                                <div class="timeline timeline-one-side" data-timeline-axis-style="dotted">
                                    @foreach($trips as $trip)
                                        <div class="timeline-block mb-3">
                                            <a href="javascript:clickOnMyTrips({{ $trip->eventId }});">
                                                <span class="timeline-step bg-{{ $trip->booked ? "success" : "danger" }} p-3">
                                                    
                                                        @if(strstr($trip->tags, "SHA"))
                                                            <span class="d-flex align-items-center"><img style="height:20px;" src="{{ asset('assets') }}/img/icons/icons_shark_center.png" alt="S"></span>
                                                        @elseif(strstr($trip->tags, "TEC"))
                                                            <span class="d-flex align-items-center"><img style="height:20px;" src="{{ asset('assets') }}/img/icons/icons_tec_center.png" alt="T"></span>
                                                        @elseif(strstr($trip->tags, "LOB"))
                                                            <span class="d-flex align-items-center"><img style="height:20px;" src="{{ asset('assets') }}/img/icons/icons_lobster_center.png" alt="L"></span>
                                                        @else
                                                            <span class="d-flex align-items-center"><img style="height:20px;" src="{{ asset('assets') }}/img/icons/icons_rec_center.png" alt="R"></span>
                                                        @endif
                                                    
                                                </span>
                                            </a>
                                            <div class="timeline-content pt-1">
                                                <label id="upComingTripsDate-{{ $trip->eventId }}" hidden>{{ $trip->date }}</label>
                                                <label id="upComingTripsTime-{{ $trip->eventId }}" hidden>{{ $trip->departureTime }}</label>
                                                <label id="upComingTripsEventId-{{ $trip->eventId }}" hidden>{{ $trip->eventId }}</label>
                                                <label id="upComingTripsTripId-{{ $trip->eventId }}" hidden>{{ $trip->id }}</label>
                                                <label id="upComingTripsLinkToBook-{{ $trip->eventId }}" hidden>{{ $trip->linkToBook }}</label>
                                                <label id="upComingTripsWaiver-{{ $trip->eventId }}" hidden>{{ $trip->waiver }}</label>
                                                <label id="upComingTripsBooked-{{ $trip->eventId }}" hidden>{{ $trip->booked}}</label>
                                                <label id="upComingTripsOperator-{{ $trip->eventId }}" hidden>{{ $trip->operatorName}}</label>
                                                <label id="upComingTripsTitle-{{ $trip->eventId }}" hidden>{{ $trip->tripName}}</label>
                                                <a href="{{ route('TripDetails', ['tripId' => $trip->id]) }}">
                                                    <h6 class="do-not-translate text-dark text-sm font-weight-bold mb-0">{{ $trip->tripName}}</h6>
                                                </a>
                                                <?php 
                                                    $dateTime = DateTime::createFromFormat('Y-m-d', $trip->date);
                                                    $formattedDate = $dateTime->format('l, M j, Y');
                                                ?>
                                                <p class="text-secondary text-xs mt-1 mb-0"> {{$formattedDate}} <b>({{ $trip->departureTime}})</b></p>
                                                <p class="text-sm text-bold text-info mt-1 mb-2">
                                                <a href="{{ route('OperatorDetails', ['id' => $trip->operatorId] )}}">{{ $trip->operatorName }}</a>
                                                </p>
                                            </div>
                                        </div>
                                    @endforeach
                                    
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{---Card recommended for this weekend --}}
                <div class="col-md-8">             
                    <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h2 class="card-title text-white mx-4">Recommended for this weekend</h2>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php
                                $hasFavorite = false;

                                foreach ($favTrips as $trip) {
                                    if (isset($trip['fav']) && $trip['fav'] === 1) {
                                        $hasFavorite = true;
                                        break;
                                    }
                                }
                            ?>
                            @if(!$hasFavorite)
                                <p>There is no recommendation for this weekend at the moment. Go to <a href=" {{ route('overview') }}"><b class="text-info">"My Profile"</b></a> on the menu to set your favorite preferences.</p>
                            @else
                                <div class="table-responsive">
                                    <div class="table-responsive">
                                        <table id="tableTrips" style="display: block; max-height: 300px; overflow-y: scroll">
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
                                                @foreach($favTrips as $trip)
                                                    
                                                    @php
                                                        //because we get all the dives, we check if it's a fav
                                                        if(!$trip->fav)
                                                            continue;
                                                        // do this to avoid printing on the table trips that are not within the current month, but wanted to show them on calendar (check controller)
                                                        $tripDate = new DateTime($trip->date);
                                                        $tripMonth = $tripDate->format('F');
                                                    @endphp
                                                    
                                                    <tr style="border-bottom: 1px solid #D3D3D3;" data-tag="{{ $trip->tags }}">
                                                    <td class="px-4">{{ $tripDate->format('D') }} {{ $tripDate->format('M-d') }}</td>
                                                        <td class="px-0 py-2 text-sm text-wrap"><a href="{{ route('OperatorDetails', ['id' => $trip->operatorId] )}}">{{ $trip->operatorName }}</a></td>
                                                        <td class="px-4">{{ $trip->departureTime }}</td>
                                                        @if($trip->tripFreeSpots == 0)
                                                            <td class="text-center">-</td>
                                                        @else
                                                            <td class="text-center"> <a href="{{ $trip->linkToBook }}" target="_blank">{{ $trip->tripFreeSpots == 1000 ? "Y" : $trip->tripFreeSpots }}</a></td>
                                                        @endif
                                                        
                                                        

                                                        <td class="do-not-translate px-4 text-sm"><a href="{{ route('TripDetails', ['tripId' => $trip->id]) }}">{{ $trip->tripName }}</a></td>

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
                                                    
                                                @endforeach          
                                            </tbody>
                                        </table>
                                    </div>
                                </div>    
                            @endif
                        </div>
                    </div>
                </div>

                {{---Card My wishlist --}}
                <div class="col-md-12">
                    <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h2 class="card-title text-white mx-4"><i class="material-icons justify-content-middle align-middle" style="font-size: 40px;">favorite</i>My wishlist</h2>
                            </div>
                        </div>

                        <div class="card-body">
                            
                            @if(!count($wished))
                                <p>You have no sites in your wishlist. You can go to Dive Sites and add them to your wishlist</p>
                            @else
                                <div class="table-responsive">
                                    <div class="table-responsive">
                                        <table id="tableTrips" style="display: block; max-height: 300px; overflow-y: scroll">
                                            <thead class="text-info">
                                            <th class="align-top">
                                                    Type
                                                </th>
                                                <th class="px-4 align-top">
                                                    Site
                                                </th>
                                                <th class="px-4 align-top">
                                                    Level<a href="#" onclick="showModal();"><p class="text-xs text-info text-center mt-0 px-1">(?)</p></a>
                                                </th>
                                                <th class="px-4 align-top" data-bs-toggle="tooltip" data-bs-placement="top" title="site max depth" data-container="body" data-animation="true">
                                                    Depth
                                                </th>
                                                <th class="align-top">
                                                    Operator
                                                </th>
                                                <th class="px-4 align-top">
                                                    Date
                                                </th>
                                                <th class="px-4 align-top">
                                                    Time
                                                </th>
                                                    <th class="py-0 align-top">Availability<p class="text-xs mt-0 px-1">click-to-book</p>
                                                </th>
                                                <th class="px-4 align-top">
                                                    Trip Name
                                                </th>
                                                
                                            </thead>
                                            <tbody >
                                                @foreach($wished as $wish)
                                                    
                                                    
                                                    <tr style="border-bottom: 1px solid #D3D3D3;">
                                                        <td class="w-5 text-center align-middle"><img src="{{ asset('assets') }}/img/icons/{{ $wish->site->type }}_icon.png" height="35"></td>
                                                        <td class="do-not-translate px-4 text-sm text-left"> <a href="SiteDetails/{{$wish->site->id}}">{{ $wish->site->name}}</a></td>
                                                        <td class="text-center" style="border: none;"><img src="{{ asset('assets') }}/img/icons/icons_level_{{ $wish->site->level }}.png" height="25"></td>
                                                        <td class="px-4 text-sm text-center">{{ $wish->site->maxDepth }}</td>
                                                        {{--<td class="px-4 text-sm text-left"> <a href=" {{ route('OperatorDetails', ['id' => $wish->operatorId])}}">{{ $wish->operator}}</a></td>--}}
                                                        <td class="px-4 text-sm text-left"> <a href="OperatorDetails/{{$wish->operatorId}}">{{ $wish->operator}}</a></td>
                                                        <td class="px-4 text-sm text-left"> {{ $wish->date}}</td>
                                                        <td class="px-4 text-sm text-left"> {{ $wish->time}}</td>
                                                        <td class="px-4 text-sm text-left"> <a href="{{ $wish->linkToBook }}">{{ $wish->tripFreeSpots == 1000 ? "Y" : $wish->tripFreeSpots }}</a></td>
                                                        <td class="do-not-translate px-4 text-sm text-left"> <a href="TripDetails/{{ $wish->tripId}}">{{ $wish->tripName}}</a></td>

                                                    </tr>
                                                    
                                                @endforeach          
                                            </tbody>
                                        </table>
                                    </div>
                                </div>    
                            @endif
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
    <script src="/assets/js/plugins/fullcalendar.min.js"></script>
    <link href="{{ asset("assets") }}/css/calendar-buttons.css" rel="stylesheet" />

    <!-- Google tag (gtag.js) event -->
    @if(session('newUser'))
    <script>
        gtag('event', 'conversion_event_signup', {
            // <event_parameters>
        });
        gtag('event', 'conversion_subscribe_paid', {
            // <event_parameters>
        });
    </script>
    @endif

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

    <script>

        function clickOnMyTrips(id) {
            //mandar modal aca
            
            const parsedDate = new Date(document.getElementById("upComingTripsDate-" + id).innerText + ' ' + document.getElementById("upComingTripsTime-" + id).innerText);

            // Get the components you need
            const day = parsedDate.getDate();
            const month = parsedDate.toLocaleString('default', { month: 'short' });
            const year = parsedDate.getFullYear();
            const hours = parsedDate.getHours();
            const minutes = parsedDate.getMinutes();

            const operator = document.getElementById("upComingTripsOperator-" + id).innerText;
            const title = document.getElementById("upComingTripsTitle-" + id).innerText;
            const eventId = document.getElementById("upComingTripsEventId-" + id).innerText;
            const tripId = document.getElementById("upComingTripsTripId-" + id).innerText;
            const linkToBook = document.getElementById("upComingTripsLinkToBook-" + id).innerText;
            const waiver = document.getElementById("upComingTripsWaiver-" + id).innerText;
            const booked = document.getElementById("upComingTripsBooked-" + id).innerText;
            // Create the desired string
            const formattedString = `${day} ${month} ${year} ${hours}:${String(minutes).padStart(2, '0')}`;
            var modal = document.getElementById('modal-title-notification-calendar');
            modal.innerHTML = "Edit event in calendar: <br>" + formattedString + "<br> <b>" + title + "</b> <br> <p class='text-info text-sm text-bold'>" + operator + "<p>";
            $('#modal-calendar').modal('show');

            document.getElementById("button-go").href = '/TripDetails/' + tripId;
            document.getElementById("button-book").href = '/SetEventBook/' + eventId;
            document.getElementById("button-remove").href = '/RemoveFromCalendar/' + eventId;
            document.getElementById("button-link").href = linkToBook;
            document.getElementById("button-waiver").href = waiver;

            if(booked == '1') {
                document.getElementById("div-button-book").hidden = true;
                document.getElementById("div-button-link").hidden = true;
                document.getElementById("span-booked").hidden = false;
                document.getElementById("span-not-booked").hidden = true;
            } else {
                document.getElementById("div-button-book").hidden = false;
                document.getElementById("div-button-link").hidden = false;
                document.getElementById("span-booked").hidden = true;
                document.getElementById("span-not-booked").hidden = false;
            }

            // check if we have a waiver link. If not we hide the button
            if(waiver)
                document.getElementById("div-button-waiver").hidden = false;
            else
                document.getElementById("div-button-waiver").hidden = true;
        }
    </script>

     <script>
    const favOperators = @json($favOperators);

    // 🔄 Make applyFilter available globally
    function applyFilter() {
        const filterDropdown = document.getElementById('filterOperators');
        const operatorLogo = document.getElementById('operatorLogo');
        const selectedId = filterDropdown.value;

        // Update logo
        const selectedOperator = favOperators.find(op => op.id == selectedId);
        if (selectedOperator) {
            operatorLogo.src = '{{ asset('assets') }}/' + selectedOperator.logoUrl || '/images/default-logo.png';
        } else {
            operatorLogo.src = '/images/default-logo.png';
        }

        // Filter calendar events
        const rows = document.querySelectorAll('#calendar a[class]');
        rows.forEach(function(row) {
            const tags = row.getAttribute('class');
            if (tags.includes('fc-daygrid-event')) {
                if (tags.includes('op=' + selectedId + 'f')) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const filterDropdown = document.getElementById('filterOperators');

        // Initial filter
        if (filterDropdown.options.length > 0) {
            filterDropdown.selectedIndex = 0;
            applyFilter();
        }

        // Reapply on change
        filterDropdown.addEventListener('change', applyFilter);

        // Reapply on window resize (debounced)
        let resizeTimeout;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(applyFilter, 300);
        });
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
            applyFilter();
        },
        datesSet: function() {
            setTimeout(() => {
                applyFilter();
            }, 50); // Slight delay to wait for DOM stabilization
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
                foreach($favCalendars as $trip) {
                    // fix the ' problem
                    $tripName = str_replace("'", "\\'", $trip->tripName);
                    echo "{";
                    echo "title: '" . (strstr($tripName, '(', true) ? strstr($tripName, '(', true) : $tripName) ."',";
                    echo "start: '" . $trip->date . " " . $trip->departureTime ."',";
                    echo "url: '/TripDetails/" . str($trip->id) . "',";
                    if($trip->tripType == "Technical")
                        echo "className: 'bg-gradient-success text-white op=" . $trip->operatorId  . "f' },";
                    else
                        echo "className: 'bg-gradient-secondary text-white op=" . $trip->operatorId  . "f' },";
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

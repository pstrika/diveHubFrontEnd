<x-page-template bodyClass='g-sidenav-show  bg-gray-200'>
    <x-auth.navbars.sidebar activePage="Dashboard" activeItem="Dashboard" activeSubitem=""></x-auth.navbars.sidebar>
    
    
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <!-- Navbar -->
        <x-auth.navbars.navs.auth pageTitle="My Dashboard"></x-auth.navbars.navs.auth>
        <!-- End Navbar -->
        <div class="container-fluid py-0">

        



            <div class="page-header min-height-200 max-height-300 border-radius-xl mt-4 mx-n2" style="background-image: url('/assets/img/illustrations/dashboard.jpeg');">
                <span class="mask  bg-gradient-info  opacity-4"></span>
            </div>

            <div class="card p-0 position-relative mt-n5 mx-1 z-index-2 mb-4">
                
                    <div class="p-0 mt-0 mx-2 border-radius-lg py-3 pe-1">
                        <div style="float: left;">
                            <h2 class="card-title text-info mx-3 mt-0">My Dashboard</h2>
                        </div>

                    </div>
                </div>
            </div>

            <div class="row mx-1">
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
                                <p>You have no upcoming dives in you calendar</p>
                            @else
                                <div class="timeline timeline-one-side" data-timeline-axis-style="dotted">
                                    @foreach($trips as $trip)
                                        <div class="timeline-block mb-3">
                                            <a href="{{ route('TripDetails', ['tripId' => $trip->id]) }}">
                                                <span class="timeline-step bg-{{ $trip->booked ? "success" : "danger" }} p-3">
                                                    
                                                        @if(strstr($trip->tags, "SHA"))
                                                            <span class="d-flex align-items-center"><img style="height:20px;" src="{{ asset('assets') }}/img/icons/icons_shark_center.png"></span>
                                                        @elseif(strstr($trip->tags, "TEC"))
                                                            <span class="d-flex align-items-center"><img style="height:20px;" src="{{ asset('assets') }}/img/icons/icons_tec_center.png"></span>
                                                        @elseif(strstr($trip->tags, "LOB"))
                                                            <span class="d-flex align-items-center"><img style="height:20px;" src="{{ asset('assets') }}/img/icons/icons_lobster_center.png"></span>
                                                        @else
                                                            <span class="d-flex align-items-center"><img style="height:20px;" src="{{ asset('assets') }}/img/icons/icons_rec_center.png"></span>
                                                        @endif
                                                    
                                                </span>
                                            </a>
                                            <div class="timeline-content pt-1">
                                                <a href="{{ route('TripDetails', ['tripId' => $trip->id]) }}">
                                                    <h6 class="text-dark text-sm font-weight-bold mb-0">{{ $trip->tripName}}</h6>
                                                </a>
                                                <?php 
                                                    $dateTime = DateTime::createFromFormat('Y-m-d', $trip->date);
                                                    $formattedDate = $dateTime->format('l, M j, Y');
                                                ?>
                                                <p class="text-secondary text-xs mt-1 mb-0"> {{$formattedDate}} <b>({{ $trip->departureTime}})</b></p>
                                                <p class="text-sm text-bold text-info mt-1 mb-2">
                                                    {{ $trip->operatorName }}
                                                </p>
                                            </div>
                                        </div>
                                    @endforeach
                                    
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            

            
                <div class="col-md-8">             
                    <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h2 class="card-title text-white mx-4">Recommended for next weekend</h2>
                            </div>
                        </div>
                        <div class="card-body">
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
                                                
                                            @endforeach          
                                        </tbody>
                                    </table>
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
    
    

    @endpush
</x-page-template>

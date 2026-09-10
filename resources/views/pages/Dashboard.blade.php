<x-page-template bodyClass='dh-shell bg-gray-200'>
    <x-shell.nav active="me" />
    
    
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <!-- Navbar -->
        <x-shell.header title="My Dashboard" />
        <!-- End Navbar -->
        <div class="container-fluid py-0">

        
        {{--
            Options for one saved dive. Opened from the status pill in the list and
            from a dive on the month grid (clickOnMyTrips). The ids are what that
            script fills in, so they stay; the icon-only buttons with tooltips did
            not, because on a phone there is no hover and "Options" has to read as
            options (Zach, 2026-09-10).
        --}}
        <div class="modal fade" id="modal-calendar" tabindex="-1" role="dialog" aria-labelledby="modal-title-notification-calendar" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content dh-sheet">
                    <div class="dh-sheet-head">
                        <span id="span-booked" class="dh-dive-status is-booked"><span class="material-icons-round" aria-hidden="true">check_circle</span>Booked</span>
                        <span id="span-not-booked" class="dh-dive-status is-open"><span class="material-icons-round" aria-hidden="true">radio_button_unchecked</span>Not booked yet</span>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <h6 class="dh-sheet-title" id="modal-title-notification-calendar">Edit calendar</h6>
                    <div class="dh-sheet-actions">
                        <a id="button-go" class="dh-sheet-action" href="">
                            <span class="material-icons-round" aria-hidden="true">sailing</span>
                            <span><strong>Go to the dive</strong><small>Sites, seats, price and the boat</small></span>
                        </a>
                        <div id="div-button-link">
                            <a id="button-link" class="dh-sheet-action" href="" target="_blank" rel="noopener">
                                <span class="material-icons-round" aria-hidden="true">open_in_new</span>
                                <span><strong>Book with the operator</strong><small>Opens the shop's booking page</small></span>
                            </a>
                        </div>
                        <div id="div-button-book">
                            <a id="button-book" class="dh-sheet-action" href="">
                                <span class="material-icons-round" aria-hidden="true">check_circle</span>
                                <span><strong>I am booked</strong><small>Marks this dive booked</small></span>
                            </a>
                        </div>
                        <div id="div-button-waiver">
                            <a id="button-waiver" class="dh-sheet-action" href="" target="_blank" rel="noopener">
                                <span class="material-icons-round" aria-hidden="true">description</span>
                                <span><strong>Sign the waiver</strong><small>The operator's online waiver</small></span>
                            </a>
                        </div>
                        <a id="button-remove" class="dh-sheet-action is-danger" href="">
                            <span class="material-icons-round" aria-hidden="true">event_busy</span>
                            <span><strong>Remove from my calendar</strong><small>You can add it again from the dive page</small></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>


            {{--
                Dashboard order, 2026-09-10 (Pablo's priorities, UX first).

                The banner photo and the "My Dashboard" title card that used to be
                here are gone: on a phone they filled the entire first screen and
                said nothing the header does not already say.

                Order is what a diver needs first: the dives they are on, then what
                to book next, then their wishlist, and the month grid of their
                favourite operators last and collapsed. That calendar stays,
                because some divers use nothing else, but it is a poor first
                impression and a bad use of the top of the page.
            --}}
<div class="row mx-1 dh-dash-cards">
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
                                <p class="text-sm mb-2">Nothing saved yet. Find a dive and add it to your calendar; it shows up here and in the calendar below.</p>
                                <a class="dh-btn dh-btn-primary" href="{{ route('Trips') }}"><span class="material-icons-round">sailing</span>Find a dive</a>
                            @else
                                {{--
                                    One row per saved dive (Zach, 2026-09-10). The row itself is the
                                    dive: title, date and operator all go to the trip page, and the
                                    operator name is plain text because "I wanted to go to the dive".
                                    Booked or not is said in words on a pill, not by the colour of an
                                    icon, and that pill is the visible control that opens the options
                                    (mark booked, book with the shop, waiver, remove). The tank icon is
                                    trip type only.
                                --}}
                                <ul class="dh-dive-list">
                                    @foreach($trips as $trip)
                                        @php
                                            $tags = (string) $trip->tags;
                                            $typeIcon = str_contains($tags, 'SHA') ? 'icons_shark_center.png' : (str_contains($tags, 'TEC') ? 'icons_tec_center.png' : (str_contains($tags, 'LOB') ? 'icons_lobster_center.png' : 'icons_rec_center.png'));
                                            $typeName = str_contains($tags, 'SHA') ? 'Shark' : (str_contains($tags, 'TEC') ? 'Technical' : (str_contains($tags, 'LOB') ? 'Lobster' : 'Recreational'));
                                            $when = DateTime::createFromFormat('Y-m-d', $trip->date);
                                        @endphp
                                        <li class="dh-dive-row">
                                            {{-- Hidden fields read by clickOnMyTrips() for the options sheet. --}}
                                            <label id="upComingTripsDate-{{ $trip->eventId }}" hidden>{{ $trip->date }}</label>
                                            <label id="upComingTripsTime-{{ $trip->eventId }}" hidden>{{ $trip->departureTime }}</label>
                                            <label id="upComingTripsEventId-{{ $trip->eventId }}" hidden>{{ $trip->eventId }}</label>
                                            <label id="upComingTripsTripId-{{ $trip->eventId }}" hidden>{{ $trip->id }}</label>
                                            <label id="upComingTripsLinkToBook-{{ $trip->eventId }}" hidden>{{ $trip->linkToBook }}</label>
                                            <label id="upComingTripsWaiver-{{ $trip->eventId }}" hidden>{{ $trip->waiver }}</label>
                                            <label id="upComingTripsBooked-{{ $trip->eventId }}" hidden>{{ $trip->booked}}</label>
                                            <label id="upComingTripsOperator-{{ $trip->eventId }}" hidden>{{ $trip->operatorName}}</label>
                                            <label id="upComingTripsTitle-{{ $trip->eventId }}" hidden>{{ $trip->tripName}}</label>

                                            <a class="dh-dive-main" href="{{ route('TripDetails', ['tripId' => $trip->id]) }}">
                                                <span class="dh-dive-type" title="{{ $typeName }} dive"><img src="{{ asset('assets') }}/img/icons/{{ $typeIcon }}" alt="{{ $typeName }}"></span>
                                                <span class="dh-dive-text">
                                                    <span class="dh-dive-title do-not-translate">{{ $trip->tripName }}</span>
                                                    <span class="dh-dive-when">{{ $when ? $when->format('D, M j') : $trip->date }} <b>{{ $trip->departureTime }}</b></span>
                                                    <span class="dh-dive-op do-not-translate">{{ $trip->operatorName }}</span>
                                                </span>
                                                <span class="material-icons-round dh-dive-chevron" aria-hidden="true">chevron_right</span>
                                            </a>
                                            <button type="button" class="dh-dive-status {{ $trip->booked ? 'is-booked' : 'is-open' }}" onclick="clickOnMyTrips({{ $trip->eventId }})" aria-haspopup="dialog">
                                                <span class="material-icons-round" aria-hidden="true">{{ $trip->booked ? 'check_circle' : 'radio_button_unchecked' }}</span>
                                                <span>{{ $trip->booked ? 'Booked' : 'Not booked yet' }}</span>
                                                <span class="dh-dive-status-more">Options</span>
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>

                {{---Card recommended for this weekend --}}
                <div class="col-md-8">             
                    <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                @php $ws = \Carbon\Carbon::parse($weekendStart ?? now()); $weekendLabel = $ws->isCurrentWeek() ? 'this weekend' : 'the weekend of ' . $ws->format('M j'); @endphp
                                <h2 class="card-title text-white mx-4">Recommended for {{ $weekendLabel }}</h2>
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
                                <p>There is no recommendation for {{ $weekendLabel }} at the moment. Go to <a href=" {{ route('overview') }}"><b class="text-info">"My Profile"</b></a> on the menu to set your favorite preferences.</p>
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
                                <h2 class="card-title text-white mx-4"><i class="material-icons justify-content-middle align-middle" style="font-size: 40px;">favorite</i>My wishlist
                                    {{-- A way in from the card itself, not only from the empty state. --}}
                                    <a class="dh-dash-headlink" href="{{ route('DiveSites') }}">Add sites</a></h2>
                            </div>
                        </div>

                        <div class="card-body">
                            
                            @if(!count($wished))
                                {{-- Pablo, 2026-09-10: the wishlist only earns its place if divers fill
                                     it, so the empty state sends them somewhere to do that. Saving a site
                                     means we tell them when a boat is scheduled to go there. --}}
                                <p class="text-sm mb-3">Save the sites you want to dive and we will tell you when a boat is going there.</p>
                                <a class="dh-btn dh-btn-primary" href="{{ route('DiveSites') }}">
                                    <span class="material-icons-round">travel_explore</span>Find sites to add
                                </a>
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

                {{--
                    My groups. Small on purpose: this is the slot the group feed will
                    grow into (what people posted, who is going, unread), once group
                    chat has read tracking. Today it answers "what is my group doing
                    next" and surfaces invites, which is the one group action a diver
                    should never miss.
                --}}
                <div class="col-md-12">
                    <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h2 class="card-title text-white mx-4"><i class="material-icons justify-content-middle align-middle" style="font-size: 40px;">groups</i>My groups
                                    <a class="dh-dash-headlink" href="{{ route('MyGroups') }}">All groups</a></h2>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            @if(($groupInvites ?? 0) > 0)
                                <a class="dh-group-invite" href="{{ route('MyGroups') }}">
                                    <span class="material-icons-round" aria-hidden="true">mail</span>
                                    <span>You have {{ $groupInvites }} group {{ $groupInvites === 1 ? 'invite' : 'invites' }} waiting</span>
                                    <span class="material-icons-round" aria-hidden="true">chevron_right</span>
                                </a>
                            @endif
                            @if(($myGroups ?? collect())->isEmpty())
                                <p class="text-sm mb-3">Dive with your people. A group has its own calendar, chat and dive reminders, and you can invite by email, WhatsApp or a Facebook page.</p>
                                <a class="dh-btn dh-btn-primary" href="{{ route('MyGroups') }}"><span class="material-icons-round">group_add</span>Create or join a group</a>
                            @else
                                <ul class="dh-group-list">
                                    @foreach($myGroups as $group)
                                        @php $next = $group->dives->first(); @endphp
                                        <li>
                                            <a class="dh-group-row" href="{{ route('Groups.show', $group->slug) }}">
                                                <span class="dh-group-avatar">
                                                    @if($group->avatar)
                                                        <img src="{{ str_starts_with($group->avatar, 'http') ? $group->avatar : asset('assets/' . ltrim($group->avatar, '/')) }}" alt="" onerror="this.remove()">
                                                    @else
                                                        <span class="material-icons-round" aria-hidden="true">groups</span>
                                                    @endif
                                                </span>
                                                <span class="dh-group-text">
                                                    <span class="dh-group-name do-not-translate">{{ $group->name }}</span>
                                                    <span class="dh-group-meta">
                                                        {{ $group->active_members_count }} {{ $group->active_members_count === 1 ? 'member' : 'members' }}
                                                        @if($next)
                                                            &middot; next dive {{ \Carbon\Carbon::parse($next->date)->format('D, M j') }}@if($next->tripName), {{ $next->tripName }}@endif
                                                        @else
                                                            &middot; no dive planned yet
                                                        @endif
                                                    </span>
                                                </span>
                                                <span class="material-icons-round dh-dive-chevron" aria-hidden="true">chevron_right</span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{--
                My calendar (Zach, 2026-09-10). The month grid at the foot of the page
                used to be the favourite operators' calendar, which is not what a
                diver expects under "my dashboard". Now it is their own calendar first,
                with the subscribe link that keeps their phone's calendar in sync, and
                the operators' calendar one switch away for the divers who plan from
                it. Both grids read the same data the page already had.
            --}}
            <div class="row mx-1 dh-cal-row">
                <div class="col-md-12">
                    <div class="card p-0 position-relative mt-5 mx-0 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h2 class="card-title text-white mx-4"><i class="material-icons justify-content-middle align-middle" style="font-size: 40px;">calendar_month</i>My calendar
                                    <a class="dh-dash-headlink" href="{{ route('MyCalendar') }}">Open full calendar</a></h2>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <div class="dh-seg" role="tablist" aria-label="Which calendar">
                                <button type="button" class="dh-seg-btn is-active" role="tab" aria-selected="true" data-dh-cal="mine">My dives</button>
                                @if( !empty($favOperators) )
                                <button type="button" class="dh-seg-btn" role="tab" aria-selected="false" data-dh-cal="ops">Favorite operators</button>
                                @endif
                            </div>

                            <div id="dh-cal-mine" data-dh-cal-panel="mine">
                                <p class="text-xs text-secondary mb-2"><span class="dh-cal-key is-booked"></span> booked &nbsp; <span class="dh-cal-key is-open"></span> not booked yet &nbsp;&middot;&nbsp; tap a dive for options, tap a day to find a dive</p>
                                <div class="calendar" id="my-calendar"></div>

                                @if($calendarFeedUrl ?? null)
                                <div class="dh-subscribe">
                                    <div class="dh-subscribe-text">
                                        <strong><span class="material-icons-round" aria-hidden="true">event_available</span> Subscribe to my dive calendar</strong>
                                        <span>Paste this link into Google Calendar, Apple Calendar or Outlook as a calendar from URL and your dives stay in sync. Anyone with the link can see your upcoming dives.</span>
                                    </div>
                                    <div class="dh-subscribe-row">
                                        <input type="text" id="calendarFeedUrl" class="form-control" value="{{ $calendarFeedUrl }}" readonly onclick="this.select();" aria-label="Calendar subscription link">
                                        <button type="button" class="dh-btn dh-btn-primary" onclick="copyCalendarFeedUrl(this)"><span class="material-icons-round">content_copy</span>Copy link</button>
                                    </div>
                                </div>
                                @endif
                            </div>

                            @if( !empty($favOperators) )
                            <div id="dh-cal-ops" data-dh-cal-panel="ops" hidden>
                                <div class="dh-fav-cal">
                            <table>
                                <tr></td>
                                    {{-- flex-wrap and min-width so a long operator name plus the logo cannot push past the card on phones --}}
                                    <div class="d-flex flex-wrap align-items-center gap-3 dh-fav-operator">
                                        <!-- Dropdown -->
                                        <div class="dropdown flex-grow-1" style="min-width: 0;">
                                            <select class="btn bg-info dropdown-toggle text-white" id="filterOperators" data-bs-toggle="dropdown" aria-expanded="false">
                                                @foreach($favOperators as $favOperator)
                                                    <option value="{{ $favOperator->id }}">{{ $favOperator->operatorName }}</option>
                                                @endforeach
                                            </select>
                                            <p class="text-xs font-weight-bold mb-2 mt-n3">dive operator</p>
                                        </div>
                                        

                                        <!-- Logo -->
                                        <div class="text-left mx-n2 mt-n3">
                                            <img id="operatorLogo" src="{{ asset('assets') }}/img/logos/logo_circle.png" height="45" alt="Operator Logo">
                                            
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
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- The Me tab lands here instead of opening a pop out, so the menu rows live on the page. --}}
            <x-shell.me-rows />

            
            
                


                
            
            
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
            operatorLogo.src = '{{ asset('assets') }}/' + selectedOperator.logoUrl || '{{ asset('assets') }}/img/logos/logo_circle.png';
        } else {
            operatorLogo.src = '{{ asset('assets') }}/img/logos/logo_circle.png';
        }

        // Filter calendar events. Each event element carries data-op (set in
        // eventDidMount below) so this is an exact match on the operator id, not a
        // substring search through the class list.
        const rows = document.querySelectorAll('#calendar .fc-event[data-op]');
        rows.forEach(function(row) {
            row.style.display = (row.dataset.op === String(selectedId)) ? '' : 'none';
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
        const todayDate = new Date().toISOString().split('T')[0];
    </script>
    @if( !empty($favOperators) )
    <script>
        function getResponsiveView() {
            const width = window.innerWidth;
            if (width >= 1200) return 'dayGridMonth';     // Large screens
            if (width >= 768) return 'dayGridWeek';       // Medium screens
            return 'dayGridThreeDay';                    // Small screens
        }

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
        eventDidMount: function(info) {
            info.el.dataset.op = info.event.extendedProps.op; // read by applyFilter()
        },
        datesSet: function() {
            setTimeout(() => {
                applyFilter();
            }, 50); // Slight delay to wait for DOM stabilization
        },
        firstDay: {{ (int) (auth()->user()->firstDayOfWeek ?? 0) }}, // null preference broke the whole script (stray comma); 0 = Sunday
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
                    echo "extendedProps: { op: '" . (int) $trip->operatorId . "' },";
                    if($trip->tripType == "Technical")
                        echo "className: 'bg-gradient-success text-white' },";
                    else
                        echo "className: 'bg-gradient-secondary text-white' },";
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

    </script>
    @else
    <script>
        var calendar = null, applyFilter = function () {};
    </script>
    @endif
    <script>
        // The favourite operators grid lives in a hidden panel; FullCalendar lays out
        // at zero width when hidden, so it renders on first switch, not on load.
        var opsRendered = false;

        // ------------------------------------------------------------------
        // My dives: the diver's own saved trips, the same list as the card above.
        // Booked and not booked are colours here and words in the list.
        // ------------------------------------------------------------------
        var myCalendar = new FullCalendar.Calendar(document.getElementById("my-calendar"), {
            dateClick: function(info) { window.location.href = '/Trips/' + info.dateStr; },
            eventClick: function(info) {
                info.jsEvent.preventDefault();
                clickOnMyTrips(info.event.extendedProps.eventId);
            },
            initialView: 'dayGridMonth',
            firstDay: {{ (int) (auth()->user()->firstDayOfWeek ?? 0) }},
            contentHeight: 'auto',
            headerToolbar: { start: '', center: 'title', end: 'prev,next today' },
            selectable: true,
            editable: false,
            initialDate: todayDate,
            events: [
                @php
                    foreach($trips as $trip) {
                        $tripName = str_replace("'", "\\'", $trip->tripName);
                        echo "{";
                        echo "title: '" . (strstr($tripName, '(', true) ? strstr($tripName, '(', true) : $tripName) . "',";
                        echo "start: '" . $trip->date . " " . $trip->departureTime . "',";
                        echo "extendedProps: { eventId: " . (int) $trip->eventId . " },";
                        echo "className: '" . ($trip->booked ? 'dh-fc-booked' : 'dh-fc-open') . "' },";
                    }
                @endphp
            ]
        });
        myCalendar.render();

        document.querySelectorAll('[data-dh-cal]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var which = btn.getAttribute('data-dh-cal');
                document.querySelectorAll('[data-dh-cal]').forEach(function (b) {
                    var on = b === btn;
                    b.classList.toggle('is-active', on);
                    b.setAttribute('aria-selected', on ? 'true' : 'false');
                });
                document.querySelectorAll('[data-dh-cal-panel]').forEach(function (panel) {
                    panel.hidden = panel.getAttribute('data-dh-cal-panel') !== which;
                });
                if (which === 'ops') {
                    if (!opsRendered) { calendar.render(); opsRendered = true; applyFilter(); }
                    else { calendar.updateSize(); }
                } else {
                    myCalendar.updateSize();
                }
            });
        });


    </script>



   




    @endpush
</x-page-template>

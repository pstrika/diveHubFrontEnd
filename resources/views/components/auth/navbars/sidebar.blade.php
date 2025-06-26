@props(['activePage', 'activeItem', 'activeSubitem'])

<aside

    
    class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3   bg-gradient-dark"
    id="sidenav-main">

    <style>
        .modal {
            z-index: 10050; /* Adjust this value to be higher than the sidebar's z-index THIS IS TO SHOW MODAL ON TOP OF SIDEBAR*/
        }
    </style>

    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-white opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
            aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand m-0 d-flex align-items-center text-wrap" href="{{ route('overview') }}">
            <img src="{{ asset('assets') }}/img/logos/logo_divershub_white.png" class="navbar-brand-img h-100" alt="main_logo">
            <span class="ms-2 font-weight-bold text-white">DiversHub ver 6.0.0 (06/25/25)</span>
        </a>
    </div>
    <hr class="horizontal light mt-0 mb-2">
    <div class="collapse navbar-collapse  w-auto h-auto" id="sidenav-collapse-main">
        <ul class="navbar-nav">
            {{-- User --}}
            <li class="nav-item mb-2 mt-0">
                <a data-bs-toggle="collapse" href="#ProfileNav" class="nav-link text-white" aria-controls="ProfileNav"
                    role="button" aria-expanded="false">
                    @if (auth()->user()->picture)
                    <img src="{{ asset('assets') }}/img/users/{{(auth()->user()->picture)}}" alt="avatar" class="avatar">
                    @else
                    <img src="{{ asset('assets') }}/img/default-avatar.png" alt="avatar" class="avatar">
                    @endif
                    <span class="nav-link-text ms-2 ps-1">{{ auth()->user()->name }}
                    </span>
                    
                </a>
                <div class="collapse" id="ProfileNav" style="">
                    <ul class="nav ">
                        @if(auth()->user()->isNotGuest())
                        <li class="nav-item" style="padding-left: 1rem;">
                            <a class="nav-link text-white" href="{{ route('overview') }}">
                                <i class="material-icons-round opacity-10">person</i>
                                <span class="sidenav-normal  ms-3  ps-1"> My Profile </span>
                            </a>
                        </li>
                        <li class="nav-item" style="padding-left: 1rem;">
                            <a class="nav-link text-white" href="{{ route('MyVisitedSites') }}">
                                <i class="material-icons-round opacity-10">where_to_vote</i>
                                <span class="sidenav-normal  ms-3  ps-1"> My Visited Sites</span>
                            </a>
                        </li>
                        
                        @endif
                        {{--<li class="nav-item">
                            <a class="nav-link text-white " href="{{ route('settings') }}">
                                <i class="material-icons-round opacity-10">settings</i>
                                <span class="sidenav-normal  ms-3  ps-1"> Settings </span>
                            </a>
                        </li>--}}
                        <form method="POST" action="{{ route('logout') }}" class="d-none" id="logout-form">
                            @csrf
                        </form>
                        
                        <li class="nav-item" style="padding-left: 1rem;">
                            <a class="nav-link text-white " href="{{ route('logout') }} "
                                onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                                @if(auth()->user()->isNotGuest())
                                    <i class="material-icons-round opacity-10">logout</i>
                                    <span class="sidenav-normal  ms-3  ps-1"> Logout </span>
                                @else
                                    <i class="material-icons-round opacity-10">person_add_alt</i>
                                    <span class="sidenav-normal  ms-3  ps-1"> Create account </span>
                                @endif
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <hr class="horizontal light mt-0">

            {{-- Dashboard --}}
            @if(auth()->user()->isNotGuest())
            <li class="nav-item {{ $activePage == 'Dashboard' ? ' active ' : '' }}">
                <a class="nav-link text-white {{ $activeItem == 'Dashboard' ? ' active' : '' }}  "
                    href="{{ route('MyDashboard') }}">
                    <i class="material-icons-round opacity-10">dashboard</i>
                    <span class="nav-link-text ms-2 ps-1">My Dashboard</span>
                </a>
            </li>
            @else
            <li class="nav-item {{ $activePage == 'Dashboard' ? ' active ' : '' }}">
                <a class="nav-link text-white {{ $activeItem == 'Dashboard' ? ' active' : '' }}  "
                    href="#" onclick="showModalGuest();">
                    <i class="material-icons-round opacity-10 text-primary">lock</i>
                    <span class="nav-link-text ms-2 ps-1 text-primary">My Dashboard</span>
                </a>
            </li>
            @endif

            {{-- Trips today --}}
            <li class="nav-item {{ $activePage == 'trips' ? ' active ' : '' }}">
                <a class="nav-link text-white {{ $activeItem == 'trips' ? ' active' : '' }}  "
                    href="{{ route('Trips') }}">
                    <i class="material-icons-round opacity-10">calendar_today</i>
                    <span class="nav-link-text ms-2 ps-1">Upcoming Trips</span>
                </a>
            </li>
            
            {{-- Weather --}}
            @if(auth()->user()->isNotGuest())
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#weather"
                    class="nav-link text-white {{ $activePage == 'Weather' ? ' active ' : '' }} "
                    aria-controls="dashboardsExamples" role="button" aria-expanded="false">
                    <i class="material-icons-round opacity-10">cloud</i>
                    <span class="nav-link-text ms-2 ps-1">Weather</span>
                </a>
                <div class="collapse {{ $activePage == 'Weather' ? ' show ' : '' }}  " id="weather">
                    <ul class="nav ">
                        
                        <li class="nav-item {{ $activeItem == 'WeatherSFL' ? ' active ' : '' }}  " style="padding-left: 1rem;">
                            <a class="nav-link text-white {{ $activeItem == 'WeatherSFL' ? ' active' : '' }}  "
                                href="{{ route('Weather') }}">
                                <span><img style="height:15px;" src="{{ asset('assets') }}/img/icons/flags/US.png"></span>
                                <span class="sidenav-normal  ms-2  ps-1"> South FL </span>
                            </a>
                        </li>

                        <li class="nav-item {{ $activeItem == 'WeatherAR' ? ' active ' : '' }}  " style="padding-left: 1rem;">
                            <a class="nav-link text-white {{ $activeItem == 'WeatherAR' ? ' active' : '' }}  "
                                href="{{ route('WeatherAR') }}">
                                <span><img style="height:15px;" src="{{ asset('assets') }}/img/icons/flags/AR.png"></span>
                                <span class="sidenav-normal  ms-2  ps-1"> Argentina </span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            @else
            <li class="nav-item">
                <a class="nav-link text-white {{ $activeItem == 'weather' ? ' active' : '' }}  "
                href="#" onclick="showModalGuest();">
                    <i class="material-icons-round opacity-10 text-primary">lock</i>
                    <span class="nav-link-text ms-2 ps-1 text-primary">Weather</span>
                </a>
            </li>
            @endif

            

            @if(auth()->user()->isNotGuest())
            {{-- Calendars --}}
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#calendars"
                    class="nav-link text-white {{ $activePage == 'Calendars' ? ' active ' : '' }} "
                    aria-controls="dashboardsExamples" role="button" aria-expanded="false">
                    <i class="material-icons-round opacity-10">calendar_month</i>
                    <span class="nav-link-text ms-2 ps-1">Calendars</span>
                </a>
                <div class="collapse {{ $activePage == 'Calendars' ? ' show ' : '' }}  " id="calendars">
                    <ul class="nav ">
                        {{--<li class="nav-item {{ $activeItem == 'CalendarRec' ? ' active ' : '' }}  ">
                            <a class="nav-link text-white {{ $activeItem == 'CalendarRec' ? ' active' : '' }}  "
                                href="{{ route('CalendarT') }}/rec">
                                <span><img style="height:25px;" src="{{ asset('assets') }}/img/icons/icons_rec.png"></span>
                                <span class="sidenav-normal  ms-2  ps-1"> Recreational </span>
                            </a>
                        </li>--}}
                        <li class="nav-item {{ $activeItem == 'MyCalendar' ? ' active ' : '' }}  " style="padding-left: 1rem;">
                            <a class="nav-link text-white {{ $activeItem == 'MyCalendar' ? ' active' : '' }}  "
                                href="{{ route('MyCalendar') }}">
                                <i class="material-icons-round opacity-10">perm_contact_calendar</i>
                                <span class="sidenav-normal  ms-2  ps-1"> My Calendar </span>
                            </a>
                        </li>

                        <li class="nav-item {{ $activeItem == 'CalendarTec' ? ' active ' : '' }}  " style="padding-left: 1rem;">
                            <a class="nav-link text-white {{ $activeItem == 'CalendarTec' ? ' active' : '' }}  "
                                href="{{ route('CalendarT') }}/tec">
                                <span><img style="height:25px;" src="{{ asset('assets') }}/img/icons/icons_tec.png"></span>
                                <span class="sidenav-normal  ms-2  ps-1"> Technical </span>
                            </a>
                        </li>

                        <li class="nav-item {{ $activeItem == 'wreckDiving' ? ' active ' : '' }}  " style="padding-left: 1rem;">
                            <a class="nav-link text-white {{ $activeItem == 'wreckDiving' ? ' active' : '' }}  "
                                href="{{ route('CalendarWreck') }}">
                                <span><img style="height:25px;" src="{{ asset('assets') }}/img/icons/wreck_icon_white.png"></span>
                                
                                <span class="sidenav-normal  ms-2  ps-1">Wreck Diving</span>
                            </a>
                        </li>

                        <li class="nav-item {{ $activeItem == 'sharkDiving' ? ' active ' : '' }}  " style="padding-left: 1rem;">
                            <a class="nav-link text-white {{ $activeItem == 'sharkDiving' ? ' active' : '' }}  "
                                href="{{ route('CalendarShark') }}">
                                <span><img style="height:25px;" src="{{ asset('assets') }}/img/icons/icons_shark.png"></span>
                                
                                <span class="sidenav-normal  ms-2  ps-1">Shark Diving</span>
                            </a>
                        </li>

                        <li class="nav-item {{ $activeItem == 'lobsterDiving' ? ' active ' : '' }}  " style="padding-left: 1rem;">
                            <a class="nav-link text-white {{ $activeItem == 'lobsterDiving' ? ' active' : '' }}  "
                                href="{{ route('CalendarLobster') }}">
                                <span><img style="height:25px;" src="{{ asset('assets') }}/img/icons/icons_lobster.png"></span>
                                
                                <span class="sidenav-normal  ms-2  ps-1">Lobster Diving</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            @else
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#calendars"
                    class="nav-link text-white {{ $activePage == 'Calendars' ? ' active ' : '' }} "
                    aria-controls="dashboardsExamples" role="button" aria-expanded="false">
                    <i class="material-icons-round opacity-10 text-primary">lock</i>
                    <span class="nav-link-text ms-2 ps-1 text-primary">Calendars</span>
                </a>
                <div class="collapse {{ $activePage == 'Calendars' ? ' show ' : '' }}  " id="calendars">
                    <ul class="nav ">
                        {{--<li class="nav-item {{ $activeItem == 'CalendarRec' ? ' active ' : '' }}  ">
                            <a class="nav-link text-white {{ $activeItem == 'CalendarRec' ? ' active' : '' }}  "
                                href="{{ route('CalendarT') }}/rec">
                                <span><img style="height:25px;" src="{{ asset('assets') }}/img/icons/icons_rec.png"></span>
                                <span class="sidenav-normal  ms-2  ps-1"> Recreational </span>
                            </a>
                        </li>--}}
                        <li class="nav-item {{ $activeItem == 'MyCalendar' ? ' active ' : '' }}  " style="padding-left: 1rem;">
                            <a class="nav-link text-white {{ $activeItem == 'MyCalendar' ? ' active' : '' }}  "
                                href="#" onclick="showModalGuest();">
                                <i class="material-icons-round opacity-10 text-primary">lock</i>
                                <span class="sidenav-normal  ms-2  ps-1 text-primary"> My Calendar </span>
                            </a>
                        </li>

                        <li class="nav-item {{ $activeItem == 'CalendarTec' ? ' active ' : '' }}  " style="padding-left: 1rem;">
                            <a class="nav-link text-white {{ $activeItem == 'CalendarTec' ? ' active' : '' }}  "
                                href="#" onclick="showModalGuest();">
                                <i class="material-icons-round opacity-10 text-primary">lock</i>
                                <span class="sidenav-normal  ms-2  ps-1 text-primary"> Technical </span>
                            </a>
                        </li>

                        <li class="nav-item {{ $activeItem == 'CalendarWreck' ? ' active ' : '' }}  " style="padding-left: 1rem;">
                            <a class="nav-link text-white {{ $activeItem == 'CalendarWreck' ? ' active' : '' }}  "
                                href="#" onclick="showModalGuest();">
                                <i class="material-icons-round opacity-10 text-primary">lock</i>
                                <span class="sidenav-normal  ms-2  ps-1 text-primary"> Wreck </span>
                            </a>
                        </li>

                        <li class="nav-item {{ $activeItem == 'sharkDiving' ? ' active ' : '' }}  " style="padding-left: 1rem;">
                            <a class="nav-link text-white {{ $activeItem == 'sharkDiving' ? ' active' : '' }}  "
                                href="#" onclick="showModalGuest();">
                                <i class="material-icons-round opacity-10 text-primary">lock</i>
                                
                                <span class="sidenav-normal  ms-2  ps-1 text-primary">Shark Diving</span>
                            </a>
                        </li>

                        <li class="nav-item {{ $activeItem == 'lobsterDiving' ? ' active ' : '' }}  " style="padding-left: 1rem;">
                            <a class="nav-link text-white {{ $activeItem == 'lobsterDiving' ? ' active' : '' }}  "
                                href="#" onclick="showModalGuest();">
                                <i class="material-icons-round opacity-10 text-primary">lock</i>
                                
                                <span class="sidenav-normal  ms-2  ps-1 text-primary">Lobster Diving</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            @endif

            {{-- Beach diving --}}
            <li class="nav-item">
                <a class="nav-link text-white {{ $activeItem == 'beachDiving' ? ' active' : '' }}  "
                    href="{{ route('BeachDiving') }}">
                    <i class="material-icons-round opacity-10">beach_access</i>
                    <span class="nav-link-text ms-2 ps-1">Beach Diving</span>
                </a>
            </li>

            {{-- Operators --}}
            <li class="nav-item">
                <a class="nav-link text-white {{ $activeItem == 'diveOperators' ? ' active' : '' }}  "
                    href="{{ route('Operators') }}">
                    <i class="material-icons-round opacity-10">directions_boat</i>
                    <span class="nav-link-text ms-2 ps-1">Dive Operators</span>
                </a>
            </li>

            {{-- Dive Sites --}}
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#sites"
                    class="nav-link text-white {{ $activePage == 'DiveSites' ? ' active ' : '' }} "
                    aria-controls="dashboardsExamples" role="button" aria-expanded="false">
                    <i class="material-icons-round opacity-10">location_on</i>
                    <span class="nav-link-text ms-2 ps-1">Dive Sites</span>
                </a>
                <div class="collapse {{ $activePage == 'DiveSites' ? ' show ' : '' }}  " id="sites">
                    <ul class="nav ">
                    <li class="nav-item {{ $activeItem == 'DiveSitesTopRated' ? ' active ' : '' }}  " style="padding-left: 1rem;">
                            <a class="nav-link text-white {{ $activeItem == 'DiveSitesTopRated' ? ' active' : '' }}  "
                                href="{{ route('DiveSites') }}">
                                <i class="material-icons-round opacity-10">star</i>
                                <span class="sidenav-normal  ms-2  ps-1"> Top Rated Sites</span>
                            </a>
                        </li>

                        <li class="nav-item {{ $activeItem == 'DiveSitesMap' ? ' active ' : '' }}  " style="padding-left: 1rem;">
                            <a class="nav-link text-white {{ $activeItem == 'DiveSitesMap' ? ' active' : '' }}  "
                                href="{{ route('DiveSitesMap') }}">
                                <i class="material-icons-round opacity-10">map</i>
                                <span class="sidenav-normal  ms-2  ps-1"> Sites Map </span>
                            </a>
                        </li>
                        
                        <li class="nav-item {{ $activeItem == 'DiveSitesSearch' ? ' active ' : '' }}  " style="padding-left: 1rem;">
                            <a class="nav-link text-white {{ $activeItem == 'DiveSitesSearch' ? ' active' : '' }}  "
                                href="/DiveSitesSearch">
                                <i class="material-icons-round opacity-10">search</i>
                                <span class="sidenav-normal  ms-2  ps-1"> Search Sites...</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            {{-- Special Dives --}}
            {{--<li class="nav-item">
                <a data-bs-toggle="collapse" href="#special"
                    class="nav-link text-white {{ $activePage == 'Special' ? ' active ' : '' }} "
                    aria-controls="dashboardsExamples" role="button" aria-expanded="false">
                    <i class="material-icons-round opacity-10">stars</i>
                    <span class="nav-link-text ms-2 ps-1">Special dives</span>
                </a>
                <div class="collapse {{ $activePage == 'Special' ? ' show ' : '' }}  " id="special">
                    <ul class="nav ">
                        <li class="nav-item {{ $activeItem == 'beachDiving' ? ' active ' : '' }}  ">
                            <a class="nav-link text-white {{ $activeItem == 'beachDiving' ? ' active' : '' }}  "
                                href="{{ route('BeachDiving') }}">
                                <i class="material-icons-round opacity-10">beach_access</i>
                                <span class="sidenav-normal  ms-2  ps-1">Beach Diving</span>
                            </a>
                        </li>
                        
                    </ul>
                </div>
            </li>--}}

            {{-- Online waivers --}}
            <li class="nav-item">
                <a class="nav-link text-white {{ $activeItem == 'waivers' ? ' active' : '' }}  "
                    href="{{ route('Waivers') }}">
                    <i class="material-icons-round opacity-10">description</i>
                    <span class="nav-link-text ms-2 ps-1">Online waivers</span>
                </a>
            </li>

            {{-- Admin Tools --}}
            @if(auth()->user()->isNotGuest())
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#planningTools"
                    class="nav-link text-white {{ $activePage == 'planningTools' ? ' active ' : '' }} "
                    aria-controls="dashboardsExamples" role="button" aria-expanded="false">
                    <i class="material-icons-round opacity-10">construction</i>
                    <span class="nav-link-text ms-2 ps-1">Planning Tools</span>
                </a>
                <div class="collapse {{ $activePage == 'planningTools' ? ' show ' : '' }}  " id="planningTools">
                    <ul class="nav ">
                        
                        <li class="nav-item {{ $activeItem == 'decoPlanner' ? ' active ' : '' }}  " style="padding-left: 1rem;">
                            <a class="nav-link text-white {{ $activeItem == 'decoPlanner' ? ' active' : '' }}  "
                                href="{{ route('DecoPlanner') }}">
                                <i class="material-icons-round opacity-10">calculate</i>
                                <span class="nav-link-text ms-2 ps-1">Deco Planning</span>
                            </a>
                        </li>

                        

                    </ul>
                </div>
            </li>
            @else
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#planningTools"
                    class="nav-link text-white {{ $activePage == 'planningTools' ? ' active ' : '' }} "
                    aria-controls="dashboardsExamples" role="button" aria-expanded="false">
                    <i class="material-icons-round opacity-10 text-primary">lock</i>
                    <span class="nav-link-text ms-2 ps-1 text-primary">Planning Tools</span>
                </a>
                <div class="collapse {{ $activePage == 'planningTools' ? ' show ' : '' }}  " id="planningTools">
                    <ul class="nav ">
                        
                        <li class="nav-item {{ $activeItem == 'decoPlanner' ? ' active ' : '' }}  " style="padding-left: 1rem;">
                            <a class="nav-link text-white {{ $activeItem == 'decoPlanner' ? ' active' : '' }}  "
                            href="#" onclick="showModalGuest();">
                                <i class="material-icons-round opacity-10 text-primary">lock</i>
                                <span class="nav-link-text ms-2 ps-1 text-primary">Deco Planner</span>
                            </a>
                        </li>

                        

                    </ul>
                </div>
            </li>
            @endif

            {{-- Admin Tools --}}
            @can('manage-users', App\Models\User::class)
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#adminTools"
                    class="nav-link text-white {{ $activePage == 'AdminTools' ? ' active ' : '' }} "
                    aria-controls="dashboardsExamples" role="button" aria-expanded="false">
                    <i class="material-icons-round opacity-10">construction</i>
                    <span class="nav-link-text ms-2 ps-1">Admin Tools</span>
                </a>
                <div class="collapse {{ $activePage == 'AdminTools' ? ' show ' : '' }}  " id="adminTools">
                    <ul class="nav ">
                        
                        <li class="nav-item {{ $activeItem == 'platformHealth' ? ' active ' : '' }}  " style="padding-left: 1rem;">
                            <a class="nav-link text-white {{ $activeItem == 'platformHealth' ? ' active' : '' }}  "
                                href="{{ route('PlatformHealth') }}">
                                <i class="material-icons-round opacity-10">health_and_safety</i>
                                <span class="nav-link-text ms-2 ps-1">Platform Health</span>
                            </a>
                        </li>

                        <li class="nav-item {{ $activeItem == 'UserAdmin' ? ' active ' : '' }}  " style="padding-left: 1rem;">
                            <a class="nav-link text-white {{ $activeItem == 'UserAdmin' ? ' active' : '' }}  "
                                href="{{ route('users') }}">
                                <i class="material-icons-round opacity-10">people</i>
                                <span class="sidenav-normal  ms-2  ps-1"> User Management </span>
                            </a>
                        </li>

                    </ul>
                </div>
            </li>
            @endcan

            {{-- Dive sites Admins --}}
            @can('manage-items', App\Models\User::class)
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#siteAdmin"
                    class="nav-link text-white {{ $activePage == 'siteAdmin' ? ' active ' : '' }} "
                    aria-controls="dashboardsExamples" role="button" aria-expanded="false">
                    <i class="material-icons-round opacity-10">person_pin_circle</i>
                    <span class="nav-link-text ms-2 ps-1">Dive Sites Admin</span>
                </a>
                <div class="collapse {{ $activePage == 'siteAdmin' ? ' show ' : '' }}  " id="siteAdmin" >
                    <ul class="nav ">
                        <li class="nav-item {{ $activeItem == 'siteAdminAdd' ? ' active ' : '' }}  " style="padding-left: 1rem;">
                            <a class="nav-link text-white {{ $activeItem == 'siteAdminAdd' ? ' active' : '' }}  "
                                href="{{ route('new-site') }}">
                                <i class="material-icons-round opacity-10">add_location_alt</i>
                                <span class="sidenav-normal  ms-2  ps-1"> Add Site </span>
                            </a>
                        </li>
                        <li class="nav-item {{ $activeItem == 'siteAdminEdit' ? ' active ' : '' }}  " style="padding-left: 1rem;">
                            <a class="nav-link text-white {{ $activeItem == 'siteAdminEdit' ? ' active' : '' }}  "
                                href="{{ route('DiveSitesAdmin') }}">
                                <i class="material-icons-round opacity-10">edit_location_alt</i>
                                <span class="sidenav-normal  ms-2  ps-1"> Edit Site </span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            @endcan           

            

            {{--
            @can('manage-items', App\Models\User::class)
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#dashboardsExamples"
                    class="nav-link text-white {{ $activePage == 'dashboard' ? ' active ' : '' }} "
                    aria-controls="dashboardsExamples" role="button" aria-expanded="false">
                    <i class="material-icons-round opacity-10">dashboard</i>
                    <span class="nav-link-text ms-2 ps-1">Dashboards</span>
                </a>
                <div class="collapse {{ $activePage == 'dashboard' ? ' show ' : '' }}  " id="dashboardsExamples">
                    <ul class="nav ">
                        <li class="nav-item {{ $activeItem == 'analytics' ? ' active ' : '' }}  ">
                            <a class="nav-link text-white {{ $activeItem == 'analytics' ? ' active' : '' }}  "
                                href="{{ route('dashboard') }}">
                                <span class="sidenav-mini-icon"> A </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Analytics </span>
                            </a>
                        </li>
                        <li class="nav-item {{ $activeItem == 'discover' ? ' active ' : '' }} ">
                            <a class="nav-link text-white {{ $activeItem == 'discover' ? ' active ' : '' }} "
                                href="{{ route('discover') }}">
                                <span class="sidenav-mini-icon"> D </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Discover </span>
                            </a>
                        </li>
                        <li class="nav-item {{ $activeItem == 'sales' ? ' active ' : '' }} ">
                            <a class="nav-link text-white {{ $activeItem == 'sales' ? ' active ' : '' }} "
                                href="{{ route('sales') }}">
                                <span class="sidenav-mini-icon"> S </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Sales </span>
                            </a>
                        </li>
                        <li class="nav-item {{ $activeItem == 'automotive' ? ' active ' : '' }}  ">
                            <a class="nav-link text-white {{ $activeItem == 'automotive' ? ' active ' : '' }} "
                                href="{{ route('automotive') }}">
                                <span class="sidenav-mini-icon"> A </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Automotive </span>
                            </a>
                        </li>
                        <li class="nav-item {{ $activeItem == 'smart-home' ? ' active ' : '' }}  ">
                            <a class="nav-link text-white {{ $activeItem == 'smart-home' ? ' active ' : '' }} "
                                href="{{ route('smart-home') }}">
                                <span class="sidenav-mini-icon"> S </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Smart Home </span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            @endcan
            @can('manage-items', App\Models\User::class)
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#LaravelExamples"
                    class="nav-link text-white {{ $activePage == 'laravel-examples' ? ' active ' : '' }}  "
                    aria-controls="LaravelExamples" role="button" aria-expanded="false">
                    <i class="fab fa-laravel"></i>
                    <span class="nav-link-text ms-2 ps-1">Laravel Examples</span>
                </a>
                <div class="collapse {{ $activePage == 'laravel-examples' ? ' show ' : '' }} " id="LaravelExamples">
                    <ul class="nav ">
                        <li class="nav-item {{ $activeItem == 'user-profile' ? ' active ' : '' }}">
                            <a class="nav-link text-white {{ $activeItem == 'user-profile' ? ' active ' : '' }}  "
                                href="{{ route('user-profile') }}">
                                <span class="sidenav-mini-icon"> UP </span>
                                <span class="sidenav-normal  ms-2  ps-1"> User Profile <b class="caret"></b></span>
                            </a>
                        </li>
                        @can('manage-users', App\Models\User::class)
                        <li class="nav-item {{ $activeItem == 'user-management' ? ' active ' : '' }}">
                            <a class="nav-link text-white {{ $activeItem == 'user-management' ? ' active ' : '' }}  "
                                href="{{ route('users') }}">
                                <span class="sidenav-mini-icon"> UM </span>
                                <span class="sidenav-normal  ms-2  ps-1"> User Management <b class="caret"></b></span>
                            </a>
                        </li>
                        <li class="nav-item {{ $activeItem == 'role-management' ? ' active ' : '' }}">
                            <a class="nav-link text-white {{ $activeItem == 'role-management' ? ' active ' : '' }}  "
                                href="{{ route('roles') }}">
                                <span class="sidenav-mini-icon"> RM </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Role Management <b class="caret"></b></span>
                            </a>
                        </li>
                        @endcan
                        @can('manage-items', App\Models\User::class)
                        <li class="nav-item {{ $activeItem == 'category-management' ? ' active ' : '' }}">
                            <a class="nav-link text-white {{ $activeItem == 'category-management' ? ' active ' : '' }}  "
                                href="{{ route('category') }}">
                                <span class="sidenav-mini-icon"> CM </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Category Management <b
                                        class="caret"></b></span>
                            </a>
                        </li>
                        <li class="nav-item {{ $activeItem == 'tag-management' ? ' active ' : '' }}">
                            <a class="nav-link text-white {{ $activeItem == 'tag-management' ? ' active ' : '' }}  "
                                href="{{ route('tag') }}">
                                <span class="sidenav-mini-icon"> TM </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Tag Management <b class="caret"></b></span>
                            </a>
                        </li>
                        @endcan
                        @can('manage-items', App\Models\User::class)
                        <li class="nav-item {{ $activeItem == 'item-management' ? ' active ' : '' }}">
                            <a class="nav-link text-white {{ $activeItem == 'item-management' ? ' active ' : '' }}  "
                                href="{{ route('items') }}">
                                <span class="sidenav-mini-icon"> IM </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Item Management <b class="caret"></b></span>
                            </a>
                        </li>
                        @else
                        <li class="nav-item {{ $activeItem == 'item-management' ? ' active ' : '' }}">
                            <a class="nav-link text-white {{ $activeItem == 'item-management' ? ' active ' : '' }}  "
                                href="{{ route('items') }}">
                                <span class="sidenav-mini-icon"> IM </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Items <b class="caret"></b></span>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </div>
            </li>
            @endcan
            @can('manage-items', App\Models\User::class)
            <li class="nav-item mt-3">
                <h6 class="ps-4  ms-2 text-uppercase text-xs font-weight-bolder text-white">PAGES</h6>
            </li>
            @endcan
            @can('manage-items', App\Models\User::class)
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#pagesExamples"
                    class="nav-link text-white {{ $activePage == 'pages' ? ' active ' : '' }}  "
                    aria-controls="pagesExamples" role="button" aria-expanded="false">
                    <i class="material-icons-round {% if page.brand == 'RTL' %}ms-2{% else %} me-2{% endif %}">image</i>
                    <span class="nav-link-text ms-2 ps-1">Pages</span>
                </a>
                <div class="collapse {{ $activePage == 'pages' ? ' show ' : '' }} " id="pagesExamples">
                    <ul class="nav ">
                        <li class="nav-item ">
                            <a class="nav-link text-white {{ $activeItem == 'profile' ? ' active ' : '' }}  "
                                data-bs-toggle="collapse" aria-expanded="false" href="#profileExample">
                                <span class="sidenav-mini-icon"> P </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Profile <b class="caret"></b></span>
                            </a>
                            <div class="collapse {{ $activeItem == 'profile' ? ' show ' : '' }} " id="profileExample">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a class="nav-link text-white {{ $activeSubitem == 'profile-overview' ? ' active ' : '' }}  "
                                            href="{{ route('overview') }}">
                                            <span class="sidenav-mini-icon"> P </span>
                                            <span class="sidenav-normal  ms-2  ps-1"> Profile Overview </span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-white {{ $activeSubitem == 'all-projects' ? ' active ' : '' }} "
                                            href="{{ route('projects') }}">
                                            <span class="sidenav-mini-icon"> A </span>
                                            <span class="sidenav-normal  ms-2  ps-1"> All Projects </span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link text-white{{ $activeItem == 'users' ? ' active ' : '' }}  "
                                data-bs-toggle="collapse" aria-expanded="false" href="#usersExample">
                                <span class="sidenav-mini-icon"> U </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Users <b class="caret"></b></span>
                            </a>
                            <div class="collapse {{ $activeItem == 'users' ? ' show ' : '' }} " id="usersExample">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a class="nav-link text-white {{ $activeSubitem == 'reports' ? ' active ' : '' }} "
                                            href="{{ route('reports') }}">
                                            <span class="sidenav-mini-icon"> R </span>
                                            <span class="sidenav-normal  ms-2  ps-1"> Reports </span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-white {{ $activeSubitem == 'new-user' ? ' active ' : '' }} "
                                            href="{{ route('new-user') }}">
                                            <span class="sidenav-mini-icon"> N </span>
                                            <span class="sidenav-normal  ms-2  ps-1"> New User </span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link text-white {{ $activeItem == 'account' ? ' active ' : '' }} "
                                data-bs-toggle="collapse" aria-expanded="false" href="#accountExample">
                                <span class="sidenav-mini-icon"> A </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Account <b class="caret"></b></span>
                            </a>
                            <div class="collapse {{ $activeItem == 'account' ? ' show ' : '' }} " id="accountExample">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a class="nav-link text-white {{ $activeSubitem == 'settings' ? ' active ' : '' }}  "
                                            href="{{ route('settings') }}">
                                            <span class="sidenav-mini-icon"> S </span>
                                            <span class="sidenav-normal  ms-2  ps-1"> Settings </span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-white {{ $activeSubitem == 'billing' ? ' active ' : '' }} "
                                            href="{{ route('billing') }}">
                                            <span class="sidenav-mini-icon"> B </span>
                                            <span class="sidenav-normal  ms-2  ps-1"> Billing </span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-white {{ $activeSubitem == 'invoice' ? ' active ' : '' }} "
                                            href="{{ route('invoice') }}">
                                            <span class="sidenav-mini-icon"> I </span>
                                            <span class="sidenav-normal  ms-2  ps-1"> Invoice </span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-white {{ $activeSubitem == 'security' ? ' active ' : '' }} "
                                            href="{{ route('security') }}">
                                            <span class="sidenav-mini-icon"> S </span>
                                            <span class="sidenav-normal  ms-2  ps-1"> Security </span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link text-white {{ $activeItem == 'projects' ? ' active ' : '' }} "
                                data-bs-toggle="collapse" aria-expanded="false" href="#projectsExample">
                                <span class="sidenav-mini-icon"> P </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Projects <b class="caret"></b></span>
                            </a>
                            <div class="collapse {{ $activeItem == 'projects' ? ' show ' : '' }} " id="projectsExample">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a class="nav-link text-white {{ $activeSubitem == 'general' ? ' active ' : '' }} "
                                            href="{{ route('general') }}">
                                            <span class="sidenav-mini-icon"> G </span>
                                            <span class="sidenav-normal  ms-2  ps-1"> General </span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-white {{ $activeSubitem == 'timeline' ? ' active ' : '' }} "
                                            href="{{ route('timeline') }}">
                                            <span class="sidenav-mini-icon"> T </span>
                                            <span class="sidenav-normal  ms-2  ps-1"> Timeline </span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-white {{ $activeSubitem == 'new-project' ? ' active ' : '' }}  "
                                            href="{{ route('new-project') }}">
                                            <span class="sidenav-mini-icon"> N </span>
                                            <span class="sidenav-normal  ms-2  ps-1"> New Project </span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link text-white {{ $activeItem == 'virtual-reality' ? ' active ' : '' }} "
                                data-bs-toggle="collapse" aria-expanded="false" href="#vrExamples">
                                <span class="sidenav-mini-icon"> V </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Virtual Reality <b class="caret"></b></span>
                            </a>
                            <div class="collapse {{ $activeItem == 'virtual-reality' ? ' show ' : '' }} "
                                id="vrExamples">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a class="nav-link text-white {{ $activeSubitem == 'vr-default' ? ' active ' : '' }} "
                                            href="{{ route('vr-default') }}">
                                            <span class="sidenav-mini-icon"> V </span>
                                            <span class="sidenav-normal  ms-2  ps-1"> VR Default </span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-white {{ $activeSubitem == 'vr-info' ? ' active ' : '' }} "
                                            href="{{ route('vr-info') }}">
                                            <span class="sidenav-mini-icon"> V </span>
                                            <span class="sidenav-normal  ms-2  ps-1"> VR Info </span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="nav-item {{ $activeItem == 'pricing-page' ? ' active ' : '' }} ">
                            <a class="nav-link text-white  {{ $activeItem == 'pricing-page' ? ' active ' : '' }}"
                                href="{{ route('pricing-page') }}">
                                <span class="sidenav-mini-icon"> P </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Pricing Page </span>
                            </a>
                        </li>
                        <li class="nav-item {{ $activeItem == 'rtl' ? ' active ' : '' }}">
                            <a class="nav-link text-white  {{ $activeItem == 'rtl' ? ' active ' : '' }} "
                                href="{{ route('rtl') }}">
                                <span class="sidenav-mini-icon"> R </span>
                                <span class="sidenav-normal  ms-2  ps-1"> RTL </span>
                            </a>
                        </li>
                        <li class="nav-item {{ $activeItem == 'widgets' ? ' active ' : '' }}">
                            <a class="nav-link text-white  {{ $activeItem == 'widgets' ? ' active ' : '' }} "
                                href="{{ route('widgets') }}">
                                <span class="sidenav-mini-icon"> W </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Widgets </span>
                            </a>
                        </li>
                        <li class="nav-item  {{ $activeItem == 'charts' ? ' active ' : '' }}">
                            <a class="nav-link text-white {{ $activeItem == 'charts' ? ' active ' : '' }}"
                                href="{{ route('charts') }}">
                                <span class="sidenav-mini-icon"> C </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Charts </span>
                            </a>
                        </li>
                        <li class="nav-item  {{ $activeItem == 'sweet-alerts' ? ' active ' : '' }}">
                            <a class="nav-link text-white {{ $activeItem == 'sweet-alerts' ? ' active ' : '' }}"
                                href="{{ route('sweet-alerts') }}">
                                <span class="sidenav-mini-icon"> S </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Sweet Alerts </span>
                            </a>
                        </li>
                        <li class="nav-item  {{ $activeItem == 'notifications' ? ' active ' : '' }}">
                            <a class="nav-link text-white {{ $activeItem == 'notifications' ? ' active ' : '' }}"
                                href="{{ route('notifications') }}">
                                <span class="sidenav-mini-icon"> N </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Notifications </span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            @endcan
            @can('manage-items', App\Models\User::class)
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#applicationsExamples"
                    class="nav-link text-white {{ $activePage == 'applications' ? ' active ' : '' }}"
                    aria-controls="applicationsExamples" role="button" aria-expanded="false">
                    <i class="material-icons-round {% if page.brand == 'RTL' %}ms-2{% else %} me-2{% endif %}">apps</i>
                    <span class="nav-link-text ms-2 ps-1">Applications</span>
                </a>
                <div class="collapse {{ $activePage == 'applications' ? 'show' : '' }} " id="applicationsExamples">
                    <ul class="nav ">
                        <li class="nav-item {{ $activeItem == 'crm' ? ' active ' : '' }}">
                            <a class="nav-link text-white {{ $activeItem == 'crm' ? ' active ' : '' }}"
                                href="{{ route('crm') }}">
                                <span class="sidenav-mini-icon"> C </span>
                                <span class="sidenav-normal  ms-2  ps-1"> CRM </span>
                            </a>
                        </li>
                        <li class="nav-item {{ $activeItem == 'kanban' ? ' active ' : '' }}">
                            <a class="nav-link text-white {{ $activeItem == 'kanban' ? ' active ' : '' }}"
                                href="{{ route('kanban') }}">
                                <span class="sidenav-mini-icon"> K </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Kanban </span>
                            </a>
                        </li>
                        <li class="nav-item {{ $activeItem == 'wizard' ? ' active ' : '' }} ">
                            <a class="nav-link text-white {{ $activeItem == 'wizard' ? ' active ' : '' }} "
                                href="{{ route('wizard') }}">
                                <span class="sidenav-mini-icon"> W </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Wizard </span>
                            </a>
                        </li>
                        <li class="nav-item {{ $activeItem == 'datatables' ? ' active ' : '' }}">
                            <a class="nav-link text-white {{ $activeItem == 'datatables' ? ' active ' : '' }}"
                                href="{{ route('datatables') }}">
                                <span class="sidenav-mini-icon"> D </span>
                                <span class="sidenav-normal  ms-2  ps-1"> DataTables </span>
                            </a>
                        </li>
                        <li class="nav-item {{ $activeItem == 'calendar' ? ' active ' : '' }}">
                            <a class="nav-link text-white {{ $activeItem == 'calendar' ? ' active ' : '' }}"
                                href="{{ route('calendar') }}">
                                <span class="sidenav-mini-icon"> C </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Calendar </span>
                            </a>
                        </li>
                        <li class="nav-item {{ $activeItem == 'stats' ? ' active ' : '' }}">
                            <a class="nav-link text-white {{ $activeItem == 'stats' ? ' active ' : '' }} "
                                href="{{ route('stats') }}">
                                <span class="sidenav-mini-icon"> S </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Stats </span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            @endcan
            @can('manage-items', App\Models\User::class)
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#ecommerceExamples"
                    class="nav-link text-white {{ $activePage == 'ecommerce' ? ' active ' : '' }} "
                    aria-controls="ecommerceExamples" role="button" aria-expanded="false">
                    <i
                        class="material-icons-round {% if page.brand == 'RTL' %}ms-2{% else %} me-2{% endif %}">shopping_basket</i>
                    <span class="nav-link-text ms-2 ps-1">Ecommerce</span>
                </a>
                <div class="collapse {{ $activePage == 'ecommerce' ? ' show ' : '' }} " id="ecommerceExamples">
                    <ul class="nav ">
                        <li class="nav-item">
                            <a class="nav-link text-white {{ $activeItem == 'products' ? ' active ' : '' }}"
                                data-bs-toggle="collapse" aria-expanded="false" href="#productsExample">
                                <span class="sidenav-mini-icon"> P </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Products <b class="caret"></b></span>
                            </a>
                            <div class="collapse {{ $activeItem == 'products' ? ' show ' : '' }}" id="productsExample">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a class="nav-link text-white {{ $activeSubitem == 'new-product' ? ' active ' : '' }}"
                                            href="{{ route('new-product') }}">
                                            <span class="sidenav-mini-icon"> N </span>
                                            <span class="sidenav-normal  ms-2  ps-1"> New Product </span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-white {{ $activeSubitem == 'edit-product' ? ' active ' : '' }} "
                                            href="{{ route('edit-product') }}">
                                            <span class="sidenav-mini-icon"> E </span>
                                            <span class="sidenav-normal  ms-2  ps-1"> Edit Product </span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-white {{ $activeSubitem == 'product-page' ? ' active ' : '' }} "
                                            href="{{ route('product-page') }}">
                                            <span class="sidenav-mini-icon"> P </span>
                                            <span class="sidenav-normal  ms-2  ps-1"> Product Page </span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-white {{ $activeSubitem == 'products-list' ? ' active ' : '' }} "
                                            href="{{ route('products-list') }}">
                                            <span class="sidenav-mini-icon"> P </span>
                                            <span class="sidenav-normal  ms-2  ps-1"> Products List </span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link text-white {{ $activeItem == 'orders' ? ' active ' : '' }}"
                                data-bs-toggle="collapse" aria-expanded="false" href="#ordersExample">
                                <span class="sidenav-mini-icon"> O </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Orders <b class="caret"></b></span>
                            </a>
                            <div class="collapse {{ $activeItem == 'orders' ? ' show ' : '' }}" id="ordersExample">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a class="nav-link text-white {{ $activeSubitem == 'order-list' ? ' active ' : '' }} "
                                            href="{{ route('list') }}">
                                            <span class="sidenav-mini-icon"> O </span>
                                            <span class="sidenav-normal  ms-2  ps-1"> Order List </span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-white {{ $activeSubitem == 'order-details' ? ' active ' : '' }} "
                                            href="{{ route('details') }}">
                                            <span class="sidenav-mini-icon"> O </span>
                                            <span class="sidenav-normal  ms-2  ps-1"> Order Details </span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="nav-item {{ $activeItem == 'referral' ? ' active ' : '' }}">
                            <a class="nav-link text-white  {{ $activeItem == 'referral' ? ' active ' : '' }}"
                                href="{{ route('referral') }}">
                                <span class="sidenav-mini-icon"> R </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Referral </span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            @endcan
            @can('manage-items', App\Models\User::class)
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#authExamples" class="nav-link text-white "
                    aria-controls="authExamples" role="button" aria-expanded="false">
                    <i
                        class="material-icons-round {% if page.brand == 'RTL' %}ms-2{% else %} me-2{% endif %}">content_paste</i>
                    <span class="nav-link-text ms-2 ps-1">Authentication</span>
                </a>
                <div class="collapse " id="authExamples">
                    <ul class="nav ">
                        <li class="nav-item ">
                            <a class="nav-link text-white " data-bs-toggle="collapse" aria-expanded="false"
                                href="#signinExample">
                                <span class="sidenav-mini-icon"> S </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Sign In <b class="caret"></b></span>
                            </a>
                            <div class="collapse " id="signinExample">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a class="nav-link text-white " href="{{ route('basic-sign-in') }}">
                                            <span class="sidenav-mini-icon"> B </span>
                                            <span class="sidenav-normal  ms-2  ps-1"> Basic </span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-white " href="{{ route('cover-sign-in') }}">
                                            <span class="sidenav-mini-icon"> C </span>
                                            <span class="sidenav-normal  ms-2  ps-1"> Cover </span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-white " href="{{ route('illustration-sign-in') }}">
                                            <span class="sidenav-mini-icon"> I </span>
                                            <span class="sidenav-normal  ms-2  ps-1"> Illustration </span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link text-white " data-bs-toggle="collapse" aria-expanded="false"
                                href="#signupExample">
                                <span class="sidenav-mini-icon"> S </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Sign Up <b class="caret"></b></span>
                            </a>
                            <div class="collapse " id="signupExample">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a class="nav-link text-white " href="{{ route('basic-sign-up') }}">
                                            <span class="sidenav-mini-icon"> B </span>
                                            <span class="sidenav-normal  ms-2  ps-1"> Basic </span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-white " href="{{ route('cover-sign-up') }}">
                                            <span class="sidenav-mini-icon"> C </span>
                                            <span class="sidenav-normal  ms-2  ps-1"> Cover </span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-white " href="{{ route('illustration-sign-up') }}">
                                            <span class="sidenav-mini-icon"> I </span>
                                            <span class="sidenav-normal  ms-2  ps-1"> Illustration </span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link text-white " data-bs-toggle="collapse" aria-expanded="false"
                                href="#resetExample">
                                <span class="sidenav-mini-icon"> R </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Reset Password <b class="caret"></b></span>
                            </a>
                            <div class="collapse " id="resetExample">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a class="nav-link text-white " href="{{ route('basic-reset') }}">
                                            <span class="sidenav-mini-icon"> B </span>
                                            <span class="sidenav-normal  ms-2  ps-1"> Basic </span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-white " href="{{ route('cover-reset') }}">
                                            <span class="sidenav-mini-icon"> C </span>
                                            <span class="sidenav-normal  ms-2  ps-1"> Cover </span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-white " href="{{ route('illustration-reset') }}">
                                            <span class="sidenav-mini-icon"> I </span>
                                            <span class="sidenav-normal  ms-2  ps-1"> Illustration </span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link text-white " data-bs-toggle="collapse" aria-expanded="false"
                                href="#lockExample">
                                <span class="sidenav-mini-icon"> L </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Lock <b class="caret"></b></span>
                            </a>
                            <div class="collapse " id="lockExample">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a class="nav-link text-white " href="{{ route('basic-lock') }}">
                                            <span class="sidenav-mini-icon"> B </span>
                                            <span class="sidenav-normal  ms-2  ps-1"> Basic </span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-white " href="{{ route('cover-lock') }}">
                                            <span class="sidenav-mini-icon"> C </span>
                                            <span class="sidenav-normal  ms-2  ps-1"> Cover </span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-white " href="{{ route('illustration-lock') }}">
                                            <span class="sidenav-mini-icon"> I </span>
                                            <span class="sidenav-normal  ms-2  ps-1"> Illustration </span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link text-white " data-bs-toggle="collapse" aria-expanded="false"
                                href="#StepExample">
                                <span class="sidenav-mini-icon"> 2 </span>
                                <span class="sidenav-normal  ms-2  ps-1"> 2-Step Verification <b
                                        class="caret"></b></span>
                            </a>
                            <div class="collapse " id="StepExample">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a class="nav-link text-white " href="{{ route('basic-verification') }}">
                                            <span class="sidenav-mini-icon"> B </span>
                                            <span class="sidenav-normal  ms-2  ps-1"> Basic </span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-white " href="{{ route('cover-verification') }}">
                                            <span class="sidenav-mini-icon"> C </span>
                                            <span class="sidenav-normal  ms-2  ps-1"> Cover </span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-white " href="{{ route('illustration-verification') }}">
                                            <span class="sidenav-mini-icon"> I </span>
                                            <span class="sidenav-normal  ms-2  ps-1"> Illustration </span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link text-white " data-bs-toggle="collapse" aria-expanded="false"
                                href="#errorExample">
                                <span class="sidenav-mini-icon"> E </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Error <b class="caret"></b></span>
                            </a>
                            <div class="collapse " id="errorExample">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a class="nav-link text-white " href="{{ route('404') }}">
                                            <span class="sidenav-mini-icon"> E </span>
                                            <span class="sidenav-normal  ms-2  ps-1"> Error 404 </span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-white " href="{{ route('500') }}">
                                            <span class="sidenav-mini-icon"> E </span>
                                            <span class="sidenav-normal  ms-2  ps-1"> Error 500 </span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </div>
            </li>
            @endcan
            @can('manage-items', App\Models\User::class)
            <li class="nav-item">
                <hr class="horizontal light" />
                <h6 class="ps-4  ms-2 text-uppercase text-xs font-weight-bolder text-white">DOCS</h6>
            </li>
            @endcan
            @can('manage-items', App\Models\User::class)
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#basicExamples" class="nav-link text-white "
                    aria-controls="basicExamples" role="button" aria-expanded="false">
                    <i
                        class="material-icons-round {% if page.brand == 'RTL' %}ms-2{% else %} me-2{% endif %}">upcoming</i>
                    <span class="nav-link-text ms-2 ps-1">Basic</span>
                </a>
                <div class="collapse " id="basicExamples">
                    <ul class="nav ">
                        <li class="nav-item ">
                            <a class="nav-link text-white " data-bs-toggle="collapse" aria-expanded="false"
                                href="#gettingStartedExample">
                                <span class="sidenav-mini-icon"> G </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Getting Started <b class="caret"></b></span>
                            </a>
                            <div class="collapse " id="gettingStartedExample">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a class="nav-link text-white "
                                            href="../../documentation/getting-started/installation.html"
                                            target="_blank">
                                            <span class="sidenav-mini-icon"> Q </span>
                                            <span class="sidenav-normal  ms-2  ps-1"> Quick Start </span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-white "
                                            href="../../documentation/getting-started/license.html"
                                            target="_blank">
                                            <span class="sidenav-mini-icon"> L </span>
                                            <span class="sidenav-normal  ms-2  ps-1"> License </span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-white "
                                            href="../../documentation/getting-started/overview.html"
                                            target="_blank">
                                            <span class="sidenav-mini-icon"> C </span>
                                            <span class="sidenav-normal  ms-2  ps-1"> Contents </span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-white "
                                            href="../../documentation/getting-started/build-tools.html"
                                            target="_blank">
                                            <span class="sidenav-mini-icon"> B </span>
                                            <span class="sidenav-normal  ms-2  ps-1"> Build Tools </span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link text-white " data-bs-toggle="collapse" aria-expanded="false"
                                href="#foundationExample">
                                <span class="sidenav-mini-icon"> F </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Foundation <b class="caret"></b></span>
                            </a>
                            <div class="collapse " id="foundationExample">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a class="nav-link text-white "
                                            href="../../documentation/foundation/colors.html"
                                            target="_blank">
                                            <span class="sidenav-mini-icon"> C </span>
                                            <span class="sidenav-normal  ms-2  ps-1"> Colors </span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-white "
                                            href="../../documentation/foundation/grid.html"
                                            target="_blank">
                                            <span class="sidenav-mini-icon"> G </span>
                                            <span class="sidenav-normal  ms-2  ps-1"> Grid </span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-white "
                                            href="../../documentation/foundation/typography.html"
                                            target="_blank">
                                            <span class="sidenav-mini-icon"> T </span>
                                            <span class="sidenav-normal  ms-2  ps-1"> Typography </span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link text-white "
                                            href="../../documentation/foundation/icons.html"
                                            target="_blank">
                                            <span class="sidenav-mini-icon"> I </span>
                                            <span class="sidenav-normal  ms-2  ps-1"> Icons </span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </div>
            </li>
            @endcan
            @can('manage-items', App\Models\User::class)
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#componentsExamples" class="nav-link text-white "
                    aria-controls="componentsExamples" role="button" aria-expanded="false">
                    <i
                        class="material-icons-round {% if page.brand == 'RTL' %}ms-2{% else %} me-2{% endif %}">view_in_ar</i>
                    <span class="nav-link-text ms-2 ps-1">Components</span>
                </a>
                <div class="collapse " id="componentsExamples">
                    <ul class="nav ">
                        <li class="nav-item ">
                            <a class="nav-link text-white "
                                href="../../documentation/components/alerts.html"
                                target="_blank">
                                <span class="sidenav-mini-icon"> A </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Alerts </span>
                            </a>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link text-white "
                                href="../../documentation/components/badge.html"
                                target="_blank">
                                <span class="sidenav-mini-icon"> B </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Badge </span>
                            </a>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link text-white "
                                href="../../documentation/components/buttons.html"
                                target="_blank">
                                <span class="sidenav-mini-icon"> B </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Buttons </span>
                            </a>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link text-white "
                                href="../../documentation/components/cards.html"
                                target="_blank">
                                <span class="sidenav-mini-icon"> C </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Card </span>
                            </a>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link text-white "
                                href="../../documentation/components/carousel.html"
                                target="_blank">
                                <span class="sidenav-mini-icon"> C </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Carousel </span>
                            </a>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link text-white "
                                href="../../documentation/components/collapse.html"
                                target="_blank">
                                <span class="sidenav-mini-icon"> C </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Collapse </span>
                            </a>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link text-white "
                                href="../../documentation/components/dropdowns.html"
                                target="_blank">
                                <span class="sidenav-mini-icon"> D </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Dropdowns </span>
                            </a>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link text-white "
                                href="../../documentation/components/forms.html"
                                target="_blank">
                                <span class="sidenav-mini-icon"> F </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Forms </span>
                            </a>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link text-white "
                                href="../../documentation/components/modal.html"
                                target="_blank">
                                <span class="sidenav-mini-icon"> M </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Modal </span>
                            </a>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link text-white "
                                href="../../documentation/components/navs.html"
                                target="_blank">
                                <span class="sidenav-mini-icon"> N </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Navs </span>
                            </a>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link text-white "
                                href="../../documentation/components/navbar.html"
                                target="_blank">
                                <span class="sidenav-mini-icon"> N </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Navbar </span>
                            </a>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link text-white "
                                href="../../documentation/components/pagination.html"
                                target="_blank">
                                <span class="sidenav-mini-icon"> P </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Pagination </span>
                            </a>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link text-white "
                                href="../../documentation/components/popovers.html"
                                target="_blank">
                                <span class="sidenav-mini-icon"> P </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Popovers </span>
                            </a>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link text-white "
                                href="../../documentation/components/progress.html"
                                target="_blank">
                                <span class="sidenav-mini-icon"> P </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Progress </span>
                            </a>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link text-white "
                                href="../../documentation/components/spinners.html"
                                target="_blank">
                                <span class="sidenav-mini-icon"> S </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Spinners </span>
                            </a>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link text-white "
                                href="../../documentation/components/tables.html"
                                target="_blank">
                                <span class="sidenav-mini-icon"> T </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Tables </span>
                            </a>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link text-white "
                                href="../../documentation/components/tooltips.html"
                                target="_blank">
                                <span class="sidenav-mini-icon"> T </span>
                                <span class="sidenav-normal  ms-2  ps-1"> Tooltips </span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            @endcan
            --}}
        </ul>
    </div>
    
    @push('js')
    <script>
        function showModalGuest() {
            $('#modal_logged_as_guest').modal('show'); // Show the modal
        };
    </script>
    @endpush
</aside>

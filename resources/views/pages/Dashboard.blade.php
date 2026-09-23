<x-page-template bodyClass='dh-shell bg-gray-200'>
    <x-shell.nav active="dashboard" />
    
    
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <!-- Navbar -->
        <x-shell.header title="My Dashboard" icon="dashboard" />
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
                            <span class="material-icons-round" aria-hidden="true">directions_boat</span>
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
{{--
    "From the blog" carousel (Pablo, 2026-09-17: "the first thing in the My
    Dashboard"; reverted 2026-09-18 - a "show it like the Blog index" grid
    version briefly replaced this, but "you broke the carousel. The
    carousel was perfect as it was" - back to one slide at a time,
    auto-advancing, dots to jump directly). Posts and their order come
    from MyDashboardController - App\Models\Post::forViewer(), ranked by
    the diver's own certification level. The thumbnail-not-showing bug
    (Pablo, 2026-09-18) was a leftover ['image'] array key from the
    mock-array days - the real column is cover_image - fixed here too,
    with the same illustration fallback the Blog index uses when a post
    has no uploaded cover. --}}
@if(!empty($blogPosts))
    @php
        $dashCoverOr = fn ($p) => $p->cover_image ? asset($p->cover_image) : asset('assets/img/illustrations/dive-site.webp');
        $dashFocusOf = fn ($p) => 'object-position: center ' . ($p->cover_focus ?: 'center') . ';';
    @endphp
    <div class="row">
        <div class="col-md-12">
            <section class="dh-card">
                <h2 class="dh-card-head">From the blog
                    <a class="dh-dash-headlink" href="{{ route('Blog') }}">See all</a></h2>
                <div class="dh-card-body">
                    <div class="dh-mini-carousel" id="dhBlogCarousel">
                        <div class="dh-mini-carousel-track">
                            @foreach($blogPosts as $i => $bp)
                                <a href="{{ route('Blog.show', $bp->slug) }}" class="dh-mini-carousel-slide {{ $i === 0 ? 'is-active' : '' }}">
                                    <img src="{{ $dashCoverOr($bp) }}" alt="" loading="lazy" style="{{ $dashFocusOf($bp) }}">
                                    <span class="dh-mini-carousel-body">
                                        <span class="dh-blog-eyebrow">{{ $bp->category }}</span>
                                        <span class="dh-mini-carousel-title">{{ $bp->title }}</span>
                                    </span>
                                </a>
                            @endforeach
                        </div>
                        @if(count($blogPosts) > 1)
                            <div class="dh-mini-carousel-dots">
                                @foreach($blogPosts as $i => $bp)
                                    <button type="button" class="dh-mini-carousel-dot {{ $i === 0 ? 'is-active' : '' }}" data-dh-carousel-dot="{{ $i }}" aria-label="Show article {{ $i + 1 }}"></button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </section>
        </div>
    </div>
@endif

<div class="row dh-dash-cards">
                {{---Card My Upcoming trips--}}
                <div class="col-md-5 mb-4">
                    <section class="dh-card">
                        <h2 class="dh-card-head">My upcoming dives</h2>
                        <div class="dh-card-body" style="display: block; max-height: 350px; overflow-y: scroll">
                            @if(count($trips) == 0)
                                <p class="text-sm mb-2">Nothing saved yet. Find a dive and add it to your calendar; it shows up here and in the calendar below.</p>
                                <a class="dh-btn dh-btn-primary" href="{{ route('Trips') }}"><span class="material-icons-round">directions_boat</span>Find a dive</a>
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
                                            $cancelled = $trip->cancelled ?? false;
                                            $tags = (string) ($trip->tags ?? '');
                                            $typeIcon = str_contains($tags, 'SHA') ? 'icons_shark_center.png' : (str_contains($tags, 'TEC') ? 'icons_tec_center.png' : (str_contains($tags, 'LOB') ? 'icons_lobster_center.png' : 'icons_rec_center.png'));
                                            $typeName = str_contains($tags, 'SHA') ? 'Shark' : (str_contains($tags, 'TEC') ? 'Technical' : (str_contains($tags, 'LOB') ? 'Lobster' : 'Recreational'));
                                            $when = DateTime::createFromFormat('Y-m-d', $trip->date);
                                        @endphp
                                        <li class="dh-dive-row {{ $cancelled ? 'is-cancelled' : '' }}">
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
                                            <label id="upComingTripsCancelled-{{ $trip->eventId }}" hidden>{{ $cancelled ? '1' : '0' }}</label>

                                            @if($trip->id)
                                                <a class="dh-dive-main" href="{{ route('TripDetails', ['tripId' => $trip->id]) }}">
                                                    <span class="dh-dive-type" title="{{ $typeName }} dive"><img src="{{ asset('assets') }}/img/icons/{{ $typeIcon }}" alt="{{ $typeName }}"></span>
                                                    <span class="dh-dive-text">
                                                        <span class="dh-dive-title do-not-translate">{{ $trip->tripName }}</span>
                                                        <span class="dh-dive-when">{{ $when ? $when->format('D, M j') : $trip->date }} <b>{{ $trip->departureTime }}</b></span>
                                                        <span class="dh-dive-op do-not-translate">{{ $trip->operatorName }}</span>
                                                    </span>
                                                    <span class="material-icons-round dh-dive-chevron" aria-hidden="true">chevron_right</span>
                                                </a>
                                            @else
                                                {{-- Cancelled: no live trip page left to link to. --}}
                                                <div class="dh-dive-main">
                                                    <span class="dh-dive-type" title="{{ $typeName }} dive"><img src="{{ asset('assets') }}/img/icons/{{ $typeIcon }}" alt="{{ $typeName }}"></span>
                                                    <span class="dh-dive-text">
                                                        <span class="dh-dive-title do-not-translate">{{ $trip->tripName }}</span>
                                                        <span class="dh-dive-when">{{ $when ? $when->format('D, M j') : $trip->date }} <b>{{ $trip->departureTime }}</b></span>
                                                        <span class="dh-dive-op do-not-translate">{{ $trip->operatorName }}</span>
                                                    </span>
                                                </div>
                                            @endif
                                            <button type="button" class="dh-dive-status {{ $cancelled ? 'is-cancelled' : ($trip->booked ? 'is-booked' : 'is-open') }}" onclick="clickOnMyTrips({{ $trip->eventId }})" aria-haspopup="dialog">
                                                <span class="material-icons-round" aria-hidden="true">{{ $cancelled ? 'cancel' : ($trip->booked ? 'check_circle' : 'radio_button_unchecked') }}</span>
                                                <span>{{ $cancelled ? 'Cancelled' : ($trip->booked ? 'Booked' : 'Not booked yet') }}</span>
                                                <span class="dh-dive-status-more">Options</span>
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </section>
                </div>

                {{--
                    My groups. Small on purpose: this is the slot the group feed will
                    grow into (what people posted, who is going, unread), once group
                    chat has read tracking. Today it answers "what is my group doing
                    next" and surfaces invites, which is the one group action a diver
                    should never miss.
                --}}
                <div class="col-md-7">
                    <section class="dh-card">
                        <h2 class="dh-card-head">My groups
                            <a class="dh-dash-headlink" href="{{ route('MyGroups') }}">All groups</a></h2>
                        <div class="dh-card-body">
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
                                                    <span class="d-flex align-items-center gap-2" style="min-width: 0;">
                                                        <span class="dh-group-name do-not-translate" style="min-width: 0;">{{ $group->name }}</span>
                                                        <span style="flex: 0 0 auto;">@include('pages.Groups.partials.VisibilityPill')</span>
                                                    </span>
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
                    </section>
                </div>
            </div>

            <div class="row">
                {{---Card recommended for this weekend --}}
                {{--
                    Rows, not a seven column table (Zach, 2026-09-10: "too wide, you
                    can scroll, but they look bad"). Date block, the trip as the link,
                    operator as plain text, seats as the one action, level and depth
                    as small facts. Capped at six; "See all" opens the finder on the
                    weekend preset, which is the same list in full.
                --}}
                <div class="col-md-12">
                    <section class="dh-card">
                        @php $ws = \Carbon\Carbon::parse($weekendStart ?? now()); $weekendLabel = $ws->isCurrentWeek() ? 'this weekend' : 'the weekend of ' . $ws->format('M j'); @endphp
                        <h2 class="dh-card-head">Recommended for {{ $weekendLabel }}
                            <a class="dh-dash-headlink" href="{{ route('Trips') }}?range=weekend">See all</a></h2>
                        <div class="dh-card-body">
                            @php $recommended = collect($favTrips)->filter(fn ($t) => !empty($t->fav))->values(); @endphp
                            @if($recommended->isEmpty())
                                <p class="text-sm mb-2">No picks for {{ $weekendLabel }} yet. Recommendations come from the places, boats and level on your profile.</p>
                                <a class="dh-btn dh-btn-primary" href="{{ route('overview') }}"><span class="material-icons-round">tune</span>Update my profile</a>
                            @else
                                <ul class="dh-trip-list">
                                    @foreach($recommended->take(6) as $trip)
                                        @php
                                            $d = new DateTime($trip->date);
                                            $site = !empty($trip->site[0]) ? $trip->site[0] : null;
                                            $seats = (int) $trip->tripFreeSpots;
                                        @endphp
                                        <li class="dh-trip-row">
                                            <span class="dh-trip-date"><b>{{ $d->format('j') }}</b><span>{{ $d->format('D') }}</span></span>
                                            <a class="dh-trip-main" href="{{ route('TripDetails', ['tripId' => $trip->id]) }}">
                                                <span class="dh-trip-title do-not-translate">{{ $trip->tripName }}</span>
                                                <span class="dh-trip-meta"><b>{{ $trip->departureTime }}</b> &middot; <span class="do-not-translate">{{ $trip->operatorName }}</span></span>
                                                @if($site)
                                                    <span class="dh-trip-facts">
                                                        <img src="{{ asset('assets') }}/img/icons/icons_level_{{ $site->level }}.png" alt="Level {{ $site->level }}">
                                                        @if($site->maxDepth)<span>{{ $site->maxDepth }} ft</span>@endif
                                                    </span>
                                                @endif
                                            </a>
                                            @if($seats <= 0)
                                                <span class="dh-seats is-full">Full</span>
                                            @elseif($trip->linkToBook)
                                                <a class="dh-seats is-open" href="{{ $trip->linkToBook }}" target="_blank" rel="noopener"><span class="material-icons-round" aria-hidden="true">event_seat</span>{{ $seats >= 1000 ? 'Book' : $seats . ' ' . Str::plural('seat', $seats) }}</a>
                                            @else
                                                <span class="dh-seats is-open"><span class="material-icons-round" aria-hidden="true">event_seat</span>{{ $seats >= 1000 ? 'Open' : $seats . ' ' . Str::plural('seat', $seats) }}</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                                @if($recommended->count() > 6)
                                    <p class="dh-card-foot"><a href="{{ route('Trips') }}?range=weekend">See all {{ $recommended->count() }} picks</a></p>
                                @endif
                            @endif
                        </div>
                    </section>
                </div>

                {{---Card My wishlist --}}
                {{--
                    One row per saved site with its next boat, instead of a nine column
                    table. The site is the link; the next boat is the second line, with
                    the seats as the action. "No boat scheduled yet" is the honest state
                    for a site with nothing confirmed; the wishlist alert email covers
                    the moment that changes.
                --}}
                <div class="col-md-12">
                    <section class="dh-card">
                        <h2 class="dh-card-head">My wishlist
                            {{-- A way in from the card itself, not only from the empty state. --}}
                            <a class="dh-dash-headlink" href="{{ route('DiveSites') }}">Add sites</a></h2>
                        <div class="dh-card-body">
                            @if(!count($wished))
                                {{-- Pablo, 2026-09-10: the wishlist only earns its place if divers fill
                                     it, so the empty state sends them somewhere to do that. Saving a site
                                     means we tell them when a boat is scheduled to go there. --}}
                                <p class="text-sm mb-3">Save the sites you want to dive and we will tell you when a boat is going there. Tap the heart on any site card.</p>
                                <a class="dh-btn dh-btn-primary" href="{{ route('DiveSites') }}">
                                    <span class="material-icons-round">travel_explore</span>Find sites to add
                                </a>
                            @else
                                {{-- Capped like the weekend picks (Zach, 2026-09-10: fifteen saved
                                     sites made the card longer than the rest of the page). The
                                     remainder is in the page, just hidden, so "show all" costs
                                     nothing and keeps the wishlist on the dashboard. --}}
                                @php $wishShown = 5; @endphp
                                <ul class="dh-trip-list">
                                    @foreach($wished as $i => $wish)
                                        @if(!$wish->site) @continue @endif
                                        @php $ws = $wish->site; $hasBoat = !empty($wish->tripId); $seats = (int) ($wish->tripFreeSpots ?? 0); @endphp
                                        <li class="dh-wish-row {{ $i >= $wishShown ? 'dh-wish-extra' : '' }}" {{ $i >= $wishShown ? 'hidden' : '' }}>
                                            <a class="dh-wish-site" href="{{ route('SiteDetails') }}/{{ $ws->slug ?? $ws->id }}">
                                                <x-site-type-icon :type="$ws->type" size="28" />
                                                <span class="dh-trip-main">
                                                    <span class="dh-trip-title do-not-translate">{{ $ws->name }}</span>
                                                    <span class="dh-trip-facts">
                                                        <img src="{{ asset('assets') }}/img/icons/icons_level_{{ $ws->level }}.png" alt="Level {{ $ws->level }}">
                                                        @if($ws->maxDepth)<span>{{ $ws->maxDepth }} ft</span>@endif
                                                        <span>{{ ucfirst($ws->type) }}</span>
                                                    </span>
                                                </span>
                                                <span class="material-icons-round dh-dive-chevron" aria-hidden="true">chevron_right</span>
                                            </a>
                                            <span class="dh-wish-next">
                                                <span class="material-icons-round" aria-hidden="true">{{ $hasBoat ? 'directions_boat' : 'schedule' }}</span>
                                                @if($hasBoat)
                                                    @php $wd = \Carbon\Carbon::parse($wish->date); @endphp
                                                    <span>Next boat</span>
                                                    <a href="{{ route('TripDetails', ['tripId' => $wish->tripId]) }}">{{ $wd->format('D, M j') }} {{ $wish->time }} &middot; <span class="do-not-translate">{{ $wish->operator }}</span></a>
                                                    @if($seats <= 0)
                                                        <span class="dh-seats is-full">Full</span>
                                                    @elseif($wish->linkToBook)
                                                        <a class="dh-seats is-open" href="{{ $wish->linkToBook }}" target="_blank" rel="noopener">{{ $seats >= 1000 ? 'Book' : $seats . ' ' . Str::plural('seat', $seats) }}</a>
                                                    @endif
                                                @else
                                                    <span>No boat scheduled yet. We will tell you when one is.</span>
                                                @endif
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                                @if(count($wished) > $wishShown)
                                    <p class="dh-card-foot">
                                        <button type="button" class="dh-linkbtn" data-dh-showall="dh-wish-extra">Show all {{ count($wished) }} sites</button>
                                    </p>
                                @endif
                            @endif
                        </div>
                    </section>
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
            <div class="row dh-cal-row">
                <div class="col-md-12">
                    <section class="dh-card">
                        <h2 class="dh-card-head">My Calendars
                            <a class="dh-dash-headlink" href="{{ route('MyCalendar') }}">Open full calendar</a></h2>
                        <div class="dh-card-body">
                            <div class="dh-seg" role="tablist" aria-label="Which calendar">
                                <button type="button" class="dh-seg-btn is-active" role="tab" aria-selected="true" data-dh-cal="mine">My dives</button>
                                @if( !empty($favOperators) )
                                <button type="button" class="dh-seg-btn" role="tab" aria-selected="false" data-dh-cal="ops">Favorite operators</button>
                                @endif
                            </div>

                            <div id="dh-cal-mine" data-dh-cal-panel="mine">
                                <p class="text-xs text-secondary mb-2"><span class="dh-cal-key is-booked"></span> booked &nbsp; <span class="dh-cal-key is-open"></span> not booked yet &nbsp;&middot;&nbsp; tap a dive for options, tap a day to find a dive</p>
                                <div class="calendar" id="my-calendar"></div>

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
                    </section>
                </div>
            </div>

            {{-- The Me tab's rows now render on every page via page-template.blade.php. --}}


                
            
            
            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>
    
    
    {{--<x-plugins></x-plugins>--}}
    
    @push('js')
    <script src="{{ asset('assets') }}/js/plugins/jquery-3.6.0.min.js" type="text/javascript"></script>
    <script src="/assets/js/plugins/fullcalendar.min.js"></script>
    <script src="{{ asset('assets') }}/js/divershub-calendar.js"></script>
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
                + '?cc=support@divers-hub.com'
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
            const cancelled = document.getElementById("upComingTripsCancelled-" + id).innerText;
            // Create the desired string
            const formattedString = `${day} ${month} ${year} ${hours}:${String(minutes).padStart(2, '0')}`;
            var modal = document.getElementById('modal-title-notification-calendar');
            modal.innerHTML = "Edit event in calendar: <br>" + formattedString + "<br> <b>" + title + "</b> <br> <p class='text-info text-sm text-bold'>" + operator + "<p>" +
                (cancelled == '1' ? "<p class='text-danger text-sm text-bold'>This trip was cancelled by the operator.</p>" : "");
            $('#modal-calendar').modal('show');

            document.getElementById("button-remove").href = '/RemoveFromCalendar/' + eventId;

            // A cancelled trip has no live trip/booking data left to act on -
            // Remove is the only thing left for the diver to do here.
            if (cancelled == '1') {
                document.getElementById("button-go").hidden = true;
                document.getElementById("div-button-link").hidden = true;
                document.getElementById("div-button-book").hidden = true;
                document.getElementById("div-button-waiver").hidden = true;
                document.getElementById("span-booked").hidden = true;
                document.getElementById("span-not-booked").hidden = true;
                return;
            }

            document.getElementById("button-go").hidden = false;
            document.getElementById("button-go").href = '/TripDetails/' + tripId;
            document.getElementById("button-book").href = '/SetEventBook/' + eventId;
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
                    echo "color: '" . ($trip->tripType == "Technical" ? "#2e7d4f" : "#5a6b78") . "' },";
                }
            @endphp
            

        ],
        views: dhResponsiveViews,
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
            initialView: getResponsiveView(),
            windowResize: function() { myCalendar.changeView(getResponsiveView()); },
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
                        echo "color: '" . (($trip->cancelled ?? false) ? '#8c8c8c' : ($trip->booked ? '#0f7b3f' : '#c0392b')) . "' },";
                    }
                @endphp
            ],
            views: dhResponsiveViews,
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



   




    {{-- "From the blog" carousel: auto-advance, pause on hover/focus so a
         diver reading one card doesn't have it swap out from under them,
         dots jump straight to a slide. Slide-count-agnostic, so a 6th post
         later needs no JS change. --}}
    <script>
        (function () {
            var root = document.getElementById('dhBlogCarousel');
            if (!root) return;
            var slides = Array.prototype.slice.call(root.querySelectorAll('.dh-mini-carousel-slide'));
            var dots = Array.prototype.slice.call(root.querySelectorAll('.dh-mini-carousel-dot'));
            if (slides.length < 2) return;

            var index = 0;
            var timer = null;

            // Slides left/right instead of an instant cut, on the timer and
            // on a swipe alike (Pablo, 2026-09-18: "when they move for timer
            // or we swipe, the content moves left or right like a carousel
            // effect"). dir is +1 (next) or -1 (prev), always passed
            // explicitly by the caller rather than derived from the index
            // change - deriving it would get the direction backwards at the
            // wraparound point (last slide back to the first, or the
            // reverse), since going from index 4 to 0 numerically looks
            // like "backward" even though a swipe-left or the auto-advance
            // timer both mean "forward".
            function show(i, dir) {
                var newIndex = (i + slides.length) % slides.length;
                if (newIndex === index) return;
                var direction = typeof dir === 'number' ? dir : (newIndex > index ? 1 : -1);

                var outgoing = slides[index];
                var incoming = slides[newIndex];

                // Snap the incoming slide off-screen on the correct side
                // with transitions off, then force a reflow so the browser
                // registers that starting position before re-enabling the
                // transition - without this there's nothing to animate
                // from and it would just appear already in place.
                incoming.style.transition = 'none';
                incoming.style.transform = 'translateX(' + (direction > 0 ? '100%' : '-100%') + ')';
                incoming.style.opacity = '0';
                void incoming.offsetWidth;
                incoming.style.transition = '';

                outgoing.classList.remove('is-active');
                outgoing.style.transform = 'translateX(' + (direction > 0 ? '-100%' : '100%') + ')';
                outgoing.style.opacity = '0';

                incoming.classList.add('is-active');
                incoming.style.transform = 'translateX(0)';
                incoming.style.opacity = '1';

                index = newIndex;
                dots.forEach(function (d, di) { d.classList.toggle('is-active', di === index); });
            }

            function start() {
                stop();
                timer = setInterval(function () { show(index + 1, 1); }, 4500);
            }
            function stop() {
                if (timer) clearInterval(timer);
                timer = null;
            }

            dots.forEach(function (dot) {
                dot.addEventListener('click', function () {
                    show(parseInt(dot.getAttribute('data-dh-carousel-dot'), 10));
                    start();
                });
            });

            root.addEventListener('mouseenter', stop);
            root.addEventListener('mouseleave', start);
            root.addEventListener('focusin', stop);
            root.addEventListener('focusout', start);

            // Swipe left/right for next/previous article (Pablo, 2026-09-18).
            // touchmove stays passive (never blocks the page's own vertical
            // scroll) and only counts a gesture as a swipe once it's clearly
            // more horizontal than vertical, so scrolling past the carousel
            // still works normally. A slide is a link to the article, so a
            // swipe that crossed the threshold also cancels the click that
            // follows on release - otherwise every swipe would also open
            // whichever slide it ended on.
            var touchStartX = null;
            var touchStartY = null;
            var swiped = false;

            root.addEventListener('touchstart', function (e) {
                if (e.touches.length !== 1) return;
                touchStartX = e.touches[0].clientX;
                touchStartY = e.touches[0].clientY;
                // Reset here, not just after a swipe's click - once a real
                // drag happens, mobile browsers don't fire a click at all on
                // release (that's how they tell a swipe from a tap), so the
                // click handler below never got a turn to clear the flag.
                // Left set from the previous gesture, it would silently
                // swallow every plain tap after the first swipe.
                swiped = false;
                stop();
            }, { passive: true });

            root.addEventListener('touchmove', function (e) {
                if (touchStartX === null) return;
                var dx = e.touches[0].clientX - touchStartX;
                var dy = e.touches[0].clientY - touchStartY;
                if (Math.abs(dx) > 24 && Math.abs(dx) > Math.abs(dy)) {
                    swiped = true;
                }
                // Once it's a confirmed horizontal swipe, tell iOS we're
                // handling this gesture ourselves - otherwise, with no
                // horizontal scroll to actually perform, Safari plays its
                // rubber-band bounce on the whole page instead (Pablo,
                // 2026-09-18: "the whole screen tries to swipe...nothing
                // really moves, but the whole screen goes and then comes").
                // Can't call this on a passive listener, hence {passive:
                // false} below - a vertical drag (swiped stays false) is
                // still never touched, so normal page scroll is unaffected.
                if (swiped) {
                    e.preventDefault();
                }
            }, { passive: false });

            root.addEventListener('touchend', function (e) {
                if (swiped && touchStartX !== null) {
                    var dx = e.changedTouches[0].clientX - touchStartX;
                    var dir = dx < 0 ? 1 : -1;
                    show(index + dir, dir);
                }
                touchStartX = null;
                touchStartY = null;
                start();
            });

            root.addEventListener('click', function (e) {
                if (swiped) {
                    e.preventDefault();
                    swiped = false;
                }
            });

            start();
        })();
    </script>

    @endpush
</x-page-template>

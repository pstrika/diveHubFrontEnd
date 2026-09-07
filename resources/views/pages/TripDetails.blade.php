<x-page-template bodyClass='dh-shell bg-gray-200' :SEO="$SEO ?? []">
    <x-shell.nav active="today" />

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">

    @if(auth()->user()->isNotGuest())
    {{--add to group modal--}}
    <div class="modal fade" id="modalAddToGroup" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-normal">Add this trip to a group</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                @if($myGroups->isEmpty())
                    <div class="modal-body">
                        <p class="text-secondary mb-0">You're not in any groups yet. <a href="{{ route('MyGroups') }}">Create or join one</a> first.</p>
                    </div>
                @elseif($myGroups->every(fn($g) => $g->alreadyAdded))
                    <div class="modal-body">
                        <p class="text-secondary mb-0">This trip is already on the calendar for all of your groups.</p>
                    </div>
                @else
                    <form method="POST" id="addToGroupForm" action="">
                        @csrf
                        <input type="hidden" name="tripId" value="{{ $tripDetails->id }}">
                        <div class="modal-body">
                            <label class="form-label">Which group?</label>
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary border w-100 d-flex justify-content-between align-items-center dropdown-toggle" type="button" id="groupPickerBtn" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span id="groupPickerLabel" class="d-flex align-items-center text-secondary">Choose a group...</span>
                                </button>
                                <ul class="dropdown-menu w-100" aria-labelledby="groupPickerBtn">
                                    @foreach($myGroups as $myGroup)
                                        @if(!$myGroup->alreadyAdded)
                                            <li>
                                                <a class="dropdown-item d-flex align-items-center" href="javascript:void(0);"
                                                   onclick="selectGroupForTrip('{{ route('Groups.dives.store', ['group' => $myGroup->slug]) }}', '{{ addslashes($myGroup->name) }}', '{{ asset('assets/' . $myGroup->avatar) }}')">
                                                    <img src="{{ asset('assets/' . $myGroup->avatar) }}" class="rounded-circle border-info me-2" style="width: 28px; height: 28px; object-fit: cover; border-width: 2px; border-style: solid;">
                                                    {{ $myGroup->name }}
                                                </a>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn bg-gradient-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" id="addToGroupSubmitBtn" class="btn bg-gradient-info" disabled>Add to group calendar</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
    @endif

        <style>
            /* ------ Default Style ---------- */
            .gauge-container {
            width: 150px;
            height: 70px;
            display: block;
            float: center;
            padding: 10px;
            /*background-color: #222;*/
            margin: 7px;
            border-radius: 3px;
            position: relative;
            }
            .gauge-container > .label {
            position: absolute;
            right: 0;
            top: 0;
            display: inline-block;
            background: rgba(0,0,0,0.5);
            font-family: monospace;
            font-size: 0.8em;
            padding: 5px 10px;
            }
            .gauge-container > .gauge .dial {
            stroke: #334455;
            stroke-width: 2;
            fill: rgba(0,0,0,0);
            }
            .gauge-container > .gauge .value {
            stroke: rgb(47, 227, 255);
            stroke-width: 2;
            fill: rgba(0,0,0,0);
            }
            .gauge-container > .gauge .value-text {
            fill: rgb(47, 227, 255);
            font-family: sans-serif;
            font-weight: bold;
            font-size: 0.8em;
            }
            /* ------- Alternate Style ------- */
            .wrapper {
            height: 100px;
            float: left;
            margin: 7px;
            overflow: hidden;
            }
            .wrapper > .gauge-container {
            margin: 0;
            }
            .gauge-container.two {
            }
            .gauge-container.two > .gauge .dial {
            stroke: #334455;
            stroke-width: 10;
            }
            .gauge-container.two > .gauge .value {
            stroke: orange;
            stroke-dasharray: none;
            stroke-width: 13;
            }
            .gauge-container.two > .gauge .value-text {
            fill: #ccc;
            font-weight: 100;
            font-size: 1em;
            }

            /* ----- Alternate Style ----- */
            .gauge-container.five > .gauge .dial {
            stroke: #D3D3D3;
            stroke-width: 15;
            }
            .gauge-container.five > .gauge .value {
            stroke: #F8774B;
            stroke-dasharray: 25 1;
            stroke-width: 15;
            }
            .gauge-container.five > .gauge .value-text {
            fill: transparent;
            font-size: 0.7em;
            }

        </style>
        
        <!-- Navbar -->
        <x-shell.header title="Dive Today" />

        <div class="container-fluid py-0 dh-board">
            {{--
                Trip details, re-skinned with the redesign (companion to the trip
                board and the site page, W2/W3). Same controller data as before:
                $tripDetails, $operator, $location, $boats, $sites, $alreadyInCalendar,
                $myGroups. The add-to-group modal above and its script below are
                unchanged.
            --}}
            @php
                $card = \App\Support\TripBoard::card($tripDetails, now(), [$operator->id => $operator]);
                $when = \Carbon\Carbon::parse($tripDetails->date);
                $isMember = auth()->user() && auth()->user()->isNotGuest();
                $a = $card['availability'];
            @endphp

            <section class="dh-site-facts dh-trip-head">
                <div class="dh-site-facts-main">
                    <p class="dh-trip-kicker">{{ $when->format('l, F j') }} · {{ ucwords($location->location) }}</p>
                    <h1 class="dh-site-title">{{ $tripDetails->tripName }}</h1>
                    <p class="dh-site-sub">
                        <a href="{{ route('OperatorDetails', ['id' => $operator->slug ?? $operator->id]) }}">{{ $operator->operatorName }}</a>
                        @if($card['time24'] !== '00:00') · departs {{ $card['time'] }} {{ $card['meridiem'] }} @else · departure time to be confirmed @endif
                        @if($tripDetails->checkInTime && $tripDetails->checkInTime !== '00:00') · check in {{ $tripDetails->checkInTime }} @endif
                    </p>
                    <div class="dh-site-chips">
                        <span class="dh-avail dh-avail-{{ $a['state'] }}">{{ $a['label'] }}</span>
                        @if($tripDetails->tripPrice)<span class="chip chip-static">${{ $tripDetails->tripPrice }} USD</span>@endif
                        @if($tripDetails->tripType)<span class="chip chip-static">{{ $tripDetails->tripType }}</span>@endif
                        @if($card['level'] !== null)<span class="chip chip-static"><x-dive-level.icon :level="$card['level']" height="16" /> {{ \App\Support\DiveLevel::name($card['level']) }}</span>@endif
                        @if($card['maxDepth'])<span class="chip chip-static">Max {{ $card['maxDepth'] }} ft</span>@endif
                    </div>
                </div>
                <div class="dh-site-facts-side">
                    <div class="dh-site-actions">
                        @if($card['bookUrl'] && $a['state'] !== 'full')
                            <a class="dh-btn dh-btn-primary" href="{{ $card['bookUrl'] }}" target="_blank" rel="noopener">Book this trip</a>
                        @elseif($operator->phone)
                            <a class="dh-btn dh-btn-primary" href="tel:{{ preg_replace('/[^0-9+]/', '', $operator->phone) }}">Call to book</a>
                        @endif
                        @if($isMember)
                            <a class="dh-btn dh-btn-ghost-dark" href="{{ route('AddEventToCalendar', ['tripId' => $tripDetails->id]) }}" title="{{ $alreadyInCalendar ? 'This trip is already in your calendar' : 'Add this trip to your calendar' }}">
                                <span class="material-icons-round">{{ $alreadyInCalendar ? 'event_available' : 'event' }}</span>{{ $alreadyInCalendar ? 'In my calendar' : 'Add to my calendar' }}
                            </a>
                            <button class="dh-btn dh-btn-ghost-dark" type="button" data-bs-toggle="modal" data-bs-target="#modalAddToGroup">
                                <span class="material-icons-round">groups</span>Add to a group
                            </button>
                        @else
                            {{-- Show, then gate (F-04). --}}
                            <a class="dh-btn dh-btn-ghost-dark" href="#" onclick="event.preventDefault();showModalGuest();"><span class="material-icons-round">event</span>Add to my calendar</a>
                        @endif
                    </div>
                    @if($isMember)
                        @php $groupsWithTrip = $myGroups->filter(fn($g) => $g->alreadyAdded); @endphp
                        @if($groupsWithTrip->isNotEmpty())
                            <span class="text-xs text-muted">Already in {{ $groupsWithTrip->pluck('name')->implode(', ') }}</span>
                        @endif
                    @endif
                </div>
            </section>

            <div class="row mx-0 dh-site-columns">
                <div class="col-lg-8 px-0 pe-lg-3">
                    <section class="dh-panel">
                        <h2 class="dh-panel-title">Dive sites on this trip</h2>
                        @if($tripDetails->siteIdStatus == "suggested")
                            <p class="dh-note">The operator did not name the site. These are our best guess from the trip name.</p>
                        @endif
                        @if(count($sites))
                            <div class="dh-site-grid">
                                @foreach($sites as $s)
                                    @php $s->photoFile = isset($s->photos[0]) ? $s->photos[0]->file : null; @endphp
                                    <x-site-card :site="$s" />
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-muted mb-0">No site listed for this trip. As advertised: "{{ $tripDetails->tripName }}".</p>
                        @endif
                    </section>

                    @if($boats != null)
                    <section class="dh-panel">
                        <h2 class="dh-panel-title">{{ count($boats) > 1 ? 'Boats' : 'Boat' }}</h2>
                        @if(count($boats) > 1)
                            <p class="dh-note">Boat not defined for this trip. {{ $operator->operatorName }} runs these boats.</p>
                        @endif
                        <div class="dh-boat-list">
                            @foreach($boats as $boat)
                                <article class="dh-boat">
                                    @if($boat->pic)<span class="dh-boat-img" style="background-image:url('{{ asset('assets') }}{{ $boat->pic }}')"></span>@endif
                                    <div>
                                        <h3 class="dh-boat-name">{{ $boat->name }}</h3>
                                        <dl class="dh-facts-list dh-facts-list-tight">
                                            @if($boat->type)<div><dt>Type</dt><dd>{{ $boat->type }}</dd></div>@endif
                                            @if($boat->capacity)<div><dt>Capacity</dt><dd>{{ $boat->capacity }} divers</dd></div>@endif
                                            @if($boat->tec_capacity)<div><dt>Tech capacity</dt><dd>{{ $boat->tec_capacity }} divers</dd></div>@endif
                                            @if($boat->manufacturer)<div><dt>Builder</dt><dd>{{ $boat->manufacturer }}</dd></div>@endif
                                            @if($boat->length)<div><dt>Length</dt><dd>{{ $boat->length }} ft</dd></div>@endif
                                            @if($boat->beam)<div><dt>Beam</dt><dd>{{ $boat->beam }} ft</dd></div>@endif
                                            @if($boat->speed)<div><dt>Speed</dt><dd>{{ $boat->speed }} kn</dd></div>@endif
                                            @if($boat->power)<div><dt>Power</dt><dd>{{ $boat->power }}</dd></div>@endif
                                        </dl>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                    @endif
                </div>

                <div class="col-lg-4 px-0">
                    <section class="dh-panel">
                        <h2 class="dh-panel-title">Dive center</h2>
                        <div class="dh-operator-card">
                            @if($operator->logoUrl)<img src="{{ asset('assets') }}{{ $operator->logoUrl }}" alt="" class="dh-operator-logo">@endif
                            <a class="dh-operator-name" href="{{ route('OperatorDetails', ['id' => $operator->slug ?? $operator->id]) }}">{{ $operator->operatorName }}</a>
                        </div>
                        <dl class="dh-facts-list dh-facts-list-stack">
                            @php
                                // Stuart Scuba runs from two marinas depending on the site; the rule below is theirs.
                                $marina = $operator->marinaAddress;
                                if ($operator->operatorName == "Stuart Scuba" && count($sites) && !in_array($sites[0]->location, ['STU', 'PSL'])) {
                                    $marina = $operator->marinaAddressAlt ?: $marina;
                                }
                            @endphp
                            @if($operator->streetAddress)<div><dt>Shop</dt><dd>{{ $operator->streetAddress }}, {{ $operator->cityAddress }} {{ $operator->stateAddress }} {{ $operator->zipAddress }}</dd></div>@endif
                            @if($marina)<div><dt>Marina</dt><dd>{{ $marina }}</dd></div>@endif
                            @if($operator->phone)<div><dt>Phone</dt><dd><a href="tel:{{ preg_replace('/[^0-9+]/', '', $operator->phone) }}">{{ $operator->phone }}</a></dd></div>@endif
                            @if($operator->email)<div><dt>Email</dt><dd><a href="mailto:{{ $operator->email }}">{{ $operator->email }}</a></dd></div>@endif
                            @if($operator->webSite)<div><dt>Website</dt><dd><a href="{{ $operator->webSite }}" target="_blank" rel="noopener">{{ parse_url($operator->webSite, PHP_URL_HOST) ?: $operator->webSite }}</a></dd></div>@endif
                            @if($operator->waiverLink)<div><dt>Waiver</dt><dd><a href="{{ $operator->waiverLink }}" target="_blank" rel="noopener">Sign online</a></dd></div>@endif
                        </dl>
                        <h3 class="dh-panel-subtitle">Fills on site</h3>
                        <div class="dh-fills">
                            @foreach(['Air' => $operator->onSiteFillAir, 'Nitrox' => $operator->onSiteFillNitrox, 'Trimix' => $operator->onSiteFillTrimix, 'Oxygen' => $operator->onSiteFillO2] as $gas => $has)
                                <span class="chip chip-static {{ $has ? 'chip-yes' : 'chip-no' }}"><span class="material-icons-round" aria-hidden="true">{{ $has ? 'check' : 'close' }}</span>{{ $gas }}</span>
                            @endforeach
                        </div>
                    </section>
                </div>
            </div>

            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>
    
    
    {{--<x-plugins></x-plugins>--}}
    
    @push('js')
    <script src="{{ asset('assets') }}/js/plugins/jquery-3.6.0.min.js" type="text/javascript"></script>

    <script>
        function selectGroupForTrip(actionUrl, groupName, avatarUrl) {
            document.getElementById('addToGroupForm').action = actionUrl;
            document.getElementById('groupPickerLabel').innerHTML =
                '<img src="' + avatarUrl + '" class="rounded-circle border-info me-2" style="width: 24px; height: 24px; object-fit: cover; border-width: 2px; border-style: solid;">' + groupName;
            document.getElementById('addToGroupSubmitBtn').disabled = false;
        }
    </script>

    @if(count($sites))

    @endif
    @endpush
</x-page-template>

<x-page-template bodyClass='dh-shell bg-gray-200' :SEO="$SEO">

    @php
        $dayNames = [
            'Mon' => 'Monday', 'Tue' => 'Tuesday', 'Wed' => 'Wednesday', 'Thu' => 'Thursday',
            'Fri' => 'Friday', 'Sat' => 'Saturday', 'Sun' => 'Sunday',
        ];

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => $operator->operatorName,
            'url' => $SEO['canonical'] ?? url()->current(),
        ];

        if (!empty($operator->desc)) {
            $desc = trim(strip_tags($operator->desc));
            $jsonLd['description'] = mb_strlen($desc) > 300 ? mb_substr($desc, 0, 297) . '...' : $desc;
        }

        if (!empty($operator->logoUrl)) {
            $jsonLd['image'] = asset('assets') . $operator->logoUrl;
        }

        if (!empty($operator->phone)) {
            $jsonLd['telephone'] = $operator->phone;
        }

        if (!empty($operator->email)) {
            $jsonLd['email'] = $operator->email;
        }

        if (!empty($operator->webSite)) {
            $jsonLd['sameAs'] = $operator->webSite;
        }

        if (!empty($operator->streetAddress) || !empty($operator->cityAddress)) {
            $jsonLd['address'] = array_filter([
                '@type' => 'PostalAddress',
                'streetAddress' => $operator->streetAddress,
                'addressLocality' => $operator->cityAddress,
                'addressRegion' => $operator->stateAddress,
                'postalCode' => $operator->zipAddress,
                'addressCountry' => $operator->coutryAddress,
            ]);
        }

        if (!empty($operator->hourOfOperation)) {
            try {
                $hours = json_decode($operator->hourOfOperation, true);
                $specs = [];
                if (is_array($hours)) {
                    foreach ($hours as $entry) {
                        $day = $dayNames[$entry['day'] ?? ''] ?? null;
                        if (!$day || empty($entry['hours']) || !str_contains($entry['hours'], '-')) {
                            continue;
                        }
                        [$open, $close] = array_map('trim', explode('-', $entry['hours'], 2));
                        if (!is_numeric($open) || !is_numeric($close)) {
                            continue;
                        }
                        $specs[] = [
                            '@type' => 'OpeningHoursSpecification',
                            'dayOfWeek' => 'https://schema.org/' . $day,
                            'opens' => sprintf('%02d:00', (int) $open),
                            'closes' => sprintf('%02d:00', (int) $close),
                        ];
                    }
                }
                if (!empty($specs)) {
                    $jsonLd['openingHoursSpecification'] = $specs;
                }
            } catch (\Throwable $e) {
                // Malformed hourOfOperation data - omit rather than break the page.
            }
        }

        // Note: aggregateRating is intentionally omitted until enough real
        // operator ratings have accumulated to be meaningful.

        $breadcrumbJsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Operators', 'item' => route('Operators')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $operator->operatorName, 'item' => $SEO['canonical'] ?? url()->current()],
            ],
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    <script type="application/ld+json">{!! json_encode($breadcrumbJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>

    <x-shell.nav active="operators" />

    
    <main class="main-content position-relative h-100 border-radius-lg">
        <!-- Navbar -->
        <x-shell.header title="Dive Operators" />
        <!-- End Navbar -->
        <div class="container-fluid py-0">

            {{--modal rating--}}
            <div class="modal fade" id="modalRatingOperator" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabelOperator" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-weight-normal" id="exampleModalLabelOperator">Rate operator <b>{{ $operator->operatorName }}</b></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="myFormOperator" class="multisteps-form__form m-auto" action="{{ route('RateOperator') }}" method="POST" enctype="multipart/form-data">
                        @csrf <!-- Add CSRF token for security -->
                        <input type="hidden" name="operatorId" value="{{ $operator->id }}">
                        <div class="modal-body m-auto">
                            <input type="hidden" id="valueRateOperator" name="rate">
                            <div id="rateOperator"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn bg-gradient-secondary" data-bs-dismiss="modal">Close</button>
                            <button class="btn bg-gradient-info ms-auto" id="submit-all-operator-rating" title="Send" onclick="submitform()">Submit</button> {{---type="submit"----}}

                        </div>
                    </form>
                    </div>
                </div>
            </div>

            <!--modal success rating-->
            @if(session('msg'))
            <div class="modal fade" id="modal-notification" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="modal-notification" aria-hidden="true">
                <div class="modal-dialog modal-danger modal-dialog-centered modal-" role="document">
                    <div class="modal-content">
                        <div class="modal-header text-center">
                            <h6 class="modal-title font-weight-normal" id="modal-title-notification">Notification</h6>

                        </div>
                        <div class="modal-body">
                            <div class="py-3 text-center">
                            <i class="material-icons h1 text-secondary">
                                task_alt
                            </i>
                            <h4 class="text-gradient text-info mt-4">{{ session('msg') }}</h4>
                            <p>Press anywhere outside this dialog to continue</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{--
                Operator detail, re-skinned to match the site and trip pages. Same
                controller data as before ($operator, $boats, $fav, $topSites, $trips,
                $ratedAlready) plus $card from OperatorBoard::card() for the chips.
                The JSON-LD at the top of this file, the two modals above, the
                rating widget and the FullCalendar script below are unchanged.
            --}}
            @php
                $isMember = auth()->user() && auth()->user()->isNotGuest();
                $hoursOfOperation = json_decode((string) $operator->hourOfOperation, true) ?: [];
                $tripPrices = json_decode((string) $operator->tripPrice, true) ?: [];
                $phoneHref = $operator->phone ? 'tel:' . preg_replace('/[^0-9+]/', '', $operator->phone) : null;
            @endphp

            <section class="dh-site-facts">
                <div class="dh-op-head dh-site-facts-main">
                    <span class="dh-op-logo">
                        @if($operator->logoUrl)<img src="{{ asset('assets') }}{{ $operator->logoUrl }}" alt="{{ $operator->operatorName }} logo">@else<span class="material-icons-round">sailing</span>@endif
                    </span>
                    <div>
                        <p class="dh-trip-kicker">{{ $operator->cityAddress }}@if($card['coast'] !== 'Other' && strcasecmp($card['coast'], $operator->cityAddress) !== 0) · {{ $card['coast'] }}@endif</p>
                        <h1 class="dh-site-title">{{ $operator->operatorName }}</h1>
                        <div class="dh-site-chips">
                            @if($card['price'])<span class="chip chip-static" title="{{ $card['priceLabel'] }}">from ${{ $card['price'] }}</span>@endif
                            @if($card['tec'])<span class="chip chip-static chip-tec">Technical trips</span>@endif
                            @if($card['private'])<span class="chip chip-static">Private charters only</span>@endif
                            @if($boats->isNotEmpty())<span class="chip chip-static">{{ $boats->count() }} {{ Str::plural('boat', $boats->count()) }}</span>@endif
                        </div>
                    </div>
                </div>
                <div class="dh-site-facts-side">
                    <div class="dh-site-rating">
                        <div id="rateYoReadOnlyOperator"></div>
                        <span class="text-xs text-muted">{{ $operator->votes ?: 'No' }} {{ Str::plural('rating', (int) $operator->votes) }}</span>
                        @if($isMember)
                            @if(!$ratedAlready)
                                <a href="#" class="text-xs" data-bs-toggle="modal" data-bs-target="#modalRatingOperator">Rate this operator</a>
                            @else
                                <span class="text-xs text-muted">You rated this operator</span>
                            @endif
                        @else
                            {{-- Show, then gate (F-04): the action is visible, the modal explains the account. --}}
                            <a href="#" class="text-xs" onclick="event.preventDefault();showModalGuest();">Rate this operator</a>
                        @endif
                    </div>
                    <div class="dh-site-actions">
                        @if($phoneHref)<a class="dh-btn dh-btn-primary" href="{{ $phoneHref }}"><span class="material-icons-round">call</span>Call</a>@endif
                        @if($operator->webSite)<a class="dh-btn dh-btn-ghost-dark" href="{{ $operator->webSite }}" target="_blank" rel="noopener"><span class="material-icons-round">open_in_new</span>Website</a>@endif
                        @if($isMember)
                            <a class="dh-btn dh-btn-ghost-dark {{ $fav ? 'is-on' : '' }}" href="{{ route('ToggleFav', ['id' => $operator->id]) }}" title="{{ $fav ? 'Remove from my favorite operators' : 'Add to my favorite operators' }}">
                                <span class="material-icons-round">{{ $fav ? 'favorite' : 'favorite_border' }}</span>{{ $fav ? 'Favorite' : 'Save' }}
                            </a>
                        @else
                            <a class="dh-btn dh-btn-ghost-dark" href="#" onclick="event.preventDefault();showModalGuest();"><span class="material-icons-round">favorite_border</span>Save</a>
                        @endif
                    </div>
                </div>
            </section>

            <div class="row mx-0 dh-site-columns">
                <div class="col-lg-8 px-0 pe-lg-3">
                    @if($trips->isNotEmpty())
                    <section class="dh-panel">
                        <h2 class="dh-panel-title">Upcoming trips</h2>
                        <p class="dh-note">Click a day to open the trip board for that date. <span class="chip chip-static">Recreational</span> <span class="chip chip-static chip-yes">Technical</span></p>
                        <div class="calendar" data-bs-toggle="calendar" id="calendar"></div>
                    </section>
                    @endif

                    @if($boats->isNotEmpty())
                    <section class="dh-panel">
                        <h2 class="dh-panel-title">{{ $boats->count() > 1 ? 'Boats' : 'Boat' }}</h2>
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

                    @if($topSites != null && count($topSites))
                    <section class="dh-panel">
                        <h2 class="dh-panel-title">Most visited sites</h2>
                        <p class="dh-note">Ranked by how often {{ $operator->operatorName }} has run trips there.</p>
                        <div class="dh-site-grid">
                            @foreach($topSites as $s)
                                <x-site-card :site="$s" />
                            @endforeach
                        </div>
                    </section>
                    @endif

                    @if($operator->desc)
                    <section class="dh-panel">
                        <h2 class="dh-panel-title">About {{ $operator->operatorName }}</h2>
                        <p class="dh-op-desc">{{ $operator->desc }}</p>
                    </section>
                    @endif
                </div>

                <div class="col-lg-4 px-0">
                    <section class="dh-panel">
                        <h2 class="dh-panel-title">Contact</h2>
                        <div id="map" class="dh-map dh-map-small mb-3" role="application" aria-label="Map of the dive shop"></div>
                        <dl class="dh-facts-list dh-facts-list-stack">
                            @if($operator->streetAddress)<div><dt>Shop</dt><dd>{{ $operator->streetAddress }}, {{ $operator->cityAddress }} {{ $operator->stateAddress }} {{ $operator->zipAddress }}</dd></div>@endif
                            @if($operator->marinaAddress)<div><dt>Marina</dt><dd>{{ $operator->marinaAddress }}</dd></div>@endif
                            @if($operator->marinaAddressAlt)<div><dt>Second marina</dt><dd>{{ $operator->marinaAddressAlt }}</dd></div>@endif
                            @if($operator->phone)<div><dt>Phone</dt><dd><a href="{{ $phoneHref }}">{{ $operator->phone }}</a></dd></div>@endif
                            @if($operator->email)<div><dt>Email</dt><dd><a href="mailto:{{ $operator->email }}">{{ $operator->email }}</a></dd></div>@endif
                            @if($operator->webSite)<div><dt>Website</dt><dd><a href="{{ $operator->webSite }}" target="_blank" rel="noopener">{{ parse_url($operator->webSite, PHP_URL_HOST) ?: $operator->webSite }}</a></dd></div>@endif
                            @if($operator->waiverLink)<div><dt>Waiver</dt><dd><a href="{{ $operator->waiverLink }}" target="_blank" rel="noopener">Sign online</a></dd></div>@endif
                        </dl>
                        @if($hoursOfOperation)
                            <h3 class="dh-panel-subtitle">Hours</h3>
                            <dl class="dh-hours">
                                @foreach($hoursOfOperation as $h)
                                    <div><dt>{{ $h['day'] ?? '' }}</dt><dd>{{ $h['hours'] ?? '' }}</dd></div>
                                @endforeach
                            </dl>
                        @endif
                    </section>

                    @if($tripPrices)
                    <section class="dh-panel">
                        <h2 class="dh-panel-title">Trip prices</h2>
                        <dl class="dh-price-list">
                            @foreach($tripPrices as $p)
                                <div><dt>{{ $p['type'] ?? '' }}</dt><dd>${{ $p['price'] ?? '' }}</dd></div>
                            @endforeach
                        </dl>
                        <p class="text-xs text-muted mb-0 mt-2">USD, as advertised by the operator. Confirm when booking.</p>
                    </section>
                    @endif

                    <section class="dh-panel">
                        <h2 class="dh-panel-title">Fills on site</h2>
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/rateYo/2.3.2/jquery.rateyo.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/rateYo/2.3.2/jquery.rateyo.min.js"></script>
    <script src="https://api.mapbox.com/mapbox-gl-js/v2.6.1/mapbox-gl.js"></script>
    <link href="https://api.mapbox.com/mapbox-gl-js/v2.6.1/mapbox-gl.css" rel="stylesheet" />
    <script src="/assets/js/plugins/fullcalendar.min.js"></script>
    <link href="{{ asset("assets") }}/css/calendar-buttons.css" rel="stylesheet" />

    <script>
        mapboxgl.accessToken = 'pk.eyJ1IjoicHN0cmlrYSIsImEiOiJjbHZsc2p2bXcyY240MmtuMDcydHJzd2UxIn0.KBf79cvk47WseBc9rNu6gQ';
        const address = '{{ $operator->streetAddress}}, {{ $operator->cityAddress}}, {{ $operator->stateAddress}} {{ $operator->zipAddress}}';
        

        fetch(`https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(address)}.json?access_token=${mapboxgl.accessToken}`)
        .then(response => response.json())
        .then(data => {
            const [lng, lat] = data.features[0].center;
            console.log(`Latitude: ${lat}, Longitude: ${lng}`);
            // Now you have the coordinates!
            const map = new mapboxgl.Map({
                container: 'map',
                style: 'mapbox://styles/pstrika/clx0wsurg01yj01qmhmvb9pd6',
                center: [lng, lat], // Set your desired center coordinates
                zoom: 12, // Set your desired zoom level
                projection: 'albers'
            });

            const marker1 = new mapboxgl.Marker({ color: '#0e4d68' })
                .setLngLat([lng, lat])
                .addTo(map);

            const popup = new mapboxgl.Popup().setText("{{ $operator->operatorName }}"); // Set your label text
            marker1.setPopup(popup);

        })
        .catch(error => console.error('Error fetching geocoding data:', error));

        
        
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
        },
        firstDay: {{ (int) (auth()->user()->firstDayOfWeek ?? 0) }}, // guest user has no preference; 0 = Sunday
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
                foreach($trips as $trip) {
                    // fix the ' problem
                    $tripName = str_replace("'", "\\'", $trip->tripName);
                    echo "{";
                    echo "title: '" . (strstr($tripName, '(', true) ? strstr($tripName, '(', true) : $tripName) ."',";
                    echo "start: '" . $trip->date . " " . $trip->departureTime ."',";
                    echo "url: '/TripDetails/" . str($trip->id) . "',";
                    if($trip->tripType == "Technical")
                        echo "className: 'bg-gradient-success text-white tripType=1 isAvail=" . (($trip->tripFreeSpots > 0) ? "Y" : "N")  . "' },";
                    else
                        echo "className: 'bg-gradient-secondary text-white tripType=0 isAvail=" . (($trip->tripFreeSpots > 0) ? "Y" : "N")  . "' },";
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

    <script>
        /* Javascript */

        //Make sure that the dom is ready
        $(function () {
            $("#rateOperator").rateYo({
                precision : 0,
                onSet: function (rating, rateYoInstance) {
                    var rateInput = document.getElementById('valueRateOperator');
                    rateInput.value = rating;
                }
            });
        });

        $(function () {
            $("#rateYoReadOnlyOperator").rateYo({
                rating: {{ $operator->rate != null ? $operator->rate : 0 }},
                readOnly: true
            });
        });
    </script>

    {{---Show modal----}}
    @if(session('msg'))
    <script>
        $(document).ready(function() {
            $('#modal-notification').modal('show'); // Show the modal
        });
    </script>
    @endif

    @endpush
</x-page-template>

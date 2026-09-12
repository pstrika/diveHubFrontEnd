<x-page-template bodyClass='dh-shell bg-gray-200' :SEO="$SEO">
    <x-shell.nav active="" />

    <main class="main-content position-relative h-100 border-radius-lg">
        <x-shell.header title="Beach Diving" icon="beach_access" />

        <style>
            iframe {
                aspect-ratio: 16 / 9;
                height: auto;
                width: 100%;
            }
        </style>

        {{--modal code--}}
        <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-weight-normal">What is this?</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0">Diver's Hub uses weather data from waves (period, direction and height) and winds (direction and speed) to predict dive conditions.</p>
                    </div>
                </div>
            </div>
        </div>

        {{--modal Level--}}
        <div class="modal fade" id="modalLevel" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-weight-normal">Site levels</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <table class="dh-health-table">
                            <thead>
                                <tr><th></th><th>Level</th><th class="text-center">Max Depth (ft)</th></tr>
                            </thead>
                            <tbody>
                                <tr><td><img src="{{ asset('assets') }}/img/icons/icons_level_0.png" alt="OW" height="25"></td><td>Open Water</td><td class="text-center">60</td></tr>
                                <tr><td><img src="{{ asset('assets') }}/img/icons/icons_level_1.png" alt="AOW" height="25"></td><td>Advanced Open Water</td><td class="text-center">130</td></tr>
                                <tr><td><img src="{{ asset('assets') }}/img/icons/icons_level_2.png" alt="Ta" height="25"></td><td>Technical Air</td><td class="text-center">150</td></tr>
                                <tr><td><img src="{{ asset('assets') }}/img/icons/icons_level_3.png" alt="Tn" height="25"></td><td>Technical Normoxic Trimix</td><td class="text-center">200</td></tr>
                                <tr><td><img src="{{ asset('assets') }}/img/icons/icons_level_4.png" alt="Th" height="25"></td><td>Technical Hypoxic Trimix</td><td class="text-center">330+</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{--
            Redesign (2026-09-12, round 2, per feedback): both beaches' verdict
            cards share one row up top (no live-current compass here, that
            stays a Weather-page thing), each with today's general weather
            (Weatherday.conditions_text/_icon - today only, per Pablo) next to
            the dive verdict, and the next tide called out since shore diving
            is usually best around high tide. A "next N days" dive/no-dive
            strip per beach follows - dropped by mistake in the first pass -
            then the collapsible webcam and the site map/list, unchanged.
        --}}
        <div class="container-fluid py-0 dh-wx">
            @php
                // Shore diving's own go/no-go, not the marine-forecast page's Good/
                // Poor/etc text bucket - the two don't always agree on the same
                // conditionsAM_score, which is exactly the discrepancy this page
                // showed in its first pass (headline said "Good", the days table
                // right below it said "No dive" for the same morning). One
                // function, used for the headline, the AM/PM halves and the days
                // table, so they can't disagree again.
                $beachGo = fn ($score) => $score !== null && $score !== '' && (float) $score >= 3.8;
                $ft = fn ($v) => $v === null || $v === '' ? null : round((float) $v, 1);
            @endphp

            <div class="row">
                @foreach($beaches as $beach)
                    @php $today = $beach['today']; @endphp
                    <div class="col-lg-6">
                        <section class="dh-wx-verdict">
                            <div class="dh-wx-head">
                                <div>
                                    <h1 class="dh-wx-place">{{ $beach['label'] }}</h1>
                                    <p class="dh-wx-date">{{ $today ? \Carbon\Carbon::parse($today->date)->format('l j F') : 'No forecast available' }}</p>
                                    @if($today && $today->conditions_text)
                                        <p class="dh-beach-sky">
                                            @if($today->conditions_icon)<img src="{{ $today->conditions_icon }}" alt="" width="22" height="22">@endif
                                            {{ $today->conditions_text }}
                                        </p>
                                    @endif
                                </div>
                                <a class="dh-wx-swap" href="{{ route('Weather') }}/{{ rawurlencode($beach['key']) }}">
                                    <span class="material-icons-round" aria-hidden="true">open_in_new</span>Full forecast
                                </a>
                            </div>

                            @if($today)
                                @php
                                    $goAM = $beachGo($today->conditionsAM_score);
                                    $goPM = $beachGo($today->conditionsPM_score);
                                    if ($goAM && $goPM) {
                                        $dayTone = 'good';
                                        $dayWord = 'Go dive!';
                                    } elseif (!$goAM && !$goPM) {
                                        $dayTone = 'poor';
                                        $dayWord = 'Not a great day for it';
                                    } else {
                                        $dayTone = 'avg';
                                        $dayWord = $goAM ? 'Good in the morning, not the afternoon' : 'Good in the afternoon, not the morning';
                                    }
                                @endphp
                                <div class="dh-wx-callout is-{{ $dayTone }}">
                                    <p class="dh-wx-word">{{ $dayWord }}</p>
                                </div>

                                {{-- The point of this page: when is high tide. --}}
                                @if($beach['nextHigh'] || $beach['tides']->isNotEmpty())
                                    <div class="dh-beach-tides">
                                        @if($beach['nextHigh'])
                                            <p class="dh-beach-next-tide">
                                                <span class="material-icons-round" aria-hidden="true">waves</span>
                                                Next high tide {{ $beach['nextHighIsToday'] ? 'today' : 'tomorrow' }} at <b>{{ $beach['nextHigh']['time']->format('g:i A') }}</b>
                                                <span class="dh-wx-hint">in {{ $beach['nextHighIn'] }}</span>
                                            </p>
                                        @endif
                                        @if($beach['tides']->isNotEmpty())
                                            <div class="dh-beach-tide-row">
                                                @foreach($beach['tides'] as $ti => $t)
                                                    <span class="dh-beach-tide {{ $t['type'] === 'HIGH' ? 'is-high' : 'is-low' }} {{ $ti === $beach['nextTideIndex'] ? 'is-next' : '' }}">
                                                        {{ ucfirst(strtolower($t['type'])) }} <b>{{ $t['time']->format('g:ia') }}</b>
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                <div class="dh-wx-halves">
                                    @foreach([['Morning', $beachGo($today->conditionsAM_score), $today->swell_height_AM, $today->wind_speed_AM, $today->wind_dir_AM],
                                              ['Afternoon', $beachGo($today->conditionsPM_score), $today->swell_height_PM, $today->wind_speed_PM, $today->wind_dir_PM]] as $half)
                                        <div class="dh-wx-half is-{{ $half[1] ? 'good' : 'poor' }}">
                                            <span class="dh-wx-lab">{{ $half[0] }}</span>
                                            <span class="dh-wx-val">{{ $half[1] ? 'Go dive!' : 'No dive' }}</span>
                                            <span class="dh-wx-sub">{{ $ft($half[2]) }} ft &middot; {{ round((float) $half[3]) }} mph {{ $half[4] }}</span>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="dh-wx-facts">
                                    @foreach([['waves', $ft($today->swell_height_AM), 'ft', 'Swell'],
                                              ['timer', $ft($today->swell_period_AM), 's', 'Period'],
                                              ['air', round((float) $today->wind_speed_AM), $today->wind_dir_AM, 'Wind'],
                                              ['thermostat', round((float) $today->water_temp_AM) . '&deg;', '', 'Water']] as $f)
                                        <div class="dh-wx-fact">
                                            <span class="material-icons-round" aria-hidden="true">{{ $f[0] }}</span>
                                            <span class="dh-wx-n">{!! $f[1] !!}<small>{{ $f[2] }}</small></span>
                                            <span class="dh-wx-u">{{ $f[3] }}</span>
                                        </div>
                                    @endforeach
                                </div>

                                <p class="dh-wx-explain">
                                    <a href="#" onclick="event.preventDefault();showModal();">How we score conditions</a>
                                </p>
                            @else
                                <p class="dh-note mb-0">No forecast available for {{ $beach['label'] }} right now.</p>
                            @endif
                        </section>
                    </div>
                @endforeach
            </div>

            {{-- One shared underwater cam (not per-location surface webcams -
                 this is a visibility reference, same feed the pre-redesign page
                 used), right below the top row. --}}
            <details class="dh-wx-more dh-wx-cam-quick dh-beach-below-top">
                <summary><span class="material-icons-round" aria-hidden="true">videocam</span>Underwater camera<span class="dh-wx-hint">Reference for current visibility</span></summary>
                <div class="dh-wx-more-body dh-wx-cam-body">
                    <iframe loading="lazy" src="https://www.youtube.com/embed/mV8zVsX_o_0?autoplay=1&mute=1&controls=0" title="Underwater camera - City of Pompano Beach" frameborder="0" allow="autoplay; accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                    <p class="text-center text-sm mt-2 mb-0"><strong>🎥 City of Pompano Beach</strong></p>
                </div>
            </details>

            {{-- Next few days, both beaches in one table: the question this page
                 exists to answer - which day to go, and where. Same $beachGo
                 cutoff as the headline above, so the two can't disagree. --}}
            @if($beaches[0]['days']->isNotEmpty())
                <section class="dh-panel">
                    <h2 class="dh-panel-title">Next {{ $beaches[0]['days']->count() }} days</h2>
                    <div class="dh-health-table-wrap">
                        <table class="dh-health-table">
                            <thead>
                                <tr>
                                    <th></th>
                                    @foreach($beaches[0]['days'] as $d)
                                        <th class="text-center">{{ \Carbon\Carbon::parse($d->date)->isToday() ? 'Today' : \Carbon\Carbon::parse($d->date)->format('D j') }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($beaches as $beach)
                                    <tr><td colspan="100%" class="dh-health-name">{{ $beach['label'] }}</td></tr>
                                    <tr>
                                        <td>Morning</td>
                                        @foreach($beach['days'] as $d)
                                            <td class="text-center">
                                                @if($beachGo($d->conditionsAM_score))
                                                    <span class="chip chip-yes">Go dive!</span>
                                                @else
                                                    <span class="chip chip-poor">No dive</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td>Afternoon</td>
                                        @foreach($beach['days'] as $d)
                                            <td class="text-center">
                                                @if($beachGo($d->conditionsPM_score))
                                                    <span class="chip chip-yes">Go dive!</span>
                                                @else
                                                    <span class="chip chip-poor">No dive</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif

            @foreach($beaches as $i => $beach)
                <details class="dh-wx-more" id="beachSitesDetails{{ $i }}">
                    <summary><span class="material-icons-round" aria-hidden="true">map</span>{{ $beach['label'] }} dive sites<span class="dh-wx-hint">{{ count($beach['sites']) }} {{ Str::plural('site', count($beach['sites'])) }}</span></summary>
                    <div class="dh-wx-more-body">
                        @if($beach['sites']->isEmpty())
                            <p class="dh-note mb-0">No beach-access sites listed yet for {{ $beach['label'] }}.</p>
                        @else
                            <div class="row">
                                <div class="col-md-5">
                                    <div id="map{{ $i }}" class="dh-beach-map"></div>
                                    <div class="dh-cal-legend">
                                        <span class="dh-cal-legend-item"><span class="dh-cal-legend-swatch" style="background:#0e7c9e"></span>Reef</span>
                                        <span class="dh-cal-legend-item"><span class="dh-cal-legend-swatch" style="background:#b0532a"></span>Wreck</span>
                                        <span class="dh-cal-legend-item"><span class="dh-cal-legend-swatch" style="background:#8a95a1"></span>Other</span>
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <div class="dh-health-table-wrap">
                                        <table class="dh-health-table">
                                            <thead>
                                                <tr>
                                                    <th></th>
                                                    <th>Site name</th>
                                                    <th class="text-center">
                                                        Level
                                                        <button type="button" class="dh-icon-btn" onclick="showModalLevel();" aria-label="What do these levels mean?"><span class="material-icons-round">help_outline</span></button>
                                                    </th>
                                                    <th class="text-center" title="Swimming distance from shore">Distance (ft)</th>
                                                    <th class="text-center">Max depth (ft)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($beach['sites'] as $site)
                                                    <tr>
                                                        <td><x-site-type-icon :type="$site->type" size="32" /></td>
                                                        <td class="dh-health-name"><a href="{{ route('SiteDetails') }}/{{ $site->slug ?? $site->id }}">{{ $site->name }}</a></td>
                                                        <td class="text-center"><img src="{{ asset('assets') }}/img/icons/icons_level_{{ $site->level }}.png" alt="Level {{ $site->level }}" height="25"></td>
                                                        <td class="text-center">{{ $site->distance_from_shore }}</td>
                                                        <td class="text-center">{{ $site->maxDepth }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </details>
            @endforeach

            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>

    @push('js')
    <script src="{{ asset('assets') }}/js/plugins/jquery-3.6.0.min.js" type="text/javascript"></script>

    <script>
        function showModal() {
            $('#modal').modal('show');
        };

        function showModalLevel() {
            $('#modalLevel').modal('show');
        };
    </script>

    <script src="https://api.mapbox.com/mapbox-gl-js/v2.6.1/mapbox-gl.js"></script>
    <link href="https://api.mapbox.com/mapbox-gl-js/v2.6.1/mapbox-gl.css" rel="stylesheet" />

    <script>
        mapboxgl.accessToken = 'pk.eyJ1IjoicHN0cmlrYSIsImEiOiJjbHZsc2p2bXcyY240MmtuMDcydHJzd2UxIn0.KBf79cvk47WseBc9rNu6gQ';

        <?php
            function beachDmsToDd($degrees, $minutes, $direction) {
                $sign = ($direction === 'N' || $direction === 'E') ? 1 : -1;
                return ($degrees + ($minutes * 60) / 3600) * $sign;
            }
        ?>

        @foreach($beaches as $i => $beach)
            @continue($beach['sites']->isEmpty() || !$beach['location'])
            (function () {
                // The map lives inside a collapsed <details> by default (Mapbox
                // sizes its canvas off the container at creation time, which is
                // 0x0 while collapsed) - so don't create it until the panel is
                // actually opened, the first time only.
                var created = false;
                var detailsEl = document.getElementById('beachSitesDetails{{ $i }}');

                function createMap() {
                    if (created) { return; }
                    created = true;

                    var map = new mapboxgl.Map({
                        container: 'map{{ $i }}',
                        style: 'mapbox://styles/pstrika/clwqz4fds03gv01qo9d4w3g21',
                        center: [{{ $beach['location']->centerLon }}, {{ $beach['location']->centerLat }}],
                        zoom: 10.5,
                        projection: 'albers'
                    });
                    map.addControl(new mapboxgl.NavigationControl({ showCompass: false }), 'top-right');

                    map.loadImage('{{ asset('assets') }}/img/icons/marker_reef.png', (error, img) => { if (!error) map.addImage('icon_reef', img); });
                    map.loadImage('{{ asset('assets') }}/img/icons/marker_wreck.png', (error, img) => { if (!error) map.addImage('icon_wreck', img); });
                    map.loadImage('{{ asset('assets') }}/img/icons/marker_other.png', (error, img) => { if (!error) map.addImage('icon_other', img); });

                    var sites = {
                        'type': 'FeatureCollection',
                        'features': [
                            <?php
                                foreach ($beach['sites'] as $site) {
                                    list($lat_deg, $lat_min, $lat_dir) = sscanf($site->gpsLat, "%d° %f' %c");
                                    list($lon_deg, $lon_min, $lon_dir) = sscanf($site->gpsLon, "%d° %f' %c");
                                    $latitude_dd = beachDmsToDd($lat_deg, $lat_min, $lat_dir);
                                    $longitude_dd = beachDmsToDd($lon_deg, $lon_min, $lon_dir);
                                    echo json_encode([
                                        'type' => 'Feature',
                                        'properties' => [
                                            'name' => $site->name,
                                            'icon' => 'icon_' . $site->type,
                                            'url' => url('SiteDetails/' . $site->id),
                                        ],
                                        'geometry' => ['type' => 'Point', 'coordinates' => [$longitude_dd, $latitude_dd]],
                                    ]) . ',';
                                }
                            ?>
                        ]
                    };

                    map.on('load', () => {
                        map.resize();
                        map.addSource('sites', { 'type': 'geojson', 'data': sites });
                        map.addLayer({
                            'id': 'poi-labels',
                            'type': 'symbol',
                            'source': 'sites',
                            'layout': {
                                'text-field': ['get', 'name'],
                                'text-variable-anchor': ['top'],
                                'text-radial-offset': 0.5,
                                'text-justify': 'auto',
                                'text-size': 12,
                                'icon-image': ['get', 'icon'],
                                'icon-size': 0.32,
                                'icon-anchor': 'bottom',
                            },
                            'paint': { 'text-color': 'white' },
                        });
                    });

                    map.on('click', function (e) {
                        var features = map.queryRenderedFeatures(e.point, { layers: ['poi-labels'] });
                        if (!features.length) { return; }
                        window.location.href = features[0].properties.url;
                    });

                    map.on('mousemove', function (e) {
                        var features = map.queryRenderedFeatures(e.point, { layers: ['poi-labels'] });
                        map.getCanvas().style.cursor = features.length ? 'pointer' : '';
                    });
                }

                if (detailsEl.open) { createMap(); }
                detailsEl.addEventListener('toggle', function () {
                    if (detailsEl.open) { createMap(); }
                });
            })();
        @endforeach
    </script>
    @endpush
</x-page-template>

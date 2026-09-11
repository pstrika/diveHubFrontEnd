<x-page-template bodyClass='dh-shell bg-gray-200' :SEO="$SEO">
    <x-shell.nav active="weather" />
    
    
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">

        <!-- Navbar -->
        <x-shell.header title="Marine forecast" />
        <!-- End Navbar -->
        <div class="container-fluid py-4">

            <div class="d-none" data-color="info" id="sidebarColorDiv"></div>
        
        {{--modal code--}}
        <div class="modal fade" id="modal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="modal-notification" aria-hidden="true">
            <div class="modal-dialog modal-danger modal-dialog-centered modal-" role="document">
                <div class="modal-content">
                    <div class="modal-header text-center">
                        <h6 class="modal-title font-weight-normal" id="modal-title-notification">What is this?</h6>
                        
                    </div>
                    <div class="modal-body">
                        <div class="py-3 text-center">
                        <i class="material-icons h1 text-secondary">
                            help_outline
                        </i>
                        <h4 class="text-gradient text-info text-md mt-4">Diver's Hub uses weather data from waves (period, direction and height) and winds (direction and speed) to predict dive conditions.</h4>
                        <p>Press anywhere outside this dialog to continue</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{--
            The forecast, 2026-09-10. Weather is a bottom tab now, so this is the
            page a diver opens the night before a trip and it has one job: should
            I dive tomorrow, and where.

            It used to open on a stock photo of a breaking wave, the location name
            and a map, which is a screen and a half before any information about
            the ocean, and it ran to nine phone screens. The verdict Pablo's
            crawler already computes (conditionsAM_score / conditionsPM_score,
            bucketed Poor, Average, Good, Perfect) was four cells in a table
            halfway down, and swell_period and wind_dir were never shown at all
            even though they are what separates a nice roll from chop.

            Order now: the verdict for today with the four numbers behind it and
            the boats going out, then seven days, then the whole coast, then
            everything else behind a tap. Webcams, charts and the full table are
            all still here, they just do not load on arrival.
        --}}
        @php
            // Shared by the cards below. Conditions text drives the colour
            // everywhere on the site (see components/conditions-pill).
            $tone = function ($text) {
                $t = strtolower(trim((string) $text));
                if (in_array($t, ['perfect', 'good'])) return 'good';
                if ($t === 'average') return 'avg';
                if ($t === '') return 'none';
                return 'poor';
            };
            $ft = fn ($v) => $v === null || $v === '' ? null : round((float) $v, 1);
            // One sentence under the verdict: the numbers, then at most one
            // clause of interpretation.
            //
            // That clause is gated on Pablo's score, deliberately. A first pass
            // branched on swell period alone and contradicted the headline in
            // about one row in six of real data: "Poor diving today" followed by
            // "long period, so it rolls rather than chops" (true, but it was 8 ft
            // and 28 mph), and "Perfect diving today" followed by "short period,
            // so it is chop" (it was 0.4 ft, which is glass, not chop). Period
            // only means chop when there is height and wind behind it.
            //
            // So the verdict decides which clause is even available, and the
            // sentence can never argue with the word above it. When it is bad we
            // name what is wrong rather than describing the sea shape.
            $why = null;
            if ($today) {
                $h = $ft($today->swell_height_AM);
                $pr = $ft($today->swell_period_AM);
                $w = $ft($today->wind_speed_AM);
                $d = $today->wind_dir_AM;
                $t = $tone($today->conditionsAM_text);

                $clause = null;
                if ($t === 'good') {
                    if ($h !== null && $h <= 1.0) {
                        $clause = 'Small and clean.';
                    } elseif ($pr !== null && $pr >= 7) {
                        $clause = 'Long period, so it rolls rather than chops.';
                    }
                } elseif ($t === 'avg') {
                    if ($w !== null && $w >= 15) {
                        $clause = 'The wind is what is holding it back.';
                    } elseif ($pr !== null && $pr < 5 && $h !== null && $h >= 1.5) {
                        $clause = 'Short period for that height, so expect chop.';
                    }
                } elseif ($t === 'poor') {
                    if ($h !== null && $h >= 3) {
                        $clause = 'Too much swell.';
                    } elseif ($w !== null && $w >= 20) {
                        $clause = 'Too much wind.';
                    } else {
                        $clause = 'Not a day for it.';
                    }
                }

                $parts = [];
                if ($h !== null) { $parts[] = $h . ' ft of swell'; }
                if ($pr !== null) { $parts[] = 'a ' . $pr . ' second period'; }
                if ($w !== null) { $parts[] = round($w) . ' mph' . ($d ? ' out of the ' . $d : ''); }
                $why = $parts ? implode(', ', $parts) . '.' : null;
                if ($why && $clause) { $why .= ' ' . $clause; }
            }
        @endphp

        <div class="container-fluid py-0 dh-wx">

            {{-- 1. The verdict. --}}
            @if($today)
            <section class="dh-wx-verdict">
                <div class="dh-wx-head">
                    <div>
                        <h1 class="dh-wx-place">{{ ucwords($location) }}</h1>
                        <p class="dh-wx-date">{{ \Carbon\Carbon::parse($today->date)->format('l j F') }}</p>
                    </div>
                    <a class="dh-wx-swap" href="#dh-wx-coast">
                        <span class="material-icons-round" aria-hidden="true">place</span>Change
                    </a>
                </div>

                @php $dayTone = $tone($today->conditionsAM_text); @endphp
                {{-- Live current reading (from the old front end, Miami and Fort
                     Lauderdale only - the two locations with a NOAA current buoy,
                     weatherlocations.buoy) sits right beside "Good diving today",
                     same row, per Pablo's spec (2026-09-11). --}}
                <div class="dh-wx-callout-row {{ $currentLocation && $currentLocation->buoy ? 'has-current' : '' }}">
                    <div class="dh-wx-callout is-{{ $dayTone }}">
                        <p class="dh-wx-word">{{ ucfirst(strtolower($today->conditionsAM_text ?: 'No forecast')) }} diving today</p>
                        @if($why)<p class="dh-wx-why">{{ $why }}</p>@endif
                    </div>

                    @if($currentLocation && $currentLocation->buoy)
                        <div class="dh-wx-current">
                            <span class="dh-wx-current-label">Current now</span>
                            <div class="dh-wx-compass">
                                <svg viewBox="0 0 100 100" class="dh-wx-compass-face" aria-hidden="true">
                                    <circle cx="50" cy="50" r="44" />
                                    <text x="50" y="17" class="dh-wx-compass-n">N</text>
                                </svg>
                                <svg viewBox="0 0 100 100" class="dh-wx-compass-needle" style="transform: rotate({{ (float) $currentLocation->dir }}deg);" aria-hidden="true">
                                    <polygon points="50,14 59,50 50,50 41,50" class="dh-wx-compass-tip" />
                                    <polygon points="50,86 59,50 50,50 41,50" class="dh-wx-compass-tail" />
                                    <circle cx="50" cy="50" r="4" class="dh-wx-compass-hub" />
                                </svg>
                            </div>
                            <span class="dh-wx-current-speed">{{ rtrim(rtrim(number_format((float) $currentLocation->speed, 2), '0'), '.') }} kts</span>
                        </div>
                    @endif
                </div>

                <div class="dh-wx-halves">
                    @foreach([['Morning', $today->conditionsAM_text, $today->swell_height_AM, $today->wind_speed_AM, $today->wind_dir_AM],
                              ['Afternoon', $today->conditionsPM_text, $today->swell_height_PM, $today->wind_speed_PM, $today->wind_dir_PM]] as $half)
                        <div class="dh-wx-half is-{{ $tone($half[1]) }}">
                            <span class="dh-wx-lab">{{ $half[0] }}</span>
                            <span class="dh-wx-val">{{ $half[1] ?: 'No forecast' }}</span>
                            <span class="dh-wx-sub">{{ $ft($half[2]) }} ft &middot; {{ round((float) $half[3]) }} mph {{ $half[4] }}</span>
                        </div>
                    @endforeach
                </div>

                {{-- The four numbers that actually move the decision. --}}
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

                @php $boatsToday = $boatsByDate[$today->date] ?? 0; @endphp
                <a class="dh-wx-boats" href="{{ route('Trips') }}/{{ $today->date }}">
                    <span class="material-icons-round" aria-hidden="true">sailing</span>
                    @if($boatsToday > 0)
                        {{ $boatsToday }} {{ Str::plural('boat', $boatsToday) }} going out from {{ ucwords($location) }} today
                    @else
                        See every boat going out today
                    @endif
                    <span class="material-icons-round dh-wx-arrow" aria-hidden="true">chevron_right</span>
                </a>

                <p class="dh-wx-explain">
                    <a href="#" onclick="event.preventDefault();showModal();">How we score conditions</a>
                </p>
            </section>
            @endif

            {{-- 1b. Live webcam for this location, collapsed by default so it
                 never distracts from the verdict above it - same tap-to-open
                 card as everything else below. Moved up from the bottom of
                 the page (2026-09-11); it used to be buried behind "Live
                 webcams" down with the charts, where almost nobody found it. --}}
            @php
                $webcamSrc = match ($location) {
                    'fort lauderdale' => 'https://www.youtube.com/embed/pXx3YQVSUGg?autoplay=1&mute=1',
                    'pompano beach' => 'https://www.youtube.com/embed/zclpD3QKEK4?autoplay=1&mute=1',
                    'west palm beach' => 'https://video-monitoring.com/beachcams/palmbeachmarriott/stream.htm',
                    'jupiter' => 'https://www.youtube.com/embed/4y7kDbwBuh0?autoplay=1&mute=1',
                    'miami beach' => 'https://www.youtube.com/embed/bi7B4EmyHHs?autoplay=1&mute=1',
                    'key west' => 'https://www.youtube.com/embed/sDXwrYQPeOQ?autoplay=1&mute=1',
                    default => null,
                };
            @endphp
            @if($webcamSrc)
            <details class="dh-wx-more dh-wx-cam-quick">
                <summary><span class="material-icons-round" aria-hidden="true">videocam</span>Live webcam<span class="dh-wx-hint">{{ ucwords($location) }}</span></summary>
                <div class="dh-wx-more-body dh-wx-cam-body">
                    <iframe loading="lazy" src="{{ $webcamSrc }}" title="Live webcam - {{ ucwords($location) }}" frameborder="0" allow="autoplay; accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                </div>
            </details>
            @endif

            {{-- 2. Seven days: the column a diver scans for "which day". --}}
            @if($days->isNotEmpty())
            <section class="dh-wx-card">
                <h2 class="dh-wx-ch">Next {{ $days->count() }} days <span class="dh-wx-hint">morning / afternoon</span></h2>
                @foreach($days as $d)
                    @php
                        $dc = \Carbon\Carbon::parse($d->date);
                        $isToday = $dc->isToday();
                        $boats = $boatsByDate[$d->date] ?? 0;
                    @endphp
                    <a class="dh-wx-day {{ $isToday ? 'is-today' : '' }}" href="{{ route('Trips') }}/{{ $d->date }}">
                        <span class="dh-wx-dday">
                            <b>{{ $isToday ? 'Today' : $dc->format('D') }}</b>
                            <span>{{ $dc->format('M j') }}</span>
                        </span>
                        <span class="dh-wx-bars">
                            <span class="dh-wx-bar is-{{ $tone($d->conditionsAM_text) }}">{{ $d->conditionsAM_text ?: '?' }}<small>AM</small></span>
                            <span class="dh-wx-bar is-{{ $tone($d->conditionsPM_text) }}">{{ $d->conditionsPM_text ?: '?' }}<small>PM</small></span>
                        </span>
                        <span class="dh-wx-dsea">
                            <b>{{ $ft($d->swell_height_AM) }} ft</b> {{ $ft($d->swell_period_AM) }}s<br>
                            {{ round((float) $d->wind_speed_AM) }} {{ $d->wind_dir_AM }}
                            @if($boats > 0)<br><span class="dh-wx-dboats">{{ $boats }} {{ Str::plural('boat', $boats) }}</span>@endif
                        </span>
                    </a>
                @endforeach
            </section>
            @endif

            {{-- 3. The whole coast today. This replaces the map as the picker and
                 answers the better question: not "show me Boynton" but "where is
                 it good". --}}
            @if($coastToday->isNotEmpty())
            <section class="dh-wx-card" id="dh-wx-coast">
                <h2 class="dh-wx-ch">
                    <span class="dh-wx-ch-title">Along the coast today <span class="dh-wx-hint">tap to switch</span></span>
                    <a class="dh-wx-sort" href="{{ route('Weather') }}/{{ rawurlencode($location) }}?coastSort={{ $coastSort === 'ntos' ? 'ston' : 'ntos' }}#dh-wx-coast">
                        <span class="material-icons-round" aria-hidden="true">swap_vert</span>{{ $coastSort === 'ntos' ? 'N to S' : 'S to N' }}
                    </a>
                </h2>
                @foreach($coastToday as $i => $c)
                    @if($coastFavCount > 0 && $i === 0)
                        <p class="dh-wx-clabel">Your favorites</p>
                    @elseif($coastFavCount > 0 && $i === $coastFavCount)
                        <p class="dh-wx-clabel">All other locations</p>
                    @endif
                    <a class="dh-wx-crow {{ $c->location === $location ? 'is-here' : '' }}" href="{{ route('Weather') }}/{{ rawurlencode($c->location) }}">
                        <span class="dh-wx-cname">{{ ucwords($c->location) }}</span>
                        <span class="dh-pill dh-pill-{{ $tone($c->conditionsAM_text) }}">{{ $c->conditionsAM_text }}</span>
                        <span class="dh-wx-cnum"><b>{{ $ft($c->swell_height_AM) }}</b> ft {{ $ft($c->swell_period_AM) }}s</span>
                    </a>
                @endforeach
            </section>
            @endif

            {{-- 4. Everything else, one tap away. Pablo's original Florida map
                 is back - Florida1.png has each city's name baked into the
                 image itself along the coastline, so the pins here have to
                 land ON that coastline, next to the matching text, not just
                 anywhere plausible. Re-measured against the actual current
                 image (2026-09-11) by scanning it for the coastline's real
                 pixel position at each label's height, since the original
                 pin coordinates (from a differently-cropped/labeled version
                 of this map, one release before this one) had drifted off
                 the text entirely once rendered against this file. Values
                 below are in the *displayed* 200px-tall image's own pixel
                 space; the +24/+16 added at render time is the container's
                 own padding (see .dh-wx-florida), not part of the position. --}}
            @php
                $floridaPins = [
                    'port st lucie'    => ['top' => 15,  'left' => 164],
                    'stuart'           => ['top' => 28,  'left' => 169],
                    'jupiter'          => ['top' => 43,  'left' => 174],
                    'west palm beach'  => ['top' => 62,  'left' => 177],
                    'boynton beach'    => ['top' => 77,  'left' => 175],
                    'pompano beach'    => ['top' => 92,  'left' => 173],
                    'fort lauderdale'  => ['top' => 104, 'left' => 172],
                    'miami beach'      => ['top' => 117, 'left' => 171],
                    'key largo'        => ['top' => 164, 'left' => 151],
                    'islamorada'       => ['top' => 188, 'left' => 107],
                    'key west'         => ['top' => 195, 'left' => 88],
                ];
            @endphp
            <details class="dh-wx-more">
                <summary><span class="material-icons-round" aria-hidden="true">public</span>All locations<span class="dh-wx-hint">tap a pin to switch</span></summary>
                <div class="dh-wx-more-body">
                    <div class="dh-wx-florida">
                        <img src="{{ asset('assets') }}/img/Florida1.png" alt="Map of Florida" class="dh-wx-florida-img">
                        @foreach($floridaPins as $pinLoc => $pos)
                            <a href="{{ route('Weather') }}/{{ rawurlencode($pinLoc) }}"
                               class="dh-wx-florida-pin {{ $location === $pinLoc ? 'is-here' : '' }}"
                               style="top: {{ $pos['top'] + 24 }}px; left: {{ $pos['left'] + 16 }}px;"
                               title="{{ ucwords($pinLoc) }}" aria-label="{{ ucwords($pinLoc) }}"></a>
                        @endforeach
                    </div>
                </div>
            </details>

            @if($today && $today->tides)
            <details class="dh-wx-more">
                <summary><span class="material-icons-round" aria-hidden="true">waterfall_chart</span>Tides and daylight<span class="dh-wx-hint">{{ ucwords($location) }}</span></summary>
                <div class="dh-wx-more-body">
                    @php $tides = json_decode($today->tides, true) ?: []; @endphp
                    <ul class="dh-wx-tides">
                        @foreach($tides as $t)
                            <li><span>{{ ucfirst(strtolower($t['tide_type'] ?? '')) }}</span>
                                <b>{{ isset($t['tide_time']) ? \Carbon\Carbon::parse($t['tide_time'])->format('H:i') : '' }}</b></li>
                        @endforeach
                        <li><span>Sunrise and sunset</span><b>{{ $today->sunrise }} &middot; {{ $today->sunset }}</b></li>
                    </ul>
                </div>
            </details>
            @endif

            <details class="dh-wx-more">
                <summary><span class="material-icons-round" aria-hidden="true">show_chart</span>Charts and the full table<span class="dh-wx-hint">waves, wind, air, humidity, rain</span></summary>
                <div class="dh-wx-more-body">

                <div class="dh-wx-charts">
                    <section class="dh-card dh-wx-chart-card">
                        <h2 class="dh-card-head">Waves (ft)</h2>
                        <div class="dh-card-body">
                            <canvas id="wavesChart" height="160"></canvas>
                            <p class="dh-wx-update"><span class="material-icons-round" aria-hidden="true">schedule</span>Last update: {{ $weathers[0]->_dateAdded }}</p>
                        </div>
                    </section>

                    <section class="dh-card dh-wx-chart-card">
                        <h2 class="dh-card-head">Winds (mph)</h2>
                        <div class="dh-card-body">
                            <canvas id="windChart" height="160"></canvas>
                            <p class="dh-wx-update"><span class="material-icons-round" aria-hidden="true">schedule</span>Last update: {{ $weathers[0]->_dateAdded }}</p>
                        </div>
                    </section>
                </div>

                <section class="dh-card dh-wx-table-card">
                    <h2 class="dh-card-head">Extended forecast</h2>
                    <div class="dh-card-body dh-wx-table-wrap">
                        @php
                            // Real <table> with a genuine AM/PM sub-header for the Marine
                            // rows (2026-09-11) - the previous "AM 2 PM 2" crammed into one
                            // cell per day was hard to scan. Weather/Sunrise rows have no
                            // AM/PM split, so they span both sub-columns with colspan.
                            $dayCols = $weathers->count();
                            $fullSpan = 1 + $dayCols * 2;
                            $tideRows = $weathers->map(function ($weather) {
                                $high = ['AM' => 'none', 'PM' => 'none'];
                                $low = ['AM' => 'none', 'PM' => 'none'];
                                $jsonString = (string) $weather->tides;
                                if (strlen($jsonString) >= 5) {
                                    foreach (json_decode($jsonString, true) ?: [] as $tide) {
                                        $suffix = date('A', strtotime($tide['tide_time']));
                                        $value = substr($tide['tide_time'], -5);
                                        if (($tide['tide_type'] ?? null) === 'HIGH') {
                                            $high[$suffix] = $value;
                                        } elseif (($tide['tide_type'] ?? null) === 'LOW') {
                                            $low[$suffix] = $value;
                                        }
                                    }
                                } else {
                                    $high = ['AM' => 'NA', 'PM' => 'NA'];
                                    $low = ['AM' => 'NA', 'PM' => 'NA'];
                                }
                                return ['high' => $high, 'low' => $low];
                            });
                            $compassPoints = [
                                'N' => 0, 'NNE' => 22, 'NE' => 45, 'ENE' => 67, 'E' => 90, 'ESE' => 112,
                                'SE' => 135, 'SSE' => 157, 'S' => 180, 'SSW' => 202, 'SW' => 225,
                                'WSW' => 247, 'W' => 270, 'WNW' => 292, 'NW' => 315, 'NNW' => 337,
                            ];
                        @endphp
                        <table class="dh-wx-table">
                            <thead>
                                <tr>
                                    <th></th>
                                    @foreach($weathers as $weather)
                                        <th colspan="2">{{ (new DateTime($weather->date))->format('l-d') }}</th>
                                    @endforeach
                                </tr>
                                <tr class="dh-wx-subhead">
                                    <th></th>
                                    @foreach($weathers as $weather)
                                        <th>AM</th><th>PM</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="dh-wx-tsection-row"><td colspan="{{ $fullSpan }}">Weather</td></tr>

                                <tr>
                                    <td class="dh-wx-tlabel"></td>
                                    @foreach($weathers as $weather)
                                        <td colspan="2"><img src="{{ $weather->conditions_icon }}" alt="{{ $weather->conditions_text }}" width="28" height="28"></td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td class="dh-wx-tlabel"></td>
                                    @foreach($weathers as $weather)
                                        <td colspan="2">{{ $weather->conditions_text }}</td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td class="dh-wx-tlabel">Temp (f)</td>
                                    @foreach($weathers as $weather)
                                        <td colspan="2"><b>{{ round($weather->mintemp_f) }}&deg; - {{ round($weather->maxtemp_f) }}&deg;</b></td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td class="dh-wx-tlabel">Humidity (%)</td>
                                    @foreach($weathers as $weather)
                                        <td colspan="2"><b>{{ $weather->avghumidity }}%</b></td>
                                    @endforeach
                                </tr>

                                <tr class="dh-wx-tsection-row"><td colspan="{{ $fullSpan }}">Marine</td></tr>

                                <tr>
                                    <td class="dh-wx-tlabel">Waves (ft)</td>
                                    @foreach($weathers as $weather)
                                        <td>{{ round($weather->swell_height_AM) }}</td>
                                        <td>{{ round($weather->swell_height_PM) }}</td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td class="dh-wx-tlabel">Period (secs)</td>
                                    @foreach($weathers as $weather)
                                        <td>{{ round($weather->swell_period_AM) }}</td>
                                        <td>{{ round($weather->swell_period_PM) }}</td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td class="dh-wx-tlabel">Wind (mph)</td>
                                    @foreach($weathers as $weather)
                                        @php
                                            $rotationAM = $compassPoints[$weather->wind_dir_AM] ?? 0;
                                            $rotationPM = $compassPoints[$weather->wind_dir_PM] ?? 0;
                                        @endphp
                                        <td><i class="material-icons-round rotate-icon" style="transform: rotate({{ $rotationAM + 180 }}deg);">navigation</i>{{ round($weather->wind_speed_AM) }}</td>
                                        <td><i class="material-icons-round rotate-icon" style="transform: rotate({{ $rotationPM + 180 }}deg);">navigation</i>{{ round($weather->wind_speed_PM) }}</td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td class="dh-wx-tlabel">Water temp (f)</td>
                                    @foreach($weathers as $weather)
                                        <td>{{ round($weather->water_temp_AM) }}&deg;</td>
                                        <td>{{ round($weather->water_temp_PM) }}&deg;</td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td class="dh-wx-tlabel">High tide</td>
                                    @foreach($tideRows as $t)
                                        <td>{{ $t['high']['AM'] }}</td>
                                        <td>{{ $t['high']['PM'] }}</td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td class="dh-wx-tlabel">Low tide</td>
                                    @foreach($tideRows as $t)
                                        <td>{{ $t['low']['AM'] }}</td>
                                        <td>{{ $t['low']['PM'] }}</td>
                                    @endforeach
                                </tr>

                                <tr class="dh-wx-tsection-row"><td colspan="{{ $fullSpan }}">Sunrise / sunset</td></tr>

                                <tr>
                                    <td class="dh-wx-tlabel">Sunrise</td>
                                    @foreach($weathers as $weather)
                                        <td colspan="2">{{ DateTime::createFromFormat('h:i A', $weather->sunrise)?->format('H:i') }}</td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td class="dh-wx-tlabel">Sunset</td>
                                    @foreach($weathers as $weather)
                                        <td colspan="2">{{ DateTime::createFromFormat('h:i A', $weather->sunset)?->format('H:i') }}</td>
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                </div>
            </details>

        </div>

            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>
    
    
    {{--<x-plugins></x-plugins>--}}
    
    @push('js')

    <script src="../../assets/js/plugins/chartjs.min.js"></script>
    <script src="{{ asset('assets') }}/js/plugins/jquery-3.6.0.min.js" type="text/javascript"></script>
    
    <script>
        function showModal() {
            $('#modal').modal('show'); // Show the modal
        };
    </script>

    <script>
        var ctx = document.getElementById('wavesChart').getContext('2d');
        var wavesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [
                    @foreach($weathers as $weather)
                        @php
                            $date = new DateTime($weather->date);
                            $dateDayName = $date->format('D-d');
                        
                            echo "'" . $dateDayName . "',";
                        @endphp
                    @endforeach
                ],
                datasets: [{
                    label: 'waves',
                    data: [
                        @foreach($weathers as $weather)
                            @php
                                
                                if ( (int)$weather->swell_height_AM > (int)$weather->swell_height_PM)
                                    echo "'" . $weather->swell_height_AM . "',";
                                else
                                    echo "'" . $weather->swell_height_PM . "',";
                            @endphp
                        @endforeach    
                    ],
                    tension: 0,
                    borderWidth: 0,
                    pointRadius: 4,
                    pointBackgroundColor: "#0e7c9e",
                    pointBorderColor: "transparent",
                    borderColor: "#0e7c9e",
                    backgroundColor: "rgba(14, 124, 158, .18)",
                    fill: true,
                    yAxisID: 'ywaves',
                    maxBarThickness: 20,
                    },
                ]

            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    }
                },
                scales: {
                    ywaves: {
                        beginAtZero: true,
                        type: 'linear',
                        display: 'true',
                        position: 'left',
                        title: {
                            text: 'waves height (ft)',
                            display: false,
                        },
                        grid: {
                            drawBorder: false,
                            display: true,
                            drawOnChartArea: true,
                            drawTicks: false,
                            borderDash: [5, 5],
                            color: 'rgba(11, 42, 58, .08)',
                        },
                        ticks: {
                            display: true,
                            padding: 10,
                            color: '#5a6b78',
                            font: {
                                size: 12,
                                weight: 500,
                                family: "Roboto",
                                style: 'normal',
                                lineHeight: 2
                            },
                        }

                    },

                    x: {
                        grid: {
                            drawBorder: false,
                            display: false,
                            drawOnChartArea: false,
                            drawTicks: false,
                            borderDash: [5, 5]
                        },
                        ticks: {
                            display: true,
                            color: '#5a6b78',
                            padding: 10,
                            font: {
                                size: 12,
                                weight: 500,
                                family: "Roboto",
                                style: 'normal',
                                lineHeight: 2
                            },
                        }
                    },


                }
            }
        });
    </script>

<script>
        var ctx = document.getElementById('windChart').getContext('2d');
        var windChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [
                    @foreach($weathers as $weather)
                        @php
                            $date = new DateTime($weather->date);
                            $dateDayName = $date->format('D-d');
                        
                            echo "'" . $dateDayName . "',";
                        @endphp
                    @endforeach
                ],
                datasets: [{
                    label: 'winds',
                    data: [
                        @foreach($weathers as $weather)
                            {{ $weather->maxwind_mph }},
                        @endforeach
                    ],
                    tension: 0,
                    borderWidth: 0,
                    pointRadius: 4,
                    pointBackgroundColor: "#0e7c9e",
                    pointBorderColor: "transparent",
                    borderColor: "#0e7c9e",
                    backgroundColor: "rgba(14, 124, 158, .18)",
                    fill: true,
                    yAxisID: 'ywaves',
                    maxBarThickness: 20,
                    },
                ]

            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    }
                },
                scales: {
                    ywaves: {
                        beginAtZero: true,
                        type: 'linear',
                        display: 'true',
                        position: 'left',
                        title: {
                            text: 'waves height (ft)',
                            display: false,
                        },
                        grid: {
                            drawBorder: false,
                            display: true,
                            drawOnChartArea: true,
                            drawTicks: false,
                            borderDash: [5, 5],
                            color: 'rgba(11, 42, 58, .08)',
                        },
                        ticks: {
                            display: true,
                            padding: 10,
                            color: '#5a6b78',
                            font: {
                                size: 12,
                                weight: 500,
                                family: "Roboto",
                                style: 'normal',
                                lineHeight: 2
                            },
                        }

                    },

                    x: {
                        grid: {
                            drawBorder: false,
                            display: false,
                            drawOnChartArea: false,
                            drawTicks: false,
                            borderDash: [5, 5]
                        },
                        ticks: {
                            display: true,
                            color: '#5a6b78',
                            padding: 10,
                            font: {
                                size: 12,
                                weight: 500,
                                family: "Roboto",
                                style: 'normal',
                                lineHeight: 2
                            },
                        }
                    },


                }
            }
        });
    </script>


    @endpush
</x-page-template>

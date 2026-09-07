<x-page-template bodyClass='dh-shell bg-gray-200' :SEO="$SEO">
    <x-shell.nav active="operators" />

    <main class="main-content position-relative h-100 border-radius-lg">
        <x-shell.header title="Dive Operators" />

        <div class="container-fluid py-0 dh-board">
            {{--
                Dive Operators explorer. Same shape as the Dive Sites explorer so the
                two read as one product: search box, filter chips, sort chips, a
                list or map view, all driven by the query string. The work happens
                in App\Support\OperatorBoard so the controller stays small and the
                same data can feed a JSON endpoint later.

                The public URL (/Operators), title, description and canonical are
                unchanged from the table version.
            --}}

            <header class="dh-explorer-head">
                <div>
                    <h1 class="dh-explorer-title">Dive operators in South Florida</h1>
                    <p class="dh-explorer-intro">Every charter and dive center from Stuart to Key West, with prices, boats, gas fills and who runs technical trips.</p>
                </div>
                <form class="dh-omnibox" method="GET" action="{{ url()->current() }}" role="search">
                    @foreach(request()->except(['q', 'page']) as $k => $v)
                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                    @endforeach
                    <span class="material-icons-round" aria-hidden="true">search</span>
                    <input type="search" name="q" value="{{ $board['filters']['q'] }}" placeholder="Search operators, cities, boats" aria-label="Search dive operators">
                    @if($board['filters']['q'] !== '')
                        <a class="dh-omnibox-clear" href="{{ url()->current() }}?{{ http_build_query(request()->except(['q','page'])) }}" aria-label="Clear search">&times;</a>
                    @endif
                </form>
            </header>

            <div class="dh-filters">
                @php
                    $regionOptions = collect(\App\Support\Coast::chipOptions())->map(fn ($label, $key) => $label . ' (' . ($board['regionCounts'][$key] ?? 0) . ')')->all();
                    $featureOptions = collect($board['featureCounts'])->map(fn ($c, $key) => \App\Support\OperatorBoard::FEATURES[$key]['label'] . ' (' . $c . ')')->all();
                @endphp
                <x-query-chips param="region" :options="$regionOptions" :selected="$board['filters']['region']" all="All coasts" label="Coast" />
                @if($featureOptions)
                    <x-query-chips param="has" :options="$featureOptions" :selected="$board['filters']['has']" all="" label="Offers" :toggle="true" />
                @endif
                <div class="d-flex flex-wrap align-items-center gap-3">
                    <x-sort-chips :options="\App\Support\OperatorBoard::SORTS" :selected="$board['filters']['sort']" />
                    <x-query-chips param="view" :options="['map' => 'Map']" :selected="$board['filters']['view'] === 'map' ? 'map' : null" all="List" label="" />
                </div>
            </div>

            <p class="dh-board-count">
                {{ count($board['cards']) }} of {{ $board['total'] }} {{ Str::plural('operator', $board['total']) }}
                @if($board['filters']['q'] !== '' || $board['filters']['region'] || $board['filters']['has'])
                    <a href="{{ url()->current() }}">clear filters</a>
                @endif
            </p>

            @if($board['filters']['view'] === 'map')
                {{-- Map view. Operators have no stored coordinates, so the browser geocodes each
                     shop address through Mapbox when this view is opened (as the old page did on
                     every visit). A lat/lon pair on the operators table would remove those calls. --}}
                <div id="map" class="dh-map" role="application" aria-label="Map of dive operators"></div>
            @elseif(empty($board['cards']))
                <div class="dh-empty">
                    <span class="material-icons-round" aria-hidden="true">sailing</span>
                    <p>No operators match. Try fewer filters or another spelling.</p>
                    <a class="dh-btn dh-btn-primary" href="{{ url()->current() }}">Show all</a>
                </div>
            @else
                <div class="dh-op-grid">
                    @foreach($board['cards'] as $card)
                        <x-operator-card :card="$card" />
                    @endforeach
                </div>
            @endif

            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>

    @if($board['filters']['view'] === 'map')
    @push('js')
    <script src="https://api.mapbox.com/mapbox-gl-js/v2.6.1/mapbox-gl.js"></script>
    <link href="https://api.mapbox.com/mapbox-gl-js/v2.6.1/mapbox-gl.css" rel="stylesheet" />
    <script>
        mapboxgl.accessToken = @json($mapboxToken);
        @php $pins = array_map(fn ($c) => ['name' => $c['name'], 'address' => $c['address'], 'url' => $c['url']], $board['cards']); @endphp
        const operators = @json($pins);
        const map = new mapboxgl.Map({
            container: 'map',
            style: 'mapbox://styles/pstrika/clx0wsurg01yj01qmhmvb9pd6',
            center: [-80.9248, 25.9379],
            zoom: 6.7,
            projection: 'albers'
        });
        const bounds = new mapboxgl.LngLatBounds();
        let placed = 0;
        operators.forEach(op => {
            fetch(`https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(op.address)}.json?access_token=${mapboxgl.accessToken}&limit=1`)
                .then(r => r.json())
                .then(data => {
                    if (!data.features || !data.features.length) return;
                    const [lng, lat] = data.features[0].center;
                    const popup = new mapboxgl.Popup({ offset: 24 }).setHTML(`<a href="${op.url}">${op.name}</a>`);
                    new mapboxgl.Marker({ color: '#0e4d68' }).setLngLat([lng, lat]).setPopup(popup).addTo(map);
                    bounds.extend([lng, lat]);
                    if (++placed > 1) map.fitBounds(bounds, { padding: 40, maxZoom: 11 });
                })
                .catch(() => {});
        });
    </script>
    @endpush
    @endif
</x-page-template>

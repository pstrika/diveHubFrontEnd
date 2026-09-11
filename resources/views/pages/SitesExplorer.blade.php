<x-page-template bodyClass='dh-shell bg-gray-200' :SEO="$SEO">
    <x-shell.nav active="sites" />

    <main class="main-content position-relative h-100 border-radius-lg">
        <x-shell.header :title="$explorer['heading']" />

        <div class="container-fluid py-0 dh-board">
            {{--
                Dive Sites explorer (proposal W4). One view serves two indexed URLs:
                /DiveSites (all sites, Top Rated intent, default sort by rating) and
                /WreckSites (wreckWiki, the wreck collection). $explorer carries the
                heading and which chips are fixed. Sites Map and Search redirect here.

                Search, type, level, sort and the list/map toggle are all query
                parameters handled in SiteController::explorer(), so any view of
                the catalog is a shareable URL and works without JavaScript. The
                map is the one enhancement: Mapbox draws the same filtered set.
            --}}

            <header class="dh-explorer-head">
                <div>
                    <h1 class="dh-explorer-title">{{ $explorer['heading'] }}</h1>
                    @if(!empty($explorer['intro']))<p class="dh-explorer-intro">{{ $explorer['intro'] }}</p>@endif
                </div>
                <form class="dh-omnibox" method="GET" action="{{ url()->current() }}" role="search">
                    @foreach(request()->except(['q', 'page']) as $k => $v)
                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                    @endforeach
                    <span class="material-icons-round" aria-hidden="true">search</span>
                    <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Search sites, wrecks, reefs" aria-label="Search dive sites">
                    @if($filters['q'] !== '')
                        <a class="dh-omnibox-clear" href="{{ url()->current() }}?{{ http_build_query(request()->except(['q','page'])) }}" aria-label="Clear search">&times;</a>
                    @endif
                </form>
            </header>

            <div class="dh-filters">
                @if(!$explorer['fixedType'])
                    <x-query-chips param="type" :options="$typeOptions" :selected="$filters['type']" all="All types" label="Type" />
                @endif
                <x-dive-level.filter-chips :selected="$filters['level']" :counts="$levelCounts" />
                <div class="d-flex flex-wrap align-items-center gap-3">
                    <x-sort-chips :options="$sortOptions" :selected="$filters['sort']" />
                    <x-query-chips param="view" :options="['map' => 'Map']" :selected="$filters['view'] === 'map' ? 'map' : null" all="List" label="" />
                </div>
            </div>

            <p class="dh-board-count">
                {{ $sites->count() }} {{ Str::plural('site', $sites->count()) }}
                @if($filters['q'] !== '' || $filters['level'] !== null || $filters['type'])
                    <a href="{{ url()->current() }}">clear filters</a>
                @endif
            </p>

            @if($filters['view'] === 'map')
                {{-- Map view: same result set as the list, plotted. Click a marker to open the site. --}}
                <div id="map" class="dh-map" role="application" aria-label="Map of dive sites"></div>
                <x-dive-level.legend class="mt-3" />
            @else
                @if($sites->isEmpty())
                    <div class="dh-empty">
                        <span class="material-icons-round" aria-hidden="true">travel_explore</span>
                        <p>No sites match. Try fewer filters or another spelling.</p>
                        <a class="dh-btn dh-btn-primary" href="{{ url()->current() }}">Show all</a>
                    </div>
                @else
                    {{-- data-member and data-csrf feed the card buttons (divershub.js): guests get the account prompt, members toggle over fetch. --}}
                    <div class="dh-site-grid dh-site-grid-wide" id="dh-site-grid" data-member="{{ $isMember ? 1 : 0 }}" data-csrf="{{ csrf_token() }}" data-wish-url="{{ url('UpdateWished') }}" data-dived-url="{{ route('UpdateVisited') }}">
                        @foreach($sites as $site)
                            <x-site-card :site="$site" />
                        @endforeach
                    </div>
                    <p class="dh-board-count mt-3">
                        <span class="material-icons-round dh-inline-icon" aria-hidden="true">favorite_border</span> saves a site to your wishlist, we tell you when a boat goes there &nbsp;&middot;&nbsp;
                        <span class="material-icons-round dh-inline-icon" aria-hidden="true">radio_button_unchecked</span> marks it as dived
                    </p>
                @endif
                <x-dive-level.legend class="mt-4" />
            @endif

            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>

    @if($filters['view'] === 'map')
    @push('js')
    <script src="https://api.mapbox.com/mapbox-gl-js/v2.6.1/mapbox-gl.js"></script>
    <link href="https://api.mapbox.com/mapbox-gl-js/v2.6.1/mapbox-gl.css" rel="stylesheet" />
    <script>
        // Same Mapbox setup as the old Sites Map page, fed by the filtered list.
        // Coordinates come pre converted from the controller (sites store DMS text).
        mapboxgl.accessToken = @json($mapboxToken);
        const features = @json($mapFeatures);
        const map = new mapboxgl.Map({
            container: 'map',
            style: 'mapbox://styles/pstrika/clwqz4fds03gv01qo9d4w3g21',
            center: features.length ? features[0].geometry.coordinates : [-80.19, 26.12],
            zoom: features.length > 1 ? 8 : 11,
            projection: 'albers'
        });
        ['reef', 'wreck', 'other'].forEach(t => map.loadImage('{{ asset('assets') }}/img/icons/marker_' + t + '.png', (e, img) => { if (!e) map.addImage('icon_' + t, img); }));
        map.on('load', () => {
            map.addSource('sites', { type: 'geojson', data: { type: 'FeatureCollection', features } });
            map.addLayer({
                id: 'sites', type: 'symbol', source: 'sites',
                layout: { 'text-field': ['get', 'name'], 'text-variable-anchor': ['top'], 'text-allow-overlap': true, 'text-radial-offset': 0.1, 'text-size': 12,
                          'icon-image': ['get', 'icon'], 'icon-size': 0.3, 'icon-anchor': 'bottom', 'icon-allow-overlap': true },
                paint: { 'text-color': 'white' }
            });
            if (features.length > 1) {
                const b = new mapboxgl.LngLatBounds();
                features.forEach(f => b.extend(f.geometry.coordinates));
                map.fitBounds(b, { padding: 40, maxZoom: 12 });
            }
            map.on('click', 'sites', e => { window.location.href = e.features[0].properties.url; });
            map.on('mouseenter', 'sites', () => map.getCanvas().style.cursor = 'pointer');
            map.on('mouseleave', 'sites', () => map.getCanvas().style.cursor = '');
        });
    </script>
    @endpush
    @endif
</x-page-template>

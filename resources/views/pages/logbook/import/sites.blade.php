<x-page-template bodyClass='dh-shell dh-logbook'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-shell.header title="Import: sites" icon="place" :h1="true" />

        <div class="container-fluid py-0 dh-board">
            <div class="row">
                <div class="col-12 pt-4">

                    <x-import-progress :step="3" />

                    <p class="text-sm text-secondary mt-3">
                        {{ $fileSites->count() }} sites are used by the dives you're importing. Each one is pre-set: linked to a Divers Hub site when the name and position agree, otherwise created as one of your own sites (name, GPS, town and country come from the file). Change any of them below.
                    </p>

                    <form method="POST" action="{{ route('Logbook.import.saveSites', $import->id) }}" id="importSitesForm">
                        @csrf
                        @php
                            $groups = ['high' => [], 'check' => [], 'none' => []];
                            foreach ($fileSites as $key => $s) $groups[$suggestions[$key]['confidence'] ?? 'none'][$key] = $s;
                            $groupMeta = [
                                'high' => ['High confidence', 'Same name as one of your sites, the same (or nearly the same) name within 2 km of a Divers Hub site, or a watch GPS entry point within 300 m of one.', 'verified'],
                                'check' => ['Check these', 'Either the position or the name agrees, not both - take a look.', 'help'],
                                'none' => ['No Divers Hub match', 'These become your own sites unless you pick otherwise.', 'add_location_alt'],
                            ];
                            $rowIndex = 0;
                        @endphp
                        @foreach ($groups as $g => $sitesInGroup)
                            @continue(!count($sitesInGroup))
                            <div class="card p-0 position-relative mt-2 z-index-2 mb-4 dh-import-group is-{{ $g }}">
                                <div class="card-body">
                                    <h3 class="h5 mb-1 d-flex align-items-center gap-2">
                                        <span class="material-icons-round" aria-hidden="true">{{ $groupMeta[$g][2] }}</span>
                                        {{ $groupMeta[$g][0] }} <span class="text-sm text-secondary fw-normal">&middot; {{ count($sitesInGroup) }}</span>
                                    </h3>
                                    <p class="text-sm text-secondary">{{ $groupMeta[$g][1] }}</p>
                                    <div class="dh-import-list">
                                    @foreach ($sitesInGroup as $key => $s)
                                        @php
                                            $rowIndex++;
                                            $sug = $suggestions[$key] ?? ['kind' => 'create', 'id' => null, 'why' => '', 'distance_m' => null];
                                            $kind = $s['decision'] ?? $sug['kind'];
                                            $catalogId = $s['catalog_site_id'] ?? ($sug['kind'] === 'catalog' ? $sug['id'] : null);
                                            $ownId = $s['user_site_id'] ?? ($sug['kind'] === 'own' ? $sug['id'] : null);
                                        @endphp
                                        <div class="dh-import-site">
                                            <div class="dh-import-site-head">
                                                <div>
                                                    <strong>{{ $s['name'] }}</strong>
                                                    <span class="text-sm text-secondary">
                                                        &middot; {{ $s['uses'] }} {{ $s['uses'] === 1 ? 'dive' : 'dives' }}
                                                        @if (($s['location'] ?? '') || ($s['country'] ?? '')) &middot; {{ collect([$s['location'] ?? null, $s['country'] ?? null])->filter()->implode(', ') }} @endif
                                                    </span>
                                                </div>
                                                @if ($sug['why'])
                                                    <span class="text-xs text-secondary">{{ $sug['why'] }}@if ($sug['distance_m'] !== null)<span data-unit-value="distance" data-metric="{{ $sug['distance_m'] }}"></span>@endif{{ $sug['suffix'] ?? '' }}</span>
                                                @endif
                                            </div>
                                            <div class="dh-import-site-choices">
                                                <label><input type="radio" name="site[{{ $key }}][kind]" value="catalog" @checked($kind === 'catalog')> Divers Hub site</label>
                                                @php $catalogPick = $catalogId ? $catalog->firstWhere('id', (int) $catalogId) : null; @endphp
                                                <span class="dh-import-search-wrap">
                                                    <input type="hidden" name="site[{{ $key }}][catalog_site_id]" value="{{ $catalogPick['id'] ?? '' }}" data-catalog-id>
                                                    <input type="text" class="dh-import-search" data-for="catalog" placeholder="Search Divers Hub sites" aria-label="Search Divers Hub sites" autocomplete="off" value="{{ $catalogPick ? $catalogPick['name'] . ($catalogPick['city'] ? ' · ' . $catalogPick['city'] : '') : '' }}">
                                                    <div class="dh-search-list dh-import-results" hidden></div>
                                                </span>
                                                @if ($own->isNotEmpty())
                                                    <label><input type="radio" name="site[{{ $key }}][kind]" value="own" @checked($kind === 'own')> One of my sites</label>
                                                    @php $ownPick = $ownId ? $own->firstWhere('id', (int) $ownId) : null; @endphp
                                                    <span class="dh-import-search-wrap">
                                                        <input type="hidden" name="site[{{ $key }}][user_site_id]" value="{{ $ownPick->id ?? '' }}" data-catalog-id>
                                                        <input type="text" class="dh-import-search" data-for="own" placeholder="Search my sites" aria-label="Search my sites" autocomplete="off" value="{{ $ownPick->name ?? '' }}">
                                                        <div class="dh-search-list dh-import-results" hidden></div>
                                                    </span>
                                                @endif
                                                <label><input type="radio" name="site[{{ $key }}][kind]" value="create" @checked($kind === 'create')> Create as my site</label>
                                                <label><input type="radio" name="site[{{ $key }}][kind]" value="name" @checked($kind === 'name')> Just keep the name</label>
                                            </div>
                                        </div>
                                    @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <a href="{{ route('Logbook.import.review', $import->id) }}" class="dh-btn dh-btn-ghost-dark">Back</a>
                            <button type="submit" class="dh-btn dh-btn-primary" id="importRun">Import dives <span class="material-icons-round" aria-hidden="true">check</span></button>
                        </div>
                        <p class="text-xs text-secondary" id="importRunBusy" hidden>Importing - this can take a minute for a big logbook.</p>
                    </form>

                </div>
            </div>
        </div>
    </main>

    @push('js')
        <script src="{{ asset('assets') }}/js/dh-units.js?v={{ config('divehub.version') }}"></script>
        <script src="{{ asset('assets') }}/js/dh-select.js?v={{ config('divehub.version') }}"></script>
        <script>
            window.dhCatalog = @json($catalog->map(fn ($c) => ['id' => $c['id'], 'name' => $c['name'], 'city' => $c['city']])->values());
            window.dhOwnSites = @json($own->map(fn ($o) => ['id' => $o->id, 'name' => $o->name, 'city' => null])->values());
        </script>
        <script>
            (function () {
                function checkRadio(el, kind) {
                    var radio = el.closest('.dh-import-site-choices').querySelector('input[type=radio][value="' + kind + '"]');
                    if (radio) radio.checked = true;
                }
                // Picking from a dropdown selects that option's radio.
                // Our dropdown fires 'change' on its hidden input; it bubbles here.
                document.querySelectorAll('.dh-import-select-wrap').forEach(function (wrap) {
                    wrap.addEventListener('change', function (e) {
                        if (e.target.value) checkRadio(wrap, wrap.getAttribute('data-for'));
                    });
                });
                // Catalog search boxes: client-side filter over window.dhCatalog.
                document.querySelectorAll('.dh-import-search').forEach(function (input) {
                    var catalog = input.getAttribute('data-for') === 'own' ? (window.dhOwnSites || []) : (window.dhCatalog || []);
                    var wrap = input.closest('.dh-import-search-wrap');
                    var hidden = wrap.querySelector('[data-catalog-id]');
                    var results = wrap.querySelector('.dh-import-results');
                    input.addEventListener('input', function () {
                        hidden.value = '';
                        var q = input.value.trim().toLowerCase();
                        results.innerHTML = '';
                        if (!q) { results.hidden = true; return; }
                        var matches = catalog.filter(function (s) { return s.name.toLowerCase().indexOf(q) !== -1; }).slice(0, 8);
                        if (!matches.length) { results.hidden = true; return; }
                        matches.forEach(function (s) {
                            var a = document.createElement('a');
                            a.href = '#';
                            a.textContent = s.name + (s.city ? ' · ' + s.city : '');
                            a.addEventListener('mousedown', function (e) {
                                e.preventDefault();
                                input.value = a.textContent;
                                hidden.value = s.id;
                                results.hidden = true;
                                checkRadio(input, input.getAttribute('data-for'));
                            });
                            results.appendChild(a);
                        });
                        results.hidden = false;
                    });
                    input.addEventListener('blur', function () { setTimeout(function () { results.hidden = true; }, 150); });
                });
                document.getElementById('importSitesForm').addEventListener('submit', function () {
                    document.getElementById('importRun').disabled = true;
                    document.getElementById('importRunBusy').hidden = false;
                });
            })();
        </script>
    @endpush
</x-page-template>

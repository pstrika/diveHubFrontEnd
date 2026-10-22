<x-page-template bodyClass='dh-shell dh-logbook'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-shell.header title="My dive sites" icon="place" :h1="true" />

        <div class="container-fluid py-0 dh-board">
            <div class="row">
                <div class="col-12 pt-4">

                    @if (session('status'))
                        <div class="dh-logbook-flash" role="status">
                            <span class="material-icons-round" aria-hidden="true">check_circle</span>
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <a href="{{ route('Logbook') }}" class="dh-btn dh-btn-ghost-dark">
                            <span class="material-icons-round" aria-hidden="true">arrow_back</span>
                            Logbook
                        </a>
                        <a href="{{ route('SiteJournal.index') }}" class="dh-btn dh-btn-ghost-dark ms-auto">
                            <span class="material-icons-round" aria-hidden="true">auto_stories</span>
                            Site journal
                        </a>
                        <a href="{{ route('Logbook.sites.create') }}" class="dh-btn dh-btn-primary">
                            <span class="material-icons-round" aria-hidden="true">add_location_alt</span>
                            New site
                        </a>
                    </div>

                    <p class="text-sm text-secondary">
                        Every site you've dived: Divers Hub catalog sites (marked, read-only) and your own sites for places that aren't in the catalog yet. Only you see your own sites; tag a dive with one from the dive's site chip.
                    </p>

                    @if ($totalSites > 0)
                        @php $sitesFiltered = (bool) array_filter($filters); @endphp
                        {{-- Search + chip rows, the same ones the logbook uses (Pablo,
                             2026-10-22). A row only shows with something to choose. --}}
                        <form method="GET" action="{{ route('Logbook.sites.index') }}" class="dh-logbook-search" role="search">
                            @foreach (['source', 'type', 'country', 'location', 'cert'] as $k)
                                @if ($filters[$k])<input type="hidden" name="{{ $k }}" value="{{ $filters[$k] }}">@endif
                            @endforeach
                            <span class="material-icons-round" aria-hidden="true">search</span>
                            <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Search name, location, notes" aria-label="Search your sites">
                            @if ($filters['q'])
                                <a href="{{ route('Logbook.sites.index', array_filter(array_diff_key($filters, ['q' => 1]))) }}" class="dh-logbook-search-clear" aria-label="Clear search"><span class="material-icons-round" aria-hidden="true">close</span></a>
                            @endif
                        </form>
                        @php
                            $chipRows = [
                                ['source', 'Source', $sourceOptions],
                                ['type', 'Type', $typeOptions],
                                ['country', 'Country', $countryOptions],
                                ['location', 'Location', $locationOptions],
                                ['cert', 'Min cert', $certOptions],
                            ];
                            // Location sits under Country: only once a country is picked
                            // (Pablo, 2026-10-22), and picking another country clears it.
                            $chipRows = array_filter($chipRows, fn ($r) => ($r[0] !== 'location' || $filters['country']) && (count($r[2]) > 1 || $filters[$r[0]]));
                        @endphp
                        @if ($chipRows)
                            <div class="dh-logbook-filters">
                                @foreach ($chipRows as [$param, $label, $options])
                                    @if ($filters[$param] && !isset($options[$filters[$param]])) @php $options[$filters[$param]] = $filters[$param]; @endphp @endif
                                    <x-query-chips :param="$param" :options="$options" :selected="$filters[$param]" all="All" :label="$label"
                                        :icons="$param === 'country' ? \App\Support\SiteFacets::flags(array_keys($options)) : []"
                                        :drop="$param === 'country' ? ['location'] : []" />
                                @endforeach
                            </div>
                        @endif
                        @if ($sitesFiltered)
                            <p class="text-sm text-secondary mb-2">{{ $sites->count() }} of {{ $totalSites }} sites &middot; <a href="{{ route('Logbook.sites.index') }}">Clear filters</a></p>
                        @endif
                    @endif

                    @if ($totalSites > 0 && $sites->isEmpty())
                        <div class="card p-0 position-relative mt-3 z-index-2 mb-4">
                            <div class="card-body text-center py-4">
                                <p class="text-sm text-secondary mb-2">No sites match these filters.</p>
                                <a href="{{ route('Logbook.sites.index') }}" class="dh-btn dh-btn-ghost-dark">Clear filters</a>
                            </div>
                        </div>
                    @elseif ($sites->isEmpty())
                        <div class="card p-0 position-relative mt-3 z-index-2 mb-4">
                            <div class="card-body text-center py-5">
                                <span class="material-icons-round" aria-hidden="true" style="font-size: 40px; color: var(--dh-shallow, #9bb);">place</span>
                                <h3 class="mt-3">No sites yet</h3>
                                <p class="text-sm text-secondary">Create one and tag your dives with it.</p>
                            </div>
                        </div>
                    @else
                        <div class="dh-logbook-list">
                            @foreach ($sites as $s)
                                @php $isMine = $s->kind === 'mine'; @endphp
                                <div class="dh-logbook-row dh-user-site-row {{ $isMine ? '' : 'is-catalog' }} {{ $isMine && $highlight === $s->id ? 'is-highlight' : '' }}" id="{{ $s->key }}">
                                    <div class="dh-logbook-row-mode">
                                        <x-site-type-icon :type="$s->iconType" size="30" />
                                        <div class="dh-logbook-row-mode-label">{{ $s->type }}</div>
                                    </div>
                                    <div class="dh-logbook-row-main d-block">
                                        <div class="dh-logbook-row-title">
                                            @if ($s->siteUrl)
                                                <a href="{{ $s->siteUrl }}" class="text-truncate">{{ $s->name }}</a>
                                            @else
                                                <span class="text-truncate">{{ $s->name }}</span>
                                            @endif
                                        </div>
                                        <div class="dh-logbook-row-sub text-sm text-secondary">
                                            {{ collect([$s->location, $s->country])->filter()->implode(', ') ?: '—' }}
                                            @if ($s->lat !== null && $s->lng !== null)
                                                &middot; <a href="https://www.google.com/maps?q={{ $s->lat }},{{ $s->lng }}" target="_blank" rel="noopener">{{ number_format($s->lat, 5) }}, {{ number_format($s->lng, 5) }}</a>
                                            @endif
                                        </div>
                                        <div class="dh-logbook-row-sub text-sm text-secondary">
                                            @php
                                                $facts = collect([
                                                    $s->cert ? 'Min ' . e($s->cert) : null,
                                                    $s->water_type ? ucfirst(e($s->water_type)) . ' water' : null,
                                                    $s->altitude_m !== null ? '<span data-unit-value="depth" data-metric="' . (int) $s->altitude_m . '"></span> altitude' : null,
                                                ])->filter();
                                            @endphp
                                            {!! $facts->isEmpty() ? '&mdash;' : $facts->implode(' &middot; ') !!}
                                        </div>
                                    </div>
                                    <div class="dh-logbook-row-stats">
                                        @if ($s->dives)
                                            <a href="{{ $s->divesUrl }}" title="Show these dives in your logbook"><strong>{{ $s->dives }}</strong><span class="text-xs text-secondary d-block">{{ $s->dives === 1 ? 'dive' : 'dives' }}</span></a>
                                        @else
                                            <div><strong>0</strong><span class="text-xs text-secondary d-block">dives</span></div>
                                        @endif
                                    </div>
                                    <div class="dh-user-site-actions">
                                        {{-- Journal on every site (Pablo, 2026-10-22). --}}
                                        <a href="{{ $s->journalUrl }}" class="dh-btn-icon" title="Photos and marine life here"><span class="material-icons-round" aria-hidden="true">auto_stories</span></a>
                                        @if ($isMine)
                                            <a href="{{ route('Logbook.sites.edit', $s->id) }}" class="dh-btn-icon" title="Edit"><span class="material-icons-round" aria-hidden="true">edit</span></a>
                                            <form method="POST" action="{{ route('Logbook.sites.destroy', $s->id) }}" class="d-inline" onsubmit="return confirm('Delete this site? Dives tagged with it keep the name as plain text.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dh-btn-icon dh-btn-icon-danger" title="Delete"><span class="material-icons-round" aria-hidden="true">delete</span></button>
                                            </form>
                                        @else
                                            {{-- A Divers Hub site: no edit/delete - the DH mark, opening its page. --}}
                                            <a href="{{ $s->siteUrl }}" class="dh-site-dh-logo" title="Divers Hub site - open its page"><img src="{{ asset('assets') }}/img/logos/logo_circle_small.png" alt="Divers Hub" width="32" height="32"></a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </main>

    @push('js')
        <script src="{{ asset('assets') }}/js/dh-units.js?v={{ config('divehub.version') }}"></script>
    @endpush
</x-page-template>

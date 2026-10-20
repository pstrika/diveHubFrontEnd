<x-page-template bodyClass='dh-shell dh-logbook'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-shell.header title="Logbook" icon="menu_book" :h1="true" />

        <div class="container-fluid py-0 dh-board">
            <div class="row">
                <div class="col-12 pt-4">

                    @if (session('status'))
                        <div class="dh-logbook-flash" role="status">
                            <span class="material-icons-round" aria-hidden="true">check_circle</span>
                            {{ session('status') }}
                        </div>
                    @endif

{{-- Units switch: always alone on its own line, top right (Pablo, 2026-10-17). --}}
                    <div class="dh-unit-row"><button type="button" class="dh-btn dh-btn-ghost-dark dh-unit-toggle" id="switchUnitsLogbook"></button></div>

                    {{-- Add a dive by hand / import a logbook / your own sites
                         (Pablo, 2026-10-16). --}}
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                        <div class="dh-logbook-actions">
                            <a href="{{ route('Logbook.create') }}" class="dh-btn dh-btn-ghost-dark">
                                <span class="material-icons-round" aria-hidden="true">add</span>
                                Add dive
                            </a>
                            {{-- A few dives from a file (Garmin, Apple Watch). The whole-
                                 logbook import lives in Logbook settings. --}}
                            <a href="{{ route('Logbook.import.index') }}" class="dh-btn dh-btn-ghost-dark" title="Add dives from a Garmin or Apple Watch file">
                                <span class="material-icons-round" aria-hidden="true">upload_file</span>
                                Import
                            </a>
                            <a href="{{ route('Logbook.sites.index') }}" class="dh-btn dh-btn-ghost-dark">
                                <span class="material-icons-round" aria-hidden="true">place</span>
                                My sites
                            </a>
                            @if (\App\Support\EarlyAccess::allows(auth()->user(), 'equipment'))
                                <a href="{{ route('Equipment.index') }}" class="dh-btn dh-btn-ghost-dark">
                                    <span class="material-icons-round" aria-hidden="true">handyman</span>
                                    Equipment
                                </a>
                            @endif
                            @if (\App\Support\EarlyAccess::allows(auth()->user(), 'species'))
                                <a href="{{ route('Species.index') }}" class="dh-btn dh-btn-ghost-dark">
                                    <span class="dh-cat-icon" style="--icon:url('{{ asset('assets/img/icons/logbook/species_reef_fish.svg') }}')" aria-hidden="true"></span>
                                    Marine life
                                </a>
                            @endif
                            <a href="{{ route('Logbook.duplicates') }}" class="dh-btn dh-btn-ghost-dark" title="Dives logged by more than one computer">
                                <span class="material-icons-round" aria-hidden="true">devices</span>
                                Duplicates
                            </a>
                            <button type="button" class="dh-btn dh-btn-ghost-dark" title="Sync from your dive computer with the app" data-bs-toggle="modal" data-bs-target="#dhAppModal">
                                <span class="material-icons-round" aria-hidden="true">phone_iphone</span>
                            </button>
                            <a href="{{ route('Logbook.settings') }}" class="dh-btn dh-btn-ghost-dark" title="Logbook settings">
                                <span class="material-icons-round" aria-hidden="true">settings</span>
                            </a>
                        </div>
                    </div>

                    {{-- "Get the app" card: dives in the logbook but none synced
                         from the app yet. Gone for good once they sync one or
                         close it (cookie, a year). --}}
                    @if ($stats['total'] > 0 && !$usesApp && !$appCardHidden)
                        <div class="dh-app-card" id="dhAppCard">
                            <span class="material-icons-round" aria-hidden="true">phone_iphone</span>
                            <div class="dh-app-card-text">
                                <strong>Sync straight from your dive computer</strong>
                                <span class="text-sm text-secondary">Logbook by Divers Hub, our iPhone app, downloads your dives over Bluetooth.</span>
                            </div>
                            <button type="button" class="dh-btn dh-btn-ghost-dark" data-bs-toggle="modal" data-bs-target="#dhAppModal">How it works</button>
                            <button type="button" class="dh-app-card-close" id="dhAppCardClose" aria-label="Hide this">
                                <span class="material-icons-round" aria-hidden="true">close</span>
                            </button>
                        </div>
                    @endif

                    {{-- Same dark tile dashboard as DiveDetails' own header
                         (Pablo, 2026-10-15: "the dashboard in the logbook landing
                         needs to follow the same shape, layout and colors as the
                         one for the dive details"). --}}
                    @if ($stats['total'] > 0)
                        @php
                            $totalMinutes = intdiv($stats['total_time_seconds'], 60);
                            $hours = intdiv($totalMinutes, 60);
                            $minutes = $totalMinutes % 60;
                        @endphp
                        <div class="dh-dive-header-stats">
                            <div class="dh-dive-header-stat">
                                {{-- Same glyph as the Dives tab in the bottom bar (Pablo, 2026-10-15). --}}
                                <span class="material-icons-round" aria-hidden="true">scuba_diving</span>
                                <div class="dh-dive-header-stat-value">{{ $stats['total'] }}</div>
                                <div class="dh-dive-header-stat-label">Total dives</div>
                            </div>
                            <div class="dh-dive-header-stat">
                                <span class="material-icons-round" aria-hidden="true">schedule</span>
                                <div class="dh-dive-header-stat-value">{{ $hours }}h {{ $minutes }}m</div>
                                <div class="dh-dive-header-stat-label">Total time</div>
                            </div>
                            <div class="dh-dive-header-stat">
                                <span class="material-icons-round" aria-hidden="true">vertical_align_bottom</span>
                                <div class="dh-dive-header-stat-value" data-unit-value="depth" data-metric="{{ $stats['max_depth_m'] }}"></div>
                                <div class="dh-dive-header-stat-label">Deepest dive</div>
                            </div>
                        </div>
                    @endif

                    {{-- Filter chips (Pablo, 2026-10-15), the same query-string
                         chips the Trips finder uses, so filters compose and URLs
                         are shareable. A row only appears when this logbook has
                         more than one value for it - a diver with no CC dives
                         never sees a circuit filter at all. --}}
                    {{-- Search (Pablo, 2026-10-17): site, city, operator, buddies,
                         notes, tags, computer or dive number. Keeps the chips. --}}
                    @if ($stats['total'] > 0)
                        <form method="GET" action="{{ route('Logbook') }}" class="dh-logbook-search" role="search">
                            @foreach (['circuit', 'class', 'type', 'tag', 'site', 'gear', 'species'] as $k)
                                @if ($filters[$k])<input type="hidden" name="{{ $k }}" value="{{ $filters[$k] }}">@endif
                            @endforeach
                            <span class="material-icons-round" aria-hidden="true">search</span>
                            <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Search site, buddy, notes, dive #" aria-label="Search your dives">
                            @if ($filters['q'])
                                <a href="{{ route('Logbook', array_filter(array_diff_key($filters, ['q' => 1]))) }}" class="dh-logbook-search-clear" aria-label="Clear search"><span class="material-icons-round" aria-hidden="true">close</span></a>
                            @endif
                        </form>
                        @if ($filterLabel)
                            <div class="dh-logbook-site-filter">
                                <span class="material-icons-round" aria-hidden="true">{{ $filterLabel[0] }}</span>
                                {{ $filterLabel[1] }} <strong>{{ $filterLabel[2] }}</strong>
                                <a href="{{ route('Logbook', array_filter(array_diff_key($filters, [$filterLabel[3] => 1]))) }}" aria-label="Show all dives"><span class="material-icons-round" aria-hidden="true">close</span></a>
                            </div>
                        @endif
                        @if ($siteFilterName)
                            <div class="dh-logbook-site-filter">
                                <span class="material-icons-round" aria-hidden="true">place</span>
                                Dives at <strong>{{ $siteFilterName }}</strong>
                                <a href="{{ route('Logbook', array_filter(array_diff_key($filters, ['site' => 1]))) }}" aria-label="Show all dives"><span class="material-icons-round" aria-hidden="true">close</span></a>
                            </div>
                        @endif
                    @endif

                    @if ($showCircuitFilter || $showClassFilter || count($tagOptions) || count($typeOptions))
                        <div class="dh-logbook-filters">
                            @if ($showCircuitFilter)
                                <x-query-chips param="circuit" :options="['oc' => 'Open Circuit', 'cc' => 'Closed Circuit']" :selected="$filters['circuit']" all="All" label="Circuit" />
                            @endif
                            @if ($showClassFilter)
                                <x-query-chips param="class" :options="$classOptions" :selected="$filters['class']" all="All" label="Level" />
                            @endif
                            @if (count($typeOptions))
                                <x-query-chips param="type" :options="$typeOptions" :selected="$filters['type']" all="All" label="Dive type" />
                            @endif
                            @if (count($tagOptions))
                                <x-query-chips param="tag" :options="$tagOptions" :selected="$filters['tag']" all="All" label="Tags" />
                            @endif
                        </div>
                    @endif

                    @if ($stats['total'] === 0)
                        <div class="card p-0 position-relative mt-3 z-index-2 mb-4">
                            <div class="card-body py-4">
                                <div class="text-center">
                                    <span class="material-icons-round" aria-hidden="true" style="font-size: 40px; color: var(--dh-shallow, #9bb);">menu_book</span>
                                    <h3 class="mt-2">No dives yet</h3>
                                    <p class="text-sm text-secondary">Three steps and your dive computer fills this in for you.</p>
                                </div>
                                @include('pages.logbook.app-steps')
                                <div class="dh-app-empty-alt">
                                    <span class="text-sm text-secondary">Already keep a logbook? Bring it with you from MacDive, Shearwater Cloud, Garmin or Oceanic+.</span>
                                    <a href="{{ route('Logbook.import.index', ['full' => 1]) }}" class="dh-btn dh-btn-ghost-dark">
                                        <span class="material-icons-round" aria-hidden="true">upload_file</span>
                                        Import your logbook
                                    </a>
                                </div>
                            </div>
                        </div>
                    @elseif ($dives->isEmpty())
                        <div class="card p-0 position-relative mt-3 z-index-2 mb-4">
                            <div class="card-body text-center py-4">
                                <p class="text-sm text-secondary mb-2">No dives match these filters.</p>
                                <a href="{{ route('Logbook') }}" class="dh-btn dh-btn-ghost-dark">Clear filters</a>
                            </div>
                        </div>
                    @else
                        <div class="dh-logbook-list">
                            @foreach ($dives as $dive)
                                @php
                                    // Catalog site, the diver's own site, or a free-typed
                                    // name - Dive::siteInfo() resolves the precedence.
                                    $siteInfo = $dive->siteInfo();
                                    $operatorName = $dive->conditions['operator_name'] ?? null;
                                    $operatorLogo = $operatorName ? ($operatorLogos[mb_strtolower($operatorName)] ?? null) : null;
                                    $city = $siteInfo['city'] ?? null;
                                    $wished = $siteInfo && $siteInfo['site'] && isset($wishedSiteIds[(int) $siteInfo['site']->id]);
                                    $siteTitle = $dive->title();
                                @endphp
                                <a href="{{ route('DiveDetails', $dive->id) }}" class="dh-logbook-row{{ isset($dive->pendingGroup) ? ' is-same-dive-pending' : '' }}">
                                    {{-- Phone only: the site + city and the time as a full-width
                                         block above the number/date/mode/stats line (Pablo,
                                         2026-10-15); on tablet/desktop this is the 4th column
                                         below instead. --}}
                                    <div class="dh-logbook-row-top d-md-none">
                                        <div class="dh-logbook-row-title">
                                            <span class="text-truncate">{{ $siteTitle }}@if ($city) &middot; {{ $city }}@endif</span>
                                            @if (isset($dive->pendingGroup))<span class="dh-same-dive-badge" title="Logged by more than one computer - open it to combine or keep apart">Same dive?</span>@endif
                                            @if (!empty($photoCounts[$dive->id]))<span class="dh-logbook-photo-badge" title="{{ $photoCounts[$dive->id] }} {{ $photoCounts[$dive->id] == 1 ? 'photo' : 'photos' }}"><span class="material-icons-round" aria-hidden="true">photo_camera</span>{{ $photoCounts[$dive->id] }}</span>@endif
                                            @if ($wished)<span class="material-icons-round dh-logbook-row-wished" title="On your wishlist">bookmark</span>@endif
                                            {{-- Time on the site line, right-aligned (Pablo, 2026-10-19, as in the app). --}}
                                            <span class="dh-logbook-row-time">{{ $dive->started_at->format('g:i A') }}</span>
                                        </div>
                                    </div>
                                    @php [$numBase, $numLetter] = array_pad(explode('.', (string) $dive->listSeq, 2), 2, null); @endphp
                                    <div class="dh-logbook-row-number" title="Dive #{{ $dive->listSeq }}">{{ $numBase }}@if ($numLetter)<span class="dh-row-number-letter">{{ $numLetter }}</span>@endif</div>
                                    <div class="dh-logbook-row-date">
                                        <div class="dh-logbook-row-day">{{ $dive->started_at->format('d') }}</div>
                                        <div class="dh-logbook-row-month">{{ $dive->started_at->format('M Y') }}</div>
                                    </div>
                                    {{-- Dive mode as its own column, same footprint as the
                                         date (Pablo, 2026-10-15: "the icon for the type of
                                         dive needs to be bigger...a third column after the
                                         date. Same size as the date"). --}}
                                    <div class="dh-logbook-row-mode" title="{{ $dive->modeLabel() }} · {{ ucfirst($dive->classification()) }}">
                                        <x-svg-icon :file="$dive->iconFile()" class="dh-logbook-row-mode-icon" />
                                        <div class="dh-logbook-row-mode-label">{{ ['technical' => 'Tec', 'snorkeling' => 'Snorkel', 'freediving' => 'Free'][$dive->classification()] ?? 'Rec' }}</div>
                                    </div>
                                    {{-- Phone: just the operator's logo, next to the dive type (as in the app). --}}
                                    @if ($operatorLogo)
                                        <div class="dh-logbook-row-oplogo d-md-none" title="{{ $operatorName }}"><img src="{{ $operatorLogo }}" alt="{{ $operatorName }}"></div>
                                    @endif
                                    {{-- Tablet/desktop 4th column, three lines: site, time ·
                                         city, operator. A catalog site (site_id) wins; otherwise
                                         a free-typed name (conditions.site_name_custom); falls
                                         back to the classification only when no site was ever
                                         tagged. --}}
                                    <div class="dh-logbook-row-main d-none d-md-block">
                                        <div class="dh-logbook-row-title">
                                            <span class="text-truncate">{{ $siteTitle }}</span>
                                            @if (isset($dive->pendingGroup))<span class="dh-same-dive-badge" title="Logged by more than one computer - open it to combine or keep apart">Same dive?</span>@endif
                                            @if (!empty($photoCounts[$dive->id]))<span class="dh-logbook-photo-badge" title="{{ $photoCounts[$dive->id] }} {{ $photoCounts[$dive->id] == 1 ? 'photo' : 'photos' }}"><span class="material-icons-round" aria-hidden="true">photo_camera</span>{{ $photoCounts[$dive->id] }}</span>@endif
                                            @if ($wished)
                                                {{-- Same bookmark the dashboard's trip cards use for a
                                                     wishlisted site. --}}
                                                <span class="material-icons-round dh-logbook-row-wished" title="On your wishlist">bookmark</span>
                                            @endif
                                            <span class="dh-logbook-row-time">{{ $dive->started_at->format('g:i A') }}</span>
                                        </div>
                                        @if ($city)
                                            <div class="dh-logbook-row-sub text-sm text-secondary">{{ $city }}</div>
                                        @endif
                                        @if ($operatorName)
                                            <div class="dh-logbook-row-sub text-sm text-secondary">
                                                @if ($operatorLogo)<img src="{{ $operatorLogo }}" alt="" class="dh-logbook-row-operator-logo">@endif
                                                {{ $operatorName }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="dh-logbook-row-stats">
                                        <div><strong><span data-unit-value="depth" data-metric="{{ $dive->max_depth_m }}"></span></strong><span class="text-xs text-secondary d-block">max depth</span></div>
                                        <div><strong>{{ gmdate('H:i', $dive->duration_seconds) }}</strong><span class="text-xs text-secondary d-block">duration</span></div>
                                    </div>
                                    <span class="material-icons-round dh-logbook-row-chevron" aria-hidden="true">chevron_right</span>
                                </a>
                            @endforeach
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3 mb-4">
                            @if ($dives->previousPageUrl())
                                <a href="{{ $dives->previousPageUrl() }}" class="dh-btn dh-btn-ghost-dark">Newer</a>
                            @else
                                <span></span>
                            @endif
                            @if ($dives->nextPageUrl())
                                <a href="{{ $dives->nextPageUrl() }}" class="dh-btn dh-btn-ghost-dark">Older</a>
                            @endif
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </main>

    <div class="modal fade" id="dhAppModal" tabindex="-1" aria-labelledby="dhAppModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="dhAppModalTitle">Sync from your dive computer</h5>
                    <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close"><span class="material-icons-round" aria-hidden="true">close</span></button>
                </div>
                <div class="modal-body">
                    @include('pages.logbook.app-steps')
                </div>
            </div>
        </div>
    </div>

    @push('js')
        <script src="{{ asset('assets') }}/js/dh-units.js?v={{ config('divehub.version') }}"></script>
        <script>
            (function () {
                var close = document.getElementById('dhAppCardClose');
                if (!close) return;
                close.addEventListener('click', function () {
                    document.cookie = 'dh_app_card_hidden=1; max-age=31536000; path=/; samesite=lax';
                    document.getElementById('dhAppCard').remove();
                });
            })();
        </script>
    @endpush
</x-page-template>

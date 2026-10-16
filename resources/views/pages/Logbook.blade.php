<x-page-template bodyClass='dh-shell dh-logbook'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-shell.header title="Logbook" icon="menu_book" :h1="true" />

        <div class="container-fluid py-0">
            <div class="row">
                <div class="col-lg-9 col-12 m-auto pt-4">

                    @if (session('status'))
                        <div class="dh-logbook-flash" role="status">
                            <span class="material-icons-round" aria-hidden="true">check_circle</span>
                            {{ session('status') }}
                        </div>
                    @endif

                    {{-- Add a dive by hand / import a logbook / your own sites
                         (Pablo, 2026-10-16), beside the unit toggle. --}}
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                        <div class="dh-logbook-actions">
                            <a href="{{ route('Logbook.create') }}" class="dh-btn dh-btn-ghost-dark">
                                <span class="material-icons-round" aria-hidden="true">add</span>
                                Add dive
                            </a>
                            <a href="{{ route('Logbook.import.index') }}" class="dh-btn dh-btn-ghost-dark">
                                <span class="material-icons-round" aria-hidden="true">upload_file</span>
                                Import
                            </a>
                            <a href="{{ route('Logbook.sites.index') }}" class="dh-btn dh-btn-ghost-dark">
                                <span class="material-icons-round" aria-hidden="true">place</span>
                                My sites
                            </a>
                            <a href="{{ route('Logbook.duplicates') }}" class="dh-btn dh-btn-ghost-dark" title="Dives logged by more than one computer">
                                <span class="material-icons-round" aria-hidden="true">devices</span>
                                Duplicates
                            </a>
                            <a href="{{ route('Logbook.settings') }}" class="dh-btn dh-btn-ghost-dark" title="Logbook settings">
                                <span class="material-icons-round" aria-hidden="true">settings</span>
                            </a>
                        </div>
                        <button type="button" class="dh-btn dh-btn-ghost-dark dh-unit-toggle" id="switchUnitsLogbook"></button>
                    </div>

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
                    @if ($showCircuitFilter || $showClassFilter || count($tagOptions) || count($typeOptions))
                        <div class="dh-logbook-filters">
                            @if ($showCircuitFilter)
                                <x-query-chips param="circuit" :options="['oc' => 'Open Circuit', 'cc' => 'Closed Circuit']" :selected="$filters['circuit']" all="All" label="Circuit" />
                            @endif
                            @if ($showClassFilter)
                                <x-query-chips param="class" :options="['recreational' => 'Recreational', 'technical' => 'Technical']" :selected="$filters['class']" all="All" label="Level" />
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
                            <div class="card-body text-center py-5">
                                <span class="material-icons-round" aria-hidden="true" style="font-size: 40px; color: var(--dh-shallow, #9bb);">menu_book</span>
                                <h3 class="mt-3">No dives yet</h3>
                                <p class="text-sm text-secondary">
                                    Sync your dive computer with the Divers Hub uploader app to see your dives here.
                                </p>
                                <a href="{{ route('UploadTokens') }}" class="dh-btn dh-btn-ghost-dark mt-2">
                                    <span class="material-icons-round" aria-hidden="true">watch</span>
                                    Set up dive computer upload
                                </a>
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
                                            @if ($wished)<span class="material-icons-round dh-logbook-row-wished" title="On your wishlist">bookmark</span>@endif
                                        </div>
                                        <div class="dh-logbook-row-sub text-sm text-secondary">{{ $dive->started_at->format('g:i A') }}</div>
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
                                        <div class="dh-logbook-row-mode-label">{{ $dive->classification() === 'technical' ? 'Tec' : 'Rec' }}</div>
                                    </div>
                                    {{-- Tablet/desktop 4th column, three lines: site, time ·
                                         city, operator. A catalog site (site_id) wins; otherwise
                                         a free-typed name (conditions.site_name_custom); falls
                                         back to the classification only when no site was ever
                                         tagged. --}}
                                    <div class="dh-logbook-row-main d-none d-md-block">
                                        <div class="dh-logbook-row-title">
                                            <span class="text-truncate">{{ $siteTitle }}</span>
                                            @if (isset($dive->pendingGroup))<span class="dh-same-dive-badge" title="Logged by more than one computer - open it to combine or keep apart">Same dive?</span>@endif
                                            @if ($wished)
                                                {{-- Same bookmark the dashboard's trip cards use for a
                                                     wishlisted site. --}}
                                                <span class="material-icons-round dh-logbook-row-wished" title="On your wishlist">bookmark</span>
                                            @endif
                                        </div>
                                        <div class="dh-logbook-row-sub text-sm text-secondary">
                                            {{ $dive->started_at->format('g:i A') }}@if ($city) &middot; {{ $city }}@endif
                                        </div>
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

    @push('js')
        <script src="{{ asset('assets') }}/js/dh-units.js?v={{ config('divehub.version') }}"></script>
    @endpush
</x-page-template>

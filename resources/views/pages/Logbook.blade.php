<x-page-template bodyClass='dh-shell'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-shell.header title="Logbook" icon="menu_book" :h1="true" />

        <div class="container-fluid py-0">
            <div class="row">
                <div class="col-lg-9 col-12 m-auto pt-4">

                    <div class="d-flex justify-content-end mb-2">
                        <button type="button" class="dh-btn dh-btn-ghost-dark dh-unit-toggle" id="switchUnitsLogbook"></button>
                    </div>

                    @if ($stats['total'] > 0)
                        <div class="dh-equip-stats mb-4">
                            <div class="dh-equip-stat is-good">
                                <div class="dh-equip-stat-head">
                                    <span class="material-icons-round" aria-hidden="true">pool</span>
                                    <span>Total dives</span>
                                </div>
                                <div class="dh-equip-stat-value">{{ $stats['total'] }}</div>
                            </div>
                            <div class="dh-equip-stat is-good">
                                <div class="dh-equip-stat-head">
                                    <span class="material-icons-round" aria-hidden="true">schedule</span>
                                    <span>Total time</span>
                                </div>
                                @php
                                    $totalMinutes = intdiv($stats['total_time_seconds'], 60);
                                    $hours = intdiv($totalMinutes, 60);
                                    $minutes = $totalMinutes % 60;
                                @endphp
                                <div class="dh-equip-stat-value">{{ $hours }}h {{ $minutes }}m</div>
                            </div>
                            <div class="dh-equip-stat is-good">
                                <div class="dh-equip-stat-head">
                                    <span class="material-icons-round" aria-hidden="true">vertical_align_bottom</span>
                                    <span>Deepest dive</span>
                                </div>
                                <div class="dh-equip-stat-value" data-unit-value="depth" data-metric="{{ $stats['max_depth_m'] }}"></div>
                            </div>
                        </div>
                    @endif

                    @if ($dives->isEmpty())
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
                    @else
                        <div class="dh-logbook-list">
                            @foreach ($dives as $dive)
                                @php
                                    // Chronological position (oldest = 1), not the raw dive_number
                                    // column - see Dive::sequenceNumber()'s docblock for why. Computed
                                    // by arithmetic from the page's newest-first rank rather than an
                                    // extra query per row: $dives->firstItem() is this page's first
                                    // row's 1-based rank in newest-first order, $loop->iteration adds
                                    // this row's offset within the page.
                                    $descRank = $dives->firstItem() + $loop->iteration - 1;
                                    $seq = $stats['total'] - $descRank + 1;
                                @endphp
                                <a href="{{ route('DiveDetails', $dive->id) }}" class="dh-logbook-row">
                                    <div class="dh-logbook-row-number" title="Dive #{{ $seq }}">{{ $seq }}</div>
                                    <div class="dh-logbook-row-date">
                                        <div class="dh-logbook-row-day">{{ $dive->started_at->format('d') }}</div>
                                        <div class="dh-logbook-row-month">{{ $dive->started_at->format('M Y') }}</div>
                                    </div>
                                    <div class="dh-logbook-row-main">
                                        <div class="dh-logbook-row-title">
                                            <x-svg-icon :file="$dive->iconFile()" class="dh-logbook-row-mode-icon" />
                                            {{-- A catalog site (site_id) wins; otherwise a free-typed name
                                                 (Pablo, 2026-10-15: "if we type a dive site that it's not in
                                                 our list...we need to show whatever the user typed" - the
                                                 DiveDetails page already stores conditions.site_name_custom
                                                 for this exact case, this list just wasn't reading it);
                                                 falls back to the depth-based classification only when no
                                                 site was ever tagged at all. --}}
                                            {{ $dive->site()->name ?? $dive->conditions['site_name_custom'] ?? ucfirst($dive->classification()) . ' Dive' }}
                                        </div>
                                        <div class="dh-logbook-row-sub text-sm text-secondary">
                                            {{ $dive->started_at->format('g:i A') }}
                                            &middot; {{ $dive->computer_model }}
                                            @if ($dive->conditions['operator_name'] ?? null)
                                                &middot; {{ $dive->conditions['operator_name'] }}
                                            @endif
                                        </div>
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

<x-page-template bodyClass='dh-shell dh-logbook'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-shell.header :title="$name" icon="auto_stories" :h1="true" />

        <div class="container-fluid py-0 dh-board">
            <div class="row">
                <div class="col-12 pt-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <a href="{{ route('SiteJournal.index') }}" class="dh-btn dh-btn-ghost-dark"><span class="material-icons-round" aria-hidden="true">arrow_back</span> Site journal</a>
                        @if ($publicUrl)
                            <a href="{{ $publicUrl }}" class="dh-btn dh-btn-ghost-dark"><span class="material-icons-round" aria-hidden="true">place</span> Site page</a>
                        @endif
                    </div>

                    <div class="dh-dive-header-stats mb-4">
                        <div class="dh-dive-header-stat">
                            <span class="material-icons-round" aria-hidden="true">scuba_diving</span>
                            <div class="dh-dive-header-stat-value">{{ $dives->count() }}</div>
                            <div class="dh-dive-header-stat-label">{{ $dives->count() === 1 ? 'Dive' : 'Dives' }}</div>
                        </div>
                        <div class="dh-dive-header-stat">
                            <span class="material-icons-round" aria-hidden="true">photo_library</span>
                            <div class="dh-dive-header-stat-value">{{ $photos->count() }}</div>
                            <div class="dh-dive-header-stat-label">Photos</div>
                        </div>
                        @if ($speciesOn)
                            <div class="dh-dive-header-stat">
                                <span class="dh-cat-icon" style="--icon:url('{{ asset('assets/img/icons/logbook/species_reef_fish.svg') }}')" aria-hidden="true"></span>
                                <div class="dh-dive-header-stat-value">{{ $species->count() }}</div>
                                <div class="dh-dive-header-stat-label">Species</div>
                            </div>
                        @endif
                    </div>

                    @if ($speciesOn)
                        <section class="mb-4">
                            <h2 class="dh-panel-title">Marine life seen here</h2>
                            @if ($species->isEmpty())
                                <p class="text-sm text-secondary">Nothing tagged at this site yet. Tag what you saw from each dive's Marine life card.</p>
                            @else
                                <div class="dh-species-list">
                                    @foreach ($species as $s)
                                        <a href="{{ route('Species.show', $s->uuid) }}" class="dh-species-row">
                                            <span class="dh-species-name">
                                                <strong>{{ $s->common_name }}</strong>
                                                <em>{{ \Carbon\Carbon::parse($s->first_seen)->format('M Y') === \Carbon\Carbon::parse($s->last_seen)->format('M Y')
                                                    ? \Carbon\Carbon::parse($s->last_seen)->format('M j, Y')
                                                    : 'First ' . \Carbon\Carbon::parse($s->first_seen)->format('M Y') . ' · last ' . \Carbon\Carbon::parse($s->last_seen)->format('M j, Y') }}</em>
                                            </span>
                                            <span class="dh-species-seen" title="Dives it was seen on"><span class="material-icons-round" aria-hidden="true">visibility</span>{{ $s->dives }}</span>
                                            <span class="material-icons-round dh-logbook-row-chevron" aria-hidden="true">chevron_right</span>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </section>
                    @endif

                    <section class="mb-4">
                        <h2 class="dh-panel-title">Your photos <span class="dh-region-count">{{ $photos->count() }}</span></h2>
                        @if ($photos->isEmpty())
                            <p class="text-sm text-secondary">No photos from this site yet. Add them from a dive's Photos card.</p>
                        @else
                            <div class="dh-dive-photos-grid">
                                @foreach ($photos as $p)
                                    <a class="dh-dive-photo" href="{{ route('DiveDetails', $p->dive_id) }}" title="{{ $p->caption ?: $p->dive?->started_at?->format('M j, Y') }}">
                                        <img src="{{ $p->url('thumb') }}" alt="{{ $p->caption }}" loading="lazy">
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </section>

                    <section class="mb-4">
                        <h2 class="dh-panel-title">Your dives <span class="dh-region-count">{{ $dives->count() }}</span></h2>
                        <div class="dh-logbook-list">
                            @foreach ($dives as $d)
                                <a href="{{ route('DiveDetails', $d->id) }}" class="dh-logbook-row">
                                    <div class="dh-logbook-row-date">
                                        <div class="dh-logbook-row-day">{{ $d->started_at->format('d') }}</div>
                                        <div class="dh-logbook-row-month">{{ $d->started_at->format('M Y') }}</div>
                                    </div>
                                    <div class="dh-logbook-row-main d-block">
                                        <div class="dh-logbook-row-title">
                                            {{-- The mode as its icon, same as the logbook rows (Pablo, 2026-10-22). --}}
                                            <span class="dh-journal-mode" title="{{ $d->modeLabel() }}"><x-svg-icon :file="$d->iconFile()" /><span class="visually-hidden">{{ $d->modeLabel() }}</span></span>
                                            <span class="text-truncate">{{ $d->started_at->format('g:i A') }}</span>
                                        </div>
                                        @php
                                            $operatorName = $d->conditions['operator_name'] ?? null;
                                            $operatorLogo = $operatorName ? ($operatorLogos[mb_strtolower($operatorName)] ?? null) : null;
                                        @endphp
                                        <div class="dh-logbook-row-sub text-sm text-secondary">
                                            @if ($operatorLogo)<img src="{{ $operatorLogo }}" alt="" class="dh-logbook-row-operator-logo">@endif
                                            {{ $operatorName }}
                                        </div>
                                    </div>
                                    <div class="dh-logbook-row-stats">
                                        <div><strong><span data-unit-value="depth" data-metric="{{ $d->max_depth_m }}"></span></strong><span class="text-xs text-secondary d-block">max depth</span></div>
                                        <div><strong>{{ gmdate('H:i', $d->duration_seconds) }}</strong><span class="text-xs text-secondary d-block">duration</span></div>
                                    </div>
                                    <span class="material-icons-round dh-logbook-row-chevron" aria-hidden="true">chevron_right</span>
                                </a>
                            @endforeach
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </main>

    @push('js')
        <script src="{{ asset('assets') }}/js/dh-units.js?v={{ config('divehub.version') }}"></script>
    @endpush
</x-page-template>

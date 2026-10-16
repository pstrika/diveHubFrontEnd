<x-page-template bodyClass='dh-shell dh-logbook'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-shell.header title="My dive sites" icon="place" :h1="true" />

        <div class="container-fluid py-0">
            <div class="row">
                <div class="col-lg-9 col-12 m-auto pt-4">

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
                        <a href="{{ route('Logbook.sites.create') }}" class="dh-btn dh-btn-primary">
                            <span class="material-icons-round" aria-hidden="true">add_location_alt</span>
                            New site
                        </a>
                    </div>

                    <p class="text-sm text-secondary">
                        Your own sites, for places that aren't in the Divers Hub catalog yet. Only you see them; tag a dive with one from the dive's site chip.
                    </p>

                    @if ($sites->isEmpty())
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
                                <div class="dh-logbook-row dh-user-site-row {{ $highlight === $s->id ? 'is-highlight' : '' }}" id="site-{{ $s->id }}">
                                    <div class="dh-logbook-row-mode">
                                        <x-site-type-icon :type="$s->iconType()" size="30" />
                                        <div class="dh-logbook-row-mode-label">{{ $s->type }}</div>
                                    </div>
                                    <div class="dh-logbook-row-main d-block">
                                        <div class="dh-logbook-row-title"><span class="text-truncate">{{ $s->name }}</span></div>
                                        <div class="dh-logbook-row-sub text-sm text-secondary">
                                            {{ collect([$s->location, $s->country])->filter()->implode(', ') ?: '—' }}
                                            @if ($s->hasGps())
                                                &middot; <a href="https://www.google.com/maps?q={{ $s->latitude }},{{ $s->longitude }}" target="_blank" rel="noopener">{{ number_format($s->latitude, 5) }}, {{ number_format($s->longitude, 5) }}</a>
                                            @endif
                                        </div>
                                        <div class="dh-logbook-row-sub text-sm text-secondary">
                                            @php
                                                $facts = collect([
                                                    $s->min_cert ? 'Min ' . e($s->min_cert) : null,
                                                    $s->water_type ? ucfirst(e($s->water_type)) . ' water' : null,
                                                    $s->altitude_m !== null ? '<span data-unit-value="depth" data-metric="' . (int) $s->altitude_m . '"></span> altitude' : null,
                                                ])->filter();
                                            @endphp
                                            {!! $facts->isEmpty() ? '&mdash;' : $facts->implode(' &middot; ') !!}
                                        </div>
                                    </div>
                                    <div class="dh-logbook-row-stats">
                                        <div><strong>{{ $s->dives_count }}</strong><span class="text-xs text-secondary d-block">{{ $s->dives_count === 1 ? 'dive' : 'dives' }}</span></div>
                                    </div>
                                    <div class="dh-user-site-actions">
                                        <a href="{{ route('Logbook.sites.edit', $s->id) }}" class="dh-btn-icon" title="Edit"><span class="material-icons-round" aria-hidden="true">edit</span></a>
                                        <form method="POST" action="{{ route('Logbook.sites.destroy', $s->id) }}" class="d-inline" onsubmit="return confirm('Delete this site? Dives tagged with it keep the name as plain text.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dh-btn-icon dh-btn-icon-danger" title="Delete"><span class="material-icons-round" aria-hidden="true">delete</span></button>
                                        </form>
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

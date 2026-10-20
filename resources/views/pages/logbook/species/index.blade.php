<x-page-template bodyClass='dh-shell dh-logbook'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-shell.header title="Marine life" icon="logbook/species_reef_fish.svg" :h1="true" />

        <div class="container-fluid py-0 dh-board">
            <div class="row">
                <div class="col-12 pt-4">

                    @if (session('status'))
                        <div class="dh-logbook-flash" role="status"><span class="material-icons-round" aria-hidden="true">check_circle</span> {{ session('status') }}</div>
                    @endif

                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <a href="{{ route('Logbook') }}" class="dh-btn dh-btn-ghost-dark"><span class="material-icons-round" aria-hidden="true">arrow_back</span> Logbook</a>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('Species.create') }}" class="dh-btn dh-btn-primary"><span class="material-icons-round" aria-hidden="true">add</span> Add a species</a>
                        </div>
                    </div>

                    <p class="text-sm text-secondary">
                        Common marine life of South Florida. Tag a dive or one of its photos with what you saw, from the dive's page.
                        @if ($seenCount) You've logged <strong>{{ $seenCount }}</strong> {{ $seenCount === 1 ? 'species' : 'different species' }}. @endif
                    </p>

                    {{-- Share sightings: asked once on the first tag; changeable here. --}}
                    <form method="POST" action="{{ route('Species.shareSightings') }}" class="dh-species-share mb-3">
                        @csrf
                        <input type="hidden" name="share" value="{{ $shareSightings ? 0 : 1 }}">
                        <span class="material-icons-round" aria-hidden="true">{{ $shareSightings ? 'public' : 'lock' }}</span>
                        <span class="text-sm">
                            @if ($shareSightings === null) Your sightings are private. You'll be asked about sharing them the first time you tag a dive.
                            @elseif ($shareSightings) Your sightings (species, site and date - never the dive itself) are shared with the community.
                            @else Your sightings are private.
                            @endif
                        </span>
                        <button type="submit" class="dh-btn dh-btn-ghost-dark">{{ $shareSightings ? 'Stop sharing' : 'Share them' }}</button>
                    </form>

                    <div class="dh-logbook-search" role="search">
                        <span class="material-icons-round" aria-hidden="true">search</span>
                        <input type="search" id="speciesFilter" placeholder="Search by name, family, genus..." aria-label="Search species">
                    </div>
                    <label class="dh-species-seen-toggle mb-3">
                        <input type="checkbox" id="speciesSeenOnly"> Only species I've seen
                    </label>

                    @foreach ($groups as $slug => $g)
                        <section class="dh-species-group" data-species-group>
                            <h2 class="dh-panel-title"><span class="dh-cat-icon" style="--icon:url('{{ \App\Models\Species::categoryIconUrl($slug) }}')" aria-hidden="true"></span>{{ $g['label'] }} <span class="dh-region-count" data-group-count>{{ $g['species']->count() }}</span></h2>
                            <div class="dh-species-list">
                                @foreach ($g['species'] as $s)
                                    @php $sn = $seen[$s->id] ?? null; @endphp
                                    <a href="{{ route('Species.show', $s->uuid) }}" class="dh-species-row"
                                       data-species-row data-seen="{{ $sn ? 1 : 0 }}"
                                       data-text="{{ mb_strtolower(implode(' ', array_filter([$s->common_name, $s->scientific_name, $s->family, $s->genus, $s->taxon_order, implode(' ', $s->other_names ?? [])]))) }}">
                                        @if ($s->photoUrl())
                                            <img src="{{ $s->photoUrl() }}" alt="" class="dh-eq-thumb" loading="lazy">
                                        @else
                                            <span class="dh-eq-thumb is-icon"><span class="dh-cat-icon" style="--icon:url('{{ $s->iconUrl() }}')" aria-hidden="true"></span></span>
                                        @endif
                                        <span class="dh-species-name">
                                            <strong>{{ $s->common_name }}</strong>
                                            @if ($s->isCustom())<span class="dh-species-mine">Yours</span>@endif
                                            <em>{{ $s->scientific_name }}</em>
                                        </span>
                                        @if ($sn)
                                            <span class="dh-species-seen" title="Last seen {{ \Carbon\Carbon::parse($sn->last_seen)->format('M j, Y') }}">
                                                <span class="material-icons-round" aria-hidden="true">visibility</span>{{ $sn->n }}
                                            </span>
                                        @endif
                                        <span class="material-icons-round dh-logbook-row-chevron" aria-hidden="true">chevron_right</span>
                                    </a>
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                    <p class="text-sm text-secondary" id="speciesNone" hidden>Nothing matches. <a href="{{ route('Species.create') }}">Add it as your own species</a>.</p>

                </div>
            </div>
        </div>
    </main>

    @push('js')
        <script>
            (function () {
                var q = document.getElementById('speciesFilter');
                var seenOnly = document.getElementById('speciesSeenOnly');
                var none = document.getElementById('speciesNone');
                function apply() {
                    var terms = q.value.trim().toLowerCase().split(/\s+/).filter(Boolean);
                    var any = false;
                    document.querySelectorAll('[data-species-group]').forEach(function (g) {
                        var shown = 0;
                        g.querySelectorAll('[data-species-row]').forEach(function (r) {
                            var ok = (!seenOnly.checked || r.dataset.seen === '1') && terms.every(function (t) { return r.dataset.text.indexOf(t) !== -1; });
                            r.hidden = !ok; if (ok) shown++;
                        });
                        g.hidden = shown === 0;
                        g.querySelector('[data-group-count]').textContent = shown;
                        if (shown) any = true;
                    });
                    none.hidden = any;
                }
                q.addEventListener('input', apply);
                seenOnly.addEventListener('change', apply);
            })();
        </script>
    @endpush
</x-page-template>

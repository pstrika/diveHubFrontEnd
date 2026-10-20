<x-page-template bodyClass='dh-shell dh-logbook'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-shell.header title="Site journal" icon="auto_stories" :h1="true" />

        <div class="container-fluid py-0 dh-board">
            <div class="row">
                <div class="col-12 pt-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <a href="{{ route('Logbook') }}" class="dh-btn dh-btn-ghost-dark"><span class="material-icons-round" aria-hidden="true">arrow_back</span> Logbook</a>
                        <a href="{{ route('MyVisitedSites') }}" class="dh-btn dh-btn-ghost-dark"><span class="material-icons-round" aria-hidden="true">check_circle</span> My visited sites</a>
                    </div>

                    <p class="text-sm text-secondary">Every site you've logged a dive at, with your photos{{ $speciesOn ? ' and the marine life you saw' : '' }} there.</p>

                    @if ($rows->isEmpty())
                        <p class="text-sm text-secondary">None yet - tag your dives with a site and they show up here.</p>
                    @else
                        <div class="dh-logbook-search" role="search">
                            <span class="material-icons-round" aria-hidden="true">search</span>
                            <input type="search" id="journalFilter" placeholder="Find a site" aria-label="Find a site">
                        </div>
                        <div class="dh-species-list">
                            @foreach ($rows as $r)
                                <a href="{{ route('SiteJournal.show', [$r['kind'], $r['id']]) }}" class="dh-species-row" data-row data-text="{{ mb_strtolower($r['name'] . ' ' . $r['city']) }}">
                                    <span class="dh-species-name">
                                        <strong>{{ $r['name'] }}</strong>
                                        @if ($r['kind'] === 'mine')<span class="dh-species-mine">Your site</span>@endif
                                        <em>{{ $r['city'] }}{{ $r['city'] ? ' · ' : '' }}last {{ $r['last']->format('M j, Y') }}</em>
                                    </span>
                                    @if ($r['photos'])<span class="dh-species-seen" title="Photos"><span class="material-icons-round" aria-hidden="true">photo_library</span>{{ $r['photos'] }}</span>@endif
                                    @if ($speciesOn && $r['species'])<span class="dh-species-seen" title="Species seen"><span class="dh-cat-icon" style="--icon:url('{{ asset('assets/img/icons/logbook/species_reef_fish.svg') }}')" aria-hidden="true"></span>{{ $r['species'] }}</span>@endif
                                    <span class="dh-species-seen" title="Dives"><span class="material-icons-round" aria-hidden="true">scuba_diving</span>{{ $r['dives'] }}</span>
                                    <span class="material-icons-round dh-logbook-row-chevron" aria-hidden="true">chevron_right</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>

    @push('js')
        <script>
            (function () {
                var q = document.getElementById('journalFilter');
                if (!q) return;
                q.addEventListener('input', function () {
                    var t = q.value.trim().toLowerCase();
                    document.querySelectorAll('[data-row]').forEach(function (r) { r.hidden = t && r.dataset.text.indexOf(t) === -1; });
                });
            })();
        </script>
    @endpush
</x-page-template>

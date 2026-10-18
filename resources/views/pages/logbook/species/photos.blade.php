<x-page-template bodyClass='dh-shell dh-logbook'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-shell.header title="Picture Management" icon="photo_camera" />

        <div class="container-fluid py-0">
            <div class="row">
                <div class="col-lg-10 col-12 m-auto pt-4">
            {{-- Picture Management: diver site photos | species pictures (Pablo, 2026-10-18). --}}
            <nav class="dh-sp-tabs mb-3" aria-label="Picture type">
                <a href="{{ route('DiverPhotos.manage.index') }}" class="{{ request()->routeIs('DiverPhotos.manage.index') ? 'is-active' : '' }}">Diver site photos</a>
                <a href="{{ route('Species.photoReview') }}" class="{{ request()->routeIs('Species.photoReview') ? 'is-active' : '' }}">Species pictures</a>
            </nav>

                    <p class="text-sm text-secondary">
                        The picture each species shows: your own photo, or a freely licensed one from Wikimedia Commons (commercial use allowed, credit shown under it) until you have yours.
                        <strong>Keep</strong> the good Commons ones; <strong>skip</strong> aquarium, market or museum shots and the species stays blank. Divers tag species on their dives and photos, but never set these pictures.
                    </p>

                    <nav class="dh-sp-tabs mb-3" aria-label="Filter">
                        @foreach (['candidate' => 'To review', 'approved' => 'Kept', 'rejected' => 'Skipped', 'missing' => 'No picture found', 'own' => 'My photos', 'not_own' => 'Without my photo'] as $k => $label)
                            <a href="{{ route('Species.photoReview', ['show' => $k]) }}" class="{{ $filter === $k ? 'is-active' : '' }}">{{ $label }} <span>{{ $counts[$k] ?? 0 }}</span></a>
                        @endforeach
                    </nav>

                    @if ($species->isEmpty())
                        <p class="text-sm text-secondary">Nothing here.</p>
                    @endif

                    <div class="dh-sp-review">
                        @foreach ($species as $s)
                            <article class="dh-sp-card" data-uuid="{{ $s->uuid }}">
                                @if ($s->photo)
                                    <img src="{{ asset('assets/img/species/' . $s->photo) }}" alt="{{ $s->common_name }}" loading="lazy">
                                @else
                                    <div class="dh-sp-card-empty"><span class="material-icons-round" aria-hidden="true">image_not_supported</span></div>
                                @endif
                                <div class="dh-sp-card-body">
                                    <strong>{{ $s->common_name }} @if ($s->photo_origin === 'own')<span class="dh-species-mine">Your photo</span>@endif</strong>
                                    <em>{{ $s->scientific_name }}</em>
                                    @if ($s->photo_origin !== 'own' && ($s->photo_credit || $s->photo_license))
                                        <a class="dh-sp-card-credit" href="{{ $s->photo_source_url }}" target="_blank" rel="noopener">{{ $s->photo_credit }} &middot; {{ $s->photo_license }}</a>
                                    @endif
                                </div>
                                @if ($s->photo)
                                    <div class="dh-sp-card-actions">
                                        <button type="button" class="dh-btn dh-btn-ghost-dark dh-sp-flip" data-flip title="Mirror left-right"><span class="material-icons-round" aria-hidden="true">flip</span></button>
                                        <button type="button" class="dh-btn {{ $s->photo_status === 'approved' ? 'dh-btn-primary' : 'dh-btn-ghost-dark' }}" data-decision="approved"><span class="material-icons-round" aria-hidden="true">check</span>Keep</button>
                                        <button type="button" class="dh-btn {{ $s->photo_status === 'rejected' ? 'dh-btn-primary' : 'dh-btn-ghost-dark' }}" data-decision="rejected"><span class="material-icons-round" aria-hidden="true">close</span>Skip</button>
                                    </div>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </main>

    @push('js')
        <script>
            (function () {
                var url = "{{ url('DiverPhotos-Admin/species') }}/";
                var csrf = "{{ csrf_token() }}";
                // Mirror left-right (fish face right, Pablo 2026-10-18).
                document.querySelectorAll('.dh-sp-card [data-flip]').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        var card = btn.closest('.dh-sp-card');
                        card.classList.add('is-busy');
                        fetch(url + card.dataset.uuid + '/flip', {
                            method: 'POST', credentials: 'same-origin',
                            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                        }).then(function (r) { if (!r.ok) throw new Error('Could not mirror'); return r.json(); })
                          .then(function (d) { card.querySelector('img').src = d.url; card.classList.remove('is-busy'); })
                          .catch(function (e) { card.classList.remove('is-busy'); alert(e.message); });
                    });
                });
                document.querySelectorAll('.dh-sp-card [data-decision]').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        var card = btn.closest('.dh-sp-card');
                        var decision = btn.getAttribute('data-decision');
                        card.classList.add('is-busy');
                        fetch(url + card.dataset.uuid + '/decide', {
                            method: 'POST', credentials: 'same-origin',
                            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                            body: JSON.stringify({ decision: decision }),
                        }).then(function (r) {
                            if (!r.ok) throw new Error('Could not save');
                            card.classList.remove('is-busy', 'is-approved', 'is-rejected');
                            card.classList.add('is-' + decision);
                            card.querySelectorAll('[data-decision]').forEach(function (b) {
                                b.classList.toggle('dh-btn-primary', b === btn);
                                b.classList.toggle('dh-btn-ghost-dark', b !== btn);
                            });
                        }).catch(function (e) { card.classList.remove('is-busy'); alert(e.message); });
                    });
                });
            })();
        </script>
    @endpush
</x-page-template>

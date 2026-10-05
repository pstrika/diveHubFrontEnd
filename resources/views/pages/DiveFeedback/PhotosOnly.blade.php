{{--
    Reached via the SAME /dive-feedback/{token} link as the main wizard -
    DiveFeedbackController::show() renders this instead once the diver has
    finished everything else (completed_at set) but asked to be reminded
    about photos specifically. Deliberately NOT the full wizard: Pablo,
    2026-10-05: "It only should allow for picture uploads - not all the
    review process if he completed the rest."
--}}
<x-page-template bodyClass='dh-shell bg-gray-200'>
    <header class="dh-topbar">
        <div class="dh-topbar-inner">
            <a class="dh-brand" href="{{ url('/') }}" aria-label="Divers Hub home">
                <img src="{{ asset('assets') }}/img/logos/logo_circle.png" alt="" width="34" height="34">
                <span>Divers Hub</span>
            </a>
        </div>
    </header>

    <main class="main-content mt-0">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <div class="container-fluid py-0 dh-board">
            <div class="dh-wizard-card">
                @php $firstName = $diver && $diver->name ? explode(' ', trim($diver->name))[0] : null; @endphp
                <h4 class="dh-wizard-greeting">{{ $firstName ? $firstName . ', got' : 'Got' }} a minute for photos?</h4>

                <div class="dh-wizard-facts">
                    @if($event && $event->date)
                        <span class="chip chip-static"><span class="material-icons-round" aria-hidden="true">event</span>{{ \Carbon\Carbon::parse($event->date)->format('D, M j') }}</span>
                    @endif
                    @if($operator)
                        <span class="chip chip-static"><span class="material-icons-round" aria-hidden="true">store</span>{{ $operator->operatorName }}</span>
                    @endif
                    @if($site)
                        <span class="chip chip-static"><span class="material-icons-round" aria-hidden="true">place</span>{{ $site->name }}</span>
                    @endif
                </div>

                <div class="dh-profile-card">
                    <div class="dh-profile-card-body">
                        <h6 class="dh-wizard-panel-title">Add a photo or two</h6>
                        <p class="dh-wizard-panel-sub">Shown publicly on the site's page once approved. Thanks for everything else you already shared!</p>

                        @if(!$site)
                            <p class="text-sm">No site on file for this dive, so photo upload isn't available here.</p>
                        @else
                            <div class="dropzone dh-dropzone" id="dh-dive-dropzone"></div>
                            <p class="text-xs text-secondary mt-2 mb-0">JPG, PNG or WebP, up to 20 MB each.</p>
                            <p class="dh-wizard-status" id="dh-dive-photo-status"></p>
                        @endif
                        {{-- Same trap as the main wizard's Finish state - see
                             its comment on #dh-dcf-close for why this isn't
                             just window.close() alone (Pablo, 2026-10-05). --}}
                        <button type="button" class="dh-btn dh-btn-ghost-dark" id="dh-dcf-close" style="margin-top: 14px;">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        document.getElementById('dh-dcf-close').addEventListener('click', function () {
            try { window.close(); } catch (e) { /* ignore */ }
            setTimeout(function () { window.location.href = '{{ url('/') }}'; }, 150);
        });
    </script>
    @if($site)
    <script src="{{ asset('assets') }}/js/plugins/dropzone.min.js"></script>
    <script>
        (function () {
            var token = @json($token);
            var csrf = document.querySelector('meta[name="csrf-token"]').content;

            Dropzone.autoDiscover = false;
            var photoStatus = document.getElementById('dh-dive-photo-status');
            var dz = new Dropzone('#dh-dive-dropzone', {
                url: '{{ url('dive-feedback') }}/' + encodeURIComponent(token) + '/photos',
                maxFilesize: 20,
                acceptedFiles: '.jpeg,.jpg,.png,.webp',
                parallelUploads: 4,
                addRemoveLinks: true,
                paramName: 'photo',
                dictDefaultMessage: 'Drop pictures here or tap to choose',
                headers: { 'X-CSRF-TOKEN': csrf },
            });
            var okCount = 0;
            dz.on('success', function () { okCount++; photoStatus.textContent = okCount + ' photo' + (okCount === 1 ? '' : 's') + ' added. Thanks!'; });
            dz.on('error', function (file, message) {
                var detail = (message && message.message) ? message.message : (typeof message === 'string' ? message : null);
                photoStatus.textContent = 'That upload did not go through' + (detail ? ' (' + detail + ')' : '') + '.';
            });
        })();
    </script>
    @endif
</x-page-template>

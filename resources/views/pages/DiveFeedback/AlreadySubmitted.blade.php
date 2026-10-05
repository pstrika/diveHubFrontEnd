{{--
    Reached via /dive-feedback/{token} once the diver has already finished
    the review (completed_at set) and isn't waiting on a photo reminder -
    the link stays valid (doesn't 404/expire early) but stops offering the
    review steps again (Pablo, 2026-10-05).
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
        <div class="container-fluid py-0 dh-board">
            <div class="dh-wizard-card">
                @php $firstName = $diver && $diver->name ? explode(' ', trim($diver->name))[0] : null; @endphp
                <h4 class="dh-wizard-greeting">Thanks{{ $firstName ? ', ' . $firstName : '' }}!</h4>

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
                    <div class="dh-profile-card-body text-center" style="padding: 40px 16px;">
                        <i class="material-icons-round" style="font-size: 2.5rem; color: var(--dh-sea);">task_alt</i>
                        <h6 class="dh-panel-title" style="text-transform: none; font-size: 1.1rem; margin-top: 12px;">You already shared your feedback for this dive.</h6>
                        <p class="text-sm text-secondary">Thanks for helping other divers plan their trip.</p>
                        {{-- Same trap as the main wizard's Finish state - see
                             its comment on #dh-dcf-close for why this isn't
                             just window.close() alone (Pablo, 2026-10-05). --}}
                        <button type="button" class="dh-btn dh-btn-primary" id="dh-dcf-close" style="margin-top: 18px;">Close</button>
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
</x-page-template>

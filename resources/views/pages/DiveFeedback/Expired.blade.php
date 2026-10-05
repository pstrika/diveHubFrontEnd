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
                <div class="dh-profile-card">
                    <div class="dh-profile-card-body text-center" style="padding: 40px 16px;">
                        <i class="material-icons-round" style="font-size: 2.5rem; color: var(--dh-muted);">schedule</i>
                        <h6 class="dh-panel-title" style="text-transform: none; font-size: 1.1rem; margin-top: 12px;">This link has expired</h6>
                        <p class="text-sm text-secondary">Feedback links are only good for 7 days after a dive. You can still rate the site or operator directly from their pages.</p>
                        <a class="dh-btn dh-btn-primary" href="{{ url('/') }}" style="margin-top: 10px;">Go to Divers Hub</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</x-page-template>

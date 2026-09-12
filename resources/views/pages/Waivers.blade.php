<x-page-template bodyClass='dh-shell bg-gray-200' :SEO="$SEO ?? []">
    <x-shell.nav active="" />

    <main class="main-content position-relative h-100 border-radius-lg">
        <x-shell.header title="Online Waivers" icon="assignment" />

        <div class="container-fluid py-0 dh-board">
            <section class="dh-panel">
                <p class="dh-note">Sign an operator's liability waiver online before your trip, if they offer one.</p>
                <div class="dh-waiver-grid">
                    @foreach($operators as $operator)
                        @if($operator->waiverLink)
                            <a href="{{ $operator->waiverLink }}" class="dh-waiver-card" target="_blank" rel="noopener">
                                <img src="{{ asset('assets') }}{{ $operator->logoUrl }}" alt="" class="dh-waiver-logo">
                                <span class="dh-waiver-name">{{ $operator->operatorName }}</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </section>

            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>
</x-page-template>

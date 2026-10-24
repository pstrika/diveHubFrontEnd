{{-- Index of the schedule pages per area and kind of dive (SEO audit, 2026-10-24). --}}
<x-page-template bodyClass='dh-shell bg-gray-200' :SEO="$SEO">
    <x-shell.nav active="today" />

    <main class="main-content position-relative h-100 border-radius-lg">
        <x-shell.header title="South Florida dive trips by area" :h1="true" />
        <div class="container-fluid py-0 dh-board">
            <section class="dh-dtrips-intro">
                <p>Divers Hub puts every South Florida dive operator's schedule on one calendar. Pick an area or a kind of dive to see the next {{ \App\Http\Controllers\DiveTripsController::DAYS }} days of departures, or open <a href="{{ route('Trips') }}">today's full schedule</a>.</p>
            </section>
            @foreach (['area' => 'By area', 'theme' => 'By kind of dive'] as $kind => $heading)
                <section class="dh-dtrips-more">
                    <h2 class="dh-day-title">{{ $heading }}</h2>
                    <ul class="dh-dtrips-grid">
                        @foreach ($pages as $s => $p)
                            @continue($p['kind'] !== $kind)
                            <li>
                                <a href="{{ route('DiveTrips.show', $s) }}"><strong>{{ $p['kind'] === 'area' ? $p['name'] : $p['name'] }}</strong></a>
                                <span>{{ $counts[$s] }} {{ Str::plural('trip', $counts[$s]) }} in the next {{ \App\Http\Controllers\DiveTripsController::DAYS }} days</span>
                                <p>{{ $p['desc'] }}</p>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>
</x-page-template>

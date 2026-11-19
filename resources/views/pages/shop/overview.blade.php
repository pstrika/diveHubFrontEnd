{{-- Shop portal: overview. Counts of what the shop has set up, next steps, and what is coming. --}}
<x-shop.page title="Shop portal">
    <x-shop.tabs :operator="$operator" :shopName="$shopName" tab="overview" />

    <div class="dh-shop-stats">
        <div class="dh-shop-stat"><b>{{ $locationCount }}</b><span>{{ Str::plural('location', $locationCount) }} on your page</span></div>
        <div class="dh-shop-stat"><b>{{ $offeringCount }}</b><span>{{ Str::plural('service', $offeringCount) }} and prices listed</span></div>
        <div class="dh-shop-stat"><b>{{ $homeDivers }}</b><span>{{ Str::plural('diver', $homeDivers) }} with you as home shop</span></div>
        <div class="dh-shop-stat"><b>{{ $scanCount }}</b><span>QR {{ Str::plural('scan', $scanCount) }} from {{ $codeCount }} {{ Str::plural('code', $codeCount) }}</span></div>
    </div>

    <section class="dh-panel">
        <h2 class="dh-panel-title">Getting set up</h2>
        <div class="dh-shop-scroll">
            <table class="dh-shop-table">
                <tbody>
                    <tr>
                        <td><b>1. Check your shop details</b><br><span class="dh-shop-muted">Name, phone, website and a short description, as divers see them.</span></td>
                        <td class="is-num"><a class="dh-btn dh-btn-ghost-dark" href="{{ route('shop-portal.profile', ['operator' => $operator->id]) }}">Shop details</a></td>
                    </tr>
                    <tr>
                        <td><b>2. Add your storefront</b><br><span class="dh-shop-muted">Address and hours. Divers can only pick you as their home shop once you have one.</span></td>
                        <td class="is-num"><a class="dh-btn dh-btn-ghost-dark" href="{{ route('shop-portal.locations', ['operator' => $operator->id]) }}">Locations</a></td>
                    </tr>
                    <tr>
                        <td><b>3. List what you offer</b><br><span class="dh-shop-muted">Fills, rentals, classes and service, with prices you have confirmed.</span></td>
                        <td class="is-num"><a class="dh-btn dh-btn-ghost-dark" href="{{ route('shop-portal.locations', ['operator' => $operator->id]) }}">Prices</a></td>
                    </tr>
                    <tr>
                        <td><b>4. Put a QR code by the register</b><br><span class="dh-shop-muted">A diver who scans it lands on your page. If they sign up, you are their home shop.</span></td>
                        <td class="is-num"><a class="dh-btn dh-btn-ghost-dark" href="{{ route('shop-portal.codes', ['operator' => $operator->id]) }}">QR codes</a></td>
                    </tr>
                    <tr>
                        <td><b>Check your boats</b><br><span class="dh-shop-muted">Capacity, size and type for each boat on your page.</span></td>
                        <td class="is-num"><a class="dh-btn dh-btn-ghost-dark" href="{{ route('shop-portal.boats', ['operator' => $operator->id]) }}">Boats</a></td>
                    </tr>
                    <tr>
                        <td><b>5. See how divers find you</b><br><span class="dh-shop-muted">Who calls you home, trips saved to calendars, visits to your page.</span></td>
                        <td class="is-num"><a class="dh-btn dh-btn-ghost-dark" href="{{ route('shop-portal.insights', ['operator' => $operator->id]) }}">Insights</a></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <section class="dh-panel">
        <h2 class="dh-panel-title">Coming soon</h2>
        <div class="dh-shop-soon">
            <div class="dh-shop-soon-tile"><h3><span class="material-icons-round" aria-hidden="true">lock</span>Promotions</h3><p>Send an offer to the divers who call you their home shop.</p></div>
            <div class="dh-shop-soon-tile"><h3><span class="material-icons-round" aria-hidden="true">lock</span>Text sign-up offer</h3><p>An offer divers can opt in to by text.</p></div>
            <div class="dh-shop-soon-tile"><h3><span class="material-icons-round" aria-hidden="true">lock</span>Competitions</h3><p>Run a derby or a cleanup with a live leaderboard.</p></div>
        </div>
    </section>
</x-shop.page>

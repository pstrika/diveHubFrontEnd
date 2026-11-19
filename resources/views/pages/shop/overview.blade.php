{{-- Shop portal: overview. Counts of what the shop has set up, next steps, and what is coming. --}}
<x-shop.page title="Shop portal">
    <x-shop.tabs :operator="$operator" :shopName="$shopName" tab="overview" />

    <div class="dh-shop-stats">
        <div class="dh-shop-stat"><b>{{ $locationCount }}</b><span>{{ Str::plural('location', $locationCount) }} on your page</span></div>
        <div class="dh-shop-stat"><b>{{ $offeringCount }}</b><span>{{ Str::plural('service', $offeringCount) }} and prices listed</span></div>
        <div class="dh-shop-stat"><b>{{ $homeDivers }}</b><span>{{ Str::plural('diver', $homeDivers) }} with you as home shop</span></div>
        <div class="dh-shop-stat"><b>{{ $scanCount }}</b><span>QR {{ Str::plural('scan', $scanCount) }} from {{ $codeCount }} {{ Str::plural('code', $codeCount) }}</span></div>
    </div>

    {{-- Setup checklist (Zach, 2026-11-19): each step shows whether it is
         done, the storefront is marked as the one everything else waits on,
         and QR codes stay closed until it exists. --}}
    @php
        $steps = [
            ['done' => $hasStorefront, 'title' => 'Add your storefront', 'text' => 'Address and hours. This is the step everything else waits on: divers can only pick you as their home shop, and you can only make QR codes, once you have one.', 'label' => 'Add storefront', 'href' => route('shop-portal.locations', ['operator' => $operator->id]), 'key' => true],
            ['done' => $detailsDone, 'title' => 'Check your shop details', 'text' => 'Name, phone, website, fills and a short description, as divers see them.', 'label' => 'Shop details', 'href' => route('shop-portal.profile', ['operator' => $operator->id])],
            ['done' => $offeringCount > 0, 'title' => 'List what you offer', 'text' => 'Fills, rentals, classes and service, with prices you have confirmed.', 'label' => 'Prices', 'href' => route('shop-portal.locations', ['operator' => $operator->id]), 'needs' => true],
            ['done' => $codeCount > 0, 'title' => 'Put a QR code by the register', 'text' => 'A diver who scans it lands on your page. If they sign up, you are their home shop.', 'label' => 'QR codes', 'href' => route('shop-portal.codes', ['operator' => $operator->id]), 'needs' => true],
        ];
        $next = collect($steps)->search(fn ($s) => !$s['done']);
    @endphp
    <section class="dh-panel">
        <h2 class="dh-panel-title">Getting set up</h2>
        <ol class="dh-shop-steps">
            @foreach($steps as $i => $step)
                @php $blocked = !empty($step['needs']) && !$hasStorefront; @endphp
                <li class="dh-shop-step {{ $step['done'] ? 'is-done' : '' }} {{ $next === $i ? 'is-next' : '' }} {{ $blocked ? 'is-blocked' : '' }}">
                    <span class="dh-shop-step-mark" aria-hidden="true"><span class="material-icons-round">{{ $step['done'] ? 'check' : ($blocked ? 'lock' : 'radio_button_unchecked') }}</span></span>
                    <div class="dh-shop-step-body">
                        <b>{{ $i + 1 }}. {{ $step['title'] }}</b>
                        @if($step['done'])<span class="dh-shop-step-tag is-done">Done</span>
                        @elseif($blocked)<span class="dh-shop-step-tag">Needs your storefront</span>
                        @elseif($next === $i)<span class="dh-shop-step-tag is-next">Do this next</span>@endif
                        <p>{{ $step['text'] }}</p>
                    </div>
                    @if($blocked)
                        <span class="dh-btn dh-btn-ghost-dark is-disabled" aria-disabled="true">{{ $step['label'] }}</span>
                    @else
                        <a class="dh-btn {{ $next === $i ? 'dh-btn-primary' : 'dh-btn-ghost-dark' }}" href="{{ $step['href'] }}">{{ $step['done'] ? 'Edit' : $step['label'] }}</a>
                    @endif
                </li>
            @endforeach
        </ol>
        <div class="dh-shop-scroll">
            <table class="dh-shop-table">
                <tbody>
                    <tr>
                        <td><b>Check your boats</b><br><span class="dh-shop-muted">Capacity, size and type for each boat on your page.</span></td>
                        <td class="is-num"><a class="dh-btn dh-btn-ghost-dark" href="{{ route('shop-portal.boats', ['operator' => $operator->id]) }}">Boats</a></td>
                    </tr>
                    <tr>
                        <td><b>See how divers find you</b><br><span class="dh-shop-muted">Who calls you home, trips saved to calendars, visits to your page.</span></td>
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

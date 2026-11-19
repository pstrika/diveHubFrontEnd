{{--
    Shop portal: insights, first pass (Zach, 2026-11-19).

    Only numbers Divers Hub has a record of, and each one says exactly what
    it counts. A trip a diver "marked booked" is the diver's own tick, not a
    booking the shop or Divers Hub can confirm, so it is never called a
    booking. Totals only: no diver is named.
--}}
@php
    $maxDay = max(1, max($byDay));
    $halfTotal = $am + $pm;
@endphp
<x-shop.page title="Shop portal">
    <x-shop.tabs :operator="$operator" :shopName="$shopName" tab="insights" />

    <section class="dh-panel">
        <h2 class="dh-panel-title">Your divers</h2>
        <div class="dh-shop-stats">
            <div class="dh-shop-stat"><b>{{ $homeDivers }}</b><span>{{ Str::plural('diver', $homeDivers) }} call you their home shop</span></div>
            <div class="dh-shop-stat"><b>{{ $favourites }}</b><span>have you as a favorite operator</span></div>
            <div class="dh-shop-stat"><b>{{ $ratingCount ? number_format($ratingAvg, 1) : 'n/a' }}</b><span>average rating from {{ $ratingCount }} {{ Str::plural('review', $ratingCount) }}</span></div>
            <div class="dh-shop-stat"><b>{{ $signups }}</b><span>{{ Str::plural('diver', $signups) }} joined Divers Hub from your QR codes</span></div>
        </div>
    </section>

    <section class="dh-panel">
        <h2 class="dh-panel-title">Your trips</h2>
        <div class="dh-shop-stats">
            <div class="dh-shop-stat"><b>{{ $listed }}</b><span>upcoming {{ Str::plural('trip', $listed) }} listed on Divers Hub</span></div>
            <div class="dh-shop-stat"><b>{{ $upcomingSaved }}</b><span>upcoming trip {{ Str::plural('spot', $upcomingSaved) }} saved to a diver's calendar</span></div>
            <div class="dh-shop-stat"><b>{{ $upcomingBooked }}</b><span>of those the diver has marked as booked</span></div>
            <div class="dh-shop-stat"><b>{{ $saved30 }}</b><span>{{ Str::plural('trip', $saved30) }} saved in the last 30 days</span></div>
        </div>
        <p class="dh-shop-muted mb-0 mt-2">{{ $upcomingDivers }} different {{ Str::plural('diver', $upcomingDivers) }} have one of your upcoming trips on their calendar. "Marked as booked" is the diver's own tick in Divers Hub. It is not a confirmed booking.</p>
    </section>

    <section class="dh-panel">
        <h2 class="dh-panel-title">When divers go out with you</h2>
        @if($halfTotal === 0)
            <p class="dh-shop-muted mb-0">No trips of yours have been saved to a calendar in the last 12 months yet.</p>
        @else
            <p class="dh-shop-muted">Trips of yours saved to a diver's calendar over the last 12 months, by the day of the trip.</p>
            <div class="dh-shop-bars" role="img" aria-label="Saved trips by day of the week">
                @foreach($byDay as $day => $n)
                    <div class="dh-shop-bar">
                        <span class="dh-shop-bar-n">{{ $n }}</span>
                        <span class="dh-shop-bar-fill" style="height: {{ max(4, (int) round($n / $maxDay * 100)) }}%"></span>
                        <span class="dh-shop-bar-label">{{ $day }}</span>
                    </div>
                @endforeach
            </div>
            <p class="dh-shop-muted mb-0 mt-2">{{ (int) round($am / $halfTotal * 100) }}% morning departures, {{ (int) round($pm / $halfTotal * 100) }}% afternoon or evening.</p>
        @endif
    </section>

    <section class="dh-panel">
        <h2 class="dh-panel-title">Attention, last 30 days</h2>
        @if(!$countingSince)
            <p class="dh-shop-muted mb-0">Divers Hub has only just started counting visits to your page and trips. Numbers will appear here as divers look at them.</p>
        @else
            <div class="dh-shop-stats">
                <div class="dh-shop-stat"><b>{{ $views30 }}</b><span>{{ Str::plural('visit', $views30) }} to your shop page</span></div>
                <div class="dh-shop-stat"><b>{{ $tripViews30 }}</b><span>times one of your trips was opened</span></div>
                <div class="dh-shop-stat"><b>{{ $waiver30 }}</b><span>{{ Str::plural('click', $waiver30) }} through to your waiver</span></div>
                <div class="dh-shop-stat"><b>{{ $scans30 }}</b><span>QR {{ Str::plural('scan', $scans30) }} ({{ $scansAll }} all time)</span></div>
            </div>
            @if($topTrips->isNotEmpty())
                <h3 class="dh-panel-subtitle mt-3">Trips divers opened most</h3>
                <div class="dh-shop-scroll">
                    <table class="dh-shop-table">
                        <tbody>
                            @foreach($topTrips as $name => $n)
                                <tr><td>{{ $name }}</td><td class="is-num">{{ $n }} {{ Str::plural('time', $n) }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
            <p class="dh-shop-muted mb-0 mt-2">Counted since {{ $countingSince->format('M j, Y') }}. Your own visits are not counted, and neither are search engines.</p>
        @endif
    </section>

    <section class="dh-panel">
        <h2 class="dh-panel-title">Google reviews</h2>
        @if(!$googleSet)
            <p class="dh-shop-muted mb-0">Divers Hub can ask your divers for a Google review after they dive with you. It is off until you add your link and switch it on under <a href="{{ route('shop-portal.profile', ['operator' => $operator->id]) }}">Shop details</a>.</p>
        @else
            <div class="dh-shop-stats">
                <div class="dh-shop-stat"><b>{{ $google30 }}</b><span>{{ Str::plural('diver', $google30) }} tapped "Review on Google" in the last 30 days</span></div>
                <div class="dh-shop-stat"><b>{{ $googleAll }}</b><span>all time</span></div>
            </div>
            <p class="dh-shop-muted mb-0 mt-2">This counts taps on the button. Divers Hub cannot see whether a review was posted. The button is offered to every diver who finishes the follow-up, whatever they rated, and nothing is given in return for a review.</p>
        @endif
    </section>

    <section class="dh-panel">
        <h2 class="dh-panel-title">Coming</h2>
        <p class="dh-shop-muted mb-0">Which dive sites your divers pick, how far ahead they plan, how you compare with the area, and how many clicked through to book. Divers Hub is collecting what it needs for these now.</p>
    </section>
</x-shop.page>

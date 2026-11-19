{{--
    "My home shop" (HomeShopController@index): what it is, change it,
    remove it. The locked rows are what is planned for divers but not
    built (Zach, 2026-11-13); they are disabled and have no route.
--}}
<x-shop.page title="My home shop">
    <section class="dh-panel">
        <h2 class="dh-panel-title">Your home shop</h2>
        @if($home)
            <div class="dh-shop-head">
                @if($home['operator']->logoUrl)<img src="{{ asset('assets') }}{{ $home['operator']->logoUrl }}" alt="">@endif
                <div>
                    <h2>{{ $home['name'] }}</h2>
                    <p>{{ $home['location']->addressLine() }}</p>
                </div>
                <a class="dh-btn dh-btn-ghost-dark" href="{{ route('OperatorDetails', ['id' => $home['operator']->slug ?: $home['operator']->id]) }}">Shop page</a>
            </div>
            <p class="dh-shop-muted">Their trips show first on your dive calendar, marked "Your home shop". Nothing else changes: every other boat is still listed.</p>
            <form method="POST" action="{{ route('home-shop.remove') }}">
                @csrf
                <button type="submit" class="dh-btn dh-btn-ghost-dark">Remove home shop</button>
            </form>
        @else
            <p class="dh-shop-muted">You have not picked one. Your home shop's trips show first on your dive calendar.</p>
        @endif
    </section>

    <section class="dh-panel">
        <h2 class="dh-panel-title">{{ $home ? 'Change it' : 'Pick one' }}</h2>
        @if($choices->isEmpty())
            <p class="dh-shop-muted mb-0">No shop has set up a storefront on Divers Hub yet.</p>
        @else
            <form method="POST" action="{{ route('home-shop.set') }}">
                @csrf
                <div class="dh-shop-form">
                    <div class="dh-shop-field">
                        <label for="hs-pick">Shop</label>
                        <select id="hs-pick" name="location_id" required>
                            @foreach($choices as $c)
                                <option value="{{ $c['id'] }}" @selected($home && $home['location']->id === $c['id'])>{{ $c['label'] }}</option>
                            @endforeach
                        </select>
                        <small>Only shops that have added their storefront are listed.</small>
                    </div>
                </div>
                <div class="dh-shop-actions"><button type="submit" class="dh-btn dh-btn-primary">{{ $home ? 'Switch' : 'Set as my home shop' }}</button></div>
            </form>
        @endif
    </section>

    <section class="dh-panel">
        <h2 class="dh-panel-title">Coming soon</h2>
        <div class="dh-shop-soon">
            <div class="dh-shop-soon-tile"><h3><span class="material-icons-round" aria-hidden="true">lock</span>Offers from my home shop</h3><p>Choose whether your home shop can send you offers.</p></div>
            <div class="dh-shop-soon-tile"><h3><span class="material-icons-round" aria-hidden="true">lock</span>Competitions</h3><p>Derbies and cleanups your home shop runs, with a live leaderboard.</p></div>
        </div>
    </section>
</x-shop.page>

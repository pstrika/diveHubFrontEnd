{{--
    What a member sees after scanning a shop's code (HomeShopController@offer).
    $state:
      set      new account, the shop was set for them: told, with a way out
      make     existing member with no home shop: asked
      switch   existing member with another home shop: asked
    A home shop is never changed here without a button being pressed.
--}}
<x-shop.page title="Home shop">
    <section class="dh-panel dh-shop-center">
        @if($state === 'set')
            <h2>{{ $shopName }} is your home shop</h2>
            <p>You joined from their code, so their trips show first on your dive calendar. You can change or remove it any time.</p>
            <div class="dh-shop-actions">
                <a class="dh-btn dh-btn-primary" href="{{ route('Trips') }}">See the dive calendar</a>
                <a class="dh-btn dh-btn-ghost-dark" href="{{ route('home-shop') }}">Change it</a>
            </div>
        @else
            <h2>{{ $state === 'switch' ? 'Switch your home shop to ' . $shopName . '?' : 'Make ' . $shopName . ' your home shop?' }}</h2>
            @if($state === 'switch')
                <p>Your home shop is {{ $current['name'] }}. Your home shop's trips show first on your dive calendar.</p>
            @else
                <p>Your home shop's trips show first on your dive calendar. You can change or remove it any time.</p>
            @endif
            <div class="dh-shop-actions">
                <form method="POST" action="{{ route('home-shop.answer') }}">@csrf<button type="submit" name="answer" value="yes" class="dh-btn dh-btn-primary">{{ $state === 'switch' ? 'Switch' : 'Yes' }}</button></form>
                <form method="POST" action="{{ route('home-shop.answer') }}">@csrf<button type="submit" name="answer" value="no" class="dh-btn dh-btn-ghost-dark">{{ $state === 'switch' ? 'Keep ' . $current['name'] : 'Not now' }}</button></form>
            </div>
        @endif
    </section>
</x-shop.page>

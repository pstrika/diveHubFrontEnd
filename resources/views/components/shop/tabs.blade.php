{{--
    Shop portal heading and tabs (Zach, 2026-11-13).

    The locked tabs are what is planned but not built: boats, promotions,
    the text sign-up offer, insights and competitions (Zach: "put the
    buttons with locks to indicate it will be a future feature"). They are
    disabled buttons with no route behind them, so there is nothing to
    reach by guessing a URL.
--}}
@props(['operator', 'shopName', 'tab'])

@php
    $tabs = [
        'overview'  => ['Overview', 'dashboard', 'shop-portal.overview'],
        'profile'   => ['Shop details', 'badge', 'shop-portal.profile'],
        'locations' => ['Locations and prices', 'place', 'shop-portal.locations'],
        'boats'     => ['Boats', 'directions_boat', 'shop-portal.boats'],
        'codes'     => ['QR codes', 'qr_code_2', 'shop-portal.codes'],
        'insights'  => ['Insights', 'insights', 'shop-portal.insights'],
    ];
    $locked = ['Promotions', 'Text sign-up offer', 'Competitions'];
    // QR codes wait on a storefront. The tab still opens, and its page says why.
    try {
        $qrWaiting = \App\Support\HomeShop::defaultLocationFor((int) $operator->id) === null;
    } catch (\Throwable $e) {
        $qrWaiting = false;
    }
@endphp

@php
    // A Divers Hub admin working on a shop they do not hold a grant for.
    $dhUser = auth()->user();
    $asPlatform = $dhUser && method_exists($dhUser, 'isAdmin') && $dhUser->isAdmin()
        && !in_array((int) $operator->id, \App\Support\ShopAccess::operatorIdsFor($dhUser), true);
@endphp
@if($asPlatform)
    <div class="dh-shop-asadmin dh-shop-noprint">
        <span class="material-icons-round" aria-hidden="true">admin_panel_settings</span>
        <span>You are managing <b>{{ $shopName }}</b> as a Divers Hub admin. Prices you enter show as "Listed by Divers Hub" until the shop confirms them.</span>
        <a href="{{ route('shop-access.index') }}">Back to Shop management</a>
    </div>
@endif

<div class="dh-shop-head">
    @if($operator->logoUrl)<img src="{{ asset('assets') }}{{ $operator->logoUrl }}" alt="">@endif
    <div>
        <h2>{{ $shopName }}</h2>
        <p>{{ $operator->cityAddress }}</p>
    </div>
    <a class="dh-btn dh-btn-ghost-dark dh-shop-noprint" href="{{ route('OperatorDetails', ['id' => $operator->slug ?: $operator->id]) }}" target="_blank" rel="noopener"><span class="material-icons-round">open_in_new</span>View public page</a>
</div>

<nav class="dh-shop-tabs" aria-label="Shop portal sections">
    @foreach($tabs as $key => [$label, $icon, $routeName])
        <a class="dh-shop-tab {{ $tab === $key ? 'is-active' : '' }}" href="{{ route($routeName, ['operator' => $operator->id]) }}" @if($tab === $key) aria-current="page" @endif>
            <span class="material-icons-round" aria-hidden="true">{{ $key === 'codes' && $qrWaiting ? 'lock' : $icon }}</span>{{ $label }}
        </a>
    @endforeach
    @foreach($locked as $label)
        <button type="button" class="dh-shop-tab is-locked" disabled title="Coming soon">
            <span class="material-icons-round" aria-hidden="true">lock</span>{{ $label }}
        </button>
    @endforeach
</nav>

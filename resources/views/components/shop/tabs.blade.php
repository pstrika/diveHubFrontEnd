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
    // In the order of the setup checklist: where you are, who you are, what
    // you charge, how divers reach you. Then the things that are not steps.
    $tabs = [
        'overview'  => ['Overview', 'dashboard', 'shop-portal.overview'],
        'locations' => ['Locations', 'place', 'shop-portal.locations'],
        'profile'   => ['Shop details', 'badge', 'shop-portal.profile'],
        'prices'    => ['Prices', 'sell', 'shop-portal.prices'],
        'codes'     => ['QR codes', 'qr_code_2', 'shop-portal.codes'],
        'boats'     => ['Boats', 'directions_boat', 'shop-portal.boats'],
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

{{-- "What next" strip (Zach, 2026-11-19): on every portal page except the
     Overview (which has the full checklist) until the four steps are done.
     It shows where the shop is and gives one button to the next step, so
     finishing a step never leaves them wondering where to go. --}}
@php
    try {
        $dhSetup = $tab === 'overview' ? null : \App\Http\Controllers\ShopPortalController::setupSteps($operator);
    } catch (\Throwable $e) {
        $dhSetup = null;
    }
    $dhNext = $dhSetup && $dhSetup['next'] !== null ? $dhSetup['steps'][$dhSetup['next']] : null;
@endphp
@if($dhNext)
    <div class="dh-shop-setup dh-shop-noprint" role="status">
        <div class="dh-shop-setup-top">
            <b>Getting set up</b>
            <span>{{ $dhSetup['done'] }} of {{ count($dhSetup['steps']) }} done</span>
        </div>
        <ol class="dh-shop-setup-steps">
            @foreach($dhSetup['steps'] as $i => $step)
                <li class="{{ $step['done'] ? 'is-done' : '' }} {{ $dhSetup['next'] === $i ? 'is-next' : '' }} {{ $step['blocked'] ? 'is-blocked' : '' }}">
                    <span class="material-icons-round" aria-hidden="true">{{ $step['done'] ? 'check_circle' : ($step['blocked'] ? 'lock' : 'radio_button_unchecked') }}</span>
                    @if($step['blocked'])<span>{{ $step['title'] }}</span>
                    @else<a href="{{ $step['href'] }}">{{ $step['title'] }}</a>@endif
                </li>
            @endforeach
        </ol>
        <div class="dh-shop-setup-next">
            @if($dhNext['tab'] === $tab)
                <span><b>Step {{ $dhSetup['next'] + 1 }}: {{ $dhNext['title'] }}.</b> {{ $dhNext['text'] }}</span>
            @else
                <span><b>Next, step {{ $dhSetup['next'] + 1 }}: {{ $dhNext['title'] }}.</b></span>
                <a class="dh-btn dh-btn-primary" href="{{ $dhNext['href'] }}">{{ $dhNext['label'] }}<span class="material-icons-round" aria-hidden="true">arrow_forward</span></a>
            @endif
        </div>
    </div>
@endif


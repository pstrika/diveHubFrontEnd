{{--
    Site shell navigation (redesign proposal: IA section, W1 note 1, W5 note 1).

    Replaces the Material Dashboard sidebar and the breadcrumb navbar on every
    page. Three destinations map to the three diver questions:

        Dive Today  -> the trip board          (route Trips)
        Dive Sites  -> the site catalog        (route DiveSites)
        Operators   -> the boat directory      (route Operators)

    Everything else that used to be a sidebar entry (calendars, weather,
    planning tools, groups, account, admin) lives in one "Me" panel, an
    offcanvas drawer rendered from shell/menu.blade.php. Nothing was removed,
    it was regrouped.

    Desktop (lg and up): a sticky top bar with the three links and an avatar
    button that opens the drawer. Phones: the top bar shrinks to brand plus
    avatar, and a fixed bottom tab bar (Today / Sites / Boats / Me) gives
    thumb reach to the whole product. Same drawer either way.

    $active is one of: today, sites, operators, me, or empty.
--}}
@props(['active' => ''])

@php
    $tabs = [
        'today'     => ['label' => 'Dive Today', 'short' => 'Today', 'icon' => 'today',           'href' => route('Trips')],
        'sites'     => ['label' => 'Dive Sites', 'short' => 'Sites', 'icon' => 'scuba_diving',    'href' => route('DiveSites')],
        'operators' => ['label' => 'Operators',  'short' => 'Boats', 'icon' => 'directions_boat', 'href' => route('Operators')],
    ];
    $user = auth()->user();
    $isGuest = !$user || !$user->isNotGuest();
@endphp

{{-- One logout form for the whole shell; menu links submit it. --}}
@auth
<form method="POST" action="{{ route('logout') }}" class="d-none" id="logout-form">@csrf</form>
@endauth

<header class="dh-topbar">
    <div class="dh-topbar-inner">
        <a class="dh-brand" href="{{ route('/') }}" aria-label="Divers Hub home">
            <img src="{{ asset('assets') }}/img/logos/logo_circle.png" alt="" width="34" height="34">
            <span>Divers Hub</span>
        </a>

        <nav class="dh-topnav" aria-label="Main">
            @foreach($tabs as $key => $tab)
                <a href="{{ $tab['href'] }}" class="dh-topnav-link {{ $active === $key ? 'is-active' : '' }}" @if($active === $key) aria-current="page" @endif>{{ $tab['label'] }}</a>
            @endforeach
        </nav>

        <div class="dh-topbar-actions">
            <form class="dh-search d-none d-md-flex" action="{{ route('DiveSitesSearch') }}" method="POST" role="search">
                @csrf
                <span class="material-icons-round" aria-hidden="true">search</span>
                <input type="search" name="searchString" placeholder="Search sites" aria-label="Search dive sites">
            </form>

            @if($isGuest)
                <a href="{{ route('login') }}" class="dh-btn-ghost d-none d-lg-inline-flex">Sign in</a>
            @endif

            <button type="button" class="dh-avatar-btn {{ $active === 'me' ? 'is-active' : '' }}" data-bs-toggle="offcanvas" data-bs-target="#dh-me" aria-controls="dh-me" aria-label="Open your menu">
                @if($user && $user->picture)
                    <img src="{{ asset('assets') }}/img/users/{{ $user->picture }}" alt="">
                @else
                    <span class="material-icons-round" aria-hidden="true">{{ $isGuest ? 'menu' : 'account_circle' }}</span>
                @endif
            </button>
        </div>
    </div>
</header>

{{-- Bottom tab bar, phones only (hidden from lg up in CSS). --}}
<nav class="dh-tabbar" aria-label="Main, mobile">
    @foreach($tabs as $key => $tab)
        <a href="{{ $tab['href'] }}" class="dh-tab {{ $active === $key ? 'is-active' : '' }}" @if($active === $key) aria-current="page" @endif>
            <span class="material-icons-round" aria-hidden="true">{{ $tab['icon'] }}</span>
            <span>{{ $tab['short'] }}</span>
        </a>
    @endforeach
    <button type="button" class="dh-tab {{ $active === 'me' ? 'is-active' : '' }}" data-bs-toggle="offcanvas" data-bs-target="#dh-me" aria-controls="dh-me">
        <span class="material-icons-round" aria-hidden="true">{{ $isGuest ? 'menu' : 'person' }}</span>
        <span>Me</span>
    </button>
</nav>

{{-- The "Me" drawer. Shared by the avatar button and the Me tab. --}}
<div class="offcanvas offcanvas-end dh-drawer" tabindex="-1" id="dh-me" aria-labelledby="dh-me-title">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="dh-me-title">
            @if($isGuest) Menu @else {{ $user->name }} @endif
        </h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <x-shell.menu />
    </div>
</div>

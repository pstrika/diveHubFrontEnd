{{--
    Site shell navigation.

    One bar, five tabs, the same whether you are signed in or not (decided with
    Zach and Pablo, 2026-09-10). A bottom bar is a map, not a menu: its value is
    that a diver stops reading it after two sessions and just reaches, so it
    never rearranges itself at sign up.

        Dives    -> the trip finder            (route Trips)
        Sites    -> the site explorer          (route DiveSites)
        Weather  -> the marine forecast        (route Weather)
        Groups   -> my groups, unread badge    (route MyGroups; guests get the account prompt)
        Me       -> my dashboard, avatar icon  (route MyDashboard; guests get the account prompt and menu)

    Weather has the slot Operators used to have. In South Florida the go or no go
    call is sea state, so a diver checks conditions the night before every trip;
    the operator list is a directory people read once when they are new, and
    every real path to an operator is a trip card, a site page or the finder's
    operator filter. Operators stays one tap away in the drawer.

    Me is a real page, not a pop out. The drawer (shell/menu.blade.php) still
    exists for everything else, opened from the avatar in the top bar on every
    screen size, and for a guest it is what the Me tab opens, since a guest has
    no dashboard yet. Nothing was removed, it was regrouped.

    Desktop (lg and up): a sticky top bar with the four links and the avatar
    button. Phones: the top bar shrinks to brand plus avatar and the fixed bottom
    tab bar gives thumb reach to the whole product.

    $active is one of: today, sites, weather, groups, me, or empty.
--}}
@props(['active' => ''])

@php
    $user = auth()->user();
    $isGuest = !$user || !$user->isNotGuest();
    // Four linked destinations plus Me. "Dives" is the trip finder (day board plus
    // date ranges). "Groups" is personal, so for guests it opens the account prompt.
    $tabs = [
        'today'   => ['label' => 'Dives',      'short' => 'Dives',   'icon' => 'scuba_diving', 'href' => route('Trips')],
        'sites'   => ['label' => 'Dive Sites', 'short' => 'Sites',   'icon' => 'pin_drop',     'href' => route('DiveSites')],
        'weather' => ['label' => 'Weather',    'short' => 'Weather', 'icon' => 'cloud',        'href' => route('Weather')],
        'groups'  => ['label' => 'Groups',     'short' => 'Groups',  'icon' => 'groups',       'href' => $isGuest ? '#' : route('MyGroups'), 'gated' => $isGuest],
    ];

    // Unread in-app notifications. Every notification but the welcome message comes
    // from a group (dives posted, reminders, invites, chat), so the count sits on
    // Groups, the tab that pulls people back. The Messages page keeps it too.
    $unread = $isGuest ? 0 : (int) $user->unreadNotifications();

    // The Me icon is the diver's own picture when they have one. users.picture is a
    // bare file name under public/assets/img/users (UserController::updateProfilePic);
    // Google sign in can leave a full URL, passed through as is.
    $avatar = null;
    if ($user && $user->picture) {
        $pic = ltrim($user->picture, '/');
        $avatar = str_starts_with($pic, 'http') ? $pic : asset('assets') . '/img/users/' . $pic;
    }
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
                <a href="{{ $tab['href'] }}" class="dh-topnav-link {{ $active === $key ? 'is-active' : '' }}" data-dh-tab="{{ $key }}" @if($active === $key) aria-current="page" @endif @if(!empty($tab['gated'])) onclick="event.preventDefault();showModalGuest();" @endif>{{ $tab['label'] }}@if($key === 'groups' && $unread > 0)<span class="dh-tab-badge" aria-label="{{ $unread }} unread">{{ $unread > 99 ? '99+' : $unread }}</span>@endif</a>
            @endforeach
        </nav>

        <div class="dh-topbar-actions">
            {{-- Full bar from md up; a plain icon opening the same form in a
                 modal on phones, where there's no room for it inline but
                 search is too important to hide entirely (2026-09-11). --}}
            <form class="dh-search d-none d-md-flex" action="{{ route('DiveSitesSearch') }}" method="POST" role="search">
                @csrf
                <span class="material-icons-round" aria-hidden="true">search</span>
                <input type="search" name="searchString" placeholder="Search sites" aria-label="Search dive sites">
            </form>
            <button type="button" class="dh-search-btn d-md-none" data-bs-toggle="modal" data-bs-target="#dh-search-modal" aria-label="Search dive sites">
                <span class="material-icons-round" aria-hidden="true">search</span>
            </button>

            @if($isGuest)
                <a href="{{ route('login') }}" class="dh-btn-ghost d-none d-lg-inline-flex">Sign in</a>
            @else
                {{-- Notification centre, just left of the avatar - where people expect it (2026-09-11). --}}
                <a href="{{ route('Messages') }}" class="dh-bell-btn" aria-label="Messages{{ $unread > 0 ? ', ' . $unread . ' unread' : '' }}">
                    <span class="material-icons-round" aria-hidden="true">notifications</span>
                    @if($unread > 0)<span class="dh-bell-badge">{{ $unread > 99 ? '99+' : $unread }}</span>@endif
                </a>
            @endif

            <button type="button" class="dh-avatar-btn {{ $active === 'me' ? 'is-active' : '' }}" data-bs-toggle="offcanvas" data-bs-target="#dh-me" aria-controls="dh-me" aria-label="Open your menu">
                @if($avatar)
                    <img src="{{ $avatar }}" alt="" onerror="this.remove()">
                @else
                    <span class="material-icons-round" aria-hidden="true">{{ $isGuest ? 'menu' : 'account_circle' }}</span>
                @endif
            </button>
        </div>
    </div>
</header>

{{-- Mobile search modal: same form and endpoint as the desktop search bar. --}}
<div class="modal fade dh-search-modal" id="dh-search-modal" tabindex="-1" aria-labelledby="dh-search-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dh-search-modal-title">Search dive sites</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="dh-search" action="{{ route('DiveSitesSearch') }}" method="POST" role="search">
                    @csrf
                    <span class="material-icons-round" aria-hidden="true">search</span>
                    <input type="search" name="searchString" placeholder="Search sites" aria-label="Search dive sites" autofocus>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Bottom tab bar, phones only (hidden from lg up in CSS). --}}
<nav class="dh-tabbar" aria-label="Main, mobile">
    @foreach($tabs as $key => $tab)
        <a href="{{ $tab['href'] }}" class="dh-tab {{ $active === $key ? 'is-active' : '' }}" data-dh-tab="{{ $key }}" @if($active === $key) aria-current="page" @endif @if(!empty($tab['gated'])) onclick="event.preventDefault();showModalGuest();" @endif>
            <span class="dh-tab-icon">
                <span class="material-icons-round" aria-hidden="true">{{ $tab['icon'] }}</span>
                @if($key === 'groups' && $unread > 0)<span class="dh-tab-badge" aria-label="{{ $unread }} unread">{{ $unread > 99 ? '99+' : $unread }}</span>@endif
            </span>
            <span>{{ $tab['short'] }}</span>
        </a>
    @endforeach
    @if($isGuest)
        {{-- No dashboard yet: the account prompt and the menu. --}}
        <button type="button" class="dh-tab {{ $active === 'me' ? 'is-active' : '' }}" data-dh-tab="me" data-bs-toggle="offcanvas" data-bs-target="#dh-me" aria-controls="dh-me">
            <span class="dh-tab-icon"><span class="material-icons-round" aria-hidden="true">person</span></span>
            <span>Me</span>
        </button>
    @else
        <a href="{{ route('MyDashboard') }}" class="dh-tab {{ $active === 'me' ? 'is-active' : '' }}" data-dh-tab="me" @if($active === 'me') aria-current="page" @endif>
            <span class="dh-tab-icon {{ $avatar ? 'dh-tab-avatar' : '' }}">
                @if($avatar)
                    {{-- If the file is missing, fall back to the icon rather than a broken image. --}}
                    <img src="{{ $avatar }}" alt="" onerror="this.parentNode.classList.remove('dh-tab-avatar');this.outerHTML='<span class=\'material-icons-round\' aria-hidden=\'true\'>person</span>'">
                @else
                    <span class="material-icons-round" aria-hidden="true">person</span>
                @endif
            </span>
            <span>Me</span>
        </a>
    @endif
</nav>

{{-- The "Me" drawer. Shared by the avatar button and the Me tab. --}}
<div class="offcanvas offcanvas-end dh-drawer" tabindex="-1" id="dh-me" aria-labelledby="dh-me-title">
    <div class="offcanvas-header">
        @if($isGuest)
            <h5 class="offcanvas-title" id="dh-me-title">Menu</h5>
        @else
            {{--
                Avatar next to the name: only when a picture was actually
                uploaded (users.picture set). If it resolves (UserAvatar::exists
                - a real file or a remote URL), show it with the theme-blue
                ring; if the value is set but the file isn't there (dev/test
                clone without the upload volume), fall back to initials rather
                than a broken image. No picture at all shows just the name,
                same as before.
            --}}
            <div class="dh-drawer-user">
                @if($user->picture)
                    @if(\App\Support\UserAvatar::exists($user->picture))
                        <img src="{{ \App\Support\UserAvatar::url($user->picture) }}" alt="" class="dh-drawer-avatar">
                    @else
                        @php
                            $nameParts = preg_split('/\s+/', trim((string) $user->name)) ?: [];
                            $initials = count($nameParts) > 1
                                ? strtoupper(substr($nameParts[0], 0, 1) . substr(end($nameParts), 0, 1))
                                : strtoupper(substr($nameParts[0] ?? '', 0, 1));
                        @endphp
                        <span class="dh-drawer-avatar dh-drawer-avatar-initials">{{ $initials }}</span>
                    @endif
                @endif
                <h5 class="offcanvas-title mb-0" id="dh-me-title">{{ $user->name }}</h5>
            </div>
        @endif
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <x-shell.menu />
    </div>
</div>

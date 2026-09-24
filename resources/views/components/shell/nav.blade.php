{{--
    Site shell navigation.

    Desktop (lg and up) keeps the original fixed bar decided with Zach and
    Pablo on 2026-09-10 - Dives, Sites, Weather, Groups, plus a More link
    that opens the drawer (shell/menu.blade.php). $tabs below, unchanged.

    Phones get their own $mobileTabs bar, customizable per diver since
    2026-09-24 (Pablo: "give them the chance to customize which icons they
    see in the bar"):

        Trips      -> the trip finder, fixed 1st         (route Trips)
        [slot 1]   -> nav_slot_1, defaults to Weather     (App\Support\NavTabs)
        Dashboard  -> fixed 3rd, enlarged                 (route MyDashboard)
        [slot 2]   -> nav_slot_2, defaults to Groups       (App\Support\NavTabs)
        More       -> fixed 5th, opens the drawer

    Trips, Dashboard and More can't be moved or swapped out - only the two
    slot values are pickable, from App\Support\NavTabs::OPTIONS (Weather,
    Groups, Sites, Operators, Deco Planner, Blog, Beach Diving, or one of
    the themed calendars), edited from the profile overview page or the
    welcome wizard. Guests get their own fixed 5 instead, no Dashboard (they
    have none) and no customization: Trips, Sites, Weather, Operators, More.

    Weather earlier took the desktop slot Operators used to have. In South
    Florida the go or no go call is sea state, so a diver checks conditions
    the night before every trip; the operator list is a directory people
    read once when they are new, and every real path to an operator is a
    trip card, a site page or the finder's operator filter. Operators stays
    one tap away in the drawer (and is a nav_slot option on mobile).

    $active is one of: today, sites, weather, groups, dashboard, me, or
    empty (plus, on mobile, whatever NavTabs key a diver has in a slot).
--}}
@props(['active' => ''])

@php
    $user = auth()->user();
    $isGuest = !$user || !$user->isNotGuest();
    // Four linked destinations plus Me. "Dives" is the trip finder (day board plus
    // date ranges). "Groups" is personal, so for guests it opens the account prompt.
    // Desktop only (see $mobileTabs below for the phone tab bar, which is
    // customizable and has its own Dashboard tab - Pablo, 2026-09-24).
    $tabs = [
        'today'   => ['label' => 'Dives',      'short' => 'Dives',   'icon' => 'scuba_diving', 'href' => route('Trips')],
        'sites'   => ['label' => 'Dive Sites', 'short' => 'Sites',   'icon' => 'pin_drop',     'href' => route('DiveSites')],
        'weather' => ['label' => 'Weather',    'short' => 'Weather', 'icon' => 'cloud',        'href' => route('Weather')],
        'groups'  => ['label' => 'Groups',     'short' => 'Groups',  'icon' => 'groups',       'href' => $isGuest ? '#' : route('MyGroups'), 'gated' => $isGuest],
    ];

    // Unread in-app notifications, for the bell icon - inbox and groups combined.
    $unread = $isGuest ? 0 : (int) $user->unreadNotifications();
    // The Groups tab's own badge is scoped to Groups-folder messages only,
    // not the bell's combined total (Pablo, 2026-09-17: "we want here the
    // message count for groups, not the entire unread inbox").
    $unreadGroups = $isGuest ? 0 : (int) $user->unreadGroupNotifications();

    // Mobile tab bar (Pablo, 2026-09-24): "Trips, Dashboard and More need to
    // stay always in the same position and cannot be swapped" - positions 1,
    // 3 and 5 are fixed here; 2 and 4 come from the diver's own
    // nav_slot_1/nav_slot_2 (App\Support\NavTabs), defaulting to Weather and
    // Groups - "the standard configuration". Guests get their own fully
    // fixed 5, no Dashboard (they have none) and no customization.
    if ($isGuest) {
        $mobileTabs = [
            ['key' => 'today',     'short' => 'Dives',     'icon' => 'scuba_diving',       'href' => route('Trips')],
            ['key' => 'sites',     'short' => 'Sites',     'icon' => 'pin_drop',           'href' => route('DiveSites')],
            ['key' => 'weather',   'short' => 'Weather',   'icon' => 'cloud',              'href' => route('Weather')],
            ['key' => 'operators', 'short' => 'Operators', 'icon' => 'directions_boat',    'href' => route('Operators')],
        ];
    } else {
        $slot1Key = \App\Support\NavTabs::resolveSlot($user->nav_slot_1, \App\Support\NavTabs::DEFAULT_SLOT_1);
        $slot2Key = \App\Support\NavTabs::resolveSlot($user->nav_slot_2, \App\Support\NavTabs::DEFAULT_SLOT_2);
        $slot1 = \App\Support\NavTabs::OPTIONS[$slot1Key];
        $slot2 = \App\Support\NavTabs::OPTIONS[$slot2Key];
        $mobileTabs = [
            ['key' => 'today', 'short' => 'Dives', 'icon' => 'scuba_diving', 'href' => route('Trips')],
            ['key' => $slot1Key, 'short' => $slot1['short'], 'icon' => $slot1['icon'], 'href' => \App\Support\NavTabs::href($slot1Key), 'badge' => $slot1Key === 'groups' ? $unreadGroups : 0],
            ['key' => 'dashboard', 'short' => 'Dashboard', 'icon' => 'dashboard', 'href' => route('MyDashboard'), 'big' => true],
            ['key' => $slot2Key, 'short' => $slot2['short'], 'icon' => $slot2['icon'], 'href' => \App\Support\NavTabs::href($slot2Key), 'badge' => $slot2Key === 'groups' ? $unreadGroups : 0],
        ];
    }

    // Renders a mobile tab's icon - a bare name is a Material icon, "svg:<file>"
    // is one of the themed calendar SVGs, same convention and recoloring as
    // shell/menu.blade.php's own $iconHtml.
    $tabIconHtml = function (string $icon) {
        if (str_starts_with($icon, 'svg:')) {
            $path = public_path('assets/img/icons/' . substr($icon, 4));
            $svg = is_file($path) ? file_get_contents($path) : '';
            $svg = preg_replace('/<defs>.*?<\/defs>/s', '', $svg);
            $svg = str_replace(' class="cls-1"', '', $svg);
            $svg = preg_replace('/<svg /', '<svg fill="currentColor" ', $svg, 1);
            return '<span class="dh-tab-icon-svg" aria-hidden="true">' . $svg . '</span>';
        }
        return '<span class="material-icons-round" aria-hidden="true">' . $icon . '</span>';
    };

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
                <a href="{{ $tab['href'] }}" class="dh-topnav-link {{ $active === $key ? 'is-active' : '' }}" data-dh-tab="{{ $key }}" @if($active === $key) aria-current="page" @endif @if(!empty($tab['gated'])) onclick="event.preventDefault();showModalGuest();" @endif>{{ $tab['label'] }}@if($key === 'groups' && $unreadGroups > 0)<span class="dh-tab-badge" aria-label="{{ $unreadGroups }} unread">{{ $unreadGroups > 99 ? '99+' : $unreadGroups }}</span>@endif</a>
            @endforeach
            {{-- Desktop-only way back into the drawer now that the avatar
                 button links straight to My Dashboard (Pablo, 2026-09-21:
                 "on desktop I lost access to the drawer"). The bottom tab
                 bar's own More tab (mobile only) already opens the same
                 drawer, unchanged. --}}
            <button type="button" class="dh-topnav-link {{ $active === 'me' ? 'is-active' : '' }}" data-dh-tab="me" data-bs-toggle="offcanvas" data-bs-target="#dh-me" aria-controls="dh-me" @if($active === 'me') aria-current="page" @endif>More</button>
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

            @if($isGuest)
                <button type="button" class="dh-avatar-btn {{ $active === 'me' ? 'is-active' : '' }}" data-bs-toggle="offcanvas" data-bs-target="#dh-me" aria-controls="dh-me" aria-label="Open your menu">
                    <span class="material-icons-round" aria-hidden="true">menu</span>
                </button>
            @else
                {{-- A small "My Profile" / "Log Out" menu on the avatar itself
                     (Pablo, 2026-09-24: "when we click on the user avatar top
                     right...show a small dialog...My Profile and Log Out"),
                     replacing the direct-link-to-overview behaviour from
                     2026-09-22. Everything else that used to live behind the
                     avatar (My Dashboard, settings, admin tools) is still in
                     the full Me drawer, reachable from the bottom bar's More
                     tab - this is deliberately just the two things people
                     actually want from clicking their own picture. --}}
                {{-- Plain manual toggle rather than data-bs-toggle="dropdown"
                     (Pablo, 2026-09-24: "if I'm already in My Profile page,
                     the dropdown does not show") - Bootstrap's own Dropdown
                     component wasn't reliably adding its "show" class on
                     every page here, for reasons not worth chasing further
                     given how small this menu is. This owns its own open/
                     close state instead of depending on that. --}}
                <div class="dropdown dh-account-menu">
                    <button type="button" id="dhAccountMenuBtn" class="dh-avatar-btn {{ $active === 'me' ? 'is-active' : '' }}" aria-haspopup="true" aria-expanded="false" aria-label="Your account">
                        @if($avatar)
                            <img src="{{ $avatar }}" alt="" onerror="this.remove()">
                        @else
                            <span class="material-icons-round" aria-hidden="true">account_circle</span>
                        @endif
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end border-radius-xl p-2 mt-2" id="dhAccountMenuList">
                        <li>
                            <a class="dropdown-item border-radius-md d-flex align-items-center" href="{{ route('overview') }}">
                                <span class="material-icons-round opacity-6 me-2" aria-hidden="true">person</span>
                                My Profile
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item border-radius-md d-flex align-items-center" href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                                <span class="material-icons-round opacity-6 me-2" aria-hidden="true">logout</span>
                                Log Out
                            </a>
                        </li>
                    </ul>
                </div>
            @endif
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

{{-- Bottom tab bar, phones only (hidden from lg up in CSS). Trips (1st),
     Dashboard (3rd, enlarged) and More (5th) are fixed; 2nd and 4th are
     $mobileTabs' customizable slots (Pablo, 2026-09-24). --}}
<nav class="dh-tabbar" aria-label="Main, mobile">
    @foreach($mobileTabs as $tab)
        <a href="{{ $tab['href'] }}" class="dh-tab {{ !empty($tab['big']) ? 'dh-tab-dashboard' : '' }} {{ $active === $tab['key'] ? 'is-active' : '' }}" data-dh-tab="{{ $tab['key'] }}" @if($active === $tab['key']) aria-current="page" @endif @if(!empty($tab['gated'])) onclick="event.preventDefault();showModalGuest();" @endif>
            <span class="dh-tab-icon {{ !empty($tab['big']) ? 'dh-tab-icon-big' : '' }}">
                {!! $tabIconHtml($tab['icon']) !!}
                @if(!empty($tab['badge']))<span class="dh-tab-badge" aria-label="{{ $tab['badge'] }} unread">{{ $tab['badge'] > 99 ? '99+' : $tab['badge'] }}</span>@endif
            </span>
            <span>{{ $tab['short'] }}</span>
        </a>
    @endforeach
    {{-- Same tab for guest and signed-in alike (Pablo, 2026-09-21: "same
         logic...icon at the bottom right should be a more") - opens the
         drawer, which for a signed-in diver now also carries the My
         Dashboard link (the avatar in the top bar is a direct shortcut to
         the profile overview instead, since 2026-09-22 - see above)
         to it). --}}
    <button type="button" class="dh-tab {{ $active === 'me' ? 'is-active' : '' }}" data-dh-tab="me" data-bs-toggle="offcanvas" data-bs-target="#dh-me" aria-controls="dh-me">
        <span class="dh-tab-icon"><span class="material-icons-round" aria-hidden="true">more_horiz</span></span>
        <span>More</span>
    </button>
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

@auth
@if(!$isGuest)
<script>
    (function () {
        var btn = document.getElementById('dhAccountMenuBtn');
        var menu = document.getElementById('dhAccountMenuList');
        if (!btn || !menu) return;

        function closeMenu() {
            menu.classList.remove('show');
            btn.setAttribute('aria-expanded', 'false');
        }
        function openMenu() {
            menu.classList.add('show');
            btn.setAttribute('aria-expanded', 'true');
        }

        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            menu.classList.contains('show') ? closeMenu() : openMenu();
        });
        document.addEventListener('click', function (e) {
            if (menu.classList.contains('show') && e.target !== btn && !menu.contains(e.target)) {
                closeMenu();
            }
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeMenu();
        });
    })();
</script>
@endif
@endauth

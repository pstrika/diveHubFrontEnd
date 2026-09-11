{{--
    Contents of the drawer behind the avatar button (and the Me tab for guests,
    who have no dashboard yet). Every link that used to be in the sidebar and is
    not one of the tab bar destinations is here, grouped by task.

    Guests see the same groups. Items that need an account are marked with a
    lock and open the guest prompt (showModalGuest, from <x-guest-modal />),
    which is "show, then gate": the feature is visible, the interaction asks
    for an account. Admin groups render only for users with those abilities,
    same @can checks the sidebar used.
--}}
@php
    $user = auth()->user();
    $isGuest = !$user || !$user->isNotGuest();

    // Renders one drawer link. Locked items keep their label visible but
    // open the guest prompt instead of navigating.
    // $icon is a Material icon name, "img:<file>" for one of Pablo's own PNG
    // icons in public/assets/img/icons (white line art on a dark round badge,
    // drawn for the old dark sidebar), or "svg:<file>" for his newer Illustrator
    // SVG exports, inlined and recolored to match the plain Material icons here.
    $iconHtml = function (string $icon) {
        if (str_starts_with($icon, 'img:')) {
            return '<span class="dh-menu-icon-img" aria-hidden="true"><img src="' . asset('assets') . '/img/icons/' . e(substr($icon, 4)) . '" alt=""></span>';
        }
        if (str_starts_with($icon, 'svg:')) {
            $path = public_path('assets/img/icons/' . substr($icon, 4));
            $svg = is_file($path) ? file_get_contents($path) : '';
            // Illustrator exports a <style> block hardcoding fill:#fff via a
            // .cls-1 class - strip that and drive the color from CSS instead
            // (currentColor), same as the Material icons next to these.
            $svg = preg_replace('/<defs>.*?<\/defs>/s', '', $svg);
            $svg = str_replace(' class="cls-1"', '', $svg);
            $svg = preg_replace('/<svg /', '<svg fill="currentColor" ', $svg, 1);
            return '<span class="dh-menu-icon-svg" aria-hidden="true">' . $svg . '</span>';
        }
        return '<span class="material-icons-round" aria-hidden="true">' . $icon . '</span>';
    };
    $link = function (string $label, string $href, string $icon, bool $locked = false) use ($iconHtml) {
        if ($locked) {
            return '<a class="dh-menu-link is-locked" href="#" onclick="event.preventDefault();showModalGuest();">'
                . $iconHtml($icon)
                . '<span>' . e($label) . '</span>'
                . '<span class="material-icons-round dh-menu-lock" aria-label="Account required">lock</span></a>';
        }
        return '<a class="dh-menu-link" href="' . $href . '">'
            . $iconHtml($icon)
            . '<span>' . e($label) . '</span></a>';
    };
@endphp

@if($isGuest)
    <div class="dh-menu-cta">
        <p class="mb-2">Save trips, plan dives and see the full forecast with a free account.</p>
        <a class="btn bg-gradient-info w-100 mb-2" href="{{ route('create-account') }}">Create a free account</a>
        <a class="btn btn-outline-dark w-100" href="{{ route('login') }}">Sign in</a>
    </div>
@else
    <div class="dh-menu-group">
        <h6>My diving</h6>
        {!! $link('My Dashboard', route('MyDashboard'), 'dashboard') !!}
        @if(\App\Http\Controllers\OnboardingController::needs(auth()->user()))
            {!! $link('Finish setting up your profile', route('welcome'), 'tune') !!}
        @endif
        {!! $link('My Calendar', route('MyCalendar'), 'event') !!}
        {!! $link('My Groups', route('MyGroups'), 'groups') !!}
        <x-shell.push-toggle />
        {!! $link('My Visited Sites', route('MyVisitedSites'), 'check_circle') !!}
        @php $unread = (int) $user->unreadNotifications(); @endphp
        {{-- Unread count moved here from the old navbar bell. --}}
        <a class="dh-menu-link" href="{{ route('Messages') }}">
            <span class="material-icons-round" aria-hidden="true">notifications</span>
            <span>Messages</span>
            @if($unread > 0)<span class="dh-menu-badge" aria-label="{{ $unread }} unread">{{ $unread }}</span>@endif
        </a>
    </div>
@endif

{{-- Rule (Zach): tools and calendars are open to everyone; only personal data (dashboard, groups, saving) needs an account. --}}
<div class="dh-menu-group">
    <h6>Calendars</h6>
    {!! $link('Recreational', route('CalendarT') . '/rec', 'svg:icons_calendar_rec.svg') !!}
    {!! $link('Technical', route('CalendarT') . '/tec', 'svg:icons_calendar_tec.svg') !!}
    {!! $link('Wreck diving', route('CalendarWreck'), 'svg:icons_calendar_wreck.svg') !!}
    {!! $link('Shark diving', route('CalendarShark'), 'svg:icons_calendar_shark.svg') !!}
    {!! $link('Lobster diving', route('CalendarLobster'), 'svg:icons_calendar_lobster.svg') !!}
</div>

<div class="dh-menu-group">
    <h6>Operators and shore</h6>
    {{-- Operators left the tab bar for Weather (2026-09-10); this row, trip cards,
         site pages and the finder's operator filter are the ways in. --}}
    {!! $link('Dive operators', route('Operators'), 'directions_boat') !!}
    {!! $link('Marine forecast', route('Weather'), 'waves') !!}
    {!! $link('Beach diving', route('BeachDiving'), 'beach_access') !!}
    {!! $link('Online waivers', route('Waivers'), 'assignment') !!}
</div>

<div class="dh-menu-group">
    <h6>Planning tools</h6>
    {!! $link('Deco planner', route('DecoPlanner'), 'timer') !!}
    {!! $link('Best gases', route('gasplanning'), 'science') !!}
</div>

@auth
    @can('manage-users', App\Models\User::class)
    <div class="dh-menu-group">
        <h6>Admin</h6>
        {!! $link('Platform health', route('PlatformHealth'), 'monitor_heart') !!}
        {!! $link('User management', route('users'), 'manage_accounts') !!}
    </div>
    @endcan
    @can('manage-items', App\Models\User::class)
    <div class="dh-menu-group">
        <h6>Dive sites admin</h6>
        {!! $link('Add site', route('new-site'), 'add_location_alt') !!}
        {!! $link('Manage sites', route('DiveSitesAdmin'), 'edit_location_alt') !!}
    </div>
    @endcan
@endauth

<div class="dh-menu-group">
    <h6>Account</h6>
    @if($isGuest)
        {!! $link('About Divers Hub', route('AboutUs'), 'info') !!}
    @else
        {!! $link('My profile', route('overview'), 'person') !!}
        {!! $link('About Divers Hub', route('AboutUs'), 'info') !!}
        <a class="dh-menu-link" href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
            <span class="material-icons-round" aria-hidden="true">logout</span><span>Log out</span>
        </a>
    @endif
</div>

{{-- Website translator (Elfsight) moved here from the old navbar so Spanish speaking and Argentine users keep it. --}}
<div class="dh-menu-group dh-menu-translate">
    <h6>Language</h6>
    <script src="https://static.elfsight.com/platform/platform.js" async></script>
    <div class="elfsight-app-559bf1d4-b8b3-4ee4-9754-85f637b79d6a" data-elfsight-app-lazy></div>
</div>

<p class="dh-menu-version"><x-version /></p>

{{--
    Contents of the "Me" drawer. Every link that used to be in the sidebar
    and is not one of the three main destinations is here, grouped by task.

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
    $link = function (string $label, string $href, string $icon, bool $locked = false) {
        if ($locked) {
            return '<a class="dh-menu-link is-locked" href="#" onclick="event.preventDefault();showModalGuest();">'
                . '<span class="material-icons-round" aria-hidden="true">' . $icon . '</span>'
                . '<span>' . e($label) . '</span>'
                . '<span class="material-icons-round dh-menu-lock" aria-label="Account required">lock</span></a>';
        }
        return '<a class="dh-menu-link" href="' . $href . '">'
            . '<span class="material-icons-round" aria-hidden="true">' . $icon . '</span>'
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
        {!! $link('My Calendar', route('MyCalendar'), 'event') !!}
        {!! $link('My Groups', route('MyGroups'), 'groups') !!}
        {!! $link('My Visited Sites', route('MyVisitedSites'), 'check_circle') !!}
        {!! $link('Messages', route('Messages'), 'notifications') !!}
    </div>
@endif

<div class="dh-menu-group">
    <h6>Calendars</h6>
    {!! $link('Recreational', route('CalendarT') . '/rec', 'event_available') !!}
    {!! $link('Technical', route('CalendarT') . '/tec', 'event_available', $isGuest) !!}
    {!! $link('Wreck diving', route('CalendarWreck'), 'event_available', $isGuest) !!}
    {!! $link('Shark diving', route('CalendarShark'), 'event_available', $isGuest) !!}
    {!! $link('Lobster diving', route('CalendarLobster'), 'event_available', $isGuest) !!}
</div>

<div class="dh-menu-group">
    <h6>Conditions and shore</h6>
    {!! $link('Weather, South Florida', route('Weather'), 'waves', $isGuest) !!}
    {!! $link('Weather, Argentina', route('WeatherAR'), 'waves', $isGuest) !!}
    {!! $link('Beach diving', route('BeachDiving'), 'beach_access') !!}
    {!! $link('Online waivers', route('Waivers'), 'assignment') !!}
</div>

<div class="dh-menu-group">
    <h6>Planning tools</h6>
    {!! $link('Deco planner', route('DecoPlanner'), 'timer', $isGuest) !!}
    {!! $link('Best gases', route('gasplanning'), 'science', $isGuest) !!}
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
        {!! $link('Settings', route('settings'), 'settings') !!}
        {!! $link('About Divers Hub', route('AboutUs'), 'info') !!}
        <a class="dh-menu-link" href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
            <span class="material-icons-round" aria-hidden="true">logout</span><span>Log out</span>
        </a>
    @endif
</div>

<p class="dh-menu-version"><x-version /></p>

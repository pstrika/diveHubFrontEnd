{{--
    The menu rows at the foot of the dashboard.

    The Me tab opens the dashboard rather than a pop out (2026-09-10), so the
    things a diver used to reach from that pop out need a home on the page:
    their calendar, visited sites, messages and profile, plus the tools and
    the operator list that left the tab bar. Same destinations as the drawer
    behind the avatar button, kept to the rows a member actually uses.
    Settings was dropped (2026-09-11) - unused page.

    Members only; the dashboard itself requires an account.
--}}
@php
    $user = auth()->user();
    $unread = $user ? (int) $user->unreadNotifications() : 0;
    $rows = [
        ['My calendar',      route('MyCalendar'),     'event'],
        ['Visited sites',    route('MyVisitedSites'), 'check_circle'],
        ['Messages',         route('Messages'),       'notifications', $unread],
        ['Dive operators',   route('Operators'),      'directions_boat'],
        ['Deco planner',     route('DecoPlanner'),    'timer'],
        ['Best gases',       route('gasplanning'),    'science'],
        ['My profile',       route('overview'),       'person'],
    ];
@endphp

<section class="dh-me-rows" aria-label="More">
    @foreach($rows as $row)
        <a class="dh-me-row" href="{{ $row[1] }}">
            <span class="material-icons-round" aria-hidden="true">{{ $row[2] }}</span>
            <span>{{ $row[0] }}</span>
            @if(!empty($row[3]))<span class="dh-menu-badge" aria-label="{{ $row[3] }} unread">{{ $row[3] }}</span>@endif
        </a>
    @endforeach
    <a class="dh-me-row" href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
        <span class="material-icons-round" aria-hidden="true">logout</span>
        <span>Log out</span>
    </a>
</section>

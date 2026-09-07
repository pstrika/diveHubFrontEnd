{{--
    Page heading row under the shell nav. Replaces the breadcrumb navbar
    (<x-auth.navbars.navs.auth>). Shows the page title and, for guests, one
    quiet line inviting them to create an account. The old pink banner and
    the "shop / Home" breadcrumb are gone (findings F-01, F-04).

    Usage: <x-shell.header title="Dive Trips" />
--}}
@props(['title' => ''])

<div class="dh-pagehead">
    @if($title !== '')
        <h6 class="dh-pagehead-title">{{ $title }}</h6>
    @endif
    @auth
        @if(!auth()->user()->isNotGuest())
            <a class="dh-pagehead-guest" href="{{ route('create-account') }}">
                <span class="material-icons-round" aria-hidden="true">person_add_alt</span>
                Browsing as a guest. Create a free account to save trips and plan dives.
            </a>
        @endif
    @endauth
</div>

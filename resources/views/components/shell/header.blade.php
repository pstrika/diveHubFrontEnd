{{--
    Page heading row under the shell nav. Replaces the breadcrumb navbar
    (<x-auth.navbars.navs.auth>). Shows the page title and, for guests, one
    quiet line inviting them to create an account. The old pink banner and
    the "shop / Home" breadcrumb are gone (findings F-01, F-04).

    Usage: <x-shell.header title="Dive Trips" />
    With an icon - a theme-colored SVG from public/assets/img/icons, same file
    used for the matching drawer link (<x-shell.header title="Wreck Diving Calendar" icon="wreck_icon.svg" />),
    or a plain Material icon name, same as the drawer's non-SVG entries
    (<x-shell.header title="Beach Diving" icon="beach_access" />).
--}}
@props(['title' => '', 'icon' => null])

@php
    $__headIconSvg = ($icon && str_ends_with($icon, '.svg')) ? \App\Support\IconSvg::themed('assets/img/icons/' . $icon) : null;
@endphp

<div class="dh-pagehead">
    @if($title !== '')
        <h6 class="dh-pagehead-title">
            {{ $title }}
            @if($__headIconSvg)
                <span class="dh-pagehead-icon" aria-hidden="true">{!! $__headIconSvg !!}</span>
            @elseif($icon)
                <span class="material-icons-round dh-pagehead-icon is-font" aria-hidden="true">{{ $icon }}</span>
            @endif
        </h6>
    @endif
    @php $__u = auth()->user(); @endphp
    @if(!$__u || !$__u->isNotGuest())
        {{-- Anonymous visitors and the shared guest user both see this. --}}
        <a class="dh-pagehead-guest" href="{{ route('create-account') }}">
            <span class="material-icons-round" aria-hidden="true">person_add_alt</span>
            Browsing as a guest. Create a free account to save trips and plan dives.
        </a>
    @endif
</div>

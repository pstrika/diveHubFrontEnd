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

    $back: a fallback href, adds the installed-PWA-only <x-shell.pwa-back />
    to this same row, pinned right (Pablo, 2026-09-18: "in the blog where it
    says 'blog'...same for all other cases" - Dive Sites/Dive
    Operators/Dive Trip Details/Blog are exactly this title). Omit it on
    every page that isn't a drill-down from something else.
--}}
@props(['title' => '', 'icon' => null, 'back' => null])

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
    @if($back)
        <x-shell.pwa-back :fallback="$back" />
    @endif
</div>

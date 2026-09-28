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
@props(['title' => '', 'icon' => null, 'back' => null, 'h1' => false])

@php
    $__headIconSvg = ($icon && str_ends_with($icon, '.svg')) ? \App\Support\IconSvg::themed('assets/img/icons/' . $icon) : null;
@endphp

<div class="dh-pagehead">
    @if($title !== '')
        {{-- :h1="true" on pages that have no other <h1> (SEO audit, 2026-09-28).
             The icon sits OUTSIDE the <h1>: it's a Material icon ligature, so
             inside the heading its name became part of the text search
             engines read ("Online Waivers assignment"). The wrapper keeps
             the class, so layout and size are unchanged. --}}
        @if($h1)
        <div class="dh-pagehead-title">
            <h1 class="dh-pagehead-h1">{{ $title }}</h1>
        @else
        <h6 class="dh-pagehead-title">
            {{ $title }}
        @endif
            @if($__headIconSvg)
                <span class="dh-pagehead-icon" aria-hidden="true">{!! $__headIconSvg !!}</span>
            @elseif($icon)
                <span class="material-icons-round dh-pagehead-icon is-font" aria-hidden="true">{{ $icon }}</span>
            @endif
        @if($h1)
        </div>
        @else
        </h6>
        @endif
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

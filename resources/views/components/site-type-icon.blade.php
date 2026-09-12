{{--
    A site's type (wreck, reef, other) as an icon. New SVGs (2026-09-11),
    recolored to the theme color via App\Support\IconSvg instead of Pablo's
    old fixed-color PNGs - falls back to the old PNG if a type has no matching
    SVG yet, so a new/unexpected type value never disappears.

    Usage: <x-site-type-icon :type="$site->type" /> or size="20" for a
    tighter spot than the 26px default.
--}}
@props(['type', 'size' => 26])

@php
    $svg = $type ? \App\Support\IconSvg::themed('assets/img/icons/' . $type . '_icon.svg') : null;
@endphp

@if($svg)
    <span class="dh-site-type-icon" style="width:{{ $size }}px;height:{{ $size }}px" aria-hidden="true">{!! $svg !!}</span>
@elseif($type)
    <img src="{{ asset('assets') }}/img/icons/{{ $type }}_icon.png" alt="" width="{{ $size }}" height="{{ $size }}">
@endif

{{--
    Level icon with a real text alternative.

    Replaces the inline <img ... alt="levelIcon"> that appeared on every row
    of the sites tables. The alt text now says what the level is, so screen
    readers and image-blocked browsers get "Advanced Open Water" instead of
    "levelIcon" (finding F-05 in the redesign proposal). The tooltip carries
    the same text for sighted users.

    Usage: <x-dive-level.icon :level="$site->level" />
           <x-dive-level.icon :level="$site->level" height="20" />
--}}
@props(['level', 'height' => 25])

@php
    $info = \App\Support\DiveLevel::get($level);
@endphp

@if($info)
    <img src="{{ asset('assets') }}/{{ $info['icon'] }}"
         alt="{{ $info['name'] }}"
         title="{{ $info['name'] }} (max {{ \App\Support\DiveLevel::depthLabel($level) }})"
         height="{{ $height }}"
         loading="lazy"
         {{ $attributes }}>
@else
    {{-- Unknown or missing level. Say so rather than 404 an icon image. --}}
    <span class="text-xs text-secondary" title="No level recorded for this site">n/a</span>
@endif

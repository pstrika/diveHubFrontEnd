{{--
    Level filter chips (W4 in the redesign proposal, "level chips").

    Plain links, no JavaScript. Each chip reloads the page with ?level=N
    added to whatever other query parameters are already present (sort,
    type, and so on), so filters compose and the URL is shareable and
    bookmarkable. The controller reads the parameter and filters the query.

    $selected is the currently active level value or null for "All levels".
    $counts, optional, is level value => number of matches; shown in parentheses.

    Usage: <x-dive-level.filter-chips :selected="$filters['level']" :counts="$levelCounts" />
--}}
@props(['selected' => null, 'counts' => null])

@php
    // Build each chip's URL from the current request so other filters survive.
    $chipUrl = function ($level) {
        $params = request()->except('level', 'page');
        if ($level !== null) {
            $params['level'] = $level;
        }
        return request()->url() . ($params ? '?' . http_build_query($params) : '');
    };
    $isAll = $selected === null || $selected === '';
@endphp

<nav class="dive-filter-chips d-flex flex-wrap align-items-center gap-2" aria-label="Filter by certification level">
    <span class="text-sm text-secondary me-1">Level</span>

    <a href="{{ $chipUrl(null) }}"
       class="chip {{ $isAll ? 'chip-on' : '' }}"
       @if($isAll) aria-current="true" @endif>All levels</a>

    @foreach(\App\Support\DiveLevel::all() as $lvl)
        @php $on = !$isAll && (int) $selected === $lvl['value']; @endphp
        <a href="{{ $chipUrl($lvl['value']) }}"
           class="chip {{ $on ? 'chip-on' : '' }}"
           title="{{ $lvl['name'] }}, max {{ \App\Support\DiveLevel::depthLabel($lvl['value']) }}"
           @if($on) aria-current="true" @endif>{{ $lvl['short'] }}@if(is_array($counts)) ({{ $counts[$lvl['value']] ?? 0 }})@endif</a>
    @endforeach
</nav>

{{--
    Generic chip row bound to one query string parameter.

    Same idea as dive-level.filter-chips (which stays for the level specific
    tooltips): each chip is a link that sets ?param=value and keeps every
    other parameter, so filters compose and URLs are shareable.

    $param    query parameter name, e.g. "region"
    $options  value => label
    $selected active value or null
    $all      label for the "everything" chip; empty string hides it
    $toggle   true for a single on/off chip (clicking the active chip clears it)

    Usage: <x-query-chips param="type" :options="$types" :selected="$filters['type']" all="All trips" label="Type" />
--}}
@props(['param', 'options', 'selected' => null, 'all' => 'All', 'label' => '', 'toggle' => false])

@php
    $url = function ($value) use ($param) {
        $params = request()->except($param, 'page');
        if ($value !== null && $value !== '') {
            $params[$param] = $value;
        }
        return request()->url() . ($params ? '?' . http_build_query($params) : '');
    };
    $isAll = $selected === null || $selected === '';
@endphp

<nav class="dive-filter-chips d-flex align-items-center gap-2" aria-label="Filter by {{ $label ?: $param }}">
    @if($label !== '')<span class="text-sm text-secondary me-1">{{ $label }}</span>@endif
    @if($all !== '')
        <a href="{{ $url(null) }}" class="chip {{ $isAll ? 'chip-on' : '' }}" @if($isAll) aria-current="true" @endif>{{ $all }}</a>
    @endif
    @foreach($options as $value => $text)
        @php $on = !$isAll && (string) $selected === (string) $value; @endphp
        <a href="{{ $on && $toggle ? $url(null) : $url($value) }}" class="chip {{ $on ? 'chip-on' : '' }}" @if($on) aria-current="true" @endif>{{ $text }}</a>
    @endforeach
</nav>

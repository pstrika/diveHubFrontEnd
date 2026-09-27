{{--
    Sort control as chips, matching the filter chips. Plain links that set
    ?sort=key and keep every other query parameter.

    $options  array of key => label, in display order
    $selected the active key

    Usage: <x-sort-chips :options="['rate' => 'Top rated', 'name' => 'A to Z', 'maxDepth' => 'Deepest']" :selected="$filters['sort']" />
--}}
@props(['options', 'selected'])

@php
    $url = function ($key) {
        $params = request()->except('sort', 'page');
        $params['sort'] = $key;
        return request()->url() . '?' . http_build_query($params);
    };
@endphp

<nav class="dive-filter-chips d-flex flex-wrap align-items-center gap-2" aria-label="Sort sites">
    <span class="text-sm text-secondary me-1">Sort</span>
    @foreach($options as $key => $label)
        <a href="{{ $url($key) }}" class="chip {{ $selected === $key ? 'chip-on' : '' }}" @if($selected === $key) aria-current="true" @endif>{{ $label }}</a>
    @endforeach
</nav>

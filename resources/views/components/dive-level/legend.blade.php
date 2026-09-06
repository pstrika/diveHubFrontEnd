{{--
    Inline level legend.

    Replaces the "Site levels" modal that used to sit behind a "(?)" link in
    the table header (finding F-09). Always visible, one line per level, and
    generated from App\Support\DiveLevel so it can never disagree with the
    icons or the filter chips.

    Usage: <x-dive-level.legend />
--}}
<div {{ $attributes->merge(['class' => 'dive-level-legend d-flex flex-wrap align-items-center gap-3 text-sm text-secondary']) }}
     role="note" aria-label="Site level legend">
    <span class="fw-bold">Levels:</span>
    @foreach(\App\Support\DiveLevel::all() as $lvl)
        <span class="d-inline-flex align-items-center gap-1 text-nowrap">
            <x-dive-level.icon :level="$lvl['value']" height="18" />
            <span><b>{{ $lvl['code'] }}</b> {{ $lvl['name'] }} <span class="opacity-7">to {{ \App\Support\DiveLevel::depthLabel($lvl['value']) }}</span></span>
        </span>
    @endforeach
</div>

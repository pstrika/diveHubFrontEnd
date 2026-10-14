{{--
    Gas-mix split pill - same O2 green / He blue convention used everywhere
    else on the site (Deco Planner, Gas Planning, Multi Dive Planner's own
    "show the gases in the table in the pill format...same convention in
    the whole site", Pablo 2026-10-14), reused here for the CCR Checklist's
    O2/He fields (Pablo, 2026-10-17: "I was referring to the split pills we
    use, not these white ones"). A solo O2% pill rounds fully; a real O2+He
    mix splits into two colored halves; plain air (21/0) and pure O2
    (100/0) collapse to a single grey "Air"/"Pure O2" label pill, same as
    the Deco Planner's own gas pills (dhGasPillO2Info in deco-planner.js).

    Usage: <x-gas-mix-pill :o2="$answer['o2_percent'] ?? null" :he="$answer['he_percent'] ?? null" />
--}}
@props(['o2' => null, 'he' => null])

@php
    $hasHe = $he !== null && $he !== '' && (float) $he > 0;
    $isAir = !$hasHe && $o2 !== null && $o2 !== '' && (int) $o2 === 21;
    $isPureO2 = !$hasHe && $o2 !== null && $o2 !== '' && (int) $o2 === 100;
@endphp

@if ($o2 === null || $o2 === '')
    —
@else
    <div {{ $attributes->merge(['class' => 'dh-gas-split-pill' . ($hasHe ? '' : ' is-solo')]) }}>
        @if ($isAir)
            <span class="dh-gas-result-pill is-o2 is-air is-gas-label">Air</span>
        @elseif ($isPureO2)
            <span class="dh-gas-result-pill is-o2 is-gas-label">Pure O2</span>
        @else
            <span class="dh-gas-result-pill is-o2">{{ $o2 }}</span>
            @if ($hasHe)
                <span class="dh-gas-result-pill is-he">{{ $he }}</span>
            @endif
        @endif
    </div>
@endif

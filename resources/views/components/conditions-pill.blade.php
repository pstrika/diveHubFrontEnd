{{--
    Sea state pill. Turns the forecast engine's text (Perfect, Good, Average,
    Poor, No Dive) into a small colored pill so conditions can sit next to
    every region header and trip card instead of in a separate table
    (proposal principle "fuse conditions with decisions", W2 note 3).

    Usage: <x-conditions-pill :text="$weather->conditionsAM_text" label="AM" />
--}}
@props(['text' => null, 'label' => null])

@php
    $t = strtolower(trim((string) $text));
    if ($t === '') {
        $tone = 'none'; $word = 'No forecast';
    } elseif (in_array($t, ['perfect', 'good'])) {
        $tone = 'good'; $word = $t === 'perfect' ? 'Perfect' : 'Good';
    } elseif ($t === 'average') {
        $tone = 'avg'; $word = 'Average';
    } else {
        $tone = 'poor'; $word = $t === 'no dive' ? 'No dive' : 'Poor';
    }
@endphp

<span {{ $attributes->merge(['class' => "dh-pill dh-pill-$tone"]) }} title="Sea conditions{{ $label ? ' ' . $label : '' }}: {{ $word }}">
    @if($label)<span class="dh-pill-label">{{ $label }}</span>@endif{{ $word }}
</span>

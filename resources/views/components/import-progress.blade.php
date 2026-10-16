{{--
    Import wizard progress (Pablo, 2026-10-16: "a progress bar instead of
    the 3 pills"). step: 1 Upload, 2 Review, 3 Sites, 4 Import.
    pct (optional) fills within the current step - the Import step's own
    dive count, so the bar keeps moving while batches run.
--}}
@props(['step' => 1, 'pct' => null])
@php
    $steps = ['Upload', 'Review', 'Sites', 'Import'];
    $n = count($steps);
    $base = ($step - 1) / $n * 100;
    $fill = $pct !== null ? $base + ($pct / 100) * (100 / $n) : $base + (100 / $n) * 0.5;
@endphp
<div class="dh-wizard-progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ (int) $fill }}" aria-label="Import progress">
    <div class="dh-wizard-progress-track"><div class="dh-wizard-progress-fill" style="width: {{ $fill }}%;"></div></div>
    <div class="dh-wizard-progress-steps">
        @foreach ($steps as $i => $label)
            <span class="{{ $i + 1 < $step ? 'is-done' : ($i + 1 === $step ? 'is-active' : '') }}">
                <span class="dh-wizard-progress-dot">@if ($i + 1 < $step)<span class="material-icons-round" aria-hidden="true">check</span>@else{{ $i + 1 }}@endif</span>
                {{ $label }}
            </span>
        @endforeach
    </div>
</div>

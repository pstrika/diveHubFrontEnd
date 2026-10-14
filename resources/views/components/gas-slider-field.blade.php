{{--
    One slider+editable-text-input gas field (Pablo, 2026-10-17: "we will
    use the sliders with the text inputs as we did everywhere else in the
    site"), matching DivePlanner.blade.php's hand-built O2%/He% pattern
    (dh-gas-row / dh-gas-input-wrap / dh-gas-editable / dh-gas-input /
    dh-gas-edit-icon / dh-gas-unit) rather than divershub.js's generic
    auto-mirror (which injects its own unlabeled input and can't be
    addressed by id from a preset chip). The slider div carries
    data-min/max/step/start so gas-blending.js can generically
    noUiSlider.create() + wire every .dh-blend-slider in one loop instead
    of 26 hand-written create() calls - see initGasSliders() there.
    data-dh-num-mirror="1" opts out of the generic system (same attribute
    DivePlanner's own sliders use for the same reason).
--}}
{{-- Default step is 0.1 for gas percentages (Pablo, 2026-10-17: "gas
     slider to step by .1"); pressure fields pass :step="1" explicitly. --}}
@props(['id', 'label', 'value' => 0, 'unit' => '%', 'min' => 0, 'max' => 100, 'step' => 0.1])
<div class="mt-2">
    <label class="dh-gas-label d-block" for="{{ $id }}">{{ $label }}</label>
    <div class="dh-gas-row">
        <div class="dh-gas-input-wrap">
            <div class="dh-gas-editable">
                <input type="text" inputmode="numeric" class="dh-gas-input" id="{{ $id }}" value="{{ $value }}">
                <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
            </div>
            <span class="dh-gas-unit">{{ $unit }}</span>
        </div>
        <div class="slider-styled dh-blend-slider" id="{{ $id }}Slider" data-dh-num-mirror="1" data-min="{{ $min }}" data-max="{{ $max }}" data-step="{{ $step }}" data-start="{{ $value }}"></div>
    </div>
</div>

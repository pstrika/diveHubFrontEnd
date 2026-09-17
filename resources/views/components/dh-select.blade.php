{{--
    Custom-styled dropdown - a themed replacement for a plain <select>, whose
    OPEN popup can't be styled at all (it renders with the OS's own native
    list, not the page's CSS/fonts) (Pablo, 2026-09-17: "I really hate the
    native dropdowns...I'm ok with dropdowns, but not the system natives").

    Renders a button showing the current label, a hidden input carrying the
    real value for form submission (so the field name/behavior a controller
    expects is unchanged), and an absolutely positioned list this page's own
    CSS fully controls. Wiring (open/close, selecting an option, the
    edit-to-unlock pattern) lives once in overview.blade.php's script block.

    $name         form field name (and the hidden input's id, unless $id given)
    $options      value => label
    $selected     current value, or null
    $placeholder  leading "not set" style option; when set, null shows this label
    $disabled     starts locked like every other richer field on this page

    Usage: <x-dh-select name="level" :options="$opts" :selected="$user->certLevel" placeholder="Not set" />
--}}
@props(['name', 'id' => null, 'options', 'selected' => null, 'placeholder' => null, 'disabled' => true])

@php
    $id = $id ?? $name;
    $selectedLabel = $placeholder;
    if ($selected !== null && array_key_exists($selected, $options)) {
        $selectedLabel = $options[$selected];
    }
@endphp

<div class="dh-select" data-select>
    <button type="button" class="dh-select-btn" id="{{ $id }}-btn" @if($disabled) disabled @endif>
        <span class="dh-select-value">{{ $selectedLabel ?? 'Select…' }}</span>
        <span class="material-icons-round" aria-hidden="true">expand_more</span>
    </button>
    <input type="hidden" name="{{ $name }}" id="{{ $id }}" value="{{ $selected }}" @if($disabled) disabled @endif>
    <ul class="dh-select-menu" hidden role="listbox">
        @if($placeholder)
            <li data-value="" role="option" class="{{ $selected === null ? 'is-selected' : '' }}">{{ $placeholder }}</li>
        @endif
        @foreach($options as $value => $label)
            <li data-value="{{ $value }}" role="option" class="{{ (string) $selected === (string) $value ? 'is-selected' : '' }}">{{ $label }}</li>
        @endforeach
    </ul>
</div>

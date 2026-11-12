{{-- The three fields that describe a rebreather, shared by "add" and "edit"
     on the hub (Zach, 2026-11-10). $prefix keeps the two forms' ids apart;
     $u is the unit being edited, or null when adding. --}}
<div class="row g-3">
    <div class="col-md-4 col-12">
        <label class="dh-gas-label" for="ccrUnit_{{ $prefix }}_model">Rebreather</label>
        <select class="form-select border" id="ccrUnit_{{ $prefix }}_model" name="model">
            @foreach ($unitOptions as $key => $opt)
                <option value="{{ $key }}" @selected(($u->model ?? 'revo') === $key)>{{ $opt['label'] }}{{ empty($opt['stages']) ? ' (no checklist yet)' : '' }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 col-12">
        <label class="dh-gas-label" for="ccrUnit_{{ $prefix }}_name">What you call it</label>
        <input class="form-control border" id="ccrUnit_{{ $prefix }}_name" name="name" maxlength="120" value="{{ $u->name ?? '' }}" placeholder="My rEvo" required>
    </div>
    <div class="col-md-4 col-12">
        <label class="dh-gas-label" for="ccrUnit_{{ $prefix }}_serial">Serial (optional)</label>
        <input class="form-control border" id="ccrUnit_{{ $prefix }}_serial" name="serial_number" maxlength="120" value="{{ $u->serial_number ?? '' }}">
    </div>
</div>
{{-- Scrubber limits (Zach, 2026-11-11). Hours and minutes boxes, because a
     phone's number pad has no colon. Empty on a rEvo means 3:00 and 1:30;
     empty on anything else means Divers Hub shows time used, never time
     left: it does not know that rebreather's limits. --}}
@php
    $ccrLim = $u ? ($u->settings['scrubber'] ?? []) : [];
    $ccrBox = fn ($k, $part) => isset($ccrLim[$k]) ? ($part === 'h' ? intdiv($ccrLim[$k], 60) : $ccrLim[$k] % 60) : '';
@endphp
<div class="dh-ccr-limits">
    <div class="dh-gas-label">Scrubber limits (optional)</div>
    <div class="dh-ccr-limits-row">
        @foreach (['top' => 'Top canister, or your one canister', 'reserve' => 'Reserve canister'] as $k => $label)
            <div class="dh-ccr-hm">
                <span class="dh-ccr-hm-label">{{ $label }}</span>
                <input class="form-control border" type="number" inputmode="numeric" min="0" max="24" name="{{ $k }}_h" value="{{ $ccrBox($k, 'h') }}" aria-label="{{ $label }}, hours"><span>h</span>
                <input class="form-control border" type="number" inputmode="numeric" min="0" max="59" name="{{ $k }}_m" value="{{ $ccrBox($k, 'm') }}" aria-label="{{ $label }}, minutes"><span>min</span>
            </div>
        @endforeach
    </div>
    <div class="dh-ccr-edit-note">Used for "time left" on the hub. Left empty, a rEvo uses 3:00 on top and 1:30 reserve; other rebreathers show time used only.</div>
</div>

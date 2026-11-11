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
